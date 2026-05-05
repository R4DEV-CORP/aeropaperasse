<?php

namespace App\Actions\ActivityRequest;

use App\Models\ActivityRequest;
use App\Models\ActivityRequestAttachment;
use App\Services\ActivityRequestRenewalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Action pour renouveler une demande d'activité approuvée.
 *
 * Reproduit l'intention du legacy ActivityRequestController::renewRequest :
 * clone la demande d'origine, la marque comme renouvellement, lie la demande
 * précédente et copie chaque pièce jointe sur le disque.
 */
class RenewActivityRequestAction
{
    public function __construct(
        private ActivityRequestRenewalService $renewalService
    ) {}

    public function execute(ActivityRequest $original, int $userId): ActivityRequestResult
    {
        if ($original->status !== 'approved') {
            return ActivityRequestResult::failure(
                'Seules les demandes approuvées peuvent être renouvelées.',
                'renew'
            );
        }

        try {
            return DB::transaction(function () use ($original, $userId) {
                $renewalData = $this->renewalService->prepareRenewalData(
                    previousActivityRequestId: $original->id,
                    clientId: $original->client_id,
                    userId: $userId,
                    isDraft: false,
                );

                $newRequest = ActivityRequest::create($renewalData);

                $this->copyAttachments($original, $newRequest);

                Log::info('Demande d\'activité renouvelée', [
                    'original_id' => $original->id,
                    'new_id' => $newRequest->id,
                    'user_id' => $userId,
                ]);

                return ActivityRequestResult::success(
                    $newRequest->fresh(),
                    'Demande renouvelée avec succès.',
                    'renew'
                );
            });
        } catch (\Exception $e) {
            Log::error('Erreur lors du renouvellement de la demande d\'activité', [
                'original_id' => $original->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return ActivityRequestResult::failure(
                'Erreur lors du renouvellement : '.$e->getMessage(),
                'renew'
            );
        }
    }

    private function copyAttachments(ActivityRequest $original, ActivityRequest $newRequest): void
    {
        $disk = Storage::disk('public');

        foreach ($original->attachments as $attachment) {
            $originalPath = $attachment->path;

            if (! $disk->exists($originalPath)) {
                Log::warning('Pièce jointe introuvable lors du renouvellement, ignorée', [
                    'attachment_id' => $attachment->id,
                    'path' => $originalPath,
                ]);

                continue;
            }

            $newPath = $this->buildCopyPath($originalPath);
            $disk->copy($originalPath, $newPath);

            ActivityRequestAttachment::create([
                'activity_request_id' => $newRequest->id,
                'type' => $attachment->type,
                'path' => $newPath,
                'name' => $attachment->name,
            ]);
        }
    }

    private function buildCopyPath(string $originalPath): string
    {
        $info = pathinfo($originalPath);
        $extension = isset($info['extension']) ? '.'.$info['extension'] : '';
        $filename = $info['filename'].'_copy_'.now()->timestamp.$extension;

        return ($info['dirname'] === '.' ? '' : $info['dirname'].'/').$filename;
    }
}
