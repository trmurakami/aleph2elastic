<?php 

include 'inc/functions_core.php';
include 'inc/config.php';


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
	$repetitive_fields = array("100","510","536","650","651","655","700","856","946","952","CAT");
	
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

	//print_r($marc);
	$body = [];

	//if (isset($marc["record"]["001"])) {
	//	print_r($marc["record"]["001"]["content"]);
	//}	
		
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
			if (!empty($person["4"])) {
				$potentialAction_correct = decode::potentialAction($person["4"]);
				$author["person"]["potentialAction"] = $potentialAction_correct;				
			}
			if (!empty($person["d"])) {
				$author["person"]["date"] = $person["d"];
			}						
			if (!empty($person["8"])) {
				$resultadoTematres = consultaTematres(trim($person["8"]));
				if (!empty($resultadoTematres["termo_correto"])) {
					$author["person"]["affiliation"]["name"] = $resultadoTematres["termo_correto"];
				} else {
					$author["person"]["affiliation"]["name_not_found"] = $resultadoTematres["termo_nao_encontrado"];
				}				
			}
			if (!empty($person["9"])) {	
				$author["person"]["affiliation"]["location"] = $person["9"];
			}
			if (!empty($potentialAction_correct)) {
				$author["person"]["USP"]["autor_funcao"] = $person["a"] . " / " . $potentialAction_correct;
			}

		}
		
		$body["doc"]["author"][] = $author;
		unset($person);
		unset($author); 
	}
	
	if (isset($marc["record"]["245"])) {
		if (isset($marc["record"]["245"]["b"][0])){
			$body["doc"]["name"] = $marc["record"]["245"]["a"][0] . ": " . $marc["record"]["245"]["b"][0];
		} else {
			$body["doc"]["name"] = $marc["record"]["245"]["a"][0];
		}
		 
	}

	if (isset($marc["record"]["246"])) {
		if (isset($marc["record"]["246"]["b"][0])){
			$body["doc"]["alternateName"] = $marc["record"]["246"]["a"][0] . ": " . $marc["record"]["246"]["b"][0];
		} else {
			$body["doc"]["alternateName"] = $marc["record"]["246"]["a"][0];
		} 
	}		
	
	if (isset($marc["record"]["260"])) {
		if (isset($marc["record"]["260"]["b"])){
			$body["doc"]["publisher"]["organization"]["name"] = $marc["record"]["260"]["b"][0];
		}
		if (isset($marc["record"]["260"]["a"])){	
			$body["doc"]["publisher"]["organization"]["location"] = $marc["record"]["260"]["a"][0];
		}	 
	}

	if (isset($marc["record"]["382"]["a"])) {
		foreach (($marc["record"]["382"]["a"]) as $meio_de_expressao) {
			$body["doc"]["USP"]["meio_de_expressao"][] = $meio_de_expressao;
		} 
	}

	if (isset($marc["record"]["500"]["a"])) {
		foreach (($marc["record"]["500"]["a"]) as $notas) {
			$body["doc"]["USP"]["notes"][] = $notas;
		} 
	}			
	
	if (isset($marc["record"]["502"])) {
		$body["doc"]["inSupportOf"] = $marc["record"]["502"]["a"][0]; 
	}

	if (isset($marc["record"]["510"]["a"])) {
		foreach (($marc["record"]["510"]["a"]) as $indexado) {
			$body["doc"]["USP"]["indexacao"][] = $indexado;
		} 
	}	

	if (isset($marc["record"]["520"]["a"])) {
		foreach (($marc["record"]["520"]["a"]) as $description) {
			$body["doc"]["description"][] = $description;
		} 
	}

	if (isset($marc["record"]["536"])) {
		foreach (($marc["record"]["536"]) as $funder) {
			//print_r($funder);
				$resultado_tematres_funder = consultaTematres($funder["a"]);
				if (!empty($resultado_tematres_funder["termo_correto"])) {
					$body["doc"]["funder"][] = $resultado_tematres_funder["termo_correto"];
				} else {
					$body["doc"]["funder"][] = $resultado_tematres_funder["termo_nao_encontrado"];
				}							
		} 
	}	
	
	if (isset($marc["record"]["590"])) {
		if (!empty($marc["record"]["590"]["d"])){
			$body["doc"]["USP"]["areaconcentracao"] = $marc["record"]["590"]["d"][0];
		}
		if (!empty($marc["record"]["590"]["m"])){
			$body["doc"]["USP"]["fatorimpacto"] = $marc["record"]["590"]["m"][0];
		}
		if (!empty($marc["record"]["590"]["n"])){
			$body["doc"]["USP"]["grupopesquisa"] = explode(";", $marc["record"]["590"]["n"][0]);
		}					

	}
	
	if (isset($marc["record"]["599"])) {
		$body["doc"]["USP"]["programa_pos_sigla"] = $marc["record"]["599"]["a"][0];
		$body["doc"]["USP"]["programa_pos_nome"] = $marc["record"]["599"]["b"][0]; 
	}	
	
	
	if (isset($marc["record"]["650"])) {
		foreach (($marc["record"]["650"]) as $subject) {
			if (isset($subject["a"])) {
				$body["doc"]["about"][] = $subject["a"];
			}	
		}
	}

	if (isset($marc["record"]["651"])) {
		foreach (($marc["record"]["651"]) as $subject) {
			$body["doc"]["about"][] = $subject["a"];
		}
	}

	if (isset($marc["record"]["655"])) {
		foreach ($marc["record"]["655"] as $genero_e_forma) {
			$body["doc"]["USP"]["about"]["genero_e_forma"][] = $genero_e_forma["a"];
		}
	}		
	
	if (isset($marc["record"]["700"])) {
	
		foreach (($marc["record"]["700"]) as $person) { 
			$author["person"]["name"] = $person["a"];
			if (!empty($person["8"])) {
				$resultadoTematres = consultaTematres(trim($person["8"]));
				if (!empty($resultadoTematres["termo_correto"])) {
					$author["person"]["affiliation"]["name"] = $resultadoTematres["termo_correto"];
				} else {
					$author["person"]["affiliation"]["name_not_found"] = $resultadoTematres["termo_nao_encontrado"];
				}				
			}
			if (!empty($person["9"])) {
				$author["person"]["affiliation"]["location"] = $person["9"];
			}
			if (!empty($person["4"])) {
				$potentialAction_correct = decode::potentialAction($person["4"]);
				$author["person"]["potentialAction"] = $potentialAction_correct;
			}
			if (!empty($potentialAction_correct)) {
				$author["person"]["USP"]["autor_funcao"] = $person["a"] . " / " . $potentialAction_correct;
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
		if (isset($marc["record"]["773"]["t"])) {
			$body["doc"]["isPartOf"]["name"] = $marc["record"]["773"]["t"][0];
		}
		if (isset($marc["record"]["773"]["h"])) {
			$body["doc"]["isPartOf"]["USP"]["dados_do_periodico"] = $marc["record"]["773"]["h"][0];
		}		
		if (isset($marc["record"]["773"]["x"])) {
			$issn_array = explode(";",$marc["record"]["773"]["x"][0]);
			$body["doc"]["isPartOf"]["issn"] = $issn_array;

			foreach ($issn_array as $issn_query) {
				if (empty($body["doc"]["USP"]["serial_metrics"])){
					$result_qualis = qualis_issn(trim($issn_query));
					if ($result_qualis["hits"]["total"] == 1) {
						$body["doc"]["USP"]["serial_metrics"] = $result_qualis["hits"]["hits"][0]["_source"];
					}	
				}
				if (empty($body["doc"]["USP"]["JCR"])){
					$result_jcr = jcr_issn(trim($issn_query));
					if ($result_jcr["hits"]["total"] == 1) {
						$body["doc"]["USP"]["JCR"] = $result_jcr["hits"]["hits"][0]["_source"];
					}
				}
				if (empty($body["doc"]["USP"]["WOS"])){
					$result_wos = wos_issn(trim($issn_query));
					if ($result_wos["hits"]["total"] == 1) {
						$body["doc"]["USP"]["WOS"] = $result_wos["hits"]["hits"][0]["_source"];
					}
				}
				if (empty($body["doc"]["USP"]["citescore"])){
					$result_citescore = citescore_issn(trim($issn_query));
					if ($result_citescore["hits"]["total"] >= 1) {
						$body["doc"]["USP"]["citescore"] = $result_citescore["hits"]["hits"][0]["_source"];
					}
				}				
				//if (empty($body["doc"]["USP"]["citescore_cover"])){
				//	$result_citescore_cover = search_citescore(trim($issn_query));
				//	if ($result_citescore_cover["hits"]["total"] == 1) {
				//		$body["doc"]["USP"]["citescore_cover"] = $result_citescore_cover["hits"]["hits"][0]["_source"];
				//	}
				//}				
			}			
		}	
	}
	
	if (isset($marc["record"]["856"])) {
	
		foreach ($marc["record"]["856"] as $url) {
			if ($url["3"] == "Documento completo" | $url["3"] == "BDTD" | $url["3"] == "Servidor ECA" | $url["3"] == "DOI" | $url["3"] == "E-Livro" | trim($url["3"]) == "Ovid" | $url["3"] == "MOMW" | $url["3"] == "Science Direct - Environmental Science" | $url["3"] == "Recursos online" | $url["3"] == "CRCnetBase" | $url["3"] == "Base local ECA" | $url["3"] == "Springer" | $url["3"] == "Science Direct - Energy" | $url["3"] == "Ebrary" | $url["3"] == "Referex Engineering" ) {
				$body["doc"]["url"][] = $url["u"];
			}					
		} 	


	}			
	
	if (isset($marc["record"]["945"])) {
		if (isset($marc["record"]["945"]["j"])){
			$body["doc"]["datePublished"] = $marc["record"]["945"]["j"][0];
		}		
		$body["doc"]["type"] = $marc["record"]["945"]["b"][0];
		
		if (isset($marc["record"]["945"]["l"])){
			$body["doc"]["USP"]["internacionalizacao"] = $marc["record"]["945"]["l"][0];
		}		
		switch ($marc["record"]["945"]["b"][0]) {
		    case "MONOGRAFIA/LIVRO":
			$body["doc"]["numberOfPages"] = $marc["record"]["300"]["a"][0];
		    break;
		    case "TESE":
			$body["doc"]["dateCreated"] = $marc["record"]["945"]["i"][0];
		    break;		    
		}
	}
	
	if (isset($marc["record"]["946"])) {	
		foreach (($marc["record"]["946"]) as $authorUSP) {
			$authorUSP_array["name"] = $authorUSP["a"];
			if (isset($authorUSP["b"])) {
				$authorUSP_array["codpes"] = $authorUSP["b"];
			}	
			$authorUSP_array["unidadeUSP"] = decode::unidadeAntiga($authorUSP["e"]);
			if (isset($authorUSP["j"])) {
				$authorUSP_array["regime_de_trabalho"] = $authorUSP["j"];
			}	
			if (isset($authorUSP["k"])) {
				$authorUSP_array["funcao"] = $authorUSP["k"];
			}		
			if (isset($authorUSP["g"])) {
				$authorUSP_array["departament"] = $authorUSP_array["unidadeUSP"] . "-" . $authorUSP["g"];
			}	
			$body["doc"]["authorUSP"][] = $authorUSP_array;
			$body["doc"]["unidadeUSP"][] = decode::unidadeAntiga($authorUSP["e"]);	
		}
	}
	
	if (isset($marc["record"]["952"])) {
		foreach ($marc["record"]["952"] as $subject_BDTD) {			
			if (isset($subject_BDTD["f"])) {
				$body["doc"]["USP"]["about_BDTD"][] = $subject_BDTD["a"];
			}	
		}
	}
	
	if (isset($marc["record"]["CAT"])) {
		foreach ($marc["record"]["CAT"] as $CAT) {
			if (isset ($CAT["a"])){
				$CAT_array["cataloger"] = $CAT["a"];
			} else {
				$CAT_array["cataloger"] = "N/A";
			} 	
			$CAT_array["date"] = substr($CAT["c"], 0, -2);
			$body["doc"]["USP"]["CAT"][] = $CAT_array;
		}
		unset($CAT);
		unset($CAT_array);		
	}		
	
	$body["doc_as_upsert"] = true;
	return $body;
	
}

/*
* Decodifica dados *
*/
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
		    case "spa":
				return "Espanhol";
		    	break;
		    case "fre":
				return "Francês";
		    	break;
		    case "mul":
				return "Multiplos idiomas";
		    	break;
		    case "ger":
				return "Alemão";
		    	break;
		    case "ita":
				return "Italiano";
		    	break;
		    case "jpn":
				return "Japonês";
		    	break;
		    case "rus":
				return "Russo";
		    	break;
		    case "chi":
				return "Chinês";
		    	break;
		    case "pol":
				return "Polonês";
		    	break;
		    case "dut":
				return "Holandês";
		    	break;
		    case "tur":
				return "Turco";
		    	break;
		    case "hun":
				return "Húngaro";
		    	break;
		    case "dan":
				return "Dinamarquês";
		    	break;
		    case "cze":
				return "Checo";
		    	break;
		    case "scc":
				return "Sérvio";
		    	break;
		    case "swe":
				return "Sueco";
		    	break;
		    case "ara":
				return "Árabe";
		    	break;
		    case "cat":
				return "Catalão";
		    	break;
		    case "kor":
				return "Coreano";
		    	break;
		    case "heb":
				return "Hebreu";
		    	break;
		    case "lat":
				return "Latin";
		    	break;																																																																																																									
		    default:
		    	return $language;		    		    
		}
	}
	
	/* Decodificar pais */
	static function country($country){
		switch ($country) {
		    case "ag":
				return "Argentina";
		    	break;
		    case "aru":
				return "Estados Unidos";
		    	break;
		    case "alu":
				return "Estados Unidos";
		    	break;											
		    case "at":
				return "Austrália";
		    	break;
		    case "au":
				return "Áustria";
		    	break;
		    case "be":
				return "Bélgica";
		    	break;																	
		    case "bl":
				return "Brasil";
		    	break;
		    case "bo":
				return "Bolívia";
		    	break;
		    case "bu":
				return "Bulgária";
		    	break;
		    case "cau":
				return "Estados Unidos";
		    	break;
		    case "cb":
				return "Camboja";
		    	break;					
		    case "cc":
				return "China";
		    	break;
		    case "ch":
				return "China";
		    	break;
		    case "ci":
				return "Croácia";
		    	break;
		    case "ck":
				return "Colômbia";
		    	break;
		    case "cl":
				return "Chile";
		    	break;
		    case "cou":
				return "Estados Unidos";
		    	break;					
		    case "cr":
				return "Costa Rica";
		    	break;
		    case "cu":
				return "Cuba";
		    	break;
		    case "dcu":
				return "Estados Unidos";
		    	break;
		    case "dk":
				return "Dinamarca";
		    	break;
		    case "dr":
				return "República Dominicana";
		    	break;
		    case "ec":
				return "Equador";
		    	break;
		    case "enk":
				return "Inglaterra";
		    	break;
		    case "es":
				return "El Salvador";
		    	break;					
		    case "et":
				return "Etiópia";
		    	break;
		    case "fi":
				return "Finlândia";
		    	break;
		    case "flu":
				return "Estados Unidos";
		    	break;
		    case "fr":
				return "França";
		    	break;
		    case "gb":
				return "República de Kiribati";
		    	break;
		    case "gr":
				return "Grécia";
		    	break;
		    case "gw":
				return "Alemanha";
		    	break;
		    case "gt":
				return "Guatemala";
		    	break;
		    case "hiu":
				return "Estados Unidos";
		    	break;										
		    case "hk":
				return "Hong-Kong";
		    	break;
		    case "ho":
				return "Honduras";
		    	break;					
		    case "hu":
				return "Hungria";
		    	break;
		    case "iau":
				return "Estados Unidos";
		    	break;					
		    case "ic":
				return "Islândia";
		    	break;					
		    case "ie":
				return "Irlanda";
		    	break;
		    case "ii":
				return "Índia";
		    	break;
		    case "ilu":
				return "Estados Unidos";
		    	break;
		    case "inu":
				return "Estados Unidos";
		    	break;					
		    case "io":
				return "Indonésia";
		    	break;					
		    case "ir":
				return "Irã";
		    	break;
		    case "is":
				return "Israel";
		    	break;
		    case "it":
				return "Itália";
		    	break;
		    case "ja":
				return "Japão";
		    	break;
		    case "ke":
				return "Quênia";
		    	break;					
		    case "ko":
				return "Coreia do Sul";
		    	break;
		    case "li":
				return "Lituânia";
		    	break;					
		    case "mau":
				return "Estados Unidos";
		    	break;
		    case "mdu":
				return "Estados Unidos";
		    	break;
		    case "miu":
				return "Estados Unidos";
		    	break;					
		    case "mou":
				return "Estados Unidos";
		    	break;
		    case "mr":
				return "Marrocos";
		    	break;										
		    case "mx":
				return "México";
		    	break;
		    case "my":
				return "Malásia";
		    	break;
		    case "mz":
				return "Moçambique";
		    	break;										
		    case "ne":
				return "Holanda";
		    	break;
		    case "ng":
				return "Nigéria";
		    	break;					
		    case "nl":
				return "Nova Caledonia";
		    	break;
		    case "nmu":
				return "Estados Unidos";
		    	break;					
		    case "no":
				return "Noruega";
		    	break;
		    case "nr":
				return "Nigéria";
		    	break;
		    case "nju":
				return "Estados Unidos";
		    	break;
		    case "nyu":
				return "Estados Unidos";
		    	break;
		    case "nvu":
				return "Estados Unidos";
		    	break;					
		    case "nz":
				return "Nova Zelândia";
		    	break;
		    case "ohu":
				return "Estados Unidos";
		    	break;					
		    case "pau":
				return "Estados Unidos";
		    	break;
		    case "pe":
				return "Peru";
		    	break;
		    case "ph":
				return "Filipinas";
		    	break;					
		    case "pk":
				return "Paquistão";
		    	break;
		    case "pl":
				return "Polônia";
		    	break;
		    case "pn":
				return "Panamá";
		    	break;					
		    case "pr":
				return "Porto Rico";
		    	break;
		    case "po":
				return "Portugal";
		    	break;
		    case "py":
				return "Paraguai";
		    	break;	
		    case "riu":
				return "Estados Unidos";
		    	break;
		    case "rm":
				return "Romênia";
		    	break;
		    case "ru":
				return "Rússia";
		    	break;
		    case "sa":
				return "África do Sul";
		    	break;
		    case "si":
				return "Singapura";
		    	break;
		    case "sp":
				return "Espanha";
		    	break;
		    case "stk":
				return "Escócia";
		    	break;
		    case "su":
				return "Arábia Saudita";
		    	break;					
		    case "sw":
				return "Suécia";
		    	break;
		    case "sz":
				return "Suiça";
		    	break;
		    case "ti":
				return "Tunísia";
		    	break;					
		    case "th":
				return "Tailândia";
		    	break;
		    case "ts":
				return "Emirados Árabes Unidos";
		    	break;
		    case "tu":
				return "Turquia";
		    	break;
		    case "txu":
				return "Estados Unidos";
		    	break;
		    case "xo":
				return "Eslováquia";
		    	break;										
		    case "xr":
				return "República Checa";
		    	break;
		    case "xx":
				return "Desconhecido";
		    	break;
		    case "xxk":
				return "Reino Unido";
		    	break;																																																																																																																																																																																																																																																																																																				
		    case "xxu":
				return "Estados Unidos";
		    	break;
		    case "xxc":
				return "Canadá";
		    	break;
		    case "xv":
				return "Eslovênia";
		    	break;					
		    case "ua":
				return "Egito";
		    	break;
		    case "utu":
				return "Estados Unidos";
		    	break;
		    case "un":
				return "Ucrânia";
		    	break;										
		    case "uy":
				return "Uruguai";
		    	break;
		    case "uk":
				return "Reino Unido";
		    	break;
		    case "yu":
				return "Iugoslávia";
		    	break;
		    case "vau":
				return "Estados Unidos";
		    	break;
		    case "ve":
				return "Venezuela";
		    	break;
		    case "xr":
				return "República Tcheca";
		    	break;
		    case "wau":
				return "Estados Unidos";
		    	break;
		    case "wiu":
				return "Estados Unidos";
		    	break;																																																	
		    default:
		    	return $country;		    		    
		}
	}

	/* Decodificar função */
	static function potentialAction($potentialAction){
		switch ($potentialAction) {
		    case "adapt":
				return "Adaptação";
		    	break;
		    case "arranjo mus":
				return "Arranjo musical / Arranjador musical";
		    	break;
		    case "comp":
				return "Compilador";
		    	break;
		    case "compos":
				return "Compositor musical";
		    	break;
		    case "coord pesq musico":
				return "Coordenador de pesquisa musicológica";
		    	break;																						
		    case "co-orient":
				return "Co-orientador";
		    	break;
		    case "ed":
				return "Editor";
		    	break;
		    case "elab":
				return "Elaborador";
		    	break;
		    case "entrev":
				return "Entrevistador";
		    	break;
		    case "org":
				return "Organizador";
		    	break;										
		    case "pref":
				return "Prefácio";
		    	break;
		    case "rev":
				return "Revisor";
		    	break;
		    case "text":
				return "Autor texto";
		    	break;
		    case "trad":
				return "Tradução";
		    	break;
		    case "transc":
				return "Transcrição";
		    	break;																																				
		    case "orient":
				return "Orientador";
		    	break;
			
		    default:
		    	return $potentialAction;	    
		}
	}

	/* Vincular Unidades antigas */
	static function unidadeAntiga($unidade){
		switch ($unidade) {
		    case "IFQSC-Q":
				return "IQSC";
		    	break;
		    case "IFQSC-F":
				return "IFSC";
		    	break;
		    case "ICMSC":
				return "ICMC";
		    	break;
		    case "CBM":
				return "CEBIMAR";
		    	break;
		    case "HPRLLP":
				return "HRAC";
		    	break;
		    default:
		    	return $unidade;
		}
	}	

}	 

