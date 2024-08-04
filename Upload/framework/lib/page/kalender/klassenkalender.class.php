<?php

class klassenkalender extends AbstractPage {

  private $info;

  private $isAdmin = false;

  private $isTeacher = false;

  private $isPupil = false;

  /**
   *
   * @var klasse[]
   */
  private $grades =  array();


  private $title = "";



  public function __construct() {

    parent::__construct(array("Kalender", "Klassenkalender"));

    $this->checkLogin();

    if(DB::getSession()->isTeacher()) {
      $this->isTeacher = true;
    }
    else if(DB::getSession()->isPupil()) {
      $this->isPupil = true;
      $this->grades = array(DB::getSession()->getSchuelerObject()->getKlassenObjekt());
    }
    else if(DB::getSession()->isEltern()) {
      $this->grades = DB::getSession()->getElternObject()->getKlassenObjectsAsArray();
    }
    else if(DB::getSession()->isMember("Webportal_Kalender_Admin")) {
      $this->isTeacher = true;
    }
    else if(DB::getSession()->isMember("Webportal_Klassenkalender")) {
      $this->isTeacher = true;
      $this->isAdmin = true;
    }
    else {
      new errorPage("Sie haben leider keinen Zugriff auf die Klassenkalender!");
      exit(0);
    }

    if(in_array("Webportal_Kalender_Admin",DB::getSession()->getGroupNames())) $this->isAdmin = true;

    if(DB::getSession()->isAdmin()) $this->isAdmin = true;



  }

  public function execute() {
    $today = date("Y-m-d");

    $currentStundenplanID = stundenplandata::getCurrentStundenplanID();

    if($currentStundenplanID < 0) {
      eval("echo(\"" . DB::getTPL()->get("klassenkalender/nocurrentstundenplan") . "\");");
      PAGE::kill(true);
      //exit(0);
    }

    if($this->isTeacher) {


      $grade = $_REQUEST['grade'];

      if($grade == "") {
        // Alle Klassen anzeigen
      }

      if(isset($_GET['action']) && ($_GET['action'] == "addkt" || $_GET['action'] == "addln")) {
        // Termin wird hinzugefügt.
        // --> Klassenzusammensuchen

        $addGrades = $_POST['grade'];

        if(!is_array($addGrades) || sizeof($addGrades) == 0) {
          new errorPage("Keine Klasse ausgewählt!");
          exit(0);
        }

      }

      if(isset($_GET['action']) && $_GET['action'] == "addkt") {

        if($this->isAdmin) {
          $lehrer = addslashes($_POST['lehrer']);
        }
        else {
          if(DB::getSession()->isTeacher())
            $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
          else $lehrer = "n/a";
        }

        $date = addslashes($_POST['date']);

        $dateEnde = addslashes($_POST['enddatum']);

        if($dateEnde == "") $dateEnde = $date;
        else if(!DateFunctions::isNaturalDate($dateEnde)) {
          new errorPage("Das angegebene Enddatum ist ungültig!");
        }
        else {
          $dateEnde = DateFunctions::getMySQLDateFromNaturalDate($dateEnde);
          $dateEnde = DateFunctions::addOneDayToMySqlDate($dateEnde);
        }



        if(!DateFunctions::isSQLDateAtOrAfterAnother($dateEnde, $date)) {
          new errorPage("Das angegebene Enddatum ist nicht nach dem Startdatum!");
        }


        $titel = addslashes($_POST['titel']);

        $stunden = [];

        for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
          if(in_array($i, (array)$_POST['stunden'])) {
            $stunden[] = $i;
          }
        }

        $stunden = implode(",",$stunden);

        $ort = addslashes($_POST['ort']);
        $betrifft = addslashes($_POST['betrifft']);
        $klassen = implode(",",$addGrades);


        DB::getDB()->query("INSERT INTO kalender_klassentermin (
              eintragDatumStart,
              eintragDatumEnde,
              eintragKlassen,
              eintragLehrer,
              eintragTitel,
              eintragEintragZeitpunkt,
            eintragOrt,
            eintragBetrifft,
            eintragStunden
            )
            values(
              '$date',
              '$dateEnde',
              '$klassen',
              '$lehrer','{$titel}',
              UNIX_TIMESTAMP(),
        '$ort',
        '$betrifft',
        '$stunden'
            )");



        if(sizeof($addGrades) > 1) {
          header("Location: index.php?page=klassenkalender&grade=allMyTermine");
          exit(0);
        }
        else {
          header("Location: index.php?page=klassenkalender&grade=" . $addGrades[0]);
          exit(0);
        }
      }

      if(isset($_GET['action']) && $_GET['action'] == "addln") {
        if($this->isAdmin) {
          $lehrer = addslashes($_POST['lehrer']);
        }
        else {
          if(DB::getSession()->isTeacher())
            $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
          else $lehrer = "n/a";
        }

        $date = DB::getDB()->escapeString($_POST['date']);
        $art = DB::getDB()->escapeString($_POST['art']);
        $fach = DB::getDB()->escapeString($_POST['fach']);
        $betrifft = DB::getDB()->escapeString($_POST['betrifft']);
        $alwaysShow = (int)( $_POST['alwaysShow'] > 0 );

        $stunden = [];

        for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
          if(in_array($i, (array)$_POST['stunden'])) {
            $stunden[] = $i;
          }
        }

        $stunden = implode(",",$stunden);


        for($i = 0; $i < sizeof($addGrades); $i++) {
          DB::getDB()->query("INSERT INTO kalender_lnw (
              eintragArt,
              eintragDatumStart,
              eintragDatumEnde,
              eintragKlasse,
              eintragLehrer,
              eintragFach,
              eintragEintragZeitpunkt,
              eintragStunden,
              eintragBetrifft,
              eintragAlwaysShow
            )
            values(
              '$art',
              '$date',
              '$date',
              '{$addGrades[$i]}',
              '$lehrer',
              '$fach',
              UNIX_TIMESTAMP(),
              '$stunden',
              '$betrifft',
              '$alwaysShow'

            )");
        }

        if(sizeof($addGrades) > 1) {
          header("Location: index.php?page=klassenkalender&grade=allMyTermine");
          exit(0);
        }
        else {
          header("Location: index.php?page=klassenkalender&grade=" . $addGrades[0]);
          exit(0);
        }
      }

