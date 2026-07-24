<?php

use App\Http\Controllers\JerseyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('landing-page');

Route::middleware(['auth', 'client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/home', function () {
        return Inertia::render('Client/Home');
    })->name('home');

    Route::prefix('design')->name('design.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Client/Design');
        })->name('index');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Client/Orders');
        })->name('index');
    });

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Client/Chat');
        })->name('index');
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

    Route::middleware(['throttle:api'])->group(function () {
        Route::resource('jersey', JerseyController::class)->only(['store', 'update', 'destroy']);
    });

    Route::get('/design', function () {
        return Inertia::render('Admin/Design');
    })->name('design');

    Route::get('/orders', function () {
        return Inertia::render('Admin/Orders');
    })->name('orders');

    Route::get('/sales', function () {
        return Inertia::render('Admin/Sales');
    })->name('sales');

    Route::get('/couriers', function () {
        return Inertia::render('Admin/Couriers');
    })->name('couriers');

    Route::get('/shipping', function () {
        return Inertia::render('Admin/Shipping');
    })->name('shipping');

    Route::get('/gcash', function () {
        return Inertia::render('Admin/Gcash');
    })->name('gcash');

    Route::get('/messages', function () {
        return Inertia::render('Admin/Messages');
    })->name('messages');

    Route::get('/users', function () {
        return Inertia::render('Admin/Users');
    })->name('users');

    Route::get('profile', function () {
        return Inertia::render('Admin/Profile');
    })->name('profile');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
