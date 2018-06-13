#!/usr/bin/php
<?php 

chdir(__DIR__);
include 'inc/config.php';
include 'inc/functions.php';

//bloco da consulta SQL

// $consulta = "
// select DIM.IDFPESCPQ, DIM.CODPES, UNIDADE.SGLUND, SETOR.NOMABVSET, VINCULO.TIPVIN, IMGARQXML
// from DBMAINT.DIM_PESSOA_XMLUSP DIM
// inner join DBMAINT.VINCULOPESSOAUSP VINCULO on DIM.CODPES = VINCULO.CODPES
// inner join DBMAINT.UNIDADE UNIDADE on VINCULO.CODUND = UNIDADE.CODUND
// inner join DBMAINT.SETOR SETOR on VINCULO.CODSET = SETOR.CODSET
// where VINCULO.SITATL = 'A'
// AND VINCULO.TIPVIN = 'SERVIDOR'
// ";

$consulta = "
select DIM.IDFPESCPQ, DIM.CODPES, UNIDADE.SGLUND, SETOR.NOMABVSET, VINCULO.TIPVIN, IMGARQXML
from DBMAINT.DIM_PESSOA_XMLUSP DIM
inner join DBMAINT.VINCULOPESSOAUSP VINCULO on DIM.CODPES = VINCULO.CODPES
inner join DBMAINT.UNIDADE UNIDADE on VINCULO.CODUND = UNIDADE.CODUND
inner join DBMAINT.SETOR SETOR on VINCULO.CODSET = SETOR.CODSET
where VINCULO.SITATL = 'A'
AND TRUNC(DIM.DTAULTALT) >= TRUNC(SYSDATE - 1)
";

//where TRUNC(DIM.DTAULTALT) >= TRUNC(SYSDATE - 1)
//AND VINCULO.SITATL = 'A'
//AND VINCULO.TIPVIN = 'SERVIDOR'

//where DIM.CODPES = '3473118'

$stid = oci_parse($conn_replica, $consulta) or die ("erro");
 
//Executa os comandos SQL
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
    // foreach ($row as $userUSP) {
    //     print_r($userUSP);        
    //     echo "\n";
    // }
    unlink("zip.zip");
    unlink("curriculo.xml");

    //print_r($row);

    $zip = $row['IMGARQXML']->load();
    //print_r($zip);

    $zipFile = fopen("zip.zip", "w");
    fwrite($zipFile, $zip); 
    fclose($zipFile);

    $zipFile = "zip.zip";
    $fileInsideZip = ''.$row['IDFPESCPQ'].'.xml';
    $content = file_get_contents("zip://$zipFile#$fileInsideZip");

    $xmlFile = fopen("curriculo.xml", "w");
    fwrite($xmlFile, $content); 
    fclose($xmlFile);

    $output = shell_exec('curl -X POST -F "file=@'.__DIR__.'/curriculo.xml" -F "codpes='.$row["CODPES"].'" -F "unidadeUSP='.trim($row["SGLUND"]).'" -F "tag='.trim($row["NOMABVSET"]).'" -F "tipvin='.$row["TIPVIN"].'" http://143.107.154.38/dev_coletaprod/lattes_xml_to_elastic.php');
    echo "<pre>$output</pre>";

}

// Close the Oracle connection
oci_close($conn_replica);

?>