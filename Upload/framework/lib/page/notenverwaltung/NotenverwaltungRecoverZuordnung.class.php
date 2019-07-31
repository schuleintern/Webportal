<?php

class NotenverwaltungRecoverZuordnung extends AbstractPage {

	
	public function __construct() {
		
		if(!DB::getGlobalSettings()->hasNotenverwaltung) {
			die("Notenverwaltung nicht lizenziert.");
		}

		
		parent::__construct(['Notenverwaltung - Noten reparieren'],false,false,true);
		
		
		
		if(!DB::getSession()->isTeacher()) {
			new errorPage();
		}

	}

	public function execute() {

	    $arbeiten = NoteArbeit::getByKuerzel(DB::getSession()->getTeacherObject()->getKuerzel());
	   
	    
	    if($_GET['save'] > 0) {
	        for($i = 0; $i < sizeof($arbeiten); $i++) {
	            $unterrichtID = $_POST[$arbeiten[$i]->getID() . "_unterrichtID"];
	            
	            $unterricht = SchuelerUnterricht::getByID($unterrichtID);
	            
	            
	            DB::getDB()->query("UPDATE noten_arbeiten SET arbeitFachKurzform='" . $unterricht->getFach()->getKurzform() . "', arbeitUnterrichtName='" . $unterricht->getBezeichnung() . "' WHERE arbeitID='" . $arbeiten[$i]->getID() . "'");
	        }
	        
	        header("Location: index.php?page=NotenverwaltungRecoverZuordnung&saved=1");
	        exit(0);
	    }
	    
	    
	    
	    $myUnterricht = SchuelerUnterricht::getUnterrichtForLehrer(DB::getSession()->getTeacherObject());
	    
	    $myRealUnterricht = [];
	    
	    $unterrichtShown = [];
	    
	    for($i = 0; $i < sizeof($myUnterricht); $i++) {
	        
	        if(!in_array($myUnterricht[$i]->getID(), $unterrichtShown)) {
	            
	            $unterrichtShown[] = $myUnterricht[$i]->getID();
	            
	            $koppelUnterricht = $myUnterricht[$i]->getKoppelUnterricht();
	            for($k = 0; $k < sizeof($koppelUnterricht); $k++) $unterrichtShown[] = $koppelUnterricht[$k]->getID();
	            
	            
	            $schueler = $myUnterricht[$i]->getSchueler();
	            if(sizeof($schueler) > 0) {
	                $myRealUnterricht[] = $myUnterricht[$i];
	            }
	        }
	    }
	    
	    
    
	    $myUnterricht = $myRealUnterricht;
	    
	    
	    
	    $table = "";
	    for($i = 0; $i < sizeof($arbeiten); $i++) {
	        $table .= "<tr><td>" . $arbeiten[$i]->getName() . "<br />";
	        
	        $table .= $arbeiten[$i]->hasDatum() ? $arbeiten[$i]->getDatumAsNaturalDate() : "Ohne Datum";
	        $table .= "<br />";
	        $table .= $arbeiten[$i]->getBereich();
	        
	        $table .= "</td><td><table class=\"table table-bordered\"><tr><th>Name</th><th>Note</th></tr>";
	        
	        $noten = $arbeiten[$i]->getNoten();
	        
	        for($n = 0; $n < sizeof($noten); $n++) {
	            $table .= "<tr><td>" . $noten[$n]->getSchueler()->getCompleteSchuelerName() . " (Klasse " . $noten[$n]->getSchueler()->getKlasse() . ")</td>";
	            $table .= "<td>" . $noten[$n]->getDisplayWert() . "</td></tr>";
	        }
	        
	        $table .= "</table></td><td>";
	        
	        
	        $table .= "<select name=\"" . $arbeiten[$i]->getID() . "_unterrichtID\" class=\"form-control\">";
	        
	        $unt = SchuelerUnterricht::getByFachAndName($arbeiten[$i]->getFach(), $arbeiten[$i]->getUnterrichtName());
	        
	        $table .= $this->getMyUnterrichtSelect($myUnterricht, ($unt != null ? $unt->getID() : 0));
	        
	        $table .= "</select></td></tr>";
	        
	    }
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("notenverwaltung/recover/index") . "\");");
	}
	
	/**
	 * 
	 * @param SchuelerUnterricht[] $myUnterricht
	 * @param int $selectedID
	 */
	private function getMyUnterrichtSelect($myUnterricht, $selectedID) {
	    
	    $unterrichtSelect = "";
	    for($i = 0; $i < sizeof($myUnterricht); $i++) {
	        $unterrichtSelect .= "<option value=\"" . $myUnterricht[$i]->getID() . "\"" . (($selectedID == $myUnterricht[$i]->getID()) ? "selected=\"selected\"" : "") . ">" . $myUnterricht[$i]->getAllKlassenAsList() . ": " . $myUnterricht[$i]->getFach()->getKurzform() . "</option>";
	    }
	    
	    return $unterrichtSelect;
	}

	public static function hasSettings() {
		return false;
	}

	public static function getSettingsDescription() {
		return [];
	}
	
	public static function getSiteDisplayName() {
		return 'Notenverwaltung - Startseite';
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Notenverwaltung_Admin';
	}
	
	public static function need2Factor() {
	    return TwoFactor::is2FAActive() && TwoFactor::force2FAForNoten();
	}

}


?>