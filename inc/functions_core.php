<?php
/**
 * Arquivo de classes e funções do principais do sistema
 */

/**
 * Classe de interação com o Elasticsearch
 */
class elasticsearch {

    /**
     * Executa o commando get no Elasticsearch
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch                         
     * @param string[] $fields Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
     * 
     */
    public static function elastic_get ($_id,$type,$fields) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["id"] = $_id;
        $params["_source"] = $fields;
        
        $response = $client->get($params);        
        return $response;    
    }    

    /**
     * Executa o commando search no Elasticsearch
     * 
     * @param string $type Tipo de documento no índice do Elasticsearch                         
     * @param string[] $fields Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
     * @param int $size Quantidade de registros nas respostas
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch
     * 
     */    
    public static function elastic_search ($type,$fields,$size,$body) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["_source"] = $fields;
        $params["size"] = $size;
        $params["body"] = $body;
        
        $response = $client->search($params);        
        return $response;
    }
    
    /**
     * Executa o commando update no Elasticsearch
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch  
     * 
     */     
    public static function elastic_update ($_id,$type,$body) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["id"] = $_id;
        $params["body"] = $body;
        
        $response = $client->update($params);        
        return $response;
    }

    /**
     * Executa o commando delete no Elasticsearch
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch     
     * 
     */     
    public static function elastic_delete ($_id,$type) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["id"] = $_id;
        
        $response = $client->delete($params);        
        return $response;
    }
    
    /**
     * Executa o commando delete_by_query no Elasticsearch
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch  
     * 
     */     
    public static function elastic_delete_by_query ($_id,$type,$body) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["id"] = $_id;
        $params["body"] = $body;
        
        $response = $client->deleteByQuery($params);        
        return $response;
    }    
    
    /**
     * Executa o commando update no Elasticsearch e retorna uma resposta em html
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch  
     * 
     */     
    static function store_record ($_id,$type,$body){
        $response = elasticsearch::elastic_update($_id,$type,$body);    
        echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   

    }
    
}

?>