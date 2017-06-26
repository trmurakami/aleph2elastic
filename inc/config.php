<?php

	/* Exibir erros */ 
	ini_set('display_errors', 1); 
	ini_set('display_startup_errors', 1); 
	error_reporting(E_ALL);

	/* Endereço do server, sem http:// */ 
	$hosts = [
		'172.31.0.90' 
	];

	
	/* Configurações do Elasticsearch */
	$index = "sibi";
	$type = "producao";

	/* Load libraries for PHP composer */ 
	require (__DIR__.'/../vendor/autoload.php'); 

	/* Load Elasticsearch Client */ 
	$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 


?>