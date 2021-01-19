<?php



class mebis extends AbstractPage {
	
	public function __construct() {
		parent::__construct(array("mebis Kennungen"));
		
		$this->checkLogin();
		
		if(!(DB::getSession()->isTeacher() || DB::getSession()->isPupil() || DB::getSession()->isAdmin())) {
			new errorPage("Dieser Bereich ist nur für Lehrer und Schüler.");
			exit(0);
		}
	}

	public function execute() {
		// Admin?

		$vorname = "";
		$nachname =  "";
		
		if(DB::getSession()->isTeacher()) {
			$vorname = DB::getSession()->getTeacherObject()->getRufname();
			$nachname = DB::getSession()->getTeacherObject()->getName();
		}
		
		else if(DB::getSession()->isPupil()) {
			$vorname = DB::getSession()->getPupilObject()->getRufname();
			$nachname = DB::getSession()->getPupilObject()->getName();
		}
		

		// Suche den eigenen Account
		$account = DB::getDB()->query_first("SELECT * FROM mebis_accounts WHERE 
				mebisAccountVorname LIKE '" . DB::getDB()->escapeString($vorname) . "'
				AND
				mebisAccountNachname LIKE '" . DB::getDB()->escapeString($nachname) . "'	
		");
		
		if($account['mebisAccountID'] > 0) {
			eval("echo(\"" . DB::getTPL()->get("mebis/index") . "\");");
		}
		else {
			eval("echo(\"" . DB::getTPL()->get("mebis/noAccount") . "\");");
		}
		//

		
	}
	
	public static function hasAdmin() {
	    return true;
	}
	
	public static function displayAdministration($selfURL) {
	    if($_REQUEST['action'] == 'upload') {
    	    if(is_file($_FILES['csvdatei']['tmp_name'])) {
    	        
    	        self::importCSVFile($_FILES['csvdatei']['tmp_name']);
    	        
    	        $success = "Es wurden " . sizeof(file($_FILES['csvdatei']['tmp_name'])) . " Accounts gespeichert / aktualisiert.";
	        
    	    }
    	    
    	    if(is_file($_FILES['csvdateischueler']['tmp_name'])) {
    	        
    	        self::importCSVFile($_FILES['csvdateischueler']['tmp_name']);
    	        
    	        $success .= "<br />Es wurden " . sizeof(file($_FILES['csvdateischueler']['tmp_name'])) . " Accounts (Schüler) gespeichert / aktualisiert.";
    	        
    	    }
	    }
	    
	    if($_REQUEST['action'] == 'deleteAll') {
	        $successDelete = true;
	        
	        DB::getDB()->query("TRUNCATE mebis_accounts");
	    }
	    
	    // Kennungen anzeigen
	    
	    $mebisKennungenHTML = "";
	    
	    $mebisSQL = DB::getDB()->query("SELECT * FROM mebis_accounts ORDER BY mebisAccountBenutzername ASC");
	    
	    while($m = DB::getDB()->fetch_array($mebisSQL)) {
	        $mebisKennungenHTML .= "<tr>";
	        
	        $mebisKennungenHTML .= "<td>" . $m['mebisAccountBenutzername'] . "</td>";
	        $mebisKennungenHTML .= "<td>" . $m['mebisAccountVorname'] . "</td>";
	        $mebisKennungenHTML .= "<td>" . $m['mebisAccountNachname'] . "</td>";
	        $mebisKennungenHTML .= "<td>" . $m['mebisAccountPasswort'] . "</td>";
	        
	        $mebisKennungenHTML .= "</tr>";	        
	    }
	    
	    	    
	    eval("\$admin = \"" . DB::getTPL()->get("mebis/upload") . "\";");
	    
	    return $admin;
	    
	}
	
	private static function importCSVFile($tempName) {
	    $data = file($tempName);
	    	    
	    for($i = 0; $i < sizeof($data); $i++) {
	        $line = explode(";",str_replace("\r","",str_replace("\n","",str_replace("\"","",$data[$i]))));
	        
	        $vorhanden = DB::getDB()->query_first("SELECT * FROM mebis_accounts WHERE mebisAccountBenutzername LIKE '" . DB::getDB()->escapeString(($line[2])) . "'");
	        
	        if($vorhanden['mebisAccountID'] > 0) {
	            DB::getDB()->query("UPDATE mebis_accounts SET
    						mebisAccountVorname = '" . DB::getDB()->escapeString(($line[0])) . "',
    						mebisAccountNachname = '" . DB::getDB()->escapeString(($line[1])) . "',
    						mebisAccountBenutzername = '" . DB::getDB()->escapeString(($line[2])) . "',
    						mebisAccountPasswort = '" . DB::getDB()->escapeString(($line[3])) . "'
	                
    							WHERE mebisAccountID='" . $vorhanden['mebisAccountID'] . "'");
	        }
	        
	        else DB::getDB()->query("INSERT INTO mebis_accounts (mebisAccountVorname,mebisAccountNachname,mebisAccountBenutzername,mebisAccountPasswort)
    						values(
    							'" . DB::getDB()->escapeString(($line[0])) . "',
    							'" . DB::getDB()->escapeString(($line[1])) . "',
    							'" . DB::getDB()->escapeString(($line[2])) . "',
    							'" . DB::getDB()->escapeString(($line[3])) . "')");
	        
	    }
	    
	}
	
	public static function getAdminMenuGroup() {
	    return "Kleinere Module";
	}
	
	public static function getAdminMenuGroupIcon() {
	    return 'fa fa-file';
	}
	
	public static function getAdminMenuIcon() {
	    return 'fa fa-compass';
	}
	
	public static function notifyUserDeleted($userID) {
		// Nichts
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getAdminGroup() {
	    return 'Webportal_Mebis_Admin';
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
		return [
		    [
		        'name' => 'mebis-schueler',
		        'typ' =>'BOOLEAN',
		        'titel' => 'Kennungsanzeige für Schüler aktivieren?'
		    ]
		];
	}
	
	
	
	
	public static function getSiteDisplayName() {
		return 'Mebis Kennungen';
	}

	
	public static function onlyForSchool() {
		return [];
	}
	
	

}


?>