<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DeliveryFeeController as AdminDeliveryFeeController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\OperationsController as AdminOperationsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])
            ->middleware('throttle:6,1')
            ->name('login.store');
    });

    Route::middleware(['auth:admin', 'admin.active'])->group(function (): void {
        Route::get('/', [AdminOperationsController::class, 'dashboard'])->name('dashboard');
        Route::get('/menu', [AdminMenuController::class, 'index'])->name('menu.index');
        Route::get('/orders', [AdminOperationsController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{orderId}', [AdminOperationsController::class, 'orderDetails'])->whereNumber('orderId')->name('orders.show');
        Route::put('/orders/{orderId}', [AdminOperationsController::class, 'updateOrder'])->name('orders.update');
        Route::get('/inventory', [AdminOperationsController::class, 'inventory'])->name('inventory.index');
        Route::put('/inventory/{productId}', [AdminOperationsController::class, 'updateInventory'])->name('inventory.update');
        Route::get('/customers', [AdminOperationsController::class, 'customers'])->name('customers.index');
        Route::get('/delivery-fees', [AdminDeliveryFeeController::class, 'index'])->name('delivery-fees.index');
        Route::post('/delivery-fees', [AdminDeliveryFeeController::class, 'store'])->name('delivery-fees.store');
        Route::put('/delivery-fees', [AdminDeliveryFeeController::class, 'update'])->name('delivery-fees.update');
        Route::delete('/delivery-fees/{deliveryFeeId}', [AdminDeliveryFeeController::class, 'destroy'])->whereNumber('deliveryFeeId')->name('delivery-fees.destroy');
        Route::post('/products', [AdminMenuController::class, 'storeProduct'])->name('products.store');
        Route::put('/products/{productId}', [AdminMenuController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{productId}', [AdminMenuController::class, 'deleteProduct'])->name('products.destroy');
        Route::post('/categories', [AdminMenuController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{categoryId}', [AdminMenuController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{categoryId}', [AdminMenuController::class, 'deleteCategory'])->name('categories.destroy');
        Route::post('/sizes', [AdminMenuController::class, 'storeSize'])->name('sizes.store');
        Route::delete('/sizes/{sizeId}', [AdminMenuController::class, 'deleteSize'])->name('sizes.destroy');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/', [MenuController::class, 'home'])->name('home');
Route::get('/products', [MenuController::class, 'index'])->name('products.index');
Route::get('/categories/{categorySlug}', [MenuController::class, 'category'])
    ->where('categorySlug', 'coffee|milk-tea|pastries|pizza')
    ->name('categories.show');
Route::get('/menu/{productId}', [MenuController::class, 'show'])->whereNumber('productId')->name('menu.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::put('/cart/items/{cartItemId}', [CartController::class, 'update'])->where('cartItemId', '(?:[0-9]+|p[0-9]+-s[0-9]+)')->name('cart.items.update');
Route::delete('/cart/items/{cartItemId}', [CartController::class, 'destroy'])->where('cartItemId', '(?:[0-9]+|p[0-9]+-s[0-9]+)')->name('cart.items.destroy');
Route::post('/buy-now/{productId}', [CartController::class, 'buyNow'])->whereNumber('productId')->name('buy-now');
Route::match(['get', 'post', 'put'], '/cart/checkout-selection', [CartController::class, 'selectForCheckout'])->name('cart.checkout.select');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

Route::middleware(['auth', 'customer.active'])->group(function (): void {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::post('/account/orders/{orderId}/cancel', [AccountController::class, 'cancelOrder'])->whereNumber('orderId')->name('account.orders.cancel');
    Route::delete('/account/orders', [AccountController::class, 'clearOrders'])->name('account.orders.clear');
    Route::delete('/account/orders/{orderId}', [AccountController::class, 'deleteOrder'])->whereNumber('orderId')->name('account.orders.destroy');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/account/photo', [AccountController::class, 'uploadPhoto'])->name('account.photo.store');
    Route::delete('/account/photo', [AccountController::class, 'deletePhoto'])->name('account.photo.destroy');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::put('/account/addresses/{addressId}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::patch('/account/addresses/{addressId}/default', [AccountController::class, 'setDefaultAddress'])->name('account.addresses.default');
    Route::delete('/account/addresses/{addressId}', [AccountController::class, 'deleteAddress'])->name('account.addresses.destroy');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Defense-in-depth: Block direct access to environment and sensitive files at the routing layer
Route::any('/.env{any?}', fn () => abort(404))->where('any', '.*');
Route::any('/.git{any?}', fn () => abort(404))->where('any', '.*');
Route::any('/{file}.env', fn () => abort(404))->where('file', '.*');
Route::any('/{file}.sql', fn () => abort(404))->where('file', '.*');
