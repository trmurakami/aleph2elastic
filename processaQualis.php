#!/usr/bin/php
<?php

include 'inc/config.php';
$index = "serial_metrics";
include 'inc/functions.php';

while( $line = fgets(STDIN) ) {
    
    $record = explode("\t",$line); // Transforma a linha em array
    $id = str_replace('"','',$record[0]); // ISSN em id
    $query["query"]["ids"]["values"][] = $id; // Cria a consulta para o ISSN
    $get_existing = elasticsearch::elastic_search("qualis",null,null,$query); // Consulta o ISSN na Indice

    //print_r($get_existing);
    
    if ($get_existing["hits"]["total"] == 1) {
        $body["doc"] = $get_existing["hits"]["hits"][0]["_source"]; // Resultado na variável
        $qualis_array["area"] = trim($record[2],"\"\n\r\f "); 
        $qualis_array["nota"] = trim($record[3],"\"\n\r\f ");
        $qualis_array["area_nota"] = $qualis_array["area"]." / ".$qualis_array["nota"];
        
        foreach( $body["doc"]["qualis"]["2015"] as $qualis2015 ){
            $array_area_nota[] = $qualis2015["area_nota"];
        }

        if (in_array($qualis_array["area_nota"], $array_area_nota)) {
            $array_area_nota = [];
            $body = [];            
            $record = [];
            $query = [];            
            $qualis_array = [];
            continue 1;
        } else {
            $body["doc"]["qualis"]["2015"][] = $qualis_array;
            $qualis_array = [];
        }                            
            $body["doc_as_upsert"] = true;
            
    } else {                
        $body["doc"]["title"] = str_replace('"','',$record[1]);
        $body["doc"]["issn"][] = $id;
        $qualis_array["area"] = trim($record[2],"\"\n\r\f ");
        $qualis_array["nota"] = trim($record[3],"\"\n\r\f ");
        $qualis_array["area_nota"] = $qualis_array["area"]." / ".$qualis_array["nota"];
        $body["doc"]["qualis"]["2015"][] = $qualis_array;
        $body["doc_as_upsert"] = true;
        unset($qualis_array);        
    }

    //print_r($body);

    $response = elasticsearch::elastic_update($id,"qualis",$body);

    $array_area_nota = [];
    $body = [];            
    $record = [];
    $query = [];
    
    usleep(2500000);
}