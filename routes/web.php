<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BudgetController::class, 'publicIndex'])->name('home');
Route::get('/budgets/public/{budget}', [BudgetController::class, 'publicShow'])->name('budgets.public.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::patch('budgets/{budget}/publication', [BudgetController::class, 'updatePublication'])->name('budgets.publication.update');
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{budget}/items/create', [BudgetItemController::class, 'create'])->name('budgets.items.create');
    Route::post('budgets/{budget}/items', [BudgetItemController::class, 'store'])->name('budgets.items.store');
    Route::get('budgets/{budget}/items/{budgetItem}/edit', [BudgetItemController::class, 'edit'])->name('budgets.items.edit');
    Route::put('budgets/{budget}/items/{budgetItem}', [BudgetItemController::class, 'update'])->name('budgets.items.update');
    Route::delete('budgets/{budget}/items/{budgetItem}', [BudgetItemController::class, 'destroy'])->name('budgets.items.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin-dashboard')->name('dashboard');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('resources', ResourceController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
});

require __DIR__.'/auth.php';