      if(isset($_GET['action']) && $_GET['action'] == "getJSONData") {


          $termine = $this->getClassDates($grade);

          echo json_encode($termine);
          exit(0);
      }

      if(isset($_GET['action']) && $_GET['action'] == 'getICSFeedURL') {
          $feed = ICSFeed::getKlassenkalenderFeed($_REQUEST['grade'], DB::getUserID(), 1);
          $feed2 = ICSFeed::getKlassenkalenderFeed($_REQUEST['grade'], DB::getUserID(), 0);

          echo json_encode([
              'feedURL' => $feed->getURL(),
              'feedURL2' => $feed2->getURL()
          ]);

          exit(0);
      }

      if(isset($_GET['action']) && $_GET['action'] == "editLNW") {

          header("Content-Type: application/json");

          if($this->isTeacher) {
              $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
          }

          $lnw = Leistungsnachweis::getByID($_REQUEST['lnwID']);

          if($lnw != null) {
              if($this->isAdmin || strtolower($lnw->getLehrer()) == strtolower($lehrer)) {
                  $lnw->setDatumStart($_POST['newDate']);
                  $lnw->setDatumEnde($_POST['newDate']);        // LNWs haben nur eine Dauer von maximal einen Tag

                  $success = true;
              }
          }
          else {
              $success = false;
          }

          $result = [
              'success' => $success
          ];

          echo json_encode($result);

          exit(0);
      }

      if(isset($_GET['action']) && $_GET['action'] == "editKlassentermin") {

          header("Content-Type: application/json");

          if($this->isTeacher) {
              $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
          }

          $lnw = Klassentermin::getByID($_REQUEST['terminID']);

          if($lnw != null) {
              if($this->isAdmin || strtolower($lnw->getLehrer()) == strtolower($lehrer)) {
                  $lnw->setDatumStart($_POST['newStartDate']);
                  $lnw->setDatumEnde($_POST['newEndDate']);
                  $success = true;
              }
          }
          else {
              $success = false;
          }

          $result = [
              'success' => $success
          ];

          echo json_encode($result);

          exit(0);
      }



      if(isset($_GET['action']) && $_GET['action'] == "delete") {
        // Eintrag löschen
        if($_GET['eventType'] == "lnw") $event = DB::getDB()->query_first("SELECT * FROM kalender_lnw WHERE eintragID='" . intval($_GET['eintragID']) . "'");
        else $event = DB::getDB()->query_first("SELECT * FROM kalender_klassentermin WHERE eintragID='" . intval($_GET['eintragID']) . "'");

        if($event['eintragID'] > 0) {
          if(DB::getSession()->isTeacher())
            $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
          else $lehrer = "n/a";

          if($this->isAdmin || strtolower($event['eintragLehrer']) == strtolower($lehrer)) {
            if($_GET['eventType'] == "lnw") DB::getDB()->query("DELETE FROM kalender_lnw WHERE eintragID='" . intval($_GET['eintragID']) . "'");
            else DB::getDB()->query("DELETE FROM kalender_klassentermin WHERE eintragID='" . intval($_GET['eintragID']) . "'");
          }
        }

        header("Location: index.php?page=klassenkalender&grade=allMyTermine");
        exit(0);
      }

      $selectMySubjects = "";

      if(DB::getSession()->isTeacher())
            $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
      else $lehrer = "n/a";

      /** $f2 = DB::getDB()->query("SELECT DISTINCT stundeFach FROM stundenplan_stunden" . ((!$this->isAdmin) ? (" WHERE stundenplanID='$currentStundenplanID' AND stundeLehrer LIKE '" . $lehrer . "'") : (" WHERE stundenplanID='$currentStundenplanID'")) . " ORDER BY stundeFach");


      while($f = DB::getDB()->fetch_array($f2)) {
        $selectMySubjects .= "<option value=\"" . $f['stundeFach'] . "\">" . $f['stundeFach'] . "</option>\n";
      } **/


      $faecher = fach::getAll();

      for($i = 0; $i < sizeof($faecher); $i++) {
          $selectMySubjects .= "<option value=\"" . $faecher[$i]->getKurzform() . "\">" . $faecher[$i]->getKurzform() . " (" . $faecher[$i]->getLangform() . ")</option>";
      }



      if($this->isAdmin) {
        $selectLehrer = "<select name=\"lehrer\" class=\"form-control\">";

        $alllehrer = lehrer::getAll();

        for($i = 0; $i < sizeof($alllehrer); $i++) {
            $selectLehrer .= "<option value=\"" . $alllehrer[$i]->getKuerzel() . "\"" . ((DB::getSession()->isTeacher() && DB::getSession()->getTeacherObject()->getAsvID() == $alllehrer[$i]->getAsvID()) ? ("selected") : ("")) . ">" . $alllehrer[$i]->getKuerzel() . "</option>";
        }

        $selectLehrer .= "</select>";
      }
      else {
        if(DB::getSession()->isTeacher())
            $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
          else $lehrer = "n/a";

        $selectLehrer = $lehrer;
      }


