<?php 


class AbsenzSchuelerInfo {
	private static $attestpflichtenLoaded = false;
	private static $attestpflichten = array();
	
	private static $commentLoaded = false;
	private static $comments = array();
	
	/**
	 * Überprüft, ob zum angegebenen Datum Attestpflicht besteht.
	 * @param schueler $schueler
	 * @param String  $date SQL Date (Wenn nicht angegeben, wird das heutige Datum verwendet)
	 * @return boolean Ja/Nein
	 */
	public static function hasAttestpflicht($schueler,$date="") {
		if(DB::getSettings()->getBoolean('absenzen-generelleattestpflicht')) return true;
		if($date == "") $date = DateFunctions::getTodayAsSQLDate();
		else if(DateFunctions::isNaturalDate($date)) $date = DateFunctions::getMySQLDateFromNaturalDate($date);
		
		if(is_object($schueler)) {
			if(!self::$attestpflichtenLoaded) self::loadAttestpflicht();
			
			for($i = 0; $i < sizeof(self::$attestpflichten); $i++) {
				if($schueler->getAsvID() == self::$attestpflichten[$i]['schuelerAsvID']) {
					if(DateFunctions::isSQLDateAtOrAfterAnother($date, self::$attestpflichten[$i]['attestpflichtStart'])) {
						if(DateFunctions::isSQLDateAtOrBeforeAnother($date, self::$attestpflichten[$i]['attestpflichtEnde'])) {
							return true;
						}
					}
				}	
			}
			
			return false;
		}
		else {
			return false;
		}
	}
	
	public static function getAllAttestpflichtData($schueler) {
		if(is_object($schueler)) {
			
			if(self::$attestpflichtenLoaded == false) self::loadAttestpflicht();
			
			$aps = array();
			for($i = 0; $i < sizeof(self::$attestpflichten); $i++) {
				if($schueler->getAsvID() == self::$attestpflichten[$i]['schuelerAsvID']) {
					$aps[] = self::$attestpflichten[$i];
				}
			}
				
			return $aps;
		}
		else return array();
	}
	
	private static function loadAttestpflicht() {
		$attestpflichten = DB::getDB()->query("SELECT * FROM absenzen_attestpflicht LEFT JOIN users ON absenzen_attestpflicht.attestpflichtUserID=users.userID");
		
		while($ap = DB::getDB()->fetch_array($attestpflichten)) {
			self::$attestpflichten[] = $ap;
		}
		
		self::$attestpflichtenLoaded = true;
	}
	
	private static function loadComments() {
		$comments = DB::getDB()->query("SELECT * FROM absenzen_comments");
		while($c = DB::getDB()->fetch_array($comments)) {
			self::$comments[] = $c;
		}
		
		self::$commentLoaded = true;
	}
	
	public static function getComment(schueler $schueler) {
		if(is_object($schueler)) {
			if(!self::$commentLoaded) self::loadComments();
				
			for($i = 0; $i < sizeof(self::$comments); $i++) {
				if($schueler->getAsvID() == self::$comments[$i]['schuelerAsvID']) {
					return self::$comments[$i]['commentText'];
				}
			}
				
			return false;
		}
		else {
			return "";
		}
	}
}



?>