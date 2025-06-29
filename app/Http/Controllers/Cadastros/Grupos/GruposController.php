<?php

namespace App\Http\Controllers\Cadastros\Grupos;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GruposController extends Controller
{
    public function createGrupo(Request $request)
    {
        try {
            $data = $this->validate($request, [
                'nr_seq_lider' => 'required|integer',
                'nr_seq_vice_lider' => 'required|integer',
                'nr_seq_anfitriao' => 'required|integer',
            ]);

            $dias_semana = json_encode($request->dias_semana);

            // return response()->json($request['auth'], 200);
            $filial_lider = DB::table('tab_pessoas')
                ->where('nr_sequencial', $data['nr_seq_lider'])
                ->first();

            if ($request->id_grupo) {
                DB::table('tab_grupos')
                    ->where('nr_sequencial', $request->id_grupo)
                    ->update([
                        'nr_seq_lider' => $data['nr_seq_lider'],
                        'nr_seq_vice_lider' => $data['nr_seq_vice_lider'],
                        'nr_seq_anfitriao' => $data['nr_seq_anfitriao'],
                        'nr_nivel'    => 1,
                        'id_parent'   => 1,
                        'tipo_grupo' => $request->tipo_grupo,
                        'sexo_participantes' => $request->sexo_participantes,
                        'dias_semana' => $dias_semana,
                        'horario' => $request->horario,
                        'updated_at' => date('Y-m-d h:i:s'),
                        'nr_seq_filial' => $filial_lider->nr_seq_filial,
                    ]);

                $update_membro_lider = DB::table('tab_grupo_membros')
                    ->where('nr_seq_grupo', $request->id_grupo)
                    ->where('nr_seq_tipo_membro', 1)
                    ->update([
                        'nr_seq_pessoa' => $data['nr_seq_lider'],
                    ]);

                $update_membro_vice_lider = DB::table('tab_grupo_membros')
                    ->where('nr_seq_grupo', $request->id_grupo)
                    ->where('nr_seq_tipo_membro', 2)
                    ->update([
                        'nr_seq_pessoa' => $data['nr_seq_vice_lider'],
                    ]);

                $update_membro_anfitriao = DB::table('tab_grupo_membros')
                    ->where('nr_seq_grupo', $request->id_grupo)
                    ->where('nr_seq_tipo_membro', 3)
                    ->update([
                        'nr_seq_pessoa' => $data['nr_seq_anfitriao'],
                    ]);

                $insert_grupo = $request->id_grupo;
            } else {
                $insert_grupo = DB::table('tab_grupos')->insertGetId([
                    'nr_seq_lider' => $data['nr_seq_lider'],
                    'nr_seq_vice_lider' => $data['nr_seq_vice_lider'],
                    'nr_seq_anfitriao' => $data['nr_seq_anfitriao'],
                    'nr_nivel'    => $filial_lider->nr_nivel,
                    'id_parent'   => $filial_lider->nr_sequencial,
                    'tipo_grupo' => $request->tipo_grupo,
                    'sexo_participantes' => $request->sexo_participantes,
                    'dias_semana' => $dias_semana,
                    'horario' => $request->horario,
                    'created_at' => date('Y-m-d h:i:s'),
                    'nr_seq_filial' => $filial_lider->nr_seq_filial,
                ]);

                $insert_membro_lider = DB::table('tab_grupo_membros')->insert([
                    'nr_seq_grupo' => $insert_grupo,
                    'nr_seq_pessoa' => $data['nr_seq_lider'],
                    'nr_seq_tp_membro' => 1
                ]);
                $insert_membro_vice_lider = DB::table('tab_grupo_membros')->insert([
                    'nr_seq_grupo' => $insert_grupo,
                    'nr_seq_pessoa' => $data['nr_seq_vice_lider'],
                    'nr_seq_tp_membro' => 2
                ]);
                $insert_membro_anfitriao = DB::table('tab_grupo_membros')->insert([
                    'nr_seq_grupo' => $insert_grupo,
                    'nr_seq_pessoa' => $data['nr_seq_anfitriao'],
                    'nr_seq_tp_membro' => 3
                ]);
            }

            return response()->json($insert_grupo, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
    public function createEvento(Request $request)
    {
        try {
            if ($request->id_evento) {
                DB::table('tab_eventos')
                    ->where('nr_sequencial', $request->id_evento)
                    ->update([
                        'nome_evento' => $request->nome_evento,
                        'data_evento' => $request->data_evento,
                        'local_evento' => $request->local_evento,
                        'tp_pago' => $request->tp_pago,
                        'evento_para' => $request->evento_para
                    ]);

                $insert_evento = $request->id_evento;
            } else {
                $insert_evento = DB::table('tab_eventos')->insertGetId([
                    'nome_evento' => $request->nome_evento,
                    'data_evento' => $request->data_evento,
                    'local_evento' => $request->local_evento,
                    'tp_pago' => $request->tp_pago,
                    'evento_para' => $request->evento_para,
                    'nr_seq_filial' => $request->auth->nr_seq_filial
                ]);
            }

            return response()->json($insert_evento, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
    public function registrarIngresso(Request $request)
    {
        try {
            $sql_valida = DB::table('tab_registra_ingressos')
            ->where('nr_seq_evento', $request->id_evento)
            ->where('nr_seq_pessoa', $request->id)
            ->first();

            if ($sql_valida) {
                return response()->json(['message' => 'Usuário já registrado em evento!', 400]);
            }

            $insert_ingresso = DB::table('tab_registra_ingressos')->insert([
                'nr_seq_evento' => $request->id_evento,
                'nr_seq_pessoa' => $request->id
            ]);

            return response()->json($insert_ingresso, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function uploadFiles(Request $request, $id_user)
    {
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName() . strtotime("now") . '.' . $file->extension();
                $file->move('files', $fileName);

                $uploadFileUser = DB::table('tab_pessoas')
                    ->where('nr_sequencial', $id_user)
                    ->update([
                        'imagem_perfil' => $fileName
                    ]);

                return response()->json($uploadFileUser, 200);
            }
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage(), 500]);
        }
    }
    public function createContato(Request $request)
    {
        try {

            $nr_sequencial = DB::table('tab_grupos_contato')
                ->where('nr_seq_grupo', $request->id_grupo)
                ->first();

            if (!empty($nr_sequencial)) {
                $insert_contato = DB::table('tab_grupos_contato')
                    ->where('nr_seq_grupo', $request->id_grupo)
                    ->update([
                        'endereco' => $request->endereco,
                        'cidade' => $request->cidade,
                        'uf' => $request->uf,
                        'bairro' => $request->bairro,
                        'cep' => $request->cep,
                        'numero' => $request->numero,
                        'complemento' => $request->complemento,
                    ]);
            } else {
                $insert_contato = DB::table('tab_grupos_contato')->insertGetId([
                    'nr_seq_grupo' => $request->id_grupo,
                    'endereco' => $request->endereco,
                    'cidade' => $request->cidade,
                    'uf' => $request->uf,
                    'bairro' => $request->bairro,
                    'cep' => $request->cep,
                    'numero' => $request->numero,
                    'complemento' => $request->complemento,
                ]);
            }

            return response()->json($insert_contato, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function inserirMembroCelula(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_grupo_membros')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($nr_sequencial)) {
                return response()->json('Usuário já pertence a uma céluula', 400);
            } else {
                $insert_membro_celula = DB::table('tab_grupo_membros')->insertGetId([
                    'nr_seq_grupo' => $request->id_grupo,
                    'nr_seq_pessoa' => $request->id_user,
                    'nr_seq_tp_membro' => 4,
                ]);

                $membros = DB::table('tab_grupo_membros as gm')
                    ->where('gm.nr_seq_grupo', $request->id_grupo)
                    ->join('tab_pessoas as tp', 'gm.nr_seq_pessoa', '=', 'tp.nr_sequencial')
                    ->join('tp_membro as tm', 'gm.nr_seq_tp_membro', '=', 'tm.nr_sequencial')
                    ->get();
            }

            return response()->json($membros, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function deleteMembroCelula(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_grupo_membros')
                ->where('nr_seq_pessoa', $request->id_user)
                ->where('nr_seq_grupo', $request->id_grupo)
                ->delete();

            $membros = DB::table('tab_grupo_membros as gm')
                ->where('gm.nr_seq_grupo', $request->id_grupo)
                ->join('tab_pessoas as tp', 'gm.nr_seq_pessoa', '=', 'tp.nr_sequencial')
                ->join('tp_membro as tm', 'gm.nr_seq_tp_membro', '=', 'tm.nr_sequencial')
                ->get();

            return response()->json($membros, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getListaPessoasInicio(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $lista_pessoas = DB::table('tab_pessoas as tp')
                ->select(
                    'tp.nr_sequencial',
                    'tp.nome_pessoa',
                    'tp.sexo_pessoa',
                    'tp.dt_nascimento',
                    'tp.entrou_em',
                    'tpm.dt_batismo',
                    'tp2.nome_pessoa as nome_lider',
                    'tpm.tp_participacao'
                )
                ->leftJoin('tab_pessoa_ministerio as tpm', 'tp.nr_sequencial', '=', 'tpm.nr_seq_pessoa')
                ->leftJoin('tab_pessoas as tp2', 'tp2.nr_sequencial', '=', 'tpm.nr_seq_lider')
                ->distinct()
                ->orderBy('tp.nome_pessoa', 'ASC')
                ->where('tp.st_ativo', 'true')
                ->paginate($perPage);

            // $lista_pessoas = $lista_pessoas->paginate($request->get('per_page'));

            return response()->json($lista_pessoas, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function inativarCadastro(Request $request)
    {
        try {

            $inativar_cadastro = DB::table('tab_pessoas')
                ->where('nr_sequencial', $request->nr_sequencial)
                ->update([
                    'st_ativo' => 'false'
                ]);

            return response()->json($inativar_cadastro, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getListaPessoa(Request $request, $desc_pessoa)
    {
        try {
            $lista_pessoa = DB::table('tab_pessoas')
                ->select('nr_sequencial', 'nome_pessoa')
                ->where('nome_pessoa', 'like', "%{$desc_pessoa}%")
                ->where('st_ativo', 'true')
                ->limit(100)
                ->get();

            return response()->json(['listaPessoas' => $lista_pessoa], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getGrupoId(Request $request, $id_grupo)
    {
        try {
            $grupo = DB::table('tab_grupos')
                ->where('nr_sequencial', $id_grupo)
                ->first();

            if (!empty($grupo)) {
                $grupo->nr_seq_lider = DB::table('tab_pessoas')
                    ->select('nr_sequencial', 'nome_pessoa')
                    ->where('nr_sequencial', $grupo->nr_seq_lider)
                    ->first();
            }
            if (!empty($grupo)) {
                $grupo->nr_seq_vice_lider = DB::table('tab_pessoas')
                    ->select('nr_sequencial', 'nome_pessoa')
                    ->where('nr_sequencial', $grupo->nr_seq_vice_lider)
                    ->first();
            }
            if (!empty($grupo)) {
                $grupo->nr_seq_anfitriao = DB::table('tab_pessoas')
                    ->select('nr_sequencial', 'nome_pessoa')
                    ->where('nr_sequencial', $grupo->nr_seq_anfitriao)
                    ->first();
            }

            $grupo->dias_semana = json_decode($grupo->dias_semana, true);

            $contato = DB::table('tab_grupos_contato')
                ->where('nr_seq_grupo', $id_grupo)
                ->first();

            $membros = DB::table('tab_grupo_membros as gm')
                ->where('gm.nr_seq_grupo', $id_grupo)
                ->join('tab_pessoas as tp', 'gm.nr_seq_pessoa', '=', 'tp.nr_sequencial')
                ->join('tp_membro as tm', 'gm.nr_seq_tp_membro', '=', 'tm.nr_sequencial')
                ->get();

            return response()->json([
                'grupo' => $grupo,
                'contato' => $contato,
                'membros' => $membros
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getEventoId(Request $request, $id_evento)
    {
        try {
            $evento = DB::table('tab_eventos')
                ->where('nr_sequencial', $id_evento)
                ->first();

            return response()->json([
                'evento' => $evento,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function getListaGruposInicio(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $codigoUsuario = $request->auth->nr_sequencial; // Código da pessoa logada

            $lista_grupos =  DB::table('tab_grupos as tg')
                ->select(
                    'tg.nr_sequencial',
                    'tp1.nome_pessoa as nome_lider',
                    'tg.dias_semana',
                    'tg.horario',
                    DB::raw('COUNT(tgm.nr_seq_grupo) as total_membros'),
                    'tg.sexo_participantes',
                    'tgc.bairro',
                    'tg.nr_seq_lider',
                    'tg.id_parent'
                )
                ->leftJoin('tab_pessoas as tp1', 'tg.id_parent', '=', 'tp1.nr_sequencial')
                ->leftJoin('tab_grupo_membros as tgm', 'tg.nr_sequencial', '=', 'tgm.nr_seq_grupo')
                ->leftJoin('tab_grupos_contato as tgc', 'tg.nr_sequencial', '=', 'tgc.nr_seq_grupo')
                ->distinct()
                ->groupBy('tg.nr_sequencial', 'tp1.nome_pessoa', 'tg.dias_semana', 'tg.horario', 'tg.sexo_participantes', 'tgc.bairro', 'tg.nr_seq_lider', 'tg.id_parent')
                ->havingRaw('tg.id_parent IN (
                                SELECT tp.nr_sequencial
                                FROM tab_pessoas tp
                                WHERE tp.nr_sequencial = ? OR tp.id_parent = ?
                            )', [$codigoUsuario, $codigoUsuario]) // Binding para o usuário e seu pai
                ->orderBy('tg.nr_sequencial', 'ASC')
                ->paginate($perPage);

            // $lista_pessoas = $lista_pessoas->paginate($request->get('per_page'));

            return response()->json($lista_grupos, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
    public function getListaEventosInicio(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $lista_grupos =  DB::table('tab_eventos as te')
                ->where('te.nr_seq_filial', $request->auth->nr_seq_filial)
                ->orderBy('te.nr_sequencial', 'ASC')
                ->paginate($perPage);

            return response()->json($lista_grupos, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
