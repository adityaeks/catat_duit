<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Application Routes (Auth Required)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::match(['GET', 'POST'], '/share-receive', [TransactionController::class, 'receiveShare'])->name('transactions.share-receive');
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit']);
    Route::resource('budgets', BudgetController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
