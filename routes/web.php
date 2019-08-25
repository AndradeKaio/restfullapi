<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function(){

    // detalhes do veiculo
    Route::get('veiculo/{id}', 'QueryController@getVeiculo')->name('api.get_veiculo');

    //recupera modelos de uma determinada marca
    Route::get('marca/{idMarca}/modelos', 'QueryController@getModelos')->name('api.get_modelos');


    
    Route::prefix('tipoVeiculo')->group(function(){
        // recupera todas as marcas disponiveis para o tipo de veiculo
        Route::get('{tipoVeiculo}/marcas', 'QueryController@getMarcas')->name('api.get_marcas');

        // recupera todas as cidades disponiveis para o tipo de veiculo
        Route::get('{tipoVeiculo}/cidades', 'QueryController@getCidades')->name('api.get_cidades');

        

    });
    Route::post('/filtro', 'QueryController@filtro')->name('api.filtro');
});
