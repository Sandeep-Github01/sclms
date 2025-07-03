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
Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('frontend.user.profile');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.user.dashboard');
});

Route::get('/', [FrontendUser::class, 'login_show'])->name('login');
Route::get('/user/login', [FrontendUser::class, 'login_show'])->name('frontend.user.login');
Route::post('/user/loginMatch', [FrontendUser::class, 'login'])->name('frontend.user.loginMatch');
Route::post('/user/logout', [FrontendUser::class, 'logout'])->name('frontend.user.logout');

Route::get('/user/register', [FrontendUser::class, 'register_show'])->name('frontend.user.register');
Route::post('/user/registerSave', [FrontendUser::class, 'register'])->name('frontend.user.registerSave');
Route::get('/authenticate/verificationSent', [FrontendUser::class, 'verificationSent'])->name('frontend.emails.verificationSent');
Route::get('/email_verify/{id}', [FrontendUser::class, 'verify_email'])->name('frontend.emails.verify-email')->middleware('signed');

Route::get('send-email', [MailController::class, 'sendEmail']);

Route::middleware(['auth'])->group(function () {
    Route::get('/leave/apply', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/leave/apply', [LeaveController::class, 'store'])->name('leave.store');
    Route::get('/leave/list', [LeaveController::class, 'index'])->name('leave.list');
    Route::get('/leave/{id}', [LeaveController::class, 'show'])->name('leave.show');
    Route::get('/leave/result/{id}', [LeaveController::class, 'result'])->name('leave.result');
});


Route::get('/forgot-password', [FrontendUser::class, 'showForgotPasswordForm'])->name('frontend.user.forgot-password');
Route::post('/forgot-password', [FrontendUser::class, 'sendResetLinkEmail'])->name('frontend.user.forgot-password.send');
Route::get('/reset-password/{token}', [FrontendUser::class, 'showResetPasswordForm'])->name('password.reset');  
Route::post('/reset-password', [FrontendUser::class, 'updatePassword'])->name('frontend.user.reset-password.update');