      $termine = $this->getClassDates($grade,$_GET['mode'] == 'print',$_GET['noEx'] == '1');
      // die($termine);


      $canAdd = 1;

      $doMultipleSelect = true;

      if($grade == "all_grades") {
        $gradeDisplay = "Alle Klassen";
      }
      else if($grade == "allMyGrades") {
        $gradeDisplay = "Alle meine Klassen";
      }
      else if($grade == "allMyTermine") {
        $gradeDisplay = "Von mir eingetragen";
      }
      else if($grade == "fachbetreuer") {
          $gradeDisplay = "Fachbetreuer";
          
         $fach = fach::getByASDID($_REQUEST['fachASDID']);
         if($fach != null) {
             $gradeDisplay .= " (" . $fach->getLangform() . ")";
         }
          
          $addFachID = "&fachASDID=" . $_REQUEST['fachASDID'];
      }
      elseif(substr($grade,0,3) == "all") {
        $gradeDisplay = "Alle " . substr($grade,3) . ". Klassen";
      }
      else {
        $doMultipleSelect = false;
        $gradesForSelect = $grade;
        $gradeDisplay = "Klasse " . $grade;
      }


      if($_GET['mode'] == 'print') {

        if($_GET['noEx'] != 1) $isTeacherVersion = "<table border=\"1\" width=\"100%\"><tr><td align=\"center\"><h4 align=\"center\"><font color=\"red\"><b>ACHTUNG: Lehrerversion mit Stegreifaufgaben</b></font></h4></td></tr></table>";
    else $isTeacherVersion = "";
        $datumHeute = date("d.m.Y");


        eval("\$print =\"" . DB::getTPL()->get("klassenkalender/klassenkalender_print") . "\";");

        $print = ($print);

      $pdf = new PrintNormalPageA4WithHeader("Klassenkalender");
      $pdf->setHTMLContent($print);
      $pdf->send();
        exit(0);
      }
      else {

        // Klassenwauswahl generieren

        $selectGrade = "";

          $grades = klasse::getAllKlassen();


          $selectGrade = "<select name=\"grade[]\" id=\"gradeSelect\" class=\"form-control select2\" multiple=\"multiple\" data-placeholder=\"Klassen auswählen\" style=\"width: 90%;\">";


          $selectAll = [];
          $unselectAll = [];
          $selectMy = [];



          for($i = 0; $i < sizeof($grades); $i++) {

              $name = $grades[$i]->getKlassenName();
              $name = str_replace(" ","_",$name);

              $selectGrade .= "<option id=\"kl{$name}_984984984984961651651\" value=\"" . $grades[$i]->getKlassenName() . "\"" . (($_REQUEST['grade'] == $grades[$i]->getKlassenName()) ? (" SELECTED=\"SELECTED\"") : ("")) . ">" . $grades[$i]->getKlassenName() . "</option>";

              $selectAll[] = "$('#kl{$name}_984984984984961651651').prop('selected',true)";
              $unselectAll[] = "$('#kl{$name}_984984984984961651651').prop('selected',false)";
              $selectMy[] = "$('#kl{$name}_984984984984961651651').prop('selected',false)";
          }

          if(DB::getSession()->isTeacher()) {
              $myGrades = klasse::getByUnterrichtForTeacher(lehrer::getByKuerzel($lehrer));
          }
          else {
              $myGrades = [];
          }

          for($i = 0; $i < sizeof($myGrades); $i++) {
              $name = $myGrades[$i]->getKlassenName();
              $name = str_replace(" ","_",$name);
              $selectMy[] = "$('#kl{$name}_984984984984961651651').prop('selected',true)";
          }

          $maxGrade = grade::getMaxGrade();
          $minGrade = grade::getMinGrade();

          $quickLinks = "";



          for($i = $minGrade; $i <= $maxGrade; $i++) {
            if($i > $minGrade) $quickLinks .= " | ";
            $klassen = klasse::getAllAtLevel($i);

            $select = [];
            for($g = 0; $g < sizeof($klassen); $g++) {

                $name = $klassen[$g]->getKlassenName();
                $name = str_replace(" ","_",$name);


                $select[] = "$('#kl{$name}_984984984984961651651').prop('selected',true)";
            }

            $quickLinks .= "<a href=\"#\" onclick=\"javascript:" . implode(";",$select) . ";$('#gradeSelect').select2();\">" . $i . ". Klassen</a>";
          }

          $quickLinks .= "<br ><a href=\"#\" onclick=\"javascript:" . implode(";",$selectAll) . ";$('#gradeSelect').select2();\">Alle Klassen</a> | <a href=\"#\" onclick=\"javascript:" . implode(";",$unselectAll) . ";$('#gradeSelect').select2();\">Keine Klassen</a> | <a href=\"#\" onclick=\"javascript:" . implode(";",$selectMy) . ";$('#gradeSelect').select2()\">Meine Klassen</a>";

          $selectGrade .= "</select><br ><small>" . $quickLinks . "</small>";

          $selectGrade2 = str_replace("gradeSelect", "gradeSelect2", $selectGrade);
          $selectGrade2 = str_replace("_984984984984961651651", "_984984984984961651652", $selectGrade2);


        $stundenSelectOptions = "";


        for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) {
          $stundenSelectOptions .= "<option value=\"" . $i . "\">" . $i . ". Stunde</option>\r\n";
        }

        $basicLink = "index.php?page=klassenkalender";

