<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Categories') }}
            </h2>

            <a
                href="{{ route('admin.categories.create') }}"
                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
            >
                {{ __('New Category') }}
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
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Description') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->description ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a
                                                href="{{ route('admin.categories.edit', $category) }}"
                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                            >
                                                {{ __('Edit') }}
                                            </a>

                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-500"
                                                    onclick="return confirm('{{ __('Delete this category?') }}')"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                                        {{ __('No categories found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
