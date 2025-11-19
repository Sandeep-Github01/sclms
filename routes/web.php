<?php

use Illuminate\Support\Facades\Route;

// -------------------
// <==== BACKEND ====>
// -------------------
use App\Http\Controllers\Backend\AdminController as AdminUser;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\BlackoutPeriodController;
use App\Http\Controllers\Backend\LeaveController as BackendLeaveController;
use App\Http\Controllers\Backend\SidebarController;

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminUser::class, 'showLoginForm'])->name('admin.login');
    Route::post('/', [AdminUser::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminUser::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminUser::class, 'index'])->name('backend.dashboard');
        Route::get('/profile', [AdminUser::class, 'profileIndex'])->name('admin.profile');
        Route::post('/profile', [AdminUser::class, 'profileUpdate'])->name('admin.profile.update');

        Route::get('/user', [UserController::class, 'index'])->name('admin.user.index');
        Route::get('/user/review-requests', [UserController::class, 'reviewIndex'])->name('admin.user.review_index');
        Route::get('/user/profile-review/{id}', [UserController::class, 'profileReviewForm'])->name('admin.user.profileReviewForm');
        Route::put('/user/profile-review/{id}', [UserController::class, 'processProfileReview'])->name('admin.user.profileReview');
        Route::get('/user/{id}', [UserController::class, 'show'])->name('admin.user.show');
        Route::put('/user/{id}/status', [UserController::class, 'updateStatus'])->name('admin.user.updateStatus');
        Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

        Route::resource('department', DepartmentController::class, ['as' => 'admin']);
        Route::resource('blackout', BlackoutPeriodController::class, ['as' => 'admin']);
        Route::get('/leaves/recent', [BackendLeaveController::class, 'recentLeaves'])->name('admin.leaves.recent');
        Route::get('/leaves/pending', [BackendLeaveController::class, 'index'])->name('admin.leaves.index');
        Route::get('/leaves/{id}/show', [BackendLeaveController::class, 'show'])->name('admin.leaves.show');
        Route::get('/leaves/{id}/review', [BackendLeaveController::class, 'reviewLeave'])->name('admin.review_leave');
        Route::post('/leaves/{id}/decision', [BackendLeaveController::class, 'processDecision'])->name('admin.process_decision');

        Route::get('/recent-leaves', [BackendLeaveController::class, 'recentLeaves'])->name('admin.recent_leaves');
        Route::get('/review-leave', [BackendLeaveController::class, 'index'])->name('admin.review_leave_old');
    });
    Route::get('/forgot-password', [AdminUser::class, 'showForgotPasswordForm'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminUser::class, 'sendResetLinkEmail'])->name('admin.forgot-password.send');
    Route::get('/reset-password/{token}', [AdminUser::class, 'showResetPasswordForm'])->name('admin.password.reset');
    Route::post('/reset-password', [AdminUser::class, 'updatePassword'])->name('admin.reset-password.update');
});



// --------------------
// <==== FRONTEND ====>
// --------------------
use App\Http\Controllers\Frontend\UserController as FrontendUser;
use App\Http\Controllers\Frontend\MailController;
use App\Http\Controllers\Frontend\LeaveController;
use App\Http\Controllers\Frontend\DashboardController;

use App\Http\Middleware\CheckProfileComplete;

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('frontend.user.profile');
    Route::get('/profile/edit', [FrontendUser::class, 'editProfile'])->name('frontend.user.profileEdit');
    Route::post('/profile/update', [FrontendUser::class, 'updateProfile'])->name('frontend.user.profileUpdate');
});

Route::middleware(['auth', CheckProfileComplete::class])->prefix('user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('frontend.user.dashboard');
});

Route::middleware(['auth', CheckProfileComplete::class])->prefix('leave')->group(function () {
    Route::get('/apply', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('/process', [LeaveController::class, 'process'])->name('leave.process');
    Route::get('/process/{id}', [LeaveController::class, 'processView'])->name('leave.process.view');
    Route::get('/result/{id}', [LeaveController::class, 'result'])->name('leave.result');
    Route::get('/list', [LeaveController::class, 'index'])->name('leave.list');
    Route::get('/{id}', [LeaveController::class, 'show'])->name('leave.show');
});

Route::get('/', [FrontendUser::class, 'login_show'])->name('login');
Route::get('/user/login', [FrontendUser::class, 'login_show'])->name('frontend.user.login');
Route::post('/user/loginMatch', [FrontendUser::class, 'login'])->name('frontend.user.loginMatch');
Route::post('/user/logout', [FrontendUser::class, 'logout'])->name('frontend.user.logout')->middleware('auth');

Route::get('/user/register', [FrontendUser::class, 'register_show'])->name('frontend.user.register');
Route::post('/user/registerSave', [FrontendUser::class, 'register'])->name('frontend.user.registerSave');
Route::get('/authenticate/verificationSent', [FrontendUser::class, 'verificationSent'])->name('frontend.emails.verificationSent');
Route::get('/email_verify/{id}', [FrontendUser::class, 'verify_email'])->name('frontend.emails.verify-email')->middleware('signed');

Route::get('/forgot-password', [FrontendUser::class, 'showForgotPasswordForm'])->name('frontend.user.forgot-password');
Route::post('/forgot-password', [FrontendUser::class, 'sendResetLinkEmail'])->name('frontend.user.forgot-password.send');
Route::get('/reset-password/{token}', [FrontendUser::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [FrontendUser::class, 'updatePassword'])->name('frontend.user.reset-password.update');

// Route::get('send-email', [MailController::class, 'sendEmail']);