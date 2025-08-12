<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Login routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Dashboard route (protected by auth)
Route::get('/dashboard', function () {
    return 'Welcome to Dashboard! <form method="POST" action="/logout">' . csrf_field() . '<button type="submit">Logout</button></form>';
})->middleware('auth')->name('dashboard');

// Redirect root to login
Route::redirect('/', '/login');
