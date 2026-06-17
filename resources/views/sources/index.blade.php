@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-2xl font-bold mb-8 text-gray-800 border-b pb-4 uppercase tracking-tight">Gestion des Sources de Scraping</h1>

    <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full border-collapse bg-white text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest w-1/4">Source</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest text-center">État</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest text-center">Progression</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest text-center">Documents</th>
                    <th class="px-6 py-4 text-[10px] whitespace-nowrap font-bold text-gray-500 uppercase tracking-widest">Dernier Scraping</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest text-right">Contrôle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($sources as $source)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-5">
                        <div class="font-bold text-gray-900 text-sm mb-1">{{ $source->name }}</div>
                        <a href="{{ $source->base_url }}" target="_blank" class="text-[10px] text-blue-500 hover:text-blue-700 underline decoration-blue-200 underline-offset-2">{{ $source->base_url }}</a>
                    </td>
                    <td class="px-6 py-5 text-center" id="status-col-{{ $source->id }}">
                        @php
                            $sClass = [
                                'idle' => 'bg-gray-100 text-gray-600 border-gray-200',
                                'running' => 'bg-blue-50 text-blue-700 border-blue-200 animate-pulse',
                                'paused' => 'bg-orange-50 text-orange-700 border-orange-200',
                                'error' => 'bg-red-50 text-red-700 border-red-200',
                            ][$source->scraping_status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                            $sLabel = [
                                'idle' => 'PRÊT',
                                'running' => 'EN COURS...',
                                'paused' => 'EN PAUSE',
                                'error' => 'ERREUR',
                            ][$source->scraping_status] ?? 'INACTIF';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-[9px] font-black rounded border {{ $sClass }}">
                            {{ $sLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-5 align-middle">
                        <div class="w-full min-w-[150px]">
                            <div class="flex justify-between items-end mb-1">
                                <span class="text-[10px] font-bold text-gray-600" id="page-count-{{ $source->id }}">
                                    @if($source->scraping_status === 'running' || $source->scraping_status === 'paused')
                                        Page {{ $source->current_page }} / {{ $source->total_pages ?: '?' }}
                                    @else
                                        ---
                                    @endif
                                </span>
                                <span class="text-[10px] font-black text-indigo-600" id="prog-pct-{{ $source->id }}">{{ $source->scraping_progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div id="prog-bar-{{ $source->id }}" class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $source->scraping_progress }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded text-xs font-bold border border-indigo-100 shadow-sm" id="docs-count-{{ $source->id }}">
                            {{ number_format($source->documents_collected, 0, ',', ' ') }}
                        </span>
                    </td>
                    <td class="px-6 py-5 text-[10px] text-gray-500 font-medium whitespace-nowrap" id="last-run-{{ $source->id }}">
                        {{ $source->last_run_at ? $source->last_run_at->format('d/m/Y H:i') : 'JAMAIS' }}
                    </td>
                    <td class="px-6 py-5 text-right space-x-2 whitespace-nowrap">
                        <button 
                            id="btn-scrape-{{ $source->id }}"
                            onclick="triggerScrape({{ $source->id }}, 'start')" 
                            {{ $source->scraping_status == 'running' ? 'disabled' : '' }}
                            class="{{ $source->scraping_status == 'paused' || $source->scraping_status == 'running' ? 'hidden' : '' }} bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1.5 px-4 rounded shadow-sm transition-all text-[9px] uppercase tracking-widest disabled:opacity-50"
                        >
                            Lancer
                        </button>
                        <button 
                            id="btn-pause-{{ $source->id }}"
                            onclick="triggerScrape({{ $source->id }}, 'pause')" 
                            class="{{ $source->scraping_status != 'running' ? 'hidden' : '' }} bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-1.5 px-4 rounded shadow-sm transition-all text-[9px] uppercase tracking-widest"
                        >
                            Pause
                        </button>
                        <button 
                            id="btn-resume-{{ $source->id }}"
                            onclick="triggerScrape({{ $source->id }}, 'resume')" 
                            class="{{ $source->scraping_status != 'paused' ? 'hidden' : '' }} bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 font-bold py-1.5 px-4 rounded shadow-sm transition-all text-[9px] uppercase tracking-widest"
                        >
                            Reprendre
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-6 right-6 text-white px-6 py-4 rounded-lg shadow-xl hidden opacity-0 transition-all duration-300 transform translate-y-2 z-50 flex items-center gap-3">
        <svg class="w-6 h-6 info-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span id="toast-message" class="font-medium text-[11px] uppercase tracking-wider"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activePolls = {};

    function triggerScrape(id, action) {
        let url = `/sources/${id}/scrape`;
        if (action === 'pause' || action === 'resume') {
            url = `/sources/${id}/toggle-pause`;
        }

        const bStart = document.getElementById(`btn-scrape-${id}`);
        const bPause = document.getElementById(`btn-pause-${id}`);
        const bResume = document.getElementById(`btn-resume-${id}`);

        // Update UI optimistically
        if (action === 'start' || action === 'resume') {
            bStart.classList.add('hidden');
            bResume.classList.add('hidden');
            bPause.classList.remove('hidden');
            document.getElementById(`status-col-${id}`).innerHTML = `<span class="px-3 py-1 inline-flex text-[9px] font-black rounded border bg-blue-50 text-blue-700 border-blue-200 animate-pulse">EN COURS...</span>`;
        }

        axios.post(url, {}, { headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(res => {
            showToast(res.data.message, res.data.success ? 'success' : 'error');
            if (res.data.success) {
                if (action === 'start' || action === 'resume') {
                    startPolling(id);
                } else if (action === 'pause') {
                    bStart.classList.add('hidden');
                    bPause.classList.add('hidden');
                    bResume.classList.remove('hidden');
                    document.getElementById(`status-col-${id}`).innerHTML = `<span class="px-3 py-1 inline-flex text-[9px] font-black rounded border bg-orange-50 text-orange-700 border-orange-200">EN PAUSE</span>`;
                    pollOnce(id); // Poll to get exact status
                }
            }
        })
        .catch(err => showToast("Une erreur est survenue", 'error'));
    }

    function startPolling(id) {
        if (activePolls[id]) return;
        activePolls[id] = setInterval(() => pollOnce(id), 2500); // Poll every 2.5s
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
            document.getElementById(`docs-count-${id}`).textContent = new Intl.NumberFormat('fr-FR').format(data.documents_collected);
        }
        
        // Last run
        if (document.getElementById(`last-run-${id}`)) {
            document.getElementById(`last-run-${id}`).textContent = data.last_run_at || 'JAMAIS';
        }

        // Status Badge
        const badges = {
            'idle': { text: 'PRÊT', class: 'bg-gray-100 text-gray-600 border-gray-200' },
            'running': { text: 'EN COURS...', class: 'bg-blue-50 text-blue-700 border-blue-200 animate-pulse' },
            'paused': { text: 'EN PAUSE', class: 'bg-orange-50 text-orange-700 border-orange-200' },
            'error': { text: 'ERREUR', class: 'bg-red-50 text-red-700 border-red-200' },
        };
        const b = badges[data.status] || badges['idle'];
        if (document.getElementById(`status-col-${id}`)) {
            document.getElementById(`status-col-${id}`).innerHTML = `
                <span class="px-3 py-1 inline-flex text-[9px] font-black rounded border ${b.class}">
                    ${b.text}
                </span>
            `;
        }

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

    function showToast(message, type) {
        const toast = document.getElementById('toast');
        const msgEl = document.getElementById('toast-message');
        const icon = toast.querySelector('.info-icon');
        
        msgEl.textContent = message;
        
        if (type === 'success') {
            toast.className = "fixed bottom-6 right-6 text-white px-6 py-4 rounded-lg shadow-xl opacity-0 transition-all duration-300 transform translate-y-2 z-50 flex items-center gap-3 bg-indigo-900";
            icon.classList.remove('hidden');
        } else {
            toast.className = "fixed bottom-6 right-6 text-white px-6 py-4 rounded-lg shadow-xl opacity-0 transition-all duration-300 transform translate-y-2 z-50 flex items-center gap-3 bg-red-600";
            icon.classList.add('hidden');
        }
        
        toast.classList.remove('hidden');
        
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        });

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 4000);
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
