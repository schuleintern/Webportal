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
												// Bugfix Issue#38
												// Eingaben mit dem Format x:xx bekommen eine 0 vorangestellt
												$hour = (substr($time, 0, 2) + $addHour);
												if (strlen($hour) < 2) {
													$hour = "0".$hour;
												}
    										$time = $hour . ":" . substr($time, 2, 2);
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
										
										case 'RRULE':
											$val = str_replace(';', '&', $value->getValues() );
											parse_str($val, $output);
											$event['RRULE'] = $output;
											break;
    									
    							}
								}
    						
    						$calData[] = $event;
    					}
    				}
					}
					
					$_debug = [];
					
					foreach($calData as $node) {
						if ($node['RRULE']) {

							if ($node['RRULE']['FREQ'] == 'YEARLY') {

								$interval = (int)$node['RRULE']['INTERVAL'];
								unset( $node['RRULE'] );

								for ($i = 1; $i <= $interval; $i++) {

									$clone = $node;
									$clone['dateStart'] = (int)substr($clone['dateStart'], 0,4) +$i .'-'.substr($clone['dateStart'], 5,2).'-'.substr($clone['dateStart'], 8,2);
									$clone['dateEnde'] = (int)substr($clone['dateEnde'], 0,4) +$i .'-'.substr($clone['dateEnde'], 5,2).'-'.substr($clone['dateEnde'], 8,2);
		
									$calData[] = $clone;
									$_debug[] = $clone;

								}
							}
						}
					}

					// if ($_debug) {
					// 	echo "<pre>";
					// 	print_r($_debug);
					// 	echo "</pre>";
			

					// 	// echo "<pre>";
					// 	// print_r($calData);
					// 	// echo "</pre>";
					// 	exit;
					// }
    			
    			
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

    		    $alleKategorien = [];
    		    
    		    
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

    		        $kategorie = [];

    		        // Kategorie (Es wird nur die erste Kategorie übernommen
                    if(is_array($terminData[$i]->categories) && sizeof($terminData[$i]->categories) > 0) {
                        $kategorie = $terminData[$i]->categories[0];
                    }
    		        
    		        $calData[] = [
    		          'titel' => $terminData[$i]->subject,
    		          'beschreibung' => strip_tags($terminData[$i]->body->content),
    		          'dateStart' => $dateStart,
    		          'dateEnde' => $dateEnd,
    		          'startTime' => $timeStart,
    		          'endTime' => $timeEnde,
    		          'ort' => $terminData[$i]->location->displayName,
    		          'isWholeDay' => ($terminData[$i]->isAllDay > 0) ? 1 : 0,
                        'externalID' => $terminData[$i]->id,
                        'changeKey' => $terminData[$i]->changeKey,
                      'kategorie' => $kategorie
    		        ];
    		    }
    		    
    		    
    		    if(sizeof($calData) > 0) {

    		        $inserts = 0;

                    DB::getDB()->query("DELETE FROM kalender_extern WHERE kalenderID='" . $kalender['kalenderID'] . "'");



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
    		            $line .= "'" . DB::getDB()->escapeString($calData[$v]['beschreibung']) . "',";

                        $line .= "'" . DB::getDB()->escapeString($calData[$v]['externalID']) . "',";
                        $line .= "'" . DB::getDB()->escapeString($calData[$v]['changeKey']) . "',";
                        $line .= "'" . DB::getDB()->escapeString($calData[$v]['kategorie']) . "'";
                        $line .= ")";
    		            
    		            $inserts++;

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
                                eintragKommentar,
                                eintragExternalID,
                                eintragExternalChangeKey,
                                eintragKategorieName
                            ) VALUES
                            " . $line);
    		        }


    		        
    		    }


    		    for($k = 0; $k < sizeof($alleKategorien); $k++) {
    		        DB::getDB()->query("INSERT INTO externe_kalender_kategorien (kalenderID, kategorieName) 
                        values(
                            '" . $kalender['kalenderID'] . "',
                            '" . DB::getDB()->escapeString($alleKategorien[$k]) . "'
                    ) ON DUPLICATE KEY UPDATE kalenderID=kalenderID");
                }


                $this->result .= "Kalender " . $kalender['kalenderName'] . " importiert. (" . ($inserts) . " Termine.)\r\n";
    		    
    		    
    		}
		}
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