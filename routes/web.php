<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Google OAuth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Protected Application Routes (Auth Required)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::match(['GET', 'POST'], '/share-receive', [TransactionController::class, 'receiveShare'])->name('transactions.share-receive');
    Route::post('/transactions/scan-receipt', [TransactionController::class, 'scanReceipt'])->name('transactions.scan-receipt');
    Route::resource('transactions', TransactionController::class)->except(['create', 'show', 'edit']);
    Route::post('/budgets/{budget}/top-up', [BudgetController::class, 'topUp'])->name('budgets.top-up');
    Route::resource('budgets', BudgetController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    // Web Push Notification Routes
    Route::get('/push/vapid-public-key', [PushNotificationController::class, 'vapidPublicKey'])->name('push.key');
    Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushNotificationController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/push/test', [PushNotificationController::class, 'sendTest'])->name('push.test');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
