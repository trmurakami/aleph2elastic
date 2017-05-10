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

   if (!empty($marc)){
     //print_r($marc);
     $body = fixes($marc);
     //print_r($body); 
   }

   if ($body["naoIndexar"] == true ){
     echo "Registro não é da base 03 ou 04";
   } else {
     $response = elasticsearch::elastic_update($id,$type,$body);
     //print_r($response);	
   }            
    //print_r($marc);
    $marc = [];
    //print_r($record);
    $record = [];
  }
  $sysno_old = $sysno;

}



