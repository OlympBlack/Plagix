@extends('layouts.app')

@section('content')
<div class="bg-gray-50 rounded-xl shadow-inner border border-gray-100 p-8 min-h-[80vh]">
    <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200">
        <h1 class="text-3xl font-bold text-gray-800">Bibliothèque des Documents</h1>
        <span class="bg-purple-100 text-purple-800 text-sm font-semibold px-4 py-1.5 rounded-full border border-purple-200">
            Total : {{ $documents->total() }} documents
        </span>
    </div>

    @if($documents->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-lg border-2 border-dashed border-gray-300">
            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Aucun document collecté</h3>
            <p class="text-gray-500 max-w-sm">Lancez un scraping asynchrone depuis la page des sources pour alimenter la bibliothèque.</p>
            <a href="{{ route('sources.index') }}" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition">Aller aux sources</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($documents as $doc)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 leading-snug mb-3 group-hover:text-blue-700 transition-colors line-clamp-3" title="{{ $doc->title }}">{{ $doc->title }}</h3>
                    
                    <div class="space-y-2">
                        <div class="flex items-start text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="line-clamp-1"><span class="font-medium text-gray-700">Auteur:</span> {{ $doc->author ?? 'Inconnu' }}</span>
                        </div>
                        
                        @if($doc->university)
                        <div class="flex items-start text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="line-clamp-1"><span class="font-medium text-gray-700">Université:</span> {{ $doc->university }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100" title="Source: {{ $doc->source->name ?? 'Inconnue' }}">
                        {{ Str::limit($doc->source->name ?? 'Inconnue', 15) }}
                    </span>
                    <a href="{{ $doc->source_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-semibold inline-flex items-center bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                        Consulter
                        <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection
