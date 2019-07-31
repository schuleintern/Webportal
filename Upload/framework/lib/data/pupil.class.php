<?php


class pupil {
	
	/**
	 * 
	 * @param unknown $grade
	 * @deprecated Klasse "klasse" verwenden (basiert auf ASV Daten)
	 */
	public static function getUsersOfGrade($grade) {
		$users = array();
		
		if(strpos($grade,"_") > 0) $grade = substr($grade,0,strpos($grade,"_"));
		
		$ds = DB::getDB()->query("SELECT * FROM users WHERE userID IN (SELECT userID FROM users_groups WHERE groupName LIKE 'G_" . $grade . "' AND userNetwork='SCHULE') ORDER BY userLastName COLLATE latin1_german2_ci, userFirstName COLLATE latin1_german2_ci");	
		
		while($u = DB::getDB()->fetch_array($ds)) $users[] = $u;
		
		return $users;
	
	}

}

?>