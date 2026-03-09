<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budget Details') }}
            </h2>

            <div class="flex flex-wrap gap-3">
                @can('update', $budget)
                    <a
                        href="{{ route('budgets.items.create', $budget) }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        {{ __('Add Root Item') }}
                    </a>

                    <a
                        href="{{ route('budgets.edit', $budget) }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        {{ __('Edit Budget') }}
                    </a>
                @endcan

                @if ($budget->isPubliclyVisible())
                    <a
                        href="{{ route('budgets.public.show', $budget) }}"
                        class="inline-flex items-center rounded-md border border-green-300 bg-green-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-green-700 transition hover:bg-green-100"
                    >
                        {{ __('Public View') }}
                    </a>
                @endif

                <a
                    href="{{ route('budgets.index') }}"
                    class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                >
                    {{ __('Back to Budgets') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.flash-messages')
            <style>[x-cloak]{display:none!important;}</style>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4 text-gray-900">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Code') }}</p>
                            <p class="mt-1">{{ $budget->code }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Status') }}</p>
                            <p class="mt-1">{{ \App\Models\Budget::statusOptions()[$budget->status] ?? ucfirst($budget->status) }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Visibility') }}</p>
                            <p class="mt-1">{{ $budget->isPubliclyVisible() ? __('Published on home page') : __('Hidden from public home') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Title') }}</p>
                            <p class="mt-1">{{ $budget->title }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Date') }}</p>
                            <p class="mt-1">{{ $budget->budget_date?->format('Y-m-d') ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Owner') }}</p>
                            <p class="mt-1">{{ $budget->user->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Total Cost') }}</p>
                            <p class="mt-1">{{ number_format((float) $budget->total_cost, 2) }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Description') }}</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $budget->description ?: '-' }}</p>
                    </div>
                </div>
            </div>

            @can('publish', $budget)
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex flex-col gap-4 p-6 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-lg font-semibold text-gray-900">{{ __('Public Visibility') }}</p>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $budget->isPubliclyVisible()
                                    ? __('This budget is visible on the public home page and can be consulted without logging in.')
                                    : __('This budget is private. Publish it to show it on the public home page.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('budgets.publication.update', $budget) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="published" value="{{ $budget->isPubliclyVisible() ? 0 : 1 }}">

                                <button
                                    type="submit"
                                    class="{{ $budget->isPubliclyVisible() ? 'bg-amber-600 hover:bg-amber-500' : 'bg-green-600 hover:bg-green-500' }} inline-flex items-center rounded-md px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition"
                                >
                                    {{ $budget->isPubliclyVisible() ? __('Unpublish Budget') : __('Publish Budget') }}
                                </button>
                            </form>

                            @if ($budget->isPubliclyVisible())
                                <a
                                    href="{{ route('budgets.public.show', $budget) }}"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                >
                                    {{ __('Open Public Detail') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endcan

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-lg font-semibold text-gray-900">{{ __('Cost Breakdown') }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ __('Manage the resources included in this budget and keep the total cost updated automatically.') }}</p>
                        </div>

                        @can('update', $budget)
                            <a
                                href="{{ route('budgets.items.create', $budget) }}"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                            >
                                {{ __('Add Root Item') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div
                    x-data="{
                        expanded: {},
                        toggle(id) {
                            this.expanded[id] = !(this.expanded[id] ?? false);
                        },
                    }"
                    class="overflow-x-auto"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('No.') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Item') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Category') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Quantity') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit Price') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Subtotal') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($budget->rootItems as $budgetItem)
                                @include('budgets.partials.hierarchy-row', [
                                    'budget' => $budget,
                                    'budgetItem' => $budgetItem,
                                    'depth' => 0,
                                    'ancestorIds' => [],
                                    'itemNumber' => (string) $loop->iteration,
                                    'showActions' => true,
                                ])
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                                        {{ __('No items have been added to this budget yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-right text-sm font-semibold uppercase tracking-wider text-gray-500">
                                    {{ __('Total General') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                    {{ number_format((float) $budget->total_cost, 2) }}
                                </td>
                                <td class="px-6 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
