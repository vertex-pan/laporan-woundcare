<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Operator;
use Illuminate\Support\Facades\Cookie;

class CheckOperatorSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('operator_id')) {
            // Restore from secure remember cookie if available
            if (Cookie::has('remember_operator_id')) {
                $operatorId = Cookie::get('remember_operator_id');
                $operator = Operator::find($operatorId);

                if ($operator) {
                    session([
                        'operator_id' => $operator->id,
                        'operator_name' => $operator->name,
                        'operator_vendor' => $operator->vendor,
                        'operator_role' => $operator->role,
                        'operator_whatsapp' => $operator->whatsapp,
                    ]);
                    
                    return $next($request);
                }
            }

            return redirect()->route('login')->withErrors('Harap masuk terlebih dahulu.');
        }

        return $next($request);
    }
}
