<?php

class ExtKalenderTermin extends AbstractTermin {
  public function __construct($data) {
    parent::__construct($data);
  }


  public static function getAll($kalenderID, $startDate, $endDate) {
    $all = [];

    $where = " AND ((eintragDatumStart >= '$startDate' AND eintragDatumEnde <= '$endDate') OR
    
    (eintragDatumStart <= '$startDate' AND eintragDatumEnde <= '$endDate' AND eintragDatumEnde >= '$startDate') OR
    
    (eintragDatumStart >= '$startDate' AND eintragDatumStart <= '$endDate' AND eintragDatumEnde >= '$endDate') OR
    
    (eintragDatumStart <= '$startDate' AND eintragDatumEnde >= '$endDate')
    )
    ";

    $dataAll = DB::getDB()->query("SELECT * FROM kalender_extern WHERE kalenderID='" . $kalenderID . "' $where ORDER BY eintragDatumStart ASC");

    while($d = DB::getDB()->fetch_array($dataAll)) $all[] = new ExtkalenderTermin($d);
    
    return $all;
  }
  
  
  public function getEintragZeitpunkt() {
      return '-';
  }

}
