<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        $allowedRoutes = [
            'frontend.user.profile',
            'frontend.user.profileEdit', 
            'frontend.user.profileUpdate',
            'frontend.user.logout'
        ];
        
        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }
        
        if (!$user->is_profile_complete) {
            return redirect()->route('frontend.user.profileEdit')
                ->with('warning', 'Please complete your profile first to access other features.');
        }
        
        if (strtolower($user->profile_status) !== 'approved') {
            $message = strtolower($user->profile_status) === 'pending' 
                ? 'Your profile is pending admin approval. You cannot access other features until approved.'
                : 'Your profile needs admin approval. Please contact support.';
                
            return redirect()->route('frontend.user.profile')
                ->with('warning', $message);
        }
        
        return $next($request);
    }
}