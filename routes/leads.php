<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-leads'])->group(function () {
    Route::livewire('leads', 'pages::leads.index')->name('leads.index');
});
