<?php

namespace App\Http;

use App\Http\Middleware\IsAdmin;

// ✅ ADD these:
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\HandleCors;

use Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'isAdmin' => IsAdmin::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
    ];
}

// namespace App\Http;

// use Illuminate\Foundation\Http\Kernel as HttpKernel;

// class Kernel extends HttpKernel
// {
//     /**
//      * Global HTTP middleware stack.
//      */
//     protected $middleware = [
//         \Illuminate\Http\Middleware\TrustProxies::class,
//         \Illuminate\Http\Middleware\HandleCors::class,
//         // \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
//         \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,

//         \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
//         // \App\Http\Middleware\TrimStrings::class,
//         \Illuminate\Foundation\Http\Middleware\TrimStrings::class,

//         \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
//     ];

//     /**
//      * Route middleware groups.
//      */
//     protected $middlewareGroups = [
//         'web' => [
//             \App\Http\Middleware\EncryptCookies::class,
//             \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
//             \Illuminate\Session\Middleware\StartSession::class,
//             \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//             \App\Http\Middleware\VerifyCsrfToken::class,
//             \Illuminate\Routing\Middleware\SubstituteBindings::class,
//         ],

//         'api' => [
//             'throttle:api',
//             \Illuminate\Routing\Middleware\SubstituteBindings::class,
//         ],
//     ];

//     /**
//      * Route middleware.
//      */
//     protected $routeMiddleware = [
//         'auth' => \App\Http\Middleware\Authenticate::class,
//         'isAdmin' => \App\Http\Middleware\IsAdmin::class,
//         'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
//         'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
//         'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
//     ];
// } -->
