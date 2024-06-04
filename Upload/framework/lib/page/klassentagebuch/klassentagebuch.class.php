<?php

class klassentagebuch extends AbstractPage {
  private $isTeacher = false;
  private $isPupil = false;
  private $isEltern = false;
  private $isAdmin = false;
  private $lehrerTagebuch = false;
  private $tagebuchActive = false;
  private $isOther = false;


  /**
   * Aktuelles Datum als SQLDate
   * @var string
   */
  private $currentDateSQL = "";


  /**
   * Aktuelles Datum als natürliches Datum
   * @var string
   */
  private $currentDateNatural = "";

  private $currentWeekDay = -1;


  /**
   * Klassen, die der Benutzer sehen darf
   * @var String[] Klassen
   */
  private $myGrades = [];

  private $currentStundenplan = null;


  /**
   *
   */
  public function __construct() {
    parent::__construct(['Klassentagebuch']);

    include_once("../framework/lib/data/absenzen/Absenz.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
    include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");


    $this->checkLogin();

    $this->isTeacher = DB::getSession()->isTeacher();
    $this->isPupil = DB::getSession()->isPupil();
    $this->isEltern = DB::getSession()->isEltern();
    $this->isAdmin = DB::getSession()->isMember("Webportal_Klassentagebuch_Admin");
    $this->isOther = false;


    if(!$this->isAdmin) $this->isAdmin = DB::getSession()->isAdmin();


    $this->lehrerTagebuch = DB::getSettings()->getBoolean("klassentagebuch-lehrertagebuch");

    $this->tagebuchActive = !DB::getSettings()->getBoolean("klassentagebuch-klassentagebuch-abschalten");


    if($this->isEltern && !DB::getSettings()->getBoolean("klassentagebuch-eltern-klassentagebuch")) {
      new errorPage();
    }

    if($this->isPupil && !DB::getSettings()->getBoolean("klassentagebuch-schueler-klassentagebuch")) {
      new errorPage();
    }

    if(($this->isEltern || $this->isPupil) && DB::getSettings()->getBoolean("klassentagebuch-klassentagebuch-abschalten")) {
        new errorPage();
    }

    if(!$this->isEltern && !$this->isTeacher && !$this->isPupil && !$this->isAdmin && !DB::getSession()->isMember("Webportal_Klassentagebuch_Lesen")) {
      new errorPage();
    }

    if(!$this->isEltern && !$this->isTeacher && !$this->isPupil && !$this->isAdmin && DB::getSession()->isMember("Webportal_Klassentagebuch_Lesen")) {
      $this->isOther = true;
    }

    if(!$this->lehrerTagebuch && !$this->tagebuchActive) new errorPage("Weder Klassentagebuch noch Lehrertagebuch sind aktiv.");

    if(isset($_REQUEST['currentDate'])) {
      if(DateFunctions::isNaturalDate($_REQUEST['currentDate'])) {
        $this->currentDateNatural = $_REQUEST['currentDate'];
        $this->currentDateSQL = DateFunctions::getMySQLDateFromNaturalDate($this->currentDateNatural);
      }
      else {
        $this->currentDateNatural = DateFunctions::getTodayAsNaturalDate();
        $this->currentDateSQL = DateFunctions::getTodayAsSQLDate();
      }
    }
    else {
      $this->currentDateNatural = DateFunctions::getTodayAsNaturalDate();
      $this->currentDateSQL = DateFunctions::getTodayAsSQLDate();
    }

    if($_REQUEST['action'] == "switchDate") {
      if($_REQUEST['prevDay'] > 0) {
        $this->currentDateSQL = DateFunctions::substractOneDayToMySqlDate($this->currentDateSQL);
        $this->currentDateNatural = DateFunctions::getNaturalDateFromMySQLDate($this->currentDateSQL);

        if($_REQUEST['mode'] == 'lehrerTagebuch') header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural);
        else header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
        exit(0);

      }

      elseif($_REQUEST['nextDay'] > 0) {
        $this->currentDateSQL = DateFunctions::addOneDayToMySqlDate($this->currentDateSQL);
        $this->currentDateNatural = DateFunctions::getNaturalDateFromMySQLDate($this->currentDateSQL);

        if($_REQUEST['mode'] == 'lehrerTagebuch') header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural);
        else header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
        exit(0);
      }
      else {

        $newDate = $_REQUEST['currentDateWithDay'];
        $newDate = explode(", ",$newDate)[1];
        if(DateFunctions::isNaturalDate($newDate)) {
          $this->currentDateNatural = $newDate;
          $this->currentDateSQL = DateFunctions::getMySQLDateFromNaturalDate($newDate);
          if($_REQUEST['mode'] == 'lehrerTagebuch') header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural);
          else header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
          exit(0);
        }
      }


    }



    $this->currentWeekDay = DateFunctions::getWeekDayFromNaturalDate($this->currentDateNatural);



    $this->currentStundenplan = stundenplandata::getStundenplanAtDate($this->currentDateSQL);

    if($this->currentStundenplan == null) {
      new Error("Das Klassentagebuch steht für diesen Temin nicht zur Verfügung, da kein Stundenplan für diesen Zeitpunkt hinterlegt ist.");
    }


    // Klassen zusammenstellen

    if($this->isTeacher) {
      $this->myGrades = $this->currentStundenplan->getAll("grade");
    }

    if($this->isPupil) {
      $this->myGrades = $this->currentStundenplan->getAllMyPossibleGrades(DB::getSession()->getPupilObject()->getKlasse());
    }

    if($this->isEltern) {
      $this->myGrades = $this->currentStundenplan->getStundenplanGradesFromNormalGrades(DB::getSession()->getElternObject()->getKlassenAsArray());
    }

    // Alle anderen dürfen alle Klassen sehen
    if($this->isAdmin || $this->isOther) {
      $this->myGrades = $this->currentStundenplan->getAll("grade");
    }
  }


  /**
   *
   * {@inheritDoc}
   * @see AbstractPage::execute()
   */
  public function execute() {
    switch($_REQUEST['mode']) {
      case 'showGrade':
        if($this->tagebuchActive) {
            if(in_array($_REQUEST['grade'], $this->myGrades)) {
              $this->showGrade($_REQUEST['grade']);
            }
            else {
              $this->showGrade(NULL);
            }
        }
        else {
            if($this->lehrerTagebuch && DB::getSession()->isTeacher()) {
                $this->showTeacher(DB::getSession()->getTeacherObject()->getKuerzel());
            }
            else {
                header("Location: index.php?page=aufeinenblick");
            }
        }
      break;

      case "addKlassentagebuchEntry":
        if(in_array($_REQUEST['grade'], $this->myGrades)) {
          $this->addKlassenEntry($_REQUEST['grade']);
        }
        else {
          new errorPage();
        }
      break;

      case "deleteKlassenbuchEntry":
        $this->deleteKlassentagebuchEntry();
      break;

      case "editKlassentagebuchEntry":
        $this->editKlassentagebuchEntry();
      break;

      case "addAbsenz":
        $this->addAbsenz();
      break;

      case 'addVerspaetung':
      	$this->addVerspaetung();
      break;

      case 'deleteVerspaetung':
      	$this->deleteVerspaetung();
      break;

      case 'deleteAbsenz':
      	$this->deleteAbsenz();
      break;

      case 'lehrerTagebuch':
      	if(DB::getSession()->isTeacher())
      		$this->showTeacher(DB::getSession()->getTeacherObject()->getKuerzel());
      	else new errorPage();
      break;

      case 'addTeacherEntry':
      	$this->addTeacherEntry();
      break;

      case 'exportLehrertagebuch':
         $this->exportLehrertagebuch();
      break;

      case 'exportTeacherBook':
        $this->exportTeacherBook();
      break;

      case 'editAbsenzStunden':
        $this->editAbsenzStunden();
      break;


      default:
        $this->showCurrentDateAndCurrentGrade();
      break;
    }
  }

  private function exportTacherBook() {
      $teacher = DB::getSession()->isTeacher() ? DB::getSession()->getTeacherObject() : null;

      if($teacher == null) new errorPage("Kein Lehrer.");

      // Alle Einträge des Lehrers in chronologischer Reihenfolge exportieren
  }

  private function editAbsenzStunden() {

      $absenz = Absenz::getByID($_REQUEST['absenzID']);
      if($absenz != null && $absenz->getDateAsSQLDate() == DateFunctions::getTodayAsSQLDate()) {
          $stunden = [];
          for($i = 1; $i <= stundenplandata::getMaxStunden(); $i++) {
              if($_REQUEST['stunde' . $i] > 0) {
                  $stunden[] = $i;
              }
          }

          $absenz->setStunden($stunden);

          if($_REQUEST['removeOffen'] > 0) {
              $absenz->setEntschuldigt();
          }
      }
      header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
      exit(0);
  }

  private function deleteAbsenz() {
  	$absenz = Absenz::getByID($_REQUEST['absenzID']);
  	if($absenz != null && $absenz->getDateAsSQLDate() == DateFunctions::getTodayAsSQLDate()) {
  		$absenz->delete();
  	}

  	header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
  	exit(0);
  }

  private function deleteVerspaetung() {
  	DB::getDB()->query("DELETE FROM absenzen_verspaetungen WHERE verspaetungID='" . intval($_GET['verspaetungID']) . "'");
  	header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
  	exit(0);
  }

  private function addVerspaetung() {
  	if($this->currentDateSQL == DateFunctions::getTodayAsSQLDate() || DB::isDebug()) {

  	    for($i = 0; $i < sizeof($_REQUEST['schuelerAsvID']); $i++) {


      		$schueler = schueler::getByAsvID($_POST['schuelerAsvID'][$i]);

      		if($schueler != null) {
      			DB::getDB()->query("INSERT INTO absenzen_verspaetungen (verspaetungSchuelerAsvID,verspaetungDate,verspaetungMinuten,verspaetungKommentar, verspaetungStunde) values('" . $schueler->getAsvID() . "','" . $this->currentDateSQL . "','" . intval($_POST['verspaetungMinuten']) . "','" . DB::getSession()->getUser()->getUserName() . (($_POST['verspaetungKommentar'] != "") ? (" - " . DB::getDB()->escapeString($_POST['verspaetungKommentar'])) : ("")) . "','" . DB::getDB()->escapeString($_POST['verspaetungStunde']) . "')");
      		}


  	    }


  	    header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
  	    exit(0);
  	}
  	else {
  		new errorPage();
  	}

  }

  private function addAbsenz() {
    if($this->currentDateSQL == DateFunctions::getTodayAsSQLDate()) {

      for($s = 0; $s < sizeof($_POST['schuelerAsvID']); $s++) {

        $schueler = null;

    	$schueler = schueler::getByAsvID($_POST['schuelerAsvID'][$s]);

    	if($schueler != null) {
    		$allStunden = "";

    		// Ab Stunde

            $abStunde = intval($_REQUEST['abStunde']);

            $stunden = [];

    		for($i = 1; $i <= stundenplandata::getMaxStunden(); $i++) {
    		    if($i >= $abStunde) $stunden[] = $i;
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
              '" . DB::getDB()->escapeString($schueler->getAsvID()) . "',
              '" . DateFunctions::getTodayAsSQLDate() . "',
              '" . DateFunctions::getTodayAsSQLDate() . "',
              'LEHRER',
              UNIX_TIMESTAMP(),
              '" . DB::getUserID() . "',
              '" . implode(",",$stunden) . "',
              '0',
              '0',
              'Meldung durch Lehrer (" . DB::getSession()->getTeacherObject()->getKuerzel() . ") aus dem Unterricht (per elektronischem Klassentagebuch)\r\n\r\n'
            )");

    	}
      }

      header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
      exit(0);

    }
    else {
      new errorPage("Es kann nur für den aktuellen Tag die Absenz hinzugefügt werden!");
    }

  }

  private function editKlassentagebuchEntry() {
    if($this->isTeacher) {
      $entry = TagebuchKlasseEntry::getEntryByID($_GET['entryID']);
      if($entry == null) new errorPage();
      if($entry->getTeacher() != DB::getSession()->getTeacherObject()->getKuerzel()) {
        new errorPage();
      }

      $entry->updateIsVertretung($_POST['isVertretung'] > 0);
      $entry->updateFach(DB::getDB()->escapeString($_POST['fach']));
      $entry->updateStoff(DB::getDB()->escapeString($_POST['stoff']));
      $entry->updateHausaufgaben(DB::getDB()->escapeString($_POST['hausaufgaben']));
      $entry->updateNotizen(DB::getDB()->escapeString($_POST['notizen']));

      // Dateien hinzufügen ?

      $privateFiles = $entry->getPrivateFiles();
      $publicFiles = $entry->getPublicFiles();


      for($i = 1; $i <= 3; $i++) {
          $upload = FileUpload::uploadOfficePdfOrPicture('filesPublic' . $i, $_FILES['filesPublic' . $i]['name']);


          if($upload['result']) {
              $publicFiles[] = $upload['uploadobject'];
          }

          $upload2 = FileUpload::uploadOfficePdfOrPicture('filesPrivate' . $i, $_FILES['filesPrivate' . $i]['name']);


          if($upload2['result']) {
              $privateFiles[] = $upload2['uploadobject'];
          }

      }

      $entry->updatePrivateFiles($privateFiles);
      $entry->updatePublicFiles($publicFiles);


      if($_GET['returnTo'] == 'teacher') header("Location:index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural);
      else header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
    }
    else {
      new errorPage();
    }
  }

  private function deleteKlassentagebuchEntry() {
    if($this->isTeacher) {
      $entry = TagebuchKlasseEntry::getEntryByID($_GET['entryID']);
      if($entry == null) new errorPage();
      if($entry->getTeacher() != DB::getSession()->getTeacherObject()->getKuerzel()) {
        new errorPage();
      }

      $entry->delete();

      if($_GET['returnTo'] == 'teacher') header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural);
      else header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
    }
    else {
      new errorPage();
    }
  }

  private function addKlassenEntry() {
    if($this->isTeacher) {

      $stundenAdd = [];
      for($i = 0; $i < sizeof($_POST['stunden']); $i++) {
      	if(intval($_POST['stunden'][$i]) > 0 && intval($_POST['stunden']) <= stundenplandata::getMaxStunden()) {
      		$stundenAdd[] = intval($_POST['stunden'][$i]);
      	}
      }


      $privateFiles = [];
      $publicFiles = [];


      for($i = 1; $i <= 3; $i++) {
      	$upload = FileUpload::uploadOfficePdfOrPicture('filesPublic' . $i, $_FILES['filesPublic' . $i]['name']);


      	if($upload['result']) {
      		$publicFiles[] = $upload['uploadobject']->getID();
      	}

      	$upload2 = FileUpload::uploadOfficePdfOrPicture('filesPrivate' . $i, $_FILES['filesPrivate' . $i]['name']);


      	if($upload2['result']) {
      		$privateFiles[] = $upload2['uploadobject']->getID();
      	}

      }


      if(sizeof($stundenAdd) > 0) {
      	// Debugger::debugObject($_POST,1);
      	TagebuchKlasseEntry::createEntry(
      			$_REQUEST['grade'],
      			$this->currentDateSQL,
      			$stundenAdd,
      			$_POST['fach'],
      			$_POST['stoff'],
      			DB::getSession()->getTeacherObject()->getKuerzel(),
      			$_POST['hausaufgaben'],
      			$_POST['isEntfall'] > 0,
      			$_POST['isVertretung'] > 0,
      			$_POST['notizen'],
      			$publicFiles,
      			$privateFiles
      	);
      }

      if($_POST['goBackURL'] != "") {
          header("Location: " . $_POST['goBackURL']);
          exit(0);
      }

      header("Location: index.php?page=klassentagebuch&mode=showGrade&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural);
      exit(0);
    }
    else {
      new errorPage();
    }
  }

  private function exportLehrertagebuch() {
      if($this->isTeacher) {
          if(DB::getSettings()->getBoolean("lehrertagebuch-export-antrag-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID()) > 0) {
              new errorPage("Antrag noch nicht ausgeführt.");
          }
          else if(DB::getSettings()->getValue("lehrertagebuch-export-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID()) != "") {
              if($_REQUEST['reRequest'] > 0) {
                  $upload = FileUpload::getByID(DB::getSettings()->getValue("lehrertagebuch-export-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID()));
                  if($upload != null) {
                      $upload->delete();
                  }
                  DB::getSettings()->setValue("lehrertagebuch-export-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID(),"");
                  DB::getSettings()->setValue("lehrertagebuch-export-antrag-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID(), 1);

                  header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch");
                  exit(0);
              }
              else {
                  $upload = FileUpload::getByID(DB::getSettings()->getValue("lehrertagebuch-export-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID()));
                  if($upload != null) {
                      $upload->sendFile();
                  }
                  else {
                      DB::getSettings()->setValue("lehrertagebuch-export-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID(),"");
                      DB::getSettings()->setValue("lehrertagebuch-export-antrag-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID(), 1);
                      new errorPage("Interner Fehler. Export wird neu erzeugt.");
                  }
              }
          }
      }
  }

  private function addTeacherEntry() {
  	if($this->isTeacher) {
  		$stundenAdd = [];
  		for($i = 0; $i < sizeof($_POST['stunden']); $i++) {
  			if(intval($_POST['stunden'][$i]) > 0 && intval($_POST['stunden']) <= stundenplandata::getMaxStunden()) {
  				$stundenAdd[] = intval($_POST['stunden'][$i]);
  			}
  		}

  		$privateFiles = [];
  		$publicFiles = [];


  		for($i = 1; $i <= 3; $i++) {
  			$upload = FileUpload::uploadOfficePdfOrPicture('filesPublic' . $i, $_FILES['filesPublic' . $i]['name']);


  			if($upload['result']) {
  				$publicFiles[] = $upload['uploadobject']->getID();
  			}

  			$upload2 = FileUpload::uploadOfficePdfOrPicture('filesPrivate' . $i, $_FILES['filesPrivate' . $i]['name']);


  			if($upload2['result']) {
  				$privateFiles[] = $upload2['uploadobject']->getID();
  			}

  		}


  		if(sizeof($stundenAdd) > 0) {
  			for($i = 0; $i < sizeof($_REQUEST['klassen']); $i++) {
  				TagebuchKlasseEntry::createEntry(
  						$_REQUEST['klassen'][$i],
  						$this->currentDateSQL,
  						$stundenAdd,
  						$_POST['fach'],
  						$_POST['stoff'],
  						DB::getSession()->getTeacherObject()->getKuerzel(),
  						$_POST['hausaufgaben'],
  						$_POST['isEntfall'] > 0,
  						$_POST['isVertretung'] > 0,
  						$_REQUEST['notizen'],
  						$publicFiles,$privateFiles
  				);
  			}
  		}

  		header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural);
  		exit(0);
  	}
  	else {
  		new errorPage();
  	}
  }


  private function showCurrentDateAndCurrentGrade() {
    if($this->isTeacher) {

      if($this->tagebuchActive) {
          $currentStunde = stundenplan::getCurrentStunde();

          if($currentStunde > 0)  {
          	$aktuelleStunden = $this->currentStundenplan->getStundenAtDayAndStundeForTeacher(date("N"), $currentStunde, DB::getSession()->getTeacherObject()->getKuerzel());
          }
          else $aktuelleStunden = [];

          if(sizeof($aktuelleStunden) > 0) {
            $this->showGrade($aktuelleStunden[0]['grade']);
            exit(0);
          }
          else {
            /*if($this->lehrerTagebuch) {
              header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch");
              exit(0);
            }
            else {*/
              header("Location: index.php?page=klassentagebuch&mode=showGrade&grade={$this->myGrades[0]}");
              exit(0);
            //}
          }
      }
      else {
          header("Location: index.php?page=klassentagebuch&mode=lehrerTagebuch");
          exit(0);
      }
    }

    if($this->tagebuchActive) $this->showGrade($this->myGrades[0]);
    header("Location: index.php?page=aufeinenblick");
  }

  private function showTeacher($lehrer) {
    $tableContent = "";

    $stundenplanData = $this->currentStundenplan->getPlan(['teacher',$lehrer]);


    $stundenplanData = $stundenplanData[$this->currentWeekDay-1];

    // Debugger::debugObject($stundenplanData,1);


    $teacherEntrys = TagebuchKlasseEntry::getAllForDateAndTeacher($this->currentDateSQL, $lehrer);

    // Debugger::debugObject($teacherEntrys,1);


    $dialogID = 0;

    for($i = 1; $i <= stundenplandata::getMaxStunden(); $i++) {

    	if( sizeof((array)$stundenplanData[$i-1]) > 0) {
            $tableContent .= "<tr><td rowspan=\"" . sizeof($stundenplanData[$i-1]) . "\">" . $i . "</td>";
        } else {
            $tableContent .= "<tr><td>" . $i . "</td>";
        }

      /** $entries = [];
      // Eintrag suchen
      for($e = 0; $e < sizeof($teacherEntrys); $e++) {
        if($teacherEntrys[$e]->getStunde() == $i) {
          $entries[] = $teacherEntrys[$e];
        }
      } **/

      $klassenAtStunde = [];

      for($s = 0; $s < sizeof((array)$stundenplanData[$i-1]); $s++) {
      	$klassenAtStunde[] = $stundenplanData[$i-1][$s]['grade'] ;
      }

      for($s = 0; $s < sizeof((array)$stundenplanData[$i-1]); $s++) {
        if($s > 0) {
        	$tableContent .= "</tr><tr>";
        }

        $tableContent .= "<td>";

        if($this->tagebuchActive) $tableContent .= "<a href=\"index.php?page=klassentagebuch&mode=showGrade&grade=" . $stundenplanData[$i-1][$s]['grade'] . "&currentDate=" . $this->currentDateNatural . "\">";
        $tableContent .=  $stundenplanData[$i-1][$s]['grade'];

        if($this->tagebuchActive) $tableContent .= "</a> ";

        $tableContent .= "(" . $stundenplanData[$i-1][$s]['subject'] . ")";

        $tableContent .= "<br /><button type=\"button\" class=\"btn btn-sm\" data-toggle=\"modal\" data-target=\"#history$dialogID\"><i class=\"fa fa-clock\"></i></button>";

        $grade = $stundenplanData[$i-1][$s]['grade'];
        $fach = $stundenplanData[$i-1][$s]['subject'];

        $history = $this->getHistory($grade, $fach);

        eval("\$dialogs .= \"" . DB::getTPL()->get("klassentagebuch/history_dialog") . "\";");

        $dialogID++;

        if($s == 0) {   // Nur in der ersten Zeile anzeigen

        	if(sizeof((array)$stundenplanData[$i-1]) == 0) $tableContent .= "<td>";

        	else $tableContent .= "<td rowspan=\"" . sizeof((array)$stundenplanData[$i-1]) . "\">";

        	$stunden = [$i];

        	for($n = $i+1; $n <= stundenplandata::getMaxStunden(); $n++) {
        		if($stundenplanData[$i-1] == $stundenplanData[$n] && $stundenplanData[$n]['subject'] != '') $stunden[] = $n;
        		else break;
        	}

        	$hasEntry = false;
        	for($e = 0; $e < sizeof((array)$teacherEntrys); $e++) {
        	    if($teacherEntrys[$e]->getStunde() == $i) {
        			if($hasEntry) $tableContent .= "<hr noshade=\"noshade\">";

        			$tableContent .= $this->getTeacherEntry($teacherEntrys[$e]);

        			$hasEntry = true;
        		}
        	}

        	if($hasEntry) $tableContent .= "<hr noshade=\"noshade\">";

        	$tableContent .= "<form><button type=\"button\" class=\"btn btn-success btn-sm\" data-toggle=\"modal\" data-target=\"#addentry\" onclick=\"javascript:addentry('" . implode("#",$stunden) . "','" . $stundenplanData[$i-1][$s]['subject'] . "','" . (($lehrer != DB::getSession()->getTeacherObject()->getKuerzel()) ? 1 : 0) . "','" . implode("#",$klassenAtStunde) . "')\"><i class=\"fa fa-plus\"></i> Eintrag hinzufügen</button></form>";



        	$tableContent .= "</td>";
        }

      }

      if(sizeof((array)$stundenplanData[$i-1]) == 0) {
      	$tableContent .= "<td>&nbsp;</td>";
      	$tableContent .= "<td>";

      	$hasEntry = false;
      	for($e = 0; $e < sizeof((array)$teacherEntrys); $e++) {
      	    if($teacherEntrys[$e]->getStunde() == $i) {
      	        if($hasEntry) $tableContent .= "<hr noshade=\"noshade\">";

      	        $tableContent .= $this->getTeacherEntry($teacherEntrys[$e]);

      	        $hasEntry = true;
      	    }
      	}


      	$tableContent .= "<form><button type=\"button\" class=\"btn btn-success btn-sm\" data-toggle=\"modal\" data-target=\"#addentry\" onclick=\"javascript:addentry('" . $i . "','',0,'')\"><i class=\"fa fa-plus\"></i> Eintrag hinzufügen</button></form>";



      	$tableContent .= "&nbsp;</td>";

      }





      $tableContent .= "</tr>";


    }



    $optionsStunden = "";

    for($i = 1; $i <= stundenplandata::getMaxStunden(); $i++) {
    	$optionsStunden .= "<option value=\"" . $i . "\">" . $i . ". Stunde</option>";
    }

    $optionsGrade = "";

    $grades = $this->currentStundenplan->getAll("grade");

    for($i = 0; $i < sizeof($grades); $i++) {
    	$optionsGrade.= "<option value=\"" . $grades[$i]. "\">" . $grades[$i]. "</option>";
    }


    $selectFach = "";

    $subjects = $this->currentStundenplan->getAll("subject");

    $mySubjects = $this->currentStundenplan->getAllSubjectsForTeacher(DB::getSession()->getTeacherObject()->getKuerzel());


    $selectFach .= "<optgroup label=\"Meine Fächer\">";
    for($i = 0; $i < sizeof($mySubjects); $i++) {
        $selectFach .= "<option value=\"" . $mySubjects[$i] . "\">" . $mySubjects[$i] . "</option>";
    }
    $selectFach .= "</optgroup>";

    $selectFach .= "<optgroup label=\"Andere Fächer\">";
    for($i = 0; $i < sizeof($subjects); $i++) {
        if(!in_array($subjects[$i], $mySubjects)) $selectFach .= "<option value=\"" . $subjects[$i] . "\">" . $subjects[$i] . "</option>";
    }
    $selectFach .= "</optgroup>";


    // Export

      $exportAvailible = DB::getSettings()->getValue("lehrertagebuch-export-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID())  > 0;

      $exportPending = DB::getSettings()->getBoolean("lehrertagebuch-export-antrag-" . DB::getSession()->getUser()->getTeacherObject()->getAsvID());

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/teacher") . "\");");
  }

  /**
   *
   * @param TagebuchKlasseEntry $entry
   * @return string
   */
  private function getTeacherEntry($entry) {
  	$tableContent = "";

  	$dialogs = '';

  	if($entry->isAusfall()) {
  		$tableContent .= ($entry->getFach() != "" ?  $entry->getFach() : "<i>Kein Fach</i>") . " - <font color=\"red\">Entfallen</font> (" . $entry->getTeacher() . ")<br />";
  	}
  	else {
  		$tableContent .= "<b>" . $entry->getGrade() . " - </b>";
  		$tableContent .= "<b>" . ($entry->getFach() != "" ?  $entry->getFach() : "<i>Kein Fach</i>") . "</b>";
  		if($entry->isVertretung()) $tableContent .= " <i>Vertretung</i>";
  		$tableContent .= "<br /><i class=\"fa fas fa-pencil-alt-square\"></i> " . ($entry->getStoff() != "" ? $entry->getStoff() : ("<i>Kein Stoff angegeben</i>"));

  		if($entry->getHausaufgabe() != "") {
  			$tableContent .= "<br /><i class=\"fa fa-home\"></i> " . $entry->getHausaufgabe();
  		}
  		else {
  			$tableContent .= "<br /><i class=\"fa fa-home\"></i> <i>Keine Hausaufgaben - Grundwissen wiederholen & auf die nächste Stunde vorbereiten</i>";
  		}

  		$privateFiles = $entry->getPrivateFiles();
  		$publicFiles = $entry->getPublicFiles();

  		if(sizeof($privateFiles) > 0 && $entry->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
  			$tableContent .= "<br /><b>Private Dateianhänge:</b> <br />";

  			for($i = 0; $i < sizeof($privateFiles); $i++) {

  			    if($entry->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel() && $_REQUEST['deleteFile'] == $privateFiles[$i]->getID()) {

                    $entry->removePrivateFile($privateFiles[$i]->getID());

                    $privateFiles[$i]->delete();

                    $tableContent .= "<s>" . $privateFiles[$i]->getFileName() . "</s> - Wurde gelöscht";

  			    }
  			    else {


      				$tableContent .= "<a href=\"" . $privateFiles[$i]->getURLToFile() . "\"><i class=\"fa fa-download\"></i> " . $privateFiles[$i]->getFileName() . "</a> ";
      				if($entry->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
      				    $tableContent .= "<button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"javasript:confirmAction('Soll die Datei wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural . "&deleteFile=" . $privateFiles[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i></button>";
      				}

  			    }
  				$tableContent .= "<br />";

  			}


  		}

  		if(sizeof($publicFiles) > 0) {
  			$tableContent .= "<br /><b>Dateien / Arbeitsblätter:</b> <br />";

  			for($i = 0; $i < sizeof($publicFiles); $i++) {


  			    if($entry->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel() && $_REQUEST['deleteFile'] == $publicFiles[$i]->getID()) {

  			        $entry->removePublicFile($publicFiles[$i]->getID());

  			        $publicFiles[$i]->delete();

  			        $tableContent .= "<s>" . $publicFiles[$i]->getFileName() . "</s> - Wurde gelöscht";

  			    }
  			    else {


  				$tableContent .= "<a href=\"" . $publicFiles[$i]->getURLToFile() . "\"><i class=\"fa fa-download\"></i> " . $publicFiles[$i]->getFileName() . "</a> ";

  				if($entry->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
  				    $tableContent .= "<button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"javasript:confirmAction('Soll die Datei wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=lehrerTagebuch&currentDate=" . $this->currentDateNatural . "&deleteFile=" . $publicFiles[$i]->getID() . "')\"><i class=\"fa fa-trash\"></i></button>";
  				}

  			    }
  				$tableContent .= "<br />";
  			}
  		}


  		if($entry->getNotizen() != "") {
  			$tableContent .= "<pre>";
  			$tableContent .= $entry->getNotizen();
  			$tableContent .= "</pre>";
  		}

  	}



  	if($entry->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
  		$tableContent .= "<br />";

  		if(!$entry->isAusfall()) $tableContent .= "<button type=\"button\" class=\"btn btn-xs\" data-toggle=\"modal\" data-target=\"#editentry{$entry->getID()}\"><i class=\"fa fas fa-pencil-alt\"></i> Eintrag bearbeiten</button> " ;
  		$tableContent .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"confirmAction('Soll der Eintrag wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=deleteKlassenbuchEntry&entryID=" . $entry->getID() . "&returnTo=teacher&currentDate=" . $this->currentDateNatural . "')\"><i class=\"fa fa-trash\"></i> Eintrag löschen</button> " ;


  		$selectFach = "";

  		$subjects = $this->currentStundenplan->getAll("subject");

  		for($g = 0; $g < sizeof($subjects); $g++) {
  			$selectFach .= "<option value=\"" . $subjects[$g] . "\"" . (($entry->getFach() == $subjects[$g]) ? (" selected") : ("")) . ">" . $subjects[$g] . "</option>";
  		}

  		if(!$entry->isAusfall()) eval("\$dialogs .= \"" . DB::getTPL()->get("klassentagebuch/teacher_edit_dialog") . "\";");


  	}

  	$tableContent .= $dialogs;

  	$tableContent .= "\r\n";

  	return $tableContent;
  }

  private function showGrade($grade) {
    $canEdit = false;

    $canEdit = $this->isTeacher;		// Nur Lehrer dürfen eintragen

    if(sizeof($this->myGrades) > 1) {
      $selectGrade = "";
      for($i = 0; $i < sizeof($this->myGrades); $i++) {
        $selectGrade .= "<option value=\"" . $this->myGrades[$i] . "\"" . (($grade == $this->myGrades[$i]) ? (" selected=\"selected\"") : ("")) . ">" . $this->myGrades[$i] . "</option>";
      }
    }

    if($grade == null || $grade == "") {
    	if(sizeof($this->myGrades) > 0) {
    		header("Location: index.php?page=klassentagebuch&mode=showGrade&currentDate=" . $this->currentDateNatural . "&grade=" . $this->myGrades[0]);
    		exit(0);
    	}
      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/noklasse") . "\");");
      PAGE::kill(true);
			//exit(0);
    }

    $stundenplan = $this->currentStundenplan->getPlan(['grade',$grade])[$this->currentWeekDay-1];
    $entries = TagebuchKlasseEntry::getAllForDateAndGrade($this->currentDateSQL, $grade);


    $selectFach = "";

    $subjects = $this->currentStundenplan->getAll("subject");

    if(DB::getSession()->isTeacher()) {

        $mySubjects = $this->currentStundenplan->getAllSubjectsForTeacher(DB::getSession()->getTeacherObject()->getKuerzel());


        $selectFach .= "<optgroup label=\"Meine Fächer\">";
        for($i = 0; $i < sizeof($mySubjects); $i++) {
            $selectFach .= "<option value=\"" . $mySubjects[$i] . "\">" . $mySubjects[$i] . "</option>";
        }
        $selectFach .= "</optgroup>";

        $selectFach .= "<optgroup label=\"Andere Fächer\">";
        for($i = 0; $i < sizeof($subjects); $i++) {
            if(!in_array($subjects[$i], $mySubjects)) $selectFach .= "<option value=\"" . $subjects[$i] . "\">" . $subjects[$i] . "</option>";
        }
        $selectFach .= "</optgroup>";

    }

    $tableContent = "";

      if ( $this->currentDateSQL == date('Y-m-d',time()) ) {
          $aktuelleStunde = stundenplan::getCurrentStunde();
          //echo 'aktuelleStunde: '.$aktuelleStunde.'<br>';
      }

    $dialogs = "";

    $dialogID = 1;

      $stundenSelectNewAbsenz = "";

    for($i = 0; $i < stundenplandata::getMaxStunden(); $i++) {

        $stundenSelectNewAbsenz .= "<option value=\"" . ($i+1) . "\">Ab " . ($i+1) . ". Stunde</option>";

      $tableContent .= "<tr" . ((($i+1) == $aktuelleStunde) ? (" style=\"background-color: lightgreen\"") : ("")) . "><td>" . ($i+1) ."</td>";

      $tableContent .= "<td>";

      $fach = "";
      $lehrer = "";
      $myFach = "";


      $alleLehrerDerStunde = [];

      if ($stundenplan[$i]) {
          for($s = 0; $s < count($stundenplan[$i]); $s++) {
            if($s > 0) $tableContent .= "<br />";
            $tableContent .= "<b>" . $stundenplan[$i][$s]['subject'] . "</b> (" . $stundenplan[$i][$s]['teacher'] . ")";
            $tableContent .= "<br />" . $stundenplan[$i][$s]['room'];
            if($canEdit) $tableContent .= "<br /><button type=\"button\" class=\"btn btn-sm\" data-toggle=\"modal\" data-target=\"#history$dialogID\"><i class=\"fa fa-clock\"></i></button>";
            $fach = $stundenplan[$i][$s]['subject'];
            $lehrer = $stundenplan[$i][$s]['teacher'];

            $alleLehrerDerStunde[] = strtolower($lehrer);

            if($this->isTeacher && $lehrer == DB::getSession()->getTeacherObject()->getKuerzel()) $myFach = $fach;


            if($canEdit) $history = $this->getHistory($grade, $fach);

            eval("\$dialogs .= \"" . DB::getTPL()->get("klassentagebuch/history_dialog") . "\";");

            $dialogID++;

          }
      }

      if($myFach != "") $fach = $myFach;



      $tableContent .= "</td>";

      $tableContent .= "<td>";

      $hasEntry = false;
      for($e = 0; $e < sizeof($entries); $e++) {
        if($entries[$e]->getStunde() == ($i+1) && $entries[$e]->showEntryNow()) {
          if($hasEntry) $tableContent .= "<hr noshade=\"noshade\">";

          if($entries[$e]->isAusfall()) {
            $tableContent .= ($entries[$e]->getFach() != "" ?  $entries[$e]->getFach() : "<i>Kein Fach</i>") . " - <font color=\"red\">Entfallen</font> (" . $entries[$e]->getTeacher() . ")<br />";
          }
          else {
            if($entries[$e]->getGrade() != $grade) $tableContent .= "<b>" . $entries[$e]->getGrade() . "</b> - ";   // Kopplungklasse anzeigen
            $tableContent .= "<b>" . ($entries[$e]->getFach() != "" ?  $entries[$e]->getFach() : "<i>Kein Fach</i>") . "</b> (" . $entries[$e]->getTeacher() . ")";
            if($entries[$e]->isVertretung()) $tableContent .= " <i>Vertretung</i>";
            $tableContent .= "<br /><i class=\"fa fas fa-pencil-alt-square\"></i> " . ($entries[$e]->getStoff() != "" ? $entries[$e]->getStoff() : ("<i>Kein Stoff angegeben</i>"));

            if($entries[$e]->getHausaufgabe() != "") {
              $tableContent .= "<br /><i class=\"fa fa-home\"></i> " . $entries[$e]->getHausaufgabe();
            }
            else {
              $tableContent .= "<br /><i class=\"fa fa-home\"></i> <i>Keine Hausaufgaben - Grundwissen wiederholen & auf die nächste Stunde vorbereiten</i>";
            }
          }

          $privateFiles = $entries[$e]->getPrivateFiles();
          $publicFiles = $entries[$e]->getPublicFiles();

          if(sizeof($privateFiles) > 0 && DB::getSession()->isTeacher() && $entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
          	$tableContent .= "<br /><b>Private Dateianhänge: (Nur " . DB::getSession()->getTeacherObject()->getKuerzel() . ")</b> <br />";
          	for($f = 0; $f < sizeof($privateFiles); $f++) {
          	    if($entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel() && $_REQUEST['deleteFile'] == $privateFiles[$f]->getID()) {

          	        $entries[$e]->removePrivateFile($privateFiles[$f]->getID());

          	        $privateFiles[$f]->delete();

          	        $tableContent .= "<s>" . $privateFiles[$f]->getFileName() . "</s> - Wurde gelöscht";

          	    }
          	    else {


          	        $tableContent .= "<a href=\"" . $privateFiles[$f]->getURLToFile() . "\"><i class=\"fa fa-download\"></i> " . $privateFiles[$f]->getFileName() . "</a> ";
          	        if($entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
          	            $tableContent .= "<button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"javasript:confirmAction('Soll die Datei wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=showGrade&grade=" . $grade . "&currentDate=" . $this->currentDateNatural . "&deleteFile=" . $privateFiles[$f]->getID() . "')\"><i class=\"fa fa-trash\"></i></button>";
          	        }

          	    }
          	    $tableContent .= "<br />";
          	}
          }

          if(sizeof($publicFiles) > 0) {
          	$tableContent .= "<br /><b>Dateien / Arbeitsblätter:</b> <br />";
          	for($f = 0; $f < sizeof($publicFiles); $f++) {
          	    if(DB::getSession()->isTeacher() && $entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel() && $_REQUEST['deleteFile'] == $publicFiles[$f]->getID()) {

          	        $entries[$e]->removePrivateFile($publicFiles[$f]->getID());

          	        $publicFiles[$f]->delete();

          	        $tableContent .= "<s>" . $publicFiles[$f]->getFileName() . "</s> - Wurde gelöscht";

          	    }
          	    else {


          	        $tableContent .= "<a href=\"" . $publicFiles[$f]->getURLToFile() . "\"><i class=\"fa fa-download\"></i> " . $publicFiles[$f]->getFileName() . "</a> ";
          	        if(DB::getSession()->isTeacher() && $entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
          	            $tableContent .= "<button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"javasript:confirmAction('Soll die Datei wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=showGrade&grade=" . $grade . "&currentDate=" . $this->currentDateNatural . "&deleteFile=" . $publicFiles[$f]->getID() . "')\"><i class=\"fa fa-trash\"></i></button>";
          	        }

          	    }
          	    $tableContent .= "<br />";}
          }



          if($entries[$e]->getNotizen() != "" && DB::getSession()->isTeacher() && $entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
          	$tableContent .= "<br /><pre>";
          	$tableContent .= $entries[$e]->getNotizen();
          	$tableContent .= "</pre>";
          }



          if(DB::getSession()->isTeacher() && $entries[$e]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
            $tableContent .= "<br />";

            if(!$entries[$e]->isAusfall()) $tableContent .= "<button type=\"button\" class=\"btn btn-xs\" data-toggle=\"modal\" data-target=\"#editentry{$entries[$e]->getID()}\"><i class=\"fa fas fa-pencil-alt\"></i> Eintrag bearbeiten</button> " ;
            $tableContent .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"confirmAction('Soll der Eintrag wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=deleteKlassenbuchEntry&entryID=" . $entries[$e]->getID() . "&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural . "')\"><i class=\"fa fa-trash\"></i> Eintrag löschen</button> " ;


            $selectFach = "";

            $subjects = $this->currentStundenplan->getAll("subject");

            for($g = 0; $g < sizeof($subjects); $g++) {
            	$selectFach .= "<option value=\"" . $subjects[$g] . "\"" . (($entries[$e]->getFach() == $subjects[$g]) ? (" selected") : ("")) . ">" . $subjects[$g] . "</option>";
            }


            if(!$entries[$e]->isAusfall()) eval("\$dialogs .= \"" . DB::getTPL()->get("klassentagebuch/klasse_edit_dialog") . "\";");


          }

          $tableContent .= "\r\n";

          $hasEntry = true;
        }
      }

      if($hasEntry) $tableContent .= "<hr noshade=\"noshade\">";

     if($canEdit) {

     	// Aktuelle Stunde auswählen
     	$stunden = [$i+1];



     	// Nächsten Stunden ebenfalls gleiches Fach?
     	for($n = $i+1; $n < stundenplandata::getMaxStunden(); $n++) {

     		if($stundenplan[$i] == $stundenplan[$n] && $stundenplan[$i][0]['teacher'] != '') $stunden[] = $n+1;
     		else break;
     	}


     	$tableContent .= "<form><button type=\"button\" class=\"btn btn-success btn-sm\" data-toggle=\"modal\" data-target=\"#addentry\" onclick=\"javascript:addentry('" . implode("#",$stunden) . "','" . $fach . "','" . (!in_array(strtolower(DB::getSession()->getTeacherObject()->getKuerzel()),$alleLehrerDerStunde) ? 1 : 0) . "')\"><i class=\"fa fa-plus\"></i> Eintrag hinzufügen</button></form>";
     }

      $tableContent .= "</td></tr>";
    }

      $klasse = klasse::getByStundenplanName($grade);

    if($canEdit) {		// Genauere Informationen für Lehrer anzeigen

    	// Klassenliste und Select für Krankmeldung

	    $klassenliste = "";



	    $schueler = $klasse->getSchueler(false);

	    $schuelerSelect = "";

	    for($i = 0; $i < sizeof($schueler); $i++) {
	      $klassenliste .= "<tr><td>" . ($i+1) . "</td><td>" . $schueler[$i]->getCompleteSchuelerName() . "</td><td>" . $schueler[$i]->getGeburtstagAsNaturalDate() . " (" . $schueler[$i]->getAlter() . " Jahre)</tr>";

	      $schuelerSelect .= "<option value=\"" . $schueler[$i]->getAsvID() . "\">" . $schueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
	    }


	    $krankmeldungenTH = "<th>Stunden</th>";



	    // Verspätungen

	    $verspaetungenHTML = "";

	    $vSQL = DB::getDB()->query("SELECT * FROM absenzen_verspaetungen LEFT JOIN schueler ON verspaetungSchuelerAsvID=schuelerAsvID WHERE verspaetungDate='" . $this->currentDateSQL . "' AND schuelerKlasse LIKE '" . $klasse->getKlassenName() . "'");
	    while($v = DB::getDB()->fetch_array($vSQL)) {
	    	$verspaetungenHTML .= $v['schuelerName'] . ", " . $v['schuelerRufname'] . " - " . $v['verspaetungMinuten'] . " Minuten zur " . $v['verspaetungStunde'] . ". Stunde " . (($v['verspaetungKommentar'] != "") ? (" (" .$v['verspaetungKommentar'] . ")") : (""));
	    	$verspaetungenHTML .= "<button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"javascript:confirmAction('Verspätung wirklich löschen?','index.php?page=klassentagebuch&mode=deleteVerspaetung&grade={$_REQUEST['grade']}&currentDate={$this->currentDateNatural}&verspaetungID={$v['verspaetungID']}')\"><i class=\"fa fa-trash\"></i></button><br />";
	    }

	    $verspaetungStundeSelect = "";

	    for($i = 1; $i < DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
	    	$verspaetungStundeSelect .= "<option value=\"" . $i . "\"" . ((stundenplan::getCurrentStunde() == $i) ? (" selected=\"selected\"") : ("")) . ">Zur $i. Stunde</option>";
	    }

	    $valueMinutenVerspaetung = 1;

	    // Krankmeldungen

	    $absenzen = Absenz::getAbsenzenForDate($this->currentDateSQL, $klasse->getKlassenName());

	    $krankmeldungenHTML = "";

	    $krankmeldungenHTMLOffen = "";

	      for($i = 0; $i < sizeof($absenzen); $i++) {

	          $stunden = $absenzen[$i]->getStundenAsArray();

	        $absenzHTML = "<td>" . $absenzen[$i]->getSchueler()->getCompleteSchuelerName();

              if($absenzen[$i]->getKommentar() != "") {
                  $absenzHTML .= " <a href=\"#\" data-toggle=\"tooltip\" title=\"" . @htmlspecialchars(($absenzen[$i]->getKommentar())) . "\"><i class=\"fa fa-sticky-note\"></i></a> ";
              }


              if($absenzen[$i]->isMehrtaegig()) {
                  $absenzHTML .= "<br /><small>Von " . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getDateAsSQLDate()) . " bis " . DateFunctions::getNaturalDateFromMySQLDate($absenzen[$i]->getEnddatumAsSQLDate()) . "</small>";
              }

              $absenzHTML .= "</td>";

              $absenzHTML .= "<td>" . implode(", ", $stunden) . "</td>";

              $absenzHTML .= "<td>";

	        if($absenzen[$i]->isBefreiung()) {
                $absenzHTML .= "<p><span class=\"label label-info\">Befreiung</span></p>";
	        }


	        if($absenzen[$i]->kommtSpaeter()) {
                $absenzHTML .= "<p><span class=\"label label-danger\"><i class=\"fa fa-clock\"></i> Kommt später</span></p>";
	        }

	        if($absenzen[$i]->isBeurlaubung()) {
                $absenzHTML .= "<p><span class=\"label label-info\">Beurlaubung</span></p>";
	          if($absenzen[$i]->getBeurlaubung()->isInternAbwesend()) {
                  $absenzHTML .= "<p><span class=\"label label-info\">Intern abwesend</span></p>";
	          }

	        }

	        if($absenzen[$i]->needSchriftlichEntschuldigung()) {
                if($absenzen[$i]->isSchriftlichEntschuldigt()) {
                    $absenzHTML .= "<p><small class=\"label label-success\"><i class=\"fa fas fa-pencil-alt\"></i><i class=\"fa fa-check\"></i> Schriftlich entschuldigt</small></p>";
                }
                else {
                    $absenzHTML .= "<p><small class=\"label label-warning\"><i class=\"fa fas fa-pencil-alt\"></i><i class=\"fa fa-ban\"></i> Nicht schriftlich entschuldigt</small></p>";

                }
            }



                $absenzHTML .= "</td>";
                $absenzHTML .= "<td>";

	        if($absenzen[$i]->getUserID() == DB::getUserID()) {
                $absenzHTML.= "<p><button type=\"button\" class=\"btn btn-sm btn-warning btn-block\" onclick=\"confirmAction('Soll der Eintrag wirklich gelöscht werden?','index.php?page=klassentagebuch&mode=deleteAbsenz&absenzID=" . $absenzen[$i]->getID() . "&grade=" . $_REQUEST['grade'] . "&currentDate=" . $this->currentDateNatural . "')\"><i class=\"fa fa-trash\"></i> Absenz löschen</button></p>" ;
	        }

	        if(!$absenzen[$i]->isEntschuldigt()) {
                $absenzHTML .= "<p><button type=\"button\" class=\"btn btn-sm btn-success btn-block\" data-toggle=\"modal\" data-target=\"#editAbsenzStunden\" onclick=\"javascript:jetztGekommen(" . $absenzen[$i]->getID() . ",'" . implode("#",$stunden) . "'," . 0 . ")\"><i class=\"fa fa-clock\"></i> Jetzt gekommen</button></p>";
	        }






	        if($this->currentDateSQL == DateFunctions::getTodayAsSQLDate()) {
                $absenzHTML .= "<p><button type=\"buton\" class=\"btn btn-sm btn-default btn-block\" data-toggle=\"modal\" data-target=\"#editAbsenzStunden\" onclick=\"javascript:editStunden(" . $absenzen[$i]->getID() . ",'" . implode("#",$stunden) . "'," . !$absenzen[$i]->isEntschuldigt() . ")\"><i class=\"fa fas fa-pencil-alt\"></i> Stunden bearbeiten</button></p>";
	        }

              $absenzHTML .= "</td>";





              $absenzHTML .= "</tr>";


	        if($absenzen[$i]->isEntschuldigt()) {
                $krankmeldungenHTML .= $absenzHTML;
            }
	        else {
                $krankmeldungenHTMLOffen .= $absenzHTML;
            }
	      }


	        // Klassentermine

	      $klassentermine = Klassentermin::getByClass([$_REQUEST['grade']], $this->currentDateSQL, $this->currentDateSQL);
	      $leistungsnachweise = Leistungsnachweis::getByClass([$_REQUEST['grade']], $this->currentDateSQL, $this->currentDateSQL);

	       $klassentermineHTML = "";
	       $lnwOeffentlich = "";
	       $lnwPrivat = "";

	       for($i = 0; $i < sizeof($klassentermine); $i++) {
	           $klassentermineHTML .= "<li>" . $klassentermine[$i]->getTitle() . " (" . $klassentermine[$i]->getLehrer() . ")</li>";
	       }

	       for($i = 0; $i < sizeof($leistungsnachweise); $i++) {

	       	if($leistungsnachweise[$i]->showForNotTeacher()) {
	       		$lnwOeffentlich .= "<li>" . $leistungsnachweise[$i]->getArtLangtext() . ": " . $leistungsnachweise[$i]->getFach() . " (" . $leistungsnachweise[$i]->getLehrer() . ")</li>";
	       	}
	       	else if($this->isTeacher) {
	       	    $lnwPrivat .= "<li>" . $leistungsnachweise[$i]->getArtLangtext() . ": " . $leistungsnachweise[$i]->getFach() . " (" . $leistungsnachweise[$i]->getLehrer() . ")</li>";
	         }
	       }


	       // Lehrerliste
	       $lehrer = $this->currentStundenplan->getAllTeacherOfGrade($_REQUEST['grade']);

			$lehrerliste = "";

			foreach ($lehrer as $fach => $kuerzel) {
				$lehrerliste .= "<tr><td>" . $fach . "</td><td>" . $kuerzel . "</td></tr>";
			}

    }

    if($canEdit) {
    	$optionsStunden = "";

    	for($i = 1; $i <= stundenplandata::getMaxStunden(); $i++) {

    	    $stundenSelectEditAbsenz .= '<div class="checkbox icheck">
                  <label>
                    <input type="checkbox" name="stunde' . $i . '" value="1" id="stunde' . $i . '"> ' . $i . '. Stunde
                  </label>
                </div>';

    		$optionsStunden .= "<option value=\"" . $i . "\">" . $i . ". Stunde</option>";
    	}


    	$anzahlStundenGesamt = stundenplandata::getMaxStunden();

    	$aktuelleStunde = stundenplan::getCurrentStunde();

    }

    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/klasse") . "\");");
  }

  private function getHistory($grade, $subject) {

  	$html = '';

  	$entries = TagebuchKlasseEntry::getAllForGradeAndSubject($grade, $subject);

  	for($i = 0; $i < sizeof($entries); $i++) {
  		$html .= "<b>" . DateFunctions::getNaturalDateFromMySQLDate($entries[$i]->getDate()) . "</b> - " . $entries[$i]->getTeacher() . " - " . $entries[$i]->getStunde() . ". Stunde<br />";

  		if(!$entries[$i]->isAusfall()) {

	  		if($entries[$i]->isVertretung()) $html .= "Vetretungsstunde<br />";

	  		if($entries[$i]->getStoff() != '') $html .= "<i class=\"fa fas fa-pencil-alt-square\"></i> " . $entries[$i]->getStoff() . "<br />";
	  		else $html .= "<i class=\"fa fas fa-pencil-alt-square\"></i> <i>Kein Stoff</i><br />";

	  		if($entries[$i]->getHausaufgabe() != '') $html .= "<i class=\"fa fa-home\"></i> " . $entries[$i]->getHausaufgabe() . "<br />";
	  		else $html .= "<i class=\"fa fa-home\"></i> <i>Keine Hausaufgabe</i><br />";

	  		$privateFiles = $entries[$i]->getPrivateFiles();
	  		$publicFiles = $entries[$i]->getPublicFiles();

	  		if(sizeof($privateFiles) > 0 && DB::getSession()->isTeacher() && $entries[$i]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) {
	  			$html .= "<br /><b>Private Dateianhänge: (Nur " . DB::getSession()->getTeacherObject()->getKuerzel() . ")</b> ";
	  			for($f = 0; $f < sizeof($privateFiles); $f++) {
	  				$html .= "<a href=\"" . $privateFiles[$f]->getURLToFile() . "\"><i class=\"fa fa-download\"></i> " . $privateFiles[$f]->getFileName() . "</a> ";
	  			}
	  		}

	  		if(sizeof($publicFiles) > 0) {
	  			$html .= "<br /><b>Öffentliche Dateianhänge:</b> ";
	  			for($f = 0; $f < sizeof($publicFiles); $f++) {
	  				$html .= "<a href=\"" . $publicFiles[$f]->getURLToFile() . "\"><i class=\"fa fa-download\"></i> " . $publicFiles[$f]->getFileName() . "</a> ";
	  			}
	  		}


	  		if($entries[$i]->getNotizen() != '' && $entries[$i]->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) $html .=  "<pre>" . $entries[$i]->getNotizen() . "</pre>";

	  		$html .= "<hr />";
  		}
  		else {
  			$html .= "Stunde entfallen<hr />";
  		}
  	}

  	if($html != '') {
  		// echo($html);
  		// exit();
  	}

  	return $html;
  }


  public static function hasSettings() {
    return true;
  }


  public static function getSettingsDescription() {
    return array(

        array(
            'name' => "klassentagebuch-eltern-klassentagebuch",
            'typ' => 'BOOLEAN',
            'titel' => "Den Eltern Zugriff auf das Klassentagebuch geben?",
            'text' => ""
        ),
        array(
            'name' => "klassentagebuch-schueler-klassentagebuch",
            'typ' => 'BOOLEAN',
            'titel' => "Den Schülern Zugriff auf das Klassentagebuch geben?",
            'text' => ""
        ),
        array(
            'name' => "klassentagebuch-lehrertagebuch",
            'typ' => 'BOOLEAN',
            'titel' => "Lehrertagebuch aktivieren?",
            'text' => ""
        ),
        array(
            'name' => "klassentagebuch-klassentagebuch-abschalten",
            'typ' => 'BOOLEAN',
            'titel' => "Das Klassentagebuch abschalten? (Nur privates Lehrertagebuch)",
            'text' => ""
        ),
        array(
            'name' => "klassentagebuch-view-entries-all-times",
            'typ' => 'BOOLEAN',
            'titel' => "Klassentagebucheinträge bereits nach dem Eintragen für alle anzeigen?",
            'text' => "Level 1"
        ),
        array(
            'name' => "klassentagebuch-view-entries-begin-day",
            'typ' => 'BOOLEAN',
            'titel' => "Klassentagebucheinträge bereits am Beginn des Tages für alle anzeigen?",
            'text' => "Level 2"
        )

    );
  }


  public static function getSiteDisplayName() {
    return 'Klassentagebuch';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return [
      [
        'groupName' => 'Webportal_Klassentagebuch_Admin',
        'beschreibung' => 'Administration des Klassentagebuches'
      ],
      [
        'groupName' => 'Webportal_Klassentagebuch_Lesen',
        'beschreibung' => 'Lesen des Klassentagebuches ohne eigentlich Zugriff zu haben'
      ]
    ];
  }

  public static function siteIsAlwaysActive() {
    return false;
  }

  public static function onlyForSchool() {
    return [
    ];
  }

  public static function hasAdmin() {
  	return true;
  }

  public static function getAdminMenuGroup() {
  	return 'Klassentagebuch';
  }

  public static function getAdminMenuGroupIcon() {
  	return 'fa fa-book';
  }

  public static function getAdminMenuIcon() {
  	return 'fa fa-book';
  }

  public static function getAdminGroup() {
  	return 'Webportal_Klassentagebuch_Admin';
  }

  public static function displayAdministration($selfURL) {
      if($_REQUEST['action'] == "addKalenderAccess") {
          $group = usergroup::getGroupByName("Webportal_Klassentagebuch_Lesen");
          $group->addUser(intval($_POST['userID']));
          header("Location: $selfURL");
          exit(0);
      }

      if($_REQUEST['action'] == "deleteKalenderAccess") {
          $group = usergroup::getGroupByName("Webportal_Klassentagebuch_Lesen");
          $group->removeUser(intval($_REQUEST['userID']));
          header("Location: $selfURL");
          exit(0);
      }

      if($_REQUEST['action'] == "alleAntrag") {
          $alleLehrer = lehrer::getAll();

          for($i = 0; $i < sizeof($alleLehrer); $i++) {
              DB::getSettings()->setValue("lehrertagebuch-export-antrag-" . $alleLehrer[$i]->getAsvID(),1);
              DB::getSettings()->setValue("lehrertagebuch-export-" . $alleLehrer[$i]->getAsvID(),0);
          }

          header("Location: $selfURL");
          exit(0);
      }

      if($_REQUEST['action'] == "downloadZip") {
          $alleLehrer = lehrer::getAll();

          $zip = new ZipArchive();
          $filename = "../data/temp/alle_lehrertagebuecher" . md5(rand()) . ".zip";

          if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
              die("cannot open --> $filename\n");
          }


          for($i = 0; $i < sizeof($alleLehrer); $i++) {
              if(DB::getSettings()->getValue("lehrertagebuch-export-" . $alleLehrer[$i]->getAsvID()) > 0) {
                  $upload = FileUpload::getByID(DB::getSettings()->getValue("lehrertagebuch-export-" . $alleLehrer[$i]->getAsvID()));
                  if($upload != null) {
                      $zip->addFile($upload->getFilePath(), $upload->getFileName());
                  }
              }
          }


          $zip->close();

          // Send File

          $file = $filename;

          header('Content-Description: File Transfer');
          header('Content-Type: application/zip');
          header('Content-Disposition: attachment; filename='.basename("Alle Lehrertagebuecher.zip"));
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

      /** @var lehrer[] $alleLehrer */
      $alleLehrer = lehrer::getAll();

      $exportPDF = "";

      for($i = 0; $i < sizeof($alleLehrer); $i++) {
          $exportPDF .= "<tr><td>" . $alleLehrer[$i]->getDisplayNameMitAmtsbezeichnung() . "</td>";
          $exportPDF .= "<td>";

          $export = DB::getSettings()->getValue("lehrertagebuch-export-" . $alleLehrer[$i]->getAsvID());
          $beantragt = DB::getSettings()->getValue("lehrertagebuch-export-antrag-" . $alleLehrer[$i]->getAsvID());

          if($export > 0) $exportPDF .= "Erzeugt.";
          else if($beantragt) $exportPDF .= "Beantragt";
          else $exportPDF .= "--";

          $exportPDF .= "</tr>";
      }

      eval("\$html = \"" . DB::getTPL()->get("klassentagebuch/admin/index") . "\";");


      $box = administrationmodule::getUserListWithAddFunction($selfURL, "tagebuchzugriff", "addKalenderAccess", "deleteKalenderAccess", "Benutzer mit Zugriff auf alle Klassen", "", "Webportal_Klassentagebuch_Lesen");

      $html = "<div class=\"row\"><div class=\"col-md-9\">$html</div><div class=\"col-md-3\">$box</div></div>";

      return $html;
  }

  public static function getActionSchuljahreswechsel() {
  	return 'Einträge aus dem alten Schuljahr löschen; Export PDFs löschen.';
  }

  public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
      DB::getDB()->query("DELETE FROM klassentagebuch_pdf");
      DB::getDB()->query("DELETE FROM settings WHERE settingName LIKE 'lehrertagebuch-export-antrag-%'");
      DB::getDB()->query("DELETE FROM settings WHERE settingName LIKE 'lehrertagebuch-antrag-%'");
      DB::getDB()->query("DELETE FROM klassentagebuch_klassen WHERE entryDate < '$sqlDateFirstSchoolDay'");
  }



}

