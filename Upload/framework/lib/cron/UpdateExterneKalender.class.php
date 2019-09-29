<?php

/**
 * Erstellt in der Demo Version einen fiktiven Vertretungsplan
 * @author Christian
 *
 */


class UpdateExterneKalender extends AbstractCron {

	private $result = '';
	
	public function __construct() {
	}

	public function execute() {
		
		$alleKalenderSQL = DB::getDB()->query("SELECT * FROM externe_kalender");
		
		$alleKalender = [];
		while($d = DB::getDB()->fetch_array($alleKalenderSQL)) {
			$alleKalender[] = $d;
		}
		
		for($i = 0; $i < sizeof($alleKalender); $i++) {
			$kalender = $alleKalender[$i];
			
			if($kalender['kalenderIcalFeed'] != "") {
			
    			$icalfeed = file_get_contents($kalender['kalenderIcalFeed']);
    			
    			$icalobj = new ZCiCal($icalfeed);

    			
    			
    			$calData = [];
    					
    			
    			// read back icalendar data that was just parsed
    			if(isset($icalobj->tree->child))
    			{
    				foreach($icalobj->tree->child as $node)
    				{
    					if($node->getName() == "VEVENT")
    					{
    						$event = [];
    						
    						foreach($node->data as $key => $value)
    						{
    							switch($key) {
    								case 'DTSTART':
    									// 20161221T140000Z
    									
    								    
    								    
    									$val = $value->getValues();
    									
    									
    									$addHour = 0;
    									
    									if(strpos($val, 'Z') > 0) $addHour = 1;
    									
    									
    									$event['dateStart'] = substr($val,0,4). '-' . substr($val, 4, 2) . '-' . substr($val, 6, 2);
    									if(strpos($val, 'T') == false) {
    										$event['isWholeDay'] = 1;
    										$event['startTime'] = '';
    									}
    									else {
    										$event['isWholeDay'] = 0;
    										// Zeit suchen
    										$time = substr($val, strpos($val,'T')+1);
    										$time = (substr($time, 0, 2)+$addHour) . ":" . substr($time, 2, 2);
    										$event['startTime'] = $time;
    									}
    									break;
    									
    								case 'DTEND':
    									// 20161221T140000Z
    									$val = $value->getValues();
    									$event['dateEnde'] = substr($val,0,4). '-' . substr($val, 4, 2) . '-' . substr($val, 6, 2);
    									
    									if(strpos($val, 'Z') > 0) $addHour = 1;
    									
    									if(strpos($val, 'T') == false) {
    										$event['isWholeDay'] = 1;
    										$event['endTime'] = '';
    										$event['dateEnde'] = DateFunctions::substractOneDayToMySqlDate($event['dateEnde']);
    									}
    									else {
    										$event['isWholeDay'] = 0;
    										// Zeit suchen
    										$time = substr($val, strpos($val,'T')+1);
    										$time =  (substr($time, 0, 2)+$addHour) . ":" . substr($time, 2, 2);
    										$event['endTime'] = $time;
    									}
    									
    									break;
    									
    								case 'SUMMARY':
    									$event['titel'] = $value->getValues();
    									break;
    									
    								case 'LOCATION':
    									$event['ort'] = $value->getValues();
    									break;
    									
    								case 'DESCRIPTION':
    									$event['beschreibung'] = htmlspecialchars($value->getValues());
    									break;
    									
    							}
    						}
    						
    						$calData[] = $event;
    					}
    				}
    			}
    			
    			
    			if(sizeof($calData) > 0) {
    				DB::getDB()->query("DELETE FROM kalender_extern WHERE kalenderID='" . $kalender['kalenderID'] . "'");
    				
    				$inserts = [];
    				
    				for($v = 0; $v < sizeof($calData); $v++) {
    					$line = "('" . $kalender['kalenderID'] . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['titel']) . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['dateStart']) . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['dateEnde']) . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['isWholeDay']) . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['startTime']) . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['endTime']) . "',";
    					
    					$line .= "UNIX_TIMESTAMP(),";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['ort']) . "',";
    					$line .= "'" . DB::getDB()->escapeString($calData[$v]['beschreibung']) . "')";
    					
    					$inserts[] = $line;
    				}
    				
    				DB::getDB()->query("INSERT INTO kalender_extern
    						
    				(
    					kalenderID,
    					eintragTitel,
    					eintragDatumStart,
    					eintragDatumEnde,
    					eintragIsWholeDay,
    					eintragUhrzeitStart,
    					eintragUhrzeitEnde,
    					eintragEintragZeitpunkt,
    					eintragOrt,
    					eintragKommentar
    				) VALUES
    			" . implode(",",$inserts));
    				
    			}
    			
    			$this->result .= "Kalender " . $kalender['kalenderName'] . " importiert. (" . sizeof($inserts) . " Termine.)\r\n";
    		}
    		elseif($kalender['office365Username'] != "") {
    		    $terminData = Office365Api::getTermine($kalender['office365Username']);
    		    
    		    $calData = [];
    		    
    		    
    		    for($i = 0; $i < sizeof($terminData); $i++) {
    		        $dateStart = explode("T",$terminData[$i]->start->dateTime)[0];
    		        $dateEnd = explode("T",$terminData[$i]->end->dateTime)[0];
    		        
    		        $timeStart = substr(explode("T",$terminData[$i]->start->dateTime)[1], 0, 5);
    		        $timeEnde = substr(explode("T",$terminData[$i]->end->dateTime)[1], 0, 5);
    		        
    		        $localTimeStart = new DateTime($dateStart . ' ' . $timeStart . ":00", new DateTimeZone('UTC'));
    		        $localTimeStart->setTimezone(new DateTimeZone('Europe/Berlin'));
    		        
    		        $timeStart = $localTimeStart->format("H:i");
    		        
    		        
    		        $localTimeStart = new DateTime($dateEnd . ' ' . $timeEnde . ":00", new DateTimeZone('UTC'));
    		        $localTimeStart->setTimezone(new DateTimeZone('Europe/Berlin'));
    		        
    		        $timeEnde = $localTimeStart->format("H:i");
    		        
    		        if($dateStart != $dateEnd) {
    		            $dateEnd = DateFunctions::substractOneDayToMySqlDate($dateEnd);
    		        }
    		        
    		        
    		        $calData[] = [
    		          'titel' => $terminData[$i]->subject,
    		          'beschreibung' => strip_tags($terminData[$i]->body->content),
    		          'dateStart' => $dateStart,
    		          'dateEnde' => $dateEnd,
    		          'startTime' => $timeStart,
    		          'endTime' => $timeEnde,
    		          'ort' => $terminData[$i]->location->displayName,
    		          'isWholeDay' => ($terminData[$i]->isAllDay > 0) ? 1 : 0
    		        ];
    		    }
    		    
    		    
    		    if(sizeof($calData) > 0) {
    		        DB::getDB()->query("DELETE FROM kalender_extern WHERE kalenderID='" . $kalender['kalenderID'] . "'");
    		        
    		        $inserts = [];
    		        
    		        for($v = 0; $v < sizeof($calData); $v++) {
    		            $line = "('" . $kalender['kalenderID'] . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['titel']) . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['dateStart']) . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['dateEnde']) . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['isWholeDay']) . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['startTime']) . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['endTime']) . "',";
    		            
    		            $line .= "UNIX_TIMESTAMP(),";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['ort']) . "',";
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['beschreibung']) . "')";
    		            
    		            $inserts[] = $line;
    		        }
    		        
    		        DB::getDB()->query("INSERT INTO kalender_extern
    		            
    				(
    					kalenderID,
    					eintragTitel,
    					eintragDatumStart,
    					eintragDatumEnde,
    					eintragIsWholeDay,
    					eintragUhrzeitStart,
    					eintragUhrzeitEnde,
    					eintragEintragZeitpunkt,
    					eintragOrt,
    					eintragKommentar
    				) VALUES
    			" . implode(",",$inserts));
    		        
    		    }


                $this->result .= "Kalender " . $kalender['kalenderName'] . " importiert. (" . sizeof($inserts) . " Termine.)\r\n";
    		    
    		    
    		}
		}

		
		
		if(DB::isDebug()) echo($this->result);

	}
	
	
	public function getName() {
		return "Externe Kalender downloaden";
	}
	
	public function getDescription() {
		return "Lädt die iCALs der externen Kalender herunter.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
    	return [
	    	'success' => true,
	    	'resultText' => $this->result
	   	];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 3600;		// Einmal in der Stunde ausführen
	}
}



?>