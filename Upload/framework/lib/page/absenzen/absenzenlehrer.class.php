<?php


class absenzenlehrer extends AbstractPage {

  private $stundenplan = null;
  private $stundenplanActiveKlasse = null;

  public function __construct() {

    $this->needLicense = false;

    parent::__construct(array("Absenzenverwaltung", "Lehreransicht der Absenzenverwaltung"));

    $this->checkLogin();

    $access = false;
    
    if(DB::getSession()->isMember("Webportal_Absenzen_Sekretariat")) $access = true;
    
    if(DB::getSession()->isTeacher()
    		&& (DB::getSettings()->getBoolean("absenzen-lehreransicht-entschuldigungen")
    		|| DB::getSettings()->getBoolean("absenzen-lehreransicht"))) {
    	$access = true;
    }
    
    if(DB::getSession()->isAdmin()) $access = true;
    
    if(!$access) new errorPage("Kein Zugriff.");
    
    $this->stundenplan = stundenplandata::getCurrentStundenplan();

  }

  public function execute() {

    include_once("../framework/lib/data/absenzen/Absenz.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");

    switch($_GET['mode']) {
      default:
        $this->showIndex();
      break;

      case "showGrade":
        $this->showGrade();
      break;

      case "showSchueler":
        $this->showSchueler();
      break;
      
      case "showSchuelerVerspaetungen":
          $this->showSchuelerVerspaetungen();
      break;

      case "showTotal":
        if(DB::getSettings()->getBoolean("absenzen-lehreransicht")) {
          $this->showTotal();
        }
        else {
          new errorPage("Die Absenzenansicht ist leider nicht verfügbar für Lehrer. Wenden Sie sich an Ihren Systembetreuer, um die Ansicht für Lehrer zu aktivieren!");
        }
      break;
      
      case 'markMeldung':
      	$this->markMeldung();
      break;
      
      case 'unMarkMeldung':
      	$this->unmarkMeldung();
      break;

    }

  }

