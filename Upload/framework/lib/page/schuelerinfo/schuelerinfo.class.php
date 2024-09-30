<?php

class schuelerinfo extends AbstractPage {

  private $canViewAll = false;
  private $isAdmin = false;

  /**
   * Erlaubte Klassen
   * @var klasse[]
   */
  private $allowedGradesForDetails = [];

  public function __construct() {
    parent::__construct(['Schülerinformationen']);


    $this->checkLogin();

    if(!DB::getSession()->isTeacher() && !DB::getSession()->isMember('Schuelerinfo_Sehen') && !DB::getSession()->isAdmin()) {
      new errorPage('Kein Zugriff');
    }

    if(DB::getSession()->isMember('Webportal_Schuelerinfo_Admin') || DB::getSession()->isAdmin()) $this->isAdmin = true;

    // Welche Klassen erlaubt?

    if($this->isAdmin || DB::getSession()->isMember('Schuelerinfo_Sehen') || schulinfo::isSchulleitung(DB::getSession()->getUser()) || !DB::getSettings()->getBoolean('schuelerinfo-nur-eigene-klassen')) {
      $this->allowedGradesForDetails = klasse::getAllKlassen();
    }
    else if(DB::getSession()->isTeacher()) {
      $this->allowedGradesForDetails = klasse::getByUnterrichtForTeacher(DB::getSession()->getTeacherObject());
    }
    else {
      new errorPage("Sie können keine Klassen sehen.");
    }

  }

  public function execute() {
    // Klassenübersicht anzeigen

    switch($_REQUEST['mode']) {
      default:
        // Klassenübersicht
        $this->klassenuebersicht();
      break;

      case 'klasse':
        $this->schuelerubersicht();
      break;

      case 'schueler':
        $this->schueler();
      break;

      case 'unterrichtliste':
        $this->unterrichtliste();
      break;

      case 'uploadDokument':
        $this->uploadSchuelerDokument();
      break;
      
      case 'addNotiz':
          $this->uploadNotiz();
      break;

      case 'downloadDokument':
        $this->downloadDokument();
      break;

      case 'deleteDokument':
        $this->deleteDokument();
      break;

      case 'addNA':
        $this->addNA();
      break;

      case 'deleteNA':
        $this->deleteNA();
      break;

      case 'writeNewLetter':
        $this->writeNewLetter();
      break;

      case 'editLetter':
        $this->editLetter();
      break;

      case 'uploadFoto':
      	$this->uploadFoto();
      break;

      case 'removeFoto':
      	$this->removeFoto();
      break;

      case 'getFoto':
      	$this->getFoto();
      break;

      case 'addQuaranatene':
        $this->addQuarantaene();
      break;

      case 'deleteQuarantaene':
        $this->deleteQuarantaene();
      break;

      case 'getCompleteQuarantaeneList':
        $this->getCompleteQuarantaeneList();
      break;

      case 'getQuarantaeneListForKlasse':
        $this->getQuarantaeneListeForKlasse();
      break;

      case 'getPDFList':
      	$this->getPDFList();
      break;

      case 'getFotoUebersicht':
      	$this->getFotoUebersicht();
      break;
      
      case 'getFotoZip':
        $this->getFotoZip();
      break;

      case 'searchSchueler':
        $this->searchSchueler();
      break;
    }
  }

