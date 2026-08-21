<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-team'])->group(function () {
    Route::livewire('team', 'pages::team.index')->name('team.index');
});
