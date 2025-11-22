<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Backend\AdminController as AdminUser;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\BlackoutPeriodController;
use App\Http\Controllers\Backend\LeaveController as BackendLeaveController;
use App\Http\Controllers\Frontend\UserController as FrontendUser;
use App\Http\Controllers\Frontend\LeaveController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\FileStreamController;
use App\Http\Middleware\CheckProfileComplete;

/* =========================================================
   FRONTEND  (User Panel)
========================================================= */

Route::get('/', [FrontendUser::class, 'login_show'])->name('login');
Route::get('/user/login', [FrontendUser::class, 'login_show'])->name('frontend.user.login');
Route::post('/user/loginMatch', [FrontendUser::class, 'login'])->name('frontend.user.loginMatch');
Route::post('/user/logout', [FrontendUser::class, 'logout'])->name('frontend.user.logout')->middleware('auth');

Route::get('/user/register', [FrontendUser::class, 'register_show'])->name('frontend.user.register');
Route::post('/user/registerSave', [FrontendUser::class, 'register'])->name('frontend.user.registerSave');
Route::get('/authenticate/verificationSent', [FrontendUser::class, 'verificationSent'])->name('frontend.emails.verificationSent');
Route::get('/email_verify/{id}', [FrontendUser::class, 'verify_email'])
    ->name('frontend.emails.verify-email')
    ->middleware('signed');

Route::get('/forgot-password', [FrontendUser::class, 'showForgotPasswordForm'])->name('frontend.user.forgot-password');
Route::post('/forgot-password', [FrontendUser::class, 'sendResetLinkEmail'])->name('frontend.user.forgot-password.send');
Route::get('/reset-password/{token}', [FrontendUser::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [FrontendUser::class, 'updatePassword'])->name('frontend.user.reset-password.update');

Route::middleware('auth')->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('frontend.user.profile');
    Route::get('/profile/edit', [FrontendUser::class, 'editProfile'])->name('frontend.user.profileEdit');
    Route::post('/profile/update', [FrontendUser::class, 'updateProfile'])->name('frontend.user.profileUpdate');
});

Route::middleware(['auth', CheckProfileComplete::class])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.user.dashboard');
});

Route::middleware(['auth', CheckProfileComplete::class])->prefix('leave')->group(function () {
    Route::get('/apply', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/process', [LeaveController::class, 'process'])
        ->middleware('throttle:5,1')     // 5 tries / 1 min
        ->name('leave.process');

    Route::get('/process/{id}', [LeaveController::class, 'processView'])->name('leave.process.view');
    Route::get('/result/{id}', [LeaveController::class, 'result'])->name('leave.result');
    Route::get('/list', [LeaveController::class, 'index'])->name('leave.list');
    Route::get('/{id}', [LeaveController::class, 'show'])->name('leave.show');
    Route::post('/{id}/cancel', [LeaveController::class, 'cancel'])
        ->middleware('throttle:10,1')
        ->name('leave.cancel');

    Route::get('/{id}/document', [FileStreamController::class, 'leaveDoc'])
        ->name('leave.document.download');

});

/* =========================================================
   BACKEND  (Admin Panel)
========================================================= */
Route::prefix('admin')->group(function () {

    Route::get('/', [AdminUser::class, 'showLoginForm'])->name('admin.login');
    Route::post('/', [AdminUser::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminUser::class, 'logout'])->name('admin.logout');

    Route::get('/forgot-password', [AdminUser::class, 'showForgotPasswordForm'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminUser::class, 'sendResetLinkEmail'])->name('admin.forgot-password.send');
    Route::get('/reset-password/{token}', [AdminUser::class, 'showResetPasswordForm'])->name('admin.password.reset');
    Route::post('/reset-password', [AdminUser::class, 'updatePassword'])->name('admin.reset-password.update');

    /* ---- Protected admin space ---- */
    Route::middleware('auth:admin')->group(function () {

        /* Dashboard & Profile */
        Route::get('/dashboard', [AdminUser::class, 'index'])->name('backend.dashboard');
        Route::get('/profile', [AdminUser::class, 'profileIndex'])->name('admin.profile');
        Route::post('/profile', [AdminUser::class, 'profileUpdate'])->name('admin.profile.update');

        /* User Management */
        Route::resource('user', UserController::class, ['as' => 'admin'])->only(['index', 'show', 'destroy']);
        Route::put('/user/{id}/status', [UserController::class, 'updateStatus'])->name('admin.user.updateStatus');
        Route::get('/user/review-requests', [UserController::class, 'reviewIndex'])->name('admin.user.review_index');
        Route::get('/user/profile-review/{id}', [UserController::class, 'profileReviewForm'])->name('admin.user.profileReviewForm');
        Route::put('/user/profile-review/{id}', [UserController::class, 'processProfileReview'])->name('admin.user.profileReview');

        /* Leave Management  */
        Route::get('/leaves/recent', [BackendLeaveController::class, 'recentLeaves'])->name('admin.leaves.recent');
        Route::get('/leaves/pending', [BackendLeaveController::class, 'index'])->name('admin.leaves.index');
        Route::get('/leaves/{id}/show', [BackendLeaveController::class, 'show'])->name('admin.leaves.show');
        Route::get('/leaves/{id}/review', [BackendLeaveController::class, 'reviewLeave'])->name('admin.review_leave');

        Route::post('/leaves/{id}/decision', [BackendLeaveController::class, 'processDecision'])
            ->middleware('throttle:30,1')
            ->name('admin.process_decision');

        Route::post('/leave/{id}/mark-abuse', [BackendLeaveController::class, 'markAbuse'])
            ->middleware('throttle:30,1')
            ->name('admin.leave.markAbuse');

        /* Resources */
        Route::resource('department', DepartmentController::class, ['as' => 'admin']);
        Route::resource('blackout', BlackoutPeriodController::class, ['as' => 'admin']);
    });
});

/* =========================================================
   FALL-BACK & SIGNED URL SUPPORT
========================================================= */
// Admin 404 redirect
Route::any('admin/{any}', function ($any) {
    return Auth::guard('admin')->check() ? abort(404) : redirect()->route('admin.login');
})->where('any', '.*');
