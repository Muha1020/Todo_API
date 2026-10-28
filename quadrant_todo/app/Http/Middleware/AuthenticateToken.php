<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    public function __construct(private AuthService $auth) {}
    
    //call the service to get the id from the hashed token and attaches it as n attribute
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $this->auth->authenticate($request->bearerToken());

        $request->attributes->set('user_is', $userId);

        return $next($request);
    }
}
