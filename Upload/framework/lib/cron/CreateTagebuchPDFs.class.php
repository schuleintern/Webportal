<?php

class CreateTagebuchPDFs extends AbstractCron {
    
    private $created = [];

	public function __construct() {
	}

	public function execute() {
	    for($i = 0; $i < 4; $i++) {
	        if(!$this->createOnePDF()) break;
	    }
	    echo("OK");
	}
	
	private function createOnePDF() {

	    echo("OK");
	
	    $missingPDF = DB::getDB()->query_first("SELECT * FROM klassentagebuch_pdf WHERE pdfUploadID=0 LIMIT 1");
	    
	    
	    if($missingPDF['pdfKlasse'] != "") {
	        $firstDay = mktime(10,10,10,$missingPDF['pdfMonat'],1,$missingPDF['pdfJahr']);
	    
	        $nextMonth = $missingPDF['pdfMonat'] + 1;
	        if($nextMonth == 13) $nextMonth = 1;
	    
	        $lastDay = mktime(10,10,10,$nextMonth,1,$missingPDF['pdfJahr']);
	        $lastDay -= (24 * 60 * 60);        // 1 Tag weniger
	        
	        $firstDay = DateFunctions::getMySQLDateFromUnixTimeStamp($firstDay);
	        $lastDay = DateFunctions::getMySQLDateFromUnixTimeStamp($lastDay);
	        
	        $pdf = new TagebuchPDFCreator($firstDay, $lastDay, $missingPDF['pdfKlasse']);
	        $upload = $pdf->createPDF();
	        
	        $this->created[] = $upload->getFileName();
	        
	        if($upload != null) {
	            DB::getDB()->query("UPDATE klassentagebuch_pdf SET pdfUploadID='" . $upload->getID() . "'
                WHERE
                    pdfKlasse = '" . DB::getDB()->escapeString($missingPDF['pdfKlasse']) . "' AND
                    pdfJahr = '" . DB::getDB()->escapeString($missingPDF['pdfJahr']) . "' AND
                    pdfMonat = '" . DB::getDB()->escapeString($missingPDF['pdfMonat']) . "'
                ");
	        }
	        
	        return true;
	    }
	    else return false;
	    
	}
	
	public function getName() {
		return "Tagebuch PDFs erstellen";
	}
	
	public function getDescription() {
		return "";
	}
	
	public function getCronResult() {
	    return ['success' => true, 'resultText' => implode(", ", $this->created)];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 300;		// Einmal alle zwei Stunden ausführen
	}
}



?>