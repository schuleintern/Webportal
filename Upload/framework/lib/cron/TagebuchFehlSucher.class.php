<?php 


/**
 * Sendet E-Mailnachrichten
 * @author Christian
 *
 */

class TagebuchFehlSucher extends AbstractCron {

	private $result = null;
	
	private $lastDayCron = '';
	
	private $daysPerCron = 10;
	
	public function __construct() {
		$lastDay = DB::getSettings()->getValue('cron-tagebuch-fehl-sucher-last-day');
		
		if($lastDay == '') {
		    // Erster Schultag
		    
		    
		    $jahr = date("Y");
		    $monat = date("m");
		    
		    if($monat < 8) {
		        $lastDay = "01.09." . ($jahr-1);
		    }
		    else {
		        $lastDay = "01.09." . $jahr;
		    }
		    
		    $lastDay = DateFunctions::getMySQLDateFromNaturalDate($lastDay);
		    
		}
		
		$this->lastDayCron = $lastDay;
	}
	
	public function execute() {
		if(AbstractPage::isActive('klassentagebuch')) {
		    
		    
		    $currentDay = $this->lastDayCron;
		    
		    if(DateFunctions::isSQLDateTodayOrLater($currentDay)) {
		        
		        DB::getSettings()->setValue('cron-tagebuch-fehl-sucher-last-day', DateFunctions::getTodayAsSQLDate());
		        
		        $this->result = 0;
		        
		        return;
		    }
		    
		    
		    
		    $missingEntries = [];
		    
		    
		    for($i = 0; $i < $this->daysPerCron; $i++) {
		        if(DateFunctions::isSQLDateBeforeToday($currentDay) && !Ferien::isFerien($currentDay) && !DateFunctions::isSQLDateWeekEnd($currentDay)) {
		            
		            $stundenplan = stundenplandata::getStundenplanAtDate($currentDay, true);
		            
		            // die($currentDay . " - " . $stundenplan->getName() . "(" . $stundenplan->getID() . ")");
		            		            
		            $weekday = DateFunctions::getWeekDayFromSQLDateISO($currentDay)-1;    // ISO Tag Minus 1 für Array
		            
		            if($stundenplan != null) {
		                $klassen = $stundenplan->getAll('grade');
		                
		                
		                for($g = 0; $g < sizeof($klassen); $g++) {
		                    
		                    $stundenAmTag = $stundenplan->getPlan(['gradeStrict', $klassen[$g]]);
	                    
		                    
		                    $stundenAmTag = $stundenAmTag[$weekday];
		                    
		                    $eintraegeAmTag = TagebuchKlasseEntry::getAllForDateAndGradeStrict($currentDay, $klassen[$g]);
		                    
		                    for($s = 1; $s <= stundenplandata::getMaxStunden(); $s++) {
		                        
		                        $unterricht = $stundenAmTag[$s-1];
		                        
		                        for($u = 0; $u < sizeof($unterricht); $u++) {
		                            $found = false;
		                            
		                            for($b = 0; $b < sizeof($eintraegeAmTag); $b++) {
		                                
		                                if($eintraegeAmTag[$b]->getStunde() == $s && $eintraegeAmTag[$b]->getFach() == $unterricht[$u]['subject']) {
		                                    $found = true;
		                                    break;
		                                }
		                                
		                                if($eintraegeAmTag[$b]->getStunde() == $s && $eintraegeAmTag[$b]->isVertretung()) {
		                                    $found = true;
		                                    break;
		                                }
		                            }
		                            
		                            
	
		                            
		                            if(!$found) {
		                                $missingEntries[] = [
		                                    'date' => $currentDay,
		                                    'stunde' => $s,
		                                    'klasse' => $klassen[$g],
		                                    'fach' => $unterricht[$u]['subject'],
		                                    'lehrer' => $unterricht[$u]['teacher']
		                                ];
		                            }
		                        }
		                        
		                        
		                    }
		                    
		                }
		                
		                
		            }
		            
		            
		        }
		        
		        $currentDay = DateFunctions::addOneDayToMySqlDate($currentDay);
		       
		    }
		    
		    $this->lastDayCron = $currentDay;
		    
		    
		    if(sizeof($missingEntries) > 0) {
		        $missingInserts = [];
		        
		        
		        for($m = 0; $m < sizeof($missingEntries); $m++) {
		            $missingInserts[] = "
                        (
                            '" . DB::getDB()->escapeString($missingEntries[$m]['date']) . "',
                            '" . DB::getDB()->escapeString($missingEntries[$m]['stunde']) . "',
                            '" . DB::getDB()->escapeString($missingEntries[$m]['klasse']) . "',
                            '" . DB::getDB()->escapeString($missingEntries[$m]['fach']) . "',
                            '" . DB::getDB()->escapeString($missingEntries[$m]['lehrer']) . "'
                        )
                    ";
		        }
		        
		        DB::getDB()->query("INSERT INTO klassentagebuch_fehl (fehlDatum, fehlStunde, fehlKlasse, fehlFach, fehlLehrer) values " . implode(",", $missingInserts));
		        
		        $this->result = sizeof($missingEntries);
		        
		    }
		    
		    
		    DB::getSettings()->setValue('cron-tagebuch-fehl-sucher-last-day', $currentDay);
		}
	}
	
	public function getName() {
		return "Fehlende Tagebucheinträge ermitteln";
	}
	
	public function getDescription() {
		return "Sucht fehlende Einträge im Klassentagebuch.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		return ['success' => $this->result > -1, 'resultText' => $this->result . " Einträge angelegt. Zuletzt geprüfter Tag: " . $this->lastDayCron];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 120;		// Alle 2 Minuten ausführen.
	}
}



?>