#!/usr/bin/php
<?php

include 'inc/config.php';
$index = "serial_metrics";
include 'inc/functions.php';

while( $line = fgets(STDIN) ) {

    $record = explode("\t",$line);
    $id = str_replace('"','',$record[0]);
    $query["query"]["ids"]["values"][] = $id;

    $get_existing = elasticsearch::elastic_search("qualis",null,null,$query);
    if ($get_existing["hits"]["total"] >= 1) {
        //print_r($get_existing);
        $body["doc"] = $get_existing["hits"]["hits"][0]["_source"];
        $qualis_array["area"] = trim($record[2],"\"\n\r\f ");
        $qualis_array["nota"] = trim($record[3],"\"\n\r\f ");
        $qualis_array["area_nota"] = $qualis_array["area"]." / ".$qualis_array["nota"];
        
        foreach( $body["doc"]["qualis"]["2015"] as $k => $v ){
            if($v["area_nota"] == $qualis_array["area_nota"]){
                $body = [];            
                $record = [];
                $query = [];
                $qualis_array = [];
                continue 2;
            } else {
            $body["doc"]["qualis"]["2015"][] = $qualis_array;
            $body["doc_as_upsert"] = true;
            $qualis_array = [];
            }
        }


    } else {
        $body["doc"]["title"] = str_replace('"','',$record[1]);
        $body["doc"]["issn"][] = $id;
        $qualis_array["area"] = trim($record[2],"\"\n\r\f ");
        $qualis_array["nota"] = trim($record[3],"\"\n\r\f ");
        $qualis_array["area_nota"] = $qualis_array["area"]." / ".$qualis_array["nota"];
        $body["doc"]["qualis"]["2015"][] = $qualis_array;
        $body["doc_as_upsert"] = true;        
    }

    $response = elasticsearch::elastic_update($id,"qualis",$body);

    $body = [];            
    $record = [];
    $query = [];
    usleep(250000);
}