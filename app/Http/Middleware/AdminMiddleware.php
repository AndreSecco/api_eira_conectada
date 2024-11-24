<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pessoa;

class AdminMiddleware
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
            $pessoa = Pessoa::find($decoded->sub);

            if(!$pessoa && $pessoa->nr_sequencial != 1){
                return response()->json('Usuário não autenticado');
            }

            $request->merge(['auth' => $pessoa]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido ou expirado'], 401);
        }

        return $next($request);
    }
}