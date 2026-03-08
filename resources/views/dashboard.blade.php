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

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('budgets.index') }}"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                        >
                            {{ __('View Budgets') }}
                        </a>

                        <a
                            href="{{ route('budgets.create') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                        >
                            {{ __('New Budget') }}
                        </a>
                    </div>

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
