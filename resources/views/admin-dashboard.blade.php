<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <p class="text-lg font-semibold">{{ __('Administrator access granted') }}</p>
                    <p>{{ __('This area is protected by the role middleware and is reserved for admin users.') }}</p>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('admin.categories.index') }}"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                        >
                            {{ __('Manage Categories') }}
                        </a>

                        <a
                            href="{{ route('admin.units.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                        >
                            {{ __('Manage Units') }}
                        </a>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                        <p><span class="font-medium">{{ __('User') }}:</span> {{ auth()->user()->name }}</p>
                        <p><span class="font-medium">{{ __('Email') }}:</span> {{ auth()->user()->email }}</p>
                        <p><span class="font-medium">{{ __('Role') }}:</span> {{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
