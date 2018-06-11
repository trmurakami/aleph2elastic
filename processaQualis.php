#!/usr/bin/php
<?php

require 'inc/config.php';
require 'inc/functions.php';

$index = "qualis";

while ($line = fgets(STDIN)) {
    
    $record = explode("\t", $line); // Transforma a linha em array
    $id = str_replace('"', '', $record[0]); // ISSN em id
    $query["query"]["ids"]["values"][] = $id; // Cria a consulta para o ISSN

    if (!empty($id)) {
        $get_existing = elasticsearch::elastic_search("qualis", null, null, $query, $index); // Consulta o ISSN na Indice
        echo "\n";
        print_r($id);
        echo "\n";        
    
        if ($get_existing["hits"]["total"] == 1) {
            $body["doc"] = $get_existing["hits"]["hits"][0]["_source"]; // Resultado na variável
            $qualis_array["area"] = trim($record[2], "\"\n\r\f "); 
            $qualis_array["nota"] = trim($record[3], "\"\n\r\f ");
            $qualis_array["area_nota"] = $qualis_array["area"] . " / " . $qualis_array["nota"];
            
            foreach ($body["doc"]["qualis"]["2016"] as $qualis2016) {
                $array_area_nota[] = $qualis2016["area_nota"];
            }
    
            if (in_array($qualis_array["area_nota"], $array_area_nota)) {
                $array_area_nota = [];
                $body = [];            
                $record = [];
                $query = [];            
                $qualis_array = [];
                continue 1;
            } else {
                $body["doc"]["qualis"]["2016"][] = $qualis_array;
                $qualis_array = [];
            }                            
                $body["doc_as_upsert"] = true;
                
        } else {                
            $body["doc"]["title"] = str_replace('"', '', $record[1]);
            $body["doc"]["issn"][] = $id;
            $qualis_array["area"] = trim($record[2], "\"\n\r\f ");
            $qualis_array["nota"] = trim($record[3], "\"\n\r\f ");
            $qualis_array["area_nota"] = $qualis_array["area"] . " / " . $qualis_array["nota"];
            $body["doc"]["qualis"]["2016"][] = $qualis_array;
            $body["doc_as_upsert"] = true;
            unset($qualis_array);        
        }
    
        //print_r($body);
    
        $response = elasticsearch::elastic_update($id, "qualis", $body, $index);
        //print_r($response);
    
        $array_area_nota = [];
        $body = [];            
        $record = [];
        $query = [];
        
        usleep(250);   
    }    

}