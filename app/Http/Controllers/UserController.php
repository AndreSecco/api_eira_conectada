<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\RedefinirSenhaMail;
use Illuminate\Support\Str;
use Carbon\Carbon;


class UserController extends Controller
{
    public function sendEmail(Request $request)
    {
        try {
            $email = $request->input('email');

            // Verificar se o e-mail existe no sistema
            $user = DB::table('tab_users')->where('username', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'E-mail não encontrado'], 404);
            }

            // Gera um token aleatório
            $token = Str::random(60);

            // Armazena o token na tabela password_resets
            DB::table('password_resets')->updateOrInsert(
                ['email' => $email],
                ['token' => $token],
                ['created_at' => date('Y-m-d h:i:s')]
            );
            $email = $request->input('email');

            // Envia o e-mail
            Mail::to($email)->send(new RedefinirSenhaMail($email, $token));

            return response()->json(['message' => 'E-mail de redefinição enviado com sucesso!']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'Falha ao enviar e-mail: ' . $e->getMessage();
        }
    }

    public function redefinirSenha(Request $request)
    {
        try {
            $token = $request->input('token');
            $email = $request->input('email');
        
            $passwordReset = DB::table('password_resets')->where('email', $email)->first();
        
            if (!$passwordReset || $token != $passwordReset->token) {
                return response()->json(['message' => 'Token inválido'], 400);
            }
        
            if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
                return response()->json(['message' => 'Token expirado'], 400);
            }

            $update_senha = DB::table('tab_users')
                ->where('username', $request->email)
                ->update([
                    'password' => $request->novaSenha
                ]);

            return response()->json(['message' => 'Redefinição de senha realizada com sucesso!']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'Falha ao redefinir senha: ' . $e->getMessage();
        }
    }

    protected function jwt($usuario)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $usuario->nr_sequencial, // Subject of the token
            'usuario' => $usuario->nr_seq_filial, // filial do colaborador
            'iat' => time(), // Time when JWT was issued. 
            'exp' => time() + 60 * 60 * 24 * 365 * 5 // Expiration time = 5 years
        ];
        // return $payload;
        // As you can see we are passing `JWT_SECRET` as the second parameter that will 
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }

    public function authenticate(Request $request)
    {
        try {
            $user = DB::table('tab_users as tu')
                ->select('tp.nr_sequencial', 'tu.*', 'tp.*')
                ->join('tab_pessoas as tp', 'tu.nr_seq_pessoa', '=', 'tp.nr_sequencial')
                ->Leftjoin('tab_pessoa_ministerio as tm', 'tp.nr_sequencial', '=', 'tm.nr_seq_pessoa')
                ->Leftjoin('tab_pessoa_contato as tc', 'tp.nr_sequencial', '=', 'tc.nr_seq_pessoa')
                ->Leftjoin('tab_funcoes as tf', 'tp.nr_seq_funcao', '=', 'tf.nr_sequencial')
                ->where('tu.username', $request->username)
                ->where('tu.password', $request->password)
                ->where('tp.st_ativo', 'true')
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
