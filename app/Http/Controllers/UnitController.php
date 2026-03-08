<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::query()
            ->latest()
            ->paginate(10);

        return view('units.index', compact('units'));
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        try {
            $unit->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', 'Unit could not be deleted because it is in use.');
        }

        return redirect()
            ->route('admin.units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}
