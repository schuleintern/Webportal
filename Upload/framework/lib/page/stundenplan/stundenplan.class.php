<?php


class stundenplan extends AbstractPage {

  private $info;

  private $isTeacher = false;

  private $isPupil = false;
  private $pupilGrade = "";

  private $isEsis = false;
  private $esisGrades = array();

  private $isPrint = false;

  private $viewAll = false;

  /**
   * Begin aller Stunden (aus Einstellungen)
   * @var array
   */
  private static $begin = array();

  /**
   * Begin aller Stunden (aus Einstellungen)
   * @var array
   */
  private static $ende = array();


  public function __construct() {
    parent::__construct(array("Stundenplan"));

    $this->checkLogin();

    if(DB::getSession()->isTeacher()) {
      $this->isTeacher = true;
    }
    elseif(DB::getSession()->isPupil()) {
      $this->isPupil = true;
      $this->pupilGrade = addslashes(DB::getSession()->getSchuelerObject()->getKlasse());
    }
    elseif(DB::getSession()->isEltern()) {
      $this->isEsis = true;

      $this->esisGrades = DB::getSession()->getElternObject()->getKlassenAsArray();
    }
    elseif(DB::getSession()->isMember("Webportal_Stundenplananzeige")) {
      $this->viewAll = true;
    }
    else if(DB::getSession()->isAdmin()) {
      $this->viewAll = true;
    }
  }

  public function execute() {

    if(isset($_GET['savePDF'])) $this->isPrint = true;

    if($this->isTeacher) {
      // Alle Pläne dürfen angezeigt werden.
      $plans = array("teacher","room","subject","grade");

      $planShown = false;

      for($i = 0; $i < sizeof($plans); $i++) {
        if($_REQUEST[$plans[$i]] != "") {
          $this->showPlan(array("mode" => $plans[$i], "data" => addslashes($_REQUEST[$plans[$i]])));
          $planShown = true;
        }
        else if($_GET[$plans[$i] . 'Menu'] != "") {
          // Zeigt nur das Menü an (Keine Auswahl)
          $this->showPlan(array("mode" => $plans[$i] . "Menu", "data" => ""));
          $planShown = true;
        }
      }

      if(!$planShown) {
        $this->showPlan(array("mode" => "teacher", "data" => addslashes(DB::getSession()->getTeacherObject()->getKuerzel())));
      }

      exit(0);

    }

    if($this->viewAll) {
      // Alle Pläne dürfen angezeigt werden.
      $plans = array("teacher","room","subject","grade");

      $planShown = false;

      for($i = 0; $i < sizeof($plans); $i++) {
        if($_REQUEST[$plans[$i]] != "") {
          $this->showPlan(array("mode" => $plans[$i], "data" => addslashes($_REQUEST[$plans[$i]])));
          $planShown = true;
        }
        else if($_GET[$plans[$i] . 'Menu'] != "") {
          // Zeigt nur das Menü an (Keine Auswahl)
          $this->showPlan(array("mode" => $plans[$i] . "Menu", "data" => ""));
          $planShown = true;
        }
      }

      if(!$planShown) {
        $this->showPlan(array("mode" => "teacherMenu", "data" => ""));
      }

      exit(0);

    }

    if($this->isTeacher) {
      $this->showPlan(array("mode" => "grade", "data" => $this->pupilGrade));
      exit(0);
    }

    if($this->isEsis) {
      if(sizeof($this->esisGrades) > 0) {

        if(in_array($_REQUEST['grade'], $this->esisGrades)) {
          $this->showPlan(array("mode" => "grade", "data" => $_REQUEST['grade']));
          exit(0);
        }

        else if(sizeof($this->esisGrades) > 0) {
          $this->showPlan(array("mode" => "grade", "data" => $this->esisGrades[0]));
          exit(0);
        }
        else new errorPage("Malformed Request!");

      }
      else {
        DB::showError("Leider ist für Sie keine Klasse Ihrer Kinder hinterlegt.");
        exit(0);
      }
    }

    if($this->isPupil) {

      $myGrades = stundenplandata::getCurrentStundenplan()->getAllMyPossibleGrades(DB::getSession()->getSchuelerObject()->getKlasse());

      if($_REQUEST['grade'] == '') $_REQUEST['grade'] = $myGrades[0];

      if(in_array($_REQUEST['grade'], $myGrades)) $this->showPlan(array("mode" => "grade", "data" => $_REQUEST['grade']));
      else DB::showError("Es wurde eine ungültige Klasse angegeben!");
    }



    if(isset($_GET['savePDF'])) {

      eval("\$stundenplanPrint = \"" . DB::getTPL()->get("stundenplan_pdf") . "\";");

      $stundenplanPrint = ($stundenplanPrint);

      $mpdf=new mPDF('utf-8', 'A4-L');
      $mpdf->Bookmark("Stundenplan");
      $mpdf->WriteHTML($stundenplanPrint);
      $mpdf->Output();

      exit(0);
    }

    if($dontShow || ($_REQUEST['teacher'] == "" && $_REQUEST['grade'] == "" && $_REQUEST['room'] == "" && $_REQUEST['fach'] == "")) {
      $dontShow = true;
    }
    else $dontShow = false;

    if(!$dontShow && ($this->isTeacher || DB::getSession()->isEltern())) {
      // Select Klassenplan
      // Select Raumplan
      // Select Lehrerplan

      if((($this->isTeacher && isset($_REQUEST['teacher'])) || $showAll) && $_REQUEST['teacher'] != "") {

        $i = 0;
        $selectLehrerplan = "<table class=\"table table-bordered table-striped\"><tr><td>";
        $teacher = DB::getDB()->query("SELECT DISTINCT stundeLehrer FROM stundenplan WHERE stundeLehrer != '' ORDER BY stundeLehrer");
        while($t = DB::getDB()->fetch_array($teacher)) {
          $i++;
          if($i == 10) {
            $i = 0;
            $selectLehrerplan .= "</td><td>";
          }
          $selectLehrerplan .= ((isset($_REQUEST['teacher']) && strtolower($_REQUEST['teacher']) == strtolower($t['stundeLehrer'])) ? ("&raquo; <b>" . $t['stundeLehrer'] . "</b><br />") : ("&raquo; <a href=\"index.php?page=stundenplan&teacher=" . $t['stundeLehrer'] . "\">" . $t['stundeLehrer'] . "</a><br />"));
        }
        $selectLehrerplan .= "</tR>";


        $selectLehrerplan .= "</table>";
      }
      if(isset($_REQUEST['grade']) || $showAll || (DB::getSession()->isEltern() && sizeof($klassen) > 1)) {
        $where = "";
        if(DB::getSession()->isEltern()) {
          if(is_array($klassen)) {
            $where .= " AND (";
            for($i = 0; $i < sizeof($klassen); $i++) {
              if($i > 0) $where .= " OR ";
              $where .= "stundeKlasse LIKE '" . $klassen[$i] . "%'";
            }
            $where .= ")";
          }
          else {
            $where = "AND stundeKlasse LIKE '" . $klassen . "%'";
          }
        }

        if(DB::getSession()->isTeacher()) {
            $gradeSelect = "<table class=\"table table-bordered table-striped\">";

            $allGrades = grade::getAllGradesStundenplan();

            $klassenData = array();

            $minStufe = 1000;
            $maxStufe = 0;

            for($i = 0; $i < sizeof($allGrades); $i++) {
              if(substr($allGrades[$i],0,2) >= 10) {
                $stufe = substr($allGrades[$i],0,2);
              }
              else {
                $stufe = substr($allGrades[$i],0,1);
              }

              if(!is_array($klassenData[$stufe]) && $allGrades[$i] != "") {
                $klassenData[$stufe] = array();
                if($stufe < $minStufe) $minStufe = $stufe;
                if($stufe > $maxStufe) $maxStufe = $stufe;
              }
              if($allGrades[$i] != "") $klassenData[$stufe][] = $allGrades[$i];
            }

            // Wie viele Klassenstufen wirklich vorhanden?

            $realCountGrades = 0;
            for($i = $minStufe; $i <= $maxStufe; $i++) {
              if(is_array($klassenData[$i])) $realCountGrades++;
            }

            $perRow = floor($realCountGrades);

            $countRow = 0;

            $gradeSelect .= "<tr>";
            for($i = $minStufe; $i <= $maxStufe; $i++) {
              $gradeSelect .= "<td style=\"text-align:center\">" . $i . ". Klassen</td>";
            }
            $gradeSelect .= "</tr>";

            for($i = $minStufe; $i <= $maxStufe; $i++) {
              if(is_array($klassenData[$i])) {
                if($countRow == 0) $gradeSelect .= "<tr>";
                $countRow++;

                $gradeSelect .= "<td style=\"width:" . floor(100/6) . "%\">";

                for($k = 0; $k < sizeof($klassenData[$i]); $k++) {
                  $gradeSelect .= (($_REQUEST['grade'] == $klassenData[$i][$k]) ? ("&raquo; <b>{$klassenData[$i][$k]}</b><br />") : ("&raquo; <a href=\"index.php?page=stundenplan&grade={$klassenData[$i][$k]}\">{$klassenData[$i][$k]}</a><br />"));
                }


                $gradeSelect .= "</td>";

                if($countRow == $perRow) {
                  $countRow = 0;
                  $gradeSelect .= "</tr>";
                }
              }
            }

            $gradeSelect .= "</table>";

            $selectKlassenplan = $gradeSelect;

        }
        else {
          $selectKlassenplan = "<table class=\"table table-bordered table-striped\"><tr>";
          $klasse = DB::getDB()->query("SELECT DISTINCT stundeKlasse FROM stundenplan WHERE stundeKlasse != '' $where ORDER BY stundeKlasse");
          while($t = DB::getDB()->fetch_array($klasse)) {
            $selectKlassenplan .= "<td>" . ((isset($_REQUEST['grade']) && $_REQUEST['grade'] == $t['stundeKlasse']) ? ("<b>" . $t['stundeKlasse'] . "</b>") : ("<a href=\"index.php?page=stundenplan&grade=" . $t['stundeKlasse'] . "\">" . $t['stundeKlasse'] . "</a>")) . "</td>";
          }
          $selectKlassenplan .= "</tr></table>";
        }
      }


      if(isset($_REQUEST['room']) || ($this->isTeacher && isset($_REQUEST['room']) || $showAll)) {
        $selectRaumplan = "<form action=\"index.php?page=stundenplan&gplsession={$_REQUEST['gplsession']}\" method=\"post\"><select name=\"room\" class=\"form-control\">";
        $raum = DB::getDB()->query("SELECT DISTINCT stundeRaum FROM stundenplan WHERE stundeRaum != '' ORDER BY stundeRaum");
        while($t = DB::getDB()->fetch_array($raum)) $selectRaumplan .= "<option value=\"" . $t['stundeRaum'] . "\"" . ((isset($_REQUEST['room']) && $_REQUEST['room'] == $t['stundeRaum']) ? (" selected=\"selected\"") : ("")) . ">" . $t['stundeRaum'] . "</option>\n";
        $selectRaumplan .= "</select> <input type=\"submit\" value=\"Raumplan anzeigen\" class=\"form-control\"></form>";
      }

      if(isset($_REQUEST['fach']) || ($this->isTeacher && isset($_REQUEST['fach']) || $showAll)) {
        $selectFachplan = "<form action=\"index.php?page=stundenplan\" method=\"post\"><select name=\"fach\" class=\"form-control\">";
        $raum = DB::getDB()->query("SELECT DISTINCT stundeFach FROM stundenplan WHERE stundeFach != '' ORDER BY stundeFach");
        while($t = DB::getDB()->fetch_array($raum)) $selectFachplan .= "<option value=\"" . $t['stundeFach'] . "\"" . ((isset($_REQUEST['fach']) && $_REQUEST['fach'] == $t['stundeFach']) ? (" selected=\"selected\"") : ("")) . ">" . $t['stundeFach'] . "</option>\n";
        $selectFachplan .= "</select> <input type=\"submit\" value=\"Fachplan anzeigen\" class=\"form-control\"></form>";
      }
    }


    /*
    if($this->isTeacher) {
      eval("\$menu = \"" . DB::getTPL()->get("stundenplanterminemenu_teacher") . "\";");
    }
    else {
      eval("\$menu = \"" . DB::getTPL()->get("stundenplanterminemenu_pupil") . "\";");
    }

    eval("echo(\"" . DB::getTPL()->get("stundenplan") . "\");"); **/
  }

