<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Models\Category;
use App\Models\Resource;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(): View
    {
        $resources = Resource::query()
            ->with(['category', 'unit'])
            ->latest()
            ->paginate(10);

        return view('resources.index', compact('resources'));
    }

    public function create(): View
    {
        return view('resources.create', $this->getFormData());
    }

    public function store(StoreResourceRequest $request): RedirectResponse
    {
        Resource::create($request->validated());

        return redirect()
            ->route('admin.resources.index')
            ->with('success', 'Resource created successfully.');
    }

    public function edit(Resource $resource): View
    {
        return view('resources.edit', [
            'resource' => $resource,
            ...$this->getFormData(),
        ]);
    }

    public function update(UpdateResourceRequest $request, Resource $resource): RedirectResponse
    {
        $resource->update($request->validated());

        return redirect()
            ->route('admin.resources.index')
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy(Resource $resource): RedirectResponse
    {
        try {
            $resource->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.resources.index')
                ->with('error', 'Resource could not be deleted because it is in use.');
        }

        return redirect()
            ->route('admin.resources.index')
            ->with('success', 'Resource deleted successfully.');
    }

    /**
     * @return array{categories: \Illuminate\Database\Eloquent\Collection<int, Category>, units: \Illuminate\Database\Eloquent\Collection<int, Unit>}
     */
    private function getFormData(): array
    {
        return [
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'units' => Unit::query()
                ->orderBy('name')
                ->get(['id', 'name', 'symbol']),
        ];
    }
}
