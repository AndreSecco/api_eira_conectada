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
}
