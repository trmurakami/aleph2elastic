#!/usr/bin/php
<?php

include 'inc/config.php';
include 'inc/functions.php';

while( $line = fgets(STDIN) ) {
    
    $record = explode("\t",$line); // Transforma a linha em array
    echo "$record[0] \n";
    $id = str_replace('"','',substr($record[19], 0, 4) . "-" . substr($record[19], 4, 8)); // ISSN em id    
    if (!empty($record[20])){
        $id_eissn = str_replace('"','',substr($record[20], 0, 4) . "-" . substr($record[20], 4, 8)); // e-ISSN
        $query_eissn["query"]["ids"]["values"][] = $id_eissn; // Cria a consulta para o ISSN
        $index = "citescore";
        $get_existing_eissn = elasticsearch::elastic_search("issn",null,null,$query_eissn); // Consulta o ISSN na Indice        
    }
    $query["query"]["ids"]["values"][] = $id; // Cria a consulta para o ISSN
    $get_existing = elasticsearch::elastic_search("issn",null,null,$query); // Consulta o ISSN na Indice

    if ($get_existing["hits"]["total"] == 1) {

        //print_r($get_existing["hits"]["hits"][0]["_source"]);

        $body["doc"]["scopus_sub_subject_area"][] = $get_existing["hits"]["hits"][0]["_source"]["scopus_sub_subject_area"];
        $body["doc"]["scopus_sub_subject_area"][] = $record[15];
        $body["doc_as_upsert"] = true;
        $index = "citescore";
        $response = elasticsearch::elastic_update($id,"issn",$body);
        //print_r($response);
        $body = [];            
        $query = [];    
        usleep(250); 

    } else {

        $body["doc"]["title"] = str_replace('"','',$record[1]);
        $body["doc"]["issn"][] = $id;
        $array["citescore"] = floatval(str_replace(",",".",$record[2]));
        $array["percentile"] = (int)$record[3];
        $array["citation_count"] = (int)$record[4];
        $array["scholarly_output"] = (int)$record[5];
        $array["percent_cited"] = (int)$record[6];  
        $array["SNIP"] = floatval(str_replace(",",".",$record[7]));
        $array["SJR"] = floatval(str_replace(",",".",$record[8]));
        $array["rank"] = (int)$record[9];
        $array["rank_out_of"] = (int)$record[10];
        $array["publisher"] = $record[11];
        $array["type"] = $record[12];
        $array["open_access"] = $record[13];
        $array["quartile"] = $record[16];
        $array["top_10_citescore"] = $record[17];
        $body["doc"]["scopus_sub_subject_area"][] = $record[15];
        $body["doc"]["url"] = $record[18];      
        $body["doc"]["citescore"]["2016"][] = $array;
        $body["doc_as_upsert"] = true;
        unset($array);

        $index = "citescore";
        $response = elasticsearch::elastic_update($id,"issn",$body);
        //print_r($response);
        $body = [];   
        $query = [];    
        usleep(250);         
    }

    if ($get_existing_eissn["hits"]["total"] == 1) {

        $body["doc"]["scopus_sub_subject_area"][] = $get_existing_eissn["hits"]["hits"][0]["_source"]["scopus_sub_subject_area"];
        $body["doc"]["scopus_sub_subject_area"][] = $record[15];
        $body["doc_as_upsert"] = true;
        $index = "citescore";
        $response = elasticsearch::elastic_update($id_eissn,"issn",$body);
        //print_r($response);
        $body = [];            
        $query = [];    
        usleep(250);         

    } else {
        $body["doc"]["title"] = str_replace('"','',$record[1]);
        $body["doc"]["issn"][] = $id_eissn;
        $body["doc"]["issn"][] = $id;
        $array["citescore"] = (int)$record[2];
        $array["percentile"] = (int)$record[3];
        $array["citation_count"] = (int)$record[4];
        $array["scholarly_output"] = (int)$record[5];
        $array["percent_cited"] = (int)$record[6];  
        $array["SNIP"] = (int)$record[7];
        $array["SJR"] = (int)$record[8];
        $array["rank"] = (int)$record[9];
        $array["rank_out_of"] = (int)$record[10];
        $array["publisher"] = $record[11];
        $array["type"] = $record[12];
        $array["open_access"] = $record[13];
        $array["quartile"] = $record[16];
        $array["top_10_citescore"] = $record[17];
        $body["doc"]["scopus_sub_subject_area"][] = $record[15];
        $body["doc"]["url"] = $record[18];      
        $body["doc"]["citescore"]["2016"][] = $array;
        $body["doc_as_upsert"] = true;
        unset($array);

        $index = "citescore";
        $response = elasticsearch::elastic_update($id_eissn,"issn",$body);
        //print_r($response);
        $body = [];
        $query = [];    
        usleep(250);          
    }

    $record = [];



}