  private function searchSchueler() {
    $term = DB::getDB()->escapeString($_REQUEST['term']);
    header("Content-type: text/plain");


    echo("[\r\n");


    if(strlen($term) >= 2) {

      $klassen = [];

      for($i = 0; $i < sizeof($this->allowedGradesForDetails); $i++) $klassen[] = $this->allowedGradesForDetails[$i]->getKlassenName();

      $users = DB::getDB()->query("SELECT schuelerAsvID, schuelerName, schuelerRufname, schuelerVornamen, schuelerKlasse FROM schueler WHERE 
                schueler.schuelerKlasse IN ('" . implode("','", $klassen) . "') AND (schuelerName LIKE '%" . $term . "%' OR schuelerRufname LIKE '%" . $term . "%' OR schuelerVornamen LIKE '%" . $term . "%')");

      $first = true;

      while($user = DB::getDB()->fetch_array($users)) {
        if(!$first) echo(",");
        if($first) {
          $first = false;
        }
        echo("{\"id\": \"" . $user['schuelerAsvID'] . "\",\r\n");
        echo("\"value\": \"" . $user['schuelerAsvID'] . "\",\r\n");
        echo("\"label\": \"" . addslashes($user['schuelerKlasse'] . ": " . $user['schuelerName'] . ", " . $user['schuelerRufname']) . "\"}\r\n");
      }
    }


    echo("]\r\n");
    exit(0);
  }

  private function getQuarantaeneListeForKlasse() {
    $klasse = klasse::getByName($_REQUEST['klasse']);

      if($klasse != null) {
        $schueler = $klasse->getSchueler(false);

        $liste = "<h1>Liste Schülerinnen und Schüler der Klasse " . $klasse->getKlassenName() . " in Quartantäne</h1>";

        $liste .= "<table width=\"100%\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\">
            <tr>
                <td><b>Klasse</b></td>
                <td><b>Schüler</b></td>
                <td><b>Art</b></td>
                <td><b>Beginn</b></td>
                <td><b>Ende</b></td>
            </tr>";


        for($i = 0; $i < sizeof($schueler); $i++) {
          $q = SchuelerQuarantaene::getCurrentForSchueler($schueler[$i]);
          if($q != null) {
            $liste .= "<tr>";
            $liste .= "<td>" . $q->getSchueler()->getKlasse() . "</td>";
            $liste .= "<td>" . $q->getSchueler()->getCompleteSchuelerName() . "</td>";
            $liste .= "<td>" . $q->getArtDisplayName() . "</td>";
            $liste .= "<td>" . $q->getStartAsNaturalDate() . "</td>";
            $liste .= "<td>" . $q->getEndAsNaturalDate() . "</td>";
            $liste .= "</tr>";
          }

        }
        $liste .= "</table>";

        $print = new PrintNormalPageA4WithHeader("Schüler in Quarantäne");
        $print->setHTMLContent($liste);
        $print->send();
      }
  }

  private function getCompleteQuarantaeneList() {
    if(SchuelerQuarantaene::isActive()) {

      $alle = SchuelerQuarantaene::getAll();

      $liste = "<h1>Liste alle Schülerinnen und Schüler in Quartantäne</h1>";

      $liste .= "<table width=\"100%\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\">
            <tr>
                <td><b>Klasse</b></td>
                <td><b>Schüler</b></td>
                <td><b>Art</b></td>
                <td><b>Beginn</b></td>
                <td><b>Ende</b></td>
            </tr>";

      for($i = 0; $i < sizeof($alle); $i++) {
        if($alle[$i]->isToday()) {
          $liste .= "<tr>";
          $liste .= "    <td>" . $alle[$i]->getSchueler()->getKlasse() . "</td>";
          $liste .= "    <td>" . $alle[$i]->getSchueler()->getCompleteSchuelerName() . "</td>";
          $liste .= "    <td>" . $alle[$i]->getArtDisplayName() . "</td>";
          $liste .= "    <td>" . $alle[$i]->getStartAsNaturalDate() . "</td>";
          $liste .= "    <td>" . $alle[$i]->getEndAsNaturalDate() . "</td>";
          $liste .= "</tr>";
        }
      }

      $liste .= "</table>";

      $print = new PrintNormalPageA4WithHeader("Schüler in Quarantäne");
      $print->setHTMLContent($liste);
      $print->send();


    } else new errorPage("Quarantänefunktionen nicht aktiv!");
  }

  private function addQuarantaene() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage('Schüler nicht vorhanden');
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    if(!SchuelerQuarantaene::isActive()) new errorPage("Quarantäne Funktionen nicht aktiv.");

    $startDate = DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['startDate']);
    $endDate = DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['endDate']);

    $type = $_REQUEST['quarantaeneArt'] == 'I' ? 'I' : 'K1';

    /**
     * @var FileUpload|null
     */
    $attachment = null;

    $attachmentUpload = FileUpload::uploadOfficeDocumentsAndPDF("quarantaeneAnhang", "Anhang Quarantäne zu " . $schueler->getCompleteSchuelerName());

    if($attachmentUpload['result']) $attachment = $attachmentUpload['uploadobject'];


    $addOtherK1 = $_REQUEST['addk1forother'] > 0;

    SchuelerQuarantaene::addForSchueler($schueler, $startDate, $endDate, $type, $_REQUEST['comment'], $attachment);

    $infoTable = "<table border='1'><tr><td>" . $schueler->getCompleteSchuelerName() . "</td>";

    if($type == 'I') $infoTable .= "<td>Isolation</td>";
    else $infoTable .= "<td>Quarantäne</td>";
    $infoTable .= "</tr>";


    if($addOtherK1 && $type == 'I') {
      $klasse = $schueler->getKlassenObjekt();
      if($klasse != null) {
        $schuelerDerKlasse = $klasse->getSchueler(false);

        for($i = 0; $i < sizeof($schuelerDerKlasse); $i++) {
          if($schuelerDerKlasse[$i]->getAsvID() != $schueler->getAsvID()) {
            SchuelerQuarantaene::addForSchueler($schuelerDerKlasse[$i], $startDate, $endDate, 'K1', "Kontaktperson von " . $schueler->getCompleteSchuelerName(), null);
            $infoTable .= "<tr><td>" . $schuelerDerKlasse[$i]->getCompleteSchuelerName() . "</td>";
            $infoTable .= "<td>Quarantäne</td>";
            $infoTable .= "</tr>";
          }
        }
      }


    }

    $infoTable .= "</table>";

    $informTeacher = $_REQUEST['informTeacher'] > 0;
    if($informTeacher) {

      if($type == 'I') {
        $subject = "Neuer Isolationsfall in Klasse " . $schueler->getKlasse();
      } else {
        $subject = "Neuer Quarantänefall in Klasse " . $schueler->getKlasse();
      }
      $text = "<p>In der Klasse " . $schueler->getKlasse() . " gibt es folgende neue Quaratänefalle:</p>";
      $text .= $infoTable;



      FACTORY::sendMessage([
          "receiver_leader_klasse" => $schueler->getKlasse(),
          "sender_id" => false,
          "noAnswer" => true,
          "isPrivat" => true,
          "subject" => $subject,
          "text" => $text
      ]);

      /*
        $messageSender = new MessageSender();
        $messageRecipientHandler = new RecipientHandler("");
        $messageRecipientHandler->addRecipient(new KlassenteamRecipient($schueler->getKlasse()));
        $messageSender->setRecipients($messageRecipientHandler);
        $messageSender->setSender(user::getSystemUser());

        $messageSender->dontAllowAnswer();
        $messageSender->setConfidential();  // Vertraulich
        if($type == 'I') {
          $messageSender->setSubject("Neuer Isolationsfall in Klasse " . $schueler->getKlasse());
        } else {
          $messageSender->setSubject("Neuer Quarantänefall in Klasse " . $schueler->getKlasse());
        }
        $text = "<p>In der Klasse " . $schueler->getKlasse() . "gibt es folgende neue Quaratänefalle:</p>";
        $text .= $infoTable;
        $messageSender->setText($text);

        $messageSender->send();
        */

    }

    header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&openQuarantaene=1");

  }

  private function deleteQuarantaene() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage('Schüler nicht vorhanden');
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    if(!SchuelerQuarantaene::isActive()) new errorPage("Quarantäne Funktionen nicht aktiv.");

    $alle = SchuelerQuarantaene::getAllForSchueler($schueler);

    for($i = 0; $i < sizeof($alle); $i++) {
      if($alle[$i]->getID() == $_REQUEST['quarantaeneID']) {
        $alle[$i]->delete();
      }
    }

    if($_REQUEST['deleteInClass'] > 0) {
      $alleSchueler = $schueler->getKlassenObjekt()->getSchueler(false);
      for($i = 0; $i < sizeof($alleSchueler); $i++) {
        $car = SchuelerQuarantaene::getAllForSchueler($alleSchueler[$i]);
        for($c = 0; $c < sizeof($car); $c++) {
          if($car[$c]->isToday()) $car[$c]->delete();
        }
      }
    }

    header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&openQuarantaene=1");
    exit(0);
  }


  private function getFotoZip() {
      $klasse = klasse::getByName($_REQUEST['klasse']);
      
      if($klasse != null) {
          $schueler = $klasse->getSchueler(true);
          
          
          
          $foldername = md5(rand());
          $zip = new ZipArchive();
          $filename = "../data/temp/$foldername.zip";
          
          if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
              die("cannot open <$filename>\n");
          }
          
          for($i = 0; $i < sizeof($schueler); $i++) {
              if($schueler[$i]->getFoto() != null) {
                  $zip->addFile($schueler[$i]->getFoto()->getFilePath(), $schueler[$i]->getName() . " " . $schueler[$i]->getRufname() . "." . $schueler[$i]->getFoto()->getExtension());
              }
                    
          }
          
          $zip->close();
          
          
          
          // Send File
          
          $file = $filename;
          
          header('Content-Description: File Transfer');
          header('Content-Type: application/zip');
          header('Content-Disposition: attachment; filename='.basename("Schuelerfotos Klasse " . $klasse->getKlassenName() . ".zip"));
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Pragma: public');
          header('Content-Length: ' . filesize($file));
          ob_clean();
          flush();
          readfile($file);
          
          // unlink($file);
          exit(0);
          
      }
  }
  
  private function getFotoUebersicht() {
  	$klasse = klasse::getByName($_REQUEST['klasse']);

  	if($klasse != null) {

  	      if(DB::getCache()->isItemSet('schuelerinfo-fotoseite-' . $klasse->getKlassenName())) {
  	        $upload = FileUpload::getByID(DB::getCache()->getAsText('schuelerinfo-fotoseite-' . $klasse->getKlassenName()));
  	        if($upload != null) {
  	          $upload->sendFile();
  	          exit();
            }
          }


  		 $schueler = $klasse->getSchueler(true);

  		 // cssjs/images/userimages/default.png

  		 $html = "<h2>Fotoübersicht der Klasse " . $klasse->getKlassenName() . "</h2><table border=\"1\" cellpadding=\"2\" width=\"100%\"><tr>";

  		 $perLine = 0;
  		 for($i = 0; $i < sizeof($schueler); $i++) {
  		 	$perLine++;

  		 	$html .= "<td width=\"16%\" align=\"center\">";

  		 	if($schueler[$i]->getFoto() != null) {
  		 		$html .= "<img src=\"" . $schueler[$i]->getFoto()->getURLToFile() . "&maxWidth=200\">";
  		 	}
  		 	else {
  		 		$html .= "<img src=\"cssjs/images/userimages/default.png\">";
  		 	}

  		 	$html .= "<br />" . $schueler[$i]->getCompleteSchuelerName() . "</td>";

  		 	if($perLine == 6 && $i < (sizeof($schueler)-1)) {
  		 		$html .= "</tr><tr>";
  		 		$perLine = 0;
  		 	}
  		 }


  		 for($i = $perLine; $i <6; $i++) {
  		 	$html .= "<td>&nbsp;</td>";
  		 }

  		 $html .= "</tr></table>";

  		 $print = new PrintNormalPageA4WithHeader('Fotoübersicht Klasse ' . $klasse->getKlassenName());
  		 $print->setHTMLContent($html);
  		 $print->setPrintedDateInFooter();


        if(DB::getCache()->isCacheEnabled()) {
            $upload = FileUpload::uploadFromTCPdf('Fotoseite ' . $klasse->getKlassenName(), $print);
            $uploadID = $upload['uploadobject']->getID();
            DB::getCache()->storeText('schuelerinfo-fotoseite-' . $klasse->getKlassenName(), $uploadID);
        }


        $print->send();



  		 exit(0);

  	}
  }


  private function removeFoto() {
  	if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren')) {

  		$schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

  		if($schueler == null) {
  			new errorPage('Schüler nicht vorhanden');
  		}

  		if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
  			new errorPage();
  		}

  		$upload = $schueler->getFoto();
  		if($upload != null) {
  			$schueler->removeFoto();

          // Eventuell Cache für PDF Seite leeren
          if(DB::getCache()->isCacheEnabled()) DB::getCache()->forgetItem('schuelerinfo-fotoseite-' . $schueler->getKlasse());


  		}
  		header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID());

  	}
  }

  private function getFoto() {
  	if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren')) {

  		$schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

  		if($schueler == null) {
  			new errorPage('Schüler nicht vorhanden');
  		}

  		if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
  			new errorPage();
  		}

  		$upload = $schueler->getFoto();
  		
  		if($upload != null) {
  			$upload->sendImageWidthMaxWidth("250");
  			exit(0);
  		}
  		else {
  			header("Location: cssjs/images/userimages/default.png");
  			exit(0);
  		}
  	}
  }


  private function uploadFoto() {
  	if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren')) {

  		$schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

  		if($schueler == null) {
  			new errorPage('Schüler nicht vorhanden');
  		}

  		if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
  			new errorPage();
  		}

  		$upload = FileUpload::uploadPicture('schuelerFoto', $schueler->getCompleteSchuelerName());

  		if($upload['result']) {
  			// Erfolgreich

  			/**
  			 *
  			 * @var FileUpload $uploadObject
  			 */
  			$uploadObject = $upload['uploadobject'];

  			$schueler->setFoto($uploadObject);

  			// Eventuell Cache leeren

          if(DB::getCache()->isCacheEnabled()) DB::getCache()->forgetItem('schuelerinfo-fotoseite-' . $schueler->getKlasse());

          header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID());
          exit(0);

  		}
  		else {
  			new errorPage("Leider konnte das Bild nocht hochgeladen werden. Eventuell war es kein Bild?");
  		}
  	}
  }

  private function writeNewLetter() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage('Schüler nicht vorhanden');
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    $lehrer = DB::getSession()->isTeacher() ? DB::getSession()->getTeacherObject() : null;

    $newLetter = SchuelerBrief::getNewLetter($schueler, $lehrer, $_REQUEST['saveLetter'] > 0);

    $adressen = $schueler->getAdressen();

    $adressenHTMLSelect = "<table class=\"table\"><tr>";


    for($a = 0; $a < sizeof($adressen); $a++) {
      if($_REQUEST['adresseID'] == $adressen[$a]->getID()) {
        $newLetter->setAdresse($adressen[$a]->getAdresseAnschrift());
        $newLetter->setAnrede($adressen[$a]->getAnredeText());
        break;
      }
    }

    if($lehrer != null) $newLetter->setUnterschrift('Mit freundlichen Grüßen,<br /><br /><br />' . $lehrer->getDisplayNameMitAmtsbezeichnung());
    else $newLetter->setUnterschrift("Mit freundlichen Grüßen,<br /><br /><br />" . DB::getSession()->getUser()->getDisplayName());
    $newLetter->setDatum(DateFunctions::getTodayAsNaturalDate());
    
    

    header("Location: index.php?page=schuelerinfo&mode=editLetter&letterID=" . $newLetter->getID());

    exit(0);

  }

  private function editLetter() {

    $letter = SchuelerBrief::getByID($_REQUEST['letterID']);

    if($letter == null) {
      new errorPage();
    }

    $schueler = $letter->getSchueler();


    if($schueler == null) {
      new errorPage();
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    switch($_REQUEST['action']) {
      default:
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schuelerinfo/editletter") . "\");");
        PAGE::kill(true);
			  //exit(0);
      break;

      case 'saveLetter':

        $letter->setAdresse($_POST['briefAdresse']);

        $letter->setDatum($_POST['briefDatum']);

        $letter->setBetreff($_POST['briefBetreff']);

        $letter->setAnrede($_POST['briefAnrede']);

        $letter->setText($_POST['briefText']);

        $letter->setUnterschrift($_POST['briefUnterschrift']);

        $letterPDF = $letter->getLetterPDF();

        if($_REQUEST['saveToDoks'] && $_REQUEST['save'] == 2) {
          SchuelerDokument::uploadFileFromTCPDF($schueler, $_POST['briefBetreff'], $letterPDF);
        }

        switch($_REQUEST['save']) {
          case 1:
            header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&openLetter=1");
            exit(0);
          case 2:
            header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&openLetter=1&downloadLetter=" . $letter->getID());
          break;
        }
      break;

      case 'downloadLetter':
        $letterPDF = $letter->getLetterPDF();
        $letter->setPrinted();
        $letterPDF->Output($letter->getBetreff() .".pdf",'D');
        exit(0);
      break;

      case 'markDone':
      	$letter->setErledigt($_POST['kommentarErledigt']);
      	header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&openLetter=1");
      	exit(0);
      break;

    }
  }


  private function deleteDokument() {
    $dokument = SchuelerDokument::getByID($_REQUEST['dokumentID']);

    if($dokument != null) {

      $schueler = $dokument->getSchueler();

      if($schueler == null) {
        new errorPage();
      }

      if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
        new errorPage();
      }

      $dokument->delete();

      header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&deleteDokumentOK=1");
      exit(0);

    }
    else {
      new errorPage("Dokument nicht vorhanden!");
    }
  }

  private function deleteNA() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage();
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    $na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler);
    if($na != null) $na->delete();

    header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&nsOK=1");
    exit(0);
  }

  private function addNA() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage();
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    if($_REQUEST['gueltigbis'] != '') {
      if(DateFunctions::isNaturalDate($_REQUEST['gueltigbis'])) {
        $date = DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['gueltigbis']);
      }
      else $date = null;
    }
    else $date = null;
    
    if($_REQUEST['gewichtung'] != '') $gewichtung = $_REQUEST['gewichtung'];
    else $gewichtung = null;

    SchuelerNachteilsausgleich::setNachteilsausgleichForSchueler($schueler, $_REQUEST['art'], $_REQUEST['azv'], $_REQUEST['ns'], $_REQUEST['kommentar'], $date, $gewichtung);

    header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&nsOK=1");
    exit(0);
  }


  private function downloadDokument() {
    $dokument = SchuelerDokument::getByID($_REQUEST['dokumentID']);

    if($dokument != null) {

      $schueler = $dokument->getSchueler();

      if($schueler == null) {
        new errorPage();
      }

      if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
        new errorPage();
      }

      $dokument->getUpload()->sendFile();
      exit(0);
    }
    else {
      new errorPage("Dokument nicht vorhanden!");
    }
  }

  private function uploadSchuelerDokument() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage();
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }

    $result = SchuelerDokument::uploadFile($schueler, $_REQUEST['dokumentName'], $_REQUEST['dokumentKommentar'], 'dokumentFile');

    if($result) {
      header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&uploadOK=1");
      exit(0);
    }
    else {
      header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&uploadFail=1");
      exit(0);
    }
  }
  
  private function uploadNotiz() {
      $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);
            
      if($schueler == null) {
          new errorPage();
      }
      
      if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
          new errorPage();
      }
      
      $result = SchuelerDokument::addKommentar($schueler, $_REQUEST['dokumentName'], $_REQUEST['dokumentKommentar']);
      
      if($result) {
          header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&uploadOK=1");
          exit(0);
      }
      else {
          header("Location: index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler->getAsvID() . "&uploadFail=1");
          exit(0);
      }
  }

  private function schueler() {
    $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);

    if($schueler == null) {
      new errorPage();
    }

    if(!$this->checkGradeAccess($schueler->getKlassenObjekt())) {
      new errorPage();
    }


    $adressen = $schueler->getAdressen();
    $telefonNummern = $schueler->getTelefonnummer();
    $elternEmail = $schueler->getElternEMail();

    $currentDate = $_GET['currentDate'];

    $sprachen = $schueler->getFremdsprachen();

    $fremdsprachen = [];

    for($i = 0; $i < sizeof($sprachen); $i++) {
      $fremdsprachen[] = $sprachen[$i]->getSpracheFach() . (($sprachen[$i]->getSpracheAbJahrgangsstufe() !== null) ? " (ab " . $sprachen[$i]->getSpracheAbJahrgangsstufe() . ")" : "");
    }

    $fremdsprachen = implode(", ", $fremdsprachen);

    $adressenHTML = "";


    for($a = 0; $a < sizeof($adressen); $a++) {

      if($a > 0) $adressenHTML .= "<hr noshade>";

      $adressenHTML .=  $adressen[$a]->getAdresseAsText() . "<p><a href=\"http://maps.google.de/maps?q=" . $adressen[$a]->getGoogleMapsQuery() . "\" target=\"_blank\" class='btn btn-default'><i class=\"fa fa-map\"></i> Auf Google Maps anzeigen</a></p>\r\n";

      for($b = 0; $b < sizeof($telefonNummern); $b++) {
        if($telefonNummern[$b]->getAdresseID() == $adressen[$a]->getID()) {
          if($telefonNummern[$b]->getTyp() == 'mobiltelefon') {
            $adressenHTML .= "<i class=\"fa fa-mobile\"></i> " . $telefonNummern[$b]->getNummer() . "<br />";
          }
          elseif($telefonNummern[$b]->getTyp() == 'fax') {
            $adressenHTML .= "<i class=\"fa fa-fax\"></i> " . $telefonNummern[$b]->getNummer() . "<br />";
          }
          else {
            $adressenHTML .= "<i class=\"fa fa-mobile\"></i> " . $telefonNummern[$b]->getNummer() . "<br />";
          }
        }
      }

      for($b = 0; $b < sizeof($elternEmail); $b++) {
        if($elternEmail[$b]->getAdresseID() == $adressen[$a]->getID()) {
          $adressenHTML .= "<i class=\"fa fa-envelope\"></i> " . $elternEmail[$b]->getEMail() . "<br />";
        }
      }
    }

    // Absenzen

    include_once("../framework/lib/data/absenzen/Absenz.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");


    $date = $_GET['currentDate'];
    $grade = $schueler->getKlasse();

    $absenzen = Absenz::getAbsenzenForSchueler($schueler);

    
    $absenzenCalculator = new AbsenzenCalculator($absenzen);
    $absenzenCalculator->calculate();
    
    $absenzenStat = $absenzenCalculator->getDayStat();
    
    $total = $absenzenCalculator->getTotal();
    $beurlaubt = $absenzenCalculator->getBeurlaubt();
    $entschuldigt = $absenzenCalculator->getEntschuldigt();
    $fpatotal = $absenzenCalculator->getFPATotal();

    $krankenzimmer = DB::getDB()->query("SELECT * FROM absenzen_sanizimmer WHERE sanizimmerSchuelerAsvID='" . $schueler->getAsvID() . "' ORDER BY sanizimmerTimeStart");

    $sanizimmerTotal = 0;
    $sanizimmerMinutenTotal = 0;

    $sanizimmerData = array();
    while($s = DB::getDB()->fetch_array($krankenzimmer)) {
      $sanizimmerTotal++;
      $sanizimmerMinutenTotal += floor(($s['sanizimmerTimeEnde'] - $s['sanizimmerTimeStart']) / 60);
      $sanizimmerData[] = $s;
    }


    $verspaetungenTotal = 0;
    $verspaetungenMinutenTotal = 0;

    $krankenzimmer = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen WHERE verspaetungSchuelerAsvID='" . $schueler->getAsvID() . "' ORDER BY verspaetungDate");

    $verspaetungData = array();
    while($s = DB::getDB()->fetch_array($krankenzimmer)) {
      $verspaetungenTotal++;
      $verspaetungenMinutenTotal += $s['verspaetungMinuten'];
      $verspaetungData[] = $s;
    }

    $stundenHeader = "";

    $maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
    for($i = 1; $i <= $maxStunden; $i++) $stundenHeader .= "<th>" . $i . "</th>";

    $absenzenHTML = "";
    for($i = 0; $i < sizeof($absenzen); $i++) {
      $absenzenHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate());
      $absenzenHTML .= "</td><td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</td>";
      $absenzenHTML .= "<td>";

      if($absenzen[$i]->getKommentar() != "") {
        $absenzenHTML .= "<a href=\"#\" data-toggle=\"tooltip\" title=\"" . $absenzen[$i]->getKommentar() . "\"><i class=\"fa fa-sticky-note-o\"></i></a> ";
      }
      if($absenzen[$i]->isBeurlaubung()) $absenzenHTML .= "<span class=\"label label-info\">Beurlaubung</span>";
      else if($absenzen[$i]->isBefreiung()) $absenzenHTML .= "<span class=\"label label-info\">Befreiung</span>";


      $stunden = $absenzen[$i]->getStundenAsArray();
      for($s = 1; $s <= $maxStunden; $s++) {
        $absenzenHTML .= "<td>";
        if(in_array($s,$stunden)) $absenzenHTML .= "X";
        else $absenzenHTML .= "&nbsp;";
        $absenzenHTML .= "</td>";
      }
