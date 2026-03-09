<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @include('layouts.theme-init')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <x-theme-toggle />

        <div class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-gray-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
            <header class="border-b border-gray-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500 dark:text-slate-400">{{ __('Public Catalog') }}</p>
                        <h1 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ config('app.name', 'Budget System') }}</h1>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                            >
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            @if (Route::has('login'))
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                >
                                    {{ __('Log In') }}
                                </a>
                            @endif

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                                >
                                    {{ __('Register') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white px-6 py-10 shadow-sm sm:px-10">
                    <div class="grid gap-8 lg:grid-cols-[1.6fr_0.8fr] lg:items-end">
                        <div class="space-y-4">
                            <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-gray-600">
                                {{ __('Published Budgets') }}
                            </span>
                            <div class="space-y-3">
                                <h2 class="text-3xl font-semibold leading-tight text-gray-900 sm:text-4xl">
                                    {{ __('Public consultation of approved budgets') }}
                                </h2>
                                <p class="max-w-2xl text-sm leading-6 text-gray-600 sm:text-base">
                                    {{ __('Browse only the budgets that have been explicitly published by authorized users. Drafts and private records remain hidden from this public page.') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Visible Budgets') }}</p>
                                <p class="mt-3 text-3xl font-semibold text-gray-900">{{ $budgets->total() }}</p>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Access') }}</p>
                                <p class="mt-3 text-sm leading-6 text-gray-600">{{ __('No login required to review public summaries and detail pages.') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-10 space-y-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Home') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-900">{{ __('Latest published budgets') }}</h3>
                        </div>

                        <p class="text-sm text-gray-500">
                            {{ __('Showing only public records available for general consultation.') }}
                        </p>
                    </div>

                    @if ($budgets->count() > 0)
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($budgets as $budget)
                                <article class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Budget') }}</p>
                                            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $budget->title }}</p>
                                        </div>

                                        <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-green-700">
                                            {{ __('Public') }}
                                        </span>
                                    </div>

                                    <div class="mt-4 grid gap-3 text-sm text-gray-600">
                                        <div>
                                            <p class="font-medium text-gray-500">{{ __('Code') }}</p>
                                            <p class="mt-1 text-gray-900">{{ $budget->code }}</p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="font-medium text-gray-500">{{ __('Date') }}</p>
                                                <p class="mt-1 text-gray-900">{{ $budget->budget_date?->format('Y-m-d') ?? '-' }}</p>
                                            </div>

                                            <div>
                                                <p class="font-medium text-gray-500">{{ __('Items') }}</p>
                                                <p class="mt-1 text-gray-900">{{ $budget->budget_items_count }}</p>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="font-medium text-gray-500">{{ __('Description') }}</p>
                                            <p class="mt-1 leading-6 text-gray-700">
                                                {{ $budget->description ? \Illuminate\Support\Str::limit($budget->description, 140) : __('No public description available.') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex items-center justify-between gap-4 border-t border-gray-100 pt-5">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Total') }}</p>
                                            <p class="mt-2 text-xl font-semibold text-gray-900">{{ number_format((float) $budget->total_cost, 2) }}</p>
                                        </div>

                                        <a
                                            href="{{ route('budgets.public.show', $budget) }}"
                                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                                        >
                                            {{ __('View Detail') }}
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if ($budgets->hasPages())
                            <div class="rounded-2xl border border-gray-200 bg-white px-6 py-4 shadow-sm">
                                {{ $budgets->links() }}
                            </div>
                        @endif
                    @else
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center shadow-sm">
                            <p class="text-lg font-semibold text-gray-900">{{ __('There are no published budgets yet.') }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Only budgets marked as published by authorized users appear on this page.') }}</p>
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </body>
</html>
