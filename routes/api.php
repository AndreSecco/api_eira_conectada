<?php

use App\Http\Controllers\Cadastros\Pessoas\PessoasController;


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

Route::get('/teste', function () {
    return 'teste';
});

Route::post('/cadastros/createPessoa', [PessoasController::class, 'createPessoa']);
Route::post('/cadastros/createContato', [PessoasController::class, 'createContato']);
Route::post('/cadastros/createMinisterio', [PessoasController::class, 'createMinisterio']);
Route::post('/cadastros/createSaude', [PessoasController::class, 'createSaude']);
Route::post('/cadastros/createDocumentos', [PessoasController::class, 'createDocumentos']);
Route::post('/cadastros/createProfissao', [PessoasController::class, 'createProfissao']);
Route::get('/cadastros/getUserEdit/{id_user}', [PessoasController::class, 'getUserEdit']);
Route::get('/cadastros/getListaPessoa/{desc_pessoa}', [PessoasController::class, 'getListaPessoa']);
Route::get('/cadastros/getListaPessoasInicio', [PessoasController::class, 'getListaPessoasInicio']);

