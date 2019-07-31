<?php

class Lehrertermin extends AbstractTermin {
  public function __construct($data) {
    parent::__construct($data);
  }


  public static function getAll($startDate = "", $endDate = "") {
    $all = [];

    $where = "";
    if($startDate != "") {
      $where = " WHERE (eintragDatumStart >= '" . $startDate . "' OR (eintragDatumStart <= '" . $startDate . "' AND eintragDatumEnde >= '" . $startDate . "'))";
    }

    if($endDate != "") {
      if($where == "") $where = " WHERE ";
      else $where .= " AND ";
      $where .= " (eintragDatumStart <= '" . $endDate . "' OR (eintragDatumStart >= '" . $endDate . "' AND eintragDatumEnde <= '" . $endDate . "'))";
    }

    $dataAll = DB::getDB()->query("SELECT * FROM kalender_lehrer$where");

    while($d = DB::getDB()->fetch_array($dataAll)) $all[] = new Lehrertermin($d);
    
    return $all;
  }

}
