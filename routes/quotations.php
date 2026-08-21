<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-quotations'])->group(function () {
    Route::livewire('quotations', 'pages::quotations.index')->name('quotations.index');
});
