<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\AuthController;
use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\AccountProfileController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\OrderSuccessController;
use App\Http\Controllers\Shop\NotificationController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\AdminUserController;

/*
|--------------------------------------------------------------------------
| Shop Homepage
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('shop.home');

Route::get('/shop', [HomeController::class, 'index'])
    ->name('shop.index');

/*
|--------------------------------------------------------------------------
| Product Details
|--------------------------------------------------------------------------
*/

Route::get('/shop/products/{slug}', [ProductController::class, 'show'])
    ->name('shop.products.show');

/*
|--------------------------------------------------------------------------
| Shopping Cart
|--------------------------------------------------------------------------
*/

Route::get('/cart', [CartController::class, 'index'])
    ->name('shop.cart.index');

Route::post('/cart/add/{productId}', [CartController::class, 'add'])
    ->name('shop.cart.add');

Route::patch('/cart/update/{itemId}', [CartController::class, 'update'])
    ->name('shop.cart.update');

Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])
    ->name('shop.cart.remove');

/*
|--------------------------------------------------------------------------
| Customer Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/shop/register', [AuthController::class, 'showRegister'])
        ->name('shop.register');

    Route::post('/shop/register', [AuthController::class, 'register'])
        ->name('shop.register.submit');

    Route::get('/shop/login', [AuthController::class, 'showLogin'])
        ->name('shop.login');

    Route::get('/login', function () {
        return redirect()->route('shop.login');
    })->name('login');

    Route::post('/shop/login', [AuthController::class, 'login'])
        ->name('shop.login.submit');

    
    // Forgot Password
    Route::get('/shop/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('shop.password.request');

    Route::post('/shop/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('shop.password.email');

    // Reset Password
    Route::get('/shop/reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('shop.password.reset');

    Route::post('/shop/reset-password/{token}', [AuthController::class, 'resetPassword'])
        ->name('shop.password.update');
    
});

/*
|--------------------------------------------------------------------------
| Customer Account, Notifications, Orders and Checkout
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/shop/account', [AccountController::class, 'index'])
        ->name('shop.account');

    Route::get('/shop/account/edit', [AccountProfileController::class, 'edit'])
        ->name('shop.account.edit');

    Route::put('/shop/account', [AccountProfileController::class, 'update'])
        ->name('shop.account.update');

    /*
    |--------------------------------------------------------------------------
    | Customer Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/shop/notifications', [NotificationController::class, 'index'])
        ->name('shop.notifications.index');

    Route::post('/shop/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->whereNumber('id')
        ->name('shop.notifications.read');

    Route::post('/shop/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('shop.notifications.read-all');

    /*
    |--------------------------------------------------------------------------
    | Customer Order History
    |--------------------------------------------------------------------------
    */

    Route::get('/shop/account/orders', [OrderController::class, 'index'])
        ->name('shop.account.orders');

    Route::get('/shop/account/orders/{id}', [OrderController::class, 'show'])
        ->whereNumber('id')
        ->name('shop.account.orders.show');

    Route::post(
        '/shop/account/orders/{id}/proof-of-payment',
        [OrderController::class, 'uploadProof']
    )->whereNumber('id')
        ->name('shop.account.orders.proof.upload');

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    Route::get('/checkout', [CheckoutController::class, 'index'])
        ->name('shop.checkout.index');

    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('shop.checkout.store');

    /*
    |--------------------------------------------------------------------------
    | Order Confirmation
    |--------------------------------------------------------------------------
    */

    Route::get('/checkout/success', [OrderSuccessController::class, 'show'])
        ->name('shop.checkout.success');

    /*
    |--------------------------------------------------------------------------
    | Customer Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/shop/logout', [AuthController::class, 'logout'])
        ->name('shop.logout');
});


/*
|--------------------------------------------------------------------------
| Betachem Admin Portal
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('admin.permission:dashboard')
            ->name('admin.dashboard');

        // Product Management
        Route::middleware('admin.permission:products')
            ->prefix('products')
            ->name('admin.products.')
            ->group(function () {

                Route::get('/', [AdminProductController::class, 'index'])
                    ->name('index');

                Route::get('/create', [AdminProductController::class, 'create'])
                    ->name('create');

                Route::post('/', [AdminProductController::class, 'store'])
                    ->name('store');

                Route::get('/{product}/edit', [AdminProductController::class, 'edit'])
                    ->whereNumber('product')
                    ->name('edit');

                Route::put('/{product}', [AdminProductController::class, 'update'])
                    ->whereNumber('product')
                    ->name('update');

                Route::patch('/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])
                    ->whereNumber('product')
                    ->name('toggle-status');
            });

                    // Category Management
        Route::middleware('admin.permission:categories')
            ->prefix('categories')
            ->name('admin.categories.')
            ->group(function () {

                Route::get('/', [AdminCategoryController::class, 'index'])
                    ->name('index');

                Route::get('/create', [AdminCategoryController::class, 'create'])
                    ->name('create');

                Route::post('/', [AdminCategoryController::class, 'store'])
                    ->name('store');

                Route::get('/{category}/edit', [AdminCategoryController::class, 'edit'])
                    ->whereNumber('category')
                    ->name('edit');

                Route::put('/{category}', [AdminCategoryController::class, 'update'])
                    ->whereNumber('category')
                    ->name('update');

                Route::patch('/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])
                    ->whereNumber('category')
                    ->name('toggle-status');
            });

                    // Order Management
        Route::middleware('admin.permission:orders')
            ->prefix('orders')
            ->name('admin.orders.')
            ->group(function () {

                Route::get('/', [AdminOrderController::class, 'index'])
                    ->name('index');

                Route::get('/{order}', [AdminOrderController::class, 'show'])
                    ->whereNumber('order')
                    ->name('show');

                Route::patch('/{order}/status', [AdminOrderController::class, 'updateStatus'])
                    ->whereNumber('order')
                    ->name('status.update');

                Route::patch('/{order}/payment', [AdminOrderController::class, 'updatePaymentStatus'])
                    ->whereNumber('order')
                    ->name('payment.update');

                Route::get('/{order}/proof', [AdminOrderController::class, 'downloadProof'])
                    ->whereNumber('order')
                    ->name('proof.download');
            });

                    // Customer Management
        Route::middleware('admin.permission:customers')
            ->prefix('customers')
            ->name('admin.customers.')
            ->group(function () {

                Route::get('/', [AdminCustomerController::class, 'index'])
                    ->name('index');

                Route::get('/{customer}', [AdminCustomerController::class, 'show'])
                    ->whereNumber('customer')
                    ->name('show');

                Route::patch('/{customer}/status', [AdminCustomerController::class, 'updateStatus'])
                    ->whereNumber('customer')
                    ->name('status.update');
            });

                    // Contact Messages Management
        Route::middleware('admin.permission:contact_messages')
            ->prefix('contact-messages')
            ->name('admin.messages.')
            ->group(function () {

                Route::get('/', [AdminContactMessageController::class, 'index'])
                    ->name('index');

                Route::get('/{message}', [AdminContactMessageController::class, 'show'])
                    ->whereNumber('message')
                    ->name('show');

                Route::patch('/{message}', [AdminContactMessageController::class, 'update'])
                    ->whereNumber('message')
                    ->name('update');
            });

                    // Staff and Administrator User Management
        Route::middleware('admin.permission:admin_users')
            ->prefix('users')
            ->name('admin.users.')
            ->group(function () {

                Route::get('/', [AdminUserController::class, 'index'])
                    ->name('index');

                Route::get('/create', [AdminUserController::class, 'create'])
                    ->name('create');

                Route::post('/', [AdminUserController::class, 'store'])
                    ->name('store');

                Route::get('/{user}/edit', [AdminUserController::class, 'edit'])
                    ->whereNumber('user')
                    ->name('edit');

                Route::put('/{user}', [AdminUserController::class, 'update'])
                    ->whereNumber('user')
                    ->name('update');
            });
    });
