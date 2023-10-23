<?php


class klasse {

  /**
   * 
   * @var klasse[]
   */
    private static $klassen = array();

  private $klassenName = "";
  private $anzahlSchueler = 0;
  private $klassenleitung = array();
  private $schueler = array();


  public function __construct($klassenname, $anzahlSchueler) {
    $this->klassenName = $klassenname;
    $this->anzahlSchueler = $anzahlSchueler;


  }
  public function getAnzahlSchueler() {
    return $this->anzahlSchueler;
  }

  public function getKlassenName() {
    return $this->klassenName;
  }

  public function getKlassenstufe() {
    $data = DB::getDB()->query_first("SELECT DISTINCT schuelerJahrgangsstufe FROM schueler WHERE schuelerKlasse='" . $this->klassenName . "' AND schuelerJahrgangsstufe != ''");

    return $data[0];
  }

  /**
   * Liest alle Ausbildungsrichtungen
   * @return String[]
   */
  public function getAusbildungsrichtungen() {
    $data = DB::getDB()->query("SELECT DISTINCT schuelerAusbildungsrichtung FROM schueler WHERE schuelerKlasse='" . $this->klassenName . "'");

    $ausb = [];

    while($a = DB::getDB()->fetch_array($data)) {
      $ausb[] = $a[0];
    }

    return $ausb;
  }
  
  /**
   * 
   * @return NULL[]|lehrer[]
   */
  public function getKlassenlehrer() {
      $lehrer = [];
      
      $unterricht = SchuelerUnterricht::getUnterrichtForKlasse($this);
      
      for($i = 0; $i < sizeof($unterricht); $i++) {
          $l = $unterricht[$i]->getLehrer();
          
          if($l != null) {
              $found = false;
              for($j = 0; $j < sizeof($lehrer); $j++) {
                  if($lehrer[$j]->getAsvID() == $l->getAsvID()) {
                      $found = true;
                  }
              }
              
              if(!$found) {
                  $lehrer[] = $l;
              }
          }
      }
      
      return $lehrer;
  }



  /**
   * Ermittelt die Klassenleitung der Klasse
   * @return lehrer[] Lehrerobjekte, die Klassenleitungen sind
   */
  public function getKlassenLeitung() {
    if(sizeof($this->klassenleitung) == 0) {
      $kDB = DB::getDB()->query("SELECT * FROM klassenleitung NATURAL JOIN lehrer WHERE klassenleitung.klasseName LIKE '" . $this->klassenName . "' ORDER BY klassenleitungArt ASC");
      while($k = DB::getDB()->fetch_array($kDB)) {

        $this->klassenleitung[] = new lehrer($k);
      }
    }

    return $this->klassenleitung;
  }

  /**
   * Überprüft, ob der Lehrer erste Klassenleitung ist.
   * @param lehrer $lehrer
   * @return bool janein
   */
  public function isFirstKlassenleitung($lehrer) {
    $kDB = DB::getDB()->query("SELECT * FROM klassenleitung NATURAL JOIN lehrer WHERE klassenleitung.klasseName LIKE '" . $this->klassenName . "'");
    while($k = DB::getDB()->fetch_array($kDB)) {
      if($k['lehrerAsvID'] == $lehrer->getAsvID()) {
        return $k['klassenleitungArt'] == 1;
      }
    }

    return false;
  }

  /**
   *
   * @param lehrer $lehrer
   */
  public function isKlassenLeitung($lehrer) {
    $klassenleitung = $this->getKlassenLeitung();
    for($i = 0; $i < sizeof($klassenleitung); $i++) {
      if($klassenleitung[$i]->getKuerzel() == $lehrer->getKuerzel()) return true;
    }

    return false;
  }


  public function getKlassenleitungAll($klassenNamen = false) {

        if (!$klassenNamen) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM klassenleitung WHERE klasseName = '" .$klassenNamen."' ORDER BY klassenleitungArt");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = $data;
        }
        return $ret;
  }

  /**
   *
   * @return schueler[] Schueler der Klasse
   */
  public function getSchueler($withAusgetretene=true) {
    if(sizeof($this->schueler) == 0) {
      if($withAusgetretene) $schuelerSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerKlasse='" . $this->klassenName . "' ORDER BY schuelerName ASC, schuelerRufname ASC");
      else $schuelerSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerKlasse='" . $this->klassenName . "' AND (schuelerAustrittDatum IS NULL OR schuelerAustrittDatum > CURDATE()) ORDER BY schuelerName ASC, schuelerRufname ASC");

      while($s = DB::getDB()->fetch_array($schuelerSQL)) {

        $this->schueler[] = new schueler($s);
      }
    }

    return $this->schueler;

  }

  /**
   * @return klasse[] Klassen
   */
  public static function getAllKlassen() {
    if(sizeof(self::$klassen) == 0) {
      $klassen = DB::getDB()->query("SELECT DISTINCT s1.schuelerKlasse, (SELECT COUNT(s2.schuelerAsvID) FROM schueler AS s2 WHERE s2.SchuelerKlasse=s1.schuelerKlasse) AS anzahlSchueler FROM schueler as s1 ORDER BY LENGTH(s1.schuelerKlasse), s1.schuelerKlasse ASC");
      while($klasse = DB::getDB()->fetch_array($klassen)) {
        self::$klassen[] = new klasse($klasse['schuelerKlasse'],$klasse['anzahlSchueler']);
      }
    }

    return self::$klassen;
  }

  /**
   * 
   * @param unknown $level
   * @return klasse[]
   */
  public static function getAllAtLevel($level) {
      $allKlassen = self::$klassen;

      $atStufe = [];

      for($i = 0; $i < sizeof($allKlassen); $i++) {
          if($allKlassen[$i]->getKlassenStufe() == $level) $atStufe[] = $allKlassen[$i];
      }
      
      return $atStufe;
  }

  public static function getAnzahlKlassen() {
    $a = DB::getDB()->query("SELECT DISTINCT schuelerKlasse FROM schueler");

    return DB::getDB()->num_rows($a);
  }

  public static function getByName($name) {

    $alle = self::getAllKlassen();

    for($i = 0; $i < sizeof($alle); $i++) {
      if($alle[$i]->getKlassenName() == $name) {
        return $alle[$i];
      }
    }

    return new klasse($name, 0);
  }
  
  /**
   * 
   * @return klasse[]
   */
  public static function getMyKlassen() {
      if(DB::getSession()->isTeacher()) {
          return klasse::getByUnterrichtForTeacher(DB::getSession()->getTeacherObject());
      }
      
      if(DB::getSession()->isPupil()) {
          return [DB::getSession()->getPupilObject()->getKlassenObjekt()];
      }
      
      if(DB::getSession()->isEltern()) {
          return DB::getSession()->getElternObject()->getKlassenObjectsAsArray();
      }
      
      return [];
  }

  public static function getByStundenplanName($stundenplan) {
    // TODO: Universell?

    $klassenname = $stundenplan;

    // Falls eine Ausbildungsrechnung angegeben ist, diese entfernen.
    if(strpos($stundenplan,"_") > 0) {
      $klassenname = substr($stundenplan, 0, strpos($stundenplan,"_"));
    }

    return self::getByName($klassenname);
  }

  /**
   *
   * @param String[] $klassen
   * @return klasse[]
   */
  public static function getByStundenplanKlassen($klassen) {
    $grades = [];

    for($i = 0; $i < sizeof($klassen); $i++) {
      $g = self::getByStundenplanName($klassen[$i]);
      if($g != null) $grades[] = $g;
    }

    return $grades;
  }

  private $tageAnwesend = [];

  /**
   *
   * @param string $date Natural Date
   */
  public function isAnwesend($date='') {


    if($date == '') $date = DateFunctions::getTodayAsNaturalDate();

    if(sizeof($this->tageAnwesend) == 0) {

      $this->tageAnwesend = DB::getSettings()->getValue('klassenabwesenheit_' . $this->getKlassenName());
      $this->tageAnwesend = explode("\n",$this->tageAnwesend);


      for($i = 0; $i < sizeof($this->tageAnwesend); $i++) {
          $this->tageAnwesend[$i] = str_replace("\r","",str_replace("\n","",$this->tageAnwesend[$i]));
      }
    }



    if(in_array($date, $this->tageAnwesend)) return false;
    else return true;
  }


  /**
   * 
   * @param unknown $teacher
   * @return klasse[]
   */
  public static function getByUnterrichtForTeacher($teacher) {
    /**
     *
     * @var klasse[] $klassen
     */
    $klassen = [];

    // TODO schneller?
    

      //$unterricht = SchuelerUnterricht::getUnterrichtForLehrer($teacher, true);       // Kopplungen ignorieren, da hier nur Klassen gesucht werden.
      $unterrichtDB = DB::getDB()->query("SELECT  DISTINCT unterrichtKlassen FROM unterricht WHERE unterrichtLehrerID = " . $teacher->getXMLID() . " ");

      while ($unterricht = DB::getDB()->fetch_array($unterrichtDB)) {
          $klassen[] = self::getByName($unterricht['unterrichtKlassen']);
      }
      //$klassen = super_unique($klassen,'klasse');
      usort($klassen, ['klasse', 'cmp_obj']);

      /*
    $unterricht = SchuelerUnterricht::getUnterrichtForLehrer($teacher, true);       // Kopplungen ignorieren, da hier nur Klassen gesucht werden.

    for($i = 0; $i < sizeof($unterricht); $i++) {

      $klassenDesUnterrichts = $unterricht[$i]->getAllKlassen();

      for($g = 0; $g < sizeof($klassenDesUnterrichts); $g++) {
        $found = false;

        for($k = 0; $k < sizeof($klassen); $k++) {
          if($klassen[$k]->getKlassenName() == $klassenDesUnterrichts[$g]->getKlassenName()) {
            $found = true;
            break;
          }
        }

        if(!$found) {
          $klassen[] = $klassenDesUnterrichts[$g];
        }
      }
    }
    
    usort($klassen, ['klasse','cmp_obj']);
*/

    return $klassen;
  }
  
  /**
   * 
   * @param klasse $a
   * @param klasse $b
   * @return number
   */
  static function cmp_obj($a, $b) {
      $al = strtolower($a->getKlassenName());
      $bl = strtolower($b->getKlassenName);
      if ($al == $bl) {
          return 0;
      }
      return ($al > $bl) ? +1 : -1;
  }
}


?>
