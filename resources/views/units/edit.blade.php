<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Unit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @include('partials.flash-messages')

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.units.update', $unit) }}">
                    @method('PUT')
                    @include('units.partials.form', ['submitLabel' => __('Update Unit')])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
