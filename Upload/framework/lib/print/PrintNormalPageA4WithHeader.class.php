<?php

class PrintNormalPageA4WithHeader extends TCPDF {

  private $name = "";

  private $showStand = false;

  private $showPrinted = false;

  private $showLochmarken = false;
  
  private $showHeaderOnEachPage = false;
  
  private $printSpitschkaHeader = false;

  public function __construct($name, $format='A4', $orientation='P') {
    parent::__construct($orientation,'mm',$format,true,'UTF-8',false,true);

    $this->showLochmarken = ($format == 'A4' && $orientation == 'P');


    // set document information
    $this->SetCreator('SchuleIntern');
    $this->SetAuthor('SchuleIntern');
    $this->SetTitle($name);

    $this->name = $name;

    $this->SetMargins(20, 35);
    
  }
  
  public function setPrintSpitschkaHeader() {
      $this->printSpitschkaHeader = true;
  }

    //Page header
    public function Header() {
        // Logo
        if($this->page == 1|| $this->showHeaderOnEachPage) {
          if(!$this->printSpitschkaHeader) {
              if(DB::getGlobalSettings()->printLetterWithFullBackgroundImage) $image_file = 'imagesSchool/Briefkopf_2.jpg';
              else $image_file = 'imagesSchool/Briefkopf.jpg';
          }
          else {
              $image_file = 'cssjs/imagesSpitschka/Briefkopf.jpg';
          }
          
          $this->Image($image_file, 15, 10, '180', '', 'JPG', '', 'M', false, 300, '', false, false, 0, false, false, false);
          
        }
    }

    public function setHTMLContent($html) {
      $this->AddPage();
      $this->SetFont("dejavusans","",9);
      $this->writeHTML($html, true, false, false, false, '');
    }
    
    public function showHeaderOnEachPage() {
    	$this->showHeaderOnEachPage = true;
    }

    public function send() {

      if($this->showLochmarken) {

          for($i = 1; $i <= $this->numpages; $i++) {
          $this->setPage($i);
          $this->Line(0, 148.5, 10, 148.5,[]);	// Lochmarke
        }

      }
      if(DB::isDebug()) $this->Output($this->name . ".pdf", "I");
      else $this->Output($this->name . ".pdf", "D");
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Seite '.$this->getAliasNumPage(). ' von ' .$this->getAliasNbPages() . (($this->showStand) ? (" | Stand: " . DB::getAsvStand()) : "") . (($this->showPrinted) ? (" | Gedruckt: " . DateFunctions::getTodayAsNaturalDate()) : "") . " | " . DB::getGlobalSettings()->siteNamePlain, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function showStand() {
      $this->showStand = true;
    }

    public function setPrintedDateInFooter() {
      $this->showPrinted = true;
    }
}
