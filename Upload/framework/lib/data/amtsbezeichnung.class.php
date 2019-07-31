<?php

class amtsbezeichnung {
  private static $all = [];

  private $data = [];
  
  private $isWeiblich = false;

  private function __construct($data, $isWeiblich) {
    $this->data = $data;
    $this->isWeiblich = $isWeiblich;
  }
  
  public function getID() {
  	return $this->data['amtsbezeichnungID'];
  }
  
  public function getKurzform() {
  	if($this->isWeiblich) return $this->data['amtsbezeichnungKurzformW'];
  	return $this->data['amtsbezeichnungKurzform'];
  }
  
  public function getAnzeigeform() {
  	if($this->isWeiblich) return $this->data['amtsbezeichnungAnzeigeformW'];
  	return $this->data['amtsbezeichnungAnzeigeform'];
  }
 

  /**
   * 
   * @return amtsbezeichnung[] alle
   */
  public static function getAll() {
    if(sizeof(self::$all) == 0) {
      $alleSQL = DB::getDB()->query("SELECT * FROM amtsbezeichnungen WHERE amtsbezeichnungID IN (SELECT DISTINCT lehrerAmtsbezeichnung FROM lehrer)");
      while($d = DB::getDB()->fetch_array($alleSQL)) {
      	self::$all[] = new amtsbezeichnung($d, false);
      	self::$all[] = new amtsbezeichnung($d, true);
      }
    }
    
    return self::$all;
  }
  
  /**
   * 
   * @param int $id
   * @return amtsbezeichnung|null
   */
  public static function getByID($id, $isweiblich) {
  	$all = self::getAll();
  	
  	for($i = 0; $i < sizeof($all); $i++) {
  		if($all[$i]->getID() == $id && $all[$i]->isWeiblich == $isweiblich) return $all[$i];
  	}
  	
  	return null;
  }
  
  public static function getDummy() {
  	return new amtsbezeichnung([
  		'amtsbezeichnungID' => 0,
  		'amtsbezeichnungKurzform' => 'n/a',
  		'amtsbezeichnungAnzeigeform' => 'n/a',
  		'amtsbezeichnungLangform' => 'n/a'
  	], false);
  }
}
