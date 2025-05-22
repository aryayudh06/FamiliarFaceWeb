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

        // Check if user is authenticated and hasn't completed face auth
        if (Auth::check() && !Session::get('face_authenticated')) {
            return redirect()->route('face.auth');
        }

        return $next($request);
    }
}
