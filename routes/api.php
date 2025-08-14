<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ActivityCommentController;
use App\Http\Controllers\BadgeRequestController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\MessageCommentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ActivityRequestController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingCatalogController;
use App\Http\Controllers\VehiclePassController;
use App\Http\Controllers\VehiclePassCommentController;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);
Route::get('/documents/{filename}', function ($filename) {
  $path = public_path("documents/{$filename}");
  if (!file_exists($path)) {
    abort(404);
  }
  return response()->download($path);
});

// Routes pour la réinitialisation de mot de passe
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/check-reset-token', [AuthController::class, 'checkResetToken']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
  // Auth routes
  Route::get('/verify-token', [AuthController::class, 'verifyToken']);
  Route::post('/logout', [AuthController::class, 'logout']);

  // Routes pour le changement de mot de passe
  Route::post('change-password', [AuthController::class, 'changePassword']);
  Route::post('first-login-change-password', [AuthController::class, 'firstLoginChangePassword']);
  Route::get('/user', [AuthController::class, 'user']);

  // Recherche d'utilisateur précise
  Route::get('/users/search', [UserController::class, 'search']);

  // Client route
  Route::get('/user/client', [AuthController::class, 'client']);
  Route::get('/clients', [ClientController::class, 'all']);
  Route::get('/clients/{client}/quota', [ClientController::class, 'getQuotaInfo']);
  Route::get('/client/{id}', [ClientController::class, 'show']);
  Route::put('/client/{id}', [ClientController::class, 'updateOverview']);
  Route::get('/client/{id}/export-bilan', [ClientController::class, 'exportOverview']);

  // Facilitation de l'accès aux fichiers pour téléchargement en ZIP
  Route::get('/file/{path}', function (Request $request, $path) {
    $path = str_replace('__', '/', $path);

    if (Storage::disk('public')->exists($path)) {
      return response()->file(storage_path('app/public/' . $path));
    }

    return response()->json(['error' => 'Fichier introuvable'], 404);
  })->where('path', '.*');


  // Badge requests + drafts routes
  Route::get('/badge-requests/drafts', [BadgeRequestController::class, 'getDrafts']);
  Route::post('/badge-requests/drafts', [BadgeRequestController::class, 'storeDraft']);
  Route::delete('/badge-requests/drafts/{id}', [BadgeRequestController::class, 'deleteDraft']);
  Route::put('/badge-requests/drafts/{id}/submit', [BadgeRequestController::class, 'submitDraft']);
  Route::put('badge-requests/{id}/status', [BadgeRequestController::class, 'updateStatus']);
  Route::get('/badge-requests/approved', [BadgeRequestController::class, 'getApproved']);
  Route::apiResource('badge-requests', BadgeRequestController::class);

  // Comments routes
  Route::get('badge-requests/{badgeRequest}/comments', [CommentController::class, 'getComments']);
  Route::post('comments', [CommentController::class, 'store']);
  Route::put('comments/{comment}', [CommentController::class, 'update']);
  Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

  //activity request
  Route::get('/activity-requests/drafts', [ActivityRequestController::class, 'getDrafts']);
  Route::post('/activity-requests/drafts', [ActivityRequestController::class, 'storeDraft']);
  Route::put('/activity-requests/drafts/{id}/submit', [ActivityRequestController::class, 'submitDraft']);
  Route::put('/activity-requests/{id}/status', [ActivityRequestController::class, 'updateStatus']);
  Route::post('/activity-requests/{id}/renew', [ActivityRequestController::class, 'renewRequest']);
  Route::post('/activity-requests', [ActivityRequestController::class, 'store']);
  Route::get('/activity-requests', [ActivityRequestController::class, 'index']);

  // Comments Activity routes
  Route::get('activity-requests/{ActivityRequest}/comments', [ActivityCommentController::class, 'getComments']);
  Route::post('activity-comments', [ActivityCommentController::class, 'store']);
  Route::put('activity-comments/{comment}', [ActivityCommentController::class, 'update']);
  Route::delete('activity-comments/{comment}', [ActivityCommentController::class, 'destroy']);

  // Replies Activity routes
  Route::post('activity-comments/{comment}/replies', [ActivityCommentController::class, 'storeReply']);
  Route::put('replies/{reply}', [ActivityCommentController::class, 'updateReply']);
  Route::delete('replies/{reply}', [ActivityCommentController::class, 'destroyReply']);

  // Vehicle Pass routes
  Route::get('/vehicle-passes/drafts', [VehiclePassController::class, 'getDrafts']);
  Route::post('/vehicle-passes/drafts', [VehiclePassController::class, 'storeDraft']);
  Route::delete('/vehicle-passes/drafts/{id}', [VehiclePassController::class, 'deleteDraft']);
  Route::put('/vehicle-passes/drafts/{id}/submit', [VehiclePassController::class, 'submitDraft']);
  Route::put('/vehicle-passes/{id}/status', [VehiclePassController::class, 'updateStatus']);
  Route::post('/vehicle-passes', [VehiclePassController::class, 'store']);
  Route::get('/vehicle-passes', [VehiclePassController::class, 'index']);

  // Comments Vehicle Pass routes
  Route::get('vehicle-passes/{vehiclePass}/comments', [VehiclePassCommentController::class, 'getComments']);
  Route::post('vehicle-pass-comments', [VehiclePassCommentController::class, 'store']);
  Route::put('vehicle-pass-comments/{comment}', [VehiclePassCommentController::class, 'update']);
  Route::delete('vehicle-pass-comments/{comment}', [VehiclePassCommentController::class, 'destroy']);

  // Replies Vehicle Pass routes
  Route::post('vehicle-pass-comments/{comment}/replies', [VehiclePassCommentController::class, 'storeReply']);
  Route::put('vehicle-pass-replies/{reply}', [VehiclePassCommentController::class, 'updateReply']);
  Route::delete('vehicle-pass-replies/{reply}', [VehiclePassCommentController::class, 'destroyReply']);

  // client
  Route::apiResource('user/badge-requests', BadgeRequestController::class); // ⚠️ Possibly breaking route ⚠️

  // Replies routes
  Route::post('comments/{comment}/replies', [CommentController::class, 'storeReply']);
  Route::put('replies/{reply}', [CommentController::class, 'updateReply']);
  Route::delete('replies/{reply}', [CommentController::class, 'destroyReply']);
});

