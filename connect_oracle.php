#!/usr/bin/php
<?php

chdir(__DIR__);

include 'inc/config.php';
include 'inc/functions.php';

function processaFixes ($marc,$id){

    global $type;

/* Processa os fixes */


// Excluir registros com DEL
if (!empty($marc["record"]["DEL"])) {
	elasticsearch::elastic_delete($id,$type,$index);
}


switch ($marc["record"]["BAS"]["a"][0]) {
    case "Catalogação Rápida":
        echo "Não indexar";
        break;
	case "Assinatura Combinada":
        echo "Não indexar";
        break;		
    case 01:        
		if ($marc["record"]["945"]["b"][0] == "PARTITURA"){

			// $index = "partituras";
			// $body = fixes($marc);

			// if (isset($marc["record"]["260"])) {
			// 	if (isset($marc["record"]["260"]["c"])){
			// 		$excluir_caracteres = array("[","]","c");
			// 		$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			// 		$body["doc"]["datePublished"] = $only_numbers;
			// 	} else {
			// 		$body["doc"]["datePublished"] = "N/D";
			// 	}	
					
			// }
			// $body["doc"]["base"][] = "Livros";
            // $response = elasticsearch::elastic_update($id,"partitura",$body);
            // //print_r($response);

		} elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE CONCLUSAO DE CURSO - TCC") {
			//$index = "bdta_homologacao";
			//$body = fixes($marc);
			//$body["doc"]["base"][] = "Trabalhos acadêmicos";
			//$body["doc"]["sysno"] = $id;
			//if (isset($marc["record"]["260"])) {
			//	if (isset($marc["record"]["260"]["c"])){
			//		$excluir_caracteres = array("[","]","c");
			//		$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			//		$body["doc"]["datePublished"] = $only_numbers;
			//	} else {
			//		$body["doc"]["datePublished"] = "N/D";
			//	}	
					
			//}				
            //$response = elasticsearch::elastic_update($id,$type,$body);
            //print_r($response);
		} elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE ESPECIALIZACAO - TCE") {
			//$index = "bdta_homologacao";
			//$body = fixes($marc);
			//$body["doc"]["base"][] = "Trabalhos acadêmicos";
			//$body["doc"]["sysno"] = $id;
			//if (isset($marc["record"]["260"])) {
			//	if (isset($marc["record"]["260"]["c"])){
			//		$excluir_caracteres = array("[","]","c");
			//		$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			//		$body["doc"]["datePublished"] = $only_numbers;
			//	} else {
			//		$body["doc"]["datePublished"] = "N/D";
			//	}	
					
			//}				
            //$response = elasticsearch::elastic_update($id,$type,$body);
            //print_r($response);

		} elseif ($marc["record"]["945"]["b"][0] == "E-BOOK") {
			// $index = "ebooks";
			// $body = fixes($marc);

			// if (isset($marc["record"]["260"])) {
			// 	if (isset($marc["record"]["260"]["c"])){
			// 		$excluir_caracteres = array("[","]","c");
			// 		$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			// 		$body["doc"]["datePublished"] = $only_numbers;
			// 	} else {
			// 		$body["doc"]["datePublished"] = "N/D";
			// 	}	
					
			// }
			// $body["doc"]["base"][] = "E-Books";
            // $response = elasticsearch::elastic_update($id,$type,$body);
            // //print_r($response);	

		} else {

			$body = fixes($marc);
			if (isset($marc["record"]["260"])) {
			  	if (isset($marc["record"]["260"]["c"])){
			  		$excluir_caracteres = array("[","]","c");
			  		$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			  		$body["doc"]["datePublished"] = $only_numbers;
			  	} else {
			 		$body["doc"]["datePublished"] = "N/D";
				}					
			}
			$body["doc"]["base"][] = "Livros";
			$response = elasticsearch::elastic_update($id,$type,$body,"opac");
			
		}
        break;
    case 02:
        echo "Não indexar";
        break;
    case 03:
		$body = fixes($marc);
		$body["doc"]["base"][] = "Teses e dissertações";
		$body["doc"]["sysno"] = $id;
        $response = elasticsearch::elastic_update($id,$type,$body,$index);
        //print_r($response);
        break;
    case 04:
		$body = fixes($marc);
		$body["doc"]["base"][] = "Produção científica";
		$body["doc"]["sysno"] = $id;
        $response = elasticsearch::elastic_update($id,$type,$body,$index);
        //print_r($response);
        break;
    default:
        break;
}

}

function  oracle_sysno($sysno) {
    global $conn;
    $consulta_alephseq = "select Z00R_DOC_NUMBER, Z00R_FIELD_CODE, Z00R_ALPHA, Z00R_TEXT from USP01.Z00R where Z00R_DOC_NUMBER = '$sysno'";
    $stid = oci_parse($conn, $consulta_alephseq) or die ("erro");
    oci_execute($stid);
    while (($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
        $record[] = implode(" ", $row);        
    }    
    return $record;    
}

//bloco da consulta SQL
$consulta = "select Z00_DOC_NUMBER from USP01.Z00 where ORA_ROWSCN > TIMESTAMP_TO_SCN(CURRENT_TIMESTAMP - NUMTODSINTERVAL(10, 'MINUTE'))";
//$consulta = "select Z00_DOC_NUMBER from USP01.Z00 Z00 where Z00_DOC_NUMBER IN (select Z13U_REC_KEY from USP01.Z13U where Z13U.Z13U_USER_DEFINED_3 IN ('03','04')) AND Z00.ORA_ROWSCN > TIMESTAMP_TO_SCN(CURRENT_TIMESTAMP - NUMTODSINTERVAL(10, 'MINUTE'))";
$stid = oci_parse($conn, $consulta) or die ("erro");
 
//Executa os comandos SQL
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
    foreach ($row as $sysno) {
        $result_oracle_sysno = oracle_sysno($sysno);
        foreach ($result_oracle_sysno as $record_line) {
            processaAlephseq($record_line);
        }
        processaFixes($marc,$id);
        $marc = [];        
    }
}

// Close the Oracle connection
oci_close($conn);
?>