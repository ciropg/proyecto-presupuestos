<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $budget->title }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-gray-100">
            <header class="border-b border-gray-200 bg-white/90 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold uppercase tracking-[0.3em] text-gray-600">
                        {{ __('Published Budgets') }}
                    </a>

                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                        >
                            {{ __('Back to Home') }}
                        </a>

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
                                    class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                                >
                                    {{ __('Log In') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
                <section class="overflow-hidden rounded-3xl bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-slate-900 px-6 py-8 text-white sm:px-10">
                        <div class="flex flex-wrap items-start justify-between gap-6">
                            <div class="space-y-3">
                                <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-200">
                                    {{ __('Public Budget Detail') }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-slate-300">{{ __('Code') }}</p>
                                    <h1 class="mt-2 text-3xl font-semibold leading-tight">{{ $budget->code }} - {{ $budget->title }}</h1>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">{{ __('Total') }}</p>
                                <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((float) $budget->total_cost, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8 px-6 py-8 sm:px-10">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Date') }}</p>
                                <p class="mt-3 text-lg font-semibold text-gray-900">{{ $budget->budget_date?->format('Y-m-d') ?? '-' }}</p>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Status') }}</p>
                                <p class="mt-3 text-lg font-semibold text-gray-900">{{ \App\Models\Budget::statusOptions()[$budget->status] ?? ucfirst($budget->status) }}</p>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Items') }}</p>
                                <p class="mt-3 text-lg font-semibold text-gray-900">{{ $budget->budget_items_count }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Public Description') }}</p>
                            <p class="mt-4 text-sm leading-7 text-gray-700">
                                {{ $budget->description ?: __('No public description available for this budget.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 p-6">
                            <div class="flex flex-col gap-2 border-b border-gray-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-500">{{ __('Associated Items') }}</p>
                                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ __('Budget breakdown') }}</p>
                                </div>

                                <p class="text-sm text-gray-500">
                                    {{ __('All items currently associated with this published budget.') }}
                                </p>
                            </div>

                            @if ($budget->budgetItems->isNotEmpty())
                                <div class="mt-6 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Item') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Category') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Quantity') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit Price') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Subtotal') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach ($budget->budgetItems as $budgetItem)
                                                <tr>
                                                    <td class="px-4 py-4 text-sm text-gray-900">
                                                        <div class="font-medium">{{ $budgetItem->resource?->name ?? $budgetItem->name }}</div>
                                                        <div class="mt-1 text-gray-500">{{ $budgetItem->description ?: '-' }}</div>
                                                    </td>
                                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $budgetItem->resource?->category?->name ?? __('Manual item') }}</td>
                                                    <td class="px-4 py-4 text-sm text-gray-600">{{ $budgetItem->unit->name }} ({{ $budgetItem->unit->symbol }})</td>
                                                    <td class="px-4 py-4 text-sm text-gray-600">{{ number_format((float) $budgetItem->quantity, 4) }}</td>
                                                    <td class="px-4 py-4 text-sm text-gray-600">{{ number_format((float) $budgetItem->unit_price, 2) }}</td>
                                                    <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ number_format((float) $budgetItem->subtotal, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="5" class="px-4 py-4 text-right text-sm font-semibold uppercase tracking-wider text-gray-500">
                                                    {{ __('Total General') }}
                                                </td>
                                                <td class="px-4 py-4 text-sm font-semibold text-gray-900">
                                                    {{ number_format((float) $budget->total_cost, 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center text-sm text-gray-600">
                                    {{ __('This published budget does not have associated items yet.') }}
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6 text-sm text-blue-900">
                            {{ __('This page only exposes the public summary of the budget. Administrative actions and private management tools remain protected inside the authenticated module.') }}
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
