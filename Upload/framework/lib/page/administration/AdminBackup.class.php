<?php

class AdminBackup extends AbstractPage {

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
		return 'Backup';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Backup_Admin';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-download';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-cogs';
	}
	
	public static function getAdminMenuGroup() {
		return 'System';
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

			if($_REQUEST['task'] == "make") {
				
				include('./../cli/MakeBackup.php');

				$backup = new MakeBackup;
				$backup->execute( $_REQUEST['action'] );

				exit;
			}

			if($_REQUEST['task'] == "get") {
				$file = urldecode( $_REQUEST['path'] );

				set_time_limit(0);
				if (file_exists($file)) {

					ini_set(‘memory_limit’, ‘300M’);
					
					header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="'.basename($file).'"');
          header('Content-Transfer-Encoding: binary');
          header('Expires: 0');
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Pragma: public');
          header('Content-Length: ' . filesize($file));
          ob_clean();
          flush();
					readfile($file);
					

					exit;

				} else {
					echo 'no file';
				}
				flush();
				exit;
			}

			$html = '';

			$directory = '../data/backup';
			$files_json = json_encode(FILE::getFilesInFolder($directory, true, 'zip'));

			eval("\$html = \"" . DB::getTPL()->get("administration/backup/list") . "\";");

      return $html;
    }
 
		
}


?>