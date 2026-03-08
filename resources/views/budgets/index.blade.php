<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budgets') }}
            </h2>

            <a
                href="{{ route('budgets.create') }}"
                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
            >
                {{ __('New Budget') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.flash-messages')

            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold uppercase tracking-wider text-gray-500">{{ __('How to add items') }}</p>
                        <p class="text-sm text-gray-700">
                            {{ __('Create a budget, then use the new Add Item action in the table to load resources from the catalog or register manual items.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('budgets.create') }}"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                        >
                            {{ __('Create Budget') }}
                        </a>

                        @if ($budgets->count() > 0)
                            <a
                                href="{{ route('budgets.show', $budgets->first()) }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                            >
                                {{ __('Open Latest Budget') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Code') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Title') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Visibility') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Owner') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Total') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($budgets as $budget)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $budget->code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $budget->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $budget->budget_date?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $statuses[$budget->status] ?? ucfirst($budget->status) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="{{ $budget->isPubliclyVisible() ? 'border-green-200 bg-green-50 text-green-700' : 'border-gray-200 bg-gray-100 text-gray-700' }} inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                                            {{ $budget->isPubliclyVisible() ? __('Published') : __('Private') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $budget->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format((float) $budget->total_cost, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a
                                                href="{{ route('budgets.show', $budget) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                            >
                                                {{ __('View') }}
                                            </a>

                                            @if ($budget->isPubliclyVisible())
                                                <a
                                                    href="{{ route('budgets.public.show', $budget) }}"
                                                    class="inline-flex items-center rounded-md border border-green-300 bg-green-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-green-700 transition hover:bg-green-100"
                                                >
                                                    {{ __('Public View') }}
                                                </a>
                                            @endif

                                            @can('publish', $budget)
                                                <form method="POST" action="{{ route('budgets.publication.update', $budget) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="published" value="{{ $budget->isPubliclyVisible() ? 0 : 1 }}">

                                                    <button
                                                        type="submit"
                                                        class="{{ $budget->isPubliclyVisible() ? 'bg-amber-600 hover:bg-amber-500' : 'bg-green-600 hover:bg-green-500' }} inline-flex items-center rounded-md px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition"
                                                    >
                                                        {{ $budget->isPubliclyVisible() ? __('Unpublish') : __('Publish') }}
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('update', $budget)
                                                <a
                                                    href="{{ route('budgets.items.create', $budget) }}"
                                                    class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                                                >
                                                    {{ __('Add Item') }}
                                                </a>

                                                <a
                                                    href="{{ route('budgets.edit', $budget) }}"
                                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                                >
                                                    {{ __('Edit') }}
                                                </a>

                                                <form method="POST" action="{{ route('budgets.destroy', $budget) }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500"
                                                        onclick="return confirm('{{ __('Delete this budget?') }}')"
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
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                                        <div class="space-y-3">
                                            <p>{{ __('No budgets found.') }}</p>
                                            <a
                                                href="{{ route('budgets.create') }}"
                                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                                            >
                                                {{ __('Create your first budget') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $budgets->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
