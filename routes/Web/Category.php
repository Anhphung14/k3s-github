<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

$prefix = 'categories';
Route::middleware('auth')->group(function () use ($prefix) {
    Route::prefix($prefix)->group(function () use ($prefix) {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/{id}', [CategoryController::class, 'show'])->name('categories.show');
        Route::post('/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/destroy/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('categories.edit');
    });
});
