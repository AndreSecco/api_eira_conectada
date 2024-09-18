<?php

use App\Http\Controllers\Cadastros\Grupos\GruposController;
use App\Http\Controllers\Cadastros\Pessoas\PessoasController;
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

Route::group(['middleware' => 'jwt'], function () {
    Route::post('/cadastros/createPessoa', [PessoasController::class, 'createPessoa']);
    Route::post('/cadastros/createContato', [PessoasController::class, 'createContato']);
    Route::post('/cadastros/createMinisterio', [PessoasController::class, 'createMinisterio']);
    Route::post('/cadastros/createSaude', [PessoasController::class, 'createSaude']);
    Route::post('/cadastros/createDocumentos', [PessoasController::class, 'createDocumentos']);
    Route::post('/cadastros/createProfissao', [PessoasController::class, 'createProfissao']);
    Route::get('/cadastros/getUserEdit/{id_user}', [PessoasController::class, 'getUserEdit']);
    Route::get('/cadastros/getListaPessoa/{desc_pessoa}', [PessoasController::class, 'getListaPessoa']);
    Route::get('/cadastros/getListaPessoasInicio', [PessoasController::class, 'getListaPessoasInicio']);
    Route::post('/cadastros/inativarCadastro', [PessoasController::class, 'inativarCadastro']);
    Route::post('/cadastros/uploadFileUser/{id_user}', [PessoasController::class, 'uploadFiles']);

    Route::post('/cadastros/createGrupo', [GruposController::class, 'createGrupo']);

    Route::post('/cadastros/grupos/createContato', [GruposController::class, 'createContato']);
    Route::get('/cadastros/grupos/getGrupoId/{id_grupo}', [GruposController::class, 'getGrupoId']);
});