// Routes admin
Route::middleware(['auth:sanctum', 'role:admin,sadmin'])->group(function () {
  Route::apiResource('users', UserController::class);
  Route::apiResource('clients', ClientController::class);
  Route::get('/clients/{client}/document', [ClientController::class, 'downloadDocument']);
});
Route::middleware(['auth:sanctum', 'role:sclient'])->group(function () {
  Route::get('/client-users', [ClientUserController::class, 'index']);
  Route::post('/client-users', [ClientUserController::class, 'store']);
  Route::put('/client-users/{id}', [ClientUserController::class, 'update']);
  Route::delete('/client-users/{id}', [ClientUserController::class, 'destroy']);
});

// Formations
Route::middleware(['auth:sanctum', 'role:sadmin,admin'])->group(function () {
  Route::get('/trainings/clients', [TrainingController::class, 'getClientsWithTrainings']);
});

Route::middleware(['auth:sanctum', 'training.access'])->group(function () {
  Route::get('/trainings/clients/{id}', [TrainingController::class, 'getClientDetails']); // Dashboard d'utilisateurs (d'un client)
  Route::get('/trainings/users/{id}', [TrainingController::class, 'getUserTrainings']); // Modale de formations d'un utilisateur
  Route::get('/trainings/catalog', [TrainingController::class, 'getCatalog']); // Requête du catalogue de formations
  Route::post('/user-trainings', [TrainingController::class, 'store']); // Attribution de formation à un utilisateur
  Route::get('/training-catalog/search', [TrainingCatalogController::class, 'search']); // Recherche de formation
  Route::post('/user-trainings/{id}/certificate', [TrainingController::class, 'uploadCertificate']); // Upload de certificat
  Route::get('/user-trainings/{id}/certificate', [TrainingController::class, 'downloadCertificate']); // Download de certificat
});

// Route::middleware(['auth:sanctum'])->group(function () {
//     Route::get('/conversations', [ConversationController::class, 'index']);
//     Route::post('/conversations', [ConversationController::class, 'store']);
//     Route::get('/conversations/{id}', [ConversationController::class, 'show']);
//     Route::get('/conversations/{id}/messages', [ConversationController::class, 'getMessages']);
//     Route::post('/conversations/{id}/messages', [ConversationController::class, 'addMessage']);
//     Route::patch('/conversations/{id}/status', [ConversationController::class, 'updateStatus']);
// });

//Routes du systeme de messagerie
// Route::middleware('auth:sanctum')->group(function () {
//     // Routes pour les messages
//     Route::get('/messages', [MessageController::class, 'index']);
//     Route::post('/messages', [MessageController::class, 'store']);
//     Route::get('/messages/{message}', [MessageController::class, 'show']);
//     Route::post('/messages/{message}/reply', [MessageController::class, 'reply']);

//     // Si vous êtes admin, ajoutez ces routes avec middleware supplémentaire
//     // Route::middleware('admin')->group(function () {
//     //     Route::get('/admin/messages', [MessageController::class, 'adminIndex']); // Pour voir tous les messages
//     //     Route::post('/admin/messages/{message}/reply', [MessageController::class, 'adminReply']); // Pour répondre
//     //     Route::put('/messages/{message}/mark-as-read', [MessageController::class, 'markAsRead']); // Marquer comme lu
//     // });
// });
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/badges', [BadgeController::class, 'index']);
  Route::post('/badges/import', [BadgeController::class, 'import']);
  Route::post('/badges', [BadgeController::class, 'store']);
  Route::get('/badges/{badge}', [BadgeController::class, 'show']);
  Route::post('/badges/{badge}/return', [BadgeController::class, 'return']);
  Route::put('/badges/{badge}/expiry-date', [BadgeController::class, 'updateExpiryDate']);
  // Route pour la vérification des badges expirés (à protéger selon vos besoins)
  Route::post('/badges/check-expired', [BadgeController::class, 'checkExpiredBadges']);

  Route::put('/badges/{id}/status', [BadgeController::class, 'updateStatus']);
});
Route::middleware(['auth:sanctum'])->group(function () {
  // Routes des discussions
  Route::get('/discussions', [DiscussionController::class, 'index']);
  Route::post('/discussions', [DiscussionController::class, 'store']);
  Route::get('/discussions/{discussion}', [DiscussionController::class, 'show']);
  Route::post('/discussions/{discussion}/status', [DiscussionController::class, 'updateStatus']);
  Route::post('/discussions/{discussion}/mark-as-read', [DiscussionController::class, 'markAsRead']);

  // Routes des commentaires
  Route::post('/discussions/{discussion}/comments', [MessageCommentController::class, 'store']);
});
Route::get('/files/{file}/download', [FileController::class, 'download'])
  ->name('files.download')
  ->middleware('auth:sanctum');
