<?php

class schulbuecher extends AbstractPage {

  public function __construct() {
    parent::__construct(['Schulbücher']);

    $this->checkLogin();

  }

  public function execute() {
    switch($_GET['mode']) {
      default:
        $this->showMyBooks();
      break;

      case 'rueckgabeCheckScan':
          if(self::userCanRueckgabe(DB::getSession()->getUser())) $this->rueckgabeCheckScan();
      break;

      case 'ausleihe':
        if(self::userCanAusleihe(DB::getSession()->getUser())) $this->ausleihe();
      break;

      case 'rueckgabe':
          if(self::userCanRueckgabe(DB::getSession()->getUser())) $this->rueckgabe();
      break;

      case 'bestand':
        if(self::userCanBestand(DB::getSession()->getUser())) $this->bestand();
      break;

      case 'deleteBook':
        if(self::userCanBestand(DB::getSession()->getUser())) $this->deleteBook();
      break;

      case 'bestandOfBook':
        if(self::userCanBestand(DB::getSession()->getUser())) $this->bestandOfBook();
      break;

      case 'deleteExemplar':
        if(self::userCanBestand(DB::getSession()->getUser())) $this->deleteExemplar();
      break;

      case 'generateAusleihBarcodes':
        if(self::userCanAusleihe(DB::getSession()->getUser())) $this->generateAusleihBarcodes();
      break;

      case 'management':
        if(self::userCanAusleihe(DB::getSession()->getUser()) || self::userCanRueckgabe(DB::getSession()->getUser())) $this->management();
      break;

      case 'listeAusleihen':
        if(self::userCanAusleihe(DB::getSession()->getUser()) || self::userCanRueckgabe(DB::getSession()->getUser())) $this->listeAusleihen();
      break;

      case 'viewExemplar':
        if(self::userCanBestand(DB::getSession()->getUser())) $this->viewExemplar();
      break;
      
      case 'getSchuelerFoto':
          if(self::userCanAusleihe(DB::getSession()->getUser()) || self::userCanRueckgabe(DB::getSession()->getUser())) $this->getSchuelerFoto();
      break;
      
      case 'showListeAusleihen':
          if(self::userCanAusleihe(DB::getSession()->getUser()) || self::userCanRueckgabe(DB::getSession()->getUser())) $this->showAllAusleihen();
      break;
    }
  }
  
  private function getSchuelerFoto() {
      if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren') && DB::getSettings()->getBoolean('schulbuecher-ausleihe-fotos')) {
          
          $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);
          
          if($schueler == null) {
              header("Location: cssjs/images/userimages/default.png");
              exit(0);
          }
          
          $upload = $schueler->getFoto();
          
