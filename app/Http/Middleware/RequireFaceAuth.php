<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RequireFaceAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip face auth for face auth routes
        if ($request->is('face/*')) {
            return $next($request);
        }

        // If user is not authenticated, redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // If user is authenticated but hasn't completed face auth
        if (!Session::get('face_authenticated')) {
            // Clear any existing session data
            Session::forget('face_authenticated');

            // Redirect to face auth page
            return redirect()->route('face.auth');
        }

        return $next($request);
    }
}
