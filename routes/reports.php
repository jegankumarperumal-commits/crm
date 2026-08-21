<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:view-reports'])->group(function () {
    Route::livewire('reports', 'pages::reports.index')->name('reports.index');
});
