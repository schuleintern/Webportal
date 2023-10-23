<?php

class PrintLetterWithWindowA4 extends TCPDF {
	
	private $name = "";
	
	private $showStand = false;
	private $betreff = "";
			
	private $datum = "";
	
	public function __construct($name) {
		parent::__construct('P','mm','A4',true,'UTF-8',false,true);
		
		// set document information
		$this->SetCreator('SchuleIntern');
		$this->SetAuthor('SchuleIntern');
		$this->SetTitle($name);
		
		$this->name = $name;

		$this->SetMargins(20, 20);
		
		
		$text = DB::getSettings()->getValue("print-fusszeile-kuvert");
		$lines = sizeof(explode("\n",$text));
		
		$this->SetAutoPageBreak(true, 15 + 5 * $lines);
		
	}
	
    //Page header
    public function Header() {
        // Logo
        
    	if($this->page == 1) {

    	}
    }
    
    public function setBetreff($betreff) {
    	$this->betreff = $betreff;
    }
   
    
    public function addLetter($empfaenger, $html) {
    	$this->AddPage();
    	
    	//$image_file = DB::getGlobalSettings()->urlToIndexPHP . '?page=printSettings&action=GetPrintHeader';
    	$image_file = PAGE::logoPrint();

    	$this->Image($image_file, 15, 10, '180', '', 'JPG', '', 'M', false, 300, '', false, false, 0, false, false, false);

    	
    	
    	
    	    	 
    	$this->SetFont('dejavusans','','8');
    	 
    	$this->setXY(20,45);
    	 
    	$this->Cell(85, 5, DB::getSettings()->getValue("print-absender-kuvert"), 0, 1, 'C', 0, '', 4);
    	$this->Line(20, 50, 105, 50);
    	 
    	$this->SetFont('dejavusans','','10');
    	 
    	$this->setXY(22,55);
    	 
    	$this->MultiCell(85, 40, $empfaenger . "\n");
    	 
    	if($this->betreff != "") {
    		$this->setXY(20,95);
    		$this->SetFont('dejavusans','','10');
    		$this->writeHTML("<b>Betreff:</b> " . $this->betreff);
    	}
    	 
    	 
    	// Ort und Datum
    	 
    	$this->SetXY(20, 90);
    	$this->writeHTML("<div align=\"right\">" . DB::getSettings()->getValue("schulinfo-ort") . ", den " . $this->datum . "</div>");
    	 
    	// Kontaktinfo
    	 
    	 
    	 
    	 
    	$this->SetFont('dejavusans','','10');
    	$kontakt = DB::getSettings()->getValue("print-rechte-seite");
    	// $kontakt = str_replace("\n", "<br>", $kontakt);
    	 
    	$kontakt = str_replace("<p>", "", $kontakt);
    	$kontakt = str_replace("</p>", "<br>", $kontakt);
    	 
    	$this->SetXY(20, 45);
    	$this->writeHTML("<div align=\"right\">" . $kontakt . "</div>");
    	
    	
    	$this->setXY(20,105);
    	
    	$this->SetFont('dejavusans','','11');
       	$this->writeHTML("<div style=\"text-align: justify\">$html</div>", true, false, true, false, '');
    }
    
    public function setDatum($date) {
    	$this->datum = $date;
    }
    
    public function send($noOutput = false) {
    	
    	for($i = 1; $i <= $this->numpages; $i++) {
    	
    		$this->setPage($i);
    		$this->Line(0, 148.5, 10, 148.5,[]);	// Lochmarke
    	
    		$this->Line(0, 105, 8, 105,[]);	// Falzmarke 1
    		$this->Line(0, 210, 8, 210,[]);	// Falzmarke 2
    	    	// Adresse zeichnen
    	    	
    	}
    	
    	// $this->Rect(20, 45, 85, 45);
    	
    	
    	
    	if(!$noOutput) $this->Output($this->name . ".pdf", "D");
    }

    // Page footer
    public function Footer() {
    	$text = DB::getSettings()->getValue("print-fusszeile-kuvert");
    	$lines = sizeof(explode("\n",$text));
        // Position at 15 mm from bottom
        
    	$text = str_replace("<p>", "", $text);
    	$text = str_replace("</p>", "<br>", $text);
        
        $this->SetY(-15 - ($lines-1)*5,20);
        // Set font
        $this->SetFont('dejavusans', '', 10);
        // Page number
        $this->writeHTML("<div style=\"text-align: center\">" . $text . "</div>");
    }
    
    public function showStand() {
    	$this->showStand = true;
    }
}