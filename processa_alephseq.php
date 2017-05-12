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

if ($marc["record"]["BAS"]["a"][0] == "Catalogação Rápida"){
	
}	

if ($marc["record"]["BAS"]["a"][0] == 01){

}

if ($marc["record"]["BAS"]["a"][0] == 02){

}

if ($marc["record"]["BAS"]["a"][0] == 03){

	$body = fixes($marc);
	$body["doc"]["base"][] = "Teses e dissertações";
	$response = elasticsearch::elastic_update($id,$type,$body);

}

if ($marc["record"]["BAS"]["a"][0] == 04){

	$body = fixes($marc);
	$body["doc"]["base"][] = "Produção científica";
	$response = elasticsearch::elastic_update($id,$type,$body);

}		

if ($marc["record"]["945"]["b"][0] == "PARTITURA"){

	$index = "partituras";
	$body = fixes($marc);

	if (isset($marc["record"]["260"])) {
		if (isset($marc["record"]["260"]["c"])){
			$excluir_caracteres = array("[","]","c");
			$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			$body["doc"]["datePublished"] = $only_numbers;
		}	
		$body["doc"]["datePublished"] = "N/D"; 
	}


	$body["doc"]["base"][] = "Livros";
	$response = elasticsearch::elastic_update($id,"partitura",$body);

} 

