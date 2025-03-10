<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pessoa;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token não fornecido'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            // return response()->json(Pessoa::find($decoded->sub));
            $request->merge(['auth' => Pessoa::find($decoded->sub)]);
            $request->auth->filiais = $decoded->filiais;
        } catch (\Exception $e) {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            return response()->json(['error' => $decoded->filiais], 401);
        }

        return $next($request);
    }
}