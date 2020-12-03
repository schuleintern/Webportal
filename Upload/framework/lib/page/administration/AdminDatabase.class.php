<?php

class AdminDatabase extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		die();	
	}

	public function execute() {
	    new errorPage();
	}
	
	private function index() {
	}
	
	public static function hasSettings() {
		return false;
	}
	
	public static function getSettingsDescription() {
		return [];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Datenbank';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Database_Admin';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-table';
	}

    public static function getAdminMenuGroupIcon() {
        return 'fa fa-database';
    }

    public static function getAdminMenuGroup() {
        return 'Datenbank';
    }

	public static function siteIsAlwaysActive() {
		return true;
	}
	/**
	 * Überprüft, ob die Seite eine Administration hat.
	 * @return boolean
	 */
	public static function hasAdmin() {
		return true;
	}

	public static function displayAdministration($selfURL) {

		$html = '';



		$db_details = DB::getDB()->query_first('SELECT 
			TABLE_SCHEMA AS DB_Name, 
			count(TABLE_SCHEMA) AS Total_Tables, 
			SUM(TABLE_ROWS) AS Total_Tables_Row, 
			ROUND(sum(data_length + index_length)/1024/1024) AS "DB Size (MB)",
			ROUND(sum( data_free )/ 1024 / 1024) AS "Free Space (MB)"
			FROM information_schema.TABLES 
			WHERE TABLE_SCHEMA = "'.DB::getGlobalSettings()->dbSettigns['database'].'"
			GROUP BY TABLE_SCHEMA ;');


		$db_details_json = json_encode($db_details);
		
		// echo '<pre>';
		// print_r($db_details);
		// echo '</pre>';
		// echo '<hr>';


		$sql = "SHOW TABLE STATUS";
		$result = DB::getDB()->query($sql);

		if (!$result) {
				echo "DB Fehler, konnte Tabellen nicht auflisten\n";
				exit;
		}

		$tables = [];
		while($row = DB::getDB()->fetch_array($result)) {

				$childs = [];
				$q = DB::getDB()->query('DESCRIBE '.$row[0]);
				while($fields = DB::getDB()->fetch_array($q)) {
					//echo "{$fields['Field']} - {$fields['Type']}<br>";
					$childs[] = $fields;
				}
				$row['child'] = $childs;

				if ($row['Data_length']) {
					$row['Data_length'] = FILE::formatBytes($row['Data_length']);
				}
				$tables[] = $row;
				

		}

		// echo '<pre>';
		// print_r($tables);
		// echo '</pre>';

		$sql_structure = json_encode( nl2br( DB::getDbStructure() ) );

		$tables_json = json_encode($tables);

		eval("\$html = \"" . DB::getTPL()->get("administration/database/list") . "\";");

		return $html;
	}
 
		
}


?>