<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Budget Details') }}
            </h2>

            <div class="flex gap-3">
                <a
                    href="{{ route('budgets.edit', $budget) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                >
                    {{ __('Edit Budget') }}
                </a>

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
                <div class="p-6 text-gray-900 space-y-2">
                    <p class="text-lg font-semibold">{{ __('Next Step: Cost Breakdown') }}</p>
                    <p class="text-sm text-gray-600">
                        {{ __('Budget items and cost breakdown will be implemented in step 8. This budget is ready to receive those records later.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
