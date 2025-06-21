<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // User login bhayepaxi aune dashboard
    public function index()
    {
        $user = Auth::user(); // login gareko user taneko

        // yedi user dashboard maa personal stats dekhauxa bhane data yaha pathauna sakincha
        // e.g. $totalLeaves = $user->leaveRequests()->count();

        return view('frontend.dashboard', compact('user'));
    }
}
