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
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group cursor-pointer"
                 onclick="openDocModal({{ $doc->id }})">
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

                        @if($doc->publication_year)
                        <div class="flex items-start text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span><span class="font-medium text-gray-700">Publication:</span> {{ $doc->publication_year }}</span>
                        </div>
                        @endif

                        @if($doc->description)
                        <div class="mt-3 pt-3 border-t border-gray-50">
                            <p class="text-sm text-gray-500 leading-relaxed line-clamp-3">{{ $doc->description }}</p>
                            <button type="button" class="mt-1 text-xs text-blue-600 hover:text-blue-800 font-semibold hover:underline"
                                    onclick="event.stopPropagation(); openDocModal({{ $doc->id }})">
                                Lire plus →
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100" title="Source: {{ $doc->source->name ?? 'Inconnue' }}">
                        {{ Str::limit($doc->source->name ?? 'Inconnue', 15) }}
                    </span>
                    <a href="{{ $doc->source_url }}" target="_blank" onclick="event.stopPropagation()" class="text-blue-600 hover:text-blue-800 text-sm font-semibold inline-flex items-center bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
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

{{-- Modale détail document --}}
<div id="docModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto no-scrollbar relative animate-fade-in">
        {{-- Header --}}
        <div class="sticky top-0 bg-white border-b border-gray-100 px-8 py-5 rounded-t-2xl flex items-start justify-between z-10">
            <h2 id="modal-title" class="text-xl font-bold text-gray-900 pr-8 leading-snug"></h2>
            <button onclick="closeDocModal()" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 transition-colors" title="Fermer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-8 py-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Auteur</p>
                    <p id="modal-author" class="text-sm text-gray-800 font-medium"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Université</p>
                    <p id="modal-university" class="text-sm text-gray-800 font-medium"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Publication</p>
                    <p id="modal-year" class="text-sm text-gray-800 font-medium"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">URL du document</p>
                    <a id="modal-url" href="#" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline break-all font-medium"></a>
                </div>
            </div>

            <div id="modal-desc-wrapper">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description complète</p>
                <div id="modal-description" class="text-sm text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4 border border-gray-100 max-h-[40vh] overflow-y-auto no-scrollbar"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-100 px-8 py-4 rounded-b-2xl flex justify-end gap-3">
            <a id="modal-consult-btn" href="#" target="_blank" class="inline-flex items-center px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                Consulter le document
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
            <button onclick="closeDocModal()" class="px-5 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.25s ease-out;
    }
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
    // Données documents injectées depuis le serveur
    const documentsData = @json($documents->getCollection()->keyBy('id'));

    function openDocModal(id) {
        const doc = documentsData[id];
        if (!doc) return;

        document.getElementById('modal-title').textContent = doc.title || '';
        document.getElementById('modal-author').textContent = doc.author || 'Inconnu';
        document.getElementById('modal-university').textContent = doc.university || 'Non renseignée';
        document.getElementById('modal-year').textContent = doc.publication_year || 'Non renseignée';
        document.getElementById('modal-url').textContent = doc.source_url || '';
        document.getElementById('modal-url').href = doc.source_url || '#';
        document.getElementById('modal-consult-btn').href = doc.source_url || '#';

        const descWrapper = document.getElementById('modal-desc-wrapper');
        const descEl = document.getElementById('modal-description');
        if (doc.description) {
            descEl.textContent = doc.description;
            descWrapper.style.display = 'block';
        } else {
            descWrapper.style.display = 'none';
        }

        const modal = document.getElementById('docModal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeDocModal() {
        const modal = document.getElementById('docModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDocModal();
    });

    // Fermer en cliquant sur le backdrop
    document.getElementById('docModal').addEventListener('click', function(e) {
        if (e.target === this) closeDocModal();
    });
</script>
@endsection
