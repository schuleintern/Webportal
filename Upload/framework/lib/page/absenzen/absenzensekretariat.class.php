<?php


class absenzensekretariat extends AbstractPage {

  private $stundenplan = null;
  private $stundenplanActiveKlasse = null;
  private $merkerActive = false;

  /**
   * Termine für die aktive Klasse
   * @var string
   */
  private $termineHTML = "";

  public function __construct() {

    $this->needLicense = false;

    parent::__construct(array("Absenzenverwaltung", "Hauptansicht - Aktuelle Absenzen"));

    $this->checkLogin();

    if(!DB::getSession()->isAdmin()) $this->checkAccessWithGroup("Webportal_Absenzen_Sekretariat");

    $this->stundenplan = stundenplandata::getCurrentStundenplan();

    if(DB::getSettings()->getValue("absenzen-merkeraktivieren") > 0) $this->merkerActive = true;

  }

  public function execute() {

    include_once("../framework/lib/data/absenzen/Absenz.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");
    include_once("../framework/lib/system/DateFunctions.class.php");

    switch($_GET['mode']) {
      default:
        $this->showIndex();
      break;
      
      case 'autocompletenameajax':
      	$this->ajaxCompleteUserName();
      break;

      case 'addAbsenzViaDialog':
        $this->addAbsenzDialog();
      break;

      case 'editComment':
        $this->editComment();
      break;

      case 'ungeklaert':
          $this->ungeklaert();
      break;

      case 'editAbsenz':
        $this->editAbsenz();
      break;

      case 'markMeldung':
        $this->markMeldung();
      break;


      case 'unMarkMeldung':
        $this->unmarkMeldung();
      break;

      case 'addBefreiung':
        $this->addBefreiung();
      break;

      case 'printBefreiung':
        $this->printBefreiung();
      break;

      case 'addSanizimmer':
        $this->addSanizimmer();
      break;

      case 'sanizimmer':
        $this->endSanizimmer();
      break;

      case 'addAttestpflicht':
        $this->addAttestpflicht();
      break;

      case 'deleteAttestpflicht':
        $this->deleteAttestpflicht();
      break;

      case 'processKrankmeldungen':
        $this->processKrankmeldungen();
      break;
      
      case 'processBeurlaubungen':
          $this->processBeurlaubungen();
      break;

      case "addBeurlaubungSingleDay":
        $this->addBeurlaubung();
      break;

      case "addVerspaetung":
        $this->addVerspaetung();
      break;

      case "deleteVerspaetung":
        $this->deleteVerspaetung();
      break;

      case 'printBeurlaubung':
        $this->printBeurlaubung();
      break;

      case "search":
        $this->searchSchueler();
      break;
      
      case "selectSchueler":
        $this->selectSchueler();
      break;

      case 'editAbsenzen':
        $this->editAbsenzen();
      break;

      case 'meldungStat':
        $this->meldungStat();
      break;

      case "sammelbeurlaubung":
        $this->sammelbeurlaubung();
      break;

      case "addMerker":
        $this->addMerker();
      break;

      case "deleteMerker":
        $this->deleteMerker();
      break;
      
      case 'periodischeBeurlaubung':
      	$this->periodischeBeurlaubung();
      break;
      
      case 'klassenanwesenheit':
      	$this->klassenanwesenheit();
      break;
    }

  }
  
