<?php 

/**
 * @deprecated Use REST
 */
class GetSchoolData extends \AbstractApi {	
	public function __construct() {
	}
	
	public function execute() {
		header("Content-type: text/xml");
		
		// Sendet die komplleten Daten der Schule

		echo('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
		echo("<schuleintern>\r\n");
		
		// Lehrer
		echo("<alle_lehrer>\r\n");
		
		$lehrer = lehrer::getAll();
		for($i = 0; $i < sizeof($lehrer); $i++) {
			echo("<lehrer>\r\n");
			
			echo("<Kuerzel>" . ($lehrer[$i]->getKuerzel()) . "</Kuerzel>\r\n");
			echo("<Rufname>" . ($lehrer[$i]->getRufname()) . "</Rufname>\r\n");
			echo("<Name>" . ($lehrer[$i]->getName()) . "</Name>\r\n");
			echo("<asvid>" . ($lehrer[$i]->getAsvID()) . "</asvid>\r\n");
			echo("<Geschlecht>" . ($lehrer[$i]->getGeschlecht()) . "</Geschlecht>\r\n");
			
			echo("</lehrer>\r\n");
		}
		
		// /Lehrer
		
		
		echo("</alle_lehrer>\r\n");
		
		
		// Fächer
		
		$faecher = DB::getDB()->query("SELECT * FROM faecher");
		
		echo("<alle_faecher>\r\n");
		
		while($f = DB::getDB()->fetch_array($faecher)) {
			echo("<fach>\r\n");
			
			echo("<xml_id>" . ($f['fachID']) . "</xml_id>\r\n");
			echo("<name_kurz>" . ($f['fachKurzform']) . "</name_kurz>\r\n");
			echo("<name_lang>" . ($f['fachLangform']) . "</name_lang>\r\n");
			
			
			echo("</fach>\r\n");
		}
		
		echo("</alle_faecher>\r\n");
		
		//
		
		
		
		echo("</schuleintern>\r\n");
	}
}



?>