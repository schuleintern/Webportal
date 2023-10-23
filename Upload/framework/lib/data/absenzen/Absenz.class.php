<?php

class Absenz {
  private $absenzID = 0;

  private $type = "";

  private $date = "";

  private $schueler = null;

  private $beurlaubung = false;
  private $befreiung = false;

  private $bemerkung = "";

  private $absendHours = array();

  private $data;

  public function __construct($data) {
    $this->data = $data;
    $this->absenzID = $data['absenzID'];
    $this->bemerkung = $data['absenzBemerkung'];
    $this->schueler = new schueler($data); // SchuelerData auch im Data Array
  }

  public function getStundenAsString() {
    return implode(", ",explode(",",$this->data['absenzStunden']));
  }

  public function getStundenAsArray() {
    return explode(",",$this->data['absenzStunden']);
  }

  public function getSchueler() {
    return $this->schueler;
  }

  public function isMehrtaegig() {
    return $this->data['absenzDatum'] != $this->data['absenzDatumEnde'];
  }

  public function getEnddatumAsSQLDate() {
    return $this->data['absenzDatumEnde'];
  }

  public function getDateAsSQLDate() {
    return $this->data['absenzDatum'];
  }

  public function isEntschuldigt() {
    return $this->data['absenzisEntschuldigt'] > 0;
  }

  public function isSchriftlichEntschuldigt() {
    if($this->needSchriftlichEntschuldigung()) return $this->data['absenzIsSchriftlichEntschuldigt'] > 0;
    else return true;
  }


  public function getGanztagsNotiz() {
    return $this->data['absenzGanztagsNotiz'];
  }

  public function getKommentar() {
    return $this->data['absenzBemerkung'];
  }
  public function getBemerkung() {
    return $this->data['absenzBemerkung'];
  }

  public function getID() {
    return $this->data['absenzID'];
  }

  public function setEntschuldigt() {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzisEntschuldigt=1 WHERE absenzID='" . $this->getID() . "'");
  }

  public function setSchriftlichEntschuldigt() {
    $this->data['absenzIsSchriftlichEntschuldigt'] = 1;
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzIsSchriftlichEntschuldigt=1 WHERE absenzID='" . $this->getID() . "'");
  }


  public function setSchriftlichUnEntschuldigt() {
    $this->data['absenzIsSchriftlichEntschuldigt'] = 0;
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzIsSchriftlichEntschuldigt=0 WHERE absenzID='" . $this->getID() . "'");
  }

  public function setUnentschuldigt() {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzisEntschuldigt=0 WHERE absenzID='" . $this->getID() . "'");
  }

  public function addKommentar($kommentar) {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzBemerkung=CONCAT(absenzBemerkung, '\r\n--------------------\r\n" . DB::getDB()->escapeString($kommentar) . "') WHERE absenzID='" . $this->getID() . "'");
  }

  
  public function addGanztagsNotiz($notiz) {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzGanztagsNotiz='".$notiz."' WHERE absenzID='" . $this->getID() . "'");
  }

  public function getKanal() {
    return $this->data['absenzQuelle'];
  }

  public function setStunden($stunden) {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzStunden='" . implode(",",$stunden) . "' WHERE absenzID='" . $this->getID() . "'");
  }

  public function setKanal($kanal) {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzQuelle='" . $kanal . "' WHERE absenzID='" . $this->getID() . "'");
  }

  public function setStartdate($date) {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzDatum='" . $date . "' WHERE absenzID='" . $this->getID() . "'");
  }

  public function setEndeDate($date) {
    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzDatumEnde='" . $date . "' WHERE absenzID='" . $this->getID() . "'");
  }

  public function isBefreiung() {
    return $this->data['absenzBefreiungID'] > 0;
  }

  public function getBefreiung() {
    $data = DB::getDB()->query_first("SELECT * FROM absenzen_befreiungen WHERE befreiungID='" . $this->data['absenzBefreiungID'] . "'");
    return new AbsenzBefreiung($data);
  }

  public function isBeurlaubung() {
    return $this->data['absenzBeurlaubungID'] > 0;
  }

