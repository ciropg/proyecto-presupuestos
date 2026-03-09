<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $budgetItem->parent_id ? __('Edit Budget Subitem') : __('Edit Budget Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @include('partials.flash-messages')

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('budgets.items.update', [$budget, $budgetItem]) }}">
                    @method('PUT')
                    @include('budgets.partials.item-form', ['submitLabel' => __('Update Item')])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
