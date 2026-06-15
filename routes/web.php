<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return redirect()->route('sources.index');
});

Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
Route::post('/sources/{source}/scrape', [SourceController::class, 'scrape'])->name('sources.scrape');
Route::get('/sources/{source}/status', [SourceController::class, 'status'])->name('sources.status');

Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
