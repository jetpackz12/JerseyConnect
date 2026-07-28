<?php

use App\Http\Controllers\AdminDesignRequestController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\DesignRequestController;
use App\Http\Controllers\GcashSettingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JerseyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminMessageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing-page');

Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::resource('home', HomeController::class)->only(['index']);
    Route::resource('design', DesignRequestController::class)->only(['index']);
    Route::resource('orders', OrderController::class)->only(['index']);
    Route::resource('chat', ChatController::class)->only(['index']);

    Route::middleware(['throttle:api'])->group(function () {
        Route::resource('home', HomeController::class)->only(['store']);

        // Client Design Requests
        Route::post('/design/{designRequest}/pay', [DesignRequestController::class, 'pay'])->name('design.pay');
        Route::delete('/design/{designRequest}/cancel', [DesignRequestController::class, 'cancel'])->name('design.cancel');

        //Client Orders
        Route::patch('/orders/{order}/address', [OrderController::class, 'updateAddress'])->name('orders.update-address');

        // Client Chat
        Route::post('/chat/{thread}/reply', [ChatController::class, 'reply'])->name('chat.reply');
        Route::patch('/chat/{thread}/read', [ChatController::class, 'markRead'])->name('chat.mark-read');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    Route::resource('jersey', JerseyController::class)->only(['index']);
    Route::resource('design', AdminDesignRequestController::class)->only(['index']);
    Route::resource('orders', AdminOrderController::class)->only(['index']);
    Route::resource('couriers', CourierController::class)->only(['index']);
    Route::resource('gcash', GcashSettingController::class)->only(['index']);
    Route::resource('messages', AdminMessageController::class)->only(['index']);

    Route::middleware(['throttle:api'])->group(function () {
        Route::resource('jersey', JerseyController::class)->only(['store', 'update', 'destroy']);

        // Admin Design Requests
        Route::put('/design/{designRequest}', [AdminDesignRequestController::class, 'update'])->name('design.update');
        Route::delete('/design/{designRequest}', [AdminDesignRequestController::class, 'destroy'])->name('design.cancel');
        Route::post('/design/{designRequest}/approve-payment', [AdminDesignRequestController::class, 'approvePayment'])->name('design.approve-payment');
        Route::post('/design/{designRequest}/reject-payment', [AdminDesignRequestController::class, 'rejectPayment'])->name('design.reject-payment');

        // Admin Orders
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

        // Admin Couriers
        Route::resource('couriers', CourierController::class)->only(['store', 'update', 'destroy']);

        // Admin Gcash
        Route::put('/gcash/details', [GcashSettingController::class, 'updateDetails'])->name('gcash.details-update');
        Route::post('/gcash/qr', [GcashSettingController::class, 'updateQr'])->name('gcash.qr-update');

        // Admin Messages
        Route::post('/messages/{thread}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');
        Route::patch('/messages/{thread}/read', [AdminMessageController::class, 'markRead'])->name('messages.mark-read');
    });

    Route::get('/sales', function () {
        return Inertia::render('Admin/Sales');
    })->name('sales');

    Route::get('/shipping', function () {
        return Inertia::render('Admin/Shipping');
    })->name('shipping');

    Route::get('/users', function () {
        return Inertia::render('Admin/Users');
    })->name('users');

    Route::get('profile', function () {
        return Inertia::render('Admin/Profile');
    })->name('profile');
});

require __DIR__ . '/auth.php';
