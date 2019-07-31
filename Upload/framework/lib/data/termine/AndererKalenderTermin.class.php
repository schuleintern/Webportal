<?php

class AndererKalenderTermin extends AbstractTermin {
  private $kategorie = null;
    
  
  protected $tableName = 'kalender_andere';
  
  public function __construct($data) {
    parent::__construct($data);
    
    if($data['eintragKategorie'] > 0) {
        $this->kategorie = KalenderKategorie::getByID($data['eintragKategorie']);
    }
  }
  
  public function getKategorie() {
      return $this->kategorie;
  }


  public static function getAll($kalenderID, $startDate, $endDate) {
    $all = [];
    
    $where = " (eintragDatumStart >= '$startDate' AND eintragDatumEnde <= '$endDate') OR 

        (eintragDatumStart <= '$startDate' AND eintragDatumEnde <= '$endDate' AND eintragDatumEnde >= '$startDate') OR 
        
        (eintragDatumStart >= '$startDate' AND eintragDatumStart <= '$endDate' AND eintragDatumEnde >= '$endDate') OR 

        (eintragDatumStart <= '$startDate' AND eintragDatumEnde >= '$endDate')

        ";
    

    $dataAll = DB::getDB()->query("SELECT * FROM kalender_andere WHERE kalenderID='" . $kalenderID . "' AND ($where) ORDER BY eintragDatumStart ASC");

    while($d = DB::getDB()->fetch_array($dataAll)) $all[] = new AndererKalenderTermin($d);
    
    return $all;
  }
  
  /**
   * 
   * @param unknown $terminID
   * @return AndererKalenderTermin|NULL
   */
  public static function getByID($terminID) {
      $dataAll = DB::getDB()->query_first("SELECT * FROM kalender_andere WHERE eintragID='" . intval($terminID) . "'");
      if($dataAll['eintragID'] > 0) return new AndererKalenderTermin($dataAll);
      else return null;
  }
  
  public function updateTitel($titel) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragTitel='" . DB::getDB()->escapeString($titel) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateOrt($ort) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragOrt='" . DB::getDB()->escapeString($ort) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateKommentar($kommentar) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragKommentar='" . DB::getDB()->escapeString($kommentar) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateKategorie($kgID) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragKategorie='" . DB::getDB()->escapeString($kgID) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateStartDatum($titel) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragDatumStart='" . DB::getDB()->escapeString($titel) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateEndDatum($titel) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragDatumEnde='" . DB::getDB()->escapeString($titel) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateIsWholeDay($status) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragIsWholeDay='" . DB::getDB()->escapeString($status ? 1 : 0) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateStartZeit($zeit) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragUhrzeitStart='" . DB::getDB()->escapeString($zeit) . "' WHERE eintragID='" . $this->getID() . "'");
  }
  
  public function updateEndZeit($zeit) {
      DB::getDB()->query("UPDATE kalender_andere SET eintragUhrzeitEnde='" . DB::getDB()->escapeString($zeit) . "' WHERE eintragID='" . $this->getID() . "'");
  }  
}
