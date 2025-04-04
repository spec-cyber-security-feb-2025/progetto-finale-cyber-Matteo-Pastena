<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedDomain = 'internal.admin'; // Cambia con il tuo dominio reale
        $referer = parse_url($request->headers->get('referer'), PHP_URL_HOST);
        $origin = parse_url($request->headers->get('origin'), PHP_URL_HOST);
        if (($referer && $referer !== $allowedDomain) || ($origin && $origin !== $allowedDomain)) {
            return response()->json(['error' => 'Access denied: Invalid Referer or Origin'], Response::HTTP_FORBIDDEN);
        }

        
        if(Auth::user() && Auth::user()->is_admin){
            return $next($request);
        }
        return redirect(route('homepage'))->with('alert', 'Not Authorized');
    }
}
