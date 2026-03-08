<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Resources') }}
            </h2>

            <a
                href="{{ route('admin.resources.create') }}"
                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
            >
                {{ __('New Resource') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.flash-messages')

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Category') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit Price') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($resources as $resource)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <div>{{ $resource->name }}</div>
                                        @if ($resource->description)
                                            <div class="mt-1 text-sm text-gray-500">{{ $resource->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $resource->category->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $resource->unit->name }} ({{ $resource->unit->symbol }})</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format((float) $resource->unit_price, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('admin.resources.edit', $resource) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                            >
                                                {{ __('Edit') }}
                                            </a>

                                            <form method="POST" action="{{ route('admin.resources.destroy', $resource) }}">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500"
                                                    onclick="return confirm('{{ __('Delete this resource?') }}')"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                        {{ __('No resources found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $resources->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
