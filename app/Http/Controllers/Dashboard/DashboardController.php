<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{

    public function fetchTextCardsData(Request $request)
    {
        try {
            $selectPessoas = DB::table('tab_pessoas')
                ->where('st_ativo', 'true')
                ->count();

            $selectGrupos = DB::table('tab_grupos')
                ->count();

            return response()->json([
                'pessoasCadastradas' => $selectPessoas,
                'selectGrupos' => $selectGrupos
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchPresentesCelulas(Request $request)
    {
        try {
            $pessoas_vinculadas = DB::select('SELECT COUNT(distinct(tgm.nr_sequencial)) as pessoas_vinculadas
                                            FROM tab_grupo_membros tgm');

            // $pessoas_presentes = DB::select('SELECT count(distinct(tcp.nr_seq_pessoa)) as pessoas_presentes from tab_celula_presentes tcp 
            // left join tab_celulas tc on tcp.nr_seq_celula = tc.nr_sequencial 
            // left join tab_grupos tg on tc.nr_seq_grupo = tg.nr_sequencial');

            return response()->json([
                'fetchPessoasVinculadas' => $pessoas_vinculadas,
                // 'fetchPessoasPresentes' => $pessoas_presentes,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchEvolucaoCelula(Request $request)
    {
        try {


            $evolucao_celula = DB::select('WITH membros_por_semana AS (
                SELECT 
                    WEEK(created_at) AS semana,
                    COUNT(nr_seq_pessoa) AS total_membros
                    FROM 
                        tab_grupo_membros
                    WHERE 
                        nr_seq_grupo = 2
                    GROUP BY 
                        WEEK(created_at)
                )

                SELECT 
                    semana,
                    SUM(total_membros) OVER (ORDER BY semana) AS total_acumulado
                FROM 
                    membros_por_semana
                ORDER BY 
                semana;');

            return response()->json([
                'evolucaoCelula' => $evolucao_celula,
                // 'fetchPessoasPresentes' => $pessoas_presentes,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchTotalBatizados(Request $request)
    {
        try {
            // $total_batizados = DB::select("SELECT
            //         SUM(CASE WHEN sexo_pessoa = 'M' AND DATEDIFF(CURDATE(), dt_nascimento) / 365.25 <= 25 THEN 1 ELSE 0 END) AS jovens_homens,
            //         SUM(CASE WHEN sexo_pessoa = 'F' AND DATEDIFF(CURDATE(), dt_nascimento) / 365.25 <= 25 THEN 1 ELSE 0 END) AS jovens_mulheres,
            //         SUM(CASE WHEN sexo_pessoa = 'M' AND DATEDIFF(CURDATE(), dt_nascimento) / 365.25 > 25 THEN 1 ELSE 0 END) AS homens,
            //         SUM(CASE WHEN sexo_pessoa  = 'F' AND DATEDIFF(CURDATE(), dt_nascimento) / 365.25 > 25 THEN 1 ELSE 0 END) AS mulheres
            //     FROM
            //         tab_pessoas tp
            //     left join tab_pessoa_ministerio tpm 
            //     on tpm.nr_seq_pessoa = tp.nr_sequencial
            //     WHERE
            //         tpm.membro_batizado  ='true'
            //     AND tpm.tp_participacao <> 'Convidado';");

            $total_batizados = DB::select("SELECT 
                            SUM(
                                CASE 
                                    -- Jovens homens por idade ou por estar em um grupo de Jovens
                                    WHEN tp.sexo_pessoa = 'M' AND (DATEDIFF(CURDATE(), tp.dt_nascimento) / 365.25 <= 25 OR tg.tipo_grupo = 'Jovens') 
                                    THEN 1 
                                    ELSE 0 
                                END
                            ) AS jovens_homens,
                            
                            SUM(
                                CASE 
                                    -- Jovens mulheres por idade ou por estar em um grupo de Jovens
                                    WHEN tp.sexo_pessoa = 'F' AND (DATEDIFF(CURDATE(), tp.dt_nascimento) / 365.25 <= 25 OR tg.tipo_grupo = 'Jovens') 
                                    THEN 1 
                                    ELSE 0 
                                END
                            ) AS jovens_mulheres,

                            SUM(
                                CASE 
                                    -- Homens adultos que não estão em grupos de Jovens
                                    WHEN tp.sexo_pessoa = 'M' AND DATEDIFF(CURDATE(), tp.dt_nascimento) / 365.25 > 25 AND tg.tipo_grupo <> 'Jovens' 
                                    THEN 1 
                                    ELSE 0 
                                END
                            ) AS homens,

                            SUM(
                                CASE 
                                    -- Mulheres adultas que não estão em grupos de Jovens
                                    WHEN tp.sexo_pessoa = 'F' AND DATEDIFF(CURDATE(), tp.dt_nascimento) / 365.25 > 25 AND tg.tipo_grupo <> 'Jovens' 
                                    THEN 1 
                                    ELSE 0 
                                END
                            ) AS mulheres
                        FROM 
                            tab_pessoas tp
                        LEFT JOIN 
                            tab_pessoa_ministerio tpm ON tpm.nr_seq_pessoa = tp.nr_sequencial
                        LEFT JOIN 
                            tab_grupo_membros tgm ON tgm.nr_seq_pessoa = tp.nr_sequencial -- Junção com membros dos grupos
                        LEFT JOIN 
                            tab_grupos tg ON tg.nr_sequencial = tgm.nr_seq_grupo -- Junção com a tabela de grupos para verificar o tipo de grupo
                        WHERE 
                            tpm.membro_batizado = 'true' 
            AND tpm.tp_participacao <> 'Convidado';");

            return response()->json([
                'totalBatizados' => $total_batizados,
                // 'fetchPessoasPresentes' => $pessoas_presentes,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchNaoBatizados(Request $request)
    {
        try {
            $total_nao_batizados = DB::select("SELECT 
                                COUNT(*) AS nao_batizados
                            FROM 
                                tab_pessoa_ministerio
                            WHERE 
                                membro_batizado = false OR membro_batizado IS NULL OR membro_batizado = '';");

            return response()->json([
                'totalNaoBatizados' => $total_nao_batizados,
                // 'fetchPessoasPresentes' => $pessoas_presentes,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchAniversariantes(Request $request)
    {
        try {
            $total_nao_batizados = DB::select("SELECT 
                                    tp.nr_sequencial,
                                    tp.imagem_perfil,
                                    tp.nome_pessoa, -- ou qualquer outro campo que você queira exibir
                                    tp.dt_nascimento,
                                    tp.sexo_pessoa,
                                    tpm.membro_batizado,
                                    tp2.nome_pessoa as nome_lider
                                FROM 
                                    tab_pessoas tp
                                LEFT JOIN tab_pessoa_ministerio tpm on tp.nr_sequencial = tpm.nr_seq_pessoa
                                LEFT JOIN tab_pessoas tp2 on tpm.nr_seq_lider = tp2.nr_sequencial
                                WHERE 
                                    MONTH(tp.dt_nascimento) = MONTH(CURDATE()) -- Filtra pelo mês atual
                                    AND tp.dt_nascimento IS NOT NULL; -- Garante que a data de aniversário não seja nula");

            return response()->json([
                'totalNaoBatizados' => $total_nao_batizados,
                // 'fetchPessoasPresentes' => $pessoas_presentes,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchLideresMes(Request $request)
    {
        try {
            $lideres_mes = DB::select("SELECT 
                                                p.imagem_perfil,                    
                                                p.nome_pessoa, -- ou qualquer outro campo que você queira exibir da tabela tab_pessoas
                                                pm.tp_participacao,
                                                p.created_at -- ou o campo que armazena a data de cadastro
                                            FROM 
                                                tab_pessoas p
                                            JOIN 
                                                tab_pessoa_ministerio pm ON p.nr_sequencial = pm.nr_seq_pessoa -- ajustando conforme a chave estrangeira
                                            WHERE 
                                                pm.tp_participacao = 'Lider' -- Filtra apenas os líderes
                                                AND MONTH(p.created_at) = MONTH(CURDATE()) -- Filtra pelo mês atual
                                                AND YEAR(p.created_at) = YEAR(CURDATE()) -- Filtra pelo ano atual");

            return response()->json([
                'lideresMes' => $lideres_mes,
                // 'fetchPessoasPresentes' => $pessoas_presentes,
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }
}
