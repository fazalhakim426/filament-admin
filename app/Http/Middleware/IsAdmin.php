<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and has an 'admin' role
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }
        return abort(422, 'In valid user login');
    }
}
