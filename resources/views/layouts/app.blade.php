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
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow-md p-4 sticky top-0 z-50">
        <div class="container mx-auto flex gap-8 items-center">
            <a href="{{ route('sources.index') }}" class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600">Plagix Engine</a>
            <div class="space-x-4">
                <a href="{{ route('sources.index') }}" class="{{ request()->routeIs('sources.*') ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600' }}">Sources</a>
                <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:text-blue-600' }}">Bibliothèque</a>
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
