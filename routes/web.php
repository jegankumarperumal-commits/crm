<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/team.php';
require __DIR__.'/roles.php';
require __DIR__.'/leads.php';
require __DIR__.'/followups.php';
require __DIR__.'/meetings.php';
require __DIR__.'/quotations.php';
require __DIR__.'/clients.php';
require __DIR__.'/reports.php';
