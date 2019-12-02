#!/usr/bin/php
<?php

include 'inc/config.php';
$index = "serial_web_of_science";
include 'inc/functions.php';

while( $line = fgets(STDIN) ) {
    
    $record = explode("\t", $line); // Transforma a linha em array
    $id = $record[2]; // ISSN em id
    
            
    $body["doc"]["title"] = $record[0];
    $body["doc"]["issn"] = $id;
    $body["doc"]["frequency"] = $record[1];
    $body["doc"]["address"] = $record[3];
    if (!empty($record[4])) {
        if (strpos($record[4], '||') !== false) {
            $body["doc"]["coverage"][] = explode("||", $record[4]);
        }
    }
    $body["doc_as_upsert"] = true;

    $response = elasticsearch::elasticUpdate($id, "WOS", $body);


    $body = [];            
    $record = [];
    
}