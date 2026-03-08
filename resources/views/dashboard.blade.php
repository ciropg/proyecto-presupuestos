<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <p class="text-lg font-semibold">{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</p>
                    <p>{{ __('Your session is active and your account can access protected routes.') }}</p>

                    @if (auth()->user()->isAdmin())
                        <p class="text-sm text-indigo-700">
                            {{ __('Your account also has administrator privileges.') }}
                        </p>
                    @endif

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                        <p><span class="font-medium">{{ __('Email') }}:</span> {{ auth()->user()->email }}</p>
                        <p><span class="font-medium">{{ __('Role') }}:</span> {{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
