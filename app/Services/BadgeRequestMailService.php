<?php

namespace App\Services;

use App\Models\BadgeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BadgeRequestMailService
{
    /**
     * Envoie un email lors de la création d'une demande de badge
     */
    public function sendCreatedMail(BadgeRequest $badgeRequest)
    {
        try {
            // Envoyer un email au demandeur
            if($badgeRequest->status === 'draft'){
                return true;
            } 
            if ($badgeRequest->email) {
                Mail::send('emails.badge-request.created', [
                    'badgeRequest' => $badgeRequest,
                    'nom' => $badgeRequest->nom,
                    'prenom' => $badgeRequest->prenom
                ], function($message) use ($badgeRequest) {
                    $message->to($badgeRequest->email);
                    $message->subject('Votre demande de badge a été soumise');
                });
            }
            
            // Notifier les admins
            $this->notifyAdmins('emails.badge-request.admin-notification', [
                'badgeRequest' => $badgeRequest,
                'subject' => 'Nouvelle demande de badge soumise',
                'action' => 'créée'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du mail de création de demande: ' . $e->getMessage());
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
            // Envoyer un email au demandeur
            if ($badgeRequest->email) {
                // Si le statut est "ready-for-pickup", utiliser le template spécifique
                if ($badgeRequest->status === 'ready-for-pickup') {
                    Mail::send('emails.badge-request.ready-for-pickup', [
                        'badgeRequest' => $badgeRequest,
                        'nom' => $badgeRequest->nom,
                        'prenom' => $badgeRequest->prenom,
                        'status' => $this->getStatusLabel($badgeRequest->status)
                    ], function($message) use ($badgeRequest) {
                        $message->to($badgeRequest->email);
                        $message->subject('Votre badge est prêt à être récupéré');
                    });
                } elseif($badgeRequest->status === 'draft'){
                    //do nothing
                } else {
                    // Sinon, utiliser le template de mise à jour standard
                    Mail::send('emails.badge-request.status-updated', [
                        'badgeRequest' => $badgeRequest,
                        'nom' => $badgeRequest->nom,
                        'prenom' => $badgeRequest->prenom,
                        'status' => $this->getStatusLabel($badgeRequest->status)
                    ], function($message) use ($badgeRequest) {
                        $message->to($badgeRequest->email);
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
            Log::error('Erreur lors de l\'envoi du mail de mise à jour de statut: ' . $e->getMessage());
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
            $admins = User::whereIn('role', ['admin', 'sadmin'])
                ->whereNotNull('email')
                ->get();
                
            foreach ($admins as $admin) {
                Mail::send($view, $data, function($message) use ($admin, $data) {
                    $message->to($admin->email);
                    $message->subject($data['subject']);
                });
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications aux admins: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convertit le code de statut en libellé plus lisible
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending_rem' => 'En attente de validation par Rem Distribution',
            'rejected_rem' => 'Rejetée par Rem Distribution',
            'pending_adp' => 'En attente de validation par ADP',
            'approved_adp' => 'Approuvée par ADP',
            'rejected_adp' => 'Rejetée par ADP',
            'pending_fabrication' => 'En cours de fabrication',
            'ready_for_delivery' => 'Prête à être remise'
        ];
        
        return $labels[$status] ?? $status;
    }
}