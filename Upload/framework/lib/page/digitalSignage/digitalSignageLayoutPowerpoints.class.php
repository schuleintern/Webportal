<?php



class digitalSignageLayoutPowerpoints extends AbstractPage {
	
	public function __construct() {
		parent::__construct ( array (
			"Digitaler Schaukasten" 
		));
		
	}
	
	public function execute() {
		die("only Admin");
	}

	public static function hasSettings() {
		return false;
	}
	
	/**
	 * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
	 * @return array(String, String)
	 * array(
	 * 	   array(
	 * 		'name' => "Name der Einstellung (z.B. formblatt-isActive)",
	 *		'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
	 *      'titel' => "Titel der Beschreibung",
	 *      'text' => "Text der Beschreibung"
	 *     )
	 *     ,
	 *     .
	 *     .
	 *     .
	 *  )
	 */
	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Powerpoints';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminGroup() {
		return "Webportal_Digitaler_Schaukasten_Admin";
	}
	
	public static function displayAdministration($selfURL) {
	    
	    if($_REQUEST['action'] == 'upload') {
	        $newUpload = FileUpload::uploadPowerpoint('newFile','dsContent');
	        if($newUpload['result']) {
	            DB::getDB()->query("INSERT INTO schaukasten_powerpoint (powerpointName, uploadID, lastUpdate) values('" . DB::getDB()->escapeString($_REQUEST['pptName']) . "','" . $newUpload['uploadobject']->getID() . "', UNIX_TIMESTAMP())");
	            
	            header("Location: $selfURL&uploaded=1");
	            exit();
	        }
	        else {
	            header("Location: $selfURL&error=1");
	            exit();
	        }
	    }
	    
		$powerpoints = DB::getDB()->query("SELECT * FROM schaukasten_powerpoint");
		
		$pHTML = '';
		while($p = DB::getDB()->fetch_array($powerpoints)) {
		    
		    $uploadFile = FileUpload::getByID($p['uploadID']);
		    
		    if($uploadFile == null) {
		        continue;
		    }
		    
		    $lastUpdate = functions::makeDateFromTimestamp($p['lastUpdate']);
		    
		    if($_REQUEST['delete'] == $p['powerpointID']) {
		        DB::getDB()->query("DELETE FROM schaukasten_powerpoint WHERE powerpointID='" . intval($_REQUEST['delete']) . "'");
		        DB::getDB()->query("DELETE FROM schaukasten_inhalt WHERE schaukastenContent='PPT" . intval($_REQUEST['delete']) . "'");
		        
		        header("Location: $selfURL&deleted=1");
		        exit();
		    }
		    
		    if($_REQUEST['uploadFile']) {
		        $newUpload = FileUpload::uploadPowerpoint('newFile','dsContent');
		        if($newUpload['result']) {
		            DB::getDB()->query("UPDATE schaukasten_powerpoint SET uploadID='" . $newUpload['uploadobject']->getID() . "', lastUpdate=UNIX_TIMESTAMP() WHERE powerpointID='" . intval($_REQUEST['uploadFile']) . "'");

		        }
		        
		        header("Location: $selfURL");
		        exit();
		    }		    
		    
		    $pHTML .= "<tr><td>" . $p['powerpointName'] . "</td>";
		    
		    $pHTML .= "<td>" . $lastUpdate . "</td>";
		    
		    $pHTML .= "<td><a href=\"" . $uploadFile->getURLToFile(true) . "\"><i class=\"fa fa-download\"></i> Download</td>";
		    
		    $pHTML .= "<td><form action=\"$selfURL&uploadFile=" . $p['powerpointID'] . "\" enctype=\"multipart/form-data\" method=\"post\"><input type=\"file\" class=\"form-control\" name=\"newFile\"><button type=\"submit\" class=\"btn\"><i class=\"fa fa-upload\"></i> Neue Datei hochladen</button></form></td>";
		    
		    $pHTML .= "<td><form><button type=\"button\" onclick=\"confirmAction('Soll die Präsentation wirklich gelöscht werden?','$selfURL&delete=" . $p['powerpointID'] . "')\" class=\"btn btn-danger\"><i class=\"fa fa-trash\"></i> Powerpoint löschen</button></form></td>";
		    
		    $pHTML .= "</tr>";
		}
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("digitalSignage/ppt/index") . "\";");
		
		return $html;
	}
	
	public static function getAdminMenuGroup() {
		return 'Digitaler Schaukasten';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-television';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-photo';
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>