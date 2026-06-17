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
            'last_run_at_timestamp' => optional($source->last_run_at)->timestamp,
            'status' => $source->scraping_status,
            'progress' => $source->scraping_progress,
            'current_page' => $source->current_page,
            'total_pages' => $source->total_pages,
        ]);
    }

    public function togglePause(ScrapingSource $source): JsonResponse
    {
        if ($source->scraping_status === 'paused') {
            $source->update(['scraping_status' => 'running']);
            ScrapeSourceJob::dispatch($source);
            $msg = "Scraping repris depuis la page " . ($source->current_page + 1);
        } else if ($source->scraping_status === 'running') {
            $source->update(['scraping_status' => 'paused']);
            $msg = "Demande d'arrêt après la page en cours transmise.";
        } else {
            return response()->json(['success' => false, 'message' => "Le scraping n'est pas en cours."]);
        }

        return response()->json(['success' => true, 'message' => $msg, 'new_status' => $source->scraping_status]);
    }
}