  /**
   * Zeigt eine Ansicht ähnlich der Absenzenansicht im Sekretariat an.
   */
  private function showTotal() {
      // Alle Klassen laden und alle Schüler dazu anzeigen

      if($_GET['currentDate'] != "") {
        if(datefunctions::isNaturalDate($_GET['currentDate'])) {
          $currentDate = $_GET['currentDate'];
        }
        else $currentDate = DateFunctions::getTodayAsNaturalDate();
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();

      $_GET['currentDate'] = $currentDate;

      $currentDateData = explode(".",$currentDate);
      $currentDateAsTime = mktime(10,10,10,$currentDateData[1],$currentDateData[0],$currentDateData[2]);

      $dayNames = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");
      $dayName = $dayNames[date("N",$currentDateAsTime)-1];

      if($_GET['changeDate'] > 0) {
        if(isset($_POST['dayBack']) && $_POST['dayBack'] == 1) {
          $currentDateAsTime -= 24*60*60;
          if(date("N", $currentDateAsTime) == 7) $currentDateAsTime -= 2*24*60*60;
        }

        else if(isset($_POST['dayForward']) && $_POST['dayForward'] == 1) {
          $currentDateAsTime += 24*60*60;
          if(date("N", $currentDateAsTime) == 6) $currentDateAsTime += 2*24*60*60;
        }

        else if(isset($_POST['toToday']) && $_POST['toToday'] == 1) {
          $currentDateAsTime = time();
        }
        else {
          $newDate = substr($_POST['dayDate'], strpos($_POST['dayDate'],", ")+2);
          $newDateData = explode(".",$newDate);
          $currentDateAsTime = mktime(10,10,10,$newDateData[1],$newDateData[0],$newDateData[2]);
        }

        header("Location: index.php?page=absenzenlehrer&mode=showTotal&currentDate=" . date("d.m.Y",$currentDateAsTime) . "&activeKlasse=" . $_GET['activeKlasse']);
        exit(0);
      }

      $klassen = klasse::getAllKlassen();
      for($i = 0; $i < sizeof($klassen); $i++) {
        $klassen[$i]->getKlassenleitung();
      }

      
      $meldungenSQL = DB::getDB()->query("SELECT * FROM absenzen_meldung JOIN users ON users.userID=absenzen_meldung.meldungUserID WHERE meldungDatum='" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "'");
      
      $meldungen = array();
      while($m = DB::getDB()->fetch_array($meldungenSQL)) {
      	$meldungen[$m['meldungKlasse']] = $m;
      }

      $klassenListeHTML = "";
      $activeKlasse = null;
      for($i = 0; $i < sizeof($klassen); $i++) {
        $kl = array();

        for($k = 0; $k < sizeof($klassen[$i]->getKlassenleitung()); $k++) {
          $kl[] = $klassen[$i]->getKlassenleitung()[$k]->getName() . ", " . substr($klassen[$i]->getKlassenleitung()[$k]->getRufname(),0,1) . "." ;
        }

        if(sizeof($kl) > 0) $kl = implode("; ",$kl);
        else $kl = "NA";

        if($_GET['activeKlasse'] == $klassen[$i]->getKlassenName()) {
          $activeKlasse = $klassen[$i];
        }

        $klassenListeHTML .= "<tr><td>" . (($klassen[$i]->getKlassenName() == $_GET['activeKlasse']) ? ("<u>") : ("")) . "<a href=\"index.php?page=absenzenlehrer&mode=showTotal&activeKlasse=" . $klassen[$i]->getKlassenName() . "&currentDate=" . $currentDate . "\" style=\"display:block\">" . $klassen[$i]->getKlassenName() . " <small>($kl)</small></a>" . (($klassen[$i]->getKlassenName() == $_GET['activeKlasse']) ? ("</u>") : (""));


        $klassenListeHTML .= "</td><td>";

        if(DB::getSettings()->getValue("absenzen-lehrer-meldungaktivieren") > 0) {
        	if(is_array($meldungen[$klassen[$i]->getKlassenName()])) {
        		$klassenListeHTML .= "<a href=\"index.php?page=absenzenlehrer&activeKlasse={$klassen[$i]->getKlassenName()}&currentDate={$currentDate}&mode=unMarkMeldung&meldungKlasse={$klassen[$i]->getKlassenName()}\" data-toggle=\"tooltip\" title=\"Gemeldet. Bearbeitet durch " . $meldungen[$klassen[$i]->getKlassenName()]['userName'] . " am " . date("d.m.Y H:i",$meldungen[$klassen[$i]->getKlassenName()]['meldungTime']) ."\"><i class=\"fa fa-check\"></i></a>";
        	}
        	else {
        		$klassenListeHTML .= "<a href=\"index.php?page=absenzenlehrer&activeKlasse={$klassen[$i]->getKlassenName()}&currentDate={$currentDate}&mode=markMeldung&meldungKlasse={$klassen[$i]->getKlassenName()}\" data-toggle=\"tooltip\" title=\"Noch nicht gemeldet. Klicken, um zu bestätigen.\"><font color=\"red\"><i class=\"fa fa-ban\"></i></font></a>";
        	}        	
        }
        else $klassenListeHTML .= "&nbsp;";

        $klassenListeHTML .= "</td></tr>";

      }




      if($activeKlasse != null) {
        if($this->stundenplan != null)
          $this->stundenplanActiveKlasse = $this->stundenplan->getPlan(array("grade",$activeKlasse->getKlassenName()));

        $schuelerListeHTML = "";
        $dialogHTML = "";
        $viewKlasse = "(Klasse " . $activeKlasse->getKlassenName() . ")";
        for($i = 0; $i < sizeof($activeKlasse->getSchueler()); $i++) {

        	if(!$activeKlasse->getSchueler()[$i]->isAusgetreten()) {
        		$nummer++;
        		$nummerShow = $nummer;
        	}
        	else $nummerShow = '-';

        	$schuelerListeHTML .= "<tr><td>" . ($nummerShow) . "<td>";

          if($activeKlasse->getSchueler()[$i]->isAusgetreten()) $schuelerListeHTML .= "<small>";



          $schuelerListeHTML .= $activeKlasse->getSchueler()[$i]->getCompleteSchuelerName();

          if($activeKlasse->getSchueler()[$i]->isAusgetreten()) $schuelerListeHTML .= "</small>" . " <span class=\"label label-info\">Ausgetreten zum " . datefunctions::getNaturalDateFromMySQLDate($activeKlasse->getSchueler()[$i]->getAustrittDatumAsMySQLDate()) . "</span>";

          if(AbsenzSchuelerInfo::hasAttestpflicht($activeKlasse->getSchueler()[$i], DateFunctions::getMySQLDateFromNaturalDate($currentDate))) {
            $schuelerListeHTML .= " <span class=\"label label-danger\">Attestpflicht</span>";
          }

          $schuelerListeHTML .= "</a>";


          $schuelerListeHTML .= "</td></tr>";

        }

        $selectSize = sizeof($activeKlasse->getSchueler());

      }
      else {
        $viewKlasse = "";
        $selectSize = 1;
        $schuelerListeHTML = "<i>Keine Klasse ausgewählt</i>";
        $dialogHTML = "";
      }



      $hasOffene = false;
      $hasToPrint = array();

      // Absenzen des Tages laden
      $absenzen = Absenz::getAbsenzenForDate(DateFunctions::getMySQLDateFromNaturalDate($currentDate), (($activeKlasse != null) ? $activeKlasse->getKlassenName() : ""));

      $krankmeldungenHTML = "";
      for($i = 0; $i < sizeof($absenzen); $i++) {
        $offen = "edit";

        $krankmeldungenHTML .= "<tr><td>" . $absenzen[$i]->getSchueler()->getKlasse() . "</td>";
        $krankmeldungenHTML .= "<td>" . $absenzen[$i]->getSchueler()->getCompleteSchuelerName() . "";

        if(!$absenzen[$i]->isEntschuldigt()) {
          $krankmeldungenHTML .= " <span class=\"label label-danger\">Ungeklärt</span> ";
        }

        if($absenzen[$i]->isBefreiung()) {
          $krankmeldungenHTML .= " <span class=\"label label-info\">Befreiung</span> ";
        }

        if($absenzen[$i]->getKommentar() != "") {
          $krankmeldungenHTML .= " <a href=\"#\" data-toggle=\"tooltip\" title=\"" . @htmlspecialchars(($absenzen[$i]->getKommentar())) . "\"><i class=\"fa fa-sticky-note-o\"></i></a> ";
        }

        if($absenzen[$i]->kommtSpaeter()) {
          $krankmeldungenHTML .= " <span class=\"label label-danger\"><i class=\"fa fa-clock-o\"></i> Kommt später</span>";
        }

        if($absenzen[$i]->isBeurlaubung()) {
          $krankmeldungenHTML .= " <span class=\"label label-info\">Beurlaubung</span>";
          if($absenzen[$i]->getBeurlaubung()->isInternAbwesend()) {
            $krankmeldungenHTML .= " <span class=\"label label-info\">Intern abwesend</span>";
          }

        }

        if($absenzen[$i]->isSchriftlichEntschuldigt()) {
          $krankmeldungenHTML .= " <small class=\"label label-success\"><i class=\"fa fas fa-pencil-alt\"></i><i class=\"fa fa-check\"></i></small>";
        }
        else {
          $krankmeldungenHTML .= " <small class=\"label label-warning\"><i class=\"fa fas fa-pencil-alt\"></i><i class=\"fa fa-ban\"></i></small>";

        }

        $krankmeldungenHTML .= "</td>";

        $stunden = $absenzen[$i]->getStundenAsArray();

        if($absenzen[$i]->getDateAsSQLDate() != DateFunctions::getMySQLDateFromNaturalDate($currentDate)) {
          $stunden = array();
          for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
            $stunden[] = $s;
          }
        }

        for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
          $krankmeldungenHTML .= "<td>" . ((in_array($s,$stunden)) ? "X" : "&nbsp;") . "</td>";
        }

        if($absenzen[$i]->isMehrtaegig()) {
          $krankmeldungenHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</td>";
        }


        $krankmeldungenHTML .= "</tr>";
      }

