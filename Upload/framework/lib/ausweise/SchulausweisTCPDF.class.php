<?php

class SchulausweisTCPDF extends TCPDF {
    private $ausweis;
    
    /**
     * 
     * @param ISGYAusweis $ausweis
     */
    public function __construct($ausweis, $isFront, $isBack) {
        $this->ausweis = $ausweis;
        
        $pageLayout = array(53.98,85.60); //  or array($height, $width)
        
        parent::__construct('L', 'mm', $pageLayout, true, 'UTF-8', false);
        
        // Barcode
        
        $this->SetMargins(0, 0, 0);
        
        $this->addPage();
        
        $this->SetAutoPageBreak(false, 0);
        // set bacground image
        
        if($isFront) $this->front();
        if($isBack) $this->back();
        
    }
    
    private function back() {
        $img_file = 'imagesSchool/Ausweis/Rueckseite.jpg';
                
        $this->Image($img_file, 0, 0, 85.60, 53.98, '', '', '', false, 300, '', false, false, 0);
        
        $this->SetFont("courier", '', 9, '', false);
        
        $this->Text(57.5, 41.5, DateFunctions::getNaturalDateFromMySQLDate($this->ausweis->getAblaufdatum()));
    }
    
    private function front() {       
        

        
        if($this->ausweis->isSchuelerausweis()) {
            $img_file = 'imagesSchool/Ausweis/SAHintergrund.jpg';
        }
        
        if($this->ausweis->isDienstausweis()) {
            $img_file = 'imagesSchool/Ausweis/MAHintergrund.jpg';
        }
        
        if($this->ausweis->isLehrerausweis()) {
            $img_file = 'imagesSchool/Ausweis/LHintergrund.jpg';
        }
        
        
        
        $this->Image($img_file, 0, 0, 85.60, 53.98, '', '', '', false, 300, '', false, false, 0);
        
        if($this->ausweis->getBarcode() != "") {
            $style = array(
                'border' => false,
                'hpadding' => 'auto',
                'vpadding' => 'auto',
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, //array(255,255,255),
                'text' => FALSE,
                'font' => 'helvetica',
                'fontsize' => 4,
                'stretchtext' => 0
            );
                        
            $this->SetFont("courier", 'b', 7, '', false);
            
            $this->Text(28, 40.5, $this->ausweis->getBarcode());
            
            
            $this->write1DBarcode($this->ausweis->getBarcode(), 'C128', 2, 42, 45, 8,'',$style);
        }
        
        // echo(file_exists(('imagesSchool/Ausweis/font.ttf')) ? "Ja" : "beub");die();
        
        // convert TTF font to TCPDF format and store it on the fonts folder
        $fontname = TCPDF_FONTS::addTTFfont('imagesSchool/Ausweis/isgyfont.ttf', 'TrueType', 'UTF-8', 32, '');
        // die("Name: " . $fontname);
        // use the font
        $this->SetFont("helvetica", '', 9, '', false);
        
        $name = explode(", ",$this->ausweis->getName());
        
        $this->Text(17.8, 18.5, $name[1]);
        $this->SetFont("helvetica", 'b', 9, '', false);
        
        
        $this->Text(17.8, 21.5, $name[0]);
        
        $this->SetFont("helvetica", '', 9, '', false);
        
        
        $this->Text(17.8, 27, ($this->ausweis->getPLZ() . " " . $this->ausweis->getOrt()));
        
        $this->Text(17.8, 33.5, DateFunctions::getNaturalDateFromMySQLDate($this->ausweis->getGeburtsdatum()));
        
        if($this->ausweis->getBild() != null) {
        
            $img_file = $this->ausweis->getBild()->getFilePath();
            $this->Image($img_file, 64, 15, 16.5, 25, '', $this->ausweis->getBild()->getExtension(), '', false, 300, '', false, false, 0);
        }
        
    }
    
    public function Header() {
    }
    
    public function old() {
        
        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set bacground image
        $img_file = 'imagesSchool/Ausweis/SAHintergrund.jpg';
        $this->Image($img_file, 0, 0, 85.60, 53.98, '', '', '', false, 300, '', false, false, 0);

        // set the starting point for the page content
        $this->setPageMark();
    }
    
    public function Footer() {
        
    }
    
    
    
}