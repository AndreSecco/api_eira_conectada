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
            $select_frequencia = DB::select('SELECT g.nome_grupo AS nome_do_grupo,
                                                    COUNT(DISTINCT gm.nr_seq_pessoa) AS total_cadastrados,
                                                    COUNT(DISTINCT cp.nr_seq_pessoa) AS total_presentes,
                                                    (COUNT(DISTINCT gm.nr_seq_pessoa) - COUNT(DISTINCT cp.nr_seq_pessoa)) AS total_faltaram,
                                                    COUNT(DISTINCT CASE WHEN cp.nr_seq_tp_membro = 6 THEN cp.nr_seq_pessoa END) AS quantidade_convidados,
                                                    DATE_SUB(DATE(c.data_celula), INTERVAL WEEKDAY(c.data_celula) DAY) AS data_do_primeiro_dia_da_semana
                                                FROM 
                                                    tab_grupos g
                                                JOIN 
                                                    tab_grupo_membros gm ON gm.nr_seq_grupo = g.nr_sequencial
                                                LEFT JOIN 
                                                    tab_celulas c ON c.nr_seq_grupo = g.nr_sequencial
                                                LEFT JOIN 
                                                    tab_celula_presentes cp ON cp.nr_seq_celula = c.nr_sequencial
                                                WHERE 
                                                    c.data_celula >= DATE_SUB(CURDATE(), INTERVAL 7 WEEK)  -- Últimas 7 semanas
                                                GROUP BY 
                                                    g.nome_grupo,
                                                    c.data_celula,
                                                    YEARWEEK(c.data_celula, 1)  -- Agrupa por semana, começando na segunda-feira
                                                ORDER BY 
                                                    c.data_celula DESC;');

            return response()->json([
                'selectFrequencia' => $select_frequencia
            ], 200);
        } catch (Exception $error) {
            return response()->json($error->getMessage(), 400);
        }
    }

    public function fetchFaixaEtaria(Request $request)
    {
        try {


            $faixa_etaria = DB::select("SELECT 
                                            CASE
                                                WHEN TIMESTAMPDIFF(YEAR, p.dt_nascimento, CURDATE()) BETWEEN 0 AND 14 THEN '0-14'
                                                WHEN TIMESTAMPDIFF(YEAR, p.dt_nascimento, CURDATE()) BETWEEN 15 AND 29 THEN '15-29'
                                                WHEN TIMESTAMPDIFF(YEAR, p.dt_nascimento, CURDATE()) BETWEEN 30 AND 59 THEN '30-59'
                                                ELSE '+60'
                                            END AS faixa_etaria,
                                            COUNT(p.nr_sequencial) AS total_pessoas
                                        FROM 
                                            tab_pessoas p
                                        GROUP BY 
                                            faixa_etaria
                                        ORDER BY 
                                            faixa_etaria;
                                        ");

            return response()->json([
                'faixaEtaria' => $faixa_etaria,
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
