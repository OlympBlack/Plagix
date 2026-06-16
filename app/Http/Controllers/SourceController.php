<?php

namespace App\Http\Controllers;

use App\Models\ScrapingSource;
use App\Jobs\ScrapeSourceJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
            Log::info("Queue dispatch scraping source: {$source->name}");

            ScrapeSourceJob::dispatch($source);

            return response()->json([
                'success' => true,
                'message' => "Scraping lancé pour {$source->name}. Traitement en cours..."
            ]);

        } catch (\Exception $e) {

            Log::error("Erreur dispatch job: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => "Erreur lors du lancement du scraping"
            ], 500);
        }
    }

    public function status(ScrapingSource $source): JsonResponse
    {
        return response()->json([
            'id' => $source->id,
            'documents_collected' => $source->documents_collected,
            'last_run_at' => optional($source->last_run_at)->format('d/m/Y H:i'),
        ]);
    }
}