<?php

namespace App\Http\Controllers;

use App\Models\CollectedDocument;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = CollectedDocument::with('source')->orderBy('id', 'desc')->paginate(15);
        return view('documents.index', compact('documents'));
    }
}