// 			$absenzenHTML .= "<td><a href=\"index.php?page=absenzensekretariat&currentDate=" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate()) . "&noReturnMainView=1&openAbsenz={$absenzen[$i]->getID()}\">Bearbeiten / Löschen</a></td>";

      $absenzenHTML .= "</tr>";

    }

    $krankenzimmerHTML = "";

    for($i = 0; $i < sizeof($sanizimmerData); $i++) {
      $krankenzimmerHTML .= "<tr><td>" . date("d.m.Y",$sanizimmerData[$i]['sanizimmerTimeStart']) . "</td>";
      $krankenzimmerHTML .= "<td>" . date("H:i",$sanizimmerData[$i]['sanizimmerTimeStart']) . "</td>";
      $krankenzimmerHTML .= "<td>" . date("H:i",$sanizimmerData[$i]['sanizimmerTimeEnde']) . "</td>";
      $krankenzimmerHTML .= "<td>";

      switch($sanizimmerData[$i]['sanizimmerResult']) {
        case 'ZURUECK':
          $krankenzimmerHTML .= "Zurück in den Unterricht"; break;

        case 'BEFREIUNG':
          $krankenzimmerHTML .= "Befreiung ausgestellt"; break;

        case 'RETTUNGSDIENST':
          $krankenzimmerHTML .= "Abholung Rettungsdienst"; break;

      }

      $krankenzimmerHTML .= "</td>";
      $krankenzimmerHTML .= "<td>{$sanizimmerData[$i]['sanizimmerGrund']}</td>";

    }

    $verspaetungHTML = "";

    for($i = 0; $i < sizeof($verspaetungData); $i++) {
      $verspaetungHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($verspaetungData[$i]['verspaetungDate']) . "</td>";
      $verspaetungHTML .= "<td>" . $verspaetungData[$i]['verspaetungMinuten'] . " Minuten zur " . $verspaetungData[$i]['verspaetungStunde'] . ". Stunde</td>";
      $verspaetungHTML .= "<td>" . $verspaetungData[$i]['verspaetungKommentar'] . "</td></tr>";
    }


    // Dokumente

    $dokumente = SchuelerDokument::getAllForSchueler($schueler);

    $dokumenteHTML = '';
    for($i = 0; $i < sizeof($dokumente); $i++) {
      $dokumenteHTML .= '<tr><td>' . functions::makeDateFromTimestamp($dokumente[$i]->getUpload()->getUploadTime()) . "</td>";

      if($dokumente[$i]->isNotiz()) {
          $dokumenteHTML .= "<td><i class=\"fa fa-sticky-note\"></i> " . $dokumente[$i]->getName() . "<br />";
          
          $dokumenteHTML .= "Notiz:<br /><pre>" . $dokumente[$i]->getNotizContent() . "</pre><br />";
          if($dokumente[$i]->getKommentar() != '') $dokumenteHTML .= "<br />" . $dokumente[$i]->getKommentar();
          
      }

      else {
          $dokumenteHTML .= "<td><a href=\"index.php?page=schuelerinfo&mode=downloadDokument&dokumentID=" . $dokumente[$i]->getID() . "\"><i class=\"" . $dokumente[$i]->getUpload()->getFileTypeIcon() . "\"></i> " . $dokumente[$i]->getName() . "</a>";
          
          $dokumenteHTML .= "<br /><small>" . $dokumente[$i]->getUpload()->getFileSize() . "<br />";
          
          
      }

      

      if($dokumente[$i]->getUpload()->getUploader() != null) {
        $dokumenteHTML .= "Abgelegt von: " . $dokumente[$i]->getUpload()->getUploader()->getDisplayName() . "</small>";
      }
      else {
        $dokumenteHTML .= "Abgelegt von: <i>Gelöscht</i></small>";
      }





      $dokumenteHTML .= "</td><td><form><button type=\"button\" class=\"btn btn-sm btn-danger\" onclick=\"javascript:confirmAction('Soll das Dokument wirklich gelöscht werden?','index.php?page=schuelerinfo&mode=deleteDokument&dokumentID=" . $dokumente[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i> Dokument löschen</button></form></tr>";
    }


    // Ausgetreten?`
    $austrittInfo = "";
    if($schueler->isAusgetreten()) {
      $austrittInfo = "<label class=\"label label-danger\">Ausgetreten zum " . DateFunctions::getNaturalDateFromMySQLDate($schueler->getAustrittDatumAsMySQLDate()) . "</label> ";
    }

    // Nachteilsausgleich

    $na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler);

    if($na != null) $hasNA = true;
    else $hasNA = false;

    // Besuchter Unterricht

    $unterrichtHTML = "";

    $unterricht = SchuelerUnterricht::getUnterrichtForSchueler($schueler);

    for($i = 0; $i < sizeof($unterricht); $i++) {
      $lehrerName = "";
      if($unterricht[$i]->getLehrer() != null) $lehrerName = $unterricht[$i]->getLehrer()->getDisplayNameMitAmtsbezeichnung();
      $unterrichtHTML .= "<tr><td>" . $unterricht[$i]->getFach()->getKurzform() . "</td><td>" . $lehrerName . "</td></tr>";
    }

    // Ausgeliehene Bücher

    $schulbuecherActive = false;
    if($this->isActive('schulbuecher')) {
      $schulbuecherActive = true;

      $buecherHTML = "";

      $exemplare = BuchAusleihe::getBySchueler([$schueler]);

      for($i = 0; $i < sizeof($exemplare); $i++) {
        if($exemplare[$i]->isAusleiheActive()) $buecherHTML .= "<tr><td>" . $exemplare[$i]->getExemplar()->getBarcode() . "</td><td>" . $exemplare[$i]->getExemplar()->getSchulbuch()->getName() . "</td></tr>";
      }
    }


    // Briefe

    if(DB::getSession()->isTeacher()) {
      $teacher = DB::getSession()->getTeacherObject();
    }
    else $teacher = null;

    $briefe = SchuelerBrief::getBriefForSchuelerAndTeacher($schueler,$teacher);

    $briefHTML = '';

    for($i = 0; $i < sizeof($briefe); $i++) {
      $briefHTML .= '<tr><td>' . ($i+1) . "</td>";
      $briefHTML .= "<td>" . (($briefe[$i]->getLehrer() != null) ? $briefe[$i]->getLehrer()->getDisplayNameMitAmtsbezeichnung() : "n/a") . "</td>";
      $briefHTML .= "<td>" . $briefe[$i]->getBetreff();

      if(!$briefe[$i]->isSaveLonger()) $briefHTML .= "<br /><label class=\"label label-danger\">Nur noch bis " . functions::makeDateFromTimestamp($briefe[$i]->getLastChangeTime() + (30*60)) . " gespeichert.</label>";

      $briefHTML .= "<td>" . ($briefe[$i]->isBriefGedruckt() ? ("<label class=\"label label-success\"><i class=\"fa fa-check\"></i> " . $briefe[$i]->getPrintDate()) : ("<label class=\"label label-danger\">Noch nicht gedruckt</label>")) . "</td>";

      $erledigt = "<form action=\"index.php?page=schuelerinfo&mode=editLetter&letterID=" . $briefe[$i]->getID() . "&action=markDone\" method=\"post\"><label class=\"label label-danger\">Nicht erledigt / keine Antwort</label><br /><input type=\"text\" name=\"kommentarErledigt\" placeholder=\"Kommentar zur Erledigung\" class=\"form-control\"><button class=\"btn btn-success\" type=\"submit\"><i class=\"fa fa-check\"></i> Als erledigt kennzeichen</button></form>";

      $briefHTML .= "<td>" . ($briefe[$i]->isErledigt() ? ("<label class=\"label label-success\"><i class=\"fa fa-check\"></i> " . $briefe[$i]->getErledigtDate() . "</label><br />" . $briefe[$i]->getErledigtKommentar() . "") : ($erledigt)) . "</td>";
      $briefHTML .= "<td><form><button type=\"button\" class=\"btn\" onclick=\"window.location.href='index.php?page=schuelerinfo&mode=editLetter&letterID=" . $briefe[$i]->getID() . "'\"><i class=\"fa fas fa-pencil-alt\"></i> Brief bearbeiten / drucken</button></form>";




    }

    // Adressen

    $adressen = $schueler->getAdressen();

    $adressenHTMLSelect = "<table class=\"table\"><tr>";


    for($a = 0; $a < sizeof($adressen); $a++) {



      $adressenHTMLSelect .=  "<td><label for=\"a" . $i . "\">" . $adressen[$a]->getAdresseAnschriftMitAuskunft() . "</label><br />
<input type=\"radio\" value=\"" . $adressen[$a]->getID() . "\" name=\"adresseID\" id=\"a" . $i . "\"" . ($adressen[$a]->isHauptansprechpartner() ? (" checked=\"checked\"") : ("")) . "</td>";

    }

    $adressenHTMLSelect .= "</tr></table>";
    
    // Noten
    
    if(DB::getGlobalSettings()->hasNotenverwaltung) {
        $withNoten = true;
        
        $notenbogen = new Notenbogen($schueler);
        $notenbogen->showDeletButtonForNoten();
        $notenbogen->setWithEditLinkToNotenEingabe();

        
        $notentabelle = $notenbogen->getNotentabelle();
        
    }


    // Quarantaene

    $quarantaeneAktiv = SchuelerQuarantaene::isActive();

    $quarantaene = SchuelerQuarantaene::getCurrentForSchueler($schueler);

    $alleQuarantaene = SchuelerQuarantaene::getAllForSchueler($schueler);

    $quarantaeneHTML = "";

    for($i = 0; $i < sizeof($alleQuarantaene); $i++) {
      $quarantaeneHTML .= "<tr>";

      $quarantaeneHTML .= "<td>" . $alleQuarantaene[$i]->getArtDisplayName() . "</td>";
      $quarantaeneHTML .= "<td>" . $alleQuarantaene[$i]->getStartAsNaturalDate() . "</td>";
      $quarantaeneHTML .= "<td>" . $alleQuarantaene[$i]->getEndAsNaturalDate() . "</td>";
      $quarantaeneHTML .= "<td>" . $alleQuarantaene[$i]->getKommentar() . "</td>";

      $fileUpload = $alleQuarantaene[$i]->getAttachment();

      $quarantaeneHTML .= "<td>";

      if($fileUpload != null) {
        $quarantaeneHTML .= "<a href=\"" . $fileUpload->getURLToFile() . "\" class=\"btn btn-block btn-default\"><i class=\"fa fa-download\"></i> Anlage</a>";
      }
      else $quarantaeneHTML .= "-- &nbsp;";



      $quarantaeneHTML .= "</td>";

      $quarantaeneHTML .= "<td>";

      $bearbeiter = $alleQuarantaene[$i]->getCreateUser();
      if($bearbeiter != null) {
        $quarantaeneHTML .= $bearbeiter->getDisplayNameWithFunction();
      }
      $quarantaeneHTML .= "</td>";



      $quarantaeneHTML .= "<td>";

      $quarantaeneHTML .= "<p><button class='btn btn-default btn-block' onclick=\"confirmAction('Möchten Sie den Eintrag löschen?','index.php?page=schuelerinfo&mode=deleteQuarantaene&schuelerAsvID=".$schueler->getAsvID() . "&quarantaeneID=" . $alleQuarantaene[$i]->getID() . "')\"><i class='fa fa-trash'></i> Löschen</button></p>";
      $quarantaeneHTML .= "<p><button class='btn btn-default btn-block' onclick=\"confirmAction('Möchten Sie den Eintrag und alle aktiven (aktuell gültigen) Quarantäne aus dieser Klasse löschen?','index.php?page=schuelerinfo&mode=deleteQuarantaene&schuelerAsvID=".$schueler->getAsvID() . "&quarantaeneID=" . $alleQuarantaene[$i]->getID() . "&deleteInClass=1')\"><i class='fa fa-trash'></i> Diesen Eintrag und alle Quarantäne aus der Klasse löschen</button></p>";

      $quarantaeneHTML .= "</td>";


      $quarantaeneHTML .= "</tr>";

    }


    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schuelerinfo/schueler") . "\");");
  }

  /**
   *
   * @param klasse $grade
   */
  private function checkGradeAccess($grade) {
    if($grade == null) return false;
    for($i = 0; $i < sizeof($this->allowedGradesForDetails); $i++) {
      if($this->allowedGradesForDetails[$i]->getKlassenName() == $grade->getKlassenName()) return true;
    }

    return false;
  }

  private function schuelerubersicht() {
    $klasse = klasse::getByName($_REQUEST['klasse']);
    $schueler = [];

    if($klasse != null) {

      $schuelerListe = '';

      $showUnterrichtListe = false;

      // Unterricht

      if($_REQUEST['klasse'] == 'ANDEREKLASSE') {
        $unterricht = SchuelerUnterricht::getSonstigenUnterricht();
      } else {
        $unterricht = SchuelerUnterricht::getUnterrichtForKlasse($klasse);
      }

      $faecherHTML = "";

      $currentUnterricht = null;

      for($i = 0; $i < sizeof($unterricht); $i++) {
        $fach = (($unterricht[$i]->getFach() != null) ? $unterricht[$i]->getFach()->getKurzform() . " (" . $unterricht[$i]->getBezeichnung() . ")" : "n/a");
        $lehrer = (($unterricht[$i]->getLehrer() != null) ? $unterricht[$i]->getLehrer()->getKuerzel() : "n/a");

        $koppel = $unterricht[$i]->getKoppelUnterricht();

        $koppelStatus = [];
        for($k = 0; $k < sizeof($koppel); $k++) {
          $koppelStatus[] = (($koppel[$k]->getFach() != null) ? $koppel[$k]->getFach()->getKurzform() . " (" . $koppel[$k]->getBezeichnung() . ")" : "n/a") . " bei " .  (($koppel[$k]->getLehrer() != null) ? $koppel[$k]->getLehrer()->getKuerzel() : "n/a");
        }



        $highlight = '';

        if($_REQUEST['unterrichtID'] == $unterricht[$i]->getID()) {
          $schueler = $unterricht[$i]->getSchueler();
          $showUnterrichtListe = true;
          $highlight = ' style="background-color:#ffe6e6;"';
          $currentUnterricht = $unterricht[$i];
        }

        $faecherHTML .= "<tr><td$highlight><a href=\"index.php?page=schuelerinfo&mode=klasse&klasse=" . $klasse->getKlassenName() . "&unterrichtID=" . $unterricht[$i]->getID() . "\">" . $fach  . "</a>" . ((sizeof($koppelStatus) > 0) ? ("<br ><small>Koppel mit: " . implode(", ",$koppelStatus) . "</small>") : ("")) . "</td><td$highlight>" . $lehrer . "</td></tr>\r\n";

      }


      if($_REQUEST['unterrichtID']) {
        $schueler = $currentUnterricht->loadSchueler();
      } else if ($_REQUEST['doPrint'] > 0) {
        $schueler = $klasse->getSchueler(false);
      } else {
        if(!$showUnterrichtListe) {
          $schueler = $klasse->getSchueler(true);
        }
      }

      if($currentUnterricht != null) {
        $schueler = $currentUnterricht->getSchueler();
      }




      for($i = 0; $i < sizeof($schueler); $i++) {

      	switch($_REQUEST['doPrint']) {
      		default:

      			$schuelerListe .= "<tr><td>" . ($i+1) . "</td>";

      			if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren')) {
      				$schuelerListe .= "<td><a href=\"index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\"><img src=\"index.php?page=schuelerinfo&mode=getFoto&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\" width=\"70\" border=\"0\"/></a></td>";
      			}

      			////////

      			$schuelerListe .= "<td><p><a href=\"index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\"><b>" . $schueler[$i]->getCompleteSchuelerName() . "</b></a>";

                if($schueler[$i]->isAusgetreten()) $schuelerListe .= " <label class=\"label label-danger\">Ausgetreten zum " . DateFunctions::getNaturalDateFromMySQLDate($schueler[$i]->getAustrittDatumAsMySQLDate()) . "</label> ";



      			$schuelerListe .= " " . (($currentUnterricht != null) ? "(Klasse " . $schueler[$i]->getKlasse() . ")" : "") . "<br />";


      			$schuelerListe .= (($schueler[$i]->getGeschlecht() == 'm') ? ("<i class=\"fa fa-mars\"></i>") : "<i class=\"fa fa-venus\"></i>");

      			$schuelerListe .= " | " . $schueler[$i]->getAlter() . " Jahre (" . $schueler[$i]->getGeburtstagAsNaturalDate() . ")";
      			$schuelerListe .= " | Wohnort: " . $schueler[$i]->getWohnort() . " | Bekenntnis: " . $schueler[$i]->getBekenntnis() . " | Ausbildungsrichtung: " . $schueler[$i]->getAusbildungsrichtung();

      			$na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler[$i]);

      			if($na != null) {
      				// $schuelerListe .= "<br /><label class=\"label label-danger\">Nachteilsausgleich -  " . $na->getArt() . " - " . $na->getArbeitszeitverlaengerung() . " Zeitzuschlag " . (($na->hasNotenschutz()) ? (" (MIT Notenschutz)") : (" (Ohne Notenschutz)")) . " - " . $na->getKommentar() . "</label>";
      			
      			    $schuelerListe .= "<br /><b>Nachteilsausgleich:</b> " . $na->getInfoString() . "";
      			}


      			$schuelerListe .= "</p><p><a href=\"index.php?page=schuelerinfo&mode=schueler&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\" class='btn btn-default btn-block'><i class=\"fa fa-info-circle\"></i> Informationen zum Schüler anzeigen</a></p>";


              if(SchuelerQuarantaene::getCurrentForSchueler($schueler[$i]) != null) $schuelerListe .= "<p>" . SchuelerQuarantaene::getCurrentForSchueler($schueler[$i])->getStatusAsDisabledButton() . "</p>";



              $schuelerListe .= "</td><td>";


      			$schuelerListe .= "<p><a class='btn btn-default btn-block' href=\"index.php?page=MessageCompose&recipient=P:" . $schueler[$i]->getAsvID() . "\"><i class=\"fa fa-paper-plane\"></i><i class=\"fa fa-child\"></i> Elektronische Nachricht an Schüler senden</a></p>";
      			if(sizeof($schueler[$i]->getParentsUsers()) > 0)
      				$schuelerListe .= "<p><a class='btn btn-default btn-block' href=\"index.php?page=MessageCompose&recipient=E:" . $schueler[$i]->getAsvID() . "\"><i class=\"fa fa-paper-plane\"></i><i class=\"fa fa-user-circle-o\"></i> Elektronische Nachricht an Eltern senden</a></p>";
      				else $schuelerListe .= "<p><i>Eltern nicht elektronisch erreichbar</i></p>";


      			$schuelerListe .= "</tr>";
      		break;

          case 'simpleList':

              $schuelerListe .= "<tr><td width=\"10%\">" . ($i+1) . "</td>";
              $schuelerListe .= "<td width=\"10%\">" . $schueler[$i]->getKlasse() . "</td>";

              if ( $schueler[$i]->isAusgetreten() ) {
                $schuelerListe .= "<td width=\"80%\">" . $schueler[$i]->getCompleteSchuelerName()." - Ausgetreten zum " . DateFunctions::getNaturalDateFromMySQLDate($schueler[$i]->getAustrittDatumAsMySQLDate()) ."</td>";
              } else {
                $schuelerListe .= "<td width=\"80%\">" . $schueler[$i]->getCompleteSchuelerName(). "</td>";
              }
              $schuelerListe .= "</tr>";


          break;

      		case 'listWithNA':
      			$schuelerListe .= "<tr><td width=\"10%\">" . ($i+1) . "</td>";
      			$schuelerListe .= "<td width=\"40%\">" . $schueler[$i]->getCompleteSchuelerName() . "</td>";

      			$schuelerListe .= "<td width=\"50%\">";

      			$na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler[$i]);

      			if($na != null) {
      			    $schuelerListe .= "<b>Nachteilsausgleich:</b> " . $na->getInfoString() . "";
      			}

      			$schuelerListe .= "</td>";


      			$schuelerListe .= "</tr>";
      		break;
      	}


      }

      if($currentUnterricht != null) {
        $currentUnterrichtKlassen = [];

        $alleKlassen = $currentUnterricht->getAllKlassen();
        for($i = 0; $i < sizeof($alleKlassen); $i++) $currentUnterrichtKlassen[] = $alleKlassen[$i]->getKlassenName();

        $currentUnterrichtKlassen = implode(", ", $currentUnterrichtKlassen);
      }


      switch($_REQUEST['doPrint']) {
      	default:
      		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schuelerinfo/grade") . "\");");
      		PAGE::kill();
					//exit(0);
      	break;

      	case 'simpleList':
      		eval("\$html = \"" . DB::getTPL()->get("schuelerinfo/print/list") . "\";");

      		$name = '';

      		if($currentUnterricht != null) {
      			$name .= "Klasse " . $currentUnterrichtKlassen;
      		}
      		else {
      			$name .= "Klasse " . $klasse->getKlassenName();
      		}

      		if($showUnterrichtListe != "") {
      			$name .= " (" . $showUnterrichtListe . ")";
      		}

      		$print = new PrintNormalPageA4WithHeader($name . ".pdf");
      		$print->setHTMLContent($html);
      		$print->send();


      	break;

      	case 'listWithNA':
      		eval("\$html = \"" . DB::getTPL()->get("schuelerinfo/print/list_na") . "\";");

      		$name = '';

      		if($currentUnterricht != null) {
      			$name .= "Klasse " . $currentUnterrichtKlassen;
      		}
      		else {
      			$name .= "Klasse " . $klasse->getKlassenName();
      		}

      		if($showUnterrichtListe != "") {
      			$name .= " (" . $showUnterrichtListe . ")";
      		}

      		$print = new PrintNormalPageA4WithHeader($name . ".pdf");
      		$print->setHTMLContent($html);
      		$print->send();
      	break;
      }

    }
    else {
      new errorPage();
    }
  }

  private function klassenuebersicht() {

    // Schüler suchen
    $grades = $this->allowedGradesForDetails;

    $gradeHTML = '';
    for($i = 0; $i < sizeof($grades); $i++) {
      $gradeHTML .= "<tr><td>" . ($i+1) . "</td>";

      $gradeHTML .= "<td><a href=\"index.php?page=schuelerinfo&mode=klasse&klasse=" . $grades[$i]->getKlassenName() . "\"><b>Klasse " . $grades[$i]->getKlassenName() . "</b></a>";

      $kNamen = [];

      $kls = $grades[$i]->getKlassenLeitung();
      for($k = 0; $k < sizeof($kls); $k++) {
        $kNamen[] =  $kls[$k]->getDisplayNameMitAmtsbezeichnung();
      }
      $gradeHTML .= "<br /><small>Klassenleitung: " . implode(" | ", $kNamen) . "<br />Ausbildungsrichtung: " . implode(", ",$grades[$i]->getAusbildungsrichtungen()) . "<br>Anzahl: ".$grades[$i]->getAnzahlSchueler()."</small>";

      if(SchuelerQuarantaene::isActive()) {
        if(SchuelerQuarantaene::hasOneInClass($grades[$i])) {
          $gradeHTML .= "<p><div class='label label-danger'><i class='fa fa-head-side-mask'></i> Schüler aus dieser Klasse in Quarantäne bzw. Isolation</div></p>";
        }
      }

      $gradeHTML .= "</td>";

      $gradeHTML .= "<td>

      <div class='btn-group' role='group'><a hreF=\"index.php?page=klassenlisten&grade=" . $grades[$i]->getKlassenName() . "&createPDF=1&gebdatum=1\" class='btn btn-xs btn-default'><i class=\"fa fa-file-pdf\"></i> Klassenliste als PDF Datei</a>
      <a hreF=\"index.php?page=klassenlisten&grade=" . $grades[$i]->getKlassenName() . "&createXLSXdaten=1\" class='btn btn-xs btn-default '><i class=\"fa fa-file-excel\"></i> Schülerdaten der Klasse als Excel Datei exportieren</a></div>
      <div class='btn-group' role='group'><a href=\"index.php?page=klassenlisten&preSelectGrade=" . $grades[$i]->getKlassenName() . "\" class='btn btn-xs btn-default'><i class=\"fa fa-list\"></i> Benutzerdefinierte Klassenlisten</a>";

      if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren')) {
      	$gradeHTML .= "<a href=\"index.php?page=schuelerinfo&mode=getFotoUebersicht&klasse=" . $grades[$i]->getKlassenName() . "\" class='btn btn-xs btn-default'><i class=\"fa fa-file-pdf\"></i> Fotoübersicht</a>";
      }



      $gradeHTML .= "</div></td>";
    }

    // Wahlunterricht
    $showUnterrichtListe = false;

    // Unterricht

    $unterricht = SchuelerUnterricht::getAllWahlUnterricht();

    $faecherHTML = "";

    $currentUnterricht = null;

    for($i = 0; $i < sizeof($unterricht); $i++) {
      $fach = (($unterricht[$i]->getFach() != null) ? $unterricht[$i]->getFach()->getKurzform() . " (" . $unterricht[$i]->getBezeichnung() . ")" : "n/a");
      $lehrer = (($unterricht[$i]->getLehrer() != null) ? $unterricht[$i]->getLehrer()->getKuerzel() : "n/a");

      $koppel = $unterricht[$i]->getKoppelUnterricht();

      $koppelStatus = [];
      for($k = 0; $k < sizeof($koppel); $k++) {
        $koppelStatus[] = (($koppel[$k]->getFach() != null) ? $koppel[$k]->getFach()->getKurzform() . " (" . $koppel[$k]->getBezeichnung() . ")" : "n/a") . " bei " .  (($koppel[$k]->getLehrer() != null) ? $koppel[$k]->getLehrer()->getKuerzel() : "n/a");
      }



      $highlight = '';

      if($_REQUEST['unterrichtID'] == $unterricht[$i]->getID()) {
        $schueler = $unterricht[$i]->getSchueler();
        $showUnterrichtListe = true;
        $highlight = ' style="background-color:#ffe6e6;"';
        $currentUnterricht = $unterricht[$i];
      }


      $faecherHTML .= "<tr><td$highlight><a href=\"index.php?page=schuelerinfo&mode=klasse&klasse=ANDEREKLASSE&unterrichtID=" . $unterricht[$i]->getID() . "\">" . $fach  . "</a>" . ((sizeof($koppelStatus) > 0) ? ("<br ><small>Koppel mit: " . implode(", ",$koppelStatus) . "</small>") : ("")) . "</td><td$highlight>" . $lehrer . "</td></tr>\r\n";

    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("schuelerinfo/grades") . "\");");
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
  public static function getSettingsDescription()
  {
    return [
        [
            'name' => 'schuelerinfo-nur-eigene-klassen',
            'typ' => 'BOOLEAN',
            'titel' => 'Für Lehrer nur eigene Klassen anzeigen?',
            'text' => 'Ist diese Option aktiv, so sehen Lehrer nur ihre eigenen Klassen. (Aus dem aktuellen Stundenplan ermittelt.) Für Schulleitung ist immer Vollzugriff erlaubt.'
        ],
        [
            'name' => 'schuelerinfo-fotos-aktivieren',
            'typ' => 'BOOLEAN',
            'titel' => 'Fotos für die Schüler aktivieren?',
            'text' => 'Soll es möglich sein für jeden Schüler ein Foto zu hinterlegen? (Beachten Sie bitte, dass Sie dazu eventuell die Einwilligung der SchülerInnen benötigen.'
        ],
        [
            'name' => 'schuelerinfo-upload-hinweis',
            'typ' => 'TEXT',
            'titel' => 'Hinweis, welche Dokumente nicht abgelegt werden dürfen',
            'text' => 'Hier können Sie einen Hinweis angeben, welche Dokumente nicht in der Schülerakte abgelegt werden dürfen.'
        ],
        [
            'name' => 'schuelerinfo-disable-unterricht',
            'typ' => 'BOOLEAN',
            'titel' => 'Unterrichte nicht anzeigen',
            'text' => 'Wenn diese Funktion aktiv ist, dann werden nur ganze Klassen angezeigt, keine Teilgruppen mehr.'
        ],
        [
            'name' => 'schuelerinfo-quarantaene',
            'typ' => 'BOOLEAN',
            'titel' => 'Quarantäne Informationen aktivieren',
            'text' => 'Für diese Funktion werden Quarantänefunktionen aktiviert. (Schüler oder Klassen in Quarantäne. Info für Lehrkräfte dazu.)'
        ]
    ];
  }


  public static function getSiteDisplayName() {
    return 'Schülerinformationssystem';
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
    return 'fa fa-television';
  }

  public static function getAdminMenuGroup() {
    return 'Schulinformationen';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-retweet';
  }

  public static function displayAdministration($selfURL) {
      if($_REQUEST['action'] == "addKalenderAccess") {
          $group = usergroup::getGroupByName("Schuelerinfo_Sehen");
          $group->addUser(intval($_POST['userID']));
          header("Location: $selfURL");
          exit(0);
      }
      
      if($_REQUEST['action'] == "deleteKalenderAccess") {
          $group = usergroup::getGroupByName("Schuelerinfo_Sehen");
          $group->removeUser(intval($_REQUEST['userID']));
          header("Location: $selfURL");
          exit(0);
      }
      
      
      $html .= 'Zugriffsberechtigungen werden in den Einstellungen hinterlegt. Im nebenstehenden Block können weitere Benutzer für den Zugriff auf die kompletten Schülerinfos freigeschaltet werden.';
      
      $box = administrationmodule::getUserListWithAddFunction($selfURL, "schuelerinfozugriff", "addKalenderAccess", "deleteKalenderAccess", "Benutzer mit Zugriff auf alle Schülerinformationen","", "Schuelerinfo_Sehen");
      
      $html = "<div class=\"row\"><div class=\"col-md-9\">$html</div><div class=\"col-md-3\">$box</div></div>";
      
      return $html;
  }

  public static function getAdminGroup() {
    return 'Webportal_Schuelerinfo_Admin';
  }
}


?>