      $tabellenStunden = "";

      $sanizimmerHTML = "";


      for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
        $tabellenStunden .= "<th>$s</th>";
      }

      // Verspätungen

      $verspaetungHTML = "";

      $verspaetungen = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen LEFT JOIN schueler ON verspaetungSchuelerAsvID=schuelerAsvID WHERE verspaetungDate='" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "'" . (($activeKlasse != null) ? (" AND schuelerKlasse LIKE '" . $activeKlasse->getKlassenName() . "'") : ("")));

      while($v = DB::getDB()->fetch_array($verspaetungen)) {
        $verspaetungHTML .= "<tr><td>" . $v['schuelerKlasse'] . "</td><td>" . $v['schuelerName'] . ", " . $v['schuelerRufname'] . "</td>";
        $verspaetungHTML .= "<td>" . $v['verspaetungMinuten'] . " zur " . $v['verspaetungStunde'] . ". Stunde</td>";
        $verspaetungHTML .= "<td>";

        if($v['verspaetungKommentar'] != "") {
          $verspaetungHTML .= "<a href=\"#\" data-toggle=\"tooltip\" title=\"" . $v['verspaetungKommentar'] . "\"><i class=\"fa fa-sticky-note\"></i></a> ";
        }

        $verspaetungHTML .= "</td></tr>";
      }

      $currentStunde = stundenplan::getCurrentStunde();

      if($currentStunde == 0) $currentStunde = DB::getSettings()->getValue("stundenplan-anzahlstunden");


      eval("echo(\"" . DB::getTPL()->get("absenzen/lehrertotal/index") . "\");");
    }
    
    
    private function markMeldung() {
    	
    	
    	
    	if($_GET['currentDate'] != "") {
    		if(DateFunctions::isNaturalDate($_GET['currentDate'])) {
    			$currentDate = $_GET['currentDate'];
    		}
    		else $currentDate = DateFunctions::getTodayAsNaturalDate();
    	}
    	else $currentDate = DateFunctions::getTodayAsNaturalDate();
    	
    	DB::getDB()->query("INSERT INTO absenzen_meldung (meldungDatum, meldungKlasse, meldungUserID, meldungTime)
        values('" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "',
        '" . DB::getDB()->escapeString($_GET['meldungKlasse']) . "',
        '" . DB::getUserID() . "',
        UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE meldungUserID='" . DB::getUserID() . "'
    ");
    	
    	header("Location: index.php?page=absenzenlehrer&mode=showTotal&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    	exit(0);
    }
    
    private function unmarkMeldung() {
    	
    	
    	if($_GET['currentDate'] != "") {
    		if(DateFunctions::isNaturalDate($_GET['currentDate'])) {
    			$currentDate = $_GET['currentDate'];
    		}
    		else $currentDate = DateFunctions::getTodayAsNaturalDate();
    	}
    	else $currentDate = DateFunctions::getTodayAsNaturalDate();
    	
    	DB::getDB()->query("DELETE FROM absenzen_meldung WHERE meldungDatum='" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "' AND meldungKlasse='" . DB::getDB()->escapeString($_GET['meldungKlasse']) . "'");
    	
    	header("Location: index.php?page=absenzenlehrer&mode=showTotal&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    	exit(0);
    }
    
    
    private function showSchuelerVerspaetungen() {
        $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);
        
        if($schueler == null) new errorPage("Der angegebene Schüler ist nicht verfügbar!");
        
        if(DB::getSession()->isAdmin() || DB::getSession()->isMember('Webportal_Absenzen_Sekretariat') || DB::getSession()->isMember(self::getAdminGroup()) || (DB::getSession()->isTeacher() && $schueler->isKlassenleitung(DB::getSession()->getTeacherObject()))) {
            
            $verspaetungen = AbsenzVerspaetung::getAllForSchueler($schueler);
            
            
            $vHTML = "";
            
            if(sizeof($verspaetungen) == 0) $vHTML = "<tr><td colspan=\"6\"><i>Keine Verspätungen vorhanden</i></td></tr>";
            
            
            for($i = 0; $i < sizeof($verspaetungen); $i++) {
                
                if($_REQUEST['action'] == 'doBearbeitung') {
                    
                    
                    if($_REQUEST['v_' . $verspaetungen[$i]->getID()] > 0) {
                        
                        if($verspaetungen[$i]->isBearbeitet() && $_REQUEST['deleteBearbeitung'] > 0) {
                            $verspaetungen[$i]->setIsNotBearbeitet();
                        }
                        else {
                            $verspaetungen[$i]->setIsBearbeitet();
                            $verspaetungen[$i]->setIsBearbeitetKommentar($_REQUEST['kommentar']);
                        }
                    }
                }
                
                $vHTML .= "<tr>";
                
                $vHTML .= "<td><input type=\"checkbox\" name=\"v_" . $verspaetungen[$i]->getID() . "\" value=\"1\" class=\"icheck checkAllVerspaetungen\"></td>";
                $vHTML .= "<td>" . $verspaetungen[$i]->getDateAsNaturalDate() . "</td>";
                $vHTML .= "<td>" . $verspaetungen[$i]->getStunde() . "</td>";
                $vHTML .= "<td>" . $verspaetungen[$i]->getMinuten() . "</td>";
                
                $vHTML .= "<td>" . $verspaetungen[$i]->getKommentar() . "</td>";
                
                $vHTML .= "<td>";
                
                if($verspaetungen[$i]->isBearbeitet()) {
                    $vHTML .= "<i class=\"fa fa-check\"></i> Ja<br />" . $verspaetungen[$i]->getBearbeitetKommentar();
                }
                else {
                    $vHTML .= "<i class=\"fa fa-ban\"></i>";
                }
                
                $vHTML .= "</td>";
                $vHTML .= "</tr>";
                
            }
            
            
            if($_REQUEST['action'] == 'doBearbeitung') {
                header("Location: index.php?page=absenzenlehrer&mode=showSchuelerVerspaetungen&schuelerAsvID={$schueler->getASVID()}");
                exit(0);
            }
            
            eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/lehrer/schueler_verspaetungen") . "\");");
            exit(0);
            
        }
        else {
            new errorPage("Leider haben Sie auf diesen Schüler keinen Zugriff!");
            exit(0);
        }
        
        
    }
    

  private function showSchueler() {
    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

    if($schueler == null) new errorPage("Der angegebene Schüler ist nicht verfügbar!");

    if(DB::getSession()->isAdmin() || DB::getSession()->isMember('Webportal_Absenzen_Sekretariat') || DB::getSession()->isMember(self::getAdminGroup()) || (DB::getSession()->isTeacher() && $schueler->isKlassenleitung(DB::getSession()->getTeacherObject()))) {

      $absenzen = Absenz::getAbsenzenForSchueler($schueler);


      $absentenHTML = "";

      for($i = 0; $i < sizeof($absenzen); $i++) {

        if($_GET['action'] == "markAsEntschuldigt" && $_GET['absenzID'] == $absenzen[$i]->getID()) {
          $absenzen[$i]->setSchriftlichEntschuldigt();
        }

        if($_GET['action'] == "markAsUnEntschuldigt" && $_GET['absenzID'] == $absenzen[$i]->getID()) {
          $absenzen[$i]->setSchriftlichUnEntschuldigt();
        }

        if(!$absenzen[$i]->isSchriftlichEntschuldigt() || ($absenzen[$i]->isSchriftlichEntschuldigt() && $_GET['showAll'] == 1)) {



          $absentenHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate()) . "</td>";
          if($absenzen[$i]->getEnddatumAsSQLDate() != $absenzen[$i]->getDateAsSQLDate()) $absentenHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</td>";
          else $absentenHTML .= "<td>&nbsp;</td>";

          if($absenzen[$i]->isBeurlaubung()) $absentenHTML .= "<td><i class=\"fa fa-check\"></i></td>";
          else $absentenHTML .= "<td>&nbsp;</td>";

          if($absenzen[$i]->isBefreiung()) $absentenHTML .= "<td><i class=\"fa fa-check\"></i></td>";
          else $absentenHTML .= "<td>&nbsp;</td>";

          $absentenHTML .= "<td>" . nl2br($absenzen[$i]->getKommentar()) . "</td>";

          if(!$absenzen[$i]->isSchriftlichEntschuldigt()) {
          	if($absenzen[$i]->isSchriftlichEntschuldigbar()) {
          		$absentenHTML .= "<td><a href=\"index.php?page=absenzenlehrer&mode=showSchueler&schuelerAsvID=" . $schueler->getAsvID() . "&showAll=" . $_GET['showAll'] . "&action=markAsEntschuldigt&absenzID=" . $absenzen[$i]->getID() . "\"><i class=\"fa fa-check\"></i> Schriftliche Entschuldigung bestätigen</a>";
          	}
          	else {
          		$absentenHTML .= "<td>";
          	}
            
            
            if(AbsenzSchuelerInfo::hasAttestpflicht($absenzen[$i]->getSchueler(), $absenzen[$i]->getDateAsSQLDate())) {
              $absentenHTML .= "<br /><span class=\"label label-danger\">ATTESTPFLICHT</span>";
            }
            
            if(DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') > 0) {
            	$absentenHTML .= "<br /><span class=\"label label-info\">Entschuldigbar bis " . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getSchriftlichEntschuldigbarDate()) . "</span>";
            }
            	
            
            if(!$absenzen[$i]->isSchriftlichEntschuldigbar()) {
            	$absentenHTML .= "<br /><span class=\"label label-danger\">Absenz kann nicht mehr schriftlich entschuldigt werden.</span>";
            }

            // Termine?
            
            $lnws = $absenzen[$i]->getLeistungsnachweiseDuringAbsenzPeriod();
            
	            $hasTermine = false;
	            
	            
	            for($l = 0; $l < sizeof($lnws); $l++) {
	            		$hasTermine = true;
	            		 
	            		$termineHTML .= $lnws[$l]->getArtLangtext() . " - " . $lnws[$l]->getFach(). " (" . $lnws[$l]->getLehrer() . ")<br />";
	            }
	            
	            if($hasTermine) {
	            	$absentenHTML .= "<br /><strong>Angekündigte Leistungsnachweise an diesem Tag:</strong><br />";
	            	$absentenHTML .= $termineHTML;
	            	$absentenHTML .= "<span class=\"label label-danger\">Attest?</span>";
	            }
	          

            $absentenHTML .= "</td>";
          }
          else {
            $absentenHTML .= "<td><a href=\"index.php?page=absenzenlehrer&mode=showSchueler&schuelerAsvID=" . $schueler->getAsvID() . "&showAll=" . $_GET['showAll'] . "&action=markAsUnEntschuldigt&absenzID=" . $absenzen[$i]->getID() . "\"><i class=\"fa fa-trash\"></i> Schriftliche Entschuldigung entfernen</a></td>";
          }
        }

      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/lehrer/schueler") . "\");");
      exit(0);

    }
    else {
      new errorPage("Leider haben Sie auf diesen Schüler keinen Zugriff!");
      exit(0);
    }


  }

  private function showGrade() {
    $klasse = klasse::getByName($_REQUEST['grade']);

    if($klasse  == null) {
      new errorPage("Die angegebene Klasse ist leider nicht gültig!");
      exit(0);
    }


    if(!DB::getSession()->isAdmin() && (DB::getSession()->isTeacher() && !$klasse->isKlassenLeitung(DB::getSession()->getTeacherObject())) && !DB::getSettings()->getBoolean('absenzen-lehrer-allelehreralleklassenentschuldigungenueberpruefen') && !DB::getSession()->isMember('Webportal_Absenzen_Sekretariat') && !DB::getSession()->isMember(self::getAdminGroup())) {
      new errorPage("Die angegebene Klasse ist leider nicht gültig! (Keine Klassenleitung!)");
      exit(0);
    }

    $schuelerHTML = "";

    $absenzenDerKlasse = Absenz::getAbsenzenForKlasse($klasse->getKlassenName());

    $schueler = $klasse->getSchueler();

    for($i = 0; $i < sizeof($schueler); $i++) {
      $gesamt = 0;
      $entschuldigt = 0;
      for($a = 0; $a < sizeof($absenzenDerKlasse); $a++) {
        if($absenzenDerKlasse[$a]->getSchueler()->getAsvID() == $schueler[$i]->getAsvID()) {
          $gesamt++;
          if($absenzenDerKlasse[$a]->isSchriftlichEntschuldigt()) $entschuldigt++;
        }
      }

      $offen = $gesamt - $entschuldigt;
      
      
      $schuelerHTML .= "<tr><td><b><i class=\"fa fa-child\"></i> ";
      $schuelerHTML .= $schueler[$i]->getCompleteSchuelerName() . "</b></a>";


      $schuelerHTML .= "<a href=\"index.php?page=absenzenlehrer&mode=showSchueler&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\" class=\"btn btn-default btn-sm btn-block\"><i class=\"fa fa-check\"></i> Absenzen verwalten</a>";
      
      $schuelerHTML .= "<a href=\"index.php?page=absenzenlehrer&mode=showSchuelerVerspaetungen&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\" class=\"btn btn-default btn-sm btn-block\"><i class=\"fa fa-clock-o\"></i> Verspätungen verwalten</a>";
      
      
      
      $schuelerHTML .= "<a href=\"index.php?page=absenzenberichte&mode=schuelerbericht&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\" class=\"btn btn-default btn-sm btn-block\"><i class=\"fa fa-print\"></i> Schülerbericht</a>";


      $schuelerHTML .= "</td>";
      
      
      $verspaetungen = AbsenzVerspaetung::getAllForSchueler($schueler[$i]);
      
      $nichtVerarbeitet = 0;
      
      for($v = 0; $v < sizeof($verspaetungen); $v++) {
          if(!$verspaetungen[$v]->isBearbeitet()) {
              $nichtVerarbeitet++;
          }
      }
      
      $schuelerHTML .= "<td>
      
      <table class=\"table table-striped\">
        <tr>
            <td>Anzahl der Absenzen:</td>
            <td>$gesamt</td>
        </tr>
        <tr>
            <td>Entschuldigt:</td>
            <td>$entschuldigt</td>
        </tr>
        <tr>
            <td>Noch nicht entschuldigt:</td>
            <td>$offen</td>
        </tr>
        <tr>
            <td>Anzahl der Verspätungen:</td>
            <td>" . sizeof($verspaetungen) . "</td>
        </tr>

        <tr>
            <td>Nicht bearbeitete Verspätungen:</td>
            <td>" . $nichtVerarbeitet . "</td>
        </tr>

      </table>";
         
	  
	  $schuelerHTML .= "</td><td>";
	  
	  
	  if($offen > 0) $schuelerHTML .= " <div class=\"label label-info\">Offene Entschuldigungen</div><br />";
	  
	  
	  if(DB::getSettings()->getValue("absenzen-lehrer-schwelle-warnung-verspaetung") > 0 && $nichtVerarbeitet >= DB::getSettings()->getValue("absenzen-lehrer-schwelle-warnung-verspaetung")) {
	      $schuelerHTML .= " <div class=\"label label-danger\">Verspätungsschwelle überschritten!</div><br />";
	  }
	  
	  
	  $schuelerHTML .= "</td></tr>";
      
      
    }



    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/lehrer/index") . "\");");
    exit(0);
  }


  private function showIndex() {
   
	$klassen = [];
  	
    
    if(DB::getSession()->isTeacher()) $klassen = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();
    
    if(DB::getSession()->isAdmin() || DB::getSession()->isMember('Webportal_Absenzen_Sekretariat')) $klassen = klasse::getAllKlassen();
        
    if(DB::getSession()->isMember(self::getAdminGroup())) $klassen = klasse::getAllKlassen();
    
    if(DB::getSettings()->getBoolean('absenzen-lehrer-allelehreralleklassenentschuldigungenueberpruefen')) $klassen = klasse::getAllKlassen();

    if(sizeof($klassen) == 0) {
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/lehrer/keine_klasse") . "\");");
      exit(0);
    }

    if(sizeof($klassen) == 1) {
      header("Location: index.php?page=absenzenlehrer&mode=showGrade&grade=" . $klassen[0]->getKlassenName());
      exit(0);
    }

    $klassenHTML = "";

    for($i = 0; $i < sizeof($klassen); $i++) {
      $klassenHTML .= "<li><a href=\"index.php?page=absenzenlehrer&mode=showGrade&grade=" . $klassen[$i]->getKlassenName() . "\">Klasse " . $klassen[$i]->getKlassenName() . "</a></li>";
    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/lehrer/multi_klasse") . "\");");
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
    return array(
        array(
          'name' => "absenzen-lehreransicht",
          'typ' => BOOLEAN,
          'titel' => "Lehreransicht auf die Absenzenverwaltung aktivieren?",
          'text' => "Mit dieser Ansicht können, die Lehrer auf die Absenzenverwaltung zugreifen. (Ohne Schreibrechte)"
        ),
    	array(
    		'name' => "absenzen-lehreransicht-entschuldigungen",
    		'typ' => BOOLEAN,
    		'titel' => "Überprüfen der Entschuldigungen für Lehrer aktivieren?",
    		'text' => "Auf die Überprüfung der Entschuldigungen haben die Personen Zugriff, die auch auf das Sekretariatsmodul Zugriff haben. Für Lehrer (Klassenleiter) kann die Ansicht hier freigeschaltet werden."
    	),
    		
    	array(
    		'name' => "absenzen-lehrer-meldungaktivieren",
    		'typ' => BOOLEAN,
    		'titel' => "Meldung der Lehrer aktivieren?",
    		'text' => "Ist diese Option aktiv, können Lehrer in der Gesamtansicht der Absenzen für Lehrer einen Haken setzen, wenn die Absenzen vollständig und richtig sind. (z.B. direkt aus dem Unterricht heraus)"
   		),
        array(
            'name' => "absenzen-lehrer-allelehreralleklassenentschuldigungenueberpruefen",
            'typ' => BOOLEAN,
            'titel' => "Alle Lehrer für alle Klassenleiteransichten freischalten?",
            'text' => "Ist diese Option aktiv, können alle Lehrer die Entschuldigungen und die Klassenleiteransicht für alle Klassen einsehen."
        ),
        array(
            'name' => "absenzen-lehrer-schwelle-warnung-verspaetung",
            'typ' => "NUMMER",
            'titel' => "Ab welcher Anzahl von Verspätungen soll eine Warnung angezeigt werden?",
            'text' => "Null für keine Warnung."
        ),
        array(
            'name' => "absenzen-lehrer-schwelle-warnung-verspaetung-info-klassenleitung",
            'typ' => "BOOLEAN",
            'titel' => "Beim Überschreiten der Anzahl der Verspätungen die Klassenleitung automatisch informieren?",
            'text' => ""
        ),
        array(
            'name' => "absenzen-lehrer-schwelle-warnung-verspaetung-info-schulleitung",
            'typ' => "BOOLEAN",
            'titel' => "Beim Überschreiten der Anzahl der Verspätungen die Schulleitung automatisch informieren?",
            'text' => ""
        )
    );
  }


  public static function getSiteDisplayName() {
    return 'Lehreransicht (Absenzen)';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return [];
  }

  public static function siteIsAlwaysActive() {
    return false;
  }
  
  public static function getAdminGroup() {
  	return 'Webportal_Absenzen_Lehrer_Admin';
  }
  
  public static function dependsPage() {
  	return ['absenzensekretariat', 'absenzenberichte','absenzenstatistik'];
  }
  
  public static function userHasAccess($user) {
  	if($user->isAdmin()) return true;
  	
  	if($user->isMember(self::getAdminGroup())) return true;
  	  	
  	if($user->isMember('Webportal_Absenzen_Sekretariat')) {
  		return true;
  	}
  	
  	if($user->isTeacher() && (DB::getSettings()->getBoolean("absenzen-lehreransicht") || DB::getSettings()->getBoolean("absenzen-lehreransicht-entschuldigungen"))) {
  		return true;
  	}
  	
  	return false;
  }
  
  public static function hasAdmin() {
  	return true;
  }
  
  public static function getAdminMenuGroup() {
  	return 'Absenzenverwaltung';
  }
  
  public static function getAdminMenuGroupIcon() {
  	return 'fa fas fa-procedures';
  }
  

}


?>
