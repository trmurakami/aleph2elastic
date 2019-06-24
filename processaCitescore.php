#!/usr/bin/php
<?php

require 'inc/config.php';
require 'inc/functions.php';

while ($line = fgets(STDIN)) {
    
    $record = explode("\t", $line); // Transforma a linha em array
    echo "$record[0] \n";
    $sourceId = $record[0];
    if (!empty($record[2])) {
        $issn = str_replace('"', '', substr($record[2], 0, 4) . "-" . substr($record[2], 4, 8));
        $body["doc"]["issn"][] = $issn;
    }  
    if (!empty($record[3])) {
        $id_eissn = str_replace('"', '', substr($record[3], 0, 4) . "-" . substr($record[3], 4, 8)); // e-ISSN
        $body["doc"]["issn"][] = $id_eissn;
    }    

    $body["doc"]["title"] = str_replace('"', '', $record[1]);
    $body["doc"]["active"] = $record[4];
    $body["doc"]["coverage"] = $record[5];
    $body["doc"]["language"] = $record[6];
    $body["doc"]["open_access"] = $record[17];
    $body["doc"]["medline"] = $record[16];
    $body["doc"]["type"] = $record[20];
    $body["doc"]["publisher"]["name"] = $record[27];
    $body["doc"]["publisher"]["location"] = $record[28];

    $array2017["citescore"] = floatval(str_replace(",", ".", $record[13]));
    $array2017["SJR"] = floatval(str_replace(",", ".", $record[14]));    
    $array2017["SNIP"] = floatval(str_replace(",", ".", $record[15]));    
    $body["doc"]["citescore"]["2017"][] = $array2017;
    unset($array2017);

    $array2016["citescore"] = floatval(str_replace(",", ".", $record[10]));
    $array2016["SJR"] = floatval(str_replace(",", ".", $record[11]));    
    $array2016["SNIP"] = floatval(str_replace(",", ".", $record[12]));    
    $body["doc"]["citescore"]["2016"][] = $array2016;
    unset($array2016);
    
    $array2015["citescore"] = floatval(str_replace(",", ".", $record[7]));
    $array2015["SJR"] = floatval(str_replace(",", ".", $record[8]));    
    $array2015["SNIP"] = floatval(str_replace(",", ".", $record[9]));    
    $body["doc"]["citescore"]["2015"][] = $array2015;
    unset($array2015);      

    $body["doc_as_upsert"] = true;
    

    $index = "citescore";

    //print_r($body);
    $response = elasticsearch::elasticUpdate($sourceId, "citescore", $body);
    print_r($response);
    unset($body);

}