  private function klassenanwesenheit() {
  	
  	if($_GET['action'] == 'save') {
  		// Debugger::debugObject($_REQUEST,1);
  	}
  	
  	$klassen = klasse::getAllKlassen();
  	
  	DB::getSettings()->setValue('absenzen-has-fpa', false);
  	
  	$htmlKlassen = "";
  	for($i = 0; $i < sizeof($klassen); $i++) {
 
  		
  		if($_GET['action'] == 'save') {
  			
  			$hasFPA = false;
  			
  			$tageSave = [];
  			$tage = $_POST["tage_" . $klassen[$i]->getKlassenName()];
  			$tage = explode("\n",str_replace("\r","",$tage));
  			
  			for($t = 0; $t <= sizeof($tage); $t++) {
  				if(DateFunctions::isNaturalDate($tage[$t])) {
  					$tageSave[] = $tage[$t];
	  				$hasFPA = true;
  				}
  			}
  			
  			if($hasFPA) DB::getSettings()->setValue('absenzen-has-fpa', true);
  			
  			DB::getSettings()->setValue('klassenabwesenheit_' . $klassen[$i]->getKlassenName(), implode("\n",$tageSave));
  		}
  		
  		
  		
  		$htmlKlassen .= "<h3>Klasse " . $klassen[$i]->getKlassenName() . "</h3><textarea class=\"form-control\" rows=\"20\" name=\"tage_" . $klassen[$i]->getKlassenName() . "\">" . DB::getSettings()->getValue('klassenabwesenheit_' . $klassen[$i]->getKlassenName()) . "</textarea><hr>";
  	}
  	
  	eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/klassenanwesenheit") . "\");");
  	PAGE::kill(true);
			//exit(0);
  }
  
  private function ajaxCompleteUserName() {
      $term = DB::getDB()->escapeString($_REQUEST['term']);
      header("Content-type: text/plain");
      
      
      echo("[\r\n");
      
      
      if(strlen($term) >= 2) {
          $users = DB::getDB()->query("SELECT schuelerAsvID, schuelerName, schuelerRufname, schuelerVornamen, schuelerKlasse FROM schueler WHERE 

                schuelerName LIKE '%" . $term . "%' OR schuelerRufname LIKE '%" . $term . "%' OR schuelerVornamen LIKE '%" . $term . "%'");
          
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
  }

  private function deleteMerker() {
    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    DB::getDB()->query("DELETE FROM absenzen_merker WHERE merkerID='" . intval($_GET['merkerID']) . "' LIMIT 1");

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit();
  }

  private function addMerker() {
    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

    if($schueler == null) {
      new errorPage("Der angegebene Schüler ist nicht vorhanden!");
      exit(0);
    }

    DB::getDB()->query("INSERT INTO absenzen_merker (merkerSchuelerAsvID, merkerDate, merkerText) values('" . $schueler->getAsvID() . "','" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "','" . DB::getDB()->escapeString($_POST['merkerText']) . " (" . DB::getSession()->getData("userName") . ")')");

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit();

  }
  
  private function periodischeBeurlaubung() {
  	$schueler = array();
  	
  	if(isset($_REQUEST['schueler']) && $_REQUEST['schueler'] != "") {
  		$schueler = $_REQUEST['schueler'];	// MutliselectField
  	}
  	
  	
  	switch($_REQUEST['action']) {
  		case "save":
  			$zeitraum = $_POST['bu_zeit'];
  			$zeitraum = explode(" bis ",$zeitraum);
  			
  			if(sizeof($zeitraum) != 2) {
  				new errorPage("Ungültiger Zeitraum!");
  			}
  			
  			if(!DateFunctions::isNaturalDate($zeitraum[0]) || !DateFunctions::isNaturalDate($zeitraum[1]) || !DateFunctions::isNaturalDateAfterAnother($zeitraum[1], $zeitraum[0])) {
  				new errorPage("Ungültiger Zeitraum!");
  			}
  			
  			$start = DateFunctions::getMySQLDateFromNaturalDate($zeitraum[0]);
  			$ende = DateFunctions::getMySQLDateFromNaturalDate($zeitraum[1]);
  			
  			
  			
  			$stunden = array();
  			
  			for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
  				if($_POST['stunde' . $i] > 0) {
  					$stunden[] = $i;
  				}
  			}
  			
  			if(sizeof($_POST['tage']) == 0) {
  				new errorPage("Keine tage ausgewählt!");
  			}
  			
  			$tage = $_POST['tage'];
  			
  			
  			// Tage zusammensuchen
  			$daysToSave = [];
  			
  			while(DateFunctions::isSQLDateAtOrBeforeAnother($start, $ende)) {
  				$tag = DateFunctions::getWeekDayFromSQLDate($start);
  				
  				if(in_array($tag, $tage) && !Ferien::isFerien($tag)) {
  					$daysToSave[] = $start;
  				}
  				
  				$start = DateFunctions::addOneDayToMySqlDate($start);
  			}
  			
  			// Debugger::debugObject($daysToSave,1);
  			
  			
  			for($i = 0; $i < sizeof($schueler); $i++) {
  				
  				DB::getDB()->query("INSERT INTO absenzen_beurlaubungen (beurlaubungCreatorID,beurlaubungIsInternAbwesend, beurlaubungPrinted) values('" . DB::getUserID() . "','" . (($_POST['internAbwesend'] > 0) ? 1 : 0) . "',1)");
  				
  				
  				$beurlaubungID = DB::getDB()->insert_id();
  				
  				for($d = 0; $d < sizeof($daysToSave); $d++) {
  				
	  				
	  				
	  				
	  				// Absenz erstellen
	  				
	  				DB::getDB()->query("INSERT INTO absenzen_absenzen (
	              absenzSchuelerAsvID,
	              absenzDatum,
	              absenzDatumEnde,
	              absenzQuelle,
	              absenzErfasstTime,
	              absenzErfasstUserID,
	              absenzStunden,
	              absenzIsEntschuldigt,
	              absenzBeurlaubungID,
	              absenzIsSchriftlichEntschuldigt,
	              absenzBemerkung
	              )
	              values (
	                '" . $schueler[$i] . "',
	                '" . $daysToSave[$d] . "',
	                '" . $daysToSave[$d]. "',
	                'PERSOENLICH',
	                UNIX_TIMESTAMP(),
	                '" . DB::getUserID() . "',
	                '" . implode(",",$stunden) . "',
	                '1',
	                '" . $beurlaubungID . "',
	                '1',
	                '" . DB::getDB()->escapeString($_POST['kommentar']) . "'
	              )");
  				}
  			}
  			
  			eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/sammelbeurlaubungOK") . "\");");
  			PAGE::kill(true);
			  //exit(0);
  			break;
  	}
  	
  	$currentStunde = stundenplan::getCurrentStunde();
  	
  	if($currentStunde == 0) $currentStunde = DB::getSettings()->getValue("stundenplan-anzahlstunden");
  	
  	$stunden = $this->getStundenAuswahl([],true);
  	
  	$schuelerOptions = "";
  	
  	$allSchueler = schueler::getAll('length(schuelerKlasse), schuelerKlasse,schuelerName,schuelerRufname');
  	
  	for($i = 0; $i < sizeof($allSchueler); $i++) {
  		$schuelerOptions .= '<option value="' . $allSchueler[$i]->getAsvID() . '">Klasse ' . $allSchueler[$i]->getKlasse() . " - " . $allSchueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
  	}
  	
  	$currentDate = date("d.m.Y");
  	eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/periodischebeurlaubung") . "\");");
  	PAGE::kill(true);
			//exit(0);
  	
  	
  }

  private function sammelbeurlaubung() {
    $schueler = array();

    if(isset($_REQUEST['schueler']) && $_REQUEST['schueler'] != "") {
      $schueler = $_REQUEST['schueler'];	// MutliselectField
    }
    

    switch($_REQUEST['action']) {
      case "save":
        $zeitraum = $_POST['bu_zeit'];
        $zeitraum = explode(" bis ",$zeitraum);

        if(sizeof($zeitraum) != 2) {
          new errorPage("Ungültiger Zeitraum!");
        }

        if(!DateFunctions::isNaturalDate($zeitraum[0]) || !DateFunctions::isNaturalDate($zeitraum[1]) || !DateFunctions::isNaturalDateAfterAnother($zeitraum[1], $zeitraum[0])) {
          new errorPage("Ungültiger Zeitraum!");
        }

        $stunden = array();

        for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
          if($_POST['stunde' . $i] > 0) {
            $stunden[] = $i;
          }
        }

        for($i = 0; $i < sizeof($schueler); $i++) {

          DB::getDB()->query("INSERT INTO absenzen_beurlaubungen (beurlaubungCreatorID,beurlaubungIsInternAbwesend) values('" . DB::getUserID() . "','" . (($_POST['internAbwesend'] > 0) ? 1 : 0) . "')");

          $beurlaubungID = DB::getDB()->insert_id();

          // Absenz erstellen

          DB::getDB()->query("INSERT INTO absenzen_absenzen (
              absenzSchuelerAsvID,
              absenzDatum,
              absenzDatumEnde,
              absenzQuelle,
              absenzErfasstTime,
              absenzErfasstUserID,
              absenzStunden,
              absenzIsEntschuldigt,
              absenzBeurlaubungID,
              absenzIsSchriftlichEntschuldigt,
              absenzBemerkung
              )
              values (
                '" . $schueler[$i] . "',
                '" . DateFunctions::getMySQLDateFromNaturalDate($zeitraum[0]) . "',
                '" . DateFunctions::getMySQLDateFromNaturalDate($zeitraum[1]) . "',
                'PERSOENLICH',
                UNIX_TIMESTAMP(),
                '" . DB::getUserID() . "',
                '" . implode(",",$stunden) . "',
                '1',
                '" . $beurlaubungID . "',
                '0',
                '" . DB::getDB()->escapeString($_POST['kommentar']) . "'
              )");
        }

        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/sammelbeurlaubungOK") . "\");");
        PAGE::kill(true);
		  	//exit(0);
      break;
    }

    $currentStunde = stundenplan::getCurrentStunde();

    if($currentStunde == 0) $currentStunde = DB::getSettings()->getValue("stundenplan-anzahlstunden");

    $stunden = $this->getStundenAuswahl();
    
    $schuelerOptions = "";
    
    $allSchueler = schueler::getAll('length(schuelerKlasse), schuelerKlasse,schuelerName,schuelerRufname');
    
    for($i = 0; $i < sizeof($allSchueler); $i++) {
    	$schuelerOptions .= '<option value="' . $allSchueler[$i]->getAsvID() . '">Klasse ' . $allSchueler[$i]->getKlasse() . " - " . $allSchueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
    }

    $currentDate = date("d.m.Y");
    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/sammelbeurlaubung") . "\");");
    PAGE::kill(true);
		//exit(0);


  }

  private function meldungStat() {
    $klasse = klasse::getByName($_GET['activeKlasse']);

    if($klasse == null) new errorPage();

    $meldungen = DB::getDB()->query("SELECT * FROM absenzen_meldung JOIN users ON users.userID=absenzen_meldung.meldungUserID WHERE meldungKlasse LIKE '" . DB::getDB()->escapeString($klasse->getKlassenName()) . "'");

    $meldungData = array();
    while($m = DB::getDB()->fetch_array($meldungen)) $meldungData[] = $m;

    $events = "";

    for($i = 0; $i < sizeof($meldungData); $i++) {
      $events .= "{
        title: 'Gemeldet bei " . $meldungData[$i]['userName'] . "',
        start: '" . $meldungData[$i]['meldungDatum'] . "T" . date("H:i",$meldungData[$i]['meldungTime']) . ":00',
        color: 'green'
      },";
    }

    $currentDateAsSQLDate = DateFunctions::getTodayAsSQLDate();

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/meldungstat") . "\");");
    PAGE::kill(true);
			//exit(0);
  }

  private function editAbsenzen() {
    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

    if($schueler == null) new errorPage("Der angegebene Schüler existiert nicht!");

    $date = $_GET['currentDate'];
    $grade = $schueler->getKlasse();

    $absenzen = Absenz::getAbsenzenForSchueler($schueler);
    
    $absenzenCalculator = new AbsenzenCalculator($absenzen);
    $absenzenCalculator->calculate();
    
    $absenzenStat = $absenzenCalculator->getDayStat();
    
    // Debugger::debugObject($absenzenStat,1);
    
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
        $absenzenHTML .= "<a href=\"#\" data-toggle=\"tooltip\" title=\"" . $absenzen[$i]->getKommentar() . "\"><i class=\"far fa-sticky-note\"></i></a> ";
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
      $absenzenHTML .= "<td><a href=\"index.php?page=absenzensekretariat&currentDate=" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate()) . "&noReturnMainView=1&openAbsenz={$absenzen[$i]->getID()}\">Bearbeiten / Löschen</a></td>";

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

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/absenzen_schueler/index") . "\");");
    PAGE::kill(true);
			//exit(0);


  }
  
  private function selectSchueler() {
      
      if($_REQUEST['currentDate'] != "") {
          if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
              $currentDate = $_REQUEST['currentDate'];
          }
          else $currentDate = DateFunctions::getTodayAsNaturalDate();
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
      
      $schueler = schueler::getByAsvID($_REQUEST['schuelerAsvID']);
      
      if($schueler != null) {
          header("Location: index.php?page=absenzensekretariat&activeKlasse=" . $schueler->getKlasse() . "&currentDate=$currentDate&openSchueler=" . $schueler->getAsvID());
          exit(0);
      }
      else {
          header("Location: index.php?page=absenzensekretariat&mode=search&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
          exit(0);
      }
  }

  private function searchSchueler() {

    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();


    $search = $_POST['pupilName'];

    $search = trim($search);

    if($search == "" || strlen($search) < 2) {
      header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
      exit();
    }

    $searches = array();

    $search = explode(" ",$search);

    for($i = 0; $i < sizeof($search); $i++) {
      $searchSafe = DB::getDB()->escapeString($search[$i]);
      $searchSafe = "%" . $searchSafe . "%";

      $searches[] = "(schuelerName LIKE '" . $searchSafe . "' OR schuelerRufname LIKE '" . $searchSafe . "' OR schuelerKlasse LIKE '" . $searchSafe . "')";
    }

    $searchSQL = implode(" OR ",$searches);

    $schueler = DB::getDB()->query("SELECT * FROM schueler WHERE $searchSQL ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");

    $htmlListe = "";

    while($s = DB::getDB()->fetch_array($schueler)) {
      $sObjekt = new schueler($s);
      $kl = $sObjekt->getKlassenleitungAsText();
      $htmlListe .= "<a href=\"index.php?page=absenzensekretariat&activeKlasse=" . $s['schuelerKlasse'] . "&currentDate=$currentDate&openSchueler=" . $s['schuelerAsvID'] . "\">(Klasse " . $s['schuelerKlasse'] . " - $kl) " . $s['schuelerName'] . ", " . $s['schuelerRufname'] . "</a><br />";
    }

    $search = implode(" ",$search);
    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/searchresult") . "\");");
  }

  private function printBeurlaubung() {
    $absenz = Absenz::getByID($_GET['absenzID']);

    if($absenz != null) {
      if($absenz->isBeurlaubung()) {

        $absenzen = $absenz->getBeurlaubung()->getAllAbsenzen();

        $absenzenHTML = "";

        for($i = 0; $i < sizeof($absenzen); $i++) {
          $absenzenHTML .= "<tr><td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate()) . "</td>";
          $absenzenHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</td>";
          $absenzenHTML .= "<td>" . $absenzen[$i]->getStundenAsString() . "</td>";
          $absenzenHTML .= "<td>" . ($absenzen[$i]->getBeurlaubung()->isInternAbwesend() ? "Ja" : "Nein") . "</td>";
          $absenzenHTML .= "<td>" . $absenzen[$i]->getKommentar() . "</td></tr>";

        }

        eval("\$printContent = \"" . DB::getTPL()->get("absenzen/sekretariat/print/beurlaubung") . "\";");

        $absenz->getBeurlaubung()->setPrinted();

        $printHeader = true;
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/print_browser_and_close") . "\");");
        PAGE::kill(true);
		  	//exit(0);
      }
      else {
        $errorMessage = "Die angegebene Absenz ist nicht gültig! (Keine Beurlaubung)";
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/close_with_error") . "\");");
        PAGE::kill(true);
			  //exit(0);
      }
    }
    else {
      $errorMessage = "Die angegebene Absenz ist nicht gültig!";
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/close_with_error") . "\");");
    }
  }

  private function deleteVerspaetung() {

    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    DB::getDB()->query("DELETE FROM absenzen_verspaetungen WHERE verspaetungID='" . intval($_GET['verspaetungID']) . "'");


    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);

  }

  private function addVerspaetung() {
    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

    if($schueler != null) {
      DB::getDB()->query("INSERT INTO absenzen_verspaetungen (verspaetungSchuelerAsvID,verspaetungDate,verspaetungMinuten,verspaetungKommentar, verspaetungStunde) values('" . $schueler->getAsvID() . "','" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "','" . intval($_POST['verspaetungMinuten']) . "','" . DB::getDB()->escapeString($_POST['verspaetungKommentar']) . "','" . DB::getDB()->escapeString($_POST['verspaetungStunde']) . "')");
    }

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);
  }

  private function addBeurlaubung() {
    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();


    $zeitraum = $_POST['bu_zeit'];
    $zeitraum = explode(" bis ",$zeitraum);

    if(sizeof($zeitraum) != 2) {
      new errorPage("Ungültiger Zeitraum!");
    }

    if(!DateFunctions::isNaturalDate($zeitraum[0]) || !DateFunctions::isNaturalDate($zeitraum[1]) || !DateFunctions::isNaturalDateAfterAnother($zeitraum[1], $zeitraum[0])) {
      new errorPage("Ungültiger Zeitraum!");
    }

    $stunden = array();

    for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
      if($_POST['stunde' . $i] > 0) {
        $stunden[] = $i;
      }
    }

    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);
    if($schueler == null) new errorPage("Der angegebene Schüler existiert nicht!");

    DB::getDB()->query("INSERT INTO absenzen_beurlaubungen (beurlaubungCreatorID,beurlaubungIsInternAbwesend) values('" . DB::getUserID() . "','" . (($_POST['internAbwesend'] > 0) ? 1 : 0) . "')");

    $beurlaubungID = DB::getDB()->insert_id();

    // Absenz erstellen

    DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBeurlaubungID,
        absenzIsSchriftlichEntschuldigt,
        absenzBemerkung
        )
        values (
          '" . $_GET['schuelerAsvID'] . "',
          '" . DateFunctions::getMySQLDateFromNaturalDate($zeitraum[0]) . "',
          '" . DateFunctions::getMySQLDateFromNaturalDate($zeitraum[1]) . "',
          'PERSOENLICH',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . implode(",",$stunden) . "',
          '1',
          '" . $beurlaubungID . "',
          '0',
          '" . DB::getDB()->escapeString($_POST['kommentar']) . "'
        )");

    $stundenplanKlasse = grade::getStundenplanGradeFromNormalGrade($schueler->getKlasse());

    $termine = Klassentermin::getByClass([$stundenplanKlasse],
        DateFunctions::getMySQLDateFromNaturalDate($zeitraum[0]),
        DateFunctions::getMySQLDateFromNaturalDate($zeitraum[1])
    );
    
    $lnw = Leistungsnachweis::getByClass([$stundenplanKlasse],
        DateFunctions::getMySQLDateFromNaturalDate($zeitraum[0]),
        DateFunctions::getMySQLDateFromNaturalDate($zeitraum[1])
        );
    
    $termineHTML = "";
    
    for($i = 0; $i < sizeof($termine); $i++) {
        $termineHTML .= "Klassentermin: " . $termine[$i]->getTitle() . "<br />";
    }
    
    for($i = 0; $i < sizeof($lnw); $i++) {
        $termineHTML .= "Leistungsnachweis: " . $lnw[$i]->getArtKurztext() . " in " . $lnw[$i]->getFach() . " bei " . $lnw[$i]->getLehrer() . "<br />";
    }
    
    
    
    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/sekretariat/beurlaubungOK") . "\");");
    
    // header("Location: index.php?page=absenzensekretariat&currentDate={$zeitraum[0]}&activeKlasse={$_GET['activeKlasse']}");
    PAGE::kill(true);
			//exit(0);
  }

  private function processKrankmeldungen() {
    $krankmeldungen = DB::getDB()->query("SELECT * FROM absenzen_krankmeldungen LEFT JOIN schueler ON krankmeldungSchuelerAsvID=schuelerAsvID LEFT JOIN users ON krankmeldungElternID=userID WHERE krankmeldungAbsenzID=0");

    $allStunden = array();
    for($i = 1; $i<= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
      $allStunden[] = $i;
    }

    $allStunden = implode(",",$allStunden);

    while($km = DB::getDB()->fetch_array($krankmeldungen)) {
      if($_POST['action_' . $km['krankmeldungID']] == "save") {
        DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBefreiungID,
        absenzBemerkung
        )
        values (
          '" . $km['schuelerAsvID'] . "',
          '" . $km['krankmeldungDate'] . "',
          '" . $km['krankmeldungUntilDate'] . "',
          'WEBPORTAL',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . $allStunden . "',
          '1',
          '0',
          'Online Krankmeldung vom " . functions::makeDateFromTimestamp($km['krankmeldungTime']) . ". \r\nBearbeitet durch " . DB::getSession()->getData("userName") . "\r\n" . DB::getDB()->escapeString($km['krankmeldungKommentar']) . "'
        )");

        $newID = DB::getDB()->insert_id();
        DB::getDB()->query("UPDATE absenzen_krankmeldungen SET krankmeldungAbsenzID='" . $newID . "' WHERE krankmeldungID='" . $km['krankmeldungID'] . "'");
      }

      if($_POST['action_' . $km['krankmeldungID']] == "delete") {
        DB::getDB()->query("DELETE FROM absenzen_krankmeldungen WHERE krankmeldungID='" . $km['krankmeldungID'] . "'");
      }
    }

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);

  }

  private function deleteAttestpflicht() {
    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $ap = DB::getDB()->query_first("SELECT * FROM absenzen_attestpflicht WHERE attestpflichtID='" . intval($_GET['attestpflichtID']) . "'");
    if($ap['attestpflichtID'] > 0) {
      DB::getDB()->query("DELETE FROM absenzen_attestpflicht WHERE attestpflichtID='" . $ap['attestpflichtID'] . "'");
    }

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);
  }

  private function addAttestpflicht() {
    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();


    if(DateFunctions::isNaturalDate($_POST['startDate'])) {
      if(DateFunctions::isNaturalDate($_POST['endDate'])) {
        if(DateFunctions::isNaturalDateAfterAnother($_POST['endDate'], $_POST['startDate'])) {
          $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

          if(is_object($schueler)) {
            DB::getDB()->query("INSERT INTO absenzen_attestpflicht (schuelerAsvID,
                  attestpflichtStart,
                  attestpflichtEnde,
                  attestpflichtUserID
                ) values (
                  '" . $schueler->getAsvID() . "',
                  '" . DateFunctions::getMySQLDateFromNaturalDate($_POST['startDate']) . "',
                  '" . DateFunctions::getMySQLDateFromNaturalDate($_POST['endDate']) . "',
                  '" . DB::getUserID() . "'
                )");
            header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
            exit(0);
          }
        }
      }
    }

    new errorPage("Beim Hinzufügen der Attestpflicht ist leider ein Fehler aufgetreten! (Datumsangaben nicht korrekt!)");
    exit(0);
  }

  private function endSanizimmer() {

    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $sanizimmerAktion = DB::getDB()->query_first("SELECT * FROM absenzen_sanizimmer JOIN schueler ON absenzen_sanizimmer.sanizimmerSchuelerAsvID=schueler.schuelerAsvID JOIN users ON absenzen_sanizimmer.sanizimmerErfasserUserID=users.userID WHERE sanizimmerID='" . intval($_GET['sanizimmerID']) . "'");
    if($sanizimmerAktion['sanizimmerID'] > 0) {
      if($_POST['zurueck'] > 0) {
        DB::getDB()->query("UPDATE absenzen_sanizimmer SET sanizimmerTimeEnde=UNIX_TIMESTAMP(), sanizimmerResult='ZURUECK' WHERE sanizimmerID='" . $sanizimmerAktion['sanizimmerID'] . "'");
      }
      else if($_POST['befreiung'] > 0) {
        DB::getDB()->query("UPDATE absenzen_sanizimmer SET sanizimmerTimeEnde=UNIX_TIMESTAMP(), sanizimmerResult='BEFREIUNG' WHERE sanizimmerID='" . $sanizimmerAktion['sanizimmerID'] . "'");

        $befreiung = DB::getDB()->query("INSERT INTO absenzen_befreiungen (befreiungUhrzeit,befreiungLehrer) values('" . date("H:i") . "','Sekretariat')");

        $idBefreiung = DB::getDB()->insert_id();

        list($stunde,$minute) = explode(":",date("H:i"));
        $stunde = stundenplan::getCurrentStunde($stunde,$minute);

        $stunden = array();
        if($stunde > 0) {
          for($i = $stunde; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
            $stunden[] = $i;
          }
        }

        DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBefreiungID,
        absenzBemerkung
        )
        values (
          '" . $sanizimmerAktion['sanizimmerSchuelerAsvID'] . "',
          '" . DateFunctions::getTodayAsSQLDate() ."',
          '" . DateFunctions::getTodayAsSQLDate() ."',
          'PERSOENLICH',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . implode(",",$stunden) . "',
          '1',
          '" . $idBefreiung . "',
          '" . date("d.m.Y H:i") . " Uhr: (" . DB::getSession()->getData("userName") . "): Befreiung ausgestellt. (Nach Sanizimmer Besuch) " . DB::getDB()->escapeString($_POST['bemerkung']) . "'
        )");
        $absenzID = DB::getDB()->insert_id();
        
        header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}&openPrintBefreiung=$absenzID");
        exit(0);
      }
      else if($_POST['rettungsdienst'] > 0) {
        DB::getDB()->query("UPDATE absenzen_sanizimmer SET sanizimmerTimeEnde=UNIX_TIMESTAMP(), sanizimmerResult='RETTUNGSDIENST' WHERE sanizimmerID='" . $sanizimmerAktion['sanizimmerID'] . "'");

        $befreiung = DB::getDB()->query("INSERT INTO absenzen_befreiungen (befreiungUhrzeit,befreiungLehrer) values('" . date("H:i") . "','Sekretariat')");

        $idBefreiung = DB::getDB()->insert_id();

        list($stunde,$minute) = explode(":",date("H:i"));
        $stunde = stundenplan::getCurrentStunde($stunde,$minute);

        $stunden = array();
        if($stunde > 0) {
          for($i = $stunde; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
            $stunden[] = $i;
          }
        }

        DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBefreiungID,
        absenzBemerkung
        )
        values (
          '" . $sanizimmerAktion['sanizimmerSchuelerAsvID'] . "',
          '" . DateFunctions::getTodayAsSQLDate() ."',
          '" . DateFunctions::getTodayAsSQLDate() ."',
          'PERSOENLICH',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . implode(",",$stunden) . "',
          '1',
          '" . $idBefreiung . "',
          '" . date("d.m.Y H:i") . " Uhr: (" . DB::getSession()->getData("userName") . "): Befreiung ausgestellt. (Nach Sanizimmer Besuch. ABHOLUNG RETTUNGSDIENST)  " . DB::getDB()->escapeString($_POST['bemerkung']) . "'
        )");
        
        $absenzID = DB::getDB()->insert_id();
        
        header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}&openPrintBefreiung=$absenzID&printRettungsDienst=1");
        exit(0);
      }

      else if($_POST['delete'] > 0) {
        DB::getDB()->query("DELETE FROM absenzen_sanizimmer WHERE sanizimmerID='" . $sanizimmerAktion['sanizimmerID'] . "'");
      }
    }
    else {
      new errorPage("Sanizimmer Eintrag nicht vorhanden!");
      die();
    }


    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);
  }

  private function addSanizimmer() {

    if($_REQUEST['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $currentDate = $_REQUEST['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

    if($schueler == null) {
      new errorPage("Der angegebene Schüler ist nicht vorhanden!");
      exit(0);
    }

    DB::getDB()->query("INSERT INTO absenzen_sanizimmer (sanizimmerSchuelerAsvID,sanizimmerTimeStart,sanizimmerErfasserUserID, sanizimmerGrund) values('" . $schueler->getAsvID() . "',UNIX_TIMESTAMP(),'" . DB::getUserID() . "','" . DB::getDB()->escapeString($_POST['sanizimmerGrund']) . "')");

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);
  }

  private function printBefreiung() {
    $absenz = Absenz::getByID($_GET['absenzID']);

    if($absenz != null) {
      if($absenz->isBefreiung()) {
        

        if($_GET['printRettungsDienst'] > 0) {
        	$adressen = $absenz->getSchueler()->getAdressen();
        	$telefonNummern = $absenz->getSchueler()->getTelefonnummer();
        	
        	$adressenHTML = "<table style=\"width:100%; border: 1px solid;\">";
        	for($a = 0; $a < sizeof($adressen); $a++) {
        		$adressenHTML .= "<tr><td style=\"width:100%; border: 1px solid;\">" . $adressen[$a]->getAdresseAsText() . "<br /><br />\r\n";
        		
        		for($b = 0; $b < sizeof($telefonNummern); $b++) {
        			if($telefonNummern[$b]->getAdresseID() == $adressen[$a]->getID()) {
        				if($telefonNummern[$b]->getTyp() == 'mobiltelefon') {
        					$adressenHTML .= "Mobil: " . $telefonNummern[$b]->getNummer() . "<br />";
        				}
        				elseif($telefonNummern[$b]->getTyp() == 'fax') {
        					$adressenHTML .= "Fax: " . $telefonNummern[$b]->getNummer() . "<br />";
        				}
        				else {
        					$adressenHTML .= "Sonstige Nummer: " . $telefonNummern[$b]->getNummer() . "<br />";
        				}
        			}
        		}
        		
        		$adressenHTML .= "</td></tr>";
        	}
        	
        	
        	
        	$adressenHTML .= "</table>";
        }
        else $adressenHTML = 'No Rettungsdienstprinting';
        
        eval("\$printContent = \"" . DB::getTPL()->get("absenzen/sekretariat/print/befreiung") . "\";");
        
        $absenz->getBefreiung()->setPrinted();

        $printHeader = true;
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/print_browser_and_close") . "\");");
        exit(0);
      }
      else {
        $errorMessage = "Die angegebene Absenz ist nicht gültig! (Keine Befreiung)";
        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/close_with_error") . "\");");
      }
    }
    else {
      $errorMessage = "Die angegebene Absenz ist nicht gültig!";
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("printDialogs/close_with_error") . "\");");
    }
  }

  private function addBefreiung() {
    if($_GET['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_GET['currentDate'])) {
        $currentDate = $_GET['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);

    if($schueler == null) {
      new errorPage("Der angegebene Schüler ist nicht vorhanden!");
      exit(0);
    }

    if(!DateFunctions::isTime($_POST['uhrzeit'])) {
      new errorPage("Die angegebene Uhrzeit ist ungültig: " . $_POST['uhrzeit']);
      die();
    }


    $befreiung = DB::getDB()->query("INSERT INTO absenzen_befreiungen (befreiungUhrzeit,befreiungLehrer) values('" . $_POST['uhrzeit'] . "','" . DB::getDB()->escapeString($_POST['currentLehrer']) . "')");

    $idBefreiung = DB::getDB()->insert_id();

    list($stunde,$minute) = explode(":",$_POST['uhrzeit']);
    $stunde = stundenplan::getCurrentStunde($stunde,$minute);

    $stunden = array();
    if($stunde > 0) {
      for($i = $stunde; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
        $stunden[] = $i;
      }
    }

    DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBefreiungID,
        absenzBemerkung
        )
        values (
          '" . $schueler->getAsvID() . "',
          '" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) ."',
          '" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) ."',
          'PERSOENLICH',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . implode(",",$stunden) . "',
          '1',
          '" . $idBefreiung . "',
          '" . date("d.m.Y H:i") . " Uhr: (" . DB::getSession()->getData("userName") . "): Befreiung ausgestellt. " . DB::getDB()->escapeString($_POST['bemerkung']) . "'
        )");

    $newID = DB::getDB()->insert_id();
    
    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}&openPrintBefreiung=$newID&printRettungsDienst=" . $_POST['printRettungsDienst']);
    exit(0);
  }

  private function markMeldung() {


    if($_GET['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_GET['currentDate'])) {
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

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);
  }

  private function unmarkMeldung() {


    if($_GET['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_GET['currentDate'])) {
        $currentDate = $_GET['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    DB::getDB()->query("DELETE FROM absenzen_meldung WHERE meldungDatum='" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "' AND meldungKlasse='" . DB::getDB()->escapeString($_GET['meldungKlasse']) . "'");

    header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
    exit(0);
    }


  private function editAbsenz() {
    $absenz = Absenz::getByID(intval($_GET['absenzID']));

    if($absenz == null) {
      new errorPage("Absenz nicht vorhanden!");
      die();
    }

    if($_POST['delete'] == 1) {

      $absenz->delete();

      if($_GET['returnToMainView'] == 1) {
        header("Location: index.php?page=absenzensekretariat&currentDate=$date&activeKlasse={$_GET['activeKlasse']}");
        exit();
      }
      else {
        header("Location: index.php?page=absenzensekretariat&mode=editAbsenzen&schuelerAsvID=" . $absenz->getSchueler()->getAsvID());
        exit(0);
      }
    }

    if(!DateFunctions::isNaturalDate($_POST['krankAm'])) {
      new errorPage("Das Startdatum ist kein richtiges Datum!");
      die();
    }

    if(!DateFunctions::isNaturalDate($_POST['krankBis'])) {
      new errorPage("Das Enddatum ist kein richtiges Datum!");
      die();
    }

    if(!DateFunctions::isNaturalDateAfterAnother($_POST['krankBis'], $_POST['krankAm'])) {
      new errorPage("Das Enddatum muss am oder nach dem Startdatum liegen! (Start: {$_POST['krankAm']}, Ende: {$_POST['krankBis']})");
      exit(0);
    }

    if(!in_array($_POST['absenzKanal'], array("TELEFON","FAX","WEBPORTAL","LEHRER","PERSOENLICH"))) {
      new errorPage("Der angegebene Kanal ist ungültig!");
      die();
    }

    $stunden = array();

    for($i = 1; $i <= $maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
      if($_POST['stunde' . $i] > 0) {
        $stunden[] = $i;
      }
    }


    if(sizeof($stunden) == 0) {
      new errorPage("Es wurden keine Stunden ausgewählt!");
      die();
    }

    if($_POST['isEntschuldigt']) $absenz->setEntschuldigt();
    else $absenz->setUnentschuldigt();

    if($_POST['jetztgekommen'] > 0) {
      $absenz->setEntschuldigt();
      $absenz->setJetztGekommen();
    }

    if(trim($_POST['bemerkung']) != "") $absenz->addKommentar($_POST['bemerkung']);

    if(!($_POST['jetztgekommen'] > 0)) $absenz->setStunden($stunden);

    $absenz->setStartdate(DateFunctions::getMySQLDateFromNaturalDate($_POST['krankAm']));
    $absenz->setEndeDate(DateFunctions::getMySQLDateFromNaturalDate($_POST['krankBis']));
    $absenz->setKanal($_POST['absenzKanal']);

    if($_GET['returnToMainView'] == 1) {
      header("Location: index.php?page=absenzensekretariat&currentDate=$date&activeKlasse={$_GET['activeKlasse']}");
      exit();
    }
    else {
      header("Location: index.php?page=absenzensekretariat&mode=editAbsenzen&schuelerAsvID=" . $absenz->getSchueler()->getAsvID());
      exit(0);
    }
  }

  private function ungeklaert() {
    $absenz = Absenz::getByID(intval($_GET['absenzID']));

    if($absenz == null) {
      new errorPage("Absenz nicht vorhanden!");
      die();
    }

    if($absenz->isEntschuldigt()) {
      new errorPage("Absenz nicht mehr offen!");
      die();
    }

    if($_POST['isStillAbsent'] == 1) {
      $absenz->setEntschuldigt();
      $absenz->addKommentar(date("d.m.Y H:i") . " Uhr: War bereits am Vortag krank. (" . DB::getSession()->getData("userName") . ")");
    }

    if($_POST['erreicht'] == 1) {
      $absenz->setEntschuldigt();
      $absenz->addKommentar(date("d.m.Y H:i") . " Uhr: Erreicht: " . $_POST['kommentar'] . " (" . DB::getSession()->getData("userName") . ")");
    }

    if($_POST['erreicht'] == 0) {
      $absenz->addKommentar(date("d.m.Y H:i") . " Uhr: Nicht erreicht: " . $_POST['kommentar'] . " (" . DB::getSession()->getData("userName") . ")");
    }
    
    if($_POST['jetztGekommen'] == 1) {
    	$absenz->addKommentar("Jetzt gekommen: " . $_POST['kommentar'] . " (" . DB::getSession()->getData("userName") . ")");
    	$absenz->setEntschuldigt();
    	$aktuelleStunde = stundenplan::getCurrentStunde();
    	if($aktuelleStunde > 0) {
    		$stunden = [];
    		for($i = 1; $i <= $aktuelleStunde; $i++) {
    			$stunden[] = $i;
    		}
    		$absenz->setStunden($stunden);
    	}
    }

    if($_POST['delete'] == 1) {
      $absenz->delete();
    }


    header("Location: index.php?page=absenzensekretariat&currentDate=$date&activeKlasse={$_GET['activeKlasse']}");
    exit();
  }

  private function editComment() {
    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);
    $date = $_POST['currentDate'];

    $_GET['currentDate'] = $date;		// Zur Formularanzeige

    if($schueler == null) {
      new errorPage("Der angegebene Schüler existiert nicht!");
      die();
    }

    DB::getDB()->query("INSERT INTO absenzen_comments (schuelerAsvID, commentText)
        values(
          '" . $schueler->getAsvID() . "',
        '" . DB::getDB()->escapeString($_POST['commentText']) . "')
        ON DUPLICATE KEY UPDATE commentText='" . DB::getDB()->escapeString($_POST['commentText']) . "'

        ");

    header("Location: index.php?page=absenzensekretariat&currentDate=$date&activeKlasse={$_GET['activeKlasse']}");
    exit();
  }

  private function addAbsenzDialog() {
    $schueler = schueler::getByAsvID($_GET['schuelerAsvID']);
    $date = $_POST['currentDate'];

    $_GET['currentDate'] = $date;		// Zur Formularanzeige

    if($schueler == null) {
      new errorPage("Der angegebene Schüler existiert nicht!");
      die();
    }

    if(!DateFunctions::isNaturalDate($date)) {
      new errorPage("Das aktuelle Datum ist ungültig!");
      die();
    }

    if(!DateFunctions::isNaturalDate($_POST['krankBis'])) {
      new errorPage("Das Enddatum der Absenz ist ungültig!");
      die();
    }

    if(!DateFunctions::isNaturalDateAfterAnother($_POST['krankBis'],$date)) {
      new errorPage("Das Enddatum der Absenz ist ungültig!");
      die();
    }

    if(!in_array($_POST['absenzKanal'], array("TELEFON","FAX","WEBPORTAL","LEHRER","PERSOENLICH"))) {
      new errorPage("Der angegebene Kanal ist ungültig!");
      die();
    }

    $stunden = array();

    for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
      if($_POST['stunde' . $i] > 0) {
        $stunden[] = $i;
      }
    }

    if(sizeof($stunden) == 0) {
      new errorPage("Es sind keine Stunden ausgewählt worden!");
      die();
    }

    DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBemerkung,
        absenzKommtSpaeter
        )
        values (
          '" . $schueler->getAsvID() . "',
          '" . DateFunctions::getMySQLDateFromNaturalDate($date) ."',
          '" . DateFunctions::getMySQLDateFromNaturalDate($_POST['krankBis']) ."',
          '" . $_POST['absenzKanal'] . "',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . implode(",",$stunden) . "',
          '" . (($_POST['entschuldigt'] > 0) ? 1 : 0) . "',
          '" . date("d.m.Y H:i") . " Uhr: (" . DB::getSession()->getData("userName") . "): " . DB::getDB()->escapeString($_POST['bemerkung']) . "',
          '" . (($_POST['entschuldigt'] == 2) ? 1 : 0) . "'
        )");

    header("Location: index.php?page=absenzensekretariat&currentDate=$date&activeKlasse={$_GET['activeKlasse']}");
    exit();

  }

  private function showIndex($openSchuelerDialog=0,$errorMessage="") {
    // Alle Klassen laden und alle Schüler dazu anzeigen

    if($_GET['currentDate'] != "") {
      if(datefunctions::isNaturalDate($_GET['currentDate'])) {
        $currentDate = $_GET['currentDate'];
      }
      else $currentDate = DateFunctions::getTodayAsNaturalDate();
    }
    else $currentDate = DateFunctions::getTodayAsNaturalDate();

    $_GET['currentDate'] = $currentDate;
    
    $currentDateSQL = DateFunctions::getMySQLDateFromNaturalDate($currentDate);

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

      header("Location: index.php?page=absenzensekretariat&currentDate=" . date("d.m.Y",$currentDateAsTime) . "&activeKlasse=" . $_GET['activeKlasse']);
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

    $merkerData = array();
    // Merker
    if($this->merkerActive) {
      $merkerSQL = DB::getDB()->query("SELECT * FROM absenzen_merker JOIN schueler ON merkerSchuelerAsvID=schuelerAsvID WHERE merkerDate <= '" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "'");
      while($merker = DB::getDB()->fetch_array($merkerSQL)) {
        $merkerData[] = $merker;
      }
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



      $klassenListeHTML .= "<tr><td>" . (($klassen[$i]->getKlassenName() == $_GET['activeKlasse']) ? ("<u>") : ("")) . "<a href=\"index.php?page=absenzensekretariat&activeKlasse=" . $klassen[$i]->getKlassenName() . "&currentDate=" . $currentDate . "\" style=\"display:block\">" . $klassen[$i]->getKlassenName() . " <small>($kl)</small></a>" . (($klassen[$i]->getKlassenName() == $_GET['activeKlasse']) ? ("</u>") : (""));

      if($this->merkerActive) {
        $merkerKlasse = array();
        for($m = 0; $m < sizeof($merkerData); $m++) {
          if($merkerData[$m]['schuelerKlasse'] == $klassen[$i]->getKlassenName()) {
            $merkerKlasse[] = "

			<button type=\"button\" class=\"btn btn-xs btn-warning\" onclick=\"window.location.href='index.php?page=absenzensekretariat&activeKlasse={$klassen[$i]->getKlassenName()}&currentDate={$currentDate}&mode=deleteMerker&merkerID=" . $merkerData[$m]['merkerID'] . "'\"><i class=\"fa fa-bell\"></i> <b>" . $merkerData[$m]['schuelerName'] . ", " . $merkerData[$m]['schuelerRufname'] . "</b><br />" . $merkerData[$m]['merkerText'] . "<br />" . DateFunctions::getNaturalDateFromMySQLDate($merkerData[$m]['merkerDate']) . "</button>";

          }
        }

        if(sizeof($merkerKlasse) > 0) {
          $klassenListeHTML .= implode("",$merkerKlasse);
        }
        else {
          $klassenListeHTML .= "";
        }
      }

      $klassenListeHTML .= "</td><td>";

      if(DB::getSettings()->getValue("absenzen-meldungaktivieren") > 0) {
        if(is_array($meldungen[$klassen[$i]->getKlassenName()])) {
          $klassenListeHTML .= "<a href=\"index.php?page=absenzensekretariat&activeKlasse={$klassen[$i]->getKlassenName()}&currentDate={$currentDate}&mode=unMarkMeldung&meldungKlasse={$klassen[$i]->getKlassenName()}\" data-toggle=\"tooltip\" title=\"Gemeldet. Bearbeitet durch " . $meldungen[$klassen[$i]->getKlassenName()]['userName'] . " am " . date("d.m.Y H:i",$meldungen[$klassen[$i]->getKlassenName()]['meldungTime']) ."\"><i class=\"fa fa-check\"></i></a>";
        }
        else {
          $klassenListeHTML .= "<a href=\"index.php?page=absenzensekretariat&activeKlasse={$klassen[$i]->getKlassenName()}&currentDate={$currentDate}&mode=markMeldung&meldungKlasse={$klassen[$i]->getKlassenName()}\" data-toggle=\"tooltip\" title=\"Noch nicht gemeldet. Klicken, um zu bestätigen.\"><font color=\"red\"><i class=\"fa fa-ban\"></i></font></a>";
        }
        $klassenListeHTML .= " <a data-toggle=\"tooltip\" title=\"Statistik der Meldungen\" href=\"index.php?page=absenzensekretariat&mode=meldungStat&activeKlasse={$klassen[$i]->getKlassenName()}&currentDate={$currentDate}\"><i class=\"fa fa-pie-chart\"></i></font></a>";

      }
      else $klassenListeHTML .= "&nbsp;";



      $klassenListeHTML .= "</td></tr>";

    }



    if($activeKlasse != null) {
      $lnw = [];
      $termine = [];
      if($this->stundenplan != null) {
              
      	$lnw = Leistungsnachweis::getByClass([$activeKlasse->getKlassenName()], DateFunctions::getMySQLDateFromNaturalDate($currentDate), DateFunctions::getMySQLDateFromNaturalDate($currentDate));
      	$termine = Klassentermin::getByClass([$activeKlasse->getKlassenName()], DateFunctions::getMySQLDateFromNaturalDate($currentDate), DateFunctions::getMySQLDateFromNaturalDate($currentDate));
      
      }
 
      $this->termineHTML = "";
      for($x = 0; $x < sizeof($lnw); $x++) {

          $this->termineHTML .= "<li>" . $lnw[$x]->getArtLangtext() . " in " . $lnw[$x]->getFach() . " (" . $lnw[$x]->getLehrer() . ")</li>";

      }
      for($x = 0; $x < sizeof($termine); $x++) {
      
      	$this->termineHTML .= "<li>" . $termine[$x]->getTitle() . " (" . $termine[$x]->getLehrer() . ")</li>";
      
      }

      $schuelerListeHTML = "";
      $dialogHTML = "";
      $viewKlasse = "(Klasse " . $activeKlasse->getKlassenName() . ")";
      
      $nummer = 0;
      
      for($i = 0; $i < sizeof($activeKlasse->getSchueler()); $i++) {

      	if(!$activeKlasse->getSchueler()[$i]->isAusgetreten()) {
      		$nummer++;
      		$nummerShow = $nummer;
      	}
      	else $nummerShow = '-';

        $schuelerListeHTML .= "<tr><td>" . ($nummerShow) . "</td>";
        $schuelerFotoDialog = "";
        
        if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren') && DB::getSettings()->getBoolean('absenzen-sekretariat-fotos-anzeigen') && (DB::getSession()->isAdmin() || DB::getSession()->isTeacher() || DB::getSession()->isMember('Schuelerinfo_Sehen'))) {
            
            $schuelerListeHTML .= "<td style=\"width:5%\">";
            
            $schuelerListeHTML .= "<img class=\"imgzoom\" src=\"https://www.rsu-intern.de/index.php?page=schuelerinfo&mode=getFoto&schuelerAsvID={$activeKlasse->getSchueler()[$i]->getAsvID()}\" width=\"50\">";
            $schuelerFotoDialog .= "<img class=\"imgzoom\" src=\"https://www.rsu-intern.de/index.php?page=schuelerinfo&mode=getFoto&schuelerAsvID={$activeKlasse->getSchueler()[$i]->getAsvID()}\" width=\"50\">";
            
        
            $schuelerListeHTML .= "</td>";
        }
        
        $schuelerListeHTML .= "<td><a href=\"#\" data-toggle=\"modal\" data-target=\"#modal" . $activeKlasse->getSchueler()[$i]->getAsvID() . "\">";

        if($activeKlasse->getSchueler()[$i]->isAusgetreten()) $schuelerListeHTML .= "<small>";



        $schuelerListeHTML .= $activeKlasse->getSchueler()[$i]->getCompleteSchuelerName();

        if($activeKlasse->getSchueler()[$i]->isAusgetreten()) $schuelerListeHTML .= "</small>" . " <span class=\"label label-info\">A: " . datefunctions::getNaturalDateFromMySQLDate($activeKlasse->getSchueler()[$i]->getAustrittDatumAsMySQLDate()) . "</span>";

        if(AbsenzSchuelerInfo::hasAttestpflicht($activeKlasse->getSchueler()[$i], DateFunctions::getMySQLDateFromNaturalDate($currentDate))) {
          $schuelerListeHTML .= " <span class=\"label label-danger\">Attestpflicht</span>";
        }

        $schuelerListeHTML .= "</a>";

        if(AbsenzSchuelerInfo::getComment($activeKlasse->getSchueler()[$i]) != "") {
          $schuelerListeHTML .= " <a href=\"#\" data-toggle=\"tooltip\" title=\"" . @htmlspecialchars((AbsenzSchuelerInfo::getComment($activeKlasse->getSchueler()[$i]))) . "\"><i class=\"far fa-sticky-note\"></i></a> ";
        }


        $merkerSchueler = array();
        for($m = 0; $m < sizeof($merkerData); $m++) {
          if($merkerData[$m]['schuelerAsvID'] == $activeKlasse->getSchueler()[$i]->getAsvID()) {
            $merkerSchueler[] = "<i class=\"fa fa-bell-o\"></i>" . $merkerData[$m]['merkerText'] . " <a href=\"index.php?page=absenzensekretariat&activeKlasse={$activeKlasse->getKlassenName()}&currentDate={$currentDate}&mode=deleteMerker&merkerID=" . $merkerData[$m]['merkerID'] . "\"><i class=\"fa fa-trash\"></i></a><br />";
          }
        }

        if(sizeof($merkerSchueler) > 0) {
          $schuelerListeHTML .= "<br /><small>" . implode("",$merkerSchueler) . "</small>";
        }

        $schuelerListeHTML .= "</td></tr>";

        $dialogHTML .= $this->getDialogHTML($activeKlasse->getSchueler()[$i]);

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
      if(!$absenzen[$i]->isEntschuldigt()) {
        $hasOffene = true;

        $dialogHTML .= $this->getOffenDialogHTML($absenzen[$i]->getSchueler(), $absenzen[$i]);

        $offen = "offen";
      }
      else {
        $dialogHTML .= $this->getEditDialogHTML($absenzen[$i]->getSchueler(), $absenzen[$i]);
      }


      $krankmeldungenHTML .= "<tr><td>" . $absenzen[$i]->getSchueler()->getKlasse() . "</td>";
      $krankmeldungenHTML .= "<td><a href=\"#\" data-toggle=\"modal\" data-target=\"#modal{$offen}" . $absenzen[$i]->getID() . "\">" . $absenzen[$i]->getSchueler()->getCompleteSchuelerName() . "</a>";

      if(!$absenzen[$i]->isEntschuldigt()) {
        $krankmeldungenHTML .= " <span class=\"label label-danger\">Ungeklärt</span>";
      }

      if($absenzen[$i]->isBefreiung()) {
        $krankmeldungenHTML .= " <span class=\"label label-info\">Befreiung</span> <a href=\"#\" onclick=\"javascript:window.open('index.php?page=absenzensekretariat&mode=printBefreiung&absenzID=" . $absenzen[$i]->getID() . "','print_absenz','widht:10,height:10')\"><i class=\"fa fa-print\"></i>" . (($absenzen[$i]->getBefreiung()->isPrinted()) ? ("") : (" <span class=\"label label-warning\">Noch nicht gedruckt</span>"));
      }

      if($absenzen[$i]->getKommentar() != "") {
        $krankmeldungenHTML .= " <a href=\"#\" data-toggle=\"tooltip\" title=\"" . @htmlspecialchars(($absenzen[$i]->getKommentar())) . "\"><i class=\"far fa-sticky-note\"></i></a> ";
      }

      if($absenzen[$i]->kommtSpaeter()) {
        $krankmeldungenHTML .= " <span class=\"label label-danger\"><i class=\"fa fa-clock-o\"></i> Kommt später</span>";
      }

      if($absenzen[$i]->isBeurlaubung()) {
        $krankmeldungenHTML .= " <span class=\"label label-info\">Beurlaubung</span> <a href=\"#\" onclick=\"javascript:window.open('index.php?page=absenzensekretariat&mode=printBeurlaubung&absenzID=" . $absenzen[$i]->getID() . "','print_beurlaubung','widht:10,height:10')\"><i class=\"fa fa-print\"></i>" . (($absenzen[$i]->getBeurlaubung()->isPrinted() || $absenzen[$i]->getBeurlaubung()->isInternAbwesend()) ? ("") : (" <span class=\"label label-warning\">Noch nicht gedruckt</span>"));
        if($absenzen[$i]->getBeurlaubung()->isInternAbwesend()) {
          $krankmeldungenHTML .= " <span class=\"label label-info\">Intern abwesend</span>";
        }



      }

      if(DB::getSettings()->getValue("absenzen-schriftlicheentschuldigung-sek")) {
	      if($absenzen[$i]->isSchriftlichEntschuldigt()) {
	        $krankmeldungenHTML .= " <small class=\"label label-success\"><i class=\"fa fas fa-pencil-alt\"></i><i class=\"fa fa-check\"></i></small>";
	      }
	      else {
	        $krankmeldungenHTML .= " <small class=\"label label-warning\"><i class=\"fa fas fa-pencil-alt\"></i><i class=\"fa fa-ban\"></i></small>";
	
	      }
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

      if($absenzen[$i]->isBefreiung() && !$absenzen[$i]->getBefreiung()->isPrinted()) {
        $hasToPrint[] = $absenzen[$i];
      }


      $krankmeldungenHTML .= "</tr>";
    }

    $tabellenStunden = "";

    $sanizimmerHTML = "";

    $sanizimmerSQL = DB::getDB()->query("SELECT * FROM absenzen_sanizimmer JOIN schueler ON absenzen_sanizimmer.sanizimmerSchuelerAsvID=schueler.schuelerAsvID JOIN users ON absenzen_sanizimmer.sanizimmerErfasserUserID=users.userID WHERE sanizimmerTimeEnde=0");

    $modalsSanizimmer = "";
    while($s = DB::getDB()->fetch_array($sanizimmerSQL))  {
      $schueler = new schueler($s);
      $sanizimmerHTML .= "<tr><td><a href=\"#\" data-toggle=\"modal\" data-target=\"#modalsanizimmer" . $schueler->getAsvID() . "\">" . $schueler->getCompleteSchuelerName() . " (" . $schueler->getKlasse() . ")</a></td>";
      $sanizimmerHTML .= "<td>" . functions::makeDateFromTimestamp($s['sanizimmerTimeStart']) . "<br /><small>" . $s['sanizimmerGrund'] . "</small></td>";
      $sanizimmerHTML .= "<td>" . $s['userName'] . "</td></tr>";

      $dialogHTML .= $this->getSanizimmerDialog($schueler,$s);
    }

    for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
      $tabellenStunden .= "<th>$s</th>";
    }


    $hasKrankmeldungen = false;
    $krankmeldungHTML = "";
    // Online Krankmeldungen
    $onlineKrankmeldungen = DB::getDB()->query("SELECT * FROM absenzen_krankmeldungen LEFT JOIN schueler ON krankmeldungSchuelerAsvID=schuelerAsvID LEFT JOIN users ON krankmeldungElternID=userID WHERE krankmeldungAbsenzID=0");
    while($km = DB::getDB()->fetch_array($onlineKrankmeldungen)) {
      $hasKrankmeldungen = true;
      eval("\$krankmeldungHTML .= \"" . DB::getTPL()->get("absenzen/sekretariat/index_bit_online_krankmeldung") . "\";");
    }
    
    $hasBeurlaubungen = false;
    $beurlaubungenHTML = '';
    $beurlaubungen = AbsenzBeurlaubungAntrag::getGenehmigtNichtVerarbeitete();
    
    for($i = 0; $i < sizeof($beurlaubungen); $i++) {
        $hasBeurlaubungen = true;
        eval("\$beurlaubungenHTML .= \"" . DB::getTPL()->get("absenzen/sekretariat/index_bit_online_beurlaubung") . "\";");
    }
    


    // Verspätungen

    $verspaetungHTML = "";

    $verspaetungen = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen LEFT JOIN schueler ON verspaetungSchuelerAsvID=schuelerAsvID WHERE verspaetungDate='" . DateFunctions::getMySQLDateFromNaturalDate($currentDate) . "'" . (($activeKlasse != null) ? (" AND schuelerKlasse LIKE '" . $activeKlasse->getKlassenName() . "'") : ("")));

    while($v = DB::getDB()->fetch_array($verspaetungen)) {
      $verspaetungHTML .= "<tr><td>" . $v['schuelerKlasse'] . "</td><td>" . $v['schuelerName'] . ", " . $v['schuelerRufname'] . "</td>";
      $verspaetungHTML .= "<td>" . $v['verspaetungMinuten'] . " (zur " . $v['verspaetungStunde'] . ". Stunde)</td>";
      $verspaetungHTML .= "<td>";

      if($v['verspaetungKommentar'] != "") {
        $verspaetungHTML .= "<a href=\"#\" data-toggle=\"tooltip\" title=\"" . $v['verspaetungKommentar'] . "\"><i class=\"far fa-sticky-note\"></i></a> ";
      }

      $verspaetungHTML .= "<a href=\"index.php?page=absenzensekretariat&mode=deleteVerspaetung&verspaetungID=" . $v['verspaetungID'] . "&currentDate=" . $currentDate . "&activeKlasse=" . $_GET['activeKlasse'] . "\"><i class=\"fa fa-trash\"></i>";


      $verspaetungHTML .= "</td></tr>";
    }

    $currentStunde = stundenplan::getCurrentStunde();

    if($currentStunde == 0) $currentStunde = DB::getSettings()->getValue("stundenplan-anzahlstunden");


    


    eval("echo(\"" . DB::getTPL()->get("absenzen/sekretariat/index") . "\");");
  }

  private function getDialogHTML($schueler) {
    $adressen = $schueler->getAdressen();
    $telefonNummern = $schueler->getTelefonnummer();

    $currentDate = $_GET['currentDate'];

    $adressenHTML = "";
    for($a = 0; $a < sizeof($adressen); $a++) {
      $adressenHTML .= "<tr><td>" . $adressen[$a]->getAdresseAsText() . "<br /><br />\r\n";

      for($b = 0; $b < sizeof($telefonNummern); $b++) {
        if($telefonNummern[$b]->getAdresseID() == $adressen[$a]->getID()) {
          if($telefonNummern[$b]->getTyp() == 'mobiltelefon') {
            $adressenHTML .= "<i class=\"fa fa-mobile\"></i> " . $telefonNummern[$b]->getNummer() . "<br />";
          }
          elseif($telefonNummern[$b]->getTyp() == 'mobiltelefon') {
            $adressenHTML .= "<i class=\"fa fa-fax\"></i> " . $telefonNummern[$b]->getNummer() . "<br />";
          }
          else {
            $adressenHTML .= "<i class=\"fa fa-mobile\"></i> " . $telefonNummern[$b]->getNummer() . "<br />";
          }
        }
      }

      $adressenHTML .= "</td><td><a href=\"http://maps.google.de/maps?q=" . $adressen[$a]->getGoogleMapsQuery() . "\" target=\"_blank\" class=\"btn btn-default\" style=\"font-size: 40pt\"><i class=\"fa fa-globe\"></i></a></td></tr>";
    }

    if($this->stundenplanActiveKlasse != null) {
      $stunde = stundenplan::getCurrentStunde();
      if($stunde == 0) {
        $currentLehrer = [];
      }
      else {
        $tag = date("N");

        $data = $this->stundenplanActiveKlasse[$tag-1][$stunde-1];

        $currentLehrer = array();
        for($i = 0; $i < sizeof($data); $i++) {
          $currentLehrer[] = $data[$i]['teacher'];
        }

      }
    }
    
    
    $schuelerFotoDialog = "";
    
    if(DB::getSettings()->getBoolean('schuelerinfo-fotos-aktivieren') && DB::getSettings()->getBoolean('absenzen-sekretariat-fotos-anzeigen') && (DB::getSession()->isAdmin() || DB::getSession()->isTeacher() || DB::getSession()->isMember('Schuelerinfo_Sehen'))) {
        
        $schuelerFotoDialog .= "<img style=\"float: left;padding:10px;\" class=\"imgzoom2\" src=\"https://www.rsu-intern.de/index.php?page=schuelerinfo&mode=getFoto&schuelerAsvID={$schueler->getAsvID()}\" width=\"50\">";
        
    }
    
    $selectLehrer = "";
    $alleLehrer = lehrer::getAll();
    for($i = 0; $i < sizeof($alleLehrer); $i++) {
        $selectLehrer .= "<option value=\"" . $alleLehrer[$i]->getKuerzel() . "\"" . ((in_array($alleLehrer[$i]->getKuerzel(), $currentLehrer)) ? (" selected=\"selected\"") : ("")) . ">" . $alleLehrer[$i]->getKuerzel() . "</option>\r\n";
    }



    $stundenAuswahl = $this->getStundenAuswahl(functions::getIntArrayFromTill(1, DB::getSettings()->getValue("stundenplan-anzahlstunden")));
    $stundenAuswahlBeurlaubung = $this->getStundenAuswahl(functions::getIntArrayFromTill(1, DB::getSettings()->getValue("stundenplan-anzahlstunden")));


    $nachteilsausgleichHTML = '';
    
    if($this->isActive("schuelerinfo")) {
        // Nachteilsausgleich
        
        $na = SchuelerNachteilsausgleich::getNachteilsausgleichForSchueler($schueler);
        if($na != null) {
            $nachteilsausgleichHTML = "<br /><label class=\"label label-danger\">Nachteilsausgleich: " . $na->getArt() . " - " . $na->getArbeitszeitverlaengerung() . " Zeitverlängerung - " . ($na->hasNotenschutz() ? ("Mit") : "Ohne") . " Notenschutz</label>";
        }
        
        // /Nachteilsausgleich
        
    }
    
    // Attestpflicht
    $aps = AbsenzSchuelerInfo::getAllAttestpflichtData($schueler);

    $absenzenDataHTML = "";
    for($i = 0; $i < sizeof($aps); $i++) {
      $absenzenDataHTML .= "Zeitraum: " . DateFunctions::getNaturalDateFromMySQLDate($aps[$i]['attestpflichtStart']) . " bis " . DateFunctions::getNaturalDateFromMySQLDate($aps[$i]['attestpflichtEnde']) . " (Hinzugefügt von " . $aps[$i]['userName'] . ")";
      $absenzenDataHTML .= " <a href=\"index.php?page=absenzensekretariat&mode=deleteAttestpflicht&attestpflichtID=" . $aps[$i]['attestpflichtID'] . "&currentDate={$currentDate}&activeKlasse={$_GET['activeKlasse']}\"><i class=\"fa fa-trash\"></i></a><br />";
    }

    $endeJuli = "31.07.20" . explode("/",DB::getSettings()->getValue("general-schuljahr"))[1];

    $start = "";

    if(DB::getSettings()->getValue("stundenplan-stunde1-start") != "") {
      $data = explode(":", DB::getSettings()->getValue("stundenplan-stunde1-start"));
      $timeErsteStunde = mktime($data[0],$data[1],0);
    }
    else $timeErsteStunde = time();

    $minutenVerspaetung = floor((time() - $timeErsteStunde) / 60);
    
    $optionsStundenVerspaetung = "";
    
    for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
    	$optionsStundenVerspaetung .= "<option value=\"$i\">Zur $i. Stunde</option>";
    }


    eval("\$dialogHTML = \"" . DB::getTPL()->get("absenzen/sekretariat/schuelerDialog") . "\";");

    return $dialogHTML;
  }

  private function getStundenAuswahl_old($selected=array()) {
  	$html = "<table class=\"table table-bordered\">";
  	$maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
  	
  	
  	$html .= "<tr><td colspan=\"6\">Vormittag (<a href=\"#\" onclick=\"javascript:selectVormittag(" . $this->idStundeSelect . "," . $maxStunden . ");\">auswählen</a>)</td><td colspan=\"" . ($maxStunden-6) . "\">Nachmittag (<a href=\"#\" onclick=\"javascript:selectNachmittag(" . $this->idStundeSelect . "," . $maxStunden . ");\">auswählen</a>)</td></tr>";
  	
  	$html .= "<tr>";
  	
  	
  	
  	for($i = 1; $i <= $maxStunden; $i++) {
  		$html .= "<td align=\"center\">
            <input type=\"checkbox\" name=\"stunde" . $i . "\" value=\"1\" id=\"stunde_" . $i . "_" . $this->idStundeSelect . "\"" . (in_array($i,$selected) ? ("checked=\"checked\"") : ("")) . "> <label for=\"stunde_" . $i . "_" . $this->idStundeSelect . "\">$i. Stunde</label>
            </td>";
  	}
  	$html .= "</tr>";
  	
  	$html .= "<tr><td colspan=\"" . $maxStunden . "\" align=\"center\">
        <a href=\"#\" onclick=\"javascript:selectNothing(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-ban\"></i> Keine Stunden auswählen</a> | ";
  	$html .= "<a href=\"#\" onclick=\"javascript:bisJetzt(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-left\"></i> Bis jetzts</a> | ";
  	$html .= "<a href=\"#\" onclick=\"javascript:abJetzt(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-right\"></i> Ab jetzts</a> | ";
  	$html .= "<a href=\"#\" onclick=\"javascript:selectAll(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-up\"></i> Alle auswählen</a>";
  	
  	
  	$html .= "</td></tr>";
  	$html .= "</table>";
  	
  	$this->idStundeSelect++;
  	
  	
  	return $html;
  }
  
  private $idStundeSelect = 1;
  
  private function getStundenAuswahl($selected=array(), $hideButtons=false) {
    $html = "<table class=\"table table-striped\"><tr><td>";
    $maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");


    $html .= "Vormittag <button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectVormittag(" . $this->idStundeSelect . "," . $maxStunden . ");\">Auswählen</button><br />";
    
    
    



    for($i = 1; $i <= 6; $i++) {
      $html .= "
            <input type=\"checkbox\" name=\"stunde" . $i . "\" value=\"1\" id=\"stunde_" . $i . "_" . $this->idStundeSelect . "\"" . (in_array($i,$selected) ? ("checked=\"checked\"") : ("")) . "> <label for=\"stunde_" . $i . "_" . $this->idStundeSelect . "\">$i. Stunde</label>
            ";
    }
    
    $html .= "</td></tr><tr><td>";
    
    $html .= "Nachmittag <button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectNachmittag(" . $this->idStundeSelect . "," . $maxStunden . ");\">auswählen</button><br />";
    
    for($i = 7; $i <= $maxStunden; $i++) {
    	$html .= "
            <input type=\"checkbox\" name=\"stunde" . $i . "\" value=\"1\" id=\"stunde_" . $i . "_" . $this->idStundeSelect . "\"" . (in_array($i,$selected) ? ("checked=\"checked\"") : ("")) . "> <label for=\"stunde_" . $i . "_" . $this->idStundeSelect . "\">$i. Stunde</label>
            ";
    }
    
    $html .= "</td></tr>";
    
    if(!$hideButtons) {
    
    $html .= "<tr><td>";
    $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectNothing(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-ban\"></i> Keine Stunden auswählen</button> ";
    $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:bisJetzt(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-left\"></i> Bis jetzt</button> ";
    $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:abJetzt(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-right\"></i> Ab jetzt</button> ";
    $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectAll(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-up\"></i> Alle auswählen</button></td></tr>";

    }
    
    $html .= "</table>";

    $this->idStundeSelect++;


    return $html;
  }

  private function getOffenDialogHTML(Schueler $schueler,$absenz) {
    $adressen = $schueler->getAdressen();
    $telefonNummern = $schueler->getTelefonnummer();


    $currentDate = $_GET['currentDate'];


    $adressenHTML = "";
    for($a = 0; $a < sizeof($adressen); $a++) {
      $adressenHTML .= "<tr><td>" . $adressen[$a]->getAdresseAsText() . "<br /><br /><a href=\"http://maps.google.de/maps?q=" . $adressen[$a]->getGoogleMapsQuery() . "\" target=\"_blank\"><i class=\"fa fa-map\"></i> Auf Google Maps anzeigen</a><br /><br />\r\n";

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

      $adressenHTML .= "</td></tr>";
    }


    eval("\$dialogHTML = \"" . DB::getTPL()->get("absenzen/sekretariat/offen_dialog") . "\";");

    return $dialogHTML;
  }

  private function getSanizimmerDialog($schueler,$dataSanizimmer) {
    $adressen = $schueler->getAdressen();
    $telefonNummern = $schueler->getTelefonnummer();


    $currentDate = $_GET['currentDate'];


    $adressenHTML = "";
    for($a = 0; $a < sizeof($adressen); $a++) {
      $adressenHTML .= "<tr><td>" . $adressen[$a]->getAdresseAsText() . "<br /><br /><a href=\"http://maps.google.de/maps?q=" . $adressen[$a]->getGoogleMapsQuery() . "\" target=\"_blank\"><i class=\"fa fa-map\"></i> Auf Google Maps anzeigen</a><br /><br />\r\n";

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

      $adressenHTML .= "</td></tr>";
    }



    eval("\$dialogHTML = \"" . DB::getTPL()->get("absenzen/sekretariat/sanizimmer_dialog") . "\";");

    return $dialogHTML;
  }

  private function getEditDialogHTML($schueler,$absenz) {
    $adressen = $schueler->getAdressen();
    $telefonNummern = $schueler->getTelefonnummer();


    $currentDate = $_GET['currentDate'];


    $adressenHTML = "";
    for($a = 0; $a < sizeof($adressen); $a++) {
      $adressenHTML .= "<tr><td>" . $adressen[$a]->getAdresseAsText() . "<br /><br /><a href=\"http://maps.google.de/maps?q=" . $adressen[$a]->getGoogleMapsQuery() . "\" target=\"_blank\"><i class=\"fa fa-map\"></i> Auf Google Maps anzeigen</a><br /><br />\r\n";

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

      $adressenHTML .= "</td></tr>";
    }

    $stunden = $absenz->getStundenAsArray();

    $stundenAuswahl = $this->getStundenAuswahl($stunden);

    $returnMainView = 1;
    if($_GET['noReturnMainView'] == 1) $returnMainView = 0;

    eval("\$dialogHTML = \"" . DB::getTPL()->get("absenzen/sekretariat/bearbeiten_dialog") . "\";");

    return $dialogHTML;
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
        /*array(
          'name' => "absenzen-attestnachdreitagen",
          'typ' => BOOLEAN,
          'titel' => "Attest nach 3 Tagen fordern?",
          'text' => "Soll ein Attest nach drei Tagen Abwesenheit gefordert werden?"
        ),*/
        array(
            'name' => "absenzen-meldungaktivieren",
            'typ' => BOOLEAN,
            'titel' => "Meldung im Sekretariat aktivieren?",
            'text' => "Dadurch wird es möglich einen Haken zu setzen, der anzeigt, ob der Klassentagebuchführer schon im Sekretariat gemeldet hat."
        ),
        [
            'name' => "absenzen-sekretariat-fotos-anzeigen",
            'typ' => BOOLEAN,
            'titel' => "Fotos der Schüler in der Sekretariatansicht aktivieren?",
            'text' => ""
        ],
        array(
            'name' => "absenzen-merkeraktivieren",
            'typ' => BOOLEAN,
            'titel' => "Merker im Sekretariat aktivieren?",
            'text' => "Mit dieser Option ist es möglich kleine Erinnerungen zu einzelnen Schülern zu setzen (für einen einzelnen Tag. z.B. 2. Pause Sekretariat)"
        ),
    	array(
    		'name' => "absenzen-schriftlicheentschuldigung-sek",
    		'typ' => BOOLEAN,
    		'titel' => "Anzeige Schriftlifliche Entschuldigung im Sekretariat aktivieren?",
    		'text' => "Sollen die Haken, ob eine schriftliche Entschuldigung vorliegt angezeigt werden?"
    	),
    	array(
    		'name' => "absenzen-generelleattestpflicht",
    		'typ' => BOOLEAN,
    		'titel' => "Generelle Attestpflicht aktivieren?",
    		'text' => ""
    	),
    	array(
    		'name' => "absenzen-fristabgabe-schriftliche-entschuldigung",
    		'typ' => 'NUMMER',
    		'titel' => "Frist zur Abgabe von schriftlichen Entschuldigungen",
    		'text' => "Wenn hier eine Zahl größer Null angegeben wird, können schriftliche Entschuldigungen nur innerhalb dieser Tagesfrist als abgegeben markiert werden."
    	) ,
        array(
            'name' => "absenzen-keine-schriftlichen-entschuldigungen",
            'typ' => 'BOOLEAN',
            'titel' => "Keine schriftlichen Entschuldigungen fordern?",
            'text' => "Wenn diese Option aktiv ist, dann werden keine schriftlichen Entschuldingen mehr gefordert."
        ),
        array(
            'name' => "absenzen-keine-schriftlichen-entschuldigungen-befreiungen",
            'typ' => 'BOOLEAN',
            'titel' => "Keine schriftlichen Entschuldigungen bei Befreiungen fordern?",
            'text' => "Wenn diese Option aktiv ist, dann werden keine schriftlichen Entschuldingen bei Befreiungen mehr gefordert. (Kein Rücklauf des Befreiungszettels mehr)"
        ),
        array(
            'name' => "absenzen-keine-schriftlichen-entschuldigungen-nur-portal",
            'typ' => 'BOOLEAN',
            'titel' => "Keine schriftlichen Entschuldigungen fordern? (Auf Portalentschuldigungen einschränken)",
            'text' => "Wenn diese Option aktiv ist, dann werden nur bei Krankmeldungen über das Portal keine schriftlichen Entschuldigungen mehr gefordert."
        ),
        [
            'name' => "krankmeldung-hinweis-lnw",
            'typ' => 'BOOLEAN',
            'titel' => "Attestpflicht bei angekkündigten LNW?",
            'text' => "Bei aktivierter Option erhalten die Eltern einen Hinweis, dass ein Attest benötigt wird, wenn da an dem Tag ein angekündigter Leistungsnachweis im Klassenkalender eingetragen ist."
        ],
        [
            'name' => "absenzen-no-duplicate",
            'typ' => 'BOOLEAN',
            'titel' => "In der Absenzenstatistik nur einen Tag pro Absenzentag zählen?",
            'text' => "Ist diese Option aktiv, dann wird nur ein Absenzentag pro Tag gezählt. Sind an einem Tag mehrere Absenzen eingetragen, wird nur ein Tag gezählt."
        ],
        [
            'name' => "absenzen-count-absenz-with-minimumhours",
            'typ' => 'NUMMER',
            'titel' => "Ab wie vielen Stunden Absenz soll ein Tag als absent zählen?",
            'text' => "Bei 0 oder kein Eintrag: Ab 1 Stunde Absenz; bei z.B. 5 werden Absenzen mit nur 1 bis 4 Stunden als nicht absenz gezählt."
        ]
    );
  }
  
  public static function getSchriftlicheEntschuldigungPolicy() {
      $text = "Für diese Absenzen müssen folgende Dokumente abgegeben werden:<br />";
      
      $text .= "<li><b>Für genehmigte Beurlaubungen:</b> Keine</li>";
      
      if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen-befreiungen')) {
          $text .= "<li><b>Für Befreiungen:</b> Keine</li>";
      }
      else {
          if(DB::getSettings()->getBoolean('absenzen-generelleattestpflicht')) {
              $text .= "<li><b>Für Befreiungen:</b> Ärztliches Attest und Rücklauf des Befreiungszettels</li>";
          }
          else {
            $text .= "<li><b>Für Befreiungen:</b> Unterschriebener Rücklauf der ausgestellten Befreiung</li>";
          }
      }
      
      if(DB::getSettings()->getBoolean('absenzen-generelleattestpflicht')) {
          $text .= "<li><b>Für alle Krankmeldungen:</b> Ärztliches Attest</li>";
      }
      else {
          if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen')) {
              if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen-nur-portal')) {
                  $text .= "<li><b>Für Krankmeldungen über das Portal:</b> Keine</li>";
                  $text .= "<li><b>Für telefonische Krankmeldungen:</b> Schriftliche Entschuldigung</li>";
              }
              else {
                  $text .= "<li><b>Für alle Krankmeldungen:</b> Keine</li>";
              }
              
          }
          else {
              $text .= "<li><b>Für alle Krankmeldungen:</b> Schriftliche Entschuldigung</li>";
          }
          
          
          if(DB::getSettings()->getBoolean('krankmeldung-hinweis-lnw')) $text .= "<li><b>Für Krankmeldungen, an denen ein Leistungsnachweis stattfand:</b> Ärztliches Attest</li>";
      }

     
      if(DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') > 0) {
          $text .= "<li><b>Endgültige Abgabefrist für Entschuldigungen und Atteste:</b> " . DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') . " Werktage</li>";
      }
      
      $text .= "</ul>";
      return $text;
  }
  
  private function processBeurlaubungen() {
      $beurlaubungen = AbsenzBeurlaubungAntrag::getGenehmigtNichtVerarbeitete();
      
      
      for($i = 0; $i < sizeof($beurlaubungen); $i++) {
          
          DB::getDB()->query("INSERT INTO absenzen_beurlaubungen (beurlaubungCreatorID, beurlaubungPrinted, beurlaubungIsInternAbwesend) values('" . DB::getSession()->getUserID() . "',1,0)");
          $beurlaubungID = DB::getDB()->insert_id();
          
          DB::getDB()->query("INSERT INTO absenzen_absenzen (
        absenzSchuelerAsvID,
        absenzDatum,
        absenzDatumEnde,
        absenzQuelle,
        absenzErfasstTime,
        absenzErfasstUserID,
        absenzStunden,
        absenzIsEntschuldigt,
        absenzBeurlaubungID,
        absenzBemerkung
        )
        values (
          '" . $beurlaubungen[$i]->getSchuelerAsvID() . "',
          '" . $beurlaubungen[$i]->getStartDatumAsSQLDate() . "',
          '" . $beurlaubungen[$i]->getEndDatumAsSQLDate() . "',
          'WEBPORTAL',
          UNIX_TIMESTAMP(),
          '" . DB::getUserID() . "',
          '" . implode(",",$beurlaubungen[$i]->getStunden()) . "',
          '1',
          '" . $beurlaubungID . "',
          'Online Beurlaubung:\r\n" . DB::getDB()->escapeString($beurlaubungen[$i]->getBegruendung()) . "\r\Bearbeitet durch " . DB::getSession()->getData("userName") . "'
        )");
          
          $beurlaubungen[$i]->setVerarbeitet();
          
      }
      
      header("Location: index.php?page=absenzensekretariat&currentDate=$currentDate&activeKlasse={$_GET['activeKlasse']}");
      exit(0);
  }

  public static function getSiteDisplayName() {
    return 'Sekretariatsansicht (Absenzen)';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array();
  }

  public static function siteIsAlwaysActive() {
    return false;
  }

  
  public static function getAdminGroup() {
  	return 'Webportal_Absenzen_Admin';
  }
  
  public static function dependsPage() {
  	return ['absenzenstatistik','absenzenberichte'];
  }
  
  public static function hasAdmin() {
  	return true;
  }
  
  public static function displayAdministration($selfURL) {
  	$html = "";
  	
  	$usergroup = usergroup::getGroupByName('Webportal_Absenzen_Sekretariat');
  	
  	if($_REQUEST['action'] == 'addUser') {
  		$usergroup->addUser($_POST['userID']);
  		header("Location: $selfURL&userAdded=1");
  		exit(0);
  		
  	}
  	
  	if($_REQUEST['action'] == 'removeUser') {
  		$usergroup->removeUser($_REQUEST['userID']);
  		header("Location: $selfURL&userDeleted=1");
  		exit(0);
  	}
  	
  	// Aktuelle Benutzer suchen, die Zugriff haben
  	 
  	$currentUserBlock = administrationmodule::getUserListWithAddFunction($selfURL, "sek", "addUser", "removeUser", "Benutzer mit Zugriff auf die Sekretariatsansicht", "Diese Benutzer haben Zugriff auf die komplette Sekretariatsansicht. (Vollzugriff)", 'Webportal_Absenzen_Sekretariat');

  	
  	eval("\$html = \"" . DB::getTPL()->get("absenzen/admin/index") . "\";");
  	
  	return $html;
  }
  
  public static function getAdminMenuGroup() {
  	return 'Absenzenverwaltung';
  }
  
  public static function getAdminMenuGroupIcon() {
  	return 'fa fas fa-procedures';
  }
  
  public static function userHasAccess($user) {
  	if($user->isAdmin()) return true;
  	
  	return $user->isMember("Webportal_Absenzen_Sekretariat");
  }

  public static function getActionSchuljahreswechsel() {
  	return 'Absenzen, Verspätungen, Attestpflichten, Befreiungen, Beurlaubungen, Krankmeldungen und Sanizimmer aus dem alten Schuljahr löschen';
  }

  public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
  	
  	$time = DateFunctions::getUnixTimeFromMySQLDate($sqlDateFirstSchoolDay);
  	
  	DB::getDB()->query("DELETE FROM absenzen_absenzen WHERE absenzDatum < '$sqlDateFirstSchoolDay'");
  	DB::getDB()->query("DELETE FROM absenzen_attestpflicht");
  	DB::getDB()->query("DELETE FROM absenzen_krankmeldungen WHERE krankmeldungDate < '$sqlDateFirstSchoolDay'");
  	DB::getDB()->query("DELETE FROM absenzen_verspaetungen WHERE verspaetungDate < '$sqlDateFirstSchoolDay'");
  	DB::getDB()->query("DELETE FROM absenzen_sanizimmer WHERE sanizimmerTimeStart < '$time'");
  	
  	
  }
}


?>
