<?php



class digitalSignageWebsites extends AbstractPage {
	
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
		return 'Webseiten';
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
	            DB::getDB()->query("INSERT INTO schaukasten_website (websiteName, websiteURL,websiteLastUpdate, websiteRefreshSeconds) values(
                    '" . DB::getDB()->escapeString($_REQUEST['websiteName']) . "',
                    '" . DB::getDB()->escapeString($_REQUEST['websiteURL']) . "',
                    UNIX_TIMESTAMP(),
                    '" . DB::getDB()->escapeString($_REQUEST['websiteRefreshSeconds']) . "'
                )");
	            
	            header("Location: $selfURL&uploaded=1");
	            exit();

	    }
	    
	    if($_REQUEST['action'] == 'edit') {
	        DB::getDB()->query("UPDATE schaukasten_website 

                SET
                    websiteName = '" . DB::getDB()->escapeString($_REQUEST['websiteName']) . "',
                    websiteURL = '" . DB::getDB()->escapeString($_REQUEST['websiteURL']) . "',
                    websiteLastUpdate = UNIX_TIMESTAMP(),
                    websiteRefreshSeconds = '" . DB::getDB()->escapeString($_REQUEST['websiteRefreshSeconds']) . "'

                WHERE websiteID='" . DB::getDB()->escapeString($_REQUEST['websiteID']) . "'");

	        
	        header("Location: $selfURL&uploaded=1");
	        exit();
	        
	    }
	    
		$websites = DB::getDB()->query("SELECT * FROM schaukasten_website");
		
		$pHTML = '';
		while($p = DB::getDB()->fetch_array($powerpoints)) {
		    
		    if($_REQUEST['delete'] == $p['websiteID']) {
		        DB::getDB()->query("DELETE FROM schaukasten_website WHERE websiteID='" . intval($_REQUEST['delete']) . "'");
		        DB::getDB()->query("DELETE FROM schaukasten_inhalt WHERE schaukastenContent='WS" . intval($_REQUEST['delete']) . "'");
		        
		        header("Location: $selfURL&deleted=1");
		        exit();
		    }

		    
		    $pHTML .= "<tr><form action=\"$selfURL&action=edit&websiteID=" . $p['websiteID'] . "\" method=\"post\">";
		    
		    $pHTML .= "<td><input type=\"text\" name=\"websiteName\" class=\"form-control\" value=\"" . $p['websiteName'] . "\"></td>";
		    
		    $pHTML .= "<td><input type=\"text\" name=\"websiteURL\" class=\"form-control\" value=\"" . $p['websiteURL'] . "\"></td>";
		    
		    $pHTML .= "<td><input type=\"text\" name=\"websiteRefreshSeconds\" class=\"form-control\" value=\"" . $p['websiteRefreshSeconds'] . "\"></td>";
		    
		    
		    $pHTML .= "<td><a href=\"" . $p['websiteURL'] . "\" target=\"_blank\"><i class=\"fa fa-anchor\"></i> Öffnen</td>";
		    		    
		    $pHTML .= "<td>
            <button type=\"submit\" class=\"btn btn-primary\"><i class=\"fa fa-save\"></i> Speichern</button>

<button type=\"button\" onclick=\"confirmAction('Soll die Webseite wirklich gelöscht werden?','$selfURL&delete=" . $p['websiteID'] . "')\" class=\"btn btn-danger\"><i class=\"fa fa-trash\"></i> Website löschen</button></form></td>";
		    
		    $pHTML .= "</tr>";
		}
		
		$html = "";
		
		eval("\$html = \"" . DB::getTPL()->get("digitalSignage/websites/index") . "\";");
		
		return $html;
	}
	
	public static function getAdminMenuGroup() {
		return 'Digitaler Schaukasten';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-television';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-anchor';
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>