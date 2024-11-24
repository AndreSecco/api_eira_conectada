<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Grupos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FilialController extends Controller
{

    public function alterarFilial(Request $request)
    {
        try {

            return response()->json($request, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function registrarFilial(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_filiais')
                ->where('nr_sequencial', $request->id_filial)
                ->first();

            if (!empty($nr_sequencial)) {
                $sql_insert = DB::table('tab_filiais')
                    ->where('nr_sequencial', $request->id_filial)
                    ->update([
                        'nome_filial' => $request->nome_filial,
                        'endereco' => $request->endereco,
                        'cidade' => $request->cidade,
                        'uf' => $request->uf,
                        'bairro' => $request->bairro,
                        'cep' => $request->cep,
                        'numero' => $request->numero,
                        'complemento' => $request->complemento,
                    ]);
            } else {
                $sql_insert = DB::table('tab_filiais')->insertGetId([
                    'nome_filial' => $request->nome_filial,
                    'endereco' => $request->endereco,
                    'cidade' => $request->cidade,
                    'uf' => $request->uf,
                    'bairro' => $request->bairro,
                    'cep' => $request->cep,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,
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

    public function getListaFiliais(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $lista_empresas = DB::table('tab_filiais as tf')
                ->distinct()
                ->orderBy('tf.nome_filial', 'ASC')
                //->where('nr_seq_empresa', $request->auth->nr_seq_empresa)
                ->paginate($perPage);

            return response()->json($lista_empresas, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getFilialId(Request $request, $id_filial)
    {
        try {
            $empresa = DB::table('tab_filiais')
                ->where('nr_sequencial', $id_filial)
                ->first();

            $admin_filial = DB::table('tab_pessoas as tb')
            ->join('tab_users as tu', 'tu.nr_seq_pessoa', '=', 'tb.nr_sequencial')
            ->where('nr_seq_filial', $id_filial)
            ->orderBy('tb.nr_sequencial', 'ASC')
            ->first();

            $empresa->pessoa = $admin_filial;

            return response()->json($empresa, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
