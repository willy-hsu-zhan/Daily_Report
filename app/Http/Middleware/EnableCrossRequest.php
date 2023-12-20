<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnableCrossRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        header("Access-Control-Allow-Origin: *");
        // header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        $requestMethod = $request->method();

        if( $requestMethod === 'OPTIONS' )
        {
            $headers = [
                'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers'),
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            ];

            return response('OK', 200)->withHeaders($headers);
        }

        return $next($request);
    }
}
