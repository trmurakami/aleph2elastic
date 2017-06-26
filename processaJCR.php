#!/usr/bin/php
<?php

include 'inc/config.php';
$index = "serial_jcr";
include 'inc/functions.php';

while( $line = fgets(STDIN) ) {
    
    $record = explode(",",$line); // Transforma a linha em array
    $id = $record[3]; // ISSN em id
    
            
    $body["doc"]["title"] = $record[1];
    $body["doc"]["abbrev_title"] = $record[2];
    $body["doc"]["issn"] = $id;
    $jcr_array["Journal_Impact_Factor"] = $record[4];
    $jcr_array["IF_without_Journal_Self_Cites"] = $record[5];
    $jcr_array["Eigenfactor_Score"] = $record[6];
    $jcr_array["JCR_Rank"] = $record[0];
    $body["doc"]["JCR"]["2016"][] = $jcr_array;
    $body["doc_as_upsert"] = true;
    unset($jcr_array); 

    $response = elasticsearch::elastic_update($id,"JCR",$body);


    $body = [];            
    $record = [];
    
}