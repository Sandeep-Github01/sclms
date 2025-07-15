<?php

use Illuminate\Support\Facades\Route;

// --------------------
// BACKEND IMPORTS
// --------------------
use App\Http\Controllers\Backend\AdminController as AdminUser;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\BlackoutPeriodController;

// --------------------
// FRONTEND IMPORTS
// --------------------
use App\Http\Controllers\Frontend\UserController as FrontendUser;
use App\Http\Controllers\Frontend\MailController;
use App\Http\Controllers\Frontend\LeaveController;
use App\Http\Controllers\Frontend\DashboardController;


// --------------------
// BACKEND ROUTES
// --------------------

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminUser::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminUser::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminUser::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminUser::class, 'index'])->name('backend.dashboard');
        Route::get('/profile', [AdminUser::class, 'profileIndex'])->name('admin.profile');
        Route::post('/profile', [AdminUser::class, 'profileUpdate'])->name('admin.profile.update');

        Route::get('/user', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('/user/{id}', [UserController::class, 'show'])->name('admin.user.show');
        Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

        Route::resource('department', DepartmentController::class, ['as' => 'admin']);
        Route::resource('blackout', BlackoutPeriodController::class, ['as' => 'admin']);
    });
});


// --------------------
// FRONTEND ROUTES
// --------------------

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('frontend.user.profile');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.user.dashboard');
    Route::get('/profile/edit', [FrontendUser::class, 'editProfile'])->name('frontend.user.profileEdit');
    Route::post('/profile/update', [FrontendUser::class, 'updateProfile'])->name('frontend.user.profileUpdate');
});

Route::get('/', [FrontendUser::class, 'login_show'])->name('login');
Route::get('/user/login', [FrontendUser::class, 'login_show'])->name('frontend.user.login');
Route::post('/user/loginMatch', [FrontendUser::class, 'login'])->name('frontend.user.loginMatch');
Route::post('/user/logout', [FrontendUser::class, 'logout'])->name('frontend.user.logout');

Route::get('/user/register', [FrontendUser::class, 'register_show'])->name('frontend.user.register');
Route::post('/user/registerSave', [FrontendUser::class, 'register'])->name('frontend.user.registerSave');
Route::get('/authenticate/verificationSent', [FrontendUser::class, 'verificationSent'])->name('frontend.emails.verificationSent');
Route::get('/email_verify/{id}', [FrontendUser::class, 'verify_email'])->name('frontend.emails.verify-email')->middleware('signed');

// Route::get('send-email', [MailController::class, 'sendEmail']);

Route::middleware(['auth'])->prefix('leave')->group(function () {
    Route::get('/apply', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/process', [LeaveController::class, 'process'])->name('leave.process');
    Route::get('/process/{id}', [LeaveController::class, 'processView'])->name('leave.process.view');
    Route::get('/result/{id}', [LeaveController::class, 'result'])->name('leave.result');
    Route::get('/list', [LeaveController::class, 'index'])->name('leave.list');
    Route::get('/{id}', [LeaveController::class, 'show'])->name('leave.show');
});

Route::get('/forgot-password', [FrontendUser::class, 'showForgotPasswordForm'])->name('frontend.user.forgot-password');
Route::post('/forgot-password', [FrontendUser::class, 'sendResetLinkEmail'])->name('frontend.user.forgot-password.send');
Route::get('/reset-password/{token}', [FrontendUser::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [FrontendUser::class, 'updatePassword'])->name('frontend.user.reset-password.update');
