<?php

class PrintNormalPageA4WithoutHeader extends TCPDF {

  private $name = "";

  private $showStand = false;

  private $showPrinted = false;

  private $showLochmarken = false;
  
  private $showHeaderOnEachPage = false;

  public function __construct($name, $format='A4', $orientation='P') {
    parent::__construct($orientation,'mm',$format,true,'UTF-8',false,true);


    // set document information
    $this->SetCreator('SchuleIntern');
    $this->SetAuthor('SchuleIntern');
    $this->SetTitle($name);

    $this->name = $name;

    $this->SetMargins(15, 10, 15, true);


  }

    //Page header
    public function Header() {

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
    }

    public function showStand() {
      $this->showStand = true;
    }

    public function setPrintedDateInFooter() {
      $this->showPrinted = true;
    }
}
