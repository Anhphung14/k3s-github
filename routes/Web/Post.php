<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

$prefix = 'posts';
Route::middleware('auth')->group(function () use ($prefix) {
    Route::prefix($prefix)->group(function () use ($prefix) {
        Route::get('/', [PostController::class, 'index'])->name('posts.index');
        Route::post('/store', [PostController::class, 'store'])->name('posts.store');
        Route::get('/create', [PostController::class, 'create'])->name('posts.create');
        Route::get('/{slug}', [PostController::class, 'show'])->name('posts.show');
        Route::post('/update/{id}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/destroy/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::get('/edit/{id}', [PostController::class, 'edit'])->name('posts.edit');
    });
});
