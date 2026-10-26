<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MR. WHITE') — Party Game</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col items-center p-4 font-sans antialiased">
    <div class="my-auto w-full max-w-md py-4">
        @yield('content')
    </div>
</body>
</html>
