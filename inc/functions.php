<?php 

include 'inc/functions_core.php';

/*
* Converte Alephseq em JSON *
*/
function processaAlephseq($line) {

	global $marc;
	global $i;
	global $id;
	 
	$id = substr($line, 0, 9);
	$field = substr($line, 10, 3);
	//$ind_1 = substr($line, 13, 1);
	//$ind_2 = substr($line, 14, 1);	

	
	$control_fields = array("LDR","FMT","001","008");
	$repetitive_fields = array("100","650","700","856","946","CAT");
	
	if (in_array($field,$control_fields)) {
		$marc["record"][$field]["content"] = trim(substr($line, 18));			
		
	} elseif (in_array($field,$repetitive_fields)) {				
		$content = explode("\$", substr($line, 18));					
		foreach ($content as &$content_line) {
			if (!empty($content_line)) {
				$marc["record"][$field][$i][substr($content_line, 0, 1)] = trim(substr($content_line, 1));
			}
						
		
		}
		
	
	} else {	
		$content = explode("\$", substr($line, 18));	
		foreach ($content as &$content_line) {
			if (!empty($content_line)) {
				$marc["record"][$field][substr($content_line, 0, 1)][] = trim(substr($content_line, 1));
			}			
		
		}
			
	}
	
	//$marc["record"][$field]["ind_1"] = $ind_1;
	//$marc["record"][$field]["ind_2"] = $ind_2;
	
	$i++;
	
	
}

/*
* Processa o fixes *
*/
function fixes($marc) {
	
	global $i;

	print_r($marc);
	$body = [];
		
	if ($marc["record"]["BAS"]["a"][0] == 01){
		unset($marc);
		$body["naoIndexar"] = true;
	}	
	
	if (isset($marc)) {	
		if ($marc["record"]["BAS"]["a"][0] == 02){
			unset($marc);
			$body["naoIndexar"] = true;
		}
	}

	if (isset($marc)) {	
		if ($marc["record"]["BAS"]["a"][0] == 03){
			$body["doc"]["base"][] = "Teses e dissertações";
		}
	}
	
	if (isset($marc)) {	
		if ($marc["record"]["BAS"]["a"][0] == 04){
			$body["doc"]["base"][] = "Produção científica";
		}
	}
	
	if (isset($marc["record"]["020"])) {
		$body["doc"]["isbn"] = $marc["record"]["020"]["a"][0]; 
	}
	
	if (isset($marc["record"]["024"])) {
		$body["doc"]["doi"] = $marc["record"]["024"]["a"][0];
	}
	
	if (isset($marc["record"]["041"])) {
		$language_correct = decode::language($marc["record"]["041"]["a"][0]);
		$body["doc"]["language"][] = $language_correct;
	}
	
	if (isset($marc["record"]["044"])) {
		$country_correct = decode::country($marc["record"]["044"]["a"][0]);
		$body["doc"]["country"][] = $country_correct;
	}				
	
	if (isset($marc["record"]["100"])) {
	
		foreach (($marc["record"]["100"]) as $person) { 
			$author["person"]["name"] = $person["a"];
			if (!empty($person["8"])) {
				$author["person"]["affiliation"]["name"] = $person["8"];
			}
			if (!empty($person["9"])) {	
				$author["person"]["affiliation"]["location"] = $person["9"];
			}				
		}
		
		$body["doc"]["author"][] = $author;
		unset($person);
		unset($author); 
	}
	
	if (isset($marc["record"]["245"])) {
		$body["doc"]["name"] = $marc["record"]["245"]["a"][0]; 
	}
	
	if (isset($marc["record"]["260"])) {
		$body["doc"]["publisher"]["organization"]["name"] = $marc["record"]["260"]["b"][0];
		$body["doc"]["publisher"]["organization"]["location"] = $marc["record"]["260"]["a"][0]; 
	}
	
	if (isset($marc["record"]["650"])) {
		foreach (($marc["record"]["650"]) as $subject) {
			$body["doc"]["about"][] = $subject["a"];
		}
	}
	
	if (isset($marc["record"]["700"])) {
	
		foreach (($marc["record"]["700"]) as $person) { 
			$author["person"]["name"] = $person["a"];
			if (!empty($person["8"])) {			
				$author["person"]["affiliation"]["name"] = $person["8"];
			}
			if (!empty($person["9"])) {
				$author["person"]["affiliation"]["location"] = $person["9"];
			}	
			$body["doc"]["author"][] = $author;
			unset($person);
			unset($author);			
		} 
	}
	
	
	if (isset($marc["record"]["711"])) {
		$body["doc"]["releasedEvent"] = $marc["record"]["711"]["a"][0];
	}	

	if (isset($marc["record"]["773"])) {
		$body["doc"]["isPartOf"] = $marc["record"]["773"]["t"][0];
	}
	
	if (isset($marc["record"]["856"])) {
	
		foreach ($marc["record"]["856"] as $url) {
			if ($url["3"] == "Documento completo") {
				$body["doc"]["url"][] = $url["u"];
			}			
		} 	


	}			
	
	if (isset($marc["record"]["945"])) {
		$body["doc"]["datePublished"] = $marc["record"]["945"]["j"][0];
		$body["doc"]["type"] = $marc["record"]["945"]["b"][0];
		$body["doc"]["USP"]["internacionalizacao"] = $marc["record"]["945"]["l"][0];
		
		switch ($marc["record"]["945"]["b"][0]) {
		    case "MONOGRAFIA/LIVRO":
			$body["doc"]["numberOfPages"] = $marc["record"]["300"]["a"][0];
		    break;
		}		
		
		

	}
	
	if (isset($marc["record"]["946"])) {
	
		foreach (($marc["record"]["946"]) as $authorUSP) {
			$authorUSP_array["name"] = $authorUSP["a"];
			$authorUSP_array["unidadeUSP"] = $authorUSP["e"];
			$authorUSP_array["departament"] = $authorUSP["g"];
			$body["doc"]["authorUSP"][] = $authorUSP_array;
			$body["doc"]["unidadeUSP"][] = $authorUSP["e"];	
		}

	}
	
	
	
	$body["doc_as_upsert"] = true;
	return $body;
	
}

class decode {

	/* Pegar o tipo de material */
	static function get_type($material_type){
		switch ($material_type) {
		    case "ARTIGO DE JORNAL":
			return "article-newspaper";
		    break;
		    case "ARTIGO DE PERIODICO":
			return "article-journal";
		    break;
		    case "PARTE DE MONOGRAFIA/LIVRO":
			return "chapter";
		    break;
		    case "APRESENTACAO SONORA/CENICA/ENTREVISTA":
			return "interview";
		    break;
		    case "TRABALHO DE EVENTO-RESUMO":
			return "paper-conference";
		    break;
		    case "TRABALHO DE EVENTO":
			return "paper-conference";
		    break;     
		    case "TESE":
			return "thesis";
		    break;          
		    case "TEXTO NA WEB":
			return "post-weblog";
		    break;
		}
	}
	
	/* Decodificar idioma */
	static function language($language){
		switch ($language) {
		    case "por":
			return "Português";
		    break;
		    case "eng":
			return "Inglês";
		    break;		    
		}
	}
	
	/* Decodificar pais */
	static function country($country){
		switch ($country) {
		    case "bl":
			return "Brasil";
		    break;
		    case "xxu":
			return "Estados Unidos";
		    break;		    
		}
	}
}	 


?>