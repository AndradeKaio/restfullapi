<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as Client;
use Weidner\Goutte\GoutteFacade as Goutte;
use App\Veiculo;

class QueryController extends Controller
{
    const NOVO_VALUE = 0;
    const USADO_VALUE = 1;

    const CARRO_VALUE = 1;
    const CAMINHAO_VALUE = 2;
    const MOTO_VALUE = 3;


    /**
     * Recebe o tipo do veiculo como parametro e retorna todas marcas
     * disponiveis para aquele tipo.
     * @param tipoVeiculo : int
     * @return result : json
     */
     
    public function getMarcas($tipoVeiculo){
        $result = [];
        if(empty($tipoVeiculo) || $tipoVeiculo > 3 || $tipoVeiculo < 1){
            $result['success'] = false;
            $result['error'] = 'Favor fornecer um valor valido para tipo veiculo! ' . self::CARRO_VALUE . ' - CARRO; ' . self::CAMINHAO_VALUE . ' - CAMINHAO; ' . self::MOTO_VALUE . ' - MOTO';

        }else{
            $client = new Client();
            $out = $client->request('GET', 'https://www.seminovosbh.com.br/marcas/buscamarca/tipo/' . $tipoVeiculo);
            if(intval($out->getStatusCode()) == 200){
                $result['success'] = true;
                $result['data'] = json_encode($out->getBody()->getContents(), true);
            }else{
                $result['success'] = false;
                $result['error'] = 'Erro inesperado!';
            }
        }
        unset($client);
        return $result;
    }



    /**
     * Recebe o id da marca e retorna todos os veiculos disponiveis para aquela marca.
     * @param idMarca : int
     * @return result : json
     */
    public function getModelos($idMarca){
        $result = [];
        if(empty($idMarca)){
            $result['success'] = false;
            $result['error'] = 'Favor fornecer o codigo da marca!';

        }else{
            $client = new Client();
            $out = $client->request('GET', 'https://www.seminovosbh.com.br/json/modelos/buscamodelo/marca/' . $idMarca . '/data.js');
            if(intval($out->getStatusCode()) == 200){
                $result['success'] = true;
                $result['data'] = json_encode($out->getBody()->getContents(), true);
            }else{
                $result['success'] = false;
                $result['error'] = 'Erro inesperado!';
            }
        }
        unset($client);
        return $result;
    }


    /**
     * Recupera todas as cidades disponiveis para um tipo de veiculo
     * @param tipoVeiculo - id do tipo de veiculo [1~3]
     * @return array - json com as cidades
     */
    public function getCidades($tipoVeiculo){
        $result = [];
        $result['success'] = false;

        if(empty($tipoVeiculo) || $tipoVeiculo > 3 || $tipoVeiculo < 1){
            $result['error'] = 'Favor fornecer um valor valido para tipo veiculo! ' . self::CARRO_VALUE . ' - CARRO; ' . self::CAMINHAO_VALUE . ' - CAMINHAO; ' . self::MOTO_VALUE . ' - MOTO';

        }else{
            $client = new Client();
            $out = $client->request('GET', 'https://www.seminovosbh.com.br/json/index/busca-cidades/veiculo/'. $tipoVeiculo .'/marca/0/modelo/0/cidade/0/data.js');
            if(intval($out->getStatusCode()) == 200){
                $result['success'] = true;
                $result['data'] = json_encode($out->getBody()->getContents(), true);
            }else{
                $result['error'] = 'Erro inesperado!';
            }
        }
        unset($client);
        return $result;
    }


    /**
     * Recupera informacoes do veiculo caso exista.
     * @param request - get request
     * @param veiculoId - id informado na busca
     */
    public function getVeiculo(Request $request, $veiculoId){

        $result = [];
        $result['success'] = false;


        if(empty($veiculoId)){
            $result['error'] = 'Favor fornecer um id do veiculo!';
        }else{
            $requestResult = Goutte::request('GET', 'https://www.seminovosbh.com.br/comprar////' . $veiculoId);
            
            // checa se o request encontrou algum veiculo
            if(!$requestResult->filter('#conteudoVeiculo')->count()){
                $result['error'] = 'Veiculo nao encontrado!';
            }else{
                $veiculo = new Veiculo($requestResult, $veiculoId);
                // dd($veiculo->to_json());
                $result['success'] = true;
                $result['data'] = $veiculo->to_json();
            }
        }
        unset($veiculo);
        return $result;
    }


