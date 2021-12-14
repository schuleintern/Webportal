<?php



class DB {
	private static $db;
	private static $tpl;
	private static $cache;
	
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
		self::$cache = new Cache();

		
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
     * @return Cache
     */
	public static function getCache() {
	    return self::$cache;
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
		return '1.3.3';
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


    /**
     * @return false|string
     * @throws Exception
     */
	public static function getDbStructure() {
	    $settings = [];
        $settings['no-data'] = true;
        $settings['add-drop-table'] = false;

        $connect = "mysql:host=" . DB::getGlobalSettings()->dbSettigns['host'] . ":" . DB::getGlobalSettings()->dbSettigns['port'] . ";dbname=" . DB::getGlobalSettings()->dbSettigns['database'];

        $dump = new \Ifsnop\Mysqldump\Mysqldump($connect, DB::getGlobalSettings()->dbSettigns['user'], DB::getGlobalSettings()->dbSettigns['password'], $settings);
        $dump->start("../data/temp/dbstruct.sql");

        return file_get_contents("../data/temp/dbstruct.sql");
    }

    public static function getDdStructureOld() {
		$queryTables = self::$db->query('SHOW TABLES');
		while($row = $queryTables->fetch_row()) {
			$target_tables[] = $row[0];
		}
		$content = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\r\n/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\r\n/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\r\n\r\n";
		foreach($target_tables as $table){
			if (empty($table)){
				continue;
			} 
			$res = self::$db->query(" SHOW CREATE TABLE `".$table."` ");
			$TableMLine = $res->fetch_row(); 
			$TableMLine[1] = str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
			$content .= $TableMLine[1]."; \r\n \r\n";
		}
		$content .= "\r\n\r\n/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\r\n/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\r\n/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\r\n";
		return $content;
	}


}



?>