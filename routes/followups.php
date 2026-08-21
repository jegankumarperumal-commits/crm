<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-follow-ups'])->group(function () {
    Route::livewire('follow-ups', 'pages::follow-ups.index')->name('follow-ups.index');
});
