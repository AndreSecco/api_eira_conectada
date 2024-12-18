<?php

namespace App\Http\Controllers\Cadastros\Pessoas;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PessoasController extends Controller
{
    public function createPessoa(Request $request)
    {
        try {
            $data = $this->validate($request, [
                'nome_pessoa' => 'required|string',
                'sexo_pessoa' => 'required|string',
                'data_nascimento' => 'required|date',
                'estado_civil' => 'required|string',
                'entrou_em' => 'required|date',
            ]);
            
            $nr_seq_filial = $request->auth->filiais[0]->nr_seq_filial;

            if(!empty($request->auth)){
                $user_cadastrando = DB::table('tab_pessoas')
                ->where('nr_sequencial', $request->auth->nr_sequencial)
                ->first();

            } else {
                $user_cadastrando = "";
            }

            if ($request->id_user) {
                DB::table('tab_pessoas')
                    ->where('nr_sequencial', $request->id_user)
                    ->update([
                        'nome_pessoa' => $data['nome_pessoa'],
                        'sexo_pessoa' => $data['sexo_pessoa'],
                        'dt_nascimento' => $data['data_nascimento'],
                        'entrou_em' => $data['entrou_em'],
                        'estado_civil' => $data['estado_civil'],
                        // 'nr_seq_filial' => $nr_seq_filial,
                        'id_conjuge' => $request->id_conjuge,
                        'user_cadastro' => 1,
                        'whatsapp' => $request->whatsapp,
                        'email' => $request->email,
                        'bio_pessoa' => $request->bio_pessoa,
                        'facebook' => $request->facebook,
                        'instagram' => $request->instagram,
                        'nr_seq_funcao' => 2,
                        'st_ativo' => 'true'
                    ]);


                $insert_pessoa = $request->id_user;
            } else {
                $insert_pessoa = DB::table('tab_pessoas')->insertGetId([
                    'nome_pessoa' => $data['nome_pessoa'],
                    'sexo_pessoa' => $data['sexo_pessoa'],
                    'nr_nivel'    => $user_cadastrando->nr_nivel ? $user_cadastrando->nr_nivel + 1 : 1,
                    'id_parent'   => $user_cadastrando->nr_sequencial ?? 7,
                    'dt_nascimento' => $data['data_nascimento'],
                    'entrou_em' => $data['entrou_em'],
                    'estado_civil' => $data['estado_civil'],
                    'nr_seq_filial' => $nr_seq_filial,
                    'id_conjuge' => $request->id_conjuge,
                    'user_cadastro' => 1,
                    'whatsapp' => $request->whatsapp,
                    'email' => $request->email,
                    'bio_pessoa' => $request->bio_pessoa,
                    'facebook' => $request->facebook,
                    'instagram' => $request->instagram,
                    'nr_seq_funcao' => 2,
                    'st_ativo' => 'true'
                ]);


                DB::table('tab_pessoas')
                    ->where('nr_sequencial', $request->id_conjuge)
                    ->update([
                        'id_conjuge' => $request->id_user
                    ]);

                DB::table('tab_pessoa_filial')->insertGetId([
                    'nr_seq_pessoa' => $insert_pessoa,
                    'nr_seq_filial' => $nr_seq_filial
                ]);
            }

            if ($request->senha) {
                $sql_email_existe = DB::table('tab_users')
                    ->where('username', $request->email)
                    ->first();

                if (!empty($sql_email_existe)) {
                    return response()->json('Email já cadastrado no sistema', 400);
                }

                // return response()->json($sql_email_existe, 200);
                DB::table('tab_users')->insertGetId([
                    'username' => $request->email,
                    'password' => $request->senha,
                    'nr_seq_pessoa' => $insert_pessoa,
                    'st_ativo' => 'true'
                ]);
            }

            return response()->json($insert_pessoa, 200);
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

            $nr_sequencial = DB::table('tab_pessoa_contato')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($nr_sequencial)) {
                $insert_contato = DB::table('tab_pessoa_contato')
                    ->where('nr_seq_pessoa', $request->id_user)
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
                $insert_contato = DB::table('tab_pessoa_contato')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
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

    public function getListaPessoasInicio(Request $request)
    {
        try {
            // return response()->json($request->auth->filiais[0]->nr_seq_filial, 200);
            $perPage = $request->get('per_page', 10);

            $filiaisStr = implode(",", array_map(function ($filial) {
                return $filial->nr_sequencial;
            }, $request->auth->filiais));

            $codigoUsuario = $request->auth->nr_sequencial; // Código da pessoa logada
            $nrNivelUsuario = $request->auth->nr_nivel; // Código da pessoa logada

            // Filtros
            $nomePessoa = $request->get('nome_pessoa');
            $nomeFilter = !empty($nomePessoa) ? "AND tp.nome_pessoa LIKE :nomePessoa" : "";

            $query = "
                WITH RECURSIVE relacionados AS (
                    -- Primeiro nível, o usuário logado
                    SELECT tp.nr_sequencial, tp.id_parent, tp.nr_nivel
                    FROM tab_pessoas tp
                    WHERE tp.nr_sequencial = :codigoUsuario1
        
                    UNION ALL
        
                    -- Recursivamente, pega os descendentes
                    SELECT tp.nr_sequencial, tp.id_parent, tp.nr_nivel
                    FROM tab_pessoas tp
                    INNER JOIN relacionados r ON r.nr_sequencial = tp.id_parent
                )
                SELECT 
                    tp.nr_sequencial,
                    tp.nome_pessoa,
                    tp.sexo_pessoa,
                    tp.dt_nascimento,
                    tp.nr_nivel,
                    tp.entrou_em,
                    tpm.dt_batismo,
                    tp2.nome_pessoa as nome_lider,
                    tpm.tp_participacao
                FROM tab_pessoas tp
                LEFT JOIN tab_pessoa_ministerio tpm ON tp.nr_sequencial = tpm.nr_seq_pessoa
                LEFT JOIN tab_pessoas tp2 ON tp2.nr_sequencial = tpm.nr_seq_lider
                WHERE tp.st_ativo = 'true'
                AND tp.nr_seq_filial IN ($filiaisStr)
                AND (
                    tp.nr_nivel > :nrNivelUsuario
                    OR tp.nr_sequencial = :codigoUsuario2
                )
                AND tp.nr_sequencial IN (
                    SELECT nr_sequencial FROM relacionados
                )
                $nomeFilter
                ORDER BY tp.nome_pessoa ASC
            ";

            $bindings = [
                'codigoUsuario1' => $codigoUsuario,
                'codigoUsuario2' => $codigoUsuario,
                'nrNivelUsuario' => $nrNivelUsuario
            ];

            // Adiciona o parâmetro do filtro de nome, se necessário
            if (!empty($nomePessoa)) {
                $bindings['nomePessoa'] = "%{$nomePessoa}%";
            }

            $lista_pessoas = DB::select(DB::raw($query), $bindings);
            // Se precisar paginar os resultados, você pode fazer manualmente:
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $collection = collect(array_values($lista_pessoas));

            $paginatedResults = new LengthAwarePaginator(
                $collection->forPage($currentPage, $perPage),
                $collection->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json($paginatedResults, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createMinisterio(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_pessoa_ministerio')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($request->nr_seq_lider)) {
                $sql_filial = DB::table('tab_pessoas')
                    ->select('nr_seq_filial', 'id_parent', 'nr_nivel')
                    ->where('nr_sequencial', $request->nr_seq_lider)
                    ->first();

                // Alterar na tab_pessoas a filial da pessoa
                $sql_tab_pessoas = DB::table('tab_pessoas')->where('nr_sequencial', $request->id_user)
                    ->update([
                        'nr_seq_filial' => $sql_filial->nr_seq_filial,
                        'id_parent' => $sql_filial->id_parent + 1,
                        'nr_nivel' => $sql_filial->nr_nivel + 1
                    ]);

                // Dar acesso pela tab_pessoa_filial
                $sql_pessoa_filial = DB::table('tab_pessoa_filial')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
                    'nr_seq_filial' => $sql_filial->nr_seq_filial
                ]);
            }

            if (!empty($nr_sequencial)) {
                $insert_ministerio = DB::table('tab_pessoa_ministerio')
                    ->where('nr_seq_pessoa', $request->id_user)
                    ->update([
                        'membro_novo' => $request->membro_novo,
                        'convidado_por' => $request->convidado_por,
                        'membro_batizado' => $request->membro_batizado,
                        'dt_batismo' => $request->dt_batismo,
                        'tp_participacao' => $request->tp_participacao,
                        'participacao_desde' => $request->participacao_desde,
                        'possui_lider' => $request->possui_lider,
                        'nr_seq_lider' => $request->nr_seq_lider,
                    ]);
            } else {
                $insert_ministerio = DB::table('tab_pessoa_ministerio')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
                    'membro_novo' => $request->membro_novo,
                    'convidado_por' => $request->convidado_por,
                    'membro_batizado' => $request->membro_batizado,
                    'dt_batismo' => $request->dt_batismo,
                    'tp_participacao' => $request->tp_participacao,
                    'participacao_desde' => $request->participacao_desde,
                    'possui_lider' => $request->possui_lider,
                    'nr_seq_lider' => $request->nr_seq_lider,
                ]);
            }

            return response()->json($insert_ministerio, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createSaude(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_pessoa_saude')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($nr_sequencial)) {
                $insert_contato = DB::table('tab_pessoa_saude')
                    ->where('nr_seq_pessoa', $request->id_user)
                    ->update([
                        'tp_sangue' => $request->tp_sangue,
                        'tp_doador' => $request->tp_doador,
                    ]);
            } else {
                $insert_contato = DB::table('tab_pessoa_saude')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
                    'tp_sangue' => $request->tp_sangue,
                    'tp_doador' => $request->tp_doador,
                ]);
            }

            return response()->json($insert_contato, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createSociais(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_sociais')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($nr_sequencial)) {
                $insert_sociais = DB::table('tab_sociais')
                    ->where('nr_seq_pessoa', $request->id_user)
                    ->update([
                        'bio_pessoa' => $request->bio,
                        'facebook' => $request->facebook,
                        'instagram' => $request->instagram,
                    ]);
            } else {
                $insert_sociais = DB::table('tab_sociais')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
                    'bio_pessoa' => $request->bio,
                    'facebook' => $request->facebook,
                    'facebook' => $request->facebook,
                    'instagram' => $request->instagram,
                ]);
            }

