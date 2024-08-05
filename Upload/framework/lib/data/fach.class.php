<?php

class fach {
  private static $all = [];

  private $data = [];

  /**
   *
   * @var lehrer[]
   */
  private $fachbetreuer = [];

  private function __construct($data) {
    $this->data = $data;
  }

  public function getID() {
    return $this->data['fachID'];
  }

  public function getKurzform() {
    return $this->data['fachKurzform'];
  }


  public function getLangform() {
    return $this->data['fachLangform'];
  }

  public function getASDID() {
      return $this->data['fachASDID'];
  }

  public function isSelbstErstellt() {
      return $this->data['fachIstSelbstErstellt'] > 0;
  }

  public function getOrdnungszahl() {
      return $this->data['fachOrdnung'];
  }

  public function setOrdnungszahl($ordnung) {
      DB::getDB()->query("UPDATE faecher SET fachOrdnung='" . intval($ordnung) . "' WHERE fachID='" . $this->getID() . "'");
  }


  /**
   *
   * @return NULL[]|lehrer[]
   */
  public function getFachbetreuer() {
     $result = [];

     if(sizeof($this->fachbetreuer) > 0) return $this->fachbetreuer;

     for($i = 1; $i <= 5; $i++) {
         $lehrer = lehrer::getByASVId(DB::getSettings()->getValue('schulinfo-fachbetreuer-' . $this->getASDID() . '-' . $i));

         if($lehrer != null) {
             $result[] = $lehrer;
         }
     }

     return $result;
  }

  /**
   *
   * @param lehrer $lehrer
   */
  public function isFachschaftsleitung($lehrer) {
      $fbs = $this->getFachbetreuer();

      for($i = 0; $i < sizeof($fbs); $i++) {
          if($fbs[$i]->getAsvID() == $lehrer->getAsvID()) return true;
      }

      return false;
  }


  /**
   * 
   * @param lehrer $lehrer
   */
  public static function getMyFachschaftsleitungFaecher($lehrer) {
      
      $fbFaecher = [];
      
      $faecher = self::getAll();
      
      for($i = 0; $i < sizeof($faecher); $i++) {
          if($faecher[$i]->isFachschaftsleitung($lehrer)) {
              $fbFaecher[] = $faecher[$i];
          }
      }
      
      return $fbFaecher; 
      

      
  }

  /**
   * @return lehrer[]
   */
  public function getFachLehrer() {
    $alleLehrer = DB::getDB()->query("SELECT * FROM lehrer WHERE lehrerID IN (SELECT DISTINCT unterrichtLehrerID FROM unterricht WHERE unterrichtFachID='" . $this->getID() . "')");

    $answer = [];

    while($l = DB::getDB()->fetch_array($alleLehrer)) $answer[] = new lehrer($l);

    return $answer;
  }

  /**
   *
   * @return fach[] alle
   */
  public static function getAll() {

    if(sizeof(self::$all) == 0) {
      $alleSQL = DB::getDB()->query("SELECT * FROM faecher ORDER BY fachOrdnung ASC");
      while($d = DB::getDB()->fetch_array($alleSQL)) self::$all[] = new fach($d);
    }
    
    return self::$all;
  }

    public static function getAllAktive()
    {
        $all = self::getAll();
        $ret = [];
        foreach ($all as $item) {
            if( DB::getSettings()->getBoolean("schulinfo-fach-" . $item->getID() . "-unterrichtet")) {
                $ret[] = $item;
            }
        }
        return $ret;
    }
  
  public static function getAllUnterrichtet() {
    $all = self::getAll();

    $answer = [];

    for($i = 0; $i < sizeof($all); $i++) {
      if(DB::getSettings()->getBoolean("schulinfo-fach-" . $all[$i]->getID() . "-unterrichtet")) $answer[] = $all[$i];
    }

    return $answer;
  }

  /**
   *
   * @param int $id
   * @return fach|null
   */
  public static function getByID($id) {
    $all = self::getAll();

    for($i = 0; $i < sizeof($all); $i++) {
      if($all[$i]->getID() == $id) return $all[$i];
    }

    return null;
  }


  /**
   *
   * @param unknown $id
   * @return fach|NULL
   */
  public static function getByKurzform($id) {
      $all = self::getAll();

      for($i = 0; $i < sizeof($all); $i++) {
          if($all[$i]->getKurzform() == $id) return $all[$i];
      }

      return null;
  }

  public static function getByASDID($id) {
      $all = self::getAll();

      for($i = 0; $i < sizeof($all); $i++) {
          if($all[$i]->getASDID() == $id) return $all[$i];
      }

      return null;
  }

  public static function getDummy() {
    return new fach([
      'fachID' => 0,
      'fachKurzform' => 'n/a',
      'fachLangform' => 'n/a'
    ]);
  }
}
