<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Allow access to these routes without profile completion
        $allowedRoutes = [
            'frontend.user.profile',
            'frontend.user.profileEdit', 
            'frontend.user.profileUpdate',
            'frontend.user.logout'
        ];
        
        // If current route is in allowed routes, proceed
        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }
        
        // Check if profile is incomplete or not approved
        if (!$user->is_profile_complete) {
            return redirect()->route('frontend.user.profileEdit')
                ->with('warning', 'Please complete your profile first to access other features.');
        }
        
        if ($user->profile_status !== 'Approved') {
            $message = $user->profile_status === 'Pending' 
                ? 'Your profile is pending admin approval. You cannot access other features until approved.'
                : 'Your profile needs admin approval. Please contact support.';
                
            return redirect()->route('frontend.user.profile')
                ->with('warning', $message);
        }
        
        return $next($request);
    }
}