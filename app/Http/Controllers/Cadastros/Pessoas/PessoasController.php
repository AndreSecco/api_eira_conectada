<?php

namespace App\Http\Controllers\Cadastros\Pessoas;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PessoasController extends Controller
{
    public function createPessoa(Request $request)
    {
        try {
            $files = $request->file('files');
            foreach ($files as $file) {
                $fileName = $file->getClientOriginalName() . strtotime("now") . '.' . $file->extension();
            }
            return response()->json($files, 200);

            $data = $this->validate($request, [
                'nome_pessoa' => 'required|string',
                'sexo_pessoa' => 'required|string',
                'data_nascimento' => 'required|date',
                'estado_civil' => 'required|string',
                'entrou_em' => 'required|date',
            ]);

            if ($request->id_user) {
                DB::table('tab_pessoas')
                    ->where('nr_sequencial', $request->id_user)
                    ->update([
                        'nome_pessoa' => $data['nome_pessoa'],
                        'sexo_pessoa' => $data['sexo_pessoa'],
                        'nr_nivel'    => 1,
                        'id_parent'   => 1,
                        'dt_nascimento' => $data['data_nascimento'],
                        'entrou_em' => $data['entrou_em'],
                        'estado_civil' => $data['estado_civil'],
                        'nr_seq_filial' => 1,
                        'id_conjuge' => $request->id_conjuge,
                        'user_cadastro' => 1,
                        'whatsapp' => $request->whatsapp,
                        'email' => $request->email,
                        'bio_pessoa' => $request->bio_pessoa,
                        'facebook' => $request->facebook,
                        'instagram' => $request->instagram,
                    ]);

                $insert_pessoa = $request->id_user;
            } else {
                $insert_pessoa = DB::table('tab_pessoas')->insertGetId([
                    'nome_pessoa' => $data['nome_pessoa'],
                    'sexo_pessoa' => $data['sexo_pessoa'],
                    'nr_nivel'    => 1,
                    'id_parent'   => 1,
                    'dt_nascimento' => $data['data_nascimento'],
                    'entrou_em' => $data['entrou_em'],
                    'estado_civil' => $data['estado_civil'],
                    'nr_seq_filial' => 1,
                    'id_conjuge' => $request->id_conjuge,
                    'user_cadastro' => 1,
                    'whatsapp' => $request->whatsapp,
                    'email' => $request->email,
                    'bio_pessoa' => $request->bio_pessoa,
                    'facebook' => $request->facebook,
                    'instagram' => $request->instagram,
                ]);
            }

            if ($request->senha) {
                DB::table('tab_users')->insertGetId([
                    'username' => $request->email,
                    'password' => $request->senha,
                    'nr_seq_pessoa' => $insert_pessoa,
                    'st_ativo' => 'S'
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

            return response()->json($insert_contato, 200);
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
                ->paginate($perPage);

            // $lista_pessoas = $lista_pessoas->paginate($request->get('per_page'));

            return response()->json($lista_pessoas, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createMinisterio(Request $request)
    {
        try {
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

            return response()->json($insert_ministerio, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createSaude(Request $request)
    {
        try {
            $insert_contato = DB::table('tab_pessoa_saude')->insertGetId([
                'nr_seq_pessoa' => $request->id_user,
                'tp_sangue' => $request->tp_sangue,
                'tp_doador' => $request->tp_doador,
            ]);

            return response()->json($insert_contato, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createDocumentos(Request $request)
    {
        try {
            $insert_contato = DB::table('tab_pessoa_documento')->insertGetId([
                'nr_seq_pessoa' => $request->id_user,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
            ]);

            return response()->json($insert_contato, 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function createProfissao(Request $request)
    {
        try {
            $insert_contato = DB::table('tab_pessoa_profissao')->insertGetId([
                'nr_seq_pessoa' => $request->id_user,
                'ds_profissao' => $request->ds_profissao,
            ]);

            return response()->json($insert_contato, 200);
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

            return response()->json([
                'pessoaProfile' => $pessoaProfile,
                'pessoaContato' => $pessoaContato,
                'pessoaMinisterio' => $pessoaMinisterio,
                'pessoaSaude' => $pessoaSaude,
                'pessoaDocumentos' => $pessoaDocumentos,
                'pessoaProfissao' => $pessoaProfissao
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
