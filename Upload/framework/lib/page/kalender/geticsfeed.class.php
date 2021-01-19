<?php

class geticsfeed extends AbstractPage {
    
  private $startDate = "";
  private $endDate = "";

  public function __construct() {

  }

  public function execute() {
      $feed = ICSFeed::getByIDAndKeys($_REQUEST['a'],$_REQUEST['b'],$_REQUEST['c']);
      
      if($feed == null) {
          http_response_code(203);
          echo("no access");
          exit(0);
      }

      $timeNow = time();
      
      $timeStart = $timeNow - (100 * 24 * 60 * 60); // 100 Tage vorher
      $timeEnd = $timeNow + (365 * 24 * 60 * 60);
      
      $this->startDate = date("Y-m-d",$timeStart);
      $this->endDate = date("Y-m-d",$timeEnd);
      

      if($feed->isKlassenkalender()) {
          $this->sendKlassenkalender($feed);
          exit(0);
      }
      
      if($feed->isAndererKalender()) {
          $this->sendAndererKalender($feed);
          exit();
      }

      if($feed->isExternerKalender()) {
          $this->sendExternerKalender($feed);
      }
      
  }
   
  
  /**
   * 
   * @param ICSFeed $feed
   */
  private function sendAndererKalender($feed) {
      $vCalendar = new \Eluceo\iCal\Component\Calendar(DB::getGlobalSettings()->siteNamePlain);
      $vCalendar->setPublishedTTL('P1H');

      // $vCalendar->setName($name);
  
      $data = json_decode($feed->getFeedData());
            
      // 100 Tage vorher abfragen
      
      $time = DateFunctions::getUnixTimeFromMySQLDate(DateFunctions::getTodayAsSQLDate());
      $time1 = $time - (100 * 24 * 3600);
      
      $time2 = $time + (365 * 24 * 3600);
        
      $kalenderTermine = AndererKalenderTermin::getAll($data, DateFunctions::getMySQLDateFromUnixTimeStamp($time1), DateFunctions::getMySQLDateFromUnixTimeStamp($time2));
      
      
      for($i = 0; $i < sizeof($kalenderTermine); $i++) {
          
          $timeStart = new DateTime($kalenderTermine[$i]->getDatumStart());
          $timeEnd = new DateTime($kalenderTermine[$i]->getDatumEnde());
          
          if($kalenderTermine[$i]->getDatumEnde() != $kalenderTermine[$i]->getDatumStart()) {
              $timeEnd = new DateTime(DateFunctions::addOneDayToMySqlDate($kalenderTermine[$i]->getDatumEnde()));
          }
          
          if(!$kalenderTermine[$i]->isWholeDay()) {
              $uhrZeitStart = $kalenderTermine[$i]->getUhrzeitStart();
              $uhrZeitEnde = $kalenderTermine[$i]->getUhrzeitEnde();
              
              list($stundeStart, $minuteStart) = explode(":",$uhrZeitStart);
              list($stundeEnde, $minuteEnde) = explode(":",$uhrZeitEnde);
              
              
              $timeStart->setTime($stundeStart, $minuteStart, 0);
              
              $timeEnd->setTime($stundeEnde, $minuteEnde, 0);
              
          }
          
          $vCalendar->addComponent(
              ICSFeed::getICSFeedObject(
                  "",
                  $kalenderTermine[$i]->getTitleRaw(),
                  $timeStart,
                  $timeEnd,
                  $kalenderTermine[$i]->getOrt(),
                  $kalenderTermine[$i]->getKommentar() . " (Eingetragen von " . $kalenderTermine[$i]->getCreatorName() . ")",
                  $kalenderTermine[$i]->isWholeDay())
             );
      }

      ICSFeed::sendICSFeed($vCalendar);
  
  }

