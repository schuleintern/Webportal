<?php


abstract class AbstractKalenderPage extends AbstractPage {
	private $info;
	protected $tableName;
	protected $title;
	protected $isExternalKalender = false;
	protected $kalenderID = 0;

	/**
	 * Darf die Einträge dieses Kalender ändern und erstellen
	 * @var string
	 */
	protected $isAdmin = false;
	
	protected $editOnlyOwn = false;

	public function __construct() {
	
		parent::__construct(array("Kalender", $this->title));

		$this->checkKalenderAccess();
	
	}
	
	/**
	 * Prüft, ob man Zugriff auf den Kalender hat und setzt das Attribut isAdmin
	 * @see $this->isAdmin
	 */
	public abstract function checkKalenderAccess();
	
	/**
	 * 
	 * @param int $termin
	 * @param String $start
	 * @param String $end
	 */
	public function updateTerminDates($terminID, $start, $end, $isAllDay=true, $newStartTime='', $newEndTime='') {
	}
	
	/**
	 * @return AbstractTermin[] Termine
	 */
	public abstract function getTermineFromDatabase($begin = '', $end = '');
	
	/**
	 * Sendet die ICS Feed URL als JSON
	 */
	public abstract function sendICSFeedURL();
	
	public function execute() {
	    if($_REQUEST['currentDate'] != '') $today = $_REQUEST['currentDate'];
	    else $today = DateFunctions::getTodayAsSQLDate();
		
	    
	    
	    if(isset($_GET['action']) && $_GET['action'] == 'getICSFeedURL') {
	        $this->sendICSFeedURL();
	    }
	    
			
		if($this->isAdmin) {
		    
		    // 
		    
		    if(isset($_GET['action']) && $_GET['action'] == "editTerminDays") {
		        $newStart = $_REQUEST['newDate'];
		        $newEnd = $_REQUEST['newEndDate'];
		        
		        $isAllDay = true;
		        
		        if(!$_REQUEST['isAllDay']) {
		            $isAllDay = false;
		            $newStartTime = $_REQUEST['newStartTimeHour'] . ":" . $_REQUEST['newStartTimeMinutes'];
		            $newEndTime = $_REQUEST['newEndTimeHour'] . ":" . $_REQUEST['newEndTimeMinutes'];
		        }
		        
		        
		        if(DateFunctions::isSQLDate($newEnd) && $isAllDay) $newEnd = DateFunctions::substractOneDayToMySqlDate($newEnd);
		        
		        $this->updateTerminDates($_REQUEST['terminID'], $newStart, $newEnd, $isAllDay, $newStartTime, $newEndTime);
		        
		        $json = [
		            'success' => 'true'
		        ];
		        
		        header("Content-type: application/json");
		        echo json_encode($json);
		        exit(0);
		    }
		    
		    if(isset($_GET['action']) && $_GET['action'] == "edit") {
		        
		        header("Content-type: application/json");
		        
		        $event = DB::getDB()->query_first("SELECT * FROM " . $this->tableName . " WHERE eintragID='" . intval($_REQUEST['eintragID']) . "'");
		        
		        
		        $canEdit = false;
		        
		        if($this->kalenderID > 0) {
		            if($event['eintragID'] > 0 && $event['kalenderID'] == $this->kalenderID) {
		                if($this->isAdmin) {
		                    $canEdit = true;
		                }
		            }
		        }
		        else {
		            if($event['eintragID'] > 0) {
		                if($this->isAdmin) {
		                    $canEdit = true;
		                }
		            }
		        }
		        
		        if(!$canEdit) {
		            echo(json_encode(['success' => false, 'errorText' => 'Keine Berechtigung']));
		            exit(0);
		        }
		        
		        
		        $date = addslashes($_POST['date']);
		        
		        $date = DateFunctions::getMySQLDateFromNaturalDate($date);
		        
		        $dateEnde = addslashes($_POST['enddatum']);
		        
		        $jsonAnswer = [
		            'success' => true,
		            'errorText' => ''
		        ];
		        
		        if($dateEnde == "") $dateEnde = $date;
		        else if(!DateFunctions::isNaturalDate($dateEnde)) {
		            $jsonAnswer = [
		                'success' => false,
		                'errorText' => 'Das angegebene Enddatum ist ungültig!'
		            ];
		            
		            echo(json_encode($jsonAnswer));
		            exit(0);
		        }
		        else {
		            $dateEnde = DateFunctions::getMySQLDateFromNaturalDate($dateEnde);
		        }
		        
		        if(!DateFunctions::isSQLDateAtOrAfterAnother($dateEnde, $date)) {
		            $jsonAnswer = [
		                'success' => false,
		                'errorText' => 'Das angegebene Enddatum ist nicht nach dem Startdatum!'
		            ];
		            echo(json_encode($jsonAnswer));
		            exit(0);
		        }
		        
		        if(trim($_POST['titel']) == '') {
		            $jsonAnswer = [
		                'success' => false,
		                'errorText' => 'Ein Titel muss angegeben werden!'
		            ];
		            echo(json_encode($jsonAnswer));
		            exit(0);
		        }
		        
		        
		        
		        $titel = addslashes($_POST['titel']);
		        
		        
		        $ort = addslashes($_POST['ort']);
		        $isWholeDay = $_POST['wholeDay'] > 0;
		        $kommentar = addslashes($_POST['kommentar']);
		        
		        $stundeStart = intval($_POST['stundeStart']);
		        $stundeEnde  = intval($_POST['stundeEnde']);
		        
		        $minuteStart = intval($_POST['minuteStart']);
		        $minuteEnde  = intval($_POST['minuteEnde']);
		        
		        if(!($stundeStart >= 0 && $stundeStart <= 24 && $stundeEnde >= 0 && $stundeEnde <= 24 && $minuteStart >= 0 && $minuteStart <= 60 && $minuteEnde >= 0 && $minuteEnde <= 60)) {
		            $jsonAnswer = [
		                'success' => false,
		                'errorText' => 'Falsches Uhrzeitformat!'
		            ];
		            echo(json_encode($jsonAnswer));
		            exit(0);
		        }
		        
		        if($stundeStart < 10) $stundeStart = "0" . $stundeStart;
		        if($stundeEnde < 10) $stundeEnde = "0" . $stundeEnde;
		        if($minuteStart < 10) $minuteStart = "0" . $minuteStart;
		        if($minuteEnde < 10) $minuteEnde = "0" . $minuteEnde;
		        
		        $uhrzeitStart = $stundeStart . ":" . $minuteStart;
		        $uhrzeitEnde  = $stundeEnde . ":" . $minuteEnde;
		        
		        
		        $eintragKategorie = intval($_POST['kategorieID']);
		        
		        
		        DB::getDB()->query("UPDATE " . $this->tableName . " SET
						eintragTitel = '$titel',
                        eintragKategorie = '$eintragKategorie',
						eintragDatumStart = '$date',
						eintragDatumEnde = '$dateEnde',
						eintragIsWholeDay = '$isWholeDay',
						eintragUhrzeitStart = '$uhrzeitStart',
						eintragUhrzeitEnde = '$uhrzeitEnde',
						eintragEintragZeitpunkt = UNIX_TIMESTAMP(),
						eintragOrt = '$ort',
						eintragKommentar = '$kommentar'
		            WHERE eintragID='" . $event['eintragID'] . "'");
		        
		        
		        echo(json_encode($jsonAnswer));
		        exit(0);
		        
		    }
		    
			if(isset($_GET['action']) && $_GET['action'] == "add") {
			    
			    header("Content-type: application/json");
			    
	
				$date = addslashes($_POST['date']);
	
				$dateEnde = addslashes($_POST['enddatum']);
				
				$jsonAnswer = [
				    'success' => true,
				    'errorText' => ''
				];
	
				if($dateEnde == "") $dateEnde = $date;
				else if(!DateFunctions::isNaturalDate($dateEnde)) {
				    $jsonAnswer = [
				        'success' => false,
				        'errorText' => 'Das angegebene Enddatum ist ungültig!'
				    ];
				    
				    echo(json_encode($jsonAnswer));
				    exit(0);
				}
				else {
					$dateEnde = DateFunctions::getMySQLDateFromNaturalDate($dateEnde);
				}
		
				if(!DateFunctions::isSQLDateAtOrAfterAnother($dateEnde, $date)) {
				    $jsonAnswer = [
				        'success' => false,
				        'errorText' => 'Das angegebene Enddatum ist nicht nach dem Startdatum!'
				    ];
				    echo(json_encode($jsonAnswer));
				    exit(0);
				}
				
				if(trim($_POST['titel']) == '') {
				    $jsonAnswer = [
				        'success' => false,
				        'errorText' => 'Ein Titel muss angegeben werden!'
				    ];
				    echo(json_encode($jsonAnswer));
				    exit(0);
				}
				
	
	
				$titel = addslashes($_POST['titel']);
	
	
				$ort = addslashes($_POST['ort']);
				$isWholeDay = $_POST['wholeDay'] > 0;
				$kommentar = addslashes($_POST['kommentar']);
				
				$stundeStart = intval($_POST['stundeStart']);
				$stundeEnde  = intval($_POST['stundeEnde']);
				
				$minuteStart = intval($_POST['minuteStart']);
				$minuteEnde  = intval($_POST['minuteEnde']);
				
				if(!($stundeStart >= 0 && $stundeStart <= 24 && $stundeEnde >= 0 && $stundeEnde <= 24 && $minuteStart >= 0 && $minuteStart <= 60 && $minuteEnde >= 0 && $minuteEnde <= 60)) {
				    $jsonAnswer = [
				        'success' => false,
				        'errorText' => 'Falsches Uhrzeitformat!'
				    ];
				    echo(json_encode($jsonAnswer));
				    exit(0);
				}
				
				if($stundeStart < 10) $stundeStart = "0" . $stundeStart;
				if($stundeEnde < 10) $stundeEnde = "0" . $stundeEnde;
				if($minuteStart < 10) $minuteStart = "0" . $minuteStart;
				if($minuteEnde < 10) $minuteEnde = "0" . $minuteEnde;
				
				$uhrzeitStart = $stundeStart . ":" . $minuteStart;
				$uhrzeitEnde  = $stundeEnde . ":" . $minuteEnde;
				
				$eintragUser = DB::getUserID();
				
				$eintragKategorie = intval($_POST['kategorieID']);
	
				DB::getDB()->query("INSERT INTO " . $this->tableName . " (
						eintragTitel,
                        eintragKategorie,
						eintragDatumStart,
						eintragDatumEnde,
						eintragUser,
						eintragIsWholeDay,
						eintragUhrzeitStart,
						eintragUhrzeitEnde,
						eintragEintragZeitpunkt,
						eintragOrt,
						eintragKommentar
                        " . (($this->kalenderID > 0) ? ",kalenderID" : "") . "
						)
						values(
							'$titel',
				            '$eintragKategorie',
							'$date',
							'$dateEnde',
							'$eintragUser',
							'$isWholeDay',
							'$uhrzeitStart',
							'$uhrzeitEnde',
							UNIX_TIMESTAMP(),
							'$ort',
							'$kommentar'
                            " . (($this->kalenderID > 0) ? (",'" . $this->kalenderID . "'") : "") . "
						)");
	
				$dataDate = explode("-",$date);
				$date = $dataDate[0] . "-" . (($dataDate[1] < 10) ? "0" : "") . $dataDate[1] . "-" . (($dataDate[2] < 10) ? "0" : "") . $dataDate[2];
	
				
				echo(json_encode($jsonAnswer));
				exit(0);
				
			}
	
			
	
			if(isset($_GET['action']) && $_GET['action'] == "delete") {
				// Eintrag löschen
				$event = DB::getDB()->query_first("SELECT * FROM " . $this->tableName . " WHERE eintragID='" . intval($_REQUEST['eintragID']) . "'");

				if($this->kalenderID > 0) {
				    if($event['eintragID'] > 0 && $event['kalenderID'] == $this->kalenderID) {
				        if($this->isAdmin) {
				            DB::getDB()->query("DELETE FROM " . $this->tableName . " WHERE eintragID='" . intval($_REQUEST['eintragID']) . "'");
				        }
				    }
				}
				else {		
    				if($event['eintragID'] > 0) {
    					if($this->isAdmin) {
    					    DB::getDB()->query("DELETE FROM " . $this->tableName . " WHERE eintragID='" . intval($_REQUEST['eintragID']) . "'");
    					}
    				}
				}
	
				header("Content-type: application/json");

				$jsonAnswer = [
				    'success' => true,
				    'errorText' => '',
				    'eintragID' => intval($_REQUEST['eintragID'])
				];
				
								
				echo(json_encode($jsonAnswer));
				exit(0);
			}
		}
		
		
		if($_REQUEST['action'] != '' && $_REQUEST['action'] == 'getJSONData') {
		    $termine = $this->getJSONTermine();
		    
		    header("Content-type: application/json");
		    echo json_encode($termine);
		    
		    exit(0);
		}
	

	
			$canAdd = $this->isAdmin ? 1 : 0;
	
	
			if($_GET['mode'] == 'print') {
	
			    
			    $termine = $this->getTermine(true);
			    
			    
				$datumHeute = date("d.m.Y");
				
				
				eval("\$print =\"" . DB::getTPL()->get("abstractKalender/print") . "\";");
					
	
				$pdf = new PrintNormalPageA4WithHeader('Kalender');
				$pdf->setPrintedDateInFooter();
				
				$pdf->setHTMLContent($print);
				
				
				$pdf->send();
	
				exit(0);
			}
			else if($this->isAdmin) {
				
				$selectHTMLStunde = "";
				for($i = 0; $i < 24; $i++) {
					$selectHTMLStunde .= "<option value=\"" . $i . "\">$i</option>\r\n";
				}
				
				$selectHTMLMinute = "";
				for($i = 0; $i < 60; $i++) {
					$selectHTMLMinute .= "<option value=\"" . $i . "\">$i</option>\r\n";
				}
				
				$kategorieHTML = "";
				
				$kategorien = KalenderKategorie::getAllForKalender($_REQUEST['kalenderID']);
				
				for($i = 0; $i < sizeof($kategorien); $i++) {
				    $kategorieHTML .= "<option value=\"" . $kategorien[$i]->getID() . "\">" . $kategorien[$i]->getKategorieName() . "</option>";
				}
				
				
				eval("echo(\"" . DB::getTPL()->get("abstractKalender/mitEintragen") . "\");");
				PAGE::kill(true);
      	//exit(0);
			}
	
		
			else {
				eval("echo(\"" . DB::getTPL()->get("abstractKalender/nurAnzeigen") . "\");");
			}
	
	}
	
	private function getTermine($showForPDF=false) {
	    $terminText = "";
	    
	    $onlyFromToday = $showForPDF;
	    $onlyFromTodayDate = $onlyFromToday ? DateFunctions::getTodayAsSQLDate() : "";
	    
	    $monthNames = array(
	        "",
	        "Januar",
	        "Februar",
	        "März",
	        "April",
	        "Mai",
	        "Juni",
	        "Juli",
	        "August",
	        "September",
	        "Oktober",
	        "November",
	        "Dezember"
	    );
	    
	    $pdfLastDay = "";
	    
	    
	    if($_REQUEST['start'] != "" && DateFunctions::isSQLDate($_REQUEST['start'])) $onlyFromTodayDate = $_REQUEST['start'];
	    if($_REQUEST['end'] != "" && DateFunctions::isSQLDate($_REQUEST['end'])) $untilDate = $_REQUEST['end'];
	    
	    if($untilDate == "") $untilDate = "2099-01-01";
	    
	    
	    $termine = $this->getTermineFromDatabase($untilDate, $onlyFromTodayDate);
	    	    
	    for($i = 0; $i < sizeof($termine); $i++) {
	        
	        
	        
	        
	        
	        
	        if($this->isAdmin) {
	            // Löschmöglichkeit
	            $addDelete = ", canDelete: 1, eventID: {$termine[$i]->getID()}, eventType: 'termin'";
	        } else $addDelete = ", canDelete: 0, eventID: {$termine[$i]->getID()}, eventType: 'termin'";
	        
	        $eintragZeitpunkt = $termine[$i]->getEintragZeitpunkt();
	        
	        $icon = "fa fa-calendar";
	        
	        $wholeDay = '';
	        
	        if($termine[$i]->isWholeDay()) {
	            // $startzeit = "23:59:00";
	            // $endzeit = "23:59:00";
	            $startzeit = $termine[$i]->getDatumStart();
	            $endzeit = $termine[$i]->getDatumEnde();
	            if($endzeit != $startzeit) $endzeit = DateFunctions::addOneDayToMySqlDate($endzeit);
	            $wholeDay = 'allDay: true,';
	        }
	        else {
	            $startzeit = $termine[$i]->getDatumStart() . "T" . $termine[$i]->getUhrzeitStart() . ":00";
	            // $endzeit = $termine[$i]->getUhrzeitEnde() . ":00";
	            $endzeit = $termine[$i]->getDatumEnde() . "T" . $termine[$i]->getUhrzeitStart() . ":00";
	        }
	        
	        if($show && !$showForPDF) {
	            $termin = [
	                
	            ];
	            
	            /**
	             * title: '" . DB::getDB()->escapeString($termine[$i]->getTitle()) . "',
	             start: '$startzeit',
	             eintragZeitpunkt: '" . $eintragZeitpunkt . "',
	             icon: '" . $icon . "',
	             ort: '" . DB::getDB()->escapeString($termine[$i]->getOrt()) . "',
	             kommentar: '" . DB::getDB()->escapeString($termine[$i]->getKommentar()) . "',
	             end: '$endzeit',
	             $wholeDay
	             color: 'green'$addDelete
	             },\r\n";
	             
	             */
	            
	            $termine[] = $termin;
	        }
	        
	        
	        elseif ($showForPDF) {
	            
	            $datum = explode("-",$termine[$i]->getDatumStart());
	            
	            $datum[0] *= 1;
	            $datum[1] *= 1;
	            $datum[2] *= 1;
	            
	            
	            if($pdfLastDay != $termine[$i]->getDatumStart()) {
	                // Tag anzeigen
	                
	                $terminText .= "<br /><br /><table border=\"1\" width=\"100%\" cellpadding=\"3\"><tr><td><b>{$datum[2]}. {$monthNames[$datum[1]]} {$datum[0]}</b></td></tr></table><br />\r\n";
	                $pdfLastDay = $termine[$i]->getDatumStart();
	                
	            }
	            $terminText .= "<br /><b>" . $termine[$i]->getTitle() . "</b>";
	            
	            if($termine[$i]->getDatumEnde() != $termine[$i]->getDatumStart()) $terminText .= " (Bis " . DateFunctions::getNaturalDateFromMySQLDate($termine[$i]->getDatumEnde()) . ")";
	            $terminText .= "<br />";
	            
	            if(!$termine[$i]->isWholeDay()) $terminText .= "Bis " . $termine[$i]->getUhrzeitEnde() . " Uhr<br />";
	            if($termine[$i]->getKommentar() != "")    $terminText .= "<small>" . $termine[$i]->getKommentar() . "</small><br />\r\n";
	        }
	        
	    }
	    
	    
	    if(! $showForPDF && !$this->isExternalKalender) {
	        // Removed
	    }
	    
	    return $terminText;
	}
	
	private function getJSONTermine($showForPDF=false) {
		$terminText = "";

		
		if($_REQUEST['start'] != "" && DateFunctions::isSQLDate($_REQUEST['start'])) $onlyFromTodayDate = $_REQUEST['start'];
		if($_REQUEST['end'] != "" && DateFunctions::isSQLDate($_REQUEST['end'])) $untilDate = $_REQUEST['end'];
	
	
		
		$termine = $this->getTermineFromDatabase($onlyFromTodayDate, $untilDate);
	
		for($i = 0; $i < sizeof($termine); $i++) {

		    $termin = [
		        'eventID' => $termine[$i]->getID(),
		        'eventType' => 'termin'
		    ];
		    
		    // Suche Kategorie
		    /** @var AbstractKalenderKategorie $kategorie */
		    $kategorie = $termine[$i]->getKategorie();
		    
		    $kategorieName = '';
		    $kategorieFarbe = 'blue';
		    $kategorieIcon = 'fa fa-calendar';
		    $kategorieID = 0;
		    
		    if($kategorie != null) {
		        $kategorieName = $kategorie->getKategorieName();
		        $kategorieFarbe = $kategorie->getFarbe();
		        $kategorieIcon = $kategorie->getIcon();
		        $kategorieID = $kategorie->getID();
		    }
		    
		    
			if($this->isAdmin) {
			    $termin['canDelete'] = true;
			    $termin['editable'] = true;
			} else {
			    $termin['canDelete'] = false;
			    $termin['editable'] = false;
			}
			
			if($this->editOnlyOwn) {
			    if($termine[$i]->getCreatorUserID() == DB::getSession()->getUser()->getUserID() || DB::getSession()->isAdmin()) {
			        $termin['canDelete'] = true;
			        $termin['editable'] = true;
			    }
			    else {
			        $termin['canDelete'] = false;
			        $termin['editable'] = false;
			    }
			}
	
			$eintragZeitpunkt = $termine[$i]->getEintragZeitpunkt();
		
			$wholeDay = '';
			
			if($termine[$i]->isWholeDay()) {
				$startzeit = $termine[$i]->getDatumStart();
				$endzeit = $termine[$i]->getDatumEnde();
				if($endzeit != $startzeit) $endzeit = DateFunctions::addOneDayToMySqlDate($endzeit);
				
				$termin['allDay'] = true;
			}
			else {
				$startzeit = $termine[$i]->getDatumStart() . "T" . $termine[$i]->getUhrzeitStart() . ":00+00:00";
				// $endzeit = $termine[$i]->getUhrzeitEnde() . ":00";
				$endzeit = $termine[$i]->getDatumEnde() . "T" . $termine[$i]->getUhrzeitEnde() . ":00+00:00";
				$termin['allDay'] = false;
			}

			$termin['title'] = $termine[$i]->getTitle();
			$termin['titleRaw'] = $termine[$i]->getTitleRaw();
			$termin['start'] = $startzeit;
			$termin['eintragZeitpunkt'] = $eintragZeitpunkt;
			$termin['icon'] = $kategorieIcon;
			$termin['ort'] = $termine[$i]->getOrt();
			$termin['kommentar'] = $termine[$i]->getKommentar();
			$termin['end'] = $endzeit;
			$termin['color'] = $kategorieFarbe;
			
			$termin['eingetragenVon'] = $termine[$i]->getCreatorName();
			
			$termin['kategorieName'] = $kategorieName;
			$termin['kategorieID'] = $kategorieID;
			
			
			$termineJSON[] = $termin;
		}
	
		if($onlyFromTodayDate == "") $onlyFromTodayDate = DateFunctions::getTodayAsSQLDate();
		
		if($untilDate != "") $addSql = "AND ferienEnde <= '$untilDate'";
		
		$addSql = " AND 
		(ferienStart >= '$onlyFromTodayDate' AND ferienEnde <= '$untilDate') OR 

		(ferienStart <= '$onlyFromTodayDate' AND ferienEnde <= '$untilDate' AND ferienEnde >= '$onlyFromTodayDate') OR 
        
		(ferienStart >= '$onlyFromTodayDate' AND ferienStart <= '$untilDate' AND ferienEnde >= '$untilDate') OR 

		(ferienStart <= '$onlyFromTodayDate' AND ferienEnde >= '$untilDate')";
		
		$ferien = DB::getDB()->query("SELECT * FROM kalender_ferien WHERE ferienStart >= '$onlyFromTodayDate' $addSql");
		
		while($f = DB::getDB()->fetch_array($ferien)) {
		    
		    if($f['ferienStart'] != $f['ferienEnde']) {
		        $f['ferienEnde'] = DateFunctions::addOneDayToMySqlDate($f['ferienEnde']);
		    }
		    
		    
		    $newTermin = [
		        'title' => $f['ferienName'],
		        'start' => $f['ferienStart'],
		        'end' => $f['ferienEnde'],
		        'eintragZeitpunkt' => '',
		        'betrifft' => '',
		        'stunden' => '',
		        'icon' => 'fa fa-sun',
		        'allDay' => true,
		        'klassen' => '&nbsp;',
		        'ort' => 'Bayern',
		        'color' => 'black',
		        'canDelete' => 0,
		        'eventID' => -1,
		        'eventType' => 'ferien',
		        'lnwtype' => 'ferien',
		        'eventDurationEditable' => false
		    ];
		    
		    $termineJSON[] = $newTermin;
		    
		}
		
		return $termineJSON;
	}

	
	public static function hasSettings() {
		return false;
	}
	

	public static function getSettingsDescription() {
		return array();
	}
	
	
	public static function getSiteDisplayName() {
		return 'Allgemeiner Kalender';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}
	
}

