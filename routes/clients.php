<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-clients-invoices'])->group(function () {
    Route::livewire('clients', 'pages::clients.index')->name('clients.index');
});
