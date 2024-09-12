<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    protected function jwt($usuario)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $usuario->nr_sequencial, // Subject of the token
            'usuario' => $usuario->nr_seq_filial, // filial do colaborador
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60 * 60 * 24 * 365 * 5 // Expiration time = 5 years
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }

    public function authenticate(Request $request)
    {
        try {
            $user = DB::table('tab_users as tu')
                ->join('tab_pessoas as tp', 'tu.nr_seq_pessoa', '=', 'tp.nr_sequencial')
                ->Leftjoin('tab_pessoa_ministerio as tm', 'tp.nr_sequencial', '=', 'tm.nr_seq_pessoa')
                ->Leftjoin('tab_pessoa_contato as tc', 'tp.nr_sequencial', '=', 'tc.nr_seq_pessoa')
                ->where('username', $request->username)
                ->where('password', $request->password)
                ->whereNotNull('st_ativo')
                ->first();


            if (!$user) {
                return response()->json('Usuário não encontrado', 400);
            }

            return response()->json([
                'token' => $this->jwt($user),
                'user' => $user
                // 'user' => auth()->user()
            ]);
            // return response()->json($user, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function testeController(Request $request)
    {
        $teste = DB::table('tab_pessoas')->get();
        return response()->json($teste, 200);
    }
}
