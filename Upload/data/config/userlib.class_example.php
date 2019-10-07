<?php 

class userlib {

	
	/**
	 * Liest zu einem Lehrerbenutzer, den Lehrerdatensatz aus
	 * @param int $userID UserID
	 * @return array() or NULL
	 */
	public function getLehrerUser($lehrerID) {
		$lehrer = DB::getDB()->query_first("SELECT * FROM lehrer WHERE lehrerAsvID='" . $lehrerID . "'");
		
		$lehrerUserName = strtolower($lehrer['lehrerKuerzel']);
		$lehrerUserName = str_replace("ö","oe",$lehrerUserName);
		$lehrerUserName = str_replace("ü","ue",$lehrerUserName);
		$lehrerUserName = str_replace("ä","ae",$lehrerUserName);
		$lehrerUserName = str_replace("ß","ss",$lehrerUserName);
		
		
		$user = DB::getDB()->query_first("SELECT * FROM users WHERE userName LIKE '" . $lehrerUserName . "'");
		
		if($user['userID'] > 0) return $user;
		else return null;
	}
	
	
	
	/**
	 * Liest zu einem Sch�lerbenutzer, den Sch�lerdatensatz aus
	 * @param int $userID UserID
	 */
	public function getPupilUser($pupilID) {
		// $schueler = DB::getDB()->query_first("SELECT * FROM schueler WHERE schuelerAsvID='" . $pupilID . "'");
		
		$user = DB::getDB()->query_first("SELECT * FROM users WHERE userAsvID LIKE '" . $pupilID . "'");
		
		if($user['userName'] != "") return $user;
		else return null;
	}
}


?>