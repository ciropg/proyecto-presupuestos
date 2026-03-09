<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @include('layouts.theme-init')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <x-theme-toggle />

        <div class="min-h-screen flex flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-slate-950">
            <div>
                <a href="/">
                    <x-application-logo class="h-20 w-20 fill-current text-gray-500 dark:text-slate-300" />
                </a>
            </div>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-slate-900">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
