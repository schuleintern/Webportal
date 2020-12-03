<?php

class AdminDatabaseUpdate extends AbstractPage {

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
		return 'Datenbank Update';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Database_Admin';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-download';
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
        $zielStruktur = file_get_contents("../framework/database.sql");

        $aktuell = DB::getDbStructure();

        $dbStruct = new dbStruct();

        $sqlUpdates = $dbStruct->getUpdates( $aktuell, $zielStruktur);

        $needUpdates = sizeof($sqlUpdates) > 0;

        if($_REQUEST['updateAll'] > 0) {
            for($i = 0; $i < sizeof($sqlUpdates); $i++) DB::getDB()->query($sqlUpdates[$i],1);
            header("Location: $selfURL");
            exit();
        }


        if($needUpdates && $_REQUEST['updateItem'] > -1) {
            if(isset($sqlUpdates[$_REQUEST['updateItem']])) {
                DB::getDB()->query($sqlUpdates[$_REQUEST['updateItem']],1);
                header("Location: $selfURL");
                exit();
            }
        }

        $updateListHTML = "";

        for($i = 0; $i < sizeof($sqlUpdates); $i++) {
            $updateListHTML .= "<tr><td>" . $sqlUpdates[$i] . "</td>";
            $updateListHTML .= "<td><a href='" . $selfURL . "&updateItem=" . $i . "' onclick='this.disabled=true' class='btn btn-block btn-primary'><i class='fa fa-upload'></i> Aktion ausführen</a></td></tr>";
        }



        $html = "";

        eval("\$html = \"" . DB::getTPL()->get("administration/database/update") . "\";");



        return $html;
    }
 
		
}


?>