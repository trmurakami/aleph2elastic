#!/usr/bin/php
<?php

$marc = [];
$i = 0;
$id = 0;

include 'inc/config.php';
include 'inc/functions.php';


/* Obtém os dados do STDIN e converte para JSON  */
while( $line = fgets(STDIN) ) {

  processaAlephseq($line);

}

/* Processa os fixes */

if (!empty($marc)){
	//print_r($marc);
	$body = fixes($marc);
	//print_r($body); 
}

if ($body["naoIndexar"] == true ){
	//echo "Registro não é da base 03 ou 04";
} else {
	$response = elasticsearch::elastic_update($id,$type,$body);
	//print_r($response);	
}
