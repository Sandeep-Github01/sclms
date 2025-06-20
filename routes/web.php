<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Backend\UserController as AdminUser;
use App\Http\Controllers\Frontend\UserController as FrontendUser;

// Route::get('/', function () {
//     return view('welcome');
// });

//Backend Routes

Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUser::class, 'index'])->name('admin.user.index');
    Route::get('/users/{id}', [AdminUser::class, 'show'])->name('admin.user.show');
    Route::delete('/users/{id}', [AdminUser::class, 'destroy'])->name('admin.user.destroy'); // Not shown in UI yet
});




//Frontend Routes

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/profile', [FrontendUser::class, 'profile'])->name('user.profile');
});

Route::get('/register', [FrontendUser::class, 'create'])->name('register');
Route::post('/register', [FrontendUser::class, 'store'])->name('register.submit');
Route::get('/verify/{token}', [FrontendUser::class, 'verifyEmail'])->name('verify.email');

Route::get('/forgot-password', [FrontendUser::class, 'resetRequest'])->name('password.request');
Route::post('/forgot-password', [FrontendUser::class, 'resetSubmit'])->name('password.email');