  public function kommtSpaeter() {
    return $this->data['absenzKommtSpaeter'] > 0;
  }

  public function getUserID() {
    return $this->data['absenzErfasstUserID'];
  }
  public function getSchuelerAsvID() {
    return $this->data['absenzSchuelerAsvID'];
  }

  

  public function getBeurlaubung() {
    $data = DB::getDB()->query_first("SELECT * FROM absenzen_beurlaubungen WHERE beurlaubungID='" . $this->data['absenzBeurlaubungID'] . "'");
    return new AbsenzBeurlaubung($data);
  }


  public function getCollection($full = false) {

        $collection = [
            "id" => $this->getID(),
            "asvID" => $this->getSchuelerAsvID(),
            "datum_start" => $this->getDateAsSQLDate(),
            "datum_end" => $this->getEnddatumAsSQLDate(),
            "quelle" => $this->getKanal(),
            "bemerkung" => $this->getBemerkung(),
            "stunden" => $this->getStundenAsString()
        ];

        if ($full) {

        }

        return $collection;
    }



  /**
   * Überprüft, ob generell eine schriftliche Entschuldigung vorgelegt werden muss.
   * 
   * @return boolean
   */
  public function needSchriftlichEntschuldigung() {
      
      
      if($this->isBeurlaubung()) return false;  // Bei Beurlaubungen keine schriftliche Entschuldigung mehr fordern, da sowieso bereits genehmigt.
      
      if($this->isBefreiung()) return !DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen-befreiungen');
      
      // Wenn LNW vorhanden, dann Entschuldigung fordern.
      
      if(sizeof($this->getLeistungsnachweiseDuringAbsenzPeriod()) > 0 && DB::getSettings()->getBoolean('krankmeldung-hinweis-lnw')) return true;
      
      if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen')) {
          if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen-nur-portal')) {
              if($this->getKanal() != 'WEBPORTAL') return true;
          }
          
