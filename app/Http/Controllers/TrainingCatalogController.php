<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TrainingCatalogController extends Controller
{
    public function sync()
    {
        try {
            $response = Http::get('https://pro.dendreo.com/groupe_rem/api/modules.php', [
                'key' => config('services.dendreo.key')
            ]);

            if (!$response->successful()) {
                throw new \Exception('Erreur lors de la récupération des formations');
            }

            $formations = $response->json();

            foreach ($formations as $formation) {
                Training::updateOrCreate(
                    ['dendreo_id' => $formation['id_module']],
                    [
                        'title' => $formation['intitule'],
                        'short_title' => $formation['intitule_court'] ?? null,
                        'duration_hours' => $formation['duree_heures'] ?? null,
                        'duration_days' => $formation['duree_jours'] ?? null,
                        'validity_duration' => $formation['duree_de_validite'] ?? null,
                        'category' => $formation['categorie']['intitule'] ?? null,
                        'parent_category' => $formation['categorie']['parent']['intitule'] ?? null
                    ]
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Synchronisation réussie',
                'count' => count($formations)
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur de synchronisation Dendreo : ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $formations = Training::where('title', 'LIKE', "%{$query}%")
            ->orWhere('short_title', 'LIKE', "%{$query}%")
            ->select('id', 'dendreo_id', 'title', 'short_title', 'duration_hours', 'duration_days', 'validity_duration', 'category')
            ->limit(10)
            ->get();

        return response()->json($formations);
    }
}