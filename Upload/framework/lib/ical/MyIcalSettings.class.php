<?php

class MyIcalSettings {
	private $user = null;
	
	private $showStundenplan = true;
	private $showLNW = true;
	private $showKlassentermine = true;
	
	private $showOnlyOwnGrades = true;
	
	private $showLehrer = true;
	private $showSchulkalener = true;
	
	private function __construct() {
		
	}
	
	/**
	 * 
	 * @param user $user
	 */
	public static function getSettingsForUser($user) {
		$settings = DB::getDB()->query_first("SELECT * FROM icalsettings WHERE userID='" . $user->getUserID() . "'");
		if($settings['userID'] > 0) {
			$settings = new MyIcalSettings();
			$settings->showStundenplan = ($settings['showStundenplan'] == "1");
			
		}
	}
}

