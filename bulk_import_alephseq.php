#!/usr/bin/php
<?php

include 'inc/config.php';
include 'inc/functions.php';

$record = array();
$sysno_old = '000000000';

while( $line = fgets(STDIN) ) {
  $sysno = substr($line,0,9);
  if($sysno_old == '000000000'){
    $sysno_old = $sysno;
  } 
  if($sysno_old == $sysno){
   $record[] = $line;
  }
  else {

    foreach ($record as $linha_de_registro) {
      processaAlephseq($linha_de_registro);
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
	$body["doc"]["base"][] = "Livros";

	if (isset($marc["record"]["260"])) {
			$excluir_caracteres = array("[","]","c");
			$only_numbers = str_replace($excluir_caracteres, "", $marc["record"]["260"]["c"][0]);
			$body["doc"]["datePublished"] = $only_numbers;
	}

	$response = elasticsearch::elastic_update($id,"partitura",$body);

} 
            
  $marc = [];
  $record = [];

  }

$sysno_old = $sysno;

}