/*
* Consulta o Tematres *
*/
function consultaTematres ($termo) {
	$ch = curl_init();
	$method = "GET";
	$url = 'http://bdpife2.sibi.usp.br/instituicoes/vocab/services.php?task=fetch&arg='.rawurlencode($termo).'&output=json';                            
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));	
	$result_get_id_tematres = curl_exec($ch);
	$resultado_get_id_tematres = json_decode($result_get_id_tematres, true);
	curl_close($ch);


	if ($resultado_get_id_tematres["resume"]["cant_result"] != 0) {
		foreach($resultado_get_id_tematres["result"] as $key => $val) {
			$term_key = $key;
		}
		
		$ch = curl_init();
		$method = "GET";
		$url = 'http://bdpife2.sibi.usp.br/instituicoes/vocab/services.php?task=fetchTerm&arg='.$term_key.'&output=json';
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result_term = curl_exec($ch);
		$resultado_term = json_decode($result_term, true);
		$termo_correto = $resultado_term["result"]["term"]["string"];
		curl_close($ch);
	} else {
		$termo_nao_encontrado = $termo;
	}

	return compact('termo_correto','termo_nao_encontrado');

}

/*
* Consulta o Qualis de uma Obra *
*/
function qualis_issn ($issn) {
		$index = "serial_metrics";
		$type = "qualis";
		$body["query"]["ids"]["values"][] = $issn;
		global $client;
		$params = [];
		$params["index"] = $index;
		$params["type"] = $type;
		$params["body"] = $body;
		
		$response = $client->search($params);        
		return $response;
}

