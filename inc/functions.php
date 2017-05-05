<?php 

/*
* Converte Alephseq em JSON *
*/
function processaAlephseq($line) {

	global $marc;
	 
	$id = substr($line, 0, 9);
	$field = substr($line, 10, 3);
	$ind_1 = substr($line, 13, 1);
	$ind_2 = substr($line, 14, 1);
	
	$control_fields = array("LDR","FMT","001","008");
	
	if (in_array($field,$control_fields)) {
		substr($line, 1);
		
		if (!empty($content_line)) {
			$marc["record"][$field]["content"] = substr($line, 18);
		}	
		
	} else {	
		$content = explode("\$", substr($line, 18));		
		foreach ($content as &$content_line) {
			if (!empty($content_line)) {
				$marc["record"][$field][substr($content_line, 0, 1)][] = substr($content_line, 1);
			}
		
		}		
	}
	
	$marc["record"][$field]["ind_1"] = $ind_1;
	$marc["record"][$field]["ind_2"] = $ind_2;

}

/*
* Processa o fixes *
*/
function fixes($marc) {

	print_r($marc);
	$body = [];
		

	if ($marc["record"]["BAS"]["a"][0] == 01){
		unset($marc);
	}	
	
	if (isset($marc)) {	
		if ($marc["record"]["BAS"]["a"][0] == 02){
			unset($marc);
		}
	}

	if (isset($marc)) {	
		if ($marc["record"]["BAS"]["a"][0] == 03){

		}
	}
	
	if (isset($marc)) {	
		if ($marc["record"]["BAS"]["a"][0] == 04){

		}
	}			

	if (isset($marc["record"]["245"])) {
		$body["doc"]["name"] = $marc["record"]["245"]["a"]; 
	}
	
	if (isset($marc["record"]["260"])) {
		$body["doc"]["publisher"] = $marc["record"]["260"]["b"]; 
	}	
	
	return $body;
	
}


?>