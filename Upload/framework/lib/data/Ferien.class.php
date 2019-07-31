<?php 

/**
 * Klasse für Ferien und Feiertage
 * @author Christian Spitschka / Spitschka IT Solutions
 *
 */
class Ferien {
	private static $ferien = array();
	
	/**
	 * Liest aus, ob an diesem Tag Ferien sind.
	 * @param String $sqlDate
	 * @return null|String null, wenn keine Ferien. Name der Ferien, wenn wahr.
	 */
	public static function isFerien($sqlDate) {
		if(sizeof(self::$ferien) == 0) self::initFerien();
		
		for($i = 0; $i < sizeof(self::$ferien); $i++) {
			if(
			 	DateFunctions::isSQLDateAtOrAfterAnother($sqlDate, self::$ferien[$i]['ferienStart'])
					&&
				DateFunctions::isSQLDateAtOrBeforeAnother($sqlDate, self::$ferien[$i]['ferienEnde'])
			) {
				return self::$ferien[$i]['ferienName'];
			}
		}
		
		return null;
	}

	
	private static function initFerien() {
		$ferien = DB::getDB()->query("SELECT * FROM kalender_ferien WHERE ferienSchuljahr='" . DB::getSettings()->getValue("general-schuljahr") . "'");
		while($f = DB::getDB()->fetch_array($ferien)) self::$ferien[] = $f;
	}
	
	public static function getBeforeShowForDatePicker() {
		$html = ',' . "\r\n" . 'beforeShowDay: function (checkDate) {' . "\r\n";
		
		if(sizeof(self::$ferien) == 0) self::initFerien();
		
		$ferien = self::$ferien;
		
		for($i = 0; $i < sizeof($ferien); $i++) {
			$start = explode("-",$ferien[$i]['ferienStart']);
			$ende = explode("-",$ferien[$i]['ferienEnde']);
			
			$html .= 'var date' . $ferien[$i]['ferienID'] . 's = new Date(' . $start[0] . ',' . ($start[1]) . ',' . $start[2] . ');' . "\r\n";
			$html .= 'var date' . $ferien[$i]['ferienID'] . 'e = new Date(' . $ende[0] . ',' . ($ende[1]) . ',' . $ende[2] . ');' . "\r\n";			

			$html .= 'if(checkDate >= date' . $ferien[$i]['ferienID'] . 's ';
			$html .= '&& checkDate <= date' . $ferien[$i]['ferienID'] . 'e ) {' . "\r\n";
			$html .= "return [false, '', '" . $ferien[$i]['ferienName'] . "'];\r\n}\r\n";
		}


		$html .= "return [true, '', ''];\r\n";
		
		$html .= '}' . "\r\n\r\n";
		
		return $html;
	}
}

?>