<?php

namespace App\Http\Controllers\Celulas;

use App\Http\Controllers\Controller;
use App\Models\Grupos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CelulasController extends Controller
{

    public function getCelulasLider(Request $request, $id_lider_celula)
    {
        try {
            $select_celulas = Grupos::where('nr_seq_lider', $id_lider_celula)
            ->get();

            return response()->json($select_celulas, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
    public function buscaMembros(Request $request, $id_grupo)
    {
        try {
            $select_celulas = DB::table('tab_grupo_membros as tgm')
                ->join('tab_pessoas as tp', 'tgm.nr_seq_pessoa', '=', 'tp.nr_sequencial')
                ->where('nr_seq_grupo', $id_grupo)
                ->get();

            $select_dados_celula = DB::table('tab_celulas as tc')
                ->join('tab_grupos as tg', 'tc.nr_seq_grupo', '=', 'tg.nr_sequencial')
                ->where('tc.nr_seq_grupo', $id_grupo)
                ->get();

            // $select_dados_celula->participantes = DB::table('')

            return response()->json([
                'membros_celula' => $select_celulas,
                'dados_celula' => $select_dados_celula
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
    public function finalizarCelula(Request $request)
    {
        try {
            $insert_celula = DB::table('tab_celulas')->insertGetId([
                'nr_seq_grupo' => $request->nr_seq_grupo,
                'obs_celula' => $request->nr_seq_grupo,
                'ofertas_voluntarias' => $request->ofertas_voluntarias,
                'nr_nivel' => 1,
                'created_at' => date('Y-m-d m:i:s'),
                'data_celula' => date('Y-m-d m:i:s')
            ]);

            foreach ($request->membros_presentes as $key => $value) {
                $insert_membros_presentes = DB::table('tab_celula_presentes')->insert([
                    'nr_seq_celula' => $insert_celula,
                    'nr_seq_pessoa' => $value['nr_sequencial'],
                    'nr_seq_tp_membro' => 1
                ]);
            }

            return response()->json($insert_celula, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
