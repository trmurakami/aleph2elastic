#!/usr/bin/php
<?php

chdir(__DIR__);

include 'inc/config.php';
include 'inc/functions.php';

$query["query"]["bool"]["must"]["query_string"]["query"] = "-_exists_:itemCollectDate";
$query['sort'] = [ ['datePublished.keyword' => ['order' => 'desc']], ];    

$params = [];
$params["index"] = $index;
$params["type"] = $type;
$params["size"] = 100;
$params["from"] = 0;
$params["body"] = $query;

print_r($query);

$cursor = $client->search($params);
$total = $cursor["hits"]["total"];   
echo "\n";
print_r($total);
echo "\n\n";


function processaFixes ($marc,$id){

	global $type;	

    /* Processa os fixes */

    $body = $marc;
    unset($body["doc"]["BAS"]);
    $body["doc_as_upsert"] = true;
    print_r($body);
    $response = elasticsearch::elastic_update($id, $type, $body);
    print_r($response);
}

function  oracle_sysno_item($sysno) {
    global $connNew;
    $consulta_alephseq = "select substr(z103.z103_rec_key_1,6,9) sysno, z13u.z13u_user_defined_3, z30.z30_barcode, z30.z30_sub_library, z30.z30_open_date, z30.z30_update_date, z30.z30_no_loans, z30.z30_call_no, z30.z30_inventory_number from USP50.Z30@ALPSRCH z30
    inner join usp01.z103@ALPSRCH z103 on CONCAT(CONCAT('USP50',substr(z30.z30_rec_key,1,9)),'02') = z103.Z103_REC_KEY
    inner join usp01.z13u@ALPSRCH z13u on substr(z103.z103_rec_key_1,6,9) = z13u.Z13u_REC_KEY
    where substr(z103.z103_rec_key_1,6,9) = '$sysno'";
    $stid = oci_parse($connNew, $consulta_alephseq) or die ("erro");
    oci_execute($stid);
    $i = 0;
    while (($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
        $body["doc"]["BAS"] = $row["Z13U_USER_DEFINED_3"];
        $body["doc"]["item"][$i]["Z30_BARCODE"] = $row["Z30_BARCODE"];
        $body["doc"]["item"][$i]["Z30_SUB_LIBRARY"] = $row["Z30_SUB_LIBRARY"];
        $body["doc"]["item"][$i]["Z30_OPEN_DATE"] = $row["Z30_OPEN_DATE"];
        $body["doc"]["item"][$i]["Z30_UPDATE_DATE"] = $row["Z30_UPDATE_DATE"];
        $body["doc"]["item"][$i]["Z30_NO_LOANS"] = $row["Z30_NO_LOANS"];
        $body["doc"]["item"][$i]["Z30_CALL_NO"] = $row["Z30_CALL_NO"];
        $body["doc"]["item"][$i]["Z30_INVENTORY_NUMBER"] = $row["Z30_INVENTORY_NUMBER"];        
        $i++;        
    } 

    if (isset($body)) {
        $body["doc"]["itemCollect"] = true;
        $body["doc"]["itemCollectDate"] = date("Ymd");  
        return $body;
    } else {
        $body["doc"]["item"] = [];
        $body["doc"]["itemCollect"] = true;
        $body["doc"]["itemCollectDate"] = date("Ymd");        
        return $body;
    }
      
    // Close the Oracle connection
    oci_close($connNew);  
}

foreach ($cursor["hits"]["hits"] as $r) {
    print_r($r["_id"]);
    echo "\n\n";
    $result_oracle_sysno = oracle_sysno_item($r["_id"]);
    processaFixes($result_oracle_sysno, $r["_id"]);
    unset($result_oracle_sysno);
}

?>