    /**
     * 
     * https://www.seminovosbh.com.br/resultadobusca/index/veiculo/1/estado/1/marca/75/modelo/cidade/25/valor1/1000/valor2/100000/ano1/2005/ano2/2019/usuario/todos/
     * /api/tipoveiculo/[1~3]/estado/x/marca/x/
     * 'https://www.seminovosbh.com.br/resultadobusca/index';
     */
    public function filtro(Request $request){
        $result = [];
        $result['success'] = false;
        $parameters = $request->all();


        // dd($parameters);

        if(empty($parameters) || sizeof($parameters) < 3 || !(array_key_exists('tipoVeiculo', $parameters))  ){
            $result['error'] = 'Favor informar o tipo do veiculo e 2 parametros auxiliares!';
        }else{

            
            $aux = $this->validateParameters($parameters);
            if(!$aux['success']){
                $result['error'] = $aux['error'];
            }else{

                $requestResult = Goutte::request('GET', $aux['url']);
                
                $tituloPreco = $requestResult->filter('.bg-busca .titulo-busca')->each(function ($veiculo) {
                    return explode('R$', $veiculo->text());
                });

                // $tituloPrecoReset = array();
                // foreach ($tituloPreco as $row) {
                //     if ($row !== null)
                //     $tituloPrecoReset[] = $row;
                // }
                // $tituloPreco = $tituloPrecoReset;
                
                // dd($tituloPreco);

                $veiculoId = $requestResult->filter('.bg-busca > dt')->filterXPath('//a[contains(@href, "")]')->each(function ($veiculo) {
                    if(sizeof(explode('/', $veiculo->extract(['href'])[0])) == 7){
                        return (explode('/', $veiculo->extract(['href'])[0])[5]);
                    }
                });

                $veiculoIdReset = array();
                foreach ($veiculoId as $row) {
                    if ($row !== null)
                    $veiculoIdReset[] = $row;
                }
                $veiculoId = $veiculoIdReset;

                // dd($veiculoId);

                $numeroPag = $requestResult->filter('.total')->each(function ($search) {
                    return intval($search->text());
                });

                $data = [];

                if(sizeof($tituloPreco) == sizeof($veiculoId)){


                    foreach($veiculoId as $key=>$veiculo){

                        if(sizeof($tituloPreco[$key]) < 2){
                            $aux = null;
                        }else{
                            $aux = trim($tituloPreco[$key][1], ' ');
                        }

                        $data[$key] = array(
                            'titulo' => $tituloPreco[$key][0],
                            'preco' => $aux,
                            'veiculoId' => $veiculo,
                        
                        );
                    }
                    $result['success'] = true;
                    $result['data'] = ['veiculos' => $data, 'paginas' => $numeroPag[0]];
                    
                }else{
                    $result['error'] = 'Erro inesperado!';
                }

            }

        }



        return $result;
    }




    public function validateParameters($parameters){

        $result['success'] = true;
        $URL_BASE = 'https://www.seminovosbh.com.br/resultadobusca/index';

        if($parameters['tipoVeiculo'] > 3 || $parameters['tipoVeiculo'] < 1){
            return['success' => false, 'error' => 'Favor fornecer um valor valido para tipo veiculo! ' . self::CARRO_VALUE . ' - CARRO; ' . self::CAMINHAO_VALUE . ' - CAMINHAO; ' . self::MOTO_VALUE . ' - MOTO'];
        }else{
            $URL_BASE .= '/veiculo/' . $parameters['tipoVeiculo'];
        }

        if(!empty($parameters['estado'])){
            if($parameters['estado'] > 2 || $parameters['estado'] < 1){
                return['success' => false, 'error' => 'Favor fornecer um valor valido para estado de conservacao! 1 - Novos; 0 - Seminovo' ];
            }else{
                $URL_BASE .= '/estado-conservacao/'.$parameters['estado-conservacao'];
            }
        }

        if(!empty($parameters['marca'])){
            if(!is_numeric($parameters['marca'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da marca!';
            }else{
                $URL_BASE .= '/marca/' . $parameters['marca'];
            }
        }

        if(!empty($parameters['modelo'])){
            if(!is_numeric($parameters['modelo'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da modelo!';
            }elseif(empty($parameters['marca'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer o codigo da marca correspondete ao modelo!';          
            }else{
                $URL_BASE .= '/modelo/' . $parameters['modelo'];
            }
        }

        if(!empty($parameters['cidade'])){
            if(!is_numeric($parameters['cidade'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da cidade!';
            }else{
                $URL_BASE .= '/cidade/' . $parameters['cidade'];
            }
        }

        if(!empty($parameters['valor1'])){
            if(!is_numeric($parameters['valor1'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da valor1!';
            }else{
                $URL_BASE .= '/valor1/' . $parameters['valor1'];
            }
        }

        if(!empty($parameters['valor2'])){
            if(!is_numeric($parameters['valor2'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da valor2!';
            }else{
                $URL_BASE .= '/valor2/' . $parameters['valor2'];
            }
        }

        if(!empty($parameters['ano1'])){
            if(!is_numeric($parameters['ano1'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da ano1!';
            }else{
                $URL_BASE .= '/ano1/' . $parameters['ano1'];
            }
        }

        if(!empty($parameters['ano2'])){
            if(!is_numeric($parameters['ano2'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da ano2!';
            }else{
                $URL_BASE .= '/ano2/' . $parameters['ano2'];
            }
        }

        if(!empty($parameters['usuario'])){
            if(!in_array($parameters['usuario'], ['todos', 'particular', 'revenda'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um valor valido para usuario! [todos, particular, revenda]';
            }else{
                $URL_BASE .= '/usuario/' . $parameters['usuario'];
            }
        }
        
        if(!empty($parameters['pagina'])){
            if(!is_numeric($parameters['pagina'])){
                $result['success'] = false;
                $result['error'] = 'Favor fornecer um inteiro valido para o valor da pagina!';
            }else{
                $URL_BASE .= '/pagina/' . $parameters['pagina'];
            }
        }
        

        $result['url'] = $URL_BASE;
        return $result;
    }

}
