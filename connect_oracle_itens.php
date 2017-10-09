#!/usr/bin/php
<?php

chdir(__DIR__);

include 'inc/config.php';
include 'inc/functions.php';

function processaFixes ($marc,$id){

	global $type;	

/* Processa os fixes */

if (isset($marc["doc"]["BAS"])) {

	switch ($marc["doc"]["BAS"]) {
		case "Catalogação Rápida":
			echo "Não indexar";
			break;
		case "Assinatura Combinada":
			echo "Não indexar";
			break;		
		case 01:        
            $body = $marc;
            unset($body["doc"]["BAS"]);
            $body["doc_as_upsert"] = true;
            $response = elasticsearch::elastic_update($id,$type,$body,"opac");
            print_r($response);
			break;
		case 02:
			echo "Não indexar";
			break;
        case 03:        
            $body = $marc;
            unset($body["doc"]["BAS"]);
            $body["doc_as_upsert"] = true;
            $response = elasticsearch::elastic_update($id,$type,$body);
            print_r($response);
			break;
		case 04:
            $body = $marc;
            unset($body["doc"]["BAS"]);
            $body["doc_as_upsert"] = true;
            $response = elasticsearch::elastic_update($id,$type,$body);
            print_r($response);
			break;
		default:
			break;
	}
}
}

function  oracle_sysno_item($sysno) {
    global $conn;
    $consulta_alephseq = "select substr(z103_rec_key_1,6,9) sysno, z13u.z13u_user_defined_3, z30.z30_barcode, z30.z30_sub_library, z30.z30_open_date, z30.z30_update_date, z30.z30_no_loans, z30.z30_call_no, z30.z30_inventory_number from USP50.Z30 
    inner join usp01.z103 z103 on CONCAT(CONCAT('USP50',substr(z30.z30_rec_key,1,9)),'02') = z103.Z103_REC_KEY
    inner join usp01.z13u z13u on substr(z103_rec_key_1,6,9) = z13u.Z13u_REC_KEY
    where substr(z103_rec_key_1,6,9) = '$sysno'";
    $stid = oci_parse($conn, $consulta_alephseq) or die ("erro");
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
    return $body;    
}



// Consulta exemplares criados e atualizados

$date_query = '20171009';

$consulta_criados = "select substr(z103_rec_key_1,6,9) sysno from usp50.z30 z30 inner join usp01.z103 z103 on CONCAT(CONCAT('USP50',substr(z30.z30_rec_key,1,9)),'02') = z103.Z103_REC_KEY where Z30_UPDATE_DATE = '$date_query'";

// Consulta exemplares excluídos
$stid = oci_parse($conn, $consulta_criados) or die ("erro");
 
//Executa os comandos SQL
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
    foreach ($row as $sysno) {
        $result_oracle_sysno = oracle_sysno_item($sysno);
        //print_r($result_oracle_sysno);
        processaFixes ($result_oracle_sysno,$sysno);
    }
}

// Close the Oracle connection
oci_close($conn);
?>