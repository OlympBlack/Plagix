@extends('layouts.app')

@section('content')
<div class="bg-gray-50/50 min-h-screen p-8">
    <div class="w-full bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <h1 class="text-2xl font-black mb-8 text-slate-800 uppercase tracking-wider">Gestion des Sources de Scraping</h1>

        <div class="overflow-hidden rounded-xl border border-gray-200">
            <table class="min-w-full bg-white text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-bold text-gray-500 uppercase tracking-widest text-xs w-2/5">Source</th>
                        <th class="px-6 py-4 font-bold text-gray-500 uppercase tracking-widest text-xs text-center">État</th>
                        <th class="px-6 py-4 font-bold text-gray-500 uppercase tracking-widest text-xs text-center">Documents</th>
                        <th class="px-6 py-4 font-bold text-gray-500 uppercase tracking-widest text-xs text-center">Dernier Scraping</th>
                        <th class="px-6 py-4 font-bold text-gray-500 uppercase tracking-widest text-xs text-right">Contrôle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sources as $source)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="font-bold text-slate-800 text-base mb-1">{{ $source->name }}</div>
                            <a href="{{ $source->base_url }}" target="_blank" class="text-sm text-blue-500 hover:text-blue-700 hover:underline">{{ $source->base_url }}</a>
                        </td>
                        <td class="px-6 py-5 text-center" id="status-col-{{ $source->id }}">
                            @php
                                $sClass = [
                                    'idle' => 'text-gray-500',
                                    'running' => 'text-green-600 bg-green-50 font-bold px-2 py-1 rounded',
                                    'paused' => 'text-orange-600 bg-orange-50 font-bold px-2 py-1 rounded',
                                    'error' => 'text-red-500 font-bold px-2 py-1 rounded',
                                ][$source->scraping_status] ?? 'text-gray-500';
                                $sLabel = [
                                    'idle' => 'PRÊT',
                                    'running' => 'EN COURS...',
                                    'paused' => 'PAUSE',
                                    'error' => 'ERREUR',
                                ][$source->scraping_status] ?? 'INACTIF';
                            @endphp
                            <span class="text-xs uppercase font-bold tracking-wider {{ $sClass }}">
                                {{ $sLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-green-600 bg-green-50 px-3 py-1 rounded text-sm font-bold" id="docs-count-{{ $source->id }}">
                                {{ $source->documents_collected }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-center text-sm text-gray-500" id="last-run-{{ $source->id }}">
                            {{ $source->last_run_at ? $source->last_run_at->format('d/m/Y H:i') : 'JAMAIS' }}
                        </td>
                        <td class="px-6 py-5 text-right space-x-2">
                            <button 
                                id="btn-scrape-{{ $source->id }}"
                                onclick="triggerScrape({{ $source->id }}, 'start')" 
                                {{ $source->scraping_status == 'running' ? 'disabled' : '' }}
                                class="{{ $source->scraping_status == 'paused' || $source->scraping_status == 'running' ? 'hidden' : '' }} bg-indigo-600 hover:bg-indigo-700 shadow-md text-white font-bold py-2 px-5 rounded transition-all text-xs uppercase tracking-widest disabled:opacity-50"
                            >
                                Lancer le scraping
                            </button>
                            <button 
                                id="btn-pause-{{ $source->id }}"
                                onclick="triggerScrape({{ $source->id }}, 'pause')" 
                                class="{{ $source->scraping_status != 'running' ? 'hidden' : '' }} bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-1.5 px-4 rounded transition-all text-xs uppercase tracking-widest shadow-sm"
                            >
                                Pause
                            </button>
                            <button 
                                id="btn-resume-{{ $source->id }}"
                                onclick="triggerScrape({{ $source->id }}, 'resume')" 
                                class="{{ $source->scraping_status != 'paused' ? 'hidden' : '' }} bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-1.5 px-4 rounded transition-all text-xs uppercase tracking-widest shadow-sm"
                            >
                                Reprendre
                            </button>
                        </td>
                    </tr>
                    <!-- Ligne de progression (pleine largeur) -->
                    <tr class="bg-white border-b border-gray-100">
                        <td colspan="5" class="px-6 pb-5 pt-2">
                            <div class="flex justify-between items-center mb-2 px-1">
                                <span class="text-sm font-bold text-slate-700">Progression du scraping</span>
                                <span class="text-sm font-bold text-slate-700 flex-1 text-center" id="page-count-{{ $source->id }}">
                                    @if($source->scraping_status === 'running' || $source->scraping_status === 'paused')
                                        Page {{ $source->current_page }} / {{ $source->total_pages ?: '?' }}
                                    @else
                                        ---
                                    @endif
                                </span>
                                <span class="text-sm font-black text-indigo-600" id="prog-pct-{{ $source->id }}">{{ $source->scraping_progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div id="prog-bar-{{ $source->id }}" class="bg-indigo-600 h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $source->scraping_progress }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notification Toast (Top Right) -->
    <div id="toast" class="fixed top-6 right-6 px-4 py-3 rounded-md shadow-lg border hidden opacity-0 transition-all duration-300 transform translate-y-[-10px] z-50 flex items-start gap-3 w-80">
        <svg id="toast-icon-success" class="w-5 h-5 text-green-600 mt-0.5 hidden flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <svg id="toast-icon-error" class="w-5 h-5 text-red-600 mt-0.5 hidden flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="flex-1">
            <p id="toast-title" class="font-bold text-sm text-slate-800 mb-0.5"></p>
            <p id="toast-message" class="text-xs text-slate-600 leading-relaxed"></p>
        </div>
        <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    let activePolls = {};

    function hideToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('opacity-0', 'translate-y-[-10px]');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }

    function showToast(title, message, type) {
        const toast = document.getElementById('toast');
        const titleEl = document.getElementById('toast-title');
        const msgEl = document.getElementById('toast-message');
        const iconSuccess = document.getElementById('toast-icon-success');
        const iconError = document.getElementById('toast-icon-error');
        
        titleEl.textContent = title;
        msgEl.textContent = message;
        
        iconSuccess.classList.add('hidden');
        iconError.classList.add('hidden');

        if (type === 'success') {
            toast.className = "fixed top-6 right-6 px-4 py-3 rounded-md shadow-lg border border-green-200 bg-green-50 opacity-0 transition-all duration-300 transform translate-y-[-10px] z-50 flex items-start gap-3 w-80";
            iconSuccess.classList.remove('hidden');
        } else {
            toast.className = "fixed top-6 right-6 px-4 py-3 rounded-md shadow-lg border border-red-200 bg-red-50 opacity-0 transition-all duration-300 transform translate-y-[-10px] z-50 flex items-start gap-3 w-80";
            iconError.classList.remove('hidden');
        }
        
        toast.classList.remove('hidden');
        
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-[-10px]');
            toast.classList.add('translate-y-0');
        });

        setTimeout(() => {
            hideToast();
        }, 5000);
    }

    function triggerScrape(id, action) {
        let url = `/sources/${id}/scrape`;
        if (action === 'pause' || action === 'resume') {
            url = `/sources/${id}/toggle-pause`;
        }

        const bStart = document.getElementById(`btn-scrape-${id}`);
        const bPause = document.getElementById(`btn-pause-${id}`);
        const bResume = document.getElementById(`btn-resume-${id}`);

        if (action === 'start' || action === 'resume') {
            bStart.classList.add('hidden');
            bResume.classList.add('hidden');
            bPause.classList.remove('hidden');
            updateStatusBadge(id, 'running');
        }

        axios.post(url, {}, { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(res => {
            let msg = res.data.message;
            if(!msg && action === 'start') msg = "Le scraping sur la source OATD fonctionne correctement avec la pagination.";
            showToast("Scraping en cours", msg || 'Opération réussie', res.data.success ? 'success' : 'error');
            
            if (res.data.success) {
                if (action === 'start' || action === 'resume') {
                    startPolling(id);
                } else if (action === 'pause') {
                    bStart.classList.add('hidden');
                    bPause.classList.add('hidden');
                    bResume.classList.remove('hidden');
                    updateStatusBadge(id, 'paused');
                    pollOnce(id);
                }
            }
        })
        .catch(err => {
            showToast("Erreur", "Une erreur inattendue est survenue", 'error');
            bPause.classList.add('hidden');
            bResume.classList.add('hidden');
            bStart.classList.remove('hidden');
            updateStatusBadge(id, 'error');
        });
    }

    function startPolling(id) {
        if (activePolls[id]) return;
        activePolls[id] = setInterval(() => pollOnce(id), 2500);
    }

    function pollOnce(id) {
        axios.get(`/sources/${id}/status`).then(res => {
            updateRowUI(res.data);
            if (['idle', 'error'].includes(res.data.status)) {
                clearInterval(activePolls[id]);
                delete activePolls[id];
            }
        }).catch(() => {
            clearInterval(activePolls[id]);
            delete activePolls[id];
        });
    }

    function updateStatusBadge(id, status) {
        const col = document.getElementById(`status-col-${id}`);
        if (!col) return;
        
        const badges = {
            'idle': { text: 'PRÊT', class: 'text-gray-500' },
            'running': { text: 'EN COURS...', class: 'text-green-600 bg-green-50 font-bold px-2 py-1 rounded' },
            'paused': { text: 'PAUSE', class: 'text-orange-600 bg-orange-50 font-bold px-2 py-1 rounded' },
            'error': { text: 'ERREUR', class: 'text-red-500 font-bold px-2 py-1 rounded' }
        };
        const b = badges[status] || badges['idle'];
        col.innerHTML = `
            <span class="text-xs uppercase font-bold tracking-wider ${b.class}">
                ${b.text}
            </span>
        `;
    }

    function updateRowUI(data) {
        const id = data.id;
        
        // Progression
        if (document.getElementById(`prog-bar-${id}`)) {
            document.getElementById(`prog-bar-${id}`).style.width = data.progress + '%';
            document.getElementById(`prog-pct-${id}`).textContent = data.progress + '%';
        }
        
        // Pagination Text
        if (document.getElementById(`page-count-${id}`)) {
            if (data.status === 'running' || data.status === 'paused') {
                document.getElementById(`page-count-${id}`).textContent = `Page ${data.current_page} / ${data.total_pages || '?'}`;
            } else {
                document.getElementById(`page-count-${id}`).textContent = '---';
            }
        }

        // Documents
        if (document.getElementById(`docs-count-${id}`)) {
            document.getElementById(`docs-count-${id}`).textContent = data.documents_collected;
        }
        
        // Last run
        if (document.getElementById(`last-run-${id}`)) {
            document.getElementById(`last-run-${id}`).textContent = data.last_run_at || 'JAMAIS';
        }

        // Status Badge
        updateStatusBadge(id, data.status);

        // Action Buttons Visibility
        const bStart = document.getElementById(`btn-scrape-${id}`);
        const bPause = document.getElementById(`btn-pause-${id}`);
        const bResume = document.getElementById(`btn-resume-${id}`);

        if (bStart && bPause && bResume) {
            if (data.status === 'running') {
                bStart.classList.add('hidden');
                bResume.classList.add('hidden');
                bPause.classList.remove('hidden');
            } else if (data.status === 'paused') {
                bStart.classList.add('hidden');
                bPause.classList.add('hidden');
                bResume.classList.remove('hidden');
            } else {
                bPause.classList.add('hidden');
                bResume.classList.add('hidden');
                bStart.classList.remove('hidden');
            }
        }
    }

    // Auto-start polling for running items on load
    window.onload = () => {
        @foreach($sources as $s)
            @if($s->scraping_status === 'running')
                startPolling({{ $s->id }});
            @endif
        @endforeach
    };
</script>
@endpush
