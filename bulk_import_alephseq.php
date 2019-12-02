#!/usr/bin/php
<?php

require 'inc/functions.php';

$record = array();
$sysno_old = '000000000';

$i = 0;
while ($line = fgets(STDIN)) {
    $sysno = substr($line, 0, 9);
    if ($sysno_old == '000000000') {
        $sysno_old = $sysno;
    } 
    if ($sysno_old == $sysno) {
        $record[] = $line;
    } else {

        foreach ($record as $linha_de_registro) {
            processaAlephseq($linha_de_registro);  
        }

        /* Processa os fixes */

        echo "$sysno \n";

        importToElastic($marc);
            
        $marc = [];
        $record = [];

    } 

    $sysno_old = $sysno;
    $i++;
}

// Send the last batch if it exists
if (!empty($params['body'])) {
    $responses = $client->bulk($params);
    //print_r($responses);
}




