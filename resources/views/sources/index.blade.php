@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-lg p-8">
    <h1 class="text-3xl font-bold mb-8 text-gray-800 border-b pb-4">Gestion des Sources de Scraping</h1>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Nom</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">URL</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Documents</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">Dernier Scraping</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($sources as $source)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $source->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap"><a href="{{ $source->base_url }}" target="_blank" class="text-blue-500 hover:text-blue-700 underline decoration-blue-300 underline-offset-2">{{ $source->base_url }}</a></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($source->is_active)
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow-sm border border-green-200">Actif</span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shadow-sm border border-red-200">Inactif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap" id="docs-count-{{ $source->id }}">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $source->documents_collected }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" id="last-run-{{ $source->id }}">{{ $source->last_run_at ? $source->last_run_at->format('d/m/Y H:i') : 'Jamais' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button 
                            data-timestamp="{{ $source->last_run_at ? $source->last_run_at->timestamp : '' }}"
                            onclick="triggerScrape({{ $source->id }}, this)" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-5 rounded-md shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center mx-auto"
                        >
                            <span class="btn-text">Lancer Scraping</span>
                            <svg class="animate-spin ml-2 h-4 w-4 text-white hidden loader" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
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
        <span id="toast-message" class="font-medium"></span>
    </div>
</div>
@endsection

@stack('scripts')
<script>
    function triggerScrape(sourceId, buttonElement) {
        // Prevent multiple clicks
        buttonElement.disabled = true;
        const btnText = buttonElement.querySelector('.btn-text');
        const loader = buttonElement.querySelector('.loader');
        const originalTimestamp = buttonElement.getAttribute('data-timestamp') || '';
        
        btnText.textContent = 'En cours...';
        loader.classList.remove('hidden');

        axios.post(`/sources/${sourceId}/scrape`, {}, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            showToast(response.data.message, 'success');
            pollStatus(sourceId, buttonElement, originalTimestamp);
        })
        .catch(error => {
            const msg = error.response?.data?.message || 'Une erreur est survenue.';
            showToast(msg, 'error');
            buttonElement.disabled = false;
            btnText.textContent = 'Lancer Scraping';
            loader.classList.add('hidden');
        });
    }

    function pollStatus(sourceId, buttonElement, originalTimestamp) {
        let pollCount = 0;
        const intervalId = setInterval(() => {
            pollCount++;
            if (pollCount > 120) { // Stop polling after ~4 minutes
                clearInterval(intervalId);
                buttonElement.disabled = false;
                buttonElement.querySelector('.btn-text').textContent = 'Lancer Scraping';
                buttonElement.querySelector('.loader').classList.add('hidden');
                return;
            }

            axios.get(`/sources/${sourceId}/status`)
                .then(response => {
                    const data = response.data;
                    const newTimestamp = data.last_run_at_timestamp ? data.last_run_at_timestamp.toString() : '';
                    
                    // Update current docs collected periodically just in case it changes dynamically
                    document.getElementById(`docs-count-${sourceId}`).innerHTML = `<span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">${data.documents_collected}</span>`;
                    
                    if (newTimestamp !== originalTimestamp && newTimestamp !== '') {
                        clearInterval(intervalId);
                        
                        document.getElementById(`last-run-${sourceId}`).innerText = data.last_run_at;
                        buttonElement.setAttribute('data-timestamp', newTimestamp);

                        buttonElement.disabled = false;
                        buttonElement.querySelector('.btn-text').textContent = 'Lancer Scraping';
                        buttonElement.querySelector('.loader').classList.add('hidden');

                        showToast("Le scraping asynchrone est terminé !", 'success');
                    }
                })
                .catch(err => console.error("Erreur de polling status:", err));
        }, 2000);
    }

    function showToast(message, type) {
        const toast = document.getElementById('toast');
        const msgEl = document.getElementById('toast-message');
        const icon = toast.querySelector('.info-icon');
        
        msgEl.textContent = message;
        
        if (type === 'success') {
            toast.className = "fixed bottom-6 right-6 text-white px-6 py-4 rounded-lg shadow-xl opacity-0 transition-all duration-300 transform translate-y-2 z-50 flex items-center gap-3 bg-emerald-500";
            icon.classList.remove('hidden');
        } else {
            toast.className = "fixed bottom-6 right-6 text-white px-6 py-4 rounded-lg shadow-xl opacity-0 transition-all duration-300 transform translate-y-2 z-50 flex items-center gap-3 bg-red-500";
            icon.classList.add('hidden');
        }
        
        toast.classList.remove('hidden');
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        });

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 5000);
    }
</script>
