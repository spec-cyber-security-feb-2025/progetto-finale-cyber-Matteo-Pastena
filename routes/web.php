<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WriterController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\RevisorController;

// Public routes
Route::get('/', [PublicController::class, 'homepage'])->name('homepage');
Route::get('/careers', [PublicController::class, 'careers'])->name('careers');
Route::post('/careers/submit', [PublicController::class, 'careersSubmit'])->name('careers.submit');

// Article routes
Route::prefix('articles')->group(function () {
    Route::get('index', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('show/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('category/{category}', [ArticleController::class, 'byCategory'])->name('articles.byCategory');
    Route::get('user/{user}', [ArticleController::class, 'byUser'])->name('articles.byUser');
    Route::get('article/search', [ArticleController::class, 'articleSearch'])->middleware('throttle:article-search')->name('articles.search');  // Apply rate limiting here
});

// Writer routes
Route::middleware('writer')->group(function () {
    Route::prefix('writer')->group(function () {
        Route::get('dashboard', [WriterController::class, 'dashboard'])->name('writer.dashboard');
        Route::get('articles/create', [ArticleController::class, 'create'])->name('articles.create');
        Route::post('articles/store', [ArticleController::class, 'store'])->name('articles.store');
        Route::get('articles/edit/{article}', [ArticleController::class, 'edit'])->name('articles.edit');
        Route::put('articles/update/{article}', [ArticleController::class, 'update'])->name('articles.update');
        Route::delete('articles/destroy/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    });
});

// Revisor routes
Route::middleware('revisor')->group(function () {
    Route::prefix('revisor')->group(function () {
        Route::get('dashboard', [RevisorController::class, 'dashboard'])->name('revisor.dashboard');
        Route::post('{article}/accept', [RevisorController::class, 'acceptArticle'])->name('revisor.acceptArticle');
        Route::post('{article}/reject', [RevisorController::class, 'rejectArticle'])->name('revisor.rejectArticle');
        Route::post('{article}/undo', [RevisorController::class, 'undoArticle'])->name('revisor.undoArticle');
    });
});

// Admin routes
Route::middleware(['admin', 'admin.local'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Includiamo il controllo Referer e Origin per le rotte critiche
        Route::post('{user}/set-admin', [AdminController::class, 'setAdmin'])
            ->middleware('auth', 'crsf', 'check.referer.origin') // Aggiungi il middleware personalizzato per referer/origin
            ->name('admin.setAdmin');

        Route::post('{user}/set-revisor', [AdminController::class, 'setRevisor'])
            ->middleware('auth', 'crsf', 'check.referer.origin') // Aggiungi il middleware personalizzato per referer/origin
            ->name('admin.setRevisor');

        Route::post('{user}/set-writer', [AdminController::class, 'setWriter'])
            ->middleware('auth', 'crsf', 'check.referer.origin') // Aggiungi il middleware personalizzato per referer/origin
            ->name('admin.setWriter');

        Route::put('edit/tag/{tag}', [AdminController::class, 'editTag'])->name('admin.editTag');
        Route::delete('delete/tag/{tag}', [AdminController::class, 'deleteTag'])->name('admin.deleteTag');
        Route::put('edit/category/{category}', [AdminController::class, 'editCategory'])->name('admin.editCategory');
        Route::delete('delete/category/{category}', [AdminController::class, 'deleteCategory'])->name('admin.deleteCategory');
        Route::post('category/store', [AdminController::class, 'storeCategory'])->name('admin.storeCategory');
        Route::post('tag/store', [AdminController::class, 'storeTag'])->name('admin.storeTag');
    });
});