/*
* Consulta o JCR de uma Obra *
*/
function jcr_issn ($issn) {
		$index = "serial_jcr";
		$type = "JCR";
		$body["query"]["ids"]["values"][] = $issn;
		global $client;
		$params = [];
		$params["index"] = $index;
		$params["type"] = $type;
		$params["body"] = $body;
		
		$response = $client->search($params);        
		return $response;
}

/*
* Consulta indexação na Web of Science de uma Obra *
*/
function wos_issn ($issn) {
		$index = "serial_web_of_science";
		$type = "WOS";
		$body["query"]["ids"]["values"][] = $issn;
		global $client;
		$params = [];
		$params["index"] = $index;
		$params["type"] = $type;
		$params["body"] = $body;
		
		$response = $client->search($params);        
		return $response;
}

/*
* Consulta indexação na Web of Science de uma Obra *
*/
function citescore_issn ($issn) {
		$index = "citescore";
		$type = "issn";
		$body["query"]["ids"]["values"][] = $issn;
		global $client;
		$params = [];
		$params["index"] = $index;
		$params["type"] = $type;
		$params["body"] = $body;
		
		$response = $client->search($params);        
		return $response;
}


/*
* Consulta Citescore e SJR *
*/
function search_citescore ($issn) {
		$index = "citescore_cover";		
		$type = "issn";
		$body["query"]["ids"]["values"][] = $issn;
		global $client;		
		$params = [];
		$params["index"] = $index;
		$params["type"] = $type;
		$params["body"] = $body;		
		$response = $client->search($params);

		if ($response["hits"]["total"] > 0){
			return $response;
		} else {	
			$data = citescore($issn);
			if (!empty($data)) {
				$result_data["hits"]["total"] = 1;
				$result_data["hits"]["hits"][0]["_source"] = $data;
				return $result_data;
			}
		} 
}

/*
* Obtem dados da API do Citescore e SJR *
*/
function citescore ($issn) {
				global $api_elsevier;
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.elsevier.com/content/serial/title/issn/'.$issn.'?apiKey='.$api_elsevier.'',
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ));
        // Send the request & save response to $resp
        $data = curl_exec($curl);
				print_r($data);				

				$index = "citescore_cover";
				$data = json_decode ($data,TRUE);
				$body["doc"] = $data;
				$body["doc_as_upsert"] = true;
				elasticsearch::elastic_update ($issn,"issn",$body);
				return $data;				


        // Close request to clear up some resources
        curl_close($curl);
}


?>