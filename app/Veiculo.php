<?php

namespace App;


class Veiculo{
    

    /* ATRIBUTOS  DO VEICULO */
    protected $veiculoId;
    protected $imagens;
    protected $preco;
    protected $detalhes;
    protected $acessorios;
    protected $observacoes;
    protected $tituloAnuncio;
    protected $contatoAnuncio;


    /**
     * construtor da classe veiculo.
     * @param resultRequest - dom resultante do request 
     * @param veiculoId - id do veiculo correspondente
     */

    public function __construct($resultRequest, $veiculoId){

        $this->veiculoId = $veiculoId;

        // filtra o DOM pela tag textoBoxVeiculo buscando pelo titulo do anuncio
        $this->tituloAnuncio = $resultRequest->filter('#textoBoxVeiculo > h5')->each(function($word){
            return trim($word->text());
        });

        // filtra o DOM pela tag textoBoxVeiculo buscando pelo preco do veiculo
        $this->preco = $resultRequest->filter('#textoBoxVeiculo > p')->each(function($word){
            return trim($word->text());
        });


        // filtra o DOM pela tag infDetalhes buscando pelo box de detalhes do anuncio
        $this->detalhes = $resultRequest->filter('#infDetalhes > span > ul > li')->each(function ($word) {
            return trim($word->text());
        });

        // filtra o DOM pela tag infDetalhes2 buscando pelo box de acessorios do anuncio
        $this->acessorios = $resultRequest->filter('#infDetalhes2 > ul > li')->each(function ($word) {
            return trim($word->text());
        });

        // filtra o DOM pela tag infDetalhes3 buscando pelo box de observacoes do anuncio
        $this->observacoes = $resultRequest->filter('#infDetalhes3 > ul > p')->each(function ($word){
            return trim($word->text());
        });

        // filtra o DOM pela tag infDetalhes4 buscando pelos contatos do anuncio
        $this->contatoAnuncio = $resultRequest->filter('#infDetalhes4 .texto> ul > li')->each(function ($word){
            return trim($word->text());
        });


        // metodo auxiliar para filtrar e concatenar imagem principal do anuncio com imagens da descricao
        $this->imagens = $this->parseImages($resultRequest->filter('#fotoVeiculo'), $resultRequest->filter('#conteudoVeiculo'));


    }


    /**
     * Filtra o DOM e concatena as imagens do anuncio em um array unico
     * @return aux - array de imagens
     */
    protected function parseImages($imagemVeiculo, $listaImagens){
        $images = [];

        $images = $imagemVeiculo->filterXPath('//img[contains(@src, "")]')->each(function ($tag){
            return $tag->extract(['src']);
        });

        $lista = $listaImagens->filterXPath('//img[contains(@src, "")]')->each(function ($tag){
            return $tag->extract(['src']);
        });


        $aux = [];
        foreach($lista as $img){
            if(!($img[0] == '/images/photoNone.jpg'))
                array_push($aux, $img[0]);
        } 
        array_push($aux, $images[0][0]);
        return $aux;
    }


    /* PUBLIC FUNCTIONS */
    
    public function to_json(){
        return json_encode(
            array(
                'veiculoId' => $this->veiculoId,
                'imagens' => $this->imagens,
                'preco' => $this->preco,
                'detalhes' => $this->detalhes,
                'acessorios' => $this->acessorios, 
                'observacoes' => $this->observacoes,
                'tituloAnuncio' => $this->tituloAnuncio,
                'contatoAnuncio' => $this->contatoAnuncio,
            )
        );
    }



}