  private function getDayName($i) {
    return (($i == 0) ? ("Mo") : (($i== 1) ? ("Di") : (($i == 2) ? ("Mi") : (($i == 3) ? ("Do") : (($i == 4) ? ("Fr") : ("X"))))));
  }

  private static function initStundenZeiten() {
    if(sizeof(self::$begin)  == 0) {
      for($i = 1; $i < 6; $i++) {
        if(DB::getSettings()->getValue("stundenplan-everydayothertimes") > 0 || $i == 1) {
          for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
              
            $dataStart = explode(":",DB::getSettings()->getValue("stundenplan-stunde-$i-$s-start"));
            $dataEnde = explode(":",DB::getSettings()->getValue("stundenplan-stunde-$i-$s-ende"));



            self::$begin[$i][$s] = (int)$dataStart[0] * 60+ $dataStart[1];
            self::$ende[$i][$s] =  (int)$dataEnde[0] * 60+$dataEnde[1];
          }
        } elseif($i > 1) {
          self::$begin[$i] = self::$begin[1];
          self::$ende[$i] = self::$ende[1];
        }
      }
    }
  }

  public static function getCurrentStunde($stunde=-1,$minute=-1) {

    self::initStundenZeiten();
    
    // Debugger::debugObject(self::$begin,1);

    $day = DateFunctions::getWeekDayFromNaturalDateISO(DateFunctions::getTodayAsNaturalDate());

    if($day > 5) return 0;		// Wochenende

    if($stunde < 0) $stunde = date("H");
    if($stunde < 0) $minute = date("i");
    
    $currentTime = mktime($stunde*1, $minute*1);
    


    for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
        $startTime = self::getStartUnixTimeStunde($day, $s);
        $endTime = self::getEndUnixTimeStunde($day, $s);
        
        // Debugger::debugObject($startTime,1);
        
        
        if($currentTime >= $startTime && $currentTime <= $endTime) return $s;
    }

    return 0;		// Keine aktuelle Stunde oder Pause
  }


  public static function getStartTimeStunde($tag,$stunde) {
    self::initStundenZeiten();

    return ((strlen(floor(self::$begin[$tag][$stunde] / 60)) == 1) ? "0" : "") . floor(self::$begin[$tag][$stunde] / 60) . ":" . ((strlen(self::$begin[$tag][$stunde] % 60) == 1) ? ("0" . self::$begin[$tag][$stunde] % 60) : (self::$begin[$tag][$stunde] % 60));
  }

  public static function getStartUnixTimeStunde($tag,$stunde) {
      self::initStundenZeiten();

      return mktime(floor(self::$begin[$tag][$stunde]/60),self::$begin[$tag][$stunde] % 60);
  }
  
  public static function getEndTimeStunde($tag,$stunde) {
    self::initStundenZeiten();

    return ((strlen(floor(self::$ende[$tag][$stunde] / 60)) == 1) ? "0" : "") . floor(self::$ende[$tag][$stunde] / 60) . ":" . ((strlen(self::$ende[$tag][$stunde] % 60) == 1) ? ("0" . self::$ende[$tag][$stunde] % 60) : (self::$ende[$tag][$stunde] % 60));
  }
  
  public static function getEndUnixTimeStunde($tag,$stunde) {
      self::initStundenZeiten();
      
      return mktime(floor(self::$ende[$tag][$stunde]/60),self::$ende[$tag][$stunde] % 60);
  }

  private function showPlan($data) {
    // array("mode" => "teacher", "data" => addslashes(DB::getSession()->getRealTeacherName())));

    $currentPlanID = stundenplandata::getCurrentStundenplanID();
    $showPlanID = $currentPlanID;

    if(!($currentPlanID > 0)) {
      new errorPage("Leider steht aktuell kein Stundenplan zur Verfügung!");
    }



    if(isset($_REQUEST['stundenplanID'])) {
      if(stundenplandata::isValidStundenplanID(intval($_REQUEST['stundenplanID']))) {
        $showPlanID = intval($_REQUEST['stundenplanID']);
      }
    }

    if(isset($_REQUEST['switchToPlanID'])) {
      if(stundenplandata::isValidStundenplanID(intval($_REQUEST['switchToPlanID']))) {
        $showPlanID = intval($_REQUEST['switchToPlanID']);
      }
    }


    $mode = "teacher";

    if(isset($data['mode'])) $mode = $data['mode'];

    if($showPlanID > 0) {
      // Plan laden
      $stundenplan = new stundenplandata($showPlanID);

      $showPlan = false;
      $showMenu = false;

      $sData = array();
      $menuData = array();

      $basicLink = "index.php?page=stundenplan&stundenplanID=" . $showPlanID;

      switch($mode) {
        case "teacher":
          $showPlan = true;
          $showMenu = true;
          $sData = $stundenplan->getPlan(array("teacher", $data['data']));
          
          $link = "index.php?page=stundenplan&stundenplanID=" . $showPlanID . "&teacher=" . $data['data'];

          $teacherObject = lehrer::getByKuerzel($data['data']);
          
          if($teacherObject != null) {
              $showTitle = $teacherObject->getName() . ", " . $teacherObject->getRufname() . " (" . $teacherObject->getKuerzel() . ")";
          }
          else {
              $showTitle = "Lehrer(in) " . $data['data'];
          }
          
          

        break;

        case "room":
          $showPlan = true;
          $showMenu = true;
          $sData = $stundenplan->getPlan(array("room", $data['data']));
          $link = "index.php?page=stundenplan&stundenplanID=" . $showPlanID . "&room=" . urlencode( $data['data'] );
          $showTitle = "Raum " . $data['data'];
        break;

        case "subject":
          $showPlan = true;
          $showMenu = true;
          $sData = $stundenplan->getPlan(array("subject", $data['data']));
          $link = "index.php?page=stundenplan&stundenplanID=" . $showPlanID . "&subject=" . urlencode( $data['data'] );
          $showTitle = "Fach " . $data['data'];
        break;

        case "grade":
          $showPlan = true;
          $showMenu = true;
          $sData = $stundenplan->getPlan(array("grade", $data['data']));
          $link = "index.php?page=stundenplan&stundenplanID=" . $showPlanID . "&grade=" . urlencode( $data['data'] );
          $showTitle = "Klasse " . $data['data'];
        break;

        case "teacherMenu":
          $showPlan = false;
          $showMenu = true;


          $link = "index.php?page=stundenplan&stundenplanID=" . $showPlanID . "&teacherMenu=1";

        break;

        default:
          // Das sollte nicht passieren --> Ganzer Plan ;-)
          $sData = $stundenplan->getPlan();
        break;

      }

      $gueltigKeit = "";

      $planNavigation = "<table class=\"table table-bordered table-striped\"><tr>";

      $allCurrentPlans = stundenplandata::getAllCurrentPlans();

      for($i = 0; $i < sizeof($allCurrentPlans); $i++) {
        $planNavigation .= "<td width=\"" . floor(100/sizeof($allCurrentPlans)) . "%\"" . (($allCurrentPlans[$i]['stundenplanID'] == $showPlanID) ? (" style=\"background-color:#CDCDCD\"") : ("")) . "><i class=\"fa fa-table\"></i> <a href=\"" . $link . "&switchToPlanID=" . $allCurrentPlans[$i]['stundenplanID'] . "\">";

        $planNavigation .= (($allCurrentPlans[$i]['stundenplanID'] == $showPlanID) ? ("<b>") : (""));
        $planNavigation .= "Stundenplan ";

        $planNavigation .= "gültig ab " . functions::getFormatedDateFromSQLDate($allCurrentPlans[$i]['stundenplanAb']);

        if($allCurrentPlans[$i]['stundenplanBis'] != "") {
          $bisDate = explode("-", $allCurrentPlans[$i]['stundenplanBis']);
          $planNavigation .= " bis " . functions::getFormatedDateFromSQLDate($allCurrentPlans[$i]['stundenplanBis']);
        }

        $planNavigation .= (($allCurrentPlans[$i]['stundenplanID'] == $showPlanID) ? ("</b>") : (""));

        $planNavigation .= "</a></td>";

        if($allCurrentPlans[$i]['stundenplanID'] == $showPlanID) {
          $gueltigKeit .= "Gültig ab " . functions::getFormatedDateFromSQLDate($allCurrentPlans[$i]['stundenplanAb']);

          if($allCurrentPlans[$i]['stundenplanBis'] != "") {
            $gueltigKeit .= " bis " . functions::getFormatedDateFromSQLDate($allCurrentPlans[$i]['stundenplanBis']);
          }
        }
      }

      $planNavigation .= "</tr></table>";

      if($showPlan) {
        $maxcells = array(
          0 => 0,
          1 => 0,
          2 => 0,
          3 => 0,
          4 => 0,
          5 => 0
        );

        for($s = 0; $s < 5; $s++) {
          for($t = 0; $t < 11; $t++) {
            $a = sizeof((array)$sData[$s][$t]);
            if($maxcells[$s] < $a) $maxcells[$s] = $a;
          }
        }



        if($this->isPrint) $html = "<table border=\"1\" width=\"100%\" cellpadding=\"2\" nobr=\"true\">";
        else $html = "<table class=\"table table-bordered table-striped table-hover\">";

        if($this->isPrint) $html .= "<tr><th style=\"width:10%\"><b>Stunde</b></th><th style=\"width:18%\" colspan=\"{$maxcells[0]}\"><b>Montag</b></th><th style=\"width:18%\" colspan=\"{$maxcells[1]}\"><b>Dienstag</b></th><th style=\"width:18%\" colspan=\"{$maxcells[2]}\"><b>Mittwoch</b></th><th style=\"width:18%\" colspan=\"{$maxcells[3]}\"><b>Donnerstag</b></th><th style=\"width:18%\" colspan=\"{$maxcells[4]}\"><b>Freitag</b></th></tr>";
        else $html .= "<tr><th style=\"width:10%\">Stunde</th><th style=\"width:18%; \" colspan=\"{$maxcells[0]}\">Montag</th><th style=\"width:18%; \" colspan=\"{$maxcells[1]}\">Dienstag</th><th style=\"width:18; %\" colspan=\"{$maxcells[2]}\">Mittwoch</th><th style=\"width:18%; \" colspan=\"{$maxcells[3]}\">Donnerstag</th><th style=\"width:18%; \" colspan=\"{$maxcells[4]}\">Freitag</th></tr>";

        $isTeacher = $this->isTeacher;

        if($this->viewAll) $this->isTeacher = true;

        $maxStunden = DB::getSettings()->getValue('stundenplan-anzahlstunden');

        
        $koppelnummer = 0;
        
        
        
        for($s = 0; $s < $maxStunden; $s++) {
          $html .= "<tr><td><strong>" . ($s+1) . ". Stunde</strong><br /><small>" . self::getStartTimeStunde(1, $s+1) . " - " . self::getEndTimeStunde(1, $s+1) . "</small></td>";
          for($t = 0; $t < 5; $t++) {

            $stundenData = $sData[$t][$s];

            if(sizeof($stundenData) > 0) {
              // $html .= "<td ><table border=0 width=\"100%\"><tr>";
              for($i = 0; $i < sizeof($stundenData); $i++) {
                if(sizeof($stundenData) == 1) {
                  $cellspan = $maxcells[$t];
                }
                elseif($i == (sizeof($stundenData)-1) && $maxcells[$t] != sizeof($stundenData)) {
                  $cellspan = $maxcells[$t] - sizeof($stundenData) + 1;
                }
                else $cellspan = 1;

                if($this->isPrint) {

                      $koppelInfo = "";
                      
                      if($mode == 'teacher') {
                          // Kopplungen zeigen
                          
                          
                          if($stundenData[$i]['grade'] != "") {
                              
                              $koppelstunden = [];
                              
                              $klassenplan = $stundenplan->getPlan(array("grade", $stundenData[$i]['grade']));
                              
                              $possibleKoppel = $klassenplan[$t][$s];
                              
                              for($l = 0; $l < sizeof($possibleKoppel); $l++) {
                                  if($possibleKoppel[$l]['teacher'] != $stundenData[$i]['teacher'] && $s) $koppelstunden[] = $possibleKoppel[$l];
                              }
                              
                              if(sizeof($koppelstunden) > 0) {
                                  
                                  $koppelnummer++;
                                                           
                                  if($koppelStundenPrint != "") $koppelStundenPrint .= ' | ';
                                  
                                  $koppelStundenPrint .= "<small>*$koppelnummer ";
                                  for($l = 0; $l < sizeof($koppelstunden); $l++) {
                                      if($l > 0) $koppelStundenPrint .= "; ";
                                      $koppelStundenPrint .= $koppelstunden[$l]['subject'] . " bei " . $koppelstunden[$l]['teacher'] . " in " . $koppelstunden[$l]['room'];
                                  }
                                  
                                  $koppelStundenPrint .= "</small>";

                                  $koppelInfo = ' <small>*' . $koppelnummer . "</small>";
                              }
                              
                          }
                          
                      }

                    $html .= "<td align=\"center\" colspan=\"$cellspan\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><b>" . $stundenData[$i]['subject'] . "</b></td><td>" .


                        $stundenData[$i]['grade']  . $koppelInfo . "</td></tr><tr><td>" .

                        $stundenData[$i]['room'] . "</td><td>" .

                        $stundenData[$i]['teacher'] . "</td></tr></table>";
                      
                      
                      $html .= "</td>\r\n";


                }
                else if($this->isTeacher) {     // Mit Links
                  $html .= "<td align=\"center\" style=\"width:" . floor(18/$maxcells[$t]) . "%" . (($i == 0) ? ("; ") : ("")) . "\" colspan=\"$cellspan\"><div style=\"display:block; text-align:left; float:left;\"><b><a href=\"$basicLink&subject=" . $stundenData[$i]['subject'] . "\">" . $stundenData[$i]['subject'] . "</a></b></div>" .


                    "<div style=\"display:block; text-align:right;\"><b><u><a href=\"$basicLink&grade=" . urlencode($stundenData[$i]['grade']) . "\">" .  $stundenData[$i]['grade'] . "</a></u></b></div><br />";
                  

                  

                  $html .=   "<div style=\"display:block; text-align:left; float:left;\"><i><a href=\"$basicLink&room=" . $stundenData[$i]['room'] . "\">" .  $stundenData[$i]['room'] . "</a></i></div>" .

                    "<div style=\"display:block; text-align:right;\"><i><a href=\"$basicLink&teacher=" . $stundenData[$i]['teacher'] . "\">" . $stundenData[$i]['teacher'] . "</a></i></div>";
                    
                 
                  if($mode == 'teacher') {
                      // Kopplungen zeigen
                      
                     
                      if($stundenData[$i]['grade'] != "") {
                          
                          $koppelstunden = [];
                      
                          $klassenplan = $stundenplan->getPlan(array("grade", $stundenData[$i]['grade']));
                          
                          $possibleKoppel = $klassenplan[$t][$s];
                          
                          for($l = 0; $l < sizeof($possibleKoppel); $l++) {
                              if($possibleKoppel[$l]['teacher'] != $stundenData[$i]['teacher'] && $s) $koppelstunden[] = $possibleKoppel[$l];
                          }
                          
                          if(sizeof($koppelstunden) > 0) {
                              $html .= "<small>";
                              for($l = 0; $l < sizeof($koppelstunden); $l++) {
                                  if($l > 0) $html .= "; ";
                                  $html .= $koppelstunden[$l]['subject'] . " bei " . $koppelstunden[$l]['teacher'] . " in " . $koppelstunden[$l]['room'];
                              }
                              
                              $html .= "</small>";
                          }

                      }
                      
                  }
                  
                 $html .= "</td>\n";

                }
                else {
                  $html .= "<td align=\"center\" style=\"width:" . floor(18/$maxcells[$t]) . "%" . (($i == 0) ? ("; ") : ("")) . "\" colspan=\"$cellspan\"><div style=\"display:block; text-align:left; float:left;\">" . $stundenData[$i]['subject'] . "</div>" .


                      "<div style=\"display:block; text-align:right;\">" .  $stundenData[$i]['grade'] . "</div><br />" .

                      "<div style=\"display:block; text-align:left; float:left;\">" . $stundenData[$i]['room'] . "</div>" .

                      "<div style=\"display:block; text-align:right;\">" . $stundenData[$i]['teacher'] . "</div></td>\n";
                  


                }
              }
              // $html .= "</td>";
            }
            else {
              $cellspan = $maxcells[$t];
              if($this->isPrint) $html .= "<td colspan=\"$cellspan\">&nbsp;</td>\n";
              else $html .= "<td colspan=\"$cellspan\">&nbsp;</td>\n";

            }
          }
          $html .= "</tr>\n";
        }

        $html .= "</table>\n";
      }

      if(!$this->isPrint) {

        if($showPlan) {
          $pdfSaveLink = " <a href=\"$link&savePDF=1\" class='btn btn-default'><i class='fas fa-file-pdf'></i> Download A4</a>";
          $pdfSaveLink .= " <a href=\"$link&savePDF=1&size=A3\" class='btn btn-default'><i class='fas fa-file-pdf'></i> Download A3</a>";

          //$pdfSaveLink = "$link&savePDF=1\" method=\"post\"><button type=\"submit\" class=\"btn btn-primary\"><i class=\"fa fa-file-pdf\"></i> Druckversion</button></form>";
          //$pdfSaveLink = "<form action=\"$link&savePDF=1\" method=\"post\"><button type=\"submit\" class=\"btn btn-primary\"><i class=\"fa fa-file-pdf\"></i> Druckversion</button></form>";

        }
        else $pdfSaveLink = "";

        $selectMenu = "";
        
        $selectMenuModals = "";

        if($showMenu && ($this->isTeacher || $this->viewAll)) {
            
          $selectMenu = "<div class='row'><div class='col-md-3'><button class=\"btn btn-block\" data-toggle=\"modal\" data-target=\"#selectTeacher\"><i class=\"fa fa-female\"></i> Lehrkraftpläne</button></div> ";
          $selectMenu .= "<div class='col-md-3'><button class=\"btn btn-block\" data-toggle=\"modal\" data-target=\"#selectGrade\"><i class=\"fa fa-user-friends\"></i> Klassenpläne</button></div> ";
          $selectMenu .= "<div class='col-md-3'><button class=\"btn btn-block\" data-toggle=\"modal\" data-target=\"#selectRoom\"><i class=\"fa fa-door-open\"></i> Raumpläne</button></div> ";
          $selectMenu .= "<div class='col-md-3'><button class=\"btn btn-block\" data-toggle=\"modal\" data-target=\"#selectSubject\"><i class=\"fa fa-flask\"></i> Fachpläne</button></div></div>";

          $allLehrer    = $stundenplan->getAll("teacher");
          $allGrades    = $stundenplan->getAll("grade"  );
          $allRooms     = $stundenplan->getAll("room"   );
          $allSubjects  = $stundenplan->getAll("subject");


          $allTeacherObjects = lehrer::getAll();

          $selectMenuModals .= '<div class="modal fade" id="selectTeacher" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    <div class="modal-header">	    
		    	<h4 class="modal-title"><i class="fa fa-female"></i> Lehrer auswählen</h4>
		      </div>
		      <div class="modal-body">
		      
		      <p>
		        <select id="lehrerKuerzel" class="form-control">
		        ';


          for($i = 0; $i < sizeof($allLehrer); $i++) {

            $selectMenuModals .= "<option value=\"" . $allLehrer[$i] . "\">";


            $found = false;
            for($t = 0; $t < sizeof($allTeacherObjects); $t++) {
              if($allLehrer[$i] == $allTeacherObjects[$t]->getKuerzel()) {
                $selectMenuModals .= $allTeacherObjects[$t]->getKuerzel() . " - " . $allTeacherObjects[$t]->getDisplayNameMitAmtsbezeichnung();
                $found = true;
                break;
              }
            }

            if(!$found) $selectMenuModals .= $allLehrer[$i];

            $selectMenuModals .= "</option>";

          }


          $selectMenuModals .= '        
		        
		        
		        </select>
		      </p>	      
		      <div class="row"><div class="col-md-4">';
          
          

          
          $lastLetter = "";
          
          
          
          for($i = 0; $i < sizeof($allLehrer); $i++) {
              
              $first = substr($allLehrer[$i],0,1);
              
              if($first != $lastLetter) {
                  $selectMenuModals .= "<h4>" . $first . "</h4>";
                  $lastLetter = $first;
              }
              $selectMenuModals .= "<a href=\"" . $basicLink . "&teacher=" . $allLehrer[$i] . "\">";
              
              
              $found = false;
              for($t = 0; $t < sizeof($allTeacherObjects); $t++) {
                  if($allLehrer[$i] == $allTeacherObjects[$t]->getKuerzel()) {
                      $selectMenuModals .= $allTeacherObjects[$t]->getKuerzel() . " - " . $allTeacherObjects[$t]->getDisplayNameMitAmtsbezeichnung();
                      $found = true;
                      break;
                  }
              }
              
              if(!$found) $selectMenuModals .= $allLehrer[$i];
              
              $selectMenuModals .= "</a><br />";
              
              if($i == round(sizeof($allLehrer) / 3) || $i == 2*round(sizeof($allLehrer) / 3)) $selectMenuModals .= "</div><div class=\"col-md-4\">";


          }
          
          $selectMenuModals .= '</div></div>
                  </div>
    		      </div>
    		     </div>
    	    </div>
    	    
    	    <script>
    	    $(document).ready(function() {
    $(\'#lehrerKuerzel\').select2({
        dropdownParent: $(\'#selectTeacher\'),
        placeholder: "Lehrkraft direkt auswählen",
        allowClear: true,
        width: \'100%\'
    });
    
    $(\'#lehrerKuerzel\').on(\'select2:select\', function (e) {
        window.location.href=\'' . $basicLink . '&teacher=\' + e.params.data.id;    
});
});
    	    </script>
    	    
    	    ';
          
          
          ///// Klassen
          
          $selectMenuModals .= '<div class="modal fade" id="selectGrade" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    <div class="modal-header">
		    	<h4 class="modal-title"><i class="fa fa-group"></i> Klasse auswählen</h4>
		      </div>
		      <div class="modal-body"><div class="row"><div class="col-md-6">';
          
          $completeGradeData = [];
          
          for($i = 1; $i <= 15; $i++) {
              $completeGradeData[$i . '. Klassen'] = [];
          }
          
          $completeGradeData['Sonstige'] = [];
          
          for($i = 0; $i < sizeof($allGrades); $i++) {
              $found = false;
              for($j = 2; $j <= 15; $j++) {
                  if(substr($allGrades[$i],0,strlen($j)) == $j) {
                      $completeGradeData[$j . '. Klassen'][] = $allGrades[$i];
                      $found = true;
                  }
              }
              
              if(!$found) $completeGradeData['Sonstige'][] = $allGrades[$i];
          }

          $numStufenWithGrades = 0;
          
          foreach ($completeGradeData as $jgs => $klassen) {
              if(sizeof($klassen) > 0) {
                  $numStufenWithGrades++;
              }
          }
          
          $currentNummer = 0;
          
          $half = round($numStufenWithGrades / 2);
          
          foreach ($completeGradeData as $jgs => $klassen) {
              if(sizeof($klassen) > 0) {
                  $selectMenuModals .= "<h4>" . $jgs . "</h4>";
                  
                  $currentNummer++;
                  
                  for($g = 0; $g < sizeof($klassen); $g++) {
                      $selectMenuModals .= "<a href=\"" . $basicLink . "&grade=" . urlencode($klassen[$g]) . "\">";
                      $selectMenuModals .= "Klasse " . $klassen[$g];
                      
                      
                      $selectMenuModals .= "</a><br />";
                  }
                  
                  if($currentNummer == $half+1) {
                      $selectMenuModals .= "</div><div class=\"col-md6\">";
                  }
                  
              }
          }
          
          $selectMenuModals .= '</div></div>
                  </div>
    		      </div>
    		     </div>
    	    </div>';
          
          
          // Raum
          
          $selectMenuModals .= '<div class="modal fade" id="selectRoom" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    <div class="modal-header">
		    	<h4 class="modal-title"><i class="fa fa-home"></i> Raum auswählen</h4>
		      </div>
		      <div class="modal-body"><div class="row"><div class="col-md-6">';
          
                    
          
          $lastLetter = "";
          
          
          
          for($i = 0; $i < sizeof($allRooms); $i++) {
              
              if(is_numeric(substr($allRooms[$i],-1))) {
              
                  $first = substr($allRooms[$i],0,strlen($allRooms[$i])-1);
                  
                  if($first != $lastLetter) {
                      $selectMenuModals .= "<h4>" . $first . "</h4>";
                      $lastLetter = $first;
                  }
              
              }
              else {
                  $selectMenuModals .= "<h4>" . $allRooms[$i] . "</h4>";
              }
              $selectMenuModals .= "<a href=\"" . $basicLink . "&room=" . $allRooms[$i] . "\">";
              
              $selectMenuModals .= $allRooms[$i];
              
              $selectMenuModals .= "</a><br />";
              
              if($i == round(sizeof($allRooms) / 2)) $selectMenuModals .= "</div><div class=\"col-md-6\">";
          }
          
          $selectMenuModals .= '</div></div>
                  </div>
    		      </div>
    		     </div>
    	    </div>';
          
          
          // Fach
          
          $selectMenuModals .= '<div class="modal fade" id="selectSubject" tabindex="-1" role="dialog">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    <div class="modal-header">
		    	<h4 class="modal-title"><i class="fa fa-flask"></i> Fach auswählen</h4>
		      </div>
		      <div class="modal-body"><div class="row"><div class="col-md-6">';
          
          
          
          $lastLetter = "";
          
          
          
          for($i = 0; $i < sizeof($allSubjects); $i++) {

              $selectMenuModals .= "<a href=\"" . $basicLink . "&subject=" . $allSubjects[$i] . "\">";
              
              $selectMenuModals .= $allSubjects[$i];
              
              $selectMenuModals .= "</a><br />";
              
              if($i == round(sizeof($allSubjects) / 2)) $selectMenuModals .= "</div><div class=\"col-md-6\">";
          }
          
          $selectMenuModals .= '</div></div>
                  </div>
    		      </div>
    		     </div>
    	    </div>';
          
          
          /*$selectMenu = "<form action=\"$basicLink\" method=\"post\" id=\"teacherform\"><div class=\"input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-user\"></i></span><select name=\"teacher\" class=\"form-control\" onchange=\"this.form.submit()\">";
          $selectMenu .= "<option value=\"\">Lehrerpläne</option>";
          
          for($i = 0; $i < sizeof($allLehrer); $i++) {
              $selectMenu .= "<option value=\"" . $allLehrer[$i] . "\"" . (($_REQUEST['teacher'] == $allLehrer[$i]) ? (" selected=\"seleced\"") : ("")) . ">" . $allLehrer[$i] . "</option>\n";
          }
          $selectMenu .= "</select></div></form></div>";
          
          $selectMenu .= "<div class=\"col-md-3\"><form action=\"$basicLink\" method=\"post\"><div class=\"input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-users\"></i></span><select name=\"grade\" class=\"form-control\" onchange=\"this.form.submit()\">";
          $selectMenu .= "<option value=\"\">Klassenpläne</option>";
          
          for($i = 0; $i < sizeof($allGrades); $i++) {
              $selectMenu .= "<option value=\"" . $allGrades[$i] . "\"" . (($_REQUEST['grade'] == $allGrades[$i]) ? (" selected=\"seleced\"") : ("")) . ">" . $allGrades[$i] . "</option>\n";
          }
          $selectMenu .= "</select></div></form></div>";
          
          $selectMenu .= "<div class=\"col-md-3\"><form action=\"$basicLink\" method=\"post\"><div class=\"input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-home\"></i></span><select name=\"room\" class=\"form-control\" onchange=\"this.form.submit()\">";
          
          $selectMenu .= "<option value=\"\">Raumpläne</option>";
          for($i = 0; $i < sizeof($allRooms); $i++) {
              $selectMenu .= "<option value=\"" . $allRooms[$i] . "\"" . (($_REQUEST['room'] == $allRooms[$i]) ? (" selected=\"seleced\"") : ("")) . ">" . $allRooms[$i] . "</option>\n";
          }
          $selectMenu .= "</select></div></form></div>";
          
          $selectMenu .= "<div class=\"col-md-3\"><form action=\"$basicLink\" method=\"post\"><div class=\"input-group\"><span class=\"input-group-addon\"><i class=\"fa fa-flask\"></i></span><select name=\"subject\" class=\"form-control\" onchange=\"this.form.submit()\">";
          
          $selectMenu .= "<option value=\"\">Fachpläne</option>";
          for($i = 0; $i < sizeof($allSubjects); $i++) {
              $selectMenu .= "<option value=\"" . $allSubjects[$i] . "\"" . (($_REQUEST['subject'] == $allSubjects[$i]) ? (" selected=\"seleced\"") : ("")) . ">" . $allSubjects[$i] . "</option>\n";
          }
          $selectMenu .= "</select></div></form></div>";
          
          
          **/
          /**
          $selectMenu .= "<div class=\"row\">";






          $selectMenu .= "</div>";
          
          
           * 
           */
        }

        parent::__construct(array("Stundenplan","Stundenplan " . $showTitle));
        eval("echo(\"" . DB::getTPL()->get("stundenplan/index") . "\");");
        PAGE::kill(true);
      }
      else {
        

        eval("\$stundenplanPrint = \"" . DB::getTPL()->get("stundenplan/stundenplan_pdf") . "\";");

        $stundenplanPrint = str_replace(" colspan=\"1\"", "", $stundenplanPrint);

        // echo($stundenplanPrint);
        // die();

        $pdf = new PrintNormalPageA4WithHeader("Stundenplan $showTitle",

            $_REQUEST['size'] == 'A3' ? 'A3' : ($_REQUEST['size'] == 'A5' ? 'A5' : 'A4'), 'L');
        $pdf->setHTMLContent($stundenplanPrint);
        $pdf->setPrintedDateInFooter();
        $pdf->send();

        exit(0);
      }

    }
    else {
      // Kein Plan zum anzeigen
    }
  }

  public static function getNotifyItems() {

  }

  public static function hasSettings() {
    return sizeof(self::getSettingsDescription()) > 0;
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
    $settings = array();

    /*$settings[] = array(
        'name' => "stundenplan-anzahlplaene",
        'typ' => 'NUMMER',
        'titel' => "Anzahl der Pläne, die sich abwechseln",
        'text' => "Wie viele verschiedene Pläne sind im Wechsel verfügbar? Bei 1 ist jede Woche der gleiche Plan. Bei 2 wechselt sich der Plan jede Woche ab."
    );*/

    $settings[] = array(
        'name' => "stundenplan-everydayothertimes",
        'typ' => 'BOOLEAN',
        'titel' => "Verschiedene Zeiten für jeden Tag definieren?",
        'text' => "Nach dem setzen dieser Einstellung bitte einmal abspeichern und zu den Einstellungen zurückkehren, um die einzelnen Tage zu sehen."
    );

    $settings[] = array(
        'name' => "stundenplan-anzahlstunden",
        'typ' => 'NUMMER',
        'titel' => "Maximale Anzahl der Stunden pro Tag",
        'text' => "Nach dem setzen dieser Einstellung bitte einmal abspeichern und zu den Einstellungen zurückkehren, um die einzelnen Tage zu sehen."
    );

    $tage = 1;

    if(DB::getSettings()->getValue("stundenplan-everydayothertimes") == 1) {
      $tage = 5;
    }

    if(DB::getSettings()->getValue("stundenplan-anzahlstunden") > 0) {
      $stunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
    }
    else $stunden = 10;

    $tagNamen = array("","Montag","Dienstag","Mittwoch","Donnerstag","Freitag");


      for($i = 1; $i <= $stunden; $i++) {
        for($t = 1; $t <= $tage; $t++) {
          $settings[] = array(
            'name' => "stundenplan-stunde-$t-$i-start",
            'typ' => 'UHRZEIT',
            'titel' => (($tage > 1) ? $tagNamen[$t] . "<br />" : "") . "Beginn der $i. Stunde",
            'text' => "Format: HH:mm<br /><b>Achten Sie bitte darauf führende Nullen mit einzugeben! Geben Sie bitte keine Leerzeichen ein.</b>",
            'required' => true
          );
        }

        for($t = 1; $t <= $tage; $t++) {
          $settings[] = array(
            'name' => "stundenplan-stunde-$t-$i-ende",
            'typ' => 'UHRZEIT',
            'titel' =>(($tage > 1) ? $tagNamen[$t] . "<br />" : "") . "Ende der $i. Stunde",
              'text' => "Format: HH:mm<br /><b>Achten Sie bitte darauf führende Nullen mit einzugeben! Geben Sie bitte keine Leerzeichen ein.</b>",
            'required' => true
          );
        }

    }

    return $settings;
  }

  public static function getSiteDisplayName() {
    return "Stundenplan";
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array(
        array(
            'groupName' => 'Webportal_Stundenplananzeige',
            'beschreibung' => 'Zur Anzeige des Stundenplans ohne Lehrer, Schüler oder Eltern zu sein.'
        )
    );

  }

  public static function siteIsAlwaysActive() {return true;}

  public static function hasAdmin() {
    return true;
  }

  public static function getAdminMenuIcon() {
    return 'fa fa-table';
  }

  public static function getAdminMenuGroupIcon() {
    return 'fa fa-table';
  }

  public static function getAdminMenuGroup() {
    return 'Stundenplan';
  }

  public static function getAdminGroup() {
    return 'Webportal_Stundenplan_Admin';
  }

  public static function displayAdministration($selfURL) {
    // Liste alle Pläne auf.

    $currentPlanID = stundenplandata::getCurrentStundenplanID();

    if($_GET['uploadPlan'] > 0) {
      // Aktuellen Plan abschließen einen tag vor Startdatum

      if(!DateFunctions::isNaturalDate($_POST['newPlanStart'])) {
        new errorPage("Das Datum ist ungültig!");
      }

      if(!DateFunctions::isNaturalDateTodayOrLater($_POST['newPlanStart'])) {
        new errorPage("Das Datum ist ungültig! (Liegt in der Vergangenheit)");
      }

      $startDate = DateFunctions::getMySQLDateFromNaturalDate($_POST['newPlanStart']);


      if($currentPlanID > 0) {
        DB::getDB()->query("UPDATE stundenplan_plaene SET stundenplanBis = DATE_SUB('" . $startDate . "',INTERVAL 1 DAY) WHERE stundenplanID='" . $currentPlanID . "'");
      }

      // Neuen Plan erstellen
      DB::getDB()->query("INSERT INTO stundenplan_plaene (stundenplanAb,stundenplanUploadUserID,stundenplanName) values(
          '" . $startDate . "',
          '" . DB::getSession()->getUserID() . "',
          '" . addslashes($_POST['newPlanName']) . "'
          )");


      $newPlanID = DB::getDB()->insert_id();

      if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS" || DB::getGlobalSettings()->stundenplanSoftware == 'TIME2007') {

        $plan = file($_FILES['newStundenplanExportFile']['tmp_name']);

        $values = array();

        for($i = 0; $i < sizeof($plan); $i++) {
          list($id, $klasse, $lehrer, $fach, $raum, $tag, $stunde) = explode(",",str_replace("\"","",utf8_encode($plan[$i])));

          $values[] = "(
          '$newPlanID',
          '$klasse',
          '$lehrer',
          '$fach',
          '$raum',
          '$tag',
          '$stunde')";

        }


        if(sizeof($values) > 0) DB::getDB()->query("INSERT INTO stundenplan_stunden (
              stundenplanID,
              stundeKlasse,
              stundeLehrer,
              stundeFach,
              stundeRaum,
              stundeTag,
              stundeStunde
          ) values " . implode(",",$values));
      }
      
      
      if(DB::getGlobalSettings()->stundenplanSoftware == "WILLI" ) {
          
          $plan = file($_FILES['newStundenplanExportFile']['tmp_name']);
          
          $values = array();

          $unterrichtMitKopplungen = [];

          for($i = 0; $i < sizeof($plan); $i++) {
            // "U903","ARD","Sw","5A","t5a","2","2","2",,"19",,,,,"9",,"1","1","2","2","P",,,,,,,,"1"
            if(substr($plan[$i],0,2) == "\"U") {
              $data = explode(",",str_replace("\"","",str_replace("\r","",str_replace("\n","",utf8_encode($plan[$i])))));
              if($data[4] != "") {
                // Mit Kopplung
                $unterrichtMitKopplungen[$data[4]][] = $data;
              }
            }
          }

          //header("Content-type: text/plain");
          //print_r($unterrichtMitKopplungen);die();


          $unterricht = [];
          
          for($i = 0; $i < sizeof($plan); $i++) {

              if(substr($plan[$i],0,4) == "\"PL\"") {
                  list($type, $tagUndStunde, $lehrer, $klasse, $fach, $raum) = explode(",",str_replace("\"","",str_replace("\r","",str_replace("\n","",utf8_encode($plan[$i])))));
                  
                  $tagText = substr($tagUndStunde,0,2);
                  $tag = 0;
                  
                  switch($tagText) {
                      case 'Mo': $tag = 1; break;
                      case 'Di': $tag = 2; break;
                      case 'Mi': $tag = 3; break;
                      case 'Do': $tag = 4; break;
                      case 'Fr': $tag = 5; break;
                  }
                  
                  $stunde = substr($tagUndStunde, 3);

                  // Ist die Stunde Teil einer Kopplung?

                $kopplungenToAdd = [];
                $ignoreIndex = -1;
                $koppelLehrer = "";

                foreach($unterrichtMitKopplungen as $kopplungText => $kopplungen) {
                  for($k = 0; $k < sizeof($kopplungen); $k++) {
                    // // "U903","ARD","Sw","5A","t5a","2","2","2",,"19",,,,,"9",,"1","1","2","2","P",,,,,,,,"1"
                    if(
                        $kopplungen[$k][1] == $lehrer
                        && $kopplungen[$k][2] == $fach
                        && $kopplungen[$k][3] == $klasse
                    ) {
                      $kopplungenToAdd = $kopplungen;
                      $koppelLehrer = $lehrer;
                      $ignoreIndex = $k;              // Eigenen Unterricht nicht nochmal einfügen
                      break;
                    }
                  }
                }

                for($k = 0; $k < sizeof($kopplungenToAdd); $k++) {
                  if($k != $ignoreIndex && $kopplungenToAdd[$k][1] == $koppelLehrer) {
                    $key = $newPlanID.$kopplungenToAdd[$k][3].$kopplungenToAdd[$k][1].$kopplungenToAdd[$k][2].$raum.$tag.$stunde;
                    if(!in_array($key, $unterricht)) {
                      $values[] = "(
                      '$newPlanID',
                      '{$kopplungenToAdd[$k][3]}',
                      '{$kopplungenToAdd[$k][1]}',
                      '{$kopplungenToAdd[$k][2]}',
                      '$raum',
                      '$tag',
                      '$stunde')";
                      $unterricht[] = $key;
                    }
                  }
                }

                $key = $newPlanID.$klasse.$lehrer.$fach.$raum.$tag.$stunde;

                if(!in_array($key, $unterricht)) {
                  $values[] = "(
                  '$newPlanID',
                  '$klasse',
                  '$lehrer',
                  '$fach',
                  '$raum',
                  '$tag',
                  '$stunde')";
                  $unterricht[] = $key;
                }



              }

          }


          if(sizeof($values) > 0) DB::getDB()->query("INSERT INTO stundenplan_stunden (
              stundenplanID,
              stundeKlasse,
              stundeLehrer,
              stundeFach,
              stundeRaum,
              stundeTag,
              stundeStunde
          ) values " . implode(",",$values));
      }

      if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {

        $values = array();

        include_once('../framework/lib/phpexcel/PHPExcel.php');

        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true);
        

        try {
          $excelPlan = $objReader->load($_FILES['newStundenplanExportFile']['tmp_name']);
        }
        catch(Exception $e) {
          new errorPage("Der Stundenplan konnte nicht importiert werden, da die Datei entweder nicht korrekt hochgeladen wurde oder keine Excel Datei ist!");
        }

        $alpha = " A B C D E F G H I J K L M N O P Q R S T U V W X Y Z";
        $alpha = explode(" ",$alpha);

        $cols = array();
        for($i = 1; $i < sizeof($alpha); $i++) {
          $cols[$alpha[$i]] = $i;
        }

        $klassenPlaene = $excelPlan->getAllSheets();

        $tageStart = array();

        $values = array();

        for($i = 0; $i < sizeof($klassenPlaene); $i++) {
          $klasse = ($klassenPlaene[$i]->getTitle());
          $klasse = str_split($klasse);

          $klasseNeu = array();
          $nonNull = false;
          for($p = 0; $p < sizeof($klasse); $p++) {
            if($klasse[$p] == "0") {
              if($nonNull) $klasseNeu[] = $klasse[$p];
            }
            else {
              $nonNull = true;
              $klasseNeu[] = $klasse[$p];
            }
          }

          $klasse = implode("",$klasseNeu);
          
          // Breite der einzelnen Tage bestimmen.

          $it = $klassenPlaene[$i]->getRowIterator(2,2);
          $tage = $it->current();

          $cellIt = $tage->getCellIterator();

          $maxCell = 0;
          
          $fridaySeen = false;
          foreach($cellIt as $cell) {
            if($cell->getValue() != "" && $cell->getValue() != "SPM++") {
              $tag = $cell->getValue();
             
              
              if($tag == "Fr") {
                  if($fridaySeen) {
                      $maxCell++;
                      continue;
                  }
                  else $fridaySeen = true;
              }
              
              $tageStart[$tag] = $cell->getColumn();
            }
            $maxCell++;
          }
          

          // Breiten bestimmen

          $breite['Mo'] = $cols[$tageStart['Di']] - $cols[$tageStart['Mo']];
          $breite['Di'] = $cols[$tageStart['Mi']] - $cols[$tageStart['Di']];
          $breite['Mi'] = $cols[$tageStart['Do']] - $cols[$tageStart['Mi']];
          $breite['Do'] = $cols[$tageStart['Fr']] - $cols[$tageStart['Do']];
          $breite['Fr'] = $maxCell - $cols[$tageStart['Fr']] + 1;
          
          
          $tage = array("Mo","Di","Mi","Do","Fr");

          // Höhe der Stunden herausfinden

          $maxRow = $klassenPlaene[$i]->getHighestRow('A');

          $anfangZeilen = array("0");
          $hoechsteZelle = 0;
          for($r = 3; $r <= $maxRow; $r++) {
            if($klassenPlaene[$i]->getCell('A' . $r)->getValue() != "") {
              $anfangZeilen[] = $r;
            }
            $hoechsteZelle++;
          }
          $anfangZeilen[] = $hoechsteZelle+1;

          for($t = 0; $t < sizeof($tage); $t++) {
            $tagNummer = $t+1;
            $ersteSpalte = $cols[$tageStart[$tage[$t]]];
            $letzteSpalte = $ersteSpalte + $breite[$tage[$t]]-1;

            for($k = $ersteSpalte; $k <= $letzteSpalte; $k++) {
              // Für jede "Zeile":
              for($s = 1; $s <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $s++) {
                $zeilen = ($anfangZeilen[$s+1] - $anfangZeilen[$s]) / 3;
                for($b = $anfangZeilen[$s]; $b < $anfangZeilen[$s+1]; $b = $b+3) {
                  $spalte = $k-1;
                  $stunde = $s;
                  $tag = $tagNummer;

                  $zeileFach = $b;
                  $zeileLehrer = $b+1;
                  $zeileRaum = $b+2;

                  if(DB::getGlobalSettings()->stundenplanSoftwareVersion == "2") {
                    $zeileLehrer = $b;
                    $zeileFach = $b+1;
                    $zeileRaum = $b+2;
                  }

                  if(DB::getGlobalSettings()->stundenplanSoftwareVersion == "3") {
                    $zeileLehrer = $b;
                    $zeileFach = $b+1;
                    $zeileRaum = $b+2;
                  }
                  
                  if(DB::getGlobalSettings()->stundenplanSoftwareVersion == "4") {
                      $zeileLehrer = $b+1;
                      $zeileFach = $b+2;
                      $zeileRaum = $b+3;
                  }

                  $fach = ($klassenPlaene[$i]->getCellByColumnAndRow($spalte,$zeileFach));
                  $lehrer = ($klassenPlaene[$i]->getCellByColumnAndRow($spalte,$zeileLehrer));
                  $raum = ($klassenPlaene[$i]->getCellByColumnAndRow($spalte,$zeileRaum));

                  if($lehrer != "" || $fach != "" || $raum != "") {
                    $values[] = "(
                    '$newPlanID',
                    '$klasse',
                    '$lehrer',
                    '$fach',
                    '$raum',
                    '$tag',
                    '$stunde')";
                  }

                }

              }
            }

          }
        }
        if(sizeof($values) > 0) {
          DB::getDB()->query("INSERT INTO stundenplan_stunden (
                  stundenplanID,
                  stundeKlasse,
                  stundeLehrer,
                  stundeFach,
                  stundeRaum,
                  stundeTag,
                  stundeStunde
              ) values " . implode(",",$values));
        }
      }


      if(DB::getGlobalSettings()->stundenplanSoftware == "MDA_PLAN") {

        $values = array();

        $newPlanID = $newPlanID;		// ;-)

        $stunden = [];

        include_once('../framework/lib/phpexcel/PHPExcel.php');

        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true);
        try {
          $excelPlan = $objReader->load($_FILES['newStundenplanExportFile']['tmp_name']);

          $sheets = $excelPlan->getAllSheets();

          for($i = 0; $i < sizeof($sheets); $i++) {
            $klasse = $sheets[$i]->getTitle();

            $klasse = str_replace("Klasse ","",$klasse);


            // Montag bis Freitag
            for($t = 1; $t <= 5; $t++) {
              $col = ($t-1) * 3 + 1;
              // 1. bis 10. Stunde
              for($s = 1; $s <= 10; $s++) {
                $rowStunde = ($s-1)*3+2;

                for($d = 0; $d < 3; $d++) {
                  // 3 Kopplungen pro Stunde

                  $fach = $sheets[$i]->getCellByColumnAndRow($col+$d, $rowStunde)->getValue();

                  if($fach != '') {
                    $lehrer = $sheets[$i]->getCellByColumnAndRow($col+$d, $rowStunde+1)->getValue();
                    $raum = $sheets[$i]->getCellByColumnAndRow($col+$d, $rowStunde+2)->getValue();

                    $anrede = substr($lehrer,0,3);
                    if($anrede == 'Hr') $geschlecht = 'm';
                    else $geschlecht = 'w';

                    $lehrerObject = lehrer::getByNameAndGeschlecht(trim(substr($lehrer,4)),$geschlecht);
                    $lehrerObject2 = lehrer::getByNameAndGeschlecht("Dr. " . trim(substr($lehrer,4)),$geschlecht);

                    // print_r($lehrerObject);

                    if($lehrerObject != null) {
                      $kuerzel = $lehrerObject->getKuerzel();
                    }
                    else if($lehrerObject2 != null) {
                      $kuerzel = $lehrerObject2->getKuerzel();
                    }
                    else {
                      $kuerzel = $lehrer;
                    }

//   									echo("Tag: $t; Stunde: $s: $fach bei $lehrer in $raum für $klasse<br />");
                    DB::getDB()->query("INSERT INTO stundenplan_stunden (
                    stundenplanID,
                    stundeKlasse,
                    stundeLehrer,
                    stundeFach,
                    stundeRaum,
                    stundeTag,
                    stundeStunde
                  ) values(
                    '" . DB::getDB()->escapeString($newPlanID) . "',
                    '" . DB::getDB()->escapeString($klasse) . "',
                    '" . DB::getDB()->escapeString($kuerzel) . "',
                    '" . DB::getDB()->escapeString($fach) . "',
                    '" . DB::getDB()->escapeString($raum) . "',
                    '" . DB::getDB()->escapeString($t) . "',
                    '" . DB::getDB()->escapeString($s) . "'
                  )


                  ");
                  }

                }
              }
            }



          }



        }
        catch(Exception $e) {
          new errorPage("Der Stundenplan konnte nicht importiert werden, da die Datei entweder nicht korrekt hochgeladen wurde oder keine Excel Datei ist!");
        }

      }
      $message = "Der Plan wurde hochgeladen.";

    }

    if($_GET['deletePlan'] > 0) {
      DB::getDB()->query("UPDATE stundenplan_plaene SET stundenplanIsDeleted=1 WHERE stundenplanID='" . intval($_GET['deletePlan']) . "'");
    }


    if(stundenplanData::getCurrentStundenplanID() > 0) {
      $warningNoPlan = "";
    }
    else {
      $warningNoPlan = "<p><font color=\"red\" size=\"+3\">ACHTUNG: es ist kein aktueller Stundenplan hinterlegt!</font></p>";
    }

    $plaeneSQL = DB::getDB()->query("SELECT * FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 ORDER BY stundenplanAb ASC");

    $plaeneHTML = "";
    while($plan = DB::getDB()->fetch_array($plaeneSQL)) {
      if($plan['stundenplanID'] == stundenplandata::getCurrentStundenplanID()) {
        $preCurrentPlan = "<font color=\"green\"><b>";
        $postCurrentPlan = "</b></font>";
      }
      else {
        $preCurrentPlan = "";
        $postCurrentPlan = "";
      }
      eval("\$plaeneHTML .= \"" . DB::getTPL()->get("stundenplanadmin/plan") . "\";");
    }

    $currentPlan = stundenplandata::getCurrentStundenplan();

    if($currentPlanID > 0) {
      $abschluss = "Der aktuelle Plan \"" . $currentPlan->getName() . "\" wird automatisch einen Tag vorher abgeschlossen.";
    }
    else {
      $abschluss = "Da kein aktueller Plan vorhanden ist, wird auch kein vorheriger Plan abgeschlossen.";
    }


    $usergroup = usergroup::getGroupByName('Webportal_Stundenplananzeige');

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

    $currentUserBlock = administrationmodule::getUserListWithAddFunction($selfURL, "other", "addUser", "removeUser", "Benutzer mit Zugriff auf die Stundenpläne", "Diese Benutzer haben Zugriff auf die kompletten Stundenpläne. (Vollzugriff)", 'Webportal_Stundenplananzeige');

    eval("\$html = \"" . DB::getTPL()->get("stundenplanadmin/index") . "\";");

    return $html;
  }

  public static function getActionSchuljahreswechsel() {
    return 'Alle Stundenpläne löschen und einen leeren Stundenplan erstellen.';
  }

  public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
    DB::getDB()->query("DELETE FROM stundenplan_plaene");
    DB::getDB()->query("DELETE FROM stundenplan_stunden");
    DB::getDB()->query("INSERT INTO stundenplan_plaene (stundenplanAb, stundenplanName) values(CURDATE(), 'Initialer leerer Stundenplan')");
  }

  public static function userHasAccess($user) {
      return DB::getSession()->isAdmin() || DB::getSession()->isPupil() || DB::getSession()->isEltern() || DB::getSession()->isTeacher() || DB::getSession()->isMember('Webportal_Stundenplananzeige');
  }


}


?>
