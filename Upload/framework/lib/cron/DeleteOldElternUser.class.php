<?php

class DeleteOldElternUser extends AbstractCron {

	private $isSuccess = true;
	private $listDeleted = "";
	private $listCreated = "";

	private $cronResult = ['success' => true, 'resultText' => ""];

	
	public function __construct() {
	}

	public function execute() {

	    if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
	        $alleELtern = user::getAllEltern();

	        $aktionen = [];

	        for($i = 0; $i < sizeof($alleELtern); $i++) {
	            if($alleELtern[$i]->isEltern()) {
	                $elternObjekt = $alleELtern[$i]->getElternObject();
	                if($elternObjekt != null) {
	                    $kinder = $elternObjekt->getRawKinderASVIDs();

	                    for($k = 0; $k < sizeof($kinder); $k++) {

	                        $delete = false;

	                        $schueler = schueler::getByAsvID($kinder[$k]);

	                        if($schueler == null) {
	                            $delete = true;
                            }

	                        if($schueler->isAusgetreten()) $delete = true;

	                        if($delete) {
	                            $aktionen[] =  "Kind " . $kinder[$k] . " aus Benutzer " . $alleELtern[$i]->getUserName() . " löschen.";
                            }
                        }


	                }
                }
            }

	        $this->cronResult['resultText'] = "Aktionen, die durchgeführt werden: \n" . implode("\n", $aktionen);

	    }
	}
	
	public function getName() {
		return "Eltern Benutzer löschen, die nicht mehr eixtsieren";
	}
	
	public function getDescription() {
		return "Löscht alle Elternbenutzerzuordnungen zu Kindern, die nicht mehr vorhanden sind. Ist es die letzte Zuordnung zu einem Kind, wird der Account komplett gelöscht.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
	    if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
	    	return $this->cronResult;
	    }
	    else {
	    	return ['success' => true, 'resultText' => 'Keine Aktion, da ASV Mailadressen verwendet werden.'];
	    	
	    }
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 7200;		// Einmal alle zwei Stunden ausführen
	}
}



?>