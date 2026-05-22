<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOperatorSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('operator_id')) {
            return redirect()->route('login')->withErrors('Harap masuk terlebih dahulu.');
        }

        return $next($request);
    }
}
