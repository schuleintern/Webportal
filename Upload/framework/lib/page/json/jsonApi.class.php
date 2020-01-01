<?php


/**
 * @deprecated
 */
class jsonApi extends AbstractPage {
	
	public function __construct() {
		parent::__construct([]);
		
		$this->checkLogin();
	}

	public function execute() {
	    
	    header("Content-type: text/json");
	    
	    switch($_REQUEST['mode']) {
	        case 'getPupilNamesForGrade':
	            if(DB::getSession()->isTeacher()) $this->pupilNames($_REQUEST['grade']);
	            else echo(json_encode([]));
	        break;
	    }
		
	}
	
	private function pupilNames($grade) {
	   
	    
	    $result = [
	        'results' => [],
	        'pagination' => [
	            'more' => false
	        ]
	    ];
	    
	    $stundenplan = stundenplandata::getCurrentStundenplan();
	    
	    if($stundenplan != null) {
	        $klassen = klasse::getByStundenplanKlassen([$grade]);
	        
	        /**
	         * 
	         * @var schueler[] $schueler
	         */
	        $schueler = [];
	        
	        for($k = 0; $k < sizeof($klassen); $k++) {
	            $schuelers = $klassen[$k]->getSchueler(true);
	            
	            for($s = 0; $s < sizeof($schuelers); $s++) {
	                $found = false;
	                
	                for($u = 0; $u < sizeof($schueler); $u++) {
	                    if($schueler[$u]->getAsvID() == $schuelers[$s]->getAsvID()) $found = true;
	                }
	                
	                if(!$found) {
	                    $schueler[] = $schuelers[$s];
	                }
	            }
	        }
	    }
	    
	    $resultSchueler = [];
	    
	    $search = strtolower($_REQUEST['q']);
	    
	    if($_REQUEST['q'] != "") {
	        // Suche
	        for($s = 0; $s < sizeof($schueler); $s++) {
	            if(strpos(strtolower($schueler[$s]->getVornamen()), $search) !== false) {
	                $resultSchueler[] = $schueler[$s];
	            }
	            elseif(strpos(strtolower($schueler[$s]->getName()), $search) !== false) {
	                $resultSchueler[] = $schueler[$s];
	            }
	        }
	    }
	    else {
	        $resultSchueler = $schueler;
	    }
	    
	    
	    for($s = 0; $s < sizeof($resultSchueler); $s++) {
	        $result['results'][] = [
	          'id' => $resultSchueler[$s]->getAsvID(),
	          'text' => $resultSchueler[$s]->getCompleteSchuelerName()
	        ];
	    }
	    
	    echo(json_encode($result));
	    exit(0);
	}
	
	public static function hasAdmin() {
	    return false;
	}

	public static function hasSettings() {
		return false;
	}

	
	public static function siteIsAlWaysActive() {
	    return true;
	}
	
	public static function onlyForSchool() {
		return [];
	}
	
	public static function getSiteDisplayName() {
	    return "JSON Api";
	}
	

}


?>