            return response()->json($insert_sociais, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createDocumentos(Request $request)
    {
        try {

            $nr_sequencial = DB::table('tab_pessoa_documento')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($nr_sequencial)) {
                $insert_contato = DB::table('tab_pessoa_documento')
                    ->where('nr_seq_pessoa', $request->id_user)
                    ->update([
                        'cpf' => $request->cpf,
                        'rg' => $request->rg,
                    ]);
            } else {
                $insert_contato = DB::table('tab_pessoa_documento')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
                    'cpf' => $request->cpf,
                    'rg' => $request->rg,
                ]);
            }

            return response()->json($insert_contato, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createProfissao(Request $request)
    {
        try {
            $nr_sequencial = DB::table('tab_pessoa_profissao')
                ->where('nr_seq_pessoa', $request->id_user)
                ->first();

            if (!empty($nr_sequencial)) {
                $insert_contato = DB::table('tab_pessoa_profissao')
                    ->where('nr_seq_pessoa', $request->id_user)
                    ->update([
                        'ds_profissao' => $request->ds_profissao,
                    ]);
            } else {
                $insert_contato = DB::table('tab_pessoa_profissao')->insertGetId([
                    'nr_seq_pessoa' => $request->id_user,
                    'ds_profissao' => $request->ds_profissao,
                ]);
            }

            return response()->json($insert_contato, 200);
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

    public function getUserEdit(Request $request, $id_user)
    {
        try {
            $pessoaProfile = DB::table('tab_pessoas')
                ->where('nr_sequencial', $id_user)
                ->first();

            $pessoaProfile->id_conjuge = DB::table('tab_pessoas')
                ->select('nr_sequencial', 'nome_pessoa')
                ->where('nr_sequencial', $id_user)
                ->first();

            $pessoaContato = DB::table('tab_pessoa_contato')
                ->where('nr_seq_pessoa', $id_user)
                ->first();

            $pessoaMinisterio = DB::table('tab_pessoa_ministerio')
                ->where('nr_seq_pessoa', $id_user)
                ->first();

            if (!empty($pessoaMinisterio)) {
                $pessoaMinisterio->nr_seq_lider = DB::table('tab_pessoas')
                    ->select('nr_sequencial', 'nome_pessoa')
                    ->where('nr_sequencial', $pessoaMinisterio->nr_seq_lider)
                    ->first();
            }
            if (!empty($pessoaMinisterio)) {
                $pessoaMinisterio->convidado_por = DB::table('tab_pessoas')
                    ->select('nr_sequencial', 'nome_pessoa')
                    ->where('nr_sequencial', $pessoaMinisterio->convidado_por)
                    ->first();
            }


            $pessoaSaude = DB::table('tab_pessoa_saude')
                ->where('nr_seq_pessoa', $id_user)
                ->first();

            $pessoaDocumentos = DB::table('tab_pessoa_documento')
                ->where('nr_seq_pessoa', $id_user)
                ->first();

            $pessoaProfissao = DB::table('tab_pessoa_profissao')
                ->where('nr_seq_pessoa', $id_user)
                ->first();

            $pessoaSociais = DB::table('tab_sociais')
                ->where('nr_seq_pessoa', $id_user)
                ->first();

            return response()->json([
                'pessoaProfile' => $pessoaProfile,
                'pessoaContato' => $pessoaContato,
                'pessoaMinisterio' => $pessoaMinisterio,
                'pessoaSaude' => $pessoaSaude,
                'pessoaDocumentos' => $pessoaDocumentos,
                'pessoaProfissao' => $pessoaProfissao,
                'pessoaSociais' => $pessoaSociais,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
