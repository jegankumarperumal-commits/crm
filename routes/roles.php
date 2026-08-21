<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-team'])->group(function () {
    Route::livewire('roles', 'pages::roles.index')->name('roles.index');
});
