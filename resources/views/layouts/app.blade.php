<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plagix - Antiplagiat</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        :root {
            --primary-color: #ff751f;
        }
        .text-primary {
            color: var(--primary-color);
        }
        .bg-primary {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow-md p-4 sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Logo Left -->
            <a href="{{ route('sources.index') }}" class="text-2xl font-bold shrink-0">
                <img src="{{ asset('logo.png') }}" alt="Plagix Logo" class="h-12">
            </a>

            <!-- Search Middle -->
            <div class="flex-1 max-w-2xl mx-8">
                <form action="{{ route('documents.index') }}" method="GET" class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="search" name="search" value="{{ request('search') }}" class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-orange-500 focus:border-orange-500 transition-colors focus:outline-none" placeholder="Rechercher une thèse, un auteur, une université...">
                    <button type="submit" class="text-white absolute right-2 bottom-1.5 bg-primary hover:bg-orange-600 focus:ring-4 focus:outline-none focus:ring-orange-300 font-medium rounded-md text-sm px-4 py-1">Chercher</button>
                </form>
            </div>

            <!-- Links Right -->
            <div class="space-x-6 shrink-0 font-medium">
                <a href="{{ route('sources.index') }}" class="{{ request()->routeIs('sources.*') ? 'text-primary' : 'text-gray-600 hover:text-primary transition-colors' }}">Sources</a>
                <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'text-primary' : 'text-gray-600 hover:text-primary transition-colors' }}">Bibliothèque</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-4 mt-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
