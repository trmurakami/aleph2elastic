#!/usr/bin/php
<?php

$marc = [];
$id = 0;

include 'inc/config.php';
include 'inc/functions.php';


/* Obtém os dados do STDIN e converte para JSON  */
while( $line = fgets(STDIN) ) {

  processaAlephseq($line);

}

/* Processa os fixes */

switch ($marc["record"]["BAS"]["a"][0]) {
    case "Catalogação Rápida":
        echo "Não indexar";
        break;
    case 01:        
		if ($marc["record"]["945"]["b"][0] == "PARTITURA"){

			$index = "partituras";
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
			$response = elasticsearch::elastic_update($id,"partitura",$body);

		} elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE CONCLUSAO DE CURSO - TCC") {
			$index = "bdta_homologacao";
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
			$response = elasticsearch::elastic_update($id,$type,$body);
		} elseif ($marc["record"]["945"]["b"][0] == "TRABALHO DE ESPECIALIZACAO - TCE") {
			$index = "bdta_homologacao";
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
			$response = elasticsearch::elastic_update($id,$type,$body);

		} elseif ($marc["record"]["945"]["b"][0] == "E-BOOK") {
			$index = "ebooks";
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
			$response = elasticsearch::elastic_update($id,$type,$body);	

		} else {

		}
        break;
    case 02:
        echo "Não indexar";
        break;
    case 03:
		$body = fixes($marc);
		$body["doc"]["base"][] = "Teses e dissertações";
		$body["doc"]["sysno"] = $id;
		$response = elasticsearch::elastic_update($id,$type,$body);
        break;
    case 04:
		$body = fixes($marc);
		$body["doc"]["base"][] = "Produção científica";
		$body["doc"]["sysno"] = $id;
		$response = elasticsearch::elastic_update($id,$type,$body);
        break;
	default:
		$body = fixes($marc);
		$body["doc"]["base"][] = $marc["record"]["BAS"]["a"][0];
		$body["doc"]["sysno"] = $id;
		$response = elasticsearch::elastic_update($id,$type,$body);		
}
