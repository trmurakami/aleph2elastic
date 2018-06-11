#!/usr/bin/php
<?php

require 'inc/config.php';
require 'inc/functions.php';

while ($line = fgets(STDIN)) {
    
    $record = explode("\t", $line); // Transforma a linha em array
    echo "$record[0] \n";
    $sourceId = $record[0];
    $id = str_replace('"', '', substr($record[2], 0, 4) . "-" . substr($record[2], 4, 8)); // ISSN em id    
    if (!empty($record[3])) {
        $id_eissn = str_replace('"', '', substr($record[3], 0, 4) . "-" . substr($record[3], 4, 8)); // e-ISSN
    }

    $body["doc"]["title"] = str_replace('"', '', $record[1]);
    if (isset($id_eissn)) {
        $body["doc"]["issn"][] = $id_eissn;
    }
    $body["doc"]["issn"][] = $id;
    $array["citescore"] = floatval(str_replace(",", ".", $record[13]));
    $array["SNIP"] = floatval(str_replace(",", ".", $record[15]));
    $array["SJR"] = floatval(str_replace(",", ".", $record[14]));
    $array["active"] = $record[4];
    $array["coverage"] = $record[5];
    $array["publisher"] = $record[27];
    $array["type"] = $record[20];
    $array["open_access"] = $record[17];
    $body["doc"]["url"] = $record[18];      
    $body["doc"]["citescore"]["2017"][] = $array;
    $body["doc_as_upsert"] = true;
    unset($array);

    $index = "citescore";

    //print_r($body);
    $response = elasticsearch::elastic_update($sourceId, "issn", $body);
    print_r($response);
    unset($body);
    unset($query);
    unset($record);

}