          return false;  // Keine Entschuldigung fordern
      }
      
      return true;      // Sonst immer fordern
  }

  /**
   * Ermittelt die angesagten Leistungsnachweise an diesem Tag.
   * @return Leistungsnachweis[] Leistungsnachweise
   */
  public function getLeistungsnachweiseDuringAbsenzPeriod() {
      $result = [];

          $lnws = Leistungsnachweis::getByClass([$this->getSchueler()->getKlasse()], $this->getDateAsSQLDate(), $this->getEnddatumAsSQLDate());

          for($l = 0; $l < sizeof($lnws); $l++) {
              if($lnws[$l]->showForNotTeacher()) {
                 $result[] = $lnws[$l];
              }
          }

      return $result;
  }

  public function isSchriftlichEntschuldigbar() {
    if(DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') > 0) {
      $dateEnde = $this->getEnddatumAsSQLDate();

      $entschuldigungDate = $dateEnde;
      $daysAdded = 0;

      while(true) {

        $entschuldigungDate = DateFunctions::addOneDayToMySqlDate($entschuldigungDate);

        if(!DateFunctions::isSQLDateWeekEnd($entschuldigungDate) && !Ferien::isFerien($entschuldigungDate)) $daysAdded++;

        if($daysAdded == DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung')) break;
      }

      if(DateFunctions::isSQLDateTodayOrLater($entschuldigungDate)) return true;
      else return false;

    }
    else return true;
  }

  public function getSchriftlichEntschuldigbarDate() {
    if(DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') > 0) {
      $dateEnde = $this->getEnddatumAsSQLDate();

      $entschuldigungDate = $dateEnde;
      $daysAdded = 0;

      while(true) {

        $entschuldigungDate = DateFunctions::addOneDayToMySqlDate($entschuldigungDate);

        if(!DateFunctions::isSQLDateWeekEnd($entschuldigungDate) && !Ferien::isFerien($entschuldigungDate)) $daysAdded++;

        if($daysAdded == DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung')) break;
      }

      return $entschuldigungDate;

    }
    else return '';
  }

  public function delete() {
    DB::getDB()->query("DELETE FROM absenzen_absenzen WHERE absenzID='" . $this->data['absenzID'] . "'");

    if($this->data['absenzBefreiungID'] > 0) {
      DB::getDB()->query("DELETE FROM absenzen_befreiungen WHERE befreiungID='" . $this->data['befreiungID'] . "'");
    }

    if($this->data['absenzBeurlaubungID'] > 0) {
      DB::getDB()->query("DELETE FROM absenzen_beurlaubungen WHERE beurlaubungID='" . $this->data['befreiungID'] . "'");
    }

  }

  public function setJetztGekommen() {
    $stunde = stundenplan::getCurrentStunde();

    $stunden = array();
    for($i = 1; $i <= $stunde; $i++) {
      $stunden[] = $i;
    }

    $this->setStunden($stunden);

    DB::getDB()->query("UPDATE absenzen_absenzen SET absenzKommtSpaeter='0' WHERE absenzID='" . $this->getID() . "'");
  }

  /**
   * @param $mysqldate
   * @param $klasse
   * @return Absenz[]
   */
  public static function getAbsenzenForDate($mysqldate,$klasse, $where = "absenzStunden != 'ganztag' AND") {
    $data = DB::getDB()->query("SELECT * FROM absenzen_absenzen LEFT JOIN schueler ON absenzen_absenzen.absenzSchuelerAsvID=schueler.schuelerAsvID
        WHERE $where
          absenzDatum <= '" . $mysqldate ."' AND absenzDatumEnde >= '" . $mysqldate . "'" . (($klasse != "") ? (" AND schuelerKlasse LIKE '" . $klasse . "'") : ("")) . "ORDER BY LENGTH(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");

    $absenzen = array();

    while($d = DB::getDB()->fetch_array($data)) {
      $absenzen[] = new Absenz($d);
    }

    return $absenzen;
  }

  public static function getAbsenzenForKlasse($klassenName) {
    $data = DB::getDB()->query("SELECT * FROM absenzen_absenzen JOIN schueler ON absenzen_absenzen.absenzSchuelerAsvID=schueler.schuelerAsvID
        WHERE schuelerKlasse LIKE '" . $klassenName . "' ORDER BY LENGTH(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");

    $absenzen = array();

    while($d = DB::getDB()->fetch_array($data)) {
      $absenzen[] = new Absenz($d);
    }

    return $absenzen;
  }

  public static function getAbsenzenForSchueler($schueler) {
    $data = DB::getDB()->query("SELECT * FROM absenzen_absenzen JOIN schueler ON absenzen_absenzen.absenzSchuelerAsvID=schueler.schuelerAsvID
        WHERE schuelerAsvID LIKE '" . $schueler->getAsvID() . "' ORDER BY LENGTH(schuelerKlasse) ASC, absenzDatum ASC, schuelerName ASC, schuelerRufname ASC");

    $absenzen = array();

    while($d = DB::getDB()->fetch_array($data)) {
      $absenzen[] = new Absenz($d);
    }

    return $absenzen;
  }

  /**
   *
   * @param unknown $id
   * @return Absenz|NULL
   */
  public static function getByID($id) {
    $absenz = DB::getDB()->query_first("SELECT * FROM absenzen_absenzen JOIN schueler ON absenzen_absenzen.absenzSchuelerAsvID=schueler.schuelerAsvID WHERE absenzID='" . DB::getDB()->escapeString($id) . "'");

    if($absenz['absenzID'] > 0) {
      return new Absenz($absenz);
    }
    else return null;
  }

  public static function wasAbsentOneDayBeforeDate($schueler, $sqldate) {
    $dataDate = explode("-",$sqldate);
    $timeDate = mktime(4,4,4,$dataDate[1],$dataDate[2],$dataDate[0]);

    $timeBefore = $timeDate - (24*60*60);

    if(date("N",$timeBefore) == "7") {
      // Sonntag
      $timeBefore -= 2*24*60*60;
    }

    $sqlDateBefore = date("Y-m-d",$timeBefore);

    $absenz = DB::getDB()->query_first("SELECT * FROM absenzen_absenzen JOIN schueler ON absenzen_absenzen.absenzSchuelerAsvID=schueler.schuelerAsvID WHERE absenzDatum <= '" . $sqlDateBefore . "' AND absenzDatumEnde >= '" . $sqlDateBefore . "' AND absenzSchuelerAsvID='" . $schueler->getAsvID() . "' AND absenzBeurlaubungID='0'");
    if($absenz['absenzID'] > 0) {
      return new Absenz($absenz);
    }
    else return null;

    return false;
  }

  /**
   * Berechnet die Tage, die wirklich krank waren. (Bei denen die Klasse anwesend war.)
   * @return int Tage
   * @deprecated @see AbsenzenCalculator
   */
  public function getTotalDays() {

    $klasse = $this->getSchueler()->getKlassenObjekt();

    $start = $this->data['absenzDatum'];
    $ende = $this->data['absenzDatumEnde'];

    $anzahl = 0;

    $currentDay = $start;
    while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {

      $tag = DateFunctions::getDayFromMySqlDate($currentDay);

      if($tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
          if($klasse->isAnwesend(DateFunctions::getNaturalDateFromMySQLDate($currentDay))) {
          $anzahl++;
        }
      }

      $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
    }

    return $anzahl;
  }


  /**
   * Berechnet die Tage, die wirklich krank waren. (Bei denen die Klasse nicht anwesend war.) (fpa Tage)
   * @return int Tage
   */
  public function getTotalDaysNotAnwesend() {

    $klasse = $this->getSchueler()->getKlassenObjekt();


    $start = $this->data['absenzDatum'];
    $ende = $this->data['absenzDatumEnde'];

    $anzahl = 0;

    $currentDay = $start;
    while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {

      $tag = DateFunctions::getDayFromMySqlDate($currentDay);

      if($tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
        if($klasse != null) {
          if(!$klasse->isAnwesend(DateFunctions::getNaturalDateFromMySQLDate($currentDay))) {
            $anzahl++;
          }
        }
      }


      $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
    }

    return $anzahl;

  }

  /**
   *
   * @deprecated @see AbsenzenCalculator
   * @param int[] $stats array(0 => 0; 1 => Januar; 2 => Februar; 3 => ....
   * @param String[] $daysWithAbsenzen Tage, an denen bereits eine Absenzen eingetragen ist
   * @return int[] stats
   */
  public function getDaysToStats($stats, $daysWithAbsenzen = []) {
    $start = $this->data['absenzDatum'];
    $ende = $this->data['absenzDatumEnde'];


    $currentDay = $start;

    // Eine interne Beurlaubung zählt nicht zur Statistik
    if($this->isBeurlaubung() && $this->getBeurlaubung()->isInternAbwesend()) return $stats;

    while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {

      $tag = DateFunctions::getDayFromMySqlDate($currentDay);

      if($tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
        $monat = DateFunctions::getMonthFromMySqlDate($currentDay);
        $stats[$monat]++;
        $stats['gesamt']++;
      }

      $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
    }

    return $stats;

  }

  /**
   * @deprecated @see AbsenzenCalculator
   * @return number
   */
  public function getBeurlaubungTage() {
    $start = $this->data['absenzDatum'];
    $ende = $this->data['absenzDatumEnde'];

    $anzahl = 0;

    $currentDay = $start;
    while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {

      $tag = DateFunctions::getDayFromMySqlDate($currentDay);

      if($tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay) && $this->isBeurlaubung()) $anzahl++;

      $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
    }

    return $anzahl;

  }

  /**
   * @deprecated @see AbsenzenCalculator
   * @param unknown $stat
   * @return unknown
   */
  public function addAbsenzenStat($stat) {
    $start = $this->data['absenzDatum'];
    $ende = $this->data['absenzDatumEnde'];

    $anzahl = 0;

    $currentDay = $start;
    while(DateFunctions::isSQLDateAtOrBeforeAnother($currentDay, $ende)) {

      $tag = DateFunctions::getDayFromMySqlDate($currentDay);

      if($tag != 0 && $tag != 6 && !Ferien::isFerien($currentDay)) {
        $stat[$tag-1]++;
      }

      $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
    }

    return $stat;
  }

}





?>