    /**
     *
     * @param ICSFeed $feed
     */
    private function sendExternerKalender($feed) {
        $vCalendar = new \Eluceo\iCal\Component\Calendar(DB::getGlobalSettings()->siteNamePlain);
        $vCalendar->setPublishedTTL('P1H');

        // $vCalendar->setName($name);

        $data = json_decode($feed->getFeedData());

        // 100 Tage vorher abfragen

        $time = DateFunctions::getUnixTimeFromMySQLDate(DateFunctions::getTodayAsSQLDate());
        $time1 = $time - (100 * 24 * 3600);

        $time2 = $time + (365 * 24 * 3600);

        $kalenderTermine = ExtKalenderTermin::getAll($data, DateFunctions::getMySQLDateFromUnixTimeStamp($time1), DateFunctions::getMySQLDateFromUnixTimeStamp($time2));


        for($i = 0; $i < sizeof($kalenderTermine); $i++) {

            $timeStart = new DateTime($kalenderTermine[$i]->getDatumStart());
            $timeEnd = new DateTime($kalenderTermine[$i]->getDatumEnde());

            if($kalenderTermine[$i]->getDatumEnde() != $kalenderTermine[$i]->getDatumStart()) {
                $timeEnd = new DateTime(DateFunctions::addOneDayToMySqlDate($kalenderTermine[$i]->getDatumEnde()));
            }

            if(!$kalenderTermine[$i]->isWholeDay()) {
                $uhrZeitStart = $kalenderTermine[$i]->getUhrzeitStart();
                $uhrZeitEnde = $kalenderTermine[$i]->getUhrzeitEnde();

                list($stundeStart, $minuteStart) = explode(":",$uhrZeitStart);
                list($stundeEnde, $minuteEnde) = explode(":",$uhrZeitEnde);


                $timeStart->setTime($stundeStart, $minuteStart, 0);

                $timeEnd->setTime($stundeEnde, $minuteEnde, 0);

            }

            $vCalendar->addComponent(
                ICSFeed::getICSFeedObject(
                    "",
                    $kalenderTermine[$i]->getTitleRaw(),
                    $timeStart,
                    $timeEnd,
                    $kalenderTermine[$i]->getOrt(),
                    $kalenderTermine[$i]->getKommentar() . " (Eingetragen von " . $kalenderTermine[$i]->getCreatorName() . ")",
                    $kalenderTermine[$i]->isWholeDay())
            );
        }

        ICSFeed::sendICSFeed($vCalendar);

    }
  
