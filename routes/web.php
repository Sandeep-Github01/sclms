<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\UserController as AdminUser;
use App\Http\Controllers\Frontend\UserController as FrontendUser;
use App\Http\Controllers\Frontend\MailController; 
use App\Http\Controllers\Frontend\LeaveController; 

use App\Http\Controllers\Frontend\DashboardController;

// Backend Routes
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('/user', [AdminUser::class, 'index'])->name('admin.user.index');
    Route::get('/user/{id}', [AdminUser::class, 'show'])->name('admin.user.show');
    Route::delete('/user/{id}', [AdminUser::class, 'destroy'])->name('admin.user.destroy');
});

// Frontend Routes
Route::get('/', [FrontendUser::class, 'login_show'])->name('login');

// Route::get('/user/register', [FrontendUser::class, 'register_show'])->name('frontend.user.register');
// Route::post('/user/registerSave', [FrontendUser::class, 'register'])->name('frontend.user.registerSave');

Route::get('/user/login', [FrontendUser::class, 'login_show'])->name('frontend.user.login');
// Route::post('/user/loginMatch', [FrontendUser::class, 'login'])->name('frontend.user.loginMatch');
// Route::get('/user/logout', [FrontendUser::class, 'logout'])->name('user.logout');

// Route::get('send-email', [MailController::class, 'sendEmail']);
// Route::get('email_verify/{id}', [FrontendUser::class, 'verify_email'])->name('frontend.emails.verify-email');
// Route::get('/authenticate/verificationSent', [FrontendUser::class,'verificationSent'])->name('frontend.emails.verificationSent');

Route::post('/user/loginMatch', [FrontendUser::class, 'login'])
     ->name('frontend.user.loginMatch');

// Logout
Route::post('/user/logout', [FrontendUser::class, 'logout'])
     ->name('frontend.user.logout');

// Register page
Route::get('/user/register', [FrontendUser::class, 'register_show'])
     ->name('frontend.user.register');

// Register submit
Route::post('/user/registerSave', [FrontendUser::class, 'register'])
     ->name('frontend.user.registerSave');

// Verification Sent page (after registration)
Route::get('/authenticate/verificationSent', [FrontendUser::class, 'verificationSent'])
     ->name('frontend.emails.verificationSent');

// Email verify link: signed route
Route::get('/email_verify/{id}', [FrontendUser::class, 'verify_email'])
     ->name('frontend.emails.verify-email')
     ->middleware('signed');

// Example: if you have MailController for testing
Route::get('send-email', [MailController::class, 'sendEmail']);

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('frontend.user.profile');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.user.dashboard');
});


Route::middleware(['auth'])->group(function () {
    // Apply form
    Route::get('/leave/apply', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/leave/apply', [LeaveController::class, 'store'])->name('leave.store');

    // List user's leaves
    Route::get('/leave/list', [LeaveController::class, 'index'])->name('leave.list');

    // Show single leave detail
    Route::get('/leave/{id}', [LeaveController::class, 'show'])->name('leave.show');

    // Result view after apply (auto approved or rejected)
    // optional: we can show result immediately after store, or redirect to list
    Route::get('/leave/result/{id}', [LeaveController::class, 'result'])->name('leave.result');
});