<?php

namespace App\Http\Controllers;

use App\Models\ScrapingSource;
use App\Jobs\ScrapeSourceJob;
use Illuminate\Http\JsonResponse;

class SourceController extends Controller
{
    public function index()
    {
        $sources = ScrapingSource::all();
        return view('sources.index', compact('sources'));
    }

    public function scrape(ScrapingSource $source): JsonResponse
    {
        try {
            dispatch(new ScrapeSourceJob($source));
            return response()->json([
                'success' => true,
                'message' => 'Le scraping de ' . $source->name . ' a été mis en file d\'attente. Traitement en cours...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du lancement : ' . $e->getMessage()
            ], 500);
        }
    }

    public function status(ScrapingSource $source): JsonResponse
    {
        return response()->json([
            'id' => $source->id,
            'documents_collected' => $source->documents_collected,
            'last_run_at_timestamp' => $source->last_run_at ? $source->last_run_at->timestamp : null,
            'last_run_at' => $source->last_run_at ? $source->last_run_at->format('d/m/Y H:i') : 'Jamais'
        ]);
    }
}
