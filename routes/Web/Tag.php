<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
 
$prefix = 'tags';

Route::middleware('auth')->group(function () use ($prefix) {
	Route::prefix($prefix)->group(function () use ($prefix) {
        Route::get('/', [TagController::class, 'index'])->name('tags.index');
        Route::post('/store', [TagController::class, 'store'])->name('tags.store');
        Route::get('/create', [TagController::class, 'create'])->name('tags.create');
        Route::get('/{slug}', [TagController::class, 'show'])->name('tags.show');
        Route::post('/update/{id}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/destroy/{id}', [TagController::class, 'destroy'])->name('tags.destroy');
        Route::get('/edit/{id}', [TagController::class, 'edit'])->name('tags.edit');
    });
});
