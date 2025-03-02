<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Grupos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CultosController extends Controller
{

    public function registrarCulto(Request $request)
    {
        try {
            $filiaisStr = implode(",", array_map(function ($filial) {
                return $filial->nr_sequencial;
            }, $request->auth->filiais));

            $sql_insert = DB::table('tab_cultos')->insertGetId([
                'nr_seq_quem_pregou' => $request->quem_pregou,
                'nr_seq_diacono_01' => $request->diacono_01,
                'dia_semana' => $request->dia_semana[0],
                'horario' => $request->horario,
                'novos_convidados' => $request->novos_convidados,
                'criancas_apresentadas' => $request->criancas_apresentadas,
                'dizimos' => $request->dizimos ? str_replace([','], ['.'], $request->dizimos) : 0,
                'ofertas_gerais' => $request->ofertas_gerais ? str_replace([','], ['.'], $request->ofertas_gerais) : 0 ,
                'doacoes_especiais' => $request->doacoes_especiais ? str_replace([','], ['.'], $request->doacoes_especiais) : 0,
                'outras_entradas' => $request->outras_entradas ? str_replace([','], ['.'], $request->outras_entradas) : 0,
                'ofertas_celulas' => str_replace([','], ['.'], $request->ofertas_celulas),
                'valor_total' => str_replace([','], ['.'], $request->valor_total),
            ]);

            $sql_assinatura_culto = DB::table('tab_assinatura_culto')->insertGetId([
                'nr_seq_culto' => $sql_insert,
                'nr_seq_pessoa' =>  $request->diacono_01,
                'st_aprovado' => 'false'
            ]);

            $sql_assinatura_culto = DB::table('tab_assinatura_culto')->insertGetId([
                'nr_seq_culto' => $sql_insert,
                'nr_seq_pessoa' =>  $request->diacono_02,
                'st_aprovado' => 'false'
            ]);

            $pastor_tesoureiro = DB::table('tab_pessoas')
            ->where('nr_seq_filial', $request->auth->nr_seq_filial)
            ->where('nr_seq_funcao', 3)
            ->orWhere('nr_seq_funcao', 4)
            ->get();

            foreach($pastor_tesoureiro as $value){
                DB::table('tab_assinatura_culto')->insertGetId([
                    'nr_seq_culto' => $sql_insert,
                    'nr_seq_pessoa' =>  $value->nr_sequencial,
                    'st_aprovado' => 'false'
                ]);
            }

            DB::select("UPDATE tab_registros_financeiros
                        SET status_transacao = 'O', nr_seq_culto = " . $sql_insert . "
                        WHERE nr_sequencial IN (
                            SELECT nr_sequencial FROM (
                                SELECT trf.nr_sequencial
                                FROM tab_registros_financeiros trf
                                WHERE trf.tipo_transacao = 1
                                AND trf.status_transacao = 'P'
                                AND trf.nr_seq_filial IN (" . $filiaisStr . ")
                            ) AS subquery
                        );");

            return response()->json($sql_insert, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getOfertasCelulas(Request $request)
    {
        try {
            $filiaisStr = implode(",", array_map(function ($filial) {
                return $filial->nr_sequencial;
            }, $request->auth->filiais));

            $sql_insert = DB::select("SELECT SUM(trf.valor_transacao) as ofertas 
                                    from tab_registros_financeiros trf
                                    where trf.tipo_transacao = 1
                                    and trf.status_transacao = 'P'
                                    AND trf.nr_seq_filial in (" . $filiaisStr . ")");

            return response()->json($sql_insert, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function uploadAnexoCulto(Request $request, $id_culto)
    {
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName() . strtotime("now") . '.' . $file->extension();
                $file->move('files/anexo_culto', $fileName);

                $uploadFileUser = DB::table('tab_cultos')
                    ->where('nr_sequencial', $id_culto)
                    ->update([
                        'anexo_culto' => $fileName
                    ]);

                return response()->json($uploadFileUser, 200);
            }
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage(), 500]);
        }
    }

    public function getConfirmacaoCulto(Request $request)
    {
        try {
            $sql_confirmacao = DB::table('tab_assinatura_culto as tac')
            ->select('tac.nr_sequencial as nr_seq_assinatura_culto', 'tp.nome_pessoa as nome_quem_pregou', 'tc.*')
            ->join('tab_cultos as tc', 'tac.nr_seq_culto', '=', 'tc.nr_sequencial')
            ->join('tab_pessoas as tp', 'tc.nr_seq_quem_pregou', '=', 'tp.nr_sequencial')
            ->where('nr_seq_pessoa', $request->auth->nr_sequencial)
            ->where('st_aprovado', 'false')
            ->get();

            return response()->json($sql_confirmacao, 200);
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage(), 500]);
        }
    }

    public function confirmarCulto(Request $request)
    {
        try {
            $sql_confirmacao = DB::table('tab_assinatura_culto as tac')
            ->where('tac.nr_sequencial', $request->nr_seq_assinatura_culto)
            ->where('nr_seq_pessoa', $request->auth->nr_sequencial)
            ->update([
                'st_aprovado' => 'true'
            ]);

            return response()->json($sql_confirmacao, 200);
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage(), 500]);
        }
    }
}
