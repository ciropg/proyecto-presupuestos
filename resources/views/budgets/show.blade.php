<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budget Details') }}
            </h2>

            <div class="flex gap-3">
                @can('update', $budget)
                    <a
                        href="{{ route('budgets.items.create', $budget) }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        {{ __('Add Item') }}
                    </a>

                    <a
                        href="{{ route('budgets.edit', $budget) }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        {{ __('Edit Budget') }}
                    </a>
                @endcan

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
                                {{ __('Add Item') }}
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Resource') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Category') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Quantity') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit Price') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Subtotal') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($budget->budgetItems as $budgetItem)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ $budgetItem->resource?->name ?? $budgetItem->name }}</div>
                                        <div class="mt-1 text-gray-500">{{ $budgetItem->description ?: '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $budgetItem->resource?->category?->name ?? __('Manual item') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $budgetItem->unit->name }} ({{ $budgetItem->unit->symbol }})</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format((float) $budgetItem->quantity, 4) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format((float) $budgetItem->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format((float) $budgetItem->subtotal, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            @can('update', $budget)
                                                <a
                                                    href="{{ route('budgets.items.edit', [$budget, $budgetItem]) }}"
                                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                                >
                                                    {{ __('Edit') }}
                                                </a>

                                                <form method="POST" action="{{ route('budgets.items.destroy', [$budget, $budgetItem]) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500"
                                                        onclick="return confirm('{{ __('Delete this item?') }}')"
                                                    >
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                        {{ __('No items have been added to this budget yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-right text-sm font-semibold uppercase tracking-wider text-gray-500">
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
