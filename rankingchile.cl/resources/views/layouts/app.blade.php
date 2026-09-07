<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    @stack('meta')
    @stack('schema')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="bg-[#FDFDFC] text-[#1B1B18] antialiased">
    @yield('body')

    @stack('scripts')
</body>
</html>