  /**
   * 
   * @param ICSFeed $feed
   */
  private function sendKlassenkalender($feed) {
      $data = json_decode($feed->getFeedData());
      
      $class = $data->klasse;
      $withEx = $data->withEx > 0;
      
      $showGrade = false;
      $name = "";
           
            
      if($class == "all_grades") {
          $lnwData = Leistungsnachweis::getByClass([], $this->startDate,$this->endDate);
          $termine = Klassentermin::getByClass([], $this->startDate,$this->endDate);
          $showGrade = true;
          $name = "Klassentermine aller Klassen";
      }
      else if($class == "allMyGrades") {
          
          $currentStundenplan = stundenplandata::getCurrentStundenplan();
          
          $lehrer = user::getUserByID($feed->getUserID());
          
          if($lehrer->isTeacher()) {
              $kuerzel = $lehrer->getTeacherObject()->getKuerzel();
          }
          else {
              http_response_code(500);
              echo("internal error. ICS Feed invalid.");
              exit(0);
          }

          $grades = klasse::getByUnterrichtForTeacher($lehrer->getTeacherObject());

          $myGrades = [];

          for($i = 0; $i < sizeof($grades); $i++) $myGrades[] = $grades[$i]->getKlassenName();
          
          // $myGrades = $currentStundenplan->getAllGradesForTeacher($kuerzel);
          
          $lnwData = Leistungsnachweis::getByClass($myGrades,$this->startDate,$this->endDate);
          $termine = Klassentermin::getByClass($myGrades,$this->startDate,$this->endDate);
          
          $showGrade = true;
          
          $name = "Klassentermine aller Klasse von " . $kuerzel;
      }
      else if($class == "allMyTermine") {
          
          $currentStundenplan = stundenplandata::getCurrentStundenplan();
          
          $lehrer = user::getUserByID($feed->getUserID());
          
          if($lehrer->isTeacher()) {
              $kuerzel = $lehrer->getTeacherObject()->getKuerzel();
          }
          else {
              http_response_code(500);
              echo("internal error. ICS Feed invalid.");
              exit(0);
          }
          
          
          $lnwData = Leistungsnachweis::getBayTeacher($kuerzel,$this->startDate,$this->endDate);
          $termine = Klassentermin::getBayTeacher($kuerzel,$this->startDate,$this->endDate);
          
          
          $showGrade = true;
          
          $name = "Klassentermine, die " . $kuerzel . " eingetragen hat.";
      }
      elseif(substr($class,0,3) == "all") {
          $showGrade = true;
          
          $class = substr($class,3);
          
          $classes = $currentStundenplan->getAllMyPossibleGrades($class);
          
          $lnwData = Leistungsnachweis::getByClass($classes, $this->startDate,$this->endDate);
          $termine = Klassentermin::getByClass($classes, $this->startDate,$this->endDate);
          
          $name = "Klassentermine der Klassen " . implode(",", $classes);
          
      }
      else {
          
          $showGrade = true;
          
          $lnwData = Leistungsnachweis::getByClass([$class],$this->startDate,$this->endDate);
          $termine = Klassentermin::getByClass([$class],$this->startDate,$this->endDate);
          
          $name = "Klassentermine der Klasse " . $class;
          
      }
      
      
      if($withEx) $name .= " (Mit unangekündigten Leisungsnachweisen.)";
      
      $feedText = "";
      
      $vCalendar = new \Eluceo\iCal\Component\Calendar(DB::getGlobalSettings()->siteNamePlain);
      $vCalendar->setPublishedTTL('P1H');
      $vCalendar->setName($name);
      
      
      for($i = 0; $i < sizeof($lnwData); $i++) {
          if($lnwData[$i]->showForNotTeacher() || $withEx) {
              $vCalendar->addComponent(ICSFeed::getICSFeedObject(
                  "LNW" . $lnwData[$i]->getID(),
                  (($showGrade) ? ($lnwData[$i]->getKlasse() . ": ") : "") . $lnwData[$i]->getArtLangtext() . " in " . $lnwData[$i]->getFach() . " bei " . $lnwData[$i]->getLehrer(),
                  new DateTime($lnwData[$i]->getDatumStart()),
                  new DateTime($lnwData[$i]->getDatumStart()), 
                  $lnwData[$i]->getBetrifft(),
                  "", true));
          }
      }
      
      
      for($i = 0; $i < sizeof($termine); $i++) {
          $vCalendar->addComponent(ICSFeed::getICSFeedObject(
              "KT" . $termine[$i]->getID(),
              $termine[$i]->getTitle(),
              new DateTime($termine[$i]->getDatumStart()),
              new DateTime(($termine[$i]->getDatumStart() != $termine[$i]->getDatumEnde()) ? $termine[$i]->getDatumEnde() : $termine[$i]->getDatumStart()),
              $termine[$i]->getOrt(),
              implode(", ", $termine[$i]->getKlassen()) . "\r\n" . $termine[$i]->getBetrifft(),
              (($termine[$i]->getDatumStart() != $termine[$i]->getDatumEnde()) ? true : false)));
      }
      
      
      
      ICSFeed::sendICSFeed($vCalendar);
      exit(0);
      
      
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
    return [];
  }


  public static function getSiteDisplayName() {
    return 'ICS Feeds';
  }
  
  public static function siteIsAlwaysActive() {
   return true;   
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
      return [];
  }

  public static function getAdminGroup() {
    return 'Webportal_ICS_FEED_Admin';
  }

  public static function hasAdmin() {
    return false;
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
    return 'ICS Feeds der Klassenkalender, die von Eltern oder Schülern sind löschen';
  }

  public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {

  }
}


?>
