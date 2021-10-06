<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Is_login
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
            $request->headers->set('Accept', 'application/json');
            if (auth('api')->check()) {
       
                return $next($request);
            }
            $response = ['success' => false];
            $response['data'] = translate('need to login');
            return response()->json($response , 404);
          
    }
}
