<?php

class AdminModules extends AbstractPage {

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
		return 'Modules';
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

		$pathModul    = '../modules/';
		$pathStore    = '../modules/store/';

		if ($_REQUEST['install']) {
			echo 'Install';

			$zip = new ZipArchive;
			if ($zip->open($pathStore.$_REQUEST['install'].'.zip') === TRUE) {
					$zip->extractTo($pathModul);
					$zip->close();
					echo ' - entpackt';

					// Get Modul JSON
					if ( file_exists($pathModul.$_REQUEST['install'].'/modul.json') ) {

						$modulJSON = json_decode( file_get_contents($pathModul.$_REQUEST['install'].'/modul.json') );

						if ( !$modulJSON->name || !$modulJSON->version || !$modulJSON->classname || !$modulJSON->folder  ) {
							echo 'Error: Missing Json Data';
							exit;
						}
						if ( !$modulJSON->settings ) {
							$modulJSON->settings = array();
						}
						$modulJSON->settings = json_encode($modulJSON->settings, JSON_HEX_QUOT );

						// Install Module DB
						if ( file_exists($pathModul.$_REQUEST['install'].'/install/database.sql') ) {
							echo ' - db!';

							$sql = file_get_contents($pathModul.$_REQUEST['install'].'/install/database.sql');
							$sqlCommands = explode(';', $sql);

							foreach($sqlCommands as $foo) {
								$foo = trim($foo);
								if ($foo) {
									DB::getDB()->query($foo);
								}
								
							}
							

							//echo '<br>'.$sql;
						}

						// Activate Module
						DB::getDB()->query("INSERT INTO `modules` (
							`name`,
							`active`,
							`folder`,
							`version`,
							`settings`
							) VALUES (
								'".$modulJSON->name."',
								1,
								'".$modulJSON->folder."',
								".$modulJSON->version.",
								'".$modulJSON->settings."'
							); ");
						
						echo ' - done!';
					}


					

			} else {
					echo 'Fehler mit dem Zip';
			}


		}


		
		$files = scandir($pathStore);
		$files = array_diff(scandir($pathStore), array('.', '..'));

		//dump($files);
		$modules = array();
		$result = DB::getDB()->query('SELECT * FROM `modules` ');
		while($row = DB::getDB()->fetch_array($result)) {
			$modules[] = $row;
		}

		//dump($modules);
		$html .= '<ul>';
		foreach($files as $file) {
			$file = str_replace('.zip','',$file);

			$found = false;
			foreach ($modules as $modul) {
				if ($modul['folder'] == $file) {
					$found = '<li>'.$modul['name'].' (Installed)</li>';
				}
			}

			if (!$found) {
				$html .= '<li>'.$file.' <a href="index.php?page=administrationmodule&module=AdminModules&install='.$file.'">Install</a></li>';
			} else {
				$html .= $found;
			}
		}
		
		eval("\$html = \"" . DB::getTPL()->get("administration/modules/list") . "\";");

		return $html;
	}
 
		
}


?>