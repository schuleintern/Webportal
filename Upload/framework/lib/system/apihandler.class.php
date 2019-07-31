<?php


/**
 * Verwaltet die Crons.
 * @author Christian Spitschka
 * @version 1.0 Beta
 */
class apihandler {
	private static $allowedActions = array(
			"GetSchoolData",
			"SingleSignOn"
	);
	
	
	public function __construct($action) {
		
		//Check Access Key
		
		if(DB::getGlobalSettings()->apiKey != $_REQUEST['apiKey']) {
			echo("Invalid Access Key!");
			exit(0);
		}
		
		//
		
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			require_once('../framework/lib/api/'.self::$allowedActions[$i].".class.php");
		}
		
		// Remove Slashes in pages
		for($i = 0; $i < sizeof(self::$allowedActions); $i++) {
			if(strpos(self::$allowedActions[$i],"/") > 0) self::$allowedActions[$i] = substr(self::$allowedActions[$i], strpos(self::$allowedActions[$i],"/")+1);
		}
		
		if(in_array($action,self::$allowedActions)) {
			$page = new $action;
			$page->execute();
		}
		else {
			die("<schuleintern><error>Given API Name not availible</error></schuleintern>");
			exit();
		}
	}
	
	public static function getAllowedActions() {
		return self::$allowedActions;
	}
}
