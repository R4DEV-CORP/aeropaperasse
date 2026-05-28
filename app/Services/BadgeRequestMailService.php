<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\BadgeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BadgeRequestMailService
{
    /**
     * Envoie un email lors de la création d'une demande de badge
     */
    public function sendCreatedMail(BadgeRequest $badgeRequest)
    {
        try {
            // Envoyer un email au demandeur
            if ($badgeRequest->status === 'draft') {
                return true;
            }

            $recipientEmail = $this->getRecipientEmail($badgeRequest);

            if ($recipientEmail) {
                Mail::send('emails.badge-request.created', [
                    'badgeRequest' => $badgeRequest,
                    'nom' => $badgeRequest->nom,
                    'prenom' => $badgeRequest->prenom,
                ], function ($message) use ($recipientEmail) {
                    $message->to($recipientEmail);
                    $message->subject('Votre demande de badge a été soumise');
                });
            }

            // Notifier les admins
            $this->notifyAdmins('emails.badge-request.admin-notification', [
                'badgeRequest' => $badgeRequest,
                'subject' => 'Nouvelle demande de badge soumise',
                'action' => 'créée',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du mail de création de demande: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Envoie un email lors du changement de statut d'une demande de badge
     */
    // public function sendStatusUpdateMail(BadgeRequest $badgeRequest, $previousStatus)
    // {
    //     try {
    //         // Envoyer un email au demandeur
    //         if ($badgeRequest->email) {
    //             Mail::send('emails.badge-request.status-updated', [
    //                 'badgeRequest' => $badgeRequest,
    //                 'nom' => $badgeRequest->nom,
    //                 'prenom' => $badgeRequest->prenom,
    //                 'status' => $this->getStatusLabel($badgeRequest->status)
    //             ], function($message) use ($badgeRequest) {
    //                 $message->to($badgeRequest->email);
    //                 $message->subject('Mise à jour de votre demande de badge');
    //             });
    //         }

    //         // Notifier les admins
    //         $this->notifyAdmins('emails.badge-request.admin-notification', [
    //             'badgeRequest' => $badgeRequest,
    //             'subject' => 'Statut de demande de badge modifié',
    //             'action' => 'mise à jour',
    //             'previous_status' => $this->getStatusLabel($previousStatus),
    //             'current_status' => $this->getStatusLabel($badgeRequest->status)
    //         ]);

    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error('Erreur lors de l\'envoi du mail de mise à jour de statut: ' . $e->getMessage());
    //         return false;
    //     }
    // }
    public function sendStatusUpdateMail(BadgeRequest $badgeRequest, $previousStatus)
    {
        try {
            // Déterminer l'email destinataire
            $recipientEmail = $this->getRecipientEmail($badgeRequest);

            // Envoyer un email au destinataire
            if ($recipientEmail) {
                // Si le statut est "ready-for-delivery", utiliser le template spécifique
                if ($badgeRequest->status === 'ready_for_delivery') {
                    Mail::send('emails.badge-request.ready-for-pickup', [
                        'badgeRequest' => $badgeRequest,
                        'nom' => $badgeRequest->nom,
                        'prenom' => $badgeRequest->prenom,
                        'status' => $this->getStatusLabel($badgeRequest->status),
                    ], function ($message) use ($recipientEmail) {
                        $message->to($recipientEmail);
                        $message->subject('Votre badge est prêt à être récupéré');
                    });
                } elseif ($badgeRequest->status === 'draft') {
                    // do nothing
                } else {
                    // Sinon, utiliser le template de mise à jour standard
                    Mail::send('emails.badge-request.status-updated', [
                        'badgeRequest' => $badgeRequest,
                        'nom' => $badgeRequest->nom,
                        'prenom' => $badgeRequest->prenom,
                        'status' => $this->getStatusLabel($badgeRequest->status),
                    ], function ($message) use ($recipientEmail) {
                        $message->to($recipientEmail);
                        $message->subject('Mise à jour de votre demande de badge');
                    });
                }
            }

            // Notifier les admins
            /*$this->notifyAdmins('emails.badge-request.admin-notification', [
                'badgeRequest' => $badgeRequest,
                'subject' => 'Statut de demande de badge modifié',
                'action' => 'mise à jour',
                'previous_status' => $this->getStatusLabel($previousStatus),
                'current_status' => $this->getStatusLabel($badgeRequest->status)
            ]);*/

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du mail de mise à jour de statut: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Notifie les administrateurs par email
     */
    private function notifyAdmins($view, $data)
    {
        try {
            // Récupérer tous les admins et super admins
            $admins = User::whereIn('role', [Role::RemAdmin->value, Role::RemSuperAdmin->value])
                ->whereNotNull('email')
                ->get();

            foreach ($admins as $admin) {
                Mail::send($view, $data, function ($message) use ($admin, $data) {
                    $message->to($admin->email);
                    $message->subject($data['subject']);
                });
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications aux admins: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Convertit le code de statut en libellé plus lisible
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending_rem' => 'En attente',
            'rejected_rem' => 'Rejetée par Rem Distribution',
            'pending_adp' => 'En attente de validation par ADP',
            'approved_adp' => 'Approuvée par ADP',
            'rejected_adp' => 'Rejetée par ADP',
            'pending_fabrication' => 'En cours de fabrication',
            'ready_for_delivery' => 'Prête à être remis',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Détermine l'email destinataire selon la priorité : client notification_email > demandeur email
     */
    private function getRecipientEmail($badgeRequest)
    {
        // Priorité 1 : Email de notification par défaut
        if ($badgeRequest->client && ! empty($badgeRequest->client->notification_email)) {
            return $badgeRequest->client->notification_email;
        }

        // Priorité 2 : Sinon, email du demandeur
        return $badgeRequest->email;
    }
}
