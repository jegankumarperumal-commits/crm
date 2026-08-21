<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-meetings'])->group(function () {
    Route::livewire('meetings', 'pages::meetings.index')->name('meetings.index');
});