          if($upload != null) {
              $upload->sendImageWidthMaxWidth("300");
              exit(0);
          }
          else {
              header("Location: cssjs/images/userimages/default.png");
              exit(0);
          }
      }
      else {
          header("Location: cssjs/images/userimages/default.png");
          exit(0);
      }
  }

  private function rueckgabeCheckScan() {
      $result = [
          'isSchueler' => false,
          'schuelerAsvID' => '',
          'schuelerName' => '',
          'isBook' => false,
          'bookZustand' => "n/a",
          'bookName' => '',
          'isAusgeliehen' => false,
          'isLentTo' => ''
     ];

      header("Content-type: text/json");


      $scan = $_REQUEST['barcode'];

      $schueler = schueler::getByAsvID($scan);

      if($schueler != null) {
          $result['isSchueler'] = true;
          $result['schuelerAsvID'] = $schueler->getAsvID();
          $result['schuelerName'] = $schueler->getCompleteSchuelerName();
      }
      else {
          $exemplar = Exemplar::getByBarcode($scan);
          
          
          if($exemplar != null) {
              $result['isBook'] = true;
              $result['bookZustand'] = $exemplar->getZustand();
              $result['bookName'] = $exemplar->getSchulbuch()->getName();       
              
              if($exemplar->isAusgeliehen()) {
                  $result['isAusgeliehen'] = true;
                  $result['isLentTo'] = $exemplar->getActiveAusleihe()->getAusleiher();
              }
          }
      }
      

      echo json_encode($result);

      exit(0);
  }

  private function rueckgabe() {
      $schueler = null;


      $scaned = false;
      
      $zustandChanged = false;


      if($_GET['action'] == 'selectPupil') {
          $schueler = schueler::getByAsvID($_POST['schuelerAsvID']);
          if($schueler == null) {
              // Exemplar suchen
              $exemplar = Exemplar::getByBarcode($_POST['schuelerAsvID']);
              if($exemplar != null) {
                  $currentAusleihe = $exemplar->getActiveAusleihe();
                  if($currentAusleihe != null) {
                      $currentAusleihe->endAusleihe();
                      $scaned = true;
                      $message = $exemplar->getSchulbuch()->getName() . ": Rückgabe erfolgreich.";
                      $success = true;
                      $schueler = $currentAusleihe->getSchueler();
                      $zustandChanged = $exemplar->setZustandScan($_REQUEST['zustand']);
                      
                  }
                  else {
                      $scaned = true;
                      $success = false;
                      $message = "Buch ist nicht ausgeliehen: " . $exemplar->getSchulbuch()->getName();
                      $schueler = null;
                  }
              }
          }
      }


      if($_REQUEST['schuelerAsvIDPreSet'] != '') {
          $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvIDPreSet']);
      }

      if($schueler != null) {


          if($_GET['action'] == 'scanBarcode') {

              $scaned = true;

              // Ist es ein Buch?

              $exemplar = Exemplar::getByBarcode($_POST['barcode']);

              $success = false;

              if($exemplar != null) {
                  // Ausleihe
                  $currentAusleihe = $exemplar->getActiveAusleihe();

                  if($currentAusleihe != null) {



                      $success = true;
                      $currentAusleihe->endAusleihe();
                      $message = $exemplar->getSchulbuch()->getName() . ": Rückgabe erfolgreich.";
                      $schueler = $currentAusleihe->getSchueler();

                      $exemplar->setZustandScan($_REQUEST['zustand']);
                  }
                  else {
                      $success = false;
                      $message = "Buch ist nicht ausgeliehen: " . $exemplar->getSchulbuch()->getName();
                  }


              }

              if(!$success) {
                  // Schüler?

                  $schuelerNew = schueler::getByAsvID($_POST['barcode']);
                  if($schuelerNew!= null) {
                      $schueler = $schuelerNew;
                      $success = true;
                      $message = "Schüler gewechselt.";
                  }
              }


          }


          $ausleihen = BuchAusleihe::getBySchueler([$schueler]);



          $buecherHTML = "";


          $summe = 0;



          for($i = 0; $i < sizeof($ausleihen); $i++) {
              if($ausleihen[$i]->isAusleiheActive()) {
                  if($_REQUEST['deleteAusleihe'] == $ausleihen[$i]->getID()) {
                      $ausleihen[$i]->delete();
                      $scaned = true;
                      $success = true;
                      $exemplar = $ausleihen[$i]->getExemplar();
                      $message = $exemplar->getSchulbuch()->getName() . ": Ausleihe gelöscht.";
                  }
                  else if($_REQUEST['rueckgabeAusleihe'] == $ausleihen[$i]->getID()) {
                      $ausleihen[$i]->endAusleihe();
                      $scaned = true;
                      $success = true;
                      $exemplar = $ausleihen[$i]->getExemplar();
                      $ausleihen[$i]->setKommentar($_REQUEST['kommentar']);
                      $exemplar->setZustand($_REQUEST['zustand']);

                      $message = $exemplar->getSchulbuch()->getName() . ": Rückgabe erfolgreich.";
                  }
                  else {
                      $buecherHTML .= "<tr><td>" . $ausleihen[$i]->getExemplar()->getSchulbuch()->getName() . "</td>";
                      $buecherHTML .= "<td>" . $ausleihen[$i]->getExemplar()->getBarcode() . "</td>";
                      $buecherHTML .= "<td>

                                <button type=\"button\" class=\"btn btn-xs btn-info\" onclick=\"rueckgabe('" . $ausleihen[$i]->getID() . "','" . $ausleihen[$i]->getExemplar()->getZustandNumber() . "');\"><i class=\"fa fa-arrow-down\"></i> Rückgabe</button>


                                <button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"confirmAction('Ausleihe wirklich löschen?','index.php?page=schulbuecher&mode=rueckgabe&schuelerAsvIDPreSet=" . $schueler->getAsvID() . "&deleteAusleihe=" . $ausleihen[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i> Löschen</button>


                        </td></tr>";

                      $summe++;

                  }
              }
          }

          // $summe = sizeof($ausleihen);

      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/rueckgabe/index") . "\");");
  }

  
  private function showAllAusleihen() {
      
      $html = '';
      
      $klassen = klasse::getAllKlassen();
      
      $linksKlassen = "";
      
      for($i = 0; $i < sizeof($klassen); $i++) {
          
          $linksKlassen .= "<li><a href=\"#klasse" . $klassen[$i]->getKlassenName() . "\">Klasse " . $klassen[$i]->getKlassenName() . "</a></li>";
          
          // $htmlKlasse = '<h4>Klasse ' . $klassen[$i]->getKlassenName() . "</h4>\r\n\r\n";
          
          // $tableHTML$htmlKlasse .= "<table width=\"100%\" border=\"1\" cellpadding=\"2\"><tr><td width=\"5%\" align=\"center\">#</td><td width=\"70%\">Name, Vorname</td><td width=\"25%\" align=\"center\">Anzahl / Exemplare</td></tr>";
          
          $tableHTML = "";
          
          $schueler = $klassen[$i]->getSchueler(false);
          
          for($o = 0; $o < sizeof($schueler); $o++) {
              $tableHTML .= "<tr><td align=\"center\" rowspan=\"2\">" . ($o+1) . "</td>";
              $tableHTML .= "<td>" . $schueler[$o]->getCompleteSchuelerName() . "</td>";
              
              $ausleihen = BuchAusleihe::getActiveAusleiheBySchueler([$schueler[$o]]);
              
              $tableHTML .= "<td align=\"center\">" . sizeof($ausleihen) . "</td></tr><tr><td colspan=\"2\">";
              
              for($a = 0; $a < sizeof($ausleihen); $a++) {
                  $exemplar = $ausleihen[$a]->getExemplar();
                  if($a > 0) $tableHTML .= " | ";
                  $tableHTML .= $exemplar->getSchulbuch()->getName() . " <i>" . $exemplar->getBarcode() . "</i>";
              }
              
              
              $tableHTML .= "</td>";
              $tableHTML .= "</tr>\r\n";
          }
          
                   
          
          
        
          
          
          eval("\$html .= \"" . DB::getTPL()->get("schulbuecher/management/ausleihen_klasse") . "\";");
                    
          
      }
      
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/management/ausleihen") . "\");");
      exit();
      
  }
  
  private function listeAusleihen() {

    $html = '';

    $klassen = klasse::getAllKlassen();
    $druck = new PrintNormalPageA4WithHeader('Alle Ausleihen');
    $druck->showHeaderOnEachPage();

    for($i = 0; $i < sizeof($klassen); $i++) {
      $htmlKlasse = '<h4>Klasse ' . $klassen[$i]->getKlassenName() . "</h4>\r\n\r\n";

      $htmlKlasse .= "<table width=\"100%\" border=\"1\" cellpadding=\"2\"><tr><td width=\"5%\" align=\"center\">#</td><td width=\"70%\">Name, Vorname</td><td width=\"25%\" align=\"center\">Anzahl / Exemplare</td></tr>";


      $schueler = $klassen[$i]->getSchueler(false);

      for($o = 0; $o < sizeof($schueler); $o++) {
        $htmlKlasse .= "<tr><td align=\"center\" rowspan=\"2\">" . ($o+1) . "</td>";
        $htmlKlasse .= "<td>" . $schueler[$o]->getCompleteSchuelerName() . "</td>";

        $ausleihen = BuchAusleihe::getActiveAusleiheBySchueler([$schueler[$o]]);

        $htmlKlasse .= "<td align=\"center\">" . sizeof($ausleihen) . "</td></tr><tr><td colspan=\"2\">";

        for($a = 0; $a < sizeof($ausleihen); $a++) {
          $exemplar = $ausleihen[$a]->getExemplar();
          if($a > 0) $htmlKlasse .= " | ";
          $htmlKlasse .= $exemplar->getSchulbuch()->getName() . " <i>" . $exemplar->getBarcode() . "</i>";
        }


        $htmlKlasse .= "</td>";
        $htmlKlasse .= "</tr>\r\n";
      }




      $htmlKlasse .= '</table>';

      $druck->setHTMLContent($htmlKlasse);




    }


    $druck->send();

  }

  private function viewExemplar() {
    $barcode = DB::getDB()->escapeString($_REQUEST['exemplarBarcode']);

    $exemplar = Exemplar::getByBarcode($barcode);

    if($exemplar != null) {


      $book = $exemplar->getSchulbuch();
      $ausleihen = $exemplar->getAusleihen();

      $ausleihe = $exemplar->getActiveAusleihe();
      if($ausleihe != null) {
          $ausleiheDatum = DateFunctions::getNaturalDateFromMySQLDate($ausleihe->getAusleiheStartDatum());
      }

      // Bisherige Ausleihen

      $ausleihenHTML = "";

      // Debugger::debugObject($ausleihen,1);

      for($i = 0; $i < sizeof($ausleihen); $i++) {
          $ausleihenHTML .= "<tr><td>" . $ausleihen[$i]->getAusleiher() . "</td>";

          $ausleihenHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($ausleihen[$i]->getAusleiheStartDatum()) . "</td>";

          if($ausleihen[$i]->getAusleiheEndDatum() != "") {
              $ausleihenHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($ausleihen[$i]->getAusleiheEndDatum()) . "</td>";
          }
          else {
              $ausleihenHTML .= "<td><i>Noch ausgeliehen</i></td>";
          }

          $ausleihenHTML .= "</tr>";
      }


      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/exemplar/index") . "\");");

    }
    else {
      new errorPage("Der Barcode ist unbekannt.");
    }
  }


  private function management() {




    // Schüler suchen
    $schuelerSelectHTML = '';


    $schueler = schueler::getAll();

    for($i = 0; $i < sizeof($schueler); $i++) {
      $schuelerSelectHTML .= "<option value=\"" . $schueler[$i]->getAsvID() . "\">" . $schueler[$i]->getCompleteSchuelerName() . "</option>";
    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/management/index") . "\");");
    exit(0);
  }

  private function deleteBook() {
    $buch = Schulbuch::getByID(DB::getDB()->escapeString($_GET['schulbuchID']));

    if($buch != null) {
      $buch->delete();
    }

    header("Location: index.php?page=schulbuecher&mode=bestand");
    exit(0);

  }


  private function deleteExemplar() {
    $exemplar = Exemplar::getByID(intval($_GET['exemplarID']));
    if($exemplar != null) {
      $exemplar->delete();
      header("Location: index.php?page=schulbuecher&mode=bestandOfBook&schulbuchID=" . $exemplar->getSchulbuch()->getID());
    }
    else {
      header("Location: index.php?page=schulbuecher&mode=bestand");
      exit(0);
    }
  }

  private function ausleihe() {

    $schueler = null;

    if($_GET['action'] == 'selectPupil') {
      $schueler = schueler::getByAsvID($_POST['schuelerAsvID']);
    }

    if($_REQUEST['schuelerAsvID'] != '') {
      $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);
    }

    if($schueler != null) {

      $scaned = false;

      if($_GET['action'] == 'scanBarcode') {

        $scaned = true;

        // Ist es ein Buch?

        $exemplar = Exemplar::getByBarcode($_POST['barcode']);

        $success = false;

        if($exemplar != null) {
          // Ausleihe
          if(!$exemplar->isAusgeliehen()) {
            $exemplar->lendToSchueler($schueler);
            $success = true;
            $message = "Exemplar \"" . $exemplar->getSchulbuch()->getName() . "\" erfolgreich verbucht.";
          }
          else {
            $message = "Das Buch ist bereits verliehen an " . $exemplar->getActiveAusleihe()->getAusleiher();
          }
        }

        if(!$success) {
          // Schüler?

          $schuelerNew = schueler::getByAsvID($_POST['barcode']);
          if($schuelerNew!= null) {
            $schueler = $schuelerNew;
            $success = true;
            $message = "Schüler gewechselt.";
          }
        }


      }

      $ausleihen = BuchAusleihe::getBySchueler([$schueler]);



      $buecherHTML = "";

      $summe = 0;

      for($i = 0; $i < sizeof($ausleihen); $i++) {
        if($_REQUEST['deleteAusleihe'] == $ausleihen[$i]->getID()) {
          $ausleihen[$i]->delete();
          $scaned = true;
          $success = true;
          $message = "Ausleihe gelöscht.";
        }

        else if($_REQUEST['rueckgabeAusleihe'] == $ausleihen[$i]->getID()) {
            $ausleihen[$i]->endAusleihe();
            $scaned = true;
            $success = true;
            $exemplar = $ausleihen[$i]->getExemplar();
            $message = $exemplar->getSchulbuch()->getName() . ": Rückgabe erfolgreich.";
            $ausleihen[$i]->setKommentar($_REQUEST['kommentar']);
            $exemplar->setZustand($_REQUEST['zustand']);
        }

        else if($ausleihen[$i]->isAusleiheActive()) {
          $buecherHTML .= "<tr><td>" . $ausleihen[$i]->getExemplar()->getSchulbuch()->getName() . "</td>";
          $buecherHTML .= "<td>" . $ausleihen[$i]->getExemplar()->getZustand() . "</td>";
          $buecherHTML .= "<td>" . $ausleihen[$i]->getExemplar()->getBarcode() . "</td>";
          $buecherHTML .= "<td>

                                <button type=\"button\" class=\"btn btn-xs btn-info\" onclick=\"rueckgabe('" . $ausleihen[$i]->getID() . "','" . $ausleihen[$i]->getExemplar()->getZustandNumber() . "');\"><i class=\"fa fa-arrow-down\"></i> Rückgabe</button>

                                <button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"confirmAction('Ausleihe wirklich löschen?','index.php?page=schulbuecher&mode=ausleihe&schuelerAsvID=" . $schueler->getAsvID() . "&deleteAusleihe=" . $ausleihen[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i> Löschen</button>


                        </td></tr>";

          $summe++;
        }
      }

    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/ausleihe/index") . "\");");
  }



  private function generateAusleihBarcodes() {
    $printPage = new PrintNormalPageA4WithHeader('barcodes');
    $printPage->showHeaderOnEachPage();

    $klassen = klasse::getAllKlassen();

    for($i = 0; $i < sizeof($klassen); $i++) {
      $schueler = $klassen[$i]->getSchueler(true);

      $html = "<h3>" . $klassen[$i]->getKlassenName() . "</h3>";

      $html .= "<table border=\"1\" style=\"width:100%\" cellpadding=\"2\"><tr><th style=\"width:50%\"></th><th style=\"width:50%\"></th></tr>";

      $count = 0;
      for($s = 0; $s < sizeof($schueler); $s++) {
        $params = $printPage->serializeTCPDFtagParameters(array($schueler[$s]->getAsvID(), 'C128', '', '', 80, 8, 0.2, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>0, 'stretchtext'=>4), 'N'));

        if($count == 0) $html .= "<tr>";
        $html .= "<td valign=\"center\">" . $schueler[$s]->getCompleteSchuelerName() . "<br /><tcpdf method=\"write1DBarcode\" params=\"" .$params . "\" /></td>";

        $count++;

        if($count == 2) {
          $count = 0;
          $html .= "</tr>";
        }
      }
      
      if($count == 1) $html .= "<td>&nbsp;</td></tr>";

      $html .= "</table>\r\n";
      $printPage->setHTMLContent($html);
    }



    $printPage->send();
  }

  private function bestandOfBook() {
    $book = Schulbuch::getByID(intval($_GET['schulbuchID']));

    $exemplare = $book->getExemplare();



    $exemplareHTML = '';
    $exemplareHTMLBankbuch = '';
    for($i = 0; $i < sizeof($exemplare); $i++) {
      $exemplarHTML = '';

      $exemplarHTML .= "<tr>";
      $exemplarHTML .= "<td>" . $exemplare[$i]->getBarcode() . "</td>";
      $exemplarHTML .= "<td>Lagerort: " .  $exemplare[$i]->getLagerort() . "<br />Zustand: " . $exemplare[$i]->getZustand() . "<br />Anschaffungsjahr: " . $exemplare[$i]->getAnschaffungsjahr() ."</td>";
      $exemplarHTML .= "<td>" . ($exemplare[$i]->isAusgeliehen() ? "<span class=\"label label-danger\">Ausgeliehen</span><br />" . $exemplare[$i]->getActiveAusleihe()->getAusleiher() . " seit " . DateFunctions::getNaturalDateFromMySQLDate($exemplare[$i]->getActiveAusleihe()->getAusleiheStartDatum()) : "<span class=\"label label-success\">Verfügbar</span>") . "</td>";
      $exemplarHTML .= "<td><form><button type=\"button\" class=\"btn\" onclick=\"javascript:confirmAction('Soll das Buch wirklich gelöscht werden?','index.php?page=schulbuecher&mode=deleteExemplar&exemplarID=" . $exemplare[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i></button></form></td>";
      $exemplarHTML .= "</tr>";

      if($exemplare[$i]->isBankbuch()) {
        $exemplareHTMLBankbuch .= $exemplarHTML;
      }
      else $exemplareHTML .= $exemplarHTML;
    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/bestand/buch") . "\");");
  }

  private function bestand() {

    $displayError = "";

    if($_GET['action'] == 'addBook') {
      Schulbuch::createNew($_POST['titel'],$_POST['verlag'],$_POST['isbn'],$_POST['preis'],$_POST['fach'],$_POST['jahrgangsstufe']);
      header("Location: index.php?page=schulbuecher&mode=bestand");
      exit(0);
    }
    
    if($_REQUEST['action'] == 'editBook') {
        $book = Schulbuch::getByID($_REQUEST['bookID']);
        
        if($book != null) {
            $book->setFach($_REQUEST['fach']);
            $book->setISBN($_REQUEST['isbn']);
            $book->setKlasse($_REQUEST['jahrgangsstufe']);
            $book->setName($_REQUEST['titel']);
            $book->setPreis($_REQUEST['preis']);
            $book->setVerlag($_REQUEST['verlag']);
            
            $displayError = "<div class=\"callout callout-success\">Das Buch wurde erfolgreich bearbeitet.</div>";
        }
        else {
            $displayError = "<div class=\"callout callout-danger\">Das Buch wurde nicht gefunden.</div>";
        }
    }

    if($_GET['action'] == 'addExemplare') {
      $book = Schulbuch::getByID(intval($_POST['buchID']));

      if($book != null) {
        $barcodes = explode("\n",str_replace("\r","",$_POST['barcodes']));
        $errors = $book->addExemplare($barcodes, $_POST['zustand'], $_POST['anschaffungsjahr'], $_POST['isBankbuch'], $_POST['lagerort']);
        if(sizeof($errors) > 0) {
          $displayError = "<div class=\"callout callout-danger\">Es sind Fehler aufgetreten:<br />" . implode("<br />",$errors) . "</div>";
        }
        else {
          header("Location: index.php?page=schulbuecher&mode=bestand");
          exit(0);
        }
      }
    }


    $books = Schulbuch::getAll();

    $html = "";

    $buchSelect = '';

    for($i = 0; $i < sizeof($books); $i++) {
      $buchSelect .= "<option value=\"" . $books[$i]->getID() . "\">" . $books[$i]->getName() . "</option>";


      $html .= "<tr>";

      /**
       * 				<th>Verlag</th>
        <th>ISBN</th>
        <th>Preis</th>
        <th>Fach</th>
        <th>Jahrgangsstufe</th>
       */

      $html .= "<td><a href=\"index.php?page=schulbuecher&mode=bestandOfBook&schulbuchID=" . $books[$i]->getID() . "\"><b>" . $books[$i]->getName() . "</b></a></td>";
      $html .= "<td>" . $books[$i]->getVerlag() . "</td>";
      $html .= "<td>" . $books[$i]->getISBN() . "</td>";
      $html .= "<td>" . $books[$i]->getPreis() . "</td>";

      $html .= "<td>" . $books[$i]->getFach() . "</td>";

      $html .= "<td>" . $books[$i]->getKlasse() . "</td>";

      $html .= "<td>" . $books[$i]->getBestand() . "</td><td>" . $books[$i]->getLentBestand(false) . "</td>";
      $html .= "<td>" . $books[$i]->getBestand(true) . "</td><td>" . $books[$i]->getLentBestand(true) . "</td>";

      $html .= "<td>";

      $html .= "<button type=\"button\" class=\"btn btn-success btn-xs\" data-toggle=\"modal\" data-target=\"#addBestand\" onclick=\"selectBook(" . $books[$i]->getID() . ")\"><i class=\"fa fa-plus\"></i> Bestand erfassen</button><br />";
      $html .= "<button type=\"button\" class=\"btn btn-info btn-xs\" onclick=\"javascript:window.location.href='index.php?page=schulbuecher&mode=bestandOfBook&schulbuchID=" . $books[$i]->getID() . "'\"><i class=\"fa fa-briefcase\"></i> Bestand verwalten</button><br />";
      $html .= "<button type=\"button\" class=\"btn btn-danger btn-xs\" onclick=\"javascript:confirmAction('Soll das Buch inkl. allen Exemplaren und allen aktiven Ausleihen gelöscht werden?','index.php?page=schulbuecher&mode=deleteBook&schulbuchID=" . $books[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i> Buch löschen</button><br />";

      $html .= "<button type=\"button\" class=\"btn btn-info btn-xs\" onclick=\"javascript: editBook(" . $books[$i]->getID() . ",'" . addslashes($books[$i]->getName()) . "','" . addslashes($books[$i]->getVerlag()) . "','" . addslashes($books[$i]->getISBN()) . "','" . addslashes($books[$i]->getPreisInEuro()) . "','" . addslashes($books[$i]->getFach()) . "','" . addslashes($books[$i]->getKlasse()) . "');\"><i class=\"fa fa-pencil\"></i> Buch bearbeiten</button>";
      
      $html .= "</td>";

      $html .= "</tr>";
    }

    $fachSelect = "";

    $faecher = fach::getAll();
    for($i = 0; $i < sizeof($faecher); $i++) {
      $fachSelect .= "<option value=\"" . $faecher[$i]->getLangform() . "\">" . $faecher[$i]->getLangform() . "</option>";
    }

    $jgsSelect = "";

    $high = 13;
    $min = grade::getMinGrade();

    for($i = $min; $i<= $high; $i++) {
      $jgsSelect .= "<option value=\"" . $i . "\">" . $i . ". Jahrgangsstufe</option>";
    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/bestand/index") . "\");");
  }

  private function showMyBooks() {
    /**
     *
     * @var Exemplar[] $myExemplare
     */
    $myAusleihen = [];

    if(DB::getSession()->isTeacher()) {
      $myAusleihen= BuchAusleihe::getByLehrer([DB::getSession()->getTeacherObject()]);
    }

    if(DB::getSession()->isPupil()) {
      $myAusleihen= BuchAusleihe::getBySchueler([DB::getSession()->getPupilObject()]);
    }

    if(DB::getSession()->isEltern()) {
      $myAusleihen= BuchAusleihe::getBySchueler(DB::getSession()->getElternObject()->getMySchueler());
    }


    $html = "";
    for($i = 0; $i < sizeof($myAusleihen); $i++) {
      if($myAusleihen[$i]->isAusleiheActive()) {
        $htmlActive .= "<tr><td>";
        $htmlActive .= $myAusleihen[$i]->getExemplar()->getSchulbuch()->getName() . "</td>";
        $htmlActive .= "<td>ISBN: " . $myAusleihen[$i]->getExemplar()->getSchulbuch()->getISBN() . "<br />Neupreis: " . $myAusleihen[$i]->getExemplar()->getSchulbuch()->getPreis() . "</td>";
        $htmlActive .= "<td>" . $myAusleihen[$i]->getAusleiher() . "</td>";
        $htmlActive .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($myAusleihen[$i]->getAusleiheStartDatum()) . "</td>";
        $htmlActive .= "</tr>";
      }
      else {
        $htmlDone .= "<tr><td>";
        $htmlDone .= $myAusleihen[$i]->getExemplar()->getSchulbuch()->getName() . "</td>";
        $htmlDone .= "<td>ISBN: " . $myAusleihen[$i]->getExemplar()->getSchulbuch()->getISBN() . "<br />Neupreis: " . $myAusleihen[$i]->getExemplar()->getSchulbuch()->getPreis() . "</td>";
        $htmlDone .= "<td>" . $myAusleihen[$i]->getAusleiher() . "</td>";
        $htmlDone .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($myAusleihen[$i]->getAusleiheStartDatum()) . " bis " . DateFunctions::getNaturalDateFromMySQLDate($myAusleihen[$i]->getAusleiheEndDatum()) . "</td>";
        $htmlDone .= "</tr>";
      }

    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schulbuecher/mybooks/index") . "\");");

  }

  /**
   *
   * @param user $user
   */
  public static function userCanAusleihe($user) {
    if($user->isAdmin()) return true;
    if($user->isMember('Webportal_Schulbuecher_Ausleihe')) return true;
    if($user->isMember('Webportal_Schulbuch_Admin')) return true;

    return false;
  }

  /**
   *
   * @param user $user
   */
  public static function userCanRueckgabe($user) {
    if($user->isAdmin()) return true;
    if($user->isMember('Webportal_Schulbuecher_Rueckgeber')) return true;
    if($user->isMember('Webportal_Schulbuch_Admin')) return true;

    return false;
  }

  /**
   *
   * @param user $user
   */
  public static function userCanBestand($user) {
    if($user->isAdmin()) return true;
    if($user->isMember('Webportal_Schulbuecher_Bestand')) return true;
    if($user->isMember('Webportal_Schulbuch_Admin')) return true;

    return false;
  }

  public static function getNotifyItems() {
    return array();
  }
  public static function hasSettings() {
    return true;
  }

  /**
   * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
   * @return array(String, String)
   * array(
   * 	   array(
   * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
   *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
   *      'titel' => "Titel der Beschreibung",
   *      'text' => "Text der Beschreibung"
   *     )
   *     ,
   *     .
   *     .
   *     .
   *  )
   */
  public static function getSettingsDescription() {
    return[
      [
        'name' => 'schulbuecher-erfasung-bankbuecher-aktiv',
        'typ' => 'BOOLEAN',
        'titel' => 'Standardmäßig Bankbücher erfassen?',
        'text' => ''
      ],
        [
            'name' => 'schulbuecher-ausleihe-fotos',
            'typ' => 'BOOLEAN',
            'titel' => 'Fotos der Schüler bei der Ausleihe und Rückgabe zeigen?',
            'text' => ''
        ]
    ];
  }


  public static function getSiteDisplayName() {
    return 'Schulbücher';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array();

  }

  public static function onlyForSchool() {
    return [];
  }

  public static function hasAdmin() {
    return true;
  }

  public static function getAdminMenuIcon() {
    return 'fa fa-book';
  }

  public static function getAdminMenuGroup() {
    return 'Schulbücher';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-book';
  }

  public static function displayAdministration($selfURL) {


    switch($_REQUEST['action']) {
      case 'addUserAusleiher':
        $group = usergroup::getGroupByName('Webportal_Schulbuecher_Ausleihe');
        $group->addUser($_REQUEST['userID']);

        header("Location: $selfURL");
        exit(0);
      break;

      case 'deleteUserAusleiher':
        $group = usergroup::getGroupByName('Webportal_Schulbuecher_Ausleihe');
        $group->removeUser($_REQUEST['userID']);

        header("Location: $selfURL");
        exit(0);
      break;


      case 'addUserRueckgeber':
        $group = usergroup::getGroupByName('Webportal_Schulbuecher_Rueckgeber');
        $group->addUser($_REQUEST['userID']);

        header("Location: $selfURL");
        exit(0);
      break;

      case 'deleteUserRueckgeber':
        $group = usergroup::getGroupByName('Webportal_Schulbuecher_Rueckgeber');
        $group->removeUser($_REQUEST['userID']);

        header("Location: $selfURL");
        exit(0);
      break;

      case 'addUserBestand':
        $group = usergroup::getGroupByName('Webportal_Schulbuecher_Bestand');
        $group->addUser($_REQUEST['userID']);

        header("Location: $selfURL");
        exit(0);
        break;

      case 'deleteUserBestand':
        $group = usergroup::getGroupByName('Webportal_Schulbuecher_Bestand');
        $group->removeUser($_REQUEST['userID']);

        header("Location: $selfURL");
        exit(0);
        break;
    }


    $currentAusleiher = administrationmodule::getUserListWithAddFunction($selfURL, 'ausleiher', 'addUserAusleiher', 'deleteUserAusleiher', 'Ausleiher der Schulbuchbücherei', 'Hier angegebene Benutzer können Schulbücher ausleihen.', 'Webportal_Schulbuecher_Ausleihe');

    $currentRueckgeber = administrationmodule::getUserListWithAddFunction($selfURL, 'rueckgeber', 'addUserRueckgeber', 'deleteUserRueckgeber', 'Rückgeber der Schulbuchbücherei', 'Hier angegebene Benutzer können Schulbücher zurückgeben.', 'Webportal_Schulbuecher_Rueckgeber');

    $currentBestandVerwalter = administrationmodule::getUserListWithAddFunction($selfURL, 'bestandverwalter', 'addUserBestand', 'deleteUserBestand', 'Bestandsverwalter der Schulbuchbücherei', 'Hier angegebene Benutzer können den Bestand verwalten.', 'Webportal_Schulbuecher_Bestand');

    eval("\$return = \"" . DB::getTPL()->get("schulbuecher/admin/index") . "\";");

    return $return;
  }

  public static function getAdminGroup() {
    return 'Webportal_Schulbuch_Admin';
  }
}


?>
