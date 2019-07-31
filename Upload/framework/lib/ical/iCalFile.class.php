<?php

class iCalFile {
	
	private $content = "";
	
	/**
	 * Erstellt ein neues "iCal File".
	 */
	public function __construct() {
		$this->content = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:SchuleIntern
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-PUBLISHED-TTL:PT1M\r\n";
	}
	
	
	/**
	 * 
	 * @param Leistungsnachweis $lnw
	 */
	public function addLeistungsnachweis($lnw) {
		$this->content .= "BEGIN:VEVENT\r\n";
		$this->content .= "UID:" . md5($lnw->getID()) . "@schuleintern.de\r\n";
	
		$this->content .= "SUMMARY:" . $lnw->getArtLangtext() . "\r\n";
		$this->content .= "DTSTART;VALUE=DATE:" . DateFunctions::getIcalDateFromSQLDate($lnw->getDatumStart()) . "\r\n";
		$this->content .= "DTEND;VALUE=DATE:" . DateFunctions::getIcalDateFromSQLDate(DateFunctions::addOneDayToMySqlDate($lnw->getDatumStart())) . "\r\n";
		
		
		$this->content .= "END:VEVENT\r\n";
	}
	
	public function sendFile() {
		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Disposition: attachment; filename="termine.ics"');
		
		$this->content .= "END:VCALENDAR";
		
		echo $this->content;
		exit(0);
	}
	
	
}

