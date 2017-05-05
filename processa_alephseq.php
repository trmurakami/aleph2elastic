<?php

$marc = [];

include 'inc/functions.php';


/* Obtém os dados do STDIN e converte para JSON  */
while( $line = fgets(STDIN) ) {

  processaAlephseq($line);

}

/* Processa os fixes */

if (isset($marc)){
	$body = fixes($marc); 
}


echo "\n";
if (!empty($body)){
	print_r($body);
} else {
	echo "Registro não é da base 03 ou 04";
}	
echo "\n\n";


?>