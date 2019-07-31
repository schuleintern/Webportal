<?php 

class TextTrenner {
	private static $trennCache = [];
	
	
	/**
	 * Trennt ein Wort.
	 * @param String $wort Wort
	 * @param String $trennZeichen Zeichen zum Trenne. (z.B. "-" oder "-<br />")
	 * @param int $maxLength Wird eine maximale Länge angegeben, wird das Wort passend getrennt. Wird keine Länge oder 0 angegeben, dann wird das Wort an der ersten passenden Stelle getrennt.
	 */
	public static function trennWort($wort, $trennZeichen, $maxLength=0) {
		if(self::$trennCache[$wort] != "") {
			$trennung = explode("-",self::$trennCache[$wort]);
			
			
		}
	}
	
	
	/**
	 * Importiert eine trenn.dat
	 * Vorhandene Einträge werden aktualisiert. Selbst eingefügte Einträge bleiben erhalten.
	 * @param String $file Pfad zur trenn.dat
	 */
	public static function importTrennDat($file) {
		if(file_exists($file)) {
			$fileData = file($file);
			
			$trenn = implode("",$fileData);
			
			$trenn = explode("  ",$trenn);
			
			$realTrenn = [];
			
			for($i = 0; $i < sizeof($trenn); $i++) {
				$word = trim($trenn[$i]);
				if($word != "") $realTrenn[] = trim($trenn[$i]);
			}
			
			$woerter = [];
			
			for($i = 0; $i < sizeof($realTrenn); $i++) {
				if(strpos($realTrenn[$i], "-") > 0) {
					$wortOhne = str_replace("-","",$realTrenn[$i]);
					$woerter[] = [$realTrenn[$i],$wortOhne];
				}
			}
			
			for($i = 0; $i < sizeof($woerter); $i++) {
				DB::getDB()->query("INSERT INTO trenndaten (trennWort, trennWortGetrennt)
				values(
					'" . DB::getDB()->escapeString(utf8_encode($woerter[$i][1])) . "',
					'" . DB::getDB()->escapeString(utf8_encode($woerter[$i][0])) . "'
				) ON DUPLICATE KEY UPDATE trennWortGetrennt='" . DB::getDB()->escapeString(utf8_encode($woerter[$i][0])) . "'");
			}
		}	
	}
}