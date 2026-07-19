<?php

use App\Http\Controllers\Admin\ModeleParcoursController;
use App\Http\Controllers\Formateur\CatalogueModelesParcoursController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin', 'admin.activity'])
    ->prefix('admin/modeles-parcours')
    ->name('admin.modeles-parcours.')
    ->group(function (): void {
        Route::get('/', [ModeleParcoursController::class, 'index'])->name('index');
        Route::get('/creer', [ModeleParcoursController::class, 'create'])->name('create');
        Route::post('/', [ModeleParcoursController::class, 'store'])->name('store');
        Route::get('/{modele}/modifier', [ModeleParcoursController::class, 'edit'])->name('edit');
        Route::put('/{modele}', [ModeleParcoursController::class, 'update'])->name('update');
        Route::post('/{modele}/publier', [ModeleParcoursController::class, 'publier'])->name('publier');
        Route::post('/{modele}/archiver', [ModeleParcoursController::class, 'archiver'])->name('archiver');
        Route::post('/{modele}/dupliquer', [ModeleParcoursController::class, 'dupliquerEnBrouillon'])->name('dupliquer');
        Route::delete('/{modele}', [ModeleParcoursController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'role:formateur', 'association.member'])
    ->prefix('formateur/modeles-parcours')
    ->name('formateur.modeles-parcours.')
    ->group(function (): void {
        Route::get('/', [CatalogueModelesParcoursController::class, 'index'])->name('index');
        Route::post('/{modele}/dupliquer', [CatalogueModelesParcoursController::class, 'dupliquer'])->name('dupliquer');
    });
