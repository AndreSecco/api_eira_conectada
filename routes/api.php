<?php

use App\Http\Controllers\Cadastros\Grupos\GruposController;
use App\Http\Controllers\Cadastros\Pessoas\PessoasController;
use App\Http\Controllers\Celulas\CelulasController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/', function () {
    return 'teste';
});
Route::post('user/authenticate', [UserController::class, 'authenticate']);
Route::post('/user/testeController', [UserController::class, 'testeController']);
Route::post('/cadastros/registrarNovoUsuario', [PessoasController::class, 'createPessoa']);
Route::post('/recuperar-senha', [UserController::class, 'sendEmail']);
Route::post('/redefinir-senha', [UserController::class, 'redefinirSenha']);


Route::group(['middleware' => 'jwt'], function () {
    Route::post('/cadastros/createPessoa', [PessoasController::class, 'createPessoa']);
    Route::post('/cadastros/createContato', [PessoasController::class, 'createContato']);
    Route::post('/cadastros/createMinisterio', [PessoasController::class, 'createMinisterio']);
    Route::post('/cadastros/createSaude', [PessoasController::class, 'createSaude']);
    Route::post('/cadastros/createSociais', [PessoasController::class, 'createSociais']);
    Route::post('/cadastros/createDocumentos', [PessoasController::class, 'createDocumentos']);
    Route::post('/cadastros/createProfissao', [PessoasController::class, 'createProfissao']);
    Route::get('/cadastros/getUserEdit/{id_user}', [PessoasController::class, 'getUserEdit']);
    Route::get('/cadastros/getListaPessoa/{desc_pessoa}', [PessoasController::class, 'getListaPessoa']);
    Route::get('/cadastros/getListaPessoasInicio', [PessoasController::class, 'getListaPessoasInicio']);
    Route::post('/cadastros/inativarCadastro', [PessoasController::class, 'inativarCadastro']);
    Route::post('/cadastros/uploadFileUser/{id_user}', [PessoasController::class, 'uploadFiles']);
    
    Route::post('/cadastros/createGrupo', [GruposController::class, 'createGrupo']);
    

    Route::get('/cadastros/getListaGruposInicio', [GruposController::class, 'getListaGruposInicio']);
    Route::post('/cadastros/grupos/createContato', [GruposController::class, 'createContato']);
    Route::get('/cadastros/grupos/getGrupoId/{id_grupo}', [GruposController::class, 'getGrupoId']);
    Route::post('/inserirMembroCelula', [GruposController::class, 'inserirMembroCelula']);
    Route::post('/deleteMembroCelula', [GruposController::class, 'deleteMembroCelula']);
    
    
    
    Route::get('/celulas/getCelulasLider/{id_lider_celula}', [CelulasController::class, 'getCelulasLider']);
    Route::get('/celulas/buscaMembros/{id_grupo}', [CelulasController::class, 'buscaMembros']);
    Route::post('/celulas/finalizarCelula', [CelulasController::class, 'finalizarCelula']);


    Route::get('/dashboard/fetchTextCardsData', [DashboardController::class, 'fetchTextCardsData']);
    Route::get('/dashboard/fetchPresentesCelulas', [DashboardController::class, 'fetchPresentesCelulas']);
    Route::get('/dashboard/fetchEvolucaoCelula', [DashboardController::class, 'fetchEvolucaoCelula']);
    Route::get('/dashboard/fetchTotalBatizados', [DashboardController::class, 'fetchTotalBatizados']);
    Route::get('/dashboard/fetchNaoBatizados', [DashboardController::class, 'fetchNaoBatizados']);
    Route::get('/dashboard/fetchAniversariantes', [DashboardController::class, 'fetchAniversariantes']);
    Route::get('/dashboard/fetchLideresMes', [DashboardController::class, 'fetchLideresMes']);
});
