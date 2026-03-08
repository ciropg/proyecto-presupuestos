<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Budget') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @include('partials.flash-messages')

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('budgets.update', $budget) }}">
                    @method('PUT')
                    @include('budgets.partials.form', ['submitLabel' => __('Update Budget')])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
