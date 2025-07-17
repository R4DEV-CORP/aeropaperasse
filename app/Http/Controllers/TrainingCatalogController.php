<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TrainingCatalogController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        $formations = Training::where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title')
            ->limit(10)
            ->get();

        return response()->json($formations);
    }
}
