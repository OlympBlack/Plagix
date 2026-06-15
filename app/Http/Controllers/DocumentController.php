<?php

namespace App\Http\Controllers;

use App\Models\CollectedDocument;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = CollectedDocument::with('source')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('author', 'LIKE', '%' . $search . '%')
                  ->orWhere('university', 'LIKE', '%' . $search . '%');
            });
        }

        $documents = $query->paginate(15)->withQueryString();

        return view('documents.index', compact('documents'));
    }
}
