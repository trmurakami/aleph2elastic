#!/usr/bin/php
<?php

chdir(__DIR__);

include 'inc/config.php';
include 'inc/functions.php';

function processaFixes ($marc,$id){

	global $type;	

	/* Processa os fixes */
	if (isset($marc["record"]["BAS"])) {

		switch ($marc["record"]["BAS"]["a"][0]) {
			case "Catalogação Rápida":
				echo "Não indexar";
				break;
			case "Assinatura Combinada":
				echo "Não indexar";
				break;		
			case 01:        
				if ($marc["record"]["945"]["b"][0] == "PARTITURA"){

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
					$body["doc"]["base"][] = "Partituras";
					$response = elasticsearch::elasticUpdate($id, $type, $body, "acorde");
					print_r($response);				

				} elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE CONCLUSAO DE CURSO - TCC") {

					$body = fixes($marc);
					$body["doc"]["base"][] = "Trabalhos acadêmicos";
					$body["doc"]["sysno"] = $id;
					if (isset($marc["record"]["260"])) {
						if (isset($marc["record"]["260"]["c"])){
							$excluir_caracteres = array("[","]","c");
							$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
							$body["doc"]["datePublished"] = $only_numbers;
						} else {
							$body["doc"]["datePublished"] = "N/D";
						}	
							
					}				
					$response = elasticsearch::elasticUpdate($id, $type, $body, "bdta_homologacao");
					print_r($response);

				} elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE ESPECIALIZACAO - TCE") {

					$body = fixes($marc);
					$body["doc"]["base"][] = "Trabalhos acadêmicos";
					$body["doc"]["sysno"] = $id;
					if (isset($marc["record"]["260"])) {
						if (isset($marc["record"]["260"]["c"])){
							$excluir_caracteres = array("[","]","c");
							$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
							$body["doc"]["datePublished"] = $only_numbers;
						} else {
							$body["doc"]["datePublished"] = "N/D";
						}	
							
					}				
					$response = elasticsearch::elasticUpdate($id, $type, $body, "bdta_homologacao");
					print_r($response);

				} elseif ($marc["record"]["945"]["b"][0] == "E-BOOK") {

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
					$body["doc"]["base"][] = "E-Books";
					$response = elasticsearch::elasticUpdate($id, $type, $body, "ebooks");
					print_r($response);	

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
					$response = elasticsearch::elasticUpdate($id, $type, $body, "opac");
					print_r($response);
					
				}
				break;
			case 02:
				echo "Não indexar";
				break;
			case 03:
				$body = fixes($marc);
				$body["doc"]["base"][] = "Teses e dissertações";
				$body["doc"]["sysno"] = $id;
				$response = elasticsearch::elasticUpdate($id, $type, $body);
				print_r($response);
				break;
			case 04:
				$body = fixes($marc);
				$body["doc"]["base"][] = "Produção científica";
				$body["doc"]["sysno"] = $id;
				$response = elasticsearch::elasticUpdate($id, $type, $body);
				print_r($response);
				break;
			case 06:
				$body = fixes($marc);
				$body["doc"]["base"][] = "Trabalhos acadêmicos";
				$body["doc"]["sysno"] = $id;
				$response = elasticsearch::elasticUpdate($id, $type, $body, "bdta");
				break; 				
			default:
				break;
		}
	}
}

//bloco da consulta SQL
$consulta = "select DISTINCT Z00_DOC_NUMBER from USP01.Z00 where ORA_ROWSCN > TIMESTAMP_TO_SCN(CURRENT_TIMESTAMP - NUMTODSINTERVAL(60, 'MINUTE'))";
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
		// Excluir registros com DEL
		if (!empty($marc["record"]["DEL"])) {
			print_r($id);
			$result_delete = elasticsearch::elasticDelete($id, $type, "");
			if ($result_delete["result"] == "not_found") {
				$result_delete = elasticsearch::elasticDelete($id, $type, "partituras");
				print_r($result_delete);
				$result_delete = elasticsearch::elasticDelete($id, $type, "bdta_homologacao");
				print_r($result_delete);
				$result_delete = elasticsearch::elasticDelete($id, $type, "ebooks");
				print_r($result_delete);
				$result_delete = elasticsearch::elasticDelete($id, $type, "opac");
				print_r($result_delete);
				$result_delete = elasticsearch::elasticDelete($id, $type, "bdta");
			}			
			print_r($result_delete);
		} else {
			processaFixes($marc, $id);
		}
		$marc = [];        
    }
}

// Close the Oracle connection
oci_close($conn);
?>