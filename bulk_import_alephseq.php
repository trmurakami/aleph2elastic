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
				$response = elasticsearch::elastic_update($id,"partitura",$body,"partituras");
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
				$response = elasticsearch::elastic_update($id,$type,$body,"bdta_homologacao");
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
				$response = elasticsearch::elastic_update($id,$type,$body,"bdta_homologacao");
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
				$response = elasticsearch::elastic_update($id,$type,$body,"ebooks");
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
				$response = elasticsearch::elastic_update($id,$type,$body,"opac");
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
			$response = elasticsearch::elastic_update($id,$type,$body);
			print_r($response);
			break;
		case 04:
			$body = fixes($marc);
			$body["doc"]["base"][] = "Produção científica";
			$body["doc"]["sysno"] = $id;
			$response = elasticsearch::elastic_update($id,$type,$body);
			print_r($response);
			break;
		default:
			break;
	}

	
	echo "$sysno \n";
            
  $marc = [];
  $record = [];

  }
  

$sysno_old = $sysno;

}




