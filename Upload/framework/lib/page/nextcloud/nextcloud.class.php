<?php



class nextcloud extends AbstractPage {
	
	public function __construct() {
		parent::__construct(array("Nextcloud"));
		
		$this->checkLogin();
	}
	
	public function execute() {

	}
	
	public static function hasAdmin() {
	    return true;
	}
	
	public static function displayAdministration($selfURL) {
	    return "";	    
	}

	public static function getAdminMenuGroup() {
	    return "Nextcloud";
	}
	
	public static function getAdminMenuGroupIcon() {
	    return 'fa fa-cloud';
	}
	
	public static function getAdminMenuIcon() {
	    return 'fa fa-cloud';
	}
	
	public static function notifyUserDeleted($userID) {
		// Nichts
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function siteIsAlwaysActive() {
	    return DB::getGlobalSettings()->enableNextCloud;
	}
	
	public static function getAdminGroup() {
	    return 'Webportal_NextCloud_Admin';
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
	    $klassen = klasse::getAllKlassen();

	    $settings = [];
	    
	    $settings[] = [
	        'titel' => "<u>Bitte beachten!</u> Änderungen auf dieser Seite werden erst in ca. 1 bis 2 Stunden aktiv.",
	        'typ' => 'TRENNER'
	    ];
	    
	    $settings[] = [
	        'titel' => "Kennungen anlegen",
	        'typ' => 'TRENNER',
	        'text' => 'Für diese Benutzergruppen werden Kennungen angelegt. Die Passwöter der Kennungen sind die selben wie hier im Portal. Damit diese funktionieren, muss der Benutzer sich einmal im Portal angemeldet haben.'
	    ];
	    
	    $settings[] =
	    [
	        'name' => 'nextcloud-schueler',
	        'typ' =>'BOOLEAN',
	        'titel' => 'Kennungen für die Schüler erzeugen?'
	    ];
	    
	    $settings[] =
	    [
	        'name' => 'nextcloud-lehrer',
	        'typ' =>'BOOLEAN',
	        'titel' => 'Kennungen für die Lehrer erzeugen?'
	    ];
	    
	    $settings[] =
	    [
	        'name' => 'nextcloud-eltern',
	        'typ' =>'BOOLEAN',
	        'titel' => 'Kennungen für die Eltern erzeugen?'
	    ];
	    
	    $settings[] =
	    [
	        'name' => 'nextcloud-other-users',
	        'typ' =>'BOOLEAN',
	        'titel' => 'Kennungen für die sonstigen Benutzer erzeugen?'
	    ];
	    
	    $settings [] = [
	        'titel' => 'Lehrer Tauschordner',
	        'typ' => 'TRENNER'
	    ];
	    
	    $settings[] =
	    [
	        'name' => 'nextcloud-lehrer-tausch',
	        'typ' =>'BOOLEAN',
	        'titel' => 'Tauschordner für Lehrer erzeugen?'
	    ];	    
	    	    
	    $settings[] =
	    [
	        'name' => 'nextcloud-lehrer-fachordner',
	        'typ' =>'BOOLEAN',
	        'titel' => 'Fächer - Tauschordner für Lehrer der Fächer erzeugen?'
	    ];	
	    
	    $settings [] = [
	        'titel' => 'Klassenbezogene Tauschordner',
	        'typ' => 'TRENNER'
	    ];
	    
	    for($i = 0; $i < sizeof($klassen); $i++) {
	        
	        $settings[] = [
	            'titel' => "Klasse " . $klassen[$i]->getKlassenName(),
	            'typ' => 'TRENNER'
	        ];

	        $settings[] = [
	            'name' => 'nextcloud-klasse-material-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'BOOLEAN',
	            'titel' => 'Materialordner für Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Lesezugriff für Schüler der Klasse, Schreibzugriff für alle Lehrer"
	        ];
	        
	        $settings[] = [
	            'name' => 'nextcloud-klasse-tausch-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'BOOLEAN',
	            'titel' => 'Tauschordner für Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Schzreibzugriff für Schüler der Klasse, Schreibzugriff für alle Lehrer"
	        ];
	        
	        $settings[] = [
	            'name' => 'nextcloud-klasse-lehrer-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'BOOLEAN',
	            'titel' => 'Lehrertauschordner für Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Schreibzugriff für alle Lehrer"
	        ];
	        
	        $settings[] = [
	            'name' => 'nextcloud-klasse-eltern-material-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'BOOLEAN',
	            'titel' => 'Materialordner für Eltern der Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Lesezugriff für Eltern der Klasse, Schreibzugriff für alle Lehrer"
	        ];
	        
	        $settings[] = [
	            'name' => 'nextcloud-klasse-eltern-tausch-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'BOOLEAN',
	            'titel' => 'Tauschordner für Eltern der Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Schreibzugriff für Eltern der Klasse, Schreibzugriff für alle Lehrer"
	        ];
	        
	        /**
	        $unterrichte = SchuelerUnterricht::getUnterrichtForKlasse($klassen[$i]);
	        
	        $options = [];
	        
	        
	        for($u = 0; $u < sizeof($unterrichte); $u++) {	            
	            $options[] = [
	                'key' => $unterrichte[$u]->getBezeichnung(),
	                'name' => $unterrichte[$u]->getBezeichnung()
	            ];
	        }
	        
	        $settings[] = [
	            'name' => 'nextcloud-klasse-unterricht-tausch-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'SELECT',
	            'multiple' => true,
	            'options' => $options,
	            'titel' => 'Tauschordner für folgende Unterricht der Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Schreibzugriff für Schüler der Klasse, Schreibzugriff für den Lehrer des Unterrichts"
	        ];
	        
	        $settings[] = [
	            'name' => 'nextcloud-klasse-unterricht-material-' . $klassen[$i]->getKlassenName(),
	            'typ' =>'SELECT',
	            'multiple' => true,
	            'options' => $options,
	            'titel' => 'Materialordner für folgende Unterricht der Klasse ' . $klassen[$i]->getKlassenName() . ' erzeugen',
	            'text' => "Lesezugriff für Schüler der Klasse, Schreibzugriff für den Lehrer des Unterrichts"
	        ]; **/
	        
	    }
	    
	    

		    
		return $settings;
	}
	
	
	
	
	public static function getSiteDisplayName() {
		return 'NextCloud Dateiaustausch';
	}

	
	public static function onlyForSchool() {
		return [];
	}
	
	
	public static function updatePasswordForCurrentUser($newPassword, $user = null) {
	    if(DB::getGlobalSettings()->enableNextCloud) {
	        $setPW = false;
	        
	        if($user == null) {
	            if(DB::getSession() == null) return null;
	            
	            $userobject = DB::getSession()->getUser();
	        }
	        
	        
	        
	        if($userobject == null) return;
	        
	        if($userobject->isTeacher() && DB::getSettings()->getBoolean("nextcloud-lehrer")) {
	            $setPW = true;
	        }
	        
	        if($userobject->isEltern() && DB::getSettings()->getBoolean("nextcloud-eltern")) {
	            $setPW = true;
	        }
	        
	        if($userobject->isPupil() && DB::getSettings()->getBoolean("nextcloud-schueler")) {
	            $setPW = true;
	        }
	        
	        if($setPW && $newPassword != "") {
	            NextCloudApi::updatePassword($userobject->getUserName(), $newPassword);
	        }
	        
	    }
	}
	

}


?>