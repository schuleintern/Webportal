<?php

class klassentagebuchauswertung extends AbstractPage {
  private $isTeacher = false;
  private $isPupil = false;
  private $isEltern = false;
  private $isAdmin = false;
  private $lehrerTagebuch = false;
  private $tagebuchActive = false;
  private $isOther = false;


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
    parent::__construct(['Klassentagebuch', 'Klassentagebuchauswertung']);

    $this->checkLogin();

    $this->isTeacher = DB::getSession()->isTeacher();
    $this->isAdmin = DB::getSession()->isMember("Webportal_Klassentagebuch_Admin");
    $this->isOther = false;


    if(!$this->isAdmin) $this->isAdmin = DB::getSession()->isAdmin();


    $this->tagebuchActive = !DB::getSettings()->getBoolean("klassentagebuch-klassentagebuch-abschalten");

    if(!$this->tagebuchActive) new errorPage("Keine Auswertungstool verfügbar, da das Klassentagebuch abgeschaltet ist.");     // Keine Auswertungstools vorhanden


    $this->currentStundenplan = stundenplandata::getCurrentStundenplan();

    if($this->currentStundenplan == null) new errorPage("Nicht verfügbar, da kein aktueller Stundenplan vorhanden.");


    // Klassen zusammenstellen

    if($this->isTeacher) {
      if(schulinfo::isSchulleitung(DB::getSession()->getUser())) $this->myGrades = $this->currentStundenplan->getAll("grade");
      else {
          $me = DB::getSession()->getTeacherObject();

          $this->myGrades = [];

          $kls = klasse::getAllKlassen();
          for($i = 0; $i < sizeof($kls); $i++) {
            if($kls[$i]->isKlassenLeitung($me)) $this->myGrades[] = $kls[$i];
          }
      }
    }
    else {
        new errorPage("Keine Auswertungstool verfügbar, da der angemeldete Benutzer kein Lehrer ist.");
    }
  }


  public function execute() {
    switch($_REQUEST['mode']) {
      case 'pdfexport':
          if(DB::getSession()->isMember(klassentagebuch::getAdminGroup()) || schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->isAdmin()) {
              $this->pdfExport();
          }
      break;


      case 'schulleitung':
          if(DB::getSession()->isMember(klassentagebuch::getAdminGroup()) || schulinfo::isSchulleitung(DB::getSession()->getUser()) || DB::getSession()->isAdmin()) {
            $this->schulleitung();
          }
      break;

      case 'klassenleitung':
          $this->klassenleitung();
      break;

      default:
        $this->fachlehrer();
      break;
    }
  }

  private function pdfExportold() {

      // Bisher erzeugte PDFs


      $klassen = stundenplandata::getCurrentStundenplan()->getAll("grade");


      $startDate = "2018-09-01";
      $endDate = "2018-09-30";


      for($i = 0; $i < sizeof($klassen); $i++) {
          $this->allEntries[$klassen[$i]] = TagebuchKlasseEntry::getAllForGrade($klassen[$i]);
      }


      $plaene = [];

      $plaeneSQL = DB::getDB()->query("SELECT * FROM stundenplan_plaene WHERE stundenplanIsDeleted=0 AND stundenplanAB >= '" . $startDate . "'");

      while($planItem = DB::getDB()->fetch_array($plaeneSQL)) {
          $plan = [
              'start' => $planItem['stundenplanAb'],
              'ende' => $planItem['stundenplanBis'],
              'id' => $planItem['stundenplanID']
          ];




          for($k = 0; $k < sizeof($klassen); $k++) {
              $stunden = [];

              for($tag = 1; $tag <= 5; $tag++) {
                  for($stunde = 1; $stunde <= stundenplandata::getMaxStunden(); $stunde++) {
                      $stunden[$tag][$stunde] = [];
                  }
              }

              $plan[$klassen[$k]] = $stunden;
          }

          $plaene[$planItem['stundenplanID']] = $plan;
      }



      foreach($plaene as $id => $plan) {
          $stundenSQL = DB::getDB()->query("SELECT * FROM stundenplan_stunden WHERE stundenplanID='" . $id . "'");

          while($stunde = DB::getDB()->fetch_array($stundenSQL)) {
              $plaene[$stunde['stundenplanID']][$stunde['stundeKlasse']][$stunde['stundeTag']][$stunde['stundeStunde']][] =  [
                  'fach' => $stunde['stundeFach'],
                  'lehrer' => $stunde['stundeLehrer'],
                  'raum' => $stunde['stundeRaum'],
                  'klasse' => $stunde['stundeKlasse']
              ];
          }


      }

      $this->stundenplaene = $plaene;

      $gesamtHTML = "";


      for($i = 0; $i < sizeof($klassen); $i++) {
          $currentDate = $startDate;


          while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDate, $endDate)) {

              if(!Ferien::isFerien($currentDate) && !DateFunctions::isSQLDateWeekEnd($currentDate)) {

                  $planAmTag = $this->getStundenplanAtDate($klassen[$i], $currentDate);
                  $entries = $this->getEntriesOfDayAndGrade($currentDate,$klassen[$i]);

                  $klasseHTML = "";


                  $klasseHTML .= "<div style=\"page-break-after: always;\"><h3>Klassentagebuch der Klasse " . $klassen[$i] . " vom " . DateFunctions::getWeekDayNameFromNaturalDate($currentDate) . ", " . DateFunctions::getNaturalDateFromMySQLDate($currentDate) . "</h3>";

                  $klasseHTML .= "<table border=\"1\" width=\"100%\" cellpadding=\"2\" cellspacing=\"0\"><tr><th width=\"10%\">Stunde</th><th width=\"30%\">Stundenplan</th><th width=\"60%\">Einträge</th></tr>";

                  for($stunde = 1; $stunde <= stundenplandata::getMaxStunden(); $stunde++) {
                      $klasseHTML .= "<tr><td>" . $stunde . ".</td><td>";

                      for($u = 0; $u < sizeof($planAmTag[$stunde]); $u++) {
                          $klasseHTML .= $planAmTag[$stunde][$u]['fach'] . " bei " . $planAmTag[$stunde][$u]['lehrer'] . " in " . $planAmTag[$stunde][$u]['raum'] . "<br />";
                      }

                      $klasseHTML .= "</td><td>";

                      for($e = 0; $e < sizeof($entries); $e++) {
                          if($entries[$e]->getStunde() == $stunde) {
                              if($entries[$e]->isAusfall()) {
                                  $klasseHTML .= "<i>Entfall</i> (Lehrkraft: " . $entries[$e]->getTeacher() . ")<br />";
                              }
                              else {
                                  $klasseHTML .= "<b>" . $entries[$e]->getFach() . " bei " . $entries[$e]->getTeacher();
                                  if($entries[$e]->isVertretung()) $klasseHTML .= " <i>Vertretung</i></b><br />";
                                  else $klasseHTML .= "</b><br />";
                                  $klasseHTML .= "S: " . $entries[$e]->getStoff() . "<br />";
                                  $klasseHTML .= "HA: " . $entries[$e]->getHausaufgabe() . "<br />";

                              }
                          }
                      }

                      $klasseHTML .= "</td></tr>";

                  }

                  $klasseHTML .= "</table></div>";

                  $gesamtHTML .= $klasseHTML;

              }

              $currentDate = DateFunctions::addOneDayToMySqlDate($currentDate);
          }


      }

      $print = new PrintNormalPageA4WithHeader("Klassentagebuch");
      $print->setPrintedDateInFooter();
      $print->showHeaderOnEachPage();
      $print->setHTMLContent($gesamtHTML);
      $print->send();


  }

  private function getStundenplanAtDate($klasse, $date) {

      $currentPlan = [];

      foreach ($this->stundenplaene as $id => $plan) {
          if(DateFunctions::isSQLDateAtOrAfterAnother($date, $plan['start'])) {
              if($plan['ende'] == "" || DateFunctions::isSQLDateAtOrBeforeAnother($date, $plan['ende'])) {
                  $currentPlan = $plan[$klasse];
              }
          }
      }

      $weekDay = DateFunctions::getWeekDayFromSQLDateISO($date);


      return $currentPlan[$weekDay];

  }


  /**
   *
   * @param unknown $day
   * @param unknown $grade
   * @return TagebuchKlasseEntry[]
   */
  private function getEntriesOfDayAndGrade($day, $grade) {
      $entries = [];

      for($i = 0; $i < sizeof($this->allEntries[$grade]); $i++) {
          if($this->allEntries[$grade][$i]->getDate() == $day) {
              $entries[] = $this->allEntries[$grade][$i];
          }
      }

      return $entries;
  }

  private function pdfExport() {
      
      if($_REQUEST['action'] == 'removeAll') {
          DB::getDB()->query("TRUNCATE TABLE klassentagebuch_pdf");
          header("Location: index.php?page=klassentagebuchauswertung&mode=pdfexport");
          exit(0);
      }

      if($_REQUEST['action'] == 'antrag') {
          $currentStundenplan = stundenplandata::getCurrentStundenplan();
          
          if($currentStundenplan == null) {
              header("Location: index.php?page=klassentagebuchauswertung&mode=pdfexport");
              exit(0);
          }
          
          $possibleGrades = $currentStundenplan->getAll("grade");
          
          $requestGrades = $_REQUEST['grades'];
          $requestMonths = $_REQUEST['months'];
          
          $grades = [];
          $months = [];
          $year = $_REQUEST['year'];
          
          if($year != date("Y") && $year != (date("Y")-1) && $year != (date("Y")+1)) {
              new errorPage();
          }
          
          
          for($i = 0; $i < sizeof($possibleGrades); $i++) {
              if(in_array($possibleGrades[$i],$requestGrades)) {
                  $grades[] = $possibleGrades[$i];
              }
          }
          
          for($m = 1; $m <= 12; $m++) {
              if(in_array($m, $requestMonths)) {
                  $months[] = $m;
              }
          }
          
          for($i = 0; $i < sizeof($grades); $i++) {
              for($m = 0; $m < sizeof($months); $m++) {
                  DB::getDB()->query("INSERT INTO klassentagebuch_pdf (pdfKlasse, pdfJahr, pdfMonat) values(
                        '" . DB::getDB()->escapeString($grades[$i]) . "',
                        '" . DB::getDB()->escapeString($year) . "',
                        '" . DB::getDB()->escapeString($months[$m]) . "'
                    ) ON DUPLICATE KEY UPDATE pdfUploadID=0");
              }
          }
          
          header("Location: index.php?page=klassentagebuchauswertung&mode=pdfexport");
          exit(0);
      }


      if($_REQUEST['action'] == 'alleInZip') {
          $daten = DB::getDB()->query("SELECT * FROM klassentagebuch_pdf ORDER BY pdfKlasse");

          $zip = new ZipArchive();
          $filename = "temp/pdf_Export_temp_" . md5(rand()) . ".zip";

          if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
              die("cannot open --> $filename\n");
          }

          while($p = DB::getDB()->fetch_array($daten)) {

              $upload = FileUpload::getByID($p['pdfUploadID']);

              if($upload != null) $zip->addFile($upload->getFilePath(), $upload->getFileName());

          }

          $zip->close();

          // Send File

          $file = $filename;

          header('Content-Description: File Transfer');
          header('Content-Type: application/zip');
          header('Content-Disposition: attachment; filename='.basename("Klassentagebuch PDF Export.zip"));
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



      $daten = DB::getDB()->query("SELECT * FROM klassentagebuch_pdf ORDER BY pdfKlasse");

      $html = "";

      while($p = DB::getDB()->fetch_array($daten)) {
          $html .= "<tr>";

          $html .= "<td>" . $p['pdfKlasse'] . "</td>";
          $html .= "<td>" . $p['pdfJahr'] . "</td>";
          $html .= "<td>" . $p['pdfMonat'] . "</td>";

          $upload = FileUpload::getByID($p['pdfUploadID']);
          if($upload != null) {
              $html .= "<td><a href=\"" . $upload->getURLToFile() . "\"><i class=\"fa fa-file-pdf-o\"></i> Download</a></td>";
              
          }
          else {
              $html .= "<td><i>Nicht verfügbar</i></td>";

          }




          $html .= "</tr>";
      }

      
      $optionsGrades = "";
      
      $currentStundenplan = stundenplandata::getCurrentStundenplan();
      
      if($currentStundenplan == null) {
          $possibleGrades = [];
      } else $possibleGrades = $currentStundenplan->getAll("grade");
      
      for($i = 0; $i < sizeof($possibleGrades); $i++) {
          $optionsGrades .= "<option value=\"" . $possibleGrades[$i] . "\" selected>" . $possibleGrades[$i] . "</option>";
      }
      
      
      $optionsMonths = "";
      
      for($m = 1; $m <= 12; $m++) {
          $optionsMonths .= "<option value=\"" . $m . "\" selected>" . $m . "</option>";
          
      }
      
      $optionsYear = "";
      
      for($m = date("Y")-1; $m <= date("Y")+1; $m++) {
          $optionsYear .= "<option value=\"" . $m . "\"" . ((date("Y") == $m) ? ("selected") : ("")) . ">" . $m . "</option>";
          
      }
      

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/auswertung/pdf/index") . "\");");
  }

  private function getHTMLForPage($unterricht, $entries) {

  }

  private function klassenleitung() {

      $klassenMitKlassenleitung = DB::getSession()->getTeacherObject()->getKlassenMitKlasseleitung();

      $klassen = [];

      for($i = 0; $i < sizeof($klassenMitKlassenleitung); $i++) {
          $klassen[] = $klassenMitKlassenleitung[$i]->getKlassenName();
      }

      if(sizeof($klassen) == 0) {
          new errorPage("Keine Klassenleitung");
      }

      $klassenNamen = implode(", ", $klassen);

      $missing = DB::getDB()->query("SELECT * FROM klassentagebuch_fehl WHERE fehlKlasse IN ('" . implode("','",$klassen) . "') ORDER BY fehlDatum DESC");

      $fehlHTML = "";

      while($f = DB::getDB()->fetch_array($missing)) {
          $fehlHTML .= "<tr>";

          $fehlHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($f['fehlDatum']) . "</td>";

          $fehlHTML .= "<td>" . $f['fehlKlasse'] . "</td>";

          $fehlHTML .= "<td>" . $f['fehlLehrer'] . "</td>";

          $fehlHTML .= "<td>" . $f['fehlStunde'] . "</td>";
          $fehlHTML .= "<td>" . $f['fehlFach'] . "</td>";

          $fehlHTML .= "</tr>";
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/auswertung/klassenleitung") . "\");");
  }

  private function schulleitung() {

      $missing = DB::getDB()->query("SELECT * FROM klassentagebuch_fehl ORDER BY fehlDatum DESC");

      $fehlHTML = "";

      while($f = DB::getDB()->fetch_array($missing)) {
          $fehlHTML .= "<tr>";

          $fehlHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($f['fehlDatum']) . "</td>";

          $fehlHTML .= "<td>" . $f['fehlKlasse'] . "</td>";

          $fehlHTML .= "<td>" . $f['fehlLehrer'] . "</td>";

          $fehlHTML .= "<td>" . $f['fehlStunde'] . "</td>";
          $fehlHTML .= "<td>" . $f['fehlFach'] . "</td>";

          $fehlHTML .= "</tr>";
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/auswertung/schulleitung") . "\");");
  }



  private function fachlehrer() {
      $missing = DB::getDB()->query("SELECT * FROM klassentagebuch_fehl WHERE fehlLehrer='" . DB::getDB()->escapeString(DB::getSession()->getTeacherObject()->getKuerzel()) . "' ORDER BY fehlDatum DESC");

      $fehlHTML = "";

      while($f = DB::getDB()->fetch_array($missing)) {
          $fehlHTML .= "<tr>";

          $fehlHTML .= "<td>" . DateFunctions::getNaturalDateFromMySQLDate($f['fehlDatum']) . "</td>";

          $fehlHTML .= "<td>" . $f['fehlKlasse'] . "</td>";
          $fehlHTML .= "<td>" . $f['fehlStunde'] . "</td>";
          $fehlHTML .= "<td>" . $f['fehlFach'] . "</td>";
          $fehlHTML .= "<td><a href=\"index.php?page=klassentagebuch&goBackURL=" . urlencode("index.php?page=klassentagebuchauswertung") . "&mode=showGrade&currentDate=" . DateFunctions::getNaturalDateFromMySQLDate($f['fehlDatum']) . "&triggerAdd=1&stunde=" . $f['fehlStunde'] . "&fach=" . urlencode($f['fehlFach']) . "&grade=" . urlencode($f['fehlKlasse']) . "\"><i class=\"fa fa-pencil\"></i> Eintrag hinzufügen</a></td>";

          $fehlHTML .= "</tr>";
      }

      eval("DB::getTPL()->out(\"" . DB::getTPL()->get("klassentagebuch/auswertung/lehrer") . "\");");
  }


  public static function hasSettings() {
    return false;
  }


  public static function getSettingsDescription() {
    return [];
  }


  public static function getSiteDisplayName() {
    return 'Klassentagebuchauswertung';
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

  public static function dependsPage() {
      return [
          'klassentagebuch'
      ];
  }

  public static function displayAdministration($selfURL) {


      if($_REQUEST['mode'] == 'resetStartDate') {
          $newDate = DateFunctions::getMySQLDateFromNaturalDate($_REQUEST['newStartDate']);


          if(DateFunctions::isSQLDate($newDate)) {
              DB::getDB()->query("TRUNCATE klassentagebuch_fehl");
              DB::getSettings()->setValue('cron-tagebuch-fehl-sucher-last-day', $newDate);

              $saved = 1;
          }
      }

      $html = "";



      $datumErmittelt = DB::getSettings()->getValue('cron-tagebuch-fehl-sucher-last-day');
      $datumErmittelt = DateFunctions::getNaturalDateFromMySQLDate($datumErmittelt);


      $summe = DB::getDB()->query_first("SELECT COUNT(*) FROM klassentagebuch_fehl")[0];

      $today = DateFunctions::getTodayAsNaturalDate();


      eval("\$html = \"" . DB::getTPL()->get("klassentagebuch/auswertung/admin/index") . "\";");
      return $html;
  }



}