        $selectMenuModals = '<div class="modal fade" id="selectGrade" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><i class="fa fa-group"></i> Klasse auswählen</h4>
          </div>
          <div class="modal-body"><h4>Alle Klassen / Meine Klassen</h4>
          
          
          <div class="row">
            <div class="col-sm-3"><a href="' . $basicLink . '&grade=all_grades" class="btn btn-primary btn-block">Alle Klassen</a></div>
            <div class="col-sm-3"><a href="' . $basicLink . '&grade=allMyGrades" class="btn btn-success btn-block">Meine Klassen</a></div>
            <div class="col-sm-6"><a href="' . $basicLink . '&grade=allMyTermine" class="btn btn-info btn-block"><i class="fa fa-pen"></i> Von mir eingetragen</a></div>
          </div>
          
          <div class="row"><div class="col-md-6">';

        $completeGradeData = [];

        for($i = 1; $i <= 15; $i++) {
            $completeGradeData[$i . '. Klassen'] = [];
        }

        $completeGradeData['Sonstige'] = [];

        $allGrades = klasse::getAllKlassen();


        for($i = 0; $i < sizeof($allGrades); $i++) {
            $found = false;
            for($j = 2; $j <= 15; $j++) {
                if($allGrades[$i]->getKlassenstufe() == $j) {
                    $completeGradeData[$j][] = $allGrades[$i]->getKlassenName();
                    $found = true;
                }
            }

            if(!$found) $completeGradeData['Sonstige'][] = $allGrades[$i]->getKlassenName();
        }

        $numStufenWithGrades = 0;

        foreach ($completeGradeData as $jgs => $klassen) {
            if(sizeof($klassen) > 0) {
                $numStufenWithGrades++;
            }
        }

        $currentNummer = 0;

        $half = floor($numStufenWithGrades / 2);

        foreach ($completeGradeData as $jgs => $klassen) {
            if(sizeof($klassen) > 0) {
                $selectMenuModals .= "<h4>" . $jgs . ". Jahrgangsstufe</h4>";

                $currentNummer++;

                $inRow = 0;

                for($g = 0; $g < sizeof($klassen); $g++) {
                    $inRow++;

                    if($inRow == 1) {
                        $selectMenuModals .= "<div class='row'>";
                    }

                    $selectMenuModals .= "<div class='col-md-6'><a href=\"" . $basicLink . "&grade=" . urlencode($klassen[$g]) . "\" class='btn btn-default btn-block'>";
                    $selectMenuModals .= "Klasse " . $klassen[$g];

                    $selectMenuModals .= "</a></div>";


                    if($inRow == 2) {
                        $selectMenuModals .= "</div>";
                        $inRow = 0;
                    }


                }

                if($inRow == 1) $selectMenuModals .= "</div>";



                $selectMenuModals .= "<div class='row'><div class='col-md-12'><a href=\"" . $basicLink . "&grade=all" .$jgs . "\" class='btn btn-default btn-block'>";
                $selectMenuModals .= "<i>Alle Klassen der Jahrgangsstufe</i>";


                $selectMenuModals .= "</a></div></div>";


                if($currentNummer == $half) {
                    $selectMenuModals .= "</div><div class=\"col-md-6\">";
                }

            }
        }

        $selectMenuModals .= '</div></div>
                  </div>
              </div>
             </div>
          </div>';

