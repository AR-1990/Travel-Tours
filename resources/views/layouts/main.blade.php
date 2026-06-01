<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel Boilerplate'))</title>
    <meta name="description" content="@yield('meta_description', 'Travel Tours platform for admins, agents, sub-agents, and users.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @php
        $viteHot = file_exists(public_path('hot'));
        $manifestPath = public_path('build/manifest.json');
        $appCssFromManifest = null;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $appCssFromManifest = $manifest['resources/css/app.css']['file'] ?? null;
        }
    @endphp
    @if ($viteHot)
        @vite(['resources/css/app.css'])
    @elseif ($appCssFromManifest)
        <link rel="stylesheet" href="{{ asset('build/'.$appCssFromManifest) }}">
    @endif
    @yield('styles')
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="min-h-screen">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
