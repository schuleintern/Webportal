<?php



class DB {
	private static $db;
	private static $tpl;
	
	/**
	 * 
	 * @var session
	 */
	private static $session = null;
	private static $settings = null;
	private static $globalsettings = null;

	public static $urlToHome;

	public static $isSchule = false;

	public static $userNetwork = "";

	public static $hasToChangePassword = false;

	public static $mySettings = array();

    /**
     * @var \Monolog\Logger
     */
	private static $errorLogger = null;

    /**
     * @var \Monolog\Logger
     */
    private static $infoLogger = null;

    /**
     * @var \Monolog\Logger
     */
    private static $activityLog = null;



    public static function boo() {
	    self::$globalsettings = new GlobalSettings();
	}

	public static function start() {
		self::$globalsettings = new GlobalSettings();
		self::$db = new mysql();
		self::$tpl = new tpl();
		self::$db->connect();
		self::$settings = new settings();
		self::$settings->init();

		
	}

    /**
     * @return mysql
     */
	public static function getDB() {
		return self::$db;
	}

    /**
     * @return tpl
     */
	public static function getTPL() {
		return self::$tpl;
	}
	
	/**
	 * 
	 */
	public static function checkDemoAccess() {
		if(DB::getGlobalSettings()->schulnummer != "9400") return true;		// Nicht Demo --> Zugriff
		else {
			if(DB::isLoggedIn() && DB::getUserID() == 1) return true;
			else return false;
		}
	}

	/**
	 * 
	 * @return settings
	 */
	public static function getSettings() {
		return self::$settings;
	}

	public static function initSession($sessionID) {
		$data = self::$db->query_first("SELECT * FROM sessions WHERE sessionID='".$sessionID."'");
		self::$session = new session($data);

		self::$userNetwork = $data['userNetwork'];

		if(self::isLoggedIn()) {
			self::$mySettings = DB::getDB()->query_first("SELECT * FROM user_settings WHERE userID='" . self::getUserID() . "'");
			if(!(self::$mySettings['userID'] > 0)) {
				self::$mySettings = DB::getDB()->query_first("SELECT * FROM user_settings WHERE userID='0'"); // Default Settings laden
			}
		}
	}

	/**
	 * 
	 * @return session
	 */
	public static function getSession() {
		return self::$session;
	}

	public static function isLoggedIn() {
		return (self::$session != null && self::$session->getData('userID') > 0 );
	}

	public static function getUserID() {
		if(self::$session != null && self::$session->getData("userID") > 0) return self::$session->getData("userID");
		else return 0;
	}

	public static function showError($message) {
		new errorPage($message);
	}

	/**
	 * 
	 * @return GlobalSettings
	 */
	public static function getGlobalSettings() {
		return self::$globalsettings;
	}

	public static function getVersion() {
		return '1.2.0';
	}

	/**
	 * Liest alle Netzwerke aus, die intern von SchuleIntern verwendet werden, und somit nicht für Synchronisationen zur Verfügung stehen.
	 * @return string[]
	 */
	public static function getInternalNetworks() {
		return array(
			"SCHULEINTERN",
			"SCHULEINTERN_SCHUELER",
			"SCHULEINTERN_LEHRER",
			"SCHULEINTERN_ELTERN"
		);
	}
	
	
	/**
	 * Überprüft, ob der DEBUG Modus an ist.
	 * @return boolean
	 */
	public static function isDebug() {
		return self::getGlobalSettings()->debugMode;
	}
	
	
	/**
	 * Ist die Installation eine Testversion?
     * @deprecated
	 * @return boolean
	 */
	public static function isTestversion() {
	    return false;
	}
	
	/**
	 * Liest den Stand der Daten aus der ASV aus.
	 * @return String natural Date des letzten Imports.
	 */
	public static function getAsvStand() {
		$stand = self::getSettings()->getValue("last-asv-import");
		if($stand != "") return $stand;
		return 'n/a';
	}
	
	/**
	 * Hat die Instanz eine Notenverwaltung? (Muss in den Einstellungen aktiviert werden.)
	 * @return boolean
	 */
	public static function hasNotenverwaltung() {
		return DB::getGlobalSettings()->hasNotenverwaltung;
	}
}



?>