        eval("echo(\"" . DB::getTPL()->get("klassenkalender/klasseMitEintragen") . "\");");
        PAGE::kill(true);
        //exit(0);
      }

    }
    else {
      // Klassenkalender anzeigen ohne Bearbeitungsmöglichkeit
      $grade = "";

      $grades = [];

      for($i = 0; $i < sizeof($this->grades); $i++) {
          $grades[] = $this->grades[$i]->getKlassenName();
      }

      if(isset($_REQUEST['grade']) && in_array($_REQUEST['grade'],$grades)) {
        $grade = addslashes($_REQUEST['grade']);
      }
      else {
        $grade = $grades[0];
      }


      if(isset($_GET['action']) && $_GET['action'] == "getJSONData") {


          $termine = $this->getClassDates($grade);

          echo json_encode($termine);
          exit(0);
      }


      if(isset($_GET['action']) && $_GET['action'] == 'getICSFeedURL') {
          $feed = ICSFeed::getKlassenkalenderFeed($grade, DB::getUserID(), 0);

          echo json_encode([
              'feedURL' => $feed->getURL(),
          ]);

          exit(0);
      }


      $termine = $this->getClassDates($grade, $_GET['mode'] == "print");

      $gradeDisplay = $grade;

      if($_GET['mode'] == 'print') {
        $isTeacherVersion = ""; // In der Schülerversion da einfach nix anzeigen

        $datumHeute = date("d.m.Y");

        eval("\$print =\"" . DB::getTPL()->get("klassenkalender/klassenkalender_print") . "\";");

          $pdf = new PrintNormalPageA4WithHeader("Klassenkalender");
          $pdf->setHTMLContent($print);
          $pdf->send();

        exit(0);
      }
      else {
        eval("echo(\"" . DB::getTPL()->get("klassenkalender/klasseNurAnzeigen") . "\");");
      }
      PAGE::kill(true);
      //exit(0);
    }
  }

  private function getClassDates($class,$showForPDF=false,$pdfShowEx=true) {


    $terminText = "";

    /**
     * Soll die Klasse vor dem Termintext angezeigt werden?
     * @var boolean $showGrade
     */
    $showGrade = false;

    if($_REQUEST['showGrade'] > 0) $showGrade = true;

    $currentStundenplan = stundenplandata::getCurrentStundenplan();

    $lnwData = [];
    $termine = [];

    if(DB::getSession()->isTeacher()) $lehrer = DB::getSession()->getTeacherObject()->getKuerzel();
    else $lehrer = "n/a";

    $onlyFromToday = $showForPDF;
    $onlyFromTodayDate = $onlyFromToday ? DateFunctions::getTodayAsSQLDate() : "";

    $untilDate = "";

    if(!$showForPDF) {
        if($_REQUEST['start'] != "" && DateFunctions::isSQLDate($_REQUEST['start'])) $onlyFromTodayDate = $_REQUEST['start'];
        if($_REQUEST['end'] != "" && DateFunctions::isSQLDate($_REQUEST['end'])) $untilDate = $_REQUEST['end'];
    }

    $fpas = [];

    if($class == "all_grades") {
      $lnwData = Leistungsnachweis::getByClass([], $onlyFromTodayDate,$untilDate);
      $termine = Klassentermin::getByClass([], $onlyFromTodayDate,$untilDate);
      $showGrade = true;

    }
    
    if($class == 'fachbetreuer') {
        $fach = fach::getByASDID($_REQUEST['fachASDID']);
        
        if($fach != null) {
            
            if(DB::getSession()->isTeacher()) {
                if($fach->isFachschaftsleitung(DB::getSession()->getTeacherObject())) {
                    $lnwData = Leistungsnachweis::getByFaecher([$fach], $onlyFromTodayDate,$untilDate);
                }
            }
        }
    }
    else if($class == "allMyGrades") {

        $myGrades = [];

        if(DB::getSession()->isTeacher()) {
            $myGradesObjects = klasse::getByUnterrichtForTeacher(DB::getSession()->getTeacherObject());
            for($i = 0; $i < sizeof($myGradesObjects); $i++) $myGrades[] = $myGradesObjects[$i]->getKlassenName();
        }
        else $myGrades = [];

      $lnwData = Leistungsnachweis::getByClass($myGrades,$onlyFromTodayDate,$untilDate);
      $termine = Klassentermin::getByClass($myGrades,$onlyFromTodayDate,$untilDate);

      $showGrade = true;
    }
    else if($class == "allMyTermine") {
        $lnwData = Leistungsnachweis::getBayTeacher($lehrer,$onlyFromToday,$untilDate);
        $termine = Klassentermin::getBayTeacher($lehrer,$onlyFromTodayDate,$untilDate);
        $showGrade = true;
    }
    elseif(substr($class,0,3) == "all") {
      $showGrade = true;

      $class = substr($class,3);

      $klassen = klasse::getAllAtLevel($class);

      $klassennamen = [];
      for($i = 0; $i < sizeof($klassen); $i++) $klassennamen[] = $klassen[$i]->getKlassenName();

      $lnwData = Leistungsnachweis::getByClass($klassennamen, $onlyFromTodayDate,$untilDate);
      $termine = Klassentermin::getByClass($klassennamen, $onlyFromTodayDate,$untilDate);

    }
    else {
        $lnwData = Leistungsnachweis::getByClass([$class],$onlyFromTodayDate,$untilDate);
        $termine = Klassentermin::getByClass([$class],$onlyFromTodayDate,$untilDate);

    }


    // Show Grade?


    if(!$showGrade) {
        $klassenInDaten = [];
        for($i = 0; $i < sizeof($lnwData); $i++) {
            if(!in_array($lnwData[$i]->getKlasse(), $klassenInDaten)) {
                $klassenInDaten[] = $lnwData[$i]->getKlasse();
                if(sizeof($klassenInDaten) > 1) {
                    $showGrade = true;
                    break;
                }
            }
        }
    }


    if(!$showGrade) {
        $klassenInDaten = [];
        for($i = 0; $i < sizeof($termine); $i++) {
            $terminKlassen = $termine[$i]->getKlassen();

            if(sizeof($terminKlassen) <= 2) {

                for($tk = 0; $tk < sizeof($terminKlassen); $tk++) {
                    if(!in_array($terminKlassen[$tk], $klassenInDaten)) {
                        $klassenInDaten[] = $terminKlassen[$tk];
                        if(sizeof($klassenInDaten) > 1) {
                            $showGrade = true;
                            break;
                        }
                    }
                }

            }
        }
    }


    $monthNames = array(
        "",
        "Januar",
        "Februar",
        "März",
        "April",
        "Mai",
        "Juni",
        "Juli",
        "August",
        "September",
        "Oktober",
        "November",
        "Dezember"
    );

    $pdfLastDay = "";


    if($showForPDF) $terminText .= "<h3>Leistungsnachweise</h3>";

    $terminDataJSON = [];

    for($i = 0; $i < sizeof($lnwData); $i++) {

      $show = true;

      if(!$this->isTeacher) $show = $lnwData[$i]->showForNotTeacher();

      /** if(($lnwData[$i]->getArt() == "STEGREIFAUFGABE" || $lnwData[$i]->getArt() == "PLNW") && !$this->isTeacher && !$lnwData[$i]->isAlwaysShow()) {
        $show = false;
      }

      if(($lnwData[$i]->getArt() == "STEGREIFAUFGABE" || $lnwData[$i]->getArt() == "PLNW") && $showForPDF && $pdfShowEx && !$lnwData[$i]->isAlwaysShow()) $show = false; **/

      $eintragZeitpunkt = $lnwData[$i]->getEintragZeitpunkt();
      if(DB::getSettings()->getBoolean("datenschutz-kein-eintragzeitpunkt")) {
          $eintragZeitpunkt = "n/a";
      }

      $icon = "fa fas fa-pencil-alt";

      $startzeit = "00:00:00";
      $endzeit = "00:00:00";

      $isAllDay = true;

      $stunden = $lnwData[$i]->getStunden();

      if(sizeof($stunden) > 0 && $stunden[0] != "") {
          $startzeit = stundenplan::getStartTimeStunde(DateFunctions::getWeekDayFromNaturalDate(DateFunctions::getNaturalDateFromMySQLDate($lnwData[$i]->getDatumStart())), $stunden[0]);
        $startzeit .= ":00";

        $endzeit = stundenplan::getEndTimeStunde(DateFunctions::getWeekDayFromNaturalDate(DateFunctions::getNaturalDateFromMySQLDate($lnwData[$i]->getDatumStart())), $stunden[sizeof($stunden)-1]);
        $endzeit .= ":00";

          $isAllDay = false;
      }

      if($show && !$showForPDF) {
          $terminDataJSON[] = [
              'title' => ((($showGrade) ? ($lnwData[$i]->getKlasse() . ": ") : ("")) . $lnwData[$i]->getArtKurztext() . " - " . $lnwData[$i]->getFach() . " - " . $lnwData[$i]->getLehrer()  . (($lnwData[$i]->isAlwaysShow()) ? (" (Angekündigt)") : (""))),
              'start' => $lnwData[$i]->getDatumStart() . "T$startzeit",
              'end' =>$lnwData[$i]->getDatumStart() . "T$endzeit",
              'eintragZeitpunkt' => $eintragZeitpunkt,
              'betrifft' => $lnwData[$i]->getBetrifft(),
              'stunden' => implode(", ", $lnwData[$i]->getStunden()),
              'icon' => $icon,
              'allDay' => $isAllDay,
              'klassen' => '',
              'ort' => '',
              'color' => $lnwData[$i]->getEintragFarbe(),
              'canDelete' => (($this->isAdmin || $this->isTeacher && strtolower($lnwData[$i]->getLehrer()) == strtolower($lehrer)) ? 1 : 0),
              'editable' => (($this->isAdmin || $this->isTeacher && strtolower($lnwData[$i]->getLehrer()) == strtolower($lehrer)) ? 1 : 0),
              'eventID' => $lnwData[$i]->getID(),
              'eventType' => 'lnw',
              'lnwtype' => $lnwData[$i]->getArt()
          ];
      }

      elseif ($show && $showForPDF) {

          if(($pdfShowEx && $lnwData[$i]->isAlwaysShow()) || ($pdfShowEx && ($lnwData[$i]->getArt() != 'STEGREIFAUFGABE' || $lnwData[$i]->getArt() != 'STEGREIFAUFGABE'))) {
            $datum = explode("-",$lnwData[$i]->getDatumStart());
    
            $datum[0] *= 1;
            $datum[1] *= 1;
            $datum[2] *= 1;
    
    
            if($pdfLastDay != $lnwData[$i]->getDatumStart()) {
              // Tag anzeigen
    
              $terminText .= "<br /><br /><table border=\"1\" width=\"100%\" cellpadding=\"3\"><tr><td><b>{$datum[2]}. {$monthNames[$datum[1]]} {$datum[0]}</b></td></tr></table><br />";
              $pdfLastDay = $lnwData[$i]->getDatumStart();

        }
        
        
        
        
        
        $terminText .= (($showGrade) ? ($lnwData[$i]->getKlasse() . ": ") : ("")) . $lnwData[$i]->getArtLangtext(). " - " . $lnwData[$i]->getFach() . " - " . $lnwData[$i]->getLehrer() . (($lnwData[$i]->isAlwaysShow()) ? (" (Angekündigt)") : ("")) . "<br />";
      
          }
      }

    }

    if($showForPDF) $terminText .= "<h3>Klassentermine</h3>";


    // Klassentermine
    for($i = 0; $i < sizeof($termine); $i++) {



      $show = true;

      if($this->isAdmin || $this->isTeacher && strtolower($termine[$i]->getLehrer()) == strtolower($lehrer)) {
        // Löschmöglichkeit
        $addDelete = ", canDelete: 1, eventID: {$termine[$i]->getID()}, eventType: 'termin'";
      } else $addDelete = ", canDelete: 0, eventID: {$termine[$i]->getID()}, eventType: 'termin'";

      $eintragZeitpunkt = $termine[$i]->getEintragZeitpunkt();

      $icon = "fa fa-calendar";

      $startzeit = "10:00:00";
      $endzeit = "11:00:00";

      $stunden = $termine[$i]->getStunden();

      if($showGrade && sizeof($termine[$i]->getKlassen()) < 3) {
        $displayGrade = implode(", ",$termine[$i]->getKlassen()) . ": ";
      }
      else $displayGrade = "";

      if($show && !$showForPDF) {

          $newTermin = [
              'title' => $displayGrade . $termine[$i]->getTitle() . " (" . $termine[$i]->getLehrer() . ")",
              'start' => $termine[$i]->getDatumStart() . "T$startzeit",
              'eintragZeitpunkt' => $eintragZeitpunkt,
              'betrifft' => $termine[$i]->getBetrifft(),
              'stunden' => implode(", ", $termine[$i]->getStunden()),
              'icon' => $icon,
              'allDay' => true,
              'klassen' => implode(", ",$termine[$i]->getKlassen()),
              'ort' => $termine[$i]->getOrt(),
              'color' => 'green',
              'canDelete' => (($this->isAdmin || $this->isTeacher && strtolower($termine[$i]->getLehrer()) == strtolower($lehrer)) ? 1 : 0),
              'editable' => (($this->isAdmin || $this->isTeacher && strtolower($termine[$i]->getLehrer()) == strtolower($lehrer)) ? 1 : 0),

              'eventID' => $termine[$i]->getID(),
              'eventType' => 'termin',
              'lnwtype' => ''
          ];

          if($termine[$i]->getDatumEnde() != $termine[$i]->getDatumStart()) {
              $newTermin['end'] = $termine[$i]->getDatumEnde() . "T$endzeit";
          }

          $terminDataJSON[] = $newTermin;

      }

      elseif ($show && $showForPDF) {

        $datum = explode("-",$termine[$i]->getDatumStart());

        $datum[0] *= 1;
        $datum[1] *= 1;
        $datum[2] *= 1;


        if($pdfLastDay != $termine[$i]->getDatumStart()) {
          // Tag anzeigen

          $terminText .= "<br /><br /><table border=\"1\" width=\"100%\" cellpadding=\"3\"><tr><td><b>{$datum[2]}. {$monthNames[$datum[1]]} {$datum[0]}</b></td></tr></table><br />";
          $pdfLastDay = $termine[$i]->getDatumStart();

        }
        $terminText .= (($showGrade) ? (implode(", ", $termine[$i]->getKlassen()) . ": ") : ("")) . $termine[$i]->getTitle() . " - " . $termine[$i]->getLehrer() . "<br />";
      }

    }


    if(! $showForPDF && $_REQUEST['ignoreFerien'] != 1) {

        $addSql = " AND
        (ferienStart >= '$onlyFromTodayDate' AND ferienEnde <= '$untilDate') OR

        (ferienStart <= '$onlyFromTodayDate' AND ferienEnde <= '$untilDate' AND ferienEnde >= '$onlyFromTodayDate') OR

        (ferienStart >= '$onlyFromTodayDate' AND ferienStart <= '$untilDate' AND ferienEnde >= '$untilDate') OR

        (ferienStart <= '$onlyFromTodayDate' AND ferienEnde >= '$untilDate')";

        $ferien = DB::getDB()->query("SELECT * FROM kalender_ferien WHERE ferienStart >= '$onlyFromTodayDate' $addSql");


      while($f = DB::getDB()->fetch_array($ferien)) {
          
          if($f['ferienStart'] != $f['ferienEnde']) {
              $f['ferienEnde'] = DateFunctions::addOneDayToMySqlDate($f['ferienEnde']);
          }

          $newTermin = [
              'title' => $f['ferienName'],
              'start' => $f['ferienStart'] . "T23:59:00",
              'end' => $f['ferienEnde'] . "T23:59:00",
              'eintragZeitpunkt' => '',
              'betrifft' => '',
              'stunden' => '',
              'icon' => 'fa fa-sun',
              'allDay' => true,
              'klassen' => '&nbsp;',
              'ort' => 'Bayern',
              'color' => 'black',
              'canDelete' => 0,
              'eventID' => -1,
              'eventType' => 'ferien',
              'lnwtype' => 'ferien',
              'rendering' => 'background'
          ];

          /**$newTermin2 = [
              'title' => $f['ferienName'],
              'start' => $f['ferienStart'] . "T23:59:00",
              'end' => $f['ferienEnde'] . "T23:59:00",
              'eintragZeitpunkt' => '',
              'betrifft' => '',
              'stunden' => '',
              'icon' => 'fa fa-sun',
              'allDay' => true,
              'klassen' => '&nbsp;',
              'ort' => 'Bayern',
              'color' => 'black',
              'canDelete' => 0,
              'eventID' => -1,
              'eventType' => 'ferien',
              'lnwtype' => 'ferien'
          ];**/

          $terminDataJSON[] = $newTermin;
          // $terminDataJSON[] = $newTermin2;

      }
    }


    if($showForPDF) return $terminText;
    else return $terminDataJSON;
  }

  public static function notifyUserDeleted($userID) {
    // Klassentermine des Lehrers löschen?
    // 12.09.2015 - erstmal nicht zur Sicherheit bis die Synchronisation zuverlässig läuft.
    // 14.10.2015 -> nein, da eventuell SA bleiben sollen.
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
    return [
        [
            'typ' => 'TRENNER',
            'titel' => 'Farben der Termine im Kalender',
            'text' => 'Hier können Sie festlegen welche Farben die Termine im Klassenkalender haben sollen.'
        ],
        [
            'name' => 'klassenkalender-lnw-color-SCHULAUFGABE',
            'typ' => 'COLOR',
            'titel' => 'Farbe der Schulaufgaben im Kalender',
            'text' => 'Die Textfarbe ist immer weiß. Standard: blau'
        ],
        [
            'name' => 'klassenkalender-lnw-color-STEGREIFAUFGABE',
            'typ' => 'COLOR',
            'titel' => 'Farbe der Stegreifaufgabe im Kalender',
            'text' => 'Die Textfarbe ist immer weiß. Standard: rot'
        ],
        [
            'name' => 'klassenkalender-lnw-color-KURZARBEIT',
            'typ' => 'COLOR',
            'titel' => 'Farbe der Kurzarbeit im Kalender',
            'text' => 'Die Textfarbe ist immer weiß. Standard: lila'
        ],
        [
            'name' => 'klassenkalender-lnw-color-MODUSTEST',
            'typ' => 'COLOR',
            'titel' => 'Farbe der Modusteste im Kalender',
            'text' => 'Die Textfarbe ist immer weiß. Standard: rosa'
        ],
        [
            'name' => 'klassenkalender-lnw-color-PLNW',
            'typ' => 'COLOR',
            'titel' => 'Farbe der praktischen Leistungsnachweise im Kalender',
            'text' => 'Die Textfarbe ist immer weiß. Standard: rot'
        ],
        [
            'name' => 'klassenkalender-lnw-color-NACHHOLSCHULAUFGABE',
            'typ' => 'COLOR',
            'titel' => 'Farbe der Nachholschulaufgaben im Kalender',
            'text' => 'Die Textfarbe ist immer weiß. Standard: blau'
        ],
        [
            'typ' => 'TRENNER',
            'titel' => 'Anzeige von Terminen für Schüler und Eltern',
            'text' => ''
        ],
        [
            'name' => 'klassenkalender-showplnworexafter1day',
            'typ' => 'BOOLEAN',
            'titel' => 'Stegreifaufgaben und praktische Leistungsnachweise einen Tag nach Termin für Schüler und Eltern sichtbar machen?',
            'text' => ''
        ],
        [
            'typ' => 'TRENNER',
            'titel' => 'Anzeigefristen von Terminen für Schüler und Eltern',
            'text' => 'Werden hier Werste größer 0 angegeben, sind die Leistungsnachweise erst diese Anzahl an Tagen vor dem Termin für die Schüler und Lehrer sichtbar.'
        ],
        [
            'name' => 'klassenkalender-lnw-frist-SCHULAUFGABE',
            'typ' => 'NUMMER',
            'titel' => 'Anzeigefrist der Schulaufgaben im Kalender',
            'text' => 'In Tagen. (0 oder leer für sofort anzeigen.)'
        ],
        [
            'name' => 'klassenkalender-lnw-frist-KURZARBEIT',
            'typ' => 'NUMMER',
            'titel' => 'Anzeigefrist der Kurzarbeit im Kalender',
            'text' => 'In Tagen. (0 oder leer für sofort anzeigen.)'
        ],
        [
            'name' => 'klassenkalender-lnw-frist-MODUSTEST',
            'typ' => 'NUMMER',
            'titel' => 'Anzeigefrist der Modusteste im Kalender',
            'text' => 'In Tagen. (0 oder leer für sofort anzeigen.)'
        ],
        [
            'name' => 'klassenkalender-lnw-frist-NACHHOLSCHULAUFGABE',
            'typ' => 'NUMMER',
            'titel' => 'Anzeigefrist der Nachholschulaufgaben im Kalender',
            'text' => 'In Tagen. (0 oder leer für sofort anzeigen.)'
        ],
        [
            'typ' => 'TRENNER',
            'titel' => 'Andere Optionen',
            'text' => ''
        ],
        
        [
            'name' => 'klassenkalender-fachbetreueransicht',
            'typ' => 'BOOLEAN',
            'titel' => 'Für Fachbetreuer die Ansicht der Fachkalender aktivieren?',
            'text' => ''
        ],
    ];
  }


  public static function getSiteDisplayName() {
    return 'Klassenkalender';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array(
        array(
            'groupName' => 'Webportal_Kalender_Admin',
            'beschreibung' => 'Administrator des Kalenders (Kann alle Einträge bearbeiten und löschen).'
        ),
        array(
            'groupName' => 'Webportal_Klassenkalender',
            'beschreibung' => "Zugriff auf die Klassenkalender (alle), ohne Lehrer, Eltern oder Schüler zu sein."
        )
    );
  }

  public static function getAdminGroup() {
    return 'Webportal_Kalender_Admin';
  }

  public static function hasAdmin() {
    return true;
  }

  public static function displayAdministration($selfURL) {

    if($_REQUEST['action'] == "addKalenderAccess") {
      $group = usergroup::getGroupByName("Webportal_Klassenkalender");
      $group->addUser(intval($_POST['userID']));
      header("Location: $selfURL");
      exit(0);
    }

    if($_REQUEST['action'] == "deleteKalenderAccess") {
      $group = usergroup::getGroupByName("Webportal_Klassenkalender");
      $group->removeUser(intval($_REQUEST['userID']));
      header("Location: $selfURL");
      exit(0);
    }

    $html = 'Die Moduladministratoren (und alle globalen Administratoren) haben auf alle Einträge im Klassenkalender Zugriff und können Einträge für alle Lehrer und Fächer anlegen und löschen.';

    // $html .= 'Auf den Klassenkalender haben nur Lehrer Zugriff.';

    $box = administrationmodule::getUserListWithAddFunction($selfURL, "klassenkalenderzugriff", "addKalenderAccess", "deleteKalenderAccess", "Benutzer mit Zugriff auf die Klassenkalender (inkl. Stegreifaufgaben)","Normalweise haben Lehrer, Eltern und Schüler jeweils angepassten Zugriff. Für einen Zugriff auf den kompletten Kalender hier bitte die Benutzer eintragen. (Gilt vor allem für Sekretariatskräfte.)", "Webportal_Klassenkalender");

    $html = "<div class=\"row\"><div class=\"col-md-9\">$html</div><div class=\"col-md-3\">$box</div></div>";

    return $html;
  }

  public static function getAdminMenuGroup() {
    return 'Kalender';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-calendar';
  }


  public static function getAdminMenuIcon() {
    return 'fa fa-child';
  }


  public static function getActionSchuljahreswechsel() {
    return 'Einträge aus dem alten Schuljahr löschen';
  }

  public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
    DB::getDB()->query("DELETE FROM kalender_lnw WHERE eintragDatumStart < '$sqlDateFirstSchoolDay'");
    DB::getDB()->query("DELETE FROM kalender_klassentermin WHERE eintragDatumStart < '$sqlDateFirstSchoolDay'");

  }
}


?>
