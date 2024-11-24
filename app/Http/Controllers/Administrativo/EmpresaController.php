<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Grupos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class EmpresaController extends Controller
{

    public function registraEmpresa(Request $request)
    {
        try {

            if (!$request->id_empresa) {
                $sql_insert = DB::table('tab_empresas')->insertGetId([
                    'nome_empresa' => "$request->nome_empresa",
                    'dt_inicio' => $request->dt_inicio,
                    'st_ativo' => "$request->st_ativo",
                ]);

                $sql_insert = DB::table('tab_filiais')->insertGetId([
                    'nome_filial' => ' MATRIZ - ' . $request->nome_empresa,
                    'nr_seq_empresa' => $sql_insert
                ]);

                $insert_pessoa = DB::table('tab_pessoas')->insertGetId([
                    'nome_pessoa' => 'admin - ' . $request->nome_empresa,
                    'sexo_pessoa' => 'O',
                    'nr_nivel'    => 1,
                    'id_parent'   => 1,
                    'estado_civil' => '',
                    'nr_seq_filial' => $sql_insert,
                    'nr_seq_funcao' => 1,
                    'st_ativo' => 'true'
                ]);

                $email =  str_replace(" ", "", $request->nome_empresa . '@gmail.com');
                $senha = '1234';
                
                $sql_email_existe = DB::table('tab_users')
                    ->where('username', $email)
                    ->first();

                if (!empty($sql_email_existe)) {
                    return response()->json('Email já cadastrado no sistema', 400);
                }

                DB::table('tab_users')->insertGetId([
                    'username' => $email,
                    'password' => $senha,
                    'nr_seq_pessoa' => $insert_pessoa,
                    'st_ativo' => 'true'
                ]);
            } else {
                $sql_insert = DB::table('tab_empresas')
                    ->where('nr_sequencial', $request->id_empresa)
                    ->update([
                        'nome_empresa' => "$request->nome_empresa",
                        'dt_inicio' => $request->dt_inicio,
                        'st_ativo' => "$request->st_ativo",
                    ]);          
            }

            return response()->json($sql_insert, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }


    public function getEmpresaId(Request $request, $id_empresa)
    {
        try {
            $empresa = DB::table('tab_empresas')
                ->where('nr_sequencial', $id_empresa)
                ->first();

            return response()->json($empresa, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getListaEmpresas(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $lista_empresas = DB::table('tab_empresas as te')
                ->distinct()
                ->orderBy('te.nome_empresa', 'ASC')
                ->paginate($perPage);

            return response()->json($lista_empresas, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
