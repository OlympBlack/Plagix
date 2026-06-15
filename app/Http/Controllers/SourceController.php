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
            ScrapeSourceJob::dispatch($source);
            return response()->json([
                'success' => true,
                'message' => 'Le scraping de ' . $source->name . ' a été lancé avec succès ! (Asynchrone)'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du lancement : ' . $e->getMessage()
            ], 500);
        }
    }
}
