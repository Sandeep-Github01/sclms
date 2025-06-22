<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\UserController as AdminUser;
use App\Http\Controllers\Frontend\UserController as FrontendUser;
use App\Http\Controllers\Frontend\MailController; 

use App\Http\Controllers\Frontend\DashboardController;

// Backend Routes
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('/user', [AdminUser::class, 'index'])->name('admin.user.index');
    Route::get('/user/{id}', [AdminUser::class, 'show'])->name('admin.user.show');
    Route::delete('/user/{id}', [AdminUser::class, 'destroy'])->name('admin.user.destroy');
});

// Frontend Routes
Route::get('/', [FrontendUser::class, 'login_show'])->name('login');

Route::get('/user/register', [FrontendUser::class, 'register_show'])->name('frontend.user.register');
Route::post('/user/registerSave', [FrontendUser::class, 'register'])->name('frontend.user.registerSave');

Route::get('/user/login', [FrontendUser::class, 'login_show'])->name('frontend.user.login');
Route::post('/user/loginMatch', [FrontendUser::class, 'login'])->name('frontend.user.loginMatch');
Route::get('/user/logout', [FrontendUser::class, 'logout'])->name('user.logout');

Route::get('send-email', [MailController::class, 'sendEmail']);
Route::get('email_verify/{id}', [FrontendUser::class, 'verify_email'])->name('frontend.emails.verify-email');
Route::get('/authenticate/verificationSent', [FrontendUser::class,'verificationSent'])->name('frontend.emails.verificationSent');

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('frontend.user.profile');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
