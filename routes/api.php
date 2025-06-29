<?php

use App\Http\Controllers\Administrativo\CultosController;
use App\Http\Controllers\Administrativo\EmpresaController;
use App\Http\Controllers\Administrativo\FilialController;
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
Route::get('/getEmpresas', [UserController::class, 'getEmpresas']);
Route::get('/getFiliaisEmpresa', [UserController::class, 'getFiliaisEmpresa']);


Route::group(['middleware' => 'admin'], function () {
    Route::post('/empresa/registraEmpresa', [EmpresaController::class, 'registraEmpresa']);
    Route::get('/empresa/getEmpresaId/{id_empresa}', [EmpresaController::class, 'getEmpresaId']);
    Route::get('/empresa/getListaEmpresas', [EmpresaController::class, 'getListaEmpresas']);
});

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
    Route::post('/cadastros/createEvento', [GruposController::class, 'createEvento']);
    Route::post('/cadastros/registrarIngresso', [GruposController::class, 'registrarIngresso']);
    

    Route::get('/cadastros/getListaGruposInicio', [GruposController::class, 'getListaGruposInicio']);
    Route::get('/cadastros/getListaEventosInicio', [GruposController::class, 'getListaEventosInicio']);
    Route::post('/cadastros/grupos/createContato', [GruposController::class, 'createContato']);
    Route::get('/cadastros/grupos/getGrupoId/{id_grupo}', [GruposController::class, 'getGrupoId']);
    Route::get('/cadastros/eventos/getEventoId/{id_evento}', [GruposController::class, 'getEventoId']);
    Route::post('/inserirMembroCelula', [GruposController::class, 'inserirMembroCelula']);
    Route::post('/deleteMembroCelula', [GruposController::class, 'deleteMembroCelula']);
    
    
    
    Route::get('/celulas/getCelulasLider/{id_lider_celula}', [CelulasController::class, 'getCelulasLider']);
    Route::get('/celulas/buscaMembros/{id_grupo}', [CelulasController::class, 'buscaMembros']);
    Route::post('/celulas/finalizarCelula', [CelulasController::class, 'finalizarCelula']);


    Route::get('/dashboard/fetchTextCardsData', [DashboardController::class, 'fetchTextCardsData']);
    Route::get('/dashboard/fetchPresentesCelulas', [DashboardController::class, 'fetchPresentesCelulas']);
    Route::get('/dashboard/fetchFaixaEtaria', [DashboardController::class, 'fetchFaixaEtaria']);
    Route::get('/dashboard/fetchTotalBatizados', [DashboardController::class, 'fetchTotalBatizados']);
    Route::get('/dashboard/fetchNaoBatizados', [DashboardController::class, 'fetchNaoBatizados']);
    Route::get('/dashboard/fetchAniversariantes', [DashboardController::class, 'fetchAniversariantes']);
    Route::get('/dashboard/fetchLideresMes', [DashboardController::class, 'fetchLideresMes']);
    
    
    Route::post('/cultos/registrarCulto', [CultosController::class, 'registrarCulto']);
    Route::post('/cultos/uploadAnexoCulto/{id_culto}', [CultosController::class, 'uploadAnexoCulto']);
    Route::get('/cultos/getConfirmacaoCulto', [CultosController::class, 'getConfirmacaoCulto']);
    Route::post('/cultos/confirmarCulto', [CultosController::class, 'confirmarCulto']);
    Route::get('/cultos/getOfertasCelulas', [CultosController::class, 'getOfertasCelulas']);
    
    
    Route::post('/filial/registrarFilial', [FilialController::class, 'registrarFilial']);
    Route::get('/filial/getFilialId/{id_filial}', [FilialController::class, 'getFilialId']);
    Route::get('/filial/getListaFiliais', [FilialController::class, 'getListaFiliais']);
    
    Route::post('/empresa/alterarFilial', [FilialController::class, 'alterarFilial']);
    
});
