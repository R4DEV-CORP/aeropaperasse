<?php

namespace App\Http\Controllers;

use App\Models\DiscussionFile;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function download(DiscussionFile $file)
    {
        // Vérifier si le fichier existe physiquement
        if (! Storage::disk('public')->exists($file->path)) {
            return response()->json([
                'message' => 'Fichier non trouvé',
            ], 404);
        }

        // Retourner le fichier pour téléchargement
        return Storage::disk('public')->download($file->path, $file->name);
    }
}
