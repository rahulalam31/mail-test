<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\DomainChecker;
use App\Http\Controllers\DomainCheckExportController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get(
    '/',
    DomainChecker::class
)->name('domain-checker');


Route::get(
    '/domain-checks/{bulkCheckId}/export',
    DomainCheckExportController::class
)->name('domain-checks.export');
