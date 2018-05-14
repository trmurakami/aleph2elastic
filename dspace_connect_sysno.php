#!/usr/bin/php
<?php 

chdir(__DIR__);
include 'inc/config.php';
include 'inc/functions.php';

//$sysno = '002872732';
//$sysno = '002860367';
//$sysno = '002881902';
$sysno = '002881896';



$result_oracle_sysno = oracle_sysno($sysno);
foreach ($result_oracle_sysno as $record_line) {
    processaAlephseq($record_line);
}
processaFixes($marc,$id);

// Close the Oracle connection
oci_close($conn);



function processaFixes ($marc,$id){

	global $type;	

	/* Processa os fixes */
	if (isset($marc["record"]["BAS"])) {

		switch ($marc["record"]["BAS"]["a"][0]) {

            case 04:            
                $data_string = buildDC($marc,$id);                
                $cookies = loginREST();
                //echo "\n\n";
                //print_r($data_string);
                //echo "\n\n";
                $searchExisting = searchItem($id, $cookies);
                if (empty($searchExisting)) {
                    createItem($cookies,$data_string);
                }                
                logoutREST($cookies);
                $marc = [];
                break;
                
			default:
				break;
		}
	}
}


function buildDC($marc,$sysno){
    //print_r($marc);
    $arrayDC["type"] = "item";

    /* Title */
    $title["key"] = "dc.title";
    $title["language"] = "pt_BR";
    $title["value"] = $marc["record"]["245"]["a"][0];
    $arrayDC["metadata"][] = $title;
    $title = [];

    /* Sysno */
    $sysnoArray["key"] = "usp.sysno";
    $sysnoArray["language"] = "pt_BR";
    $sysnoArray["value"] = $sysno;    
    $arrayDC["metadata"][] = $sysnoArray;
    $sysnoArray = [];

    /* Abstract */
    if (!empty($marc["record"]["940"]["a"])){
        $abstractArray["key"] = "dc.description.abstract";
        $abstractArray["language"] = "pt_BR";
        $abstractArray["value"] = $marc["record"]["940"]["a"][0];    
        $arrayDC["metadata"][] = $abstractArray;
        $abstractArray = []; 
    } elseif (!empty($marc["record"]["520"]["a"])){
        $abstractArray["key"] = "dc.description.abstract";
        $abstractArray["language"] = "pt_BR";
        $abstractArray["value"] = $marc["record"]["520"]["a"][0];    
        $arrayDC["metadata"][] = $abstractArray;
        $abstractArray = []; 
    }

    
    /* DateIssued */
    $dateIssuedArray["key"] = "dc.date.issued";
    $dateIssuedArray["language"] = "pt_BR";
    $dateIssuedArray["value"] = $marc["record"]["260"]["c"][0];    
    $arrayDC["metadata"][] = $dateIssuedArray;
    $dateIssuedArray = [];
    
    /* DOI */
    if (!empty($marc["record"]["024"]["a"])){
        $DOIArray["key"] = "dc.identifier.doi";
        $DOIArray["language"] = "pt_BR";
        $DOIArray["value"] = $marc["record"]["024"]["a"][0];    
        $arrayDC["metadata"][] = $DOIArray;
        $DOIArray = []; 
    }
    
    /* IsPartOf */
    $IsPartOfArray["key"] = "dc.relation.ispartof";
    $IsPartOfArray["language"] = "pt_BR";
    $IsPartOfArray["value"] = $marc["record"]["773"]["t"][0];    
    $arrayDC["metadata"][] = $IsPartOfArray;
    $IsPartOfArray = [];       

    /* Authors */

    foreach ($marc["record"]["100"] as $author) {
        $authorArray["key"] = "dc.contributor.author";
        $authorArray["language"] = "pt_BR";
        $authorArray["value"] = $author["a"];    
        $arrayDC["metadata"][] = $authorArray; 
        $authorArray = [];
        $author = [];     
    }      

    foreach ($marc["record"]["700"] as $author) {
        $authorArray["key"] = "dc.contributor.author";
        $authorArray["language"] = "pt_BR";
        $authorArray["value"] = $author["a"];    
        $arrayDC["metadata"][] = $authorArray; 
        $authorArray = [];
        $author = [];     
    }

    /* Unidade USP */
    foreach ($marc["record"]["946"] as $unidadeUSP) {
        $unidadeUSPArray["key"] = "usp.unidadeUSP";
        $unidadeUSPArray["language"] = "pt_BR";
        $unidadeUSPArray["value"] = $unidadeUSP["e"];    
        $arrayDC["metadata"][] = $unidadeUSPArray; 
        $unidadeUSPArray = [];     
    }    

    /* Subject */
    
    foreach ($marc["record"]["650"] as $subject) {
        $subjectArray["key"] = "dc.subject.other";
        $subjectArray["language"] = "pt_BR";
        $subjectArray["value"] = $subject["a"];    
        $arrayDC["metadata"][] = $subjectArray; 
        $subjectArray = [];     
    }      

    $jsonDC = json_encode($arrayDC);
    return $jsonDC;

}

function loginREST(){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"http://172.31.1.37:8080/rest/login");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
                http_build_query(array('email' => 'dgti@dt.sibi.usp.br','password' => '123456'))
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $server_output = curl_exec($ch);
    $output_parsed = explode(" ",$server_output);
    
    return $output_parsed[3];
    
    curl_close ($ch);  

} 

function testREST($cookies){

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $cookies"));
  curl_setopt($ch, CURLOPT_URL,"http://172.31.1.37:8080/rest/status");
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
  $server_output = curl_exec($ch);
  print_r($server_output);  
  curl_close ($ch);  

} 

function logoutREST($cookies){

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $cookies"));
  curl_setopt($ch, CURLOPT_URL,"http://172.31.1.37:8080/rest/logout");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
  $server_output = curl_exec($ch);
  print_r($server_output);  
  curl_close ($ch);  

} 

function createItem($cookies,$data_string){

  $ch = curl_init();          
  curl_setopt($ch, CURLOPT_URL, "http://172.31.1.37:8080/rest/collections/6b9f840e-7bfe-4511-a4f5-2e181990679e/items");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Cookie: $cookies",                                                                          
      'Content-Type: application/json'      
      )                                                                       
  );  
  $output = curl_exec($ch);
  var_dump($output);
  curl_close($ch);

}

function searchItem($sysno, $cookies)
{
    $data_string = "{\"key\":\"usp.sysno\", \"value\":\"$sysno\"}";  
    $ch = curl_init();          
    curl_setopt($ch, CURLOPT_URL, "http://172.31.1.37:8080/rest/items/find-by-metadata-field");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    if (!empty($cookies)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Cookie: $cookies",                                                                          
            'Content-Type: application/json'      
            )                                                                       
        );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    if (!empty($result)) {
        return $result[0]["uuid"];
    } else {
        return "";
    }        
    curl_close($ch);
}

function deleteItem($uuid, $cookies)
{
    $ch = curl_init();          
    curl_setopt($ch, CURLOPT_URL, "http://172.31.1.37:8080/rest/items/$uuid");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    if (!empty($cookies)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Cookie: $cookies",                                                                          
            'Content-Type: application/json'      
            )                                                                       
        );
    }
    $output = curl_exec($ch);
    $result = json_decode($output, true);
    return $result;
    curl_close($ch);
}



?>