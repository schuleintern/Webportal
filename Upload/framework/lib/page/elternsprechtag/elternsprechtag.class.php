<?php




class elternsprechtag extends AbstractPage {

	private $info;
	
	private $isAdmin = false;
	
	private static $currentSprechtag = [];
	private static $currentSprechtagID = 0;
	
	private static $sprechtagIsBuchbar = false;
	
	public function __construct() {
				
		parent::__construct(array("Elternsprechtag"));
		
		$this->checkLogin();
	}

	public function execute() {
	    
	    if(!DB::getSession()->isTeacher() && !DB::getSession()->isEltern()) {
	        new errorPage("Die Sprechtagsbuchung ist nur für Eltern oder Lehrer zugänglich.");
	    }
	    
	    
	    
	    $currentSprechtage = [];
	    
	    $currentSprechtageSQL = DB::getDB()->query("SELECT * FROM sprechtag WHERE sprechtagDate >= CURDATE() AND sprechtagIsVorlage=0 AND sprechtagIsActive=1 AND sprechtagBuchbarAb < UNIX_TIMESTAMP()");
	    
	    while($s = DB::getDB()->fetch_array($currentSprechtageSQL)) {
	        $currentSprechtage[] = $s;
	    }
	    
	    
	    if($_REQUEST['sprechtagID'] > 0) {
	        for($i = 0; $i < sizeof($currentSprechtage); $i++) {
	            if($currentSprechtage[$i]['sprechtagID'] == intval($_REQUEST['sprechtagID'])) {
	                $this->currentSprechtag = $currentSprechtage[$i];
	                $this->doSprechtagAction();
	                exit(0);
	            }
	        }
	        
	        new errorPage("Sprechtag ID ungültig!");
	    }
	    
	    
	    $showList = false;
        if(sizeof($currentSprechtage) == 1) {
            header("Location: index.php?page=elternsprechtag&sprechtagID=" . $currentSprechtage[0]['sprechtagID']);
            exit(0);
	    }
	    
	    else if(sizeof($currentSprechtage) > 1) {
	    
	        $list = "";
	        
	        for($i = 0; $i < sizeof($currentSprechtage); $i++) {
	            
	            if(!DB::getSession()->isTeacher()) {
	                
	                $grades = DB::getSession()->getElternObject()->getKlassenAsArray();
	                
	            }
	            else {
	                $allGradesOK = true;
	                $grades = [];
	            }
	            
	            
	            $klasseOK = false;
	            
	            $sprechtagKlassen = explode(";;;", $currentSprechtage[$i]['sprechtagKlassen']);
	            
	            if($currentSprechtage[$i]['sprechtagKlassen'] == "") {
	                $klasseOK = true;
	                $sprechtagKlassen = ["alle Klassen"];
	            }
	            else {
	                
	                for($g = 0; $g < sizeof($grades); $g++) {
	                    
	                    if(in_array($grades[$g], $sprechtagKlassen)) {
	                        $klasseOK = true;
	                        break;
	                    }
	                    
	                }
	            }
	            
	            
	            if($allGradesOK) $klasseOK = true;
	            
	            
	            if($klasseOK) {
	                $list .= "<a style=\"width:50%\" href=\"index.php?page=elternsprechtag&sprechtagID=" . $currentSprechtage[$i]['sprechtagID'] . "\" class=\"btn btn-default\"><b>" . $currentSprechtage[$i]['sprechtagName'] . "</b>";
	                
	                $list .= "<br /><small>Klassen: " . implode(", ", $sprechtagKlassen);
	                
	                $list .= "<br />Datum: " . DateFunctions::getNaturalDateFromMySQLDate($currentSprechtage[$i]['sprechtagDate']) . "";
	                
	                $list .=  "</small></a><br /><br />";
	            }
	            else {
	                $list .= "<a style=\"width:50%\" disabled=\"disabled\" href=\"index.php?page=elternsprechtag&sprechtagID=" . $currentSprechtage[$i]['sprechtagID'] . "\" class=\"btn btn-default\">" . $currentSprechtage[$i]['sprechtagName'];
	                
	                $list .= "<br /><small>Klassen: " . implode(", ", $sprechtagKlassen) . "</small>";
	                
	                $list .= "<br /><b>Für Sie leider nicht verfügbar</b>";
	                
	                $list .=  "</a><br /><br />";
	            }
	            

	        }
	        
	        eval("DB::getTPL()->out(\"". DB::getTPL()->get("elternsprechtag/moredays") . "\");");
	        
	        
	    }
	    else if(sizeof($currentSprechtage) == 0) {
	        eval("DB::getTPL()->out(\"". DB::getTPL()->get("elternsprechtag/noday") . "\");");
	    }

	    
	    
	    
	}
	
	public function doSprechtagAction() {
	    		
		// $this->currentSprechtag = DB::getDB()->query_first("SELECT * FROM sprechtag WHERE sprechtagDate >= CURDATE()");
		
		if($this->currentSprechtag['sprechtagBuchbarBis'] != "") {
			if(DateFunctions::isSQLDateTodayOrLater($this->currentSprechtag['sprechtagBuchbarBis'])) {
				$this->sprechtagIsBuchbar = true;
			}
		}
		
		if($_REQUEST['action'] != "administration") {
			if(!($this->currentSprechtag['sprechtagID'] > 0)) {
				$this->noCurrentDay();
			}
			
			if($this->currentSprechtag['sprechtagIsActive'] == 0) {
				$this->noCurrentDay();
			}
			
		}
		
		if($this->currentSprechtag['sprechtagID'] > 0) $this->currentSprechtagID = $this->currentSprechtag['sprechtagID'];
		
		
		$sprechtagKlassen = explode(";;;",$this->currentSprechtag['sprechtagKlassen']);
		
		
		
		if(DB::getSession()->isEltern()) {
		    $grades = DB::getSession()->getElternObject()->getKlassenAsArray();
		    
		    $klasseOK = false;
		    
		    if($this->currentSprechtag['sprechtagKlassen'] == "") {
		        $klasseOK = true;
		    }
		    else {
		    
    		    for($i = 0; $i < sizeof($grades); $i++) {
    		 
    		        if(in_array($grades[$i], $sprechtagKlassen)) {
    		            $klasseOK = true;
    		            break;
    		        }
    		        
    		    }
		    }
		    
		    
		    if(!$klasseOK) {
		        new errorPage("Leider ist dieser Sprechtag nicht für Sie verfügbar, da Sie keine Kinder in folgenden Klassen haben: " . implode(", ", $sprechtagKlassen));
		    }
		    
		}
		
		switch($_GET['action']) {
		
			case "savebuchung":
				$this->addBuchung();
			break;
			
			case "deletebuchung":
				$this->deleteBuchung();
			break;
			
			case "elternPrint":
				if(DB::getSession()->isEltern()) {
					$this->elternPrint();
				}
				else {
					new errorPage("Nur für Eltern");
				}
			break;
			
			case 'showBuchungenForTeacher':
			    $this->JSONGetBuchungenForTeacher();
			break;
			
			case "teacherPrint":
				if(DB::getSession()->isTeacher()) {
					$this->teacherPrint();
				}
				else {
					new errorPage("Nur für Lehrer");
				}
			break;
						
			default:
				if(DB::getSession()->isTeacher()) {
					$this->teacherView();
				}
				elseif(DB::getSession()->isEltern()) {
					$this->elternView();
				}
				else {
					new errorPage("Sie sind weder Lehrer noch Eltern");
				}
			break;
		}
	}
	
	private function JSONGetBuchungenForTeacher() {
	    $teacher = lehrer::getByKuerzel($_REQUEST['kuerzel']);
	    
	    
	    $result = [
	        'belegung' => "n/a"
	    ];
	    
	    if($teacher != null) {
	        $kuerzel = $teacher->getKuerzel();
	        
	        $table = "";
	        
	        $data = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN sprechtag_slots NATURAL LEFT JOIN schueler WHERE sprechtagID='" . $this->currentSprechtagID . "' AND lehrerKuerzel = '" . $kuerzel . "' ORDER BY slotStart ASC");
	        
	        while($d = DB::getDB()->fetch_array($data)) {
	            
	            $table .= "<tr><td width=\"20%\">" . date("H:i",$d['slotStart']) . " Uhr bis " . date("H:i",$d['slotEnde']) . " Uhr";
	            
	            if($d['slotIsOnlineBuchbar'] == 0) {
	                $table .= "<br /><small>Nicht online buchbar</small></td><td>--</td></tr>";
	                continue;
	            }
	            
	            $table .= "</td>";
	            
	            if($d['slotIsPause'] > 0) {
	                $table .= "<td><i>Pause / Puffer</i></td>";
	            }
	            
	            else if($d['isBuchbar'] == 1) {
	                if($d['schuelerAsvID'] != "") {
	                    $table .= "<td>Belegt</td>";
	                }
	                else $table .= "<td><b>Frei</b></td>";
	            }
	            else {
	                $table .= "<td><i>Nicht buchbar / abwesend</i></td>";
	            }
	        }
	        
	        
	        $table = "<table class=\"table table-striped\">" . $table . "</table>";
	        $result['belegung'] = $table;
	    }
	    else {
	        $resut['belegung'] = "Lehrer unbekannt";
	    }

	    
	    
	    
	    
	    header("Content-type: text/json");
	    
	    echo json_encode($result);
	    exit(0);
	}
	
	private function deleteBuchung() {
		$slot = DB::getDB()->query_first("SELECT * FROM sprechtag_buchungen WHERE buchungID='" . intval($_GET['buchungID']) . "'");
		
		if($slot['buchungID'] > 0 && $slot['elternUserID'] == DB::getSession()->getUserID()) {
			DB::getDB()->query("UPDATE sprechtag_buchungen SET schuelerAsvID='', elternUserID=0 WHERE buchungID='" . intval($_GET['buchungID']) . "'");
			header("Location: index.php?page=elternsprechtag&sprechtagID=" . $this->currentSprechtagID);
		}
		else {
			new errorPage("Zeitfenster unbekannt!");
		}
	}
	
	private function addBuchung() {

	    $schueler = schueler::getByAsvID($_POST['schuelerAsvID']);

	    if($schueler === null) new errorPage("Schüler unbekannt!");

        $grades = $schueler->getKlasse();

        $klasseOK = false;

        if($this->currentSprechtag['sprechtagKlassen'] == "") {
            $klasseOK = true;
        }
        else {

            $sprechtagKlassen = explode(";;;",$this->currentSprechtag['sprechtagKlassen']);
            if(in_array($schueler->getKlasse(), $sprechtagKlassen)) {
                $klasseOK = true;
            }
        }


        if(!$klasseOK) {
            new errorPage("Sie können diesen Slot nicht für dieses Kind buchen, da es nicht in folgenden Klassen ist: " . implode(", ", $sprechtagKlassen));
        }

		
		$slot = DB::getDB()->query_first("SELECT * FROM sprechtag_buchungen WHERE slotID='" . intval($_GET['slotID']) . "' AND lehrerKuerzel='" . DB::getDB()->escapeString($_POST['lehrerKuerzel']) . "'");
		
		if($slot['buchungID'] > 0 && $this->sprechtagIsBuchbar && $slot['isBuchbar'] == 1) {

			
			if($slot['schuelerAsvID'] == "") {
				DB::getDB()->query("UPDATE sprechtag_buchungen SET schuelerAsvID='" . DB::getDB()->escapeString($_POST['schuelerAsvID']) . "', elternUserID='" . DB::getSession()->getUserID() . "' WHERE buchungID='" . $slot['buchungID'] . "'");
				
				header("Location: index.php?page=elternsprechtag&sprechtagID=" . $this->currentSprechtagID);
				exit(0);
			}
			else {
				new errorPage("Das Zeitfenster ist nicht verfügbar oder zwischenzeitlich reserviert worden!");
				exit(0);
			}
		
		}
		else {
		    header("Location: index.php?page=elternsprechtag&sprechtagID=" . $this->currentSprechtagID);
			exit(0);
		}
	}
	
	private function teacherPrint() {
	    
	    $pdf = new PrintNormalPageA4WithHeader('Buchungen bei ' . DB::getSession()->getTeacherObject()->getKuerzel());
	    
		$myBuchungen = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL LEFT JOIN schueler NATURAL JOIN lehrer WHERE lehrerAsvID='" . DB::getSession()->getTeacherObject()->getAsvID() . "'");
	
		$myBuchungenData = array();
		while($b = DB::getDB()->fetch_array($myBuchungen)) $myBuchungenData[] = $b;
	
		$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . $this->currentSprechtagID . "' ORDER BY slotStart ASC");
		$slotData = array();
		while($s = DB::getDB()->fetch_array($slots)) {
			$slotData[] = $s;
		}
	
		$table = "<tr><th width=\"35%\"><b>Zeit</b></th><th width=\"65%\"><b>Eltern von</b></th></tr>";
	
		$lehrer = array();
		$lehrerKuerzel = array();
		$lehrerKind = array();
	
		$gebuchteLehrer = array();
	
		for($i = 0; $i < sizeof($myBuchungenData); $i++) {
			$gebuchteLehrer[] = $myBuchungenData[$i]['lehrerKuerzel'];
		}
	
	
		$rooms = array();
	
		$roomsql = DB::getDB()->query("SELECT * FROM sprechtag_raeume WHERE sprechtagID='" . $this->currentSprechtagID . "' ORDER BY lehrerKuerzel ASC");
		while($s = DB::getDB()->fetch_array($roomsql)) {
			$rooms[$s['lehrerKuerzel']] = $s['raumName'];
		}
	
		$stundenplan = stundenplandata::getCurrentStundenplan();
	
		for($i = 0; $i < sizeof($slotData); $i++) {
		    
		    if($slotData[$i]['slotIsOnlineBuchbar'] == 0 && $slotData[$i]['slotIsPause'] == 0) {
		        $table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr<br /><i>Online nicht buchbar</i></td>";
		        $table .= "<td>&nbsp;</td></tr>";
		    }
		    
		    else if($slotData[$i]['slotIsPause'] == 0) {
				$table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
	
				$found = false;
				for($k = 0; $k < sizeof($myBuchungenData); $k++) {
					if($myBuchungenData[$k]['slotID'] == $slotData[$i]['slotID']) {
					    if($myBuchungenData[$k]['schuelerAsvID'] != "") {
    						$table .= "<td>
    								<b>" . $myBuchungenData[$k]['schuelerName'] . ", " . $myBuchungenData[$k]['schuelerRufname'] . " (Klasse " . $myBuchungenData[$k]['schuelerKlasse'] . ")</b>";
    	
    						$faecher = $stundenplan->getFachForTeacherAndSchueler($myBuchungenData[$k]['lehrerKuerzel'], $myBuchungenData[$k]['schuelerKlasse']);
    	
    						if(sizeof($faecher) > 0) $table .= "<br /><small>Fach: " . implode(", ",$faecher) . "</small>";
    	
    						$table .= "</td></tr>";
    	
					    }
					    else if($myBuchungenData[$k]['isBuchbar'] == 1) {
					        $table .= "<td>&nbsp;</td></tr>";
					    }
					    
					    else if($myBuchungenData[$k]['isBuchbar'] == 0) {
					        $table .= "<td><i>Nicht anwesend</i></td></tr>";
					    }
						$found = true;
						break;
					}
				}
	
				if(!$found) {
					$table .= "<td>&nbsp; (interner Fehler)</td></tr>";
				}
			}
			
			else {
				$table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
				$table .= "<td>Pause / Puffer</td></tr>";
			}
		}
		
		$raum = $rooms[DB::getSession()->getTeacherObject()->getKuerzel()];
	
		eval("\$print =\"" . DB::getTPL()->get("elternsprechtag/lehrer_print") . "\";");
	
	
		$pdf->setHTMLContent($print);
		$pdf->send();
	}
	
	private function elternPrint() {
		$myBuchungen = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN schueler NATURAL JOIN lehrer WHERE elternUserID='" . DB::getSession()->getUserID() . "'");
	
		$myBuchungenData = array();
		while($b = DB::getDB()->fetch_array($myBuchungen)) $myBuchungenData[] = $b;
	
		$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . $this->currentSprechtagID . "' ORDER BY slotStart ASC");
		$slotData = array();
		while($s = DB::getDB()->fetch_array($slots)) {
			$slotData[] = $s;
		}
	
		$table = "<tr><th width=\"35%\">Zeit</th><th width=\"65%\">Lehrer</th></tr>";
	
		$lehrer = array();
		$lehrerKuerzel = array();
		$lehrerKind = array();
	
		$gebuchteLehrer = array();
	
		for($i = 0; $i < sizeof($myBuchungenData); $i++) {
			$gebuchteLehrer[] = $myBuchungenData[$i]['lehrerKuerzel'];
		}
	
	
		$rooms = array();
	
		$roomsql = DB::getDB()->query("SELECT * FROM sprechtag_raeume WHERE sprechtagID='" . $this->currentSprechtagID . "' ORDER BY lehrerKuerzel ASC");
		while($s = DB::getDB()->fetch_array($roomsql)) {
			$rooms[$s['lehrerKuerzel']] = $s['raumName'];
		}
		
		$stundenplan = stundenplandata::getCurrentStundenplan();
		
		for($i = 0; $i < sizeof($slotData); $i++) {
			if($slotData[$i]['slotIsPause'] == 0) {
				$table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
	
				$found = false;
				for($k = 0; $k < sizeof($myBuchungenData); $k++) {
				    if($myBuchungenData[$k]['slotID'] == $slotData[$i]['slotID'] && $myBuchungenData[$k]['schuelerAsvID'] != "") {
						$table .= "<td>
								<b>" . $myBuchungenData[$k]['lehrerName'] . ", " . $myBuchungenData[$k]['lehrerRufname'] . " (" . $myBuchungenData[$k]['lehrerKuerzel'] . ")</b><br />Raum {$rooms[$myBuchungenData[$k]['lehrerKuerzel']]}<br />Schüler: " . $myBuchungenData[$k]['schuelerName'] . ", " . $myBuchungenData[$k]['schuelerRufname'] . " (Klasse " . $myBuchungenData[$k]['schuelerKlasse'] . ")<br />";
						
						$faecher = $stundenplan->getFachForTeacherAndSchueler($myBuchungenData[$k]['lehrerKuerzel'], $myBuchungenData[$k]['schuelerKlasse']);
	
						if(sizeof($faecher) > 0) $table .= "Fach: " . implode(", ",$faecher);
						
						$table .= "</td></tr>";
						
						
						
						$found = true;
						break;
					}
				}
	
				if(!$found) {
					$table .= "<td>&nbsp;</td></tr>";
						
				}
			}
			else {
				$table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
				$table .= "<td>Pause / Puffer</td></tr>";
			}
		}
	
		eval("\$print =\"" . DB::getTPL()->get("elternsprechtag/eltern_print") . "\";");
		
		$print = ($print);
		
		$pdf = new PrintNormalPageA4WithHeader('Sprechtag Buchungen');
		$pdf->setHTMLContent($print);
	
		$pdf->send();
		
	}

	private function elternView() {
		$myBuchungen = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN schueler NATURAL JOIN lehrer WHERE elternUserID='" . DB::getSession()->getUserID() . "'");
		
		$myBuchungenData = array();
		while($b = DB::getDB()->fetch_array($myBuchungen)) $myBuchungenData[] = $b;
		
		$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . $this->currentSprechtagID . "' ORDER BY slotStart ASC");
		$slotData = array();
		while($s = DB::getDB()->fetch_array($slots)) {
			$slotData[] = $s;
		}
		
		$table = "<tr><th width=\"20%\">Zeit</th><th>Lehrer</th></tr>";
		
		$lehrer = array();
		$lehrerKuerzel = array();
		$lehrerKind = array();
		
		$gebuchteLehrer = array();
		
		for($i = 0; $i < sizeof($myBuchungenData); $i++) {
			$gebuchteLehrer[] = $myBuchungenData[$i]['lehrerKuerzel'];
		}
		
		
		$rooms = array();
		
		$roomsql = DB::getDB()->query("SELECT * FROM sprechtag_raeume WHERE sprechtagID='" . $this->currentSprechtagID . "' ORDER BY lehrerKuerzel ASC");
		while($s = DB::getDB()->fetch_array($roomsql)) {
			$rooms[$s['lehrerKuerzel']] = $s['raumName'];
		}


        $alleKlassen = false;
        $sprechtagKlassen = [];

        if($this->currentSprechtag['sprechtagKlassen'] == "") {
            $alleKlassen = true;
        }
        else {
            $sprechtagKlassen = explode(";;;", $this->currentSprechtag['sprechtagKlassen']);
        }

		$optionsSchueler = "";
		
		$schueler = DB::getSession()->getElternObject()->getMySchueler();
		for($k = 0; $k < sizeof($schueler); $k++) {
		    if($alleKlassen || in_array($schueler[$k]->getKlasse(), $sprechtagKlassen)) {
                $optionsSchueler .= "<option value=\"" . $schueler[$k]->getAsvID() . "\">" . $schueler[$k]->getCompleteSchuelerName() . " (Klasse " . $schueler[$k]->getKlasse() . ")</option>";
                $klassen = stundenplandata::getCurrentStundenplan()->getAllMyPossibleGrades($schueler[$k]->getKlasse());

                for ($i = 0; $i < sizeof($klassen); $i++) {
                    $plan = stundenplandata::getCurrentStundenplan()->getPlan(array("grade", $klassen[$i]));

                    for ($s = 0; $s < sizeof($plan); $s++) {
                        for ($o = 0; $o < sizeof($plan[$s]); $o++) {
                            for ($d = 0; $d < sizeof($plan[$s][$o]); $d++) {
                                $label = $plan[$s][$o][$d]['teacher'] . " (" . $plan[$s][$o][$d]['grade'] . " in " . $plan[$s][$o][$d]['subject'] . ")";
                                if (!in_array($label, $lehrer)) $lehrer[] = $label;
                                if (!in_array($plan[$s][$o][$d]['teacher'], $lehrerKuerzel)) $lehrerKuerzel[] = $plan[$s][$o][$d]['teacher'];


                                $label2 = $plan[$s][$o][$d]['grade'] . " in " . $plan[$s][$o][$d]['subject'];

                                if (!is_array($lehrerKind[$plan[$s][$o][$d]['teacher']])) {
                                    $lehrerKind[$plan[$s][$o][$d]['teacher']] = array();
                                }

                                if (!in_array($label2, $lehrerKind[$plan[$s][$o][$d]['teacher']])) $lehrerKind[$plan[$s][$o][$d]['teacher']][] = $label2;
                            }
                        }
                    }
                }
            }
		}
		
		sort($lehrer);
		sort($lehrerKuerzel);
		

				
		for($i = 0; $i < sizeof($slotData); $i++) {
		    if($slotData[$i]['slotIsPause'] == 0 && $slotData[$i]['slotIsOnlineBuchbar'] == 1) {
				$table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
				
				$found = false;
				for($k = 0; $k < sizeof($myBuchungenData); $k++) {
					if($myBuchungenData[$k]['slotID'] == $slotData[$i]['slotID']) {
						$table .= "<td><b>Gebucht bei " . $myBuchungenData[$k]['lehrerName'] . ", " . $myBuchungenData[$k]['lehrerRufname'] . " (" . $myBuchungenData[$k]['lehrerKuerzel'] . ")</b><br />Raum {$rooms[$myBuchungenData[$k]['lehrerKuerzel']]}<br />Schüler: " . $myBuchungenData[$k]['schuelerName'] . ", " . $myBuchungenData[$k]['schuelerRufname'];
						if($this->sprechtagIsBuchbar) $table.= "<br /><a class=\"btn btn-danger\" href=\"index.php?page=elternsprechtag&sprechtagID=" . $this->currentSprechtagID . "&action=deletebuchung&buchungID=" . $myBuchungenData[$k]['buchungID'] . "\"><i class=\"fa fa-trash\"></i> Buchung löschen</a></td>";
	
						$found = true;
						break;
					}
				}
				
				if(!$found && $this->sprechtagIsBuchbar) {
					$avai = DB::getDB()->query("SELECT * FROM sprechtag_buchungen LEFT JOIN lehrer ON lehrer.lehrerKuerzel LIKE sprechtag_buchungen.lehrerKuerzel WHERE slotID='" . $slotData[$i]['slotID'] . "' AND isBuchbar=1 AND schuelerAsvID='' ORDER BY lehrerName ASC, lehrerRufname ASC");
					
					$table .= "<td><form action=\"index.php?page=elternsprechtag&action=savebuchung&sprechtagID=" . $this->currentSprechtagID . "&slotID=" . $slotData[$i]['slotID'] . "\" method=\"post\">";
					$table .= "<table border=\"0\"><tr><td>Kind:</td><td><select name=\"schuelerAsvID\" class=\"form-control\">" . $optionsSchueler . "</select>";
					
					$table .= "</td></tr><tr><td>Lehrer:</td><td><select name=\"lehrerKuerzel\" class=\"form-control\">";
						
					$avaiData = array();
					while($a = DB::getDB()->fetch_array($avai)) {
						$avaiData[] = $a;
					}
					
					$table .= "<option value=\"-\" style=\"background-color: #CDCDCD;text-color:#000000;font-style:italic\">Lehrerin oder Lehrer auswählen</option>";
						
					for($g = 0; $g < sizeof($avaiData); $g++) {
						$a = $avaiData[$g];
						// if(!is_array($lehrerKind[$a['lehrerKuerzel']]) && !in_array($a['lehrerKuerzel'],$gebuchteLehrer)) 
						$table .= "<option value=\"" . $a['lehrerKuerzel'] . "\"" . ((is_array($lehrerKind[$a['lehrerKuerzel']])) ? (" style=\"background-color: lightgreen\"") : ("")) . ">" . $a['lehrerName'] . ", " . $a['lehrerRufname'] . " (" . $a['lehrerKuerzel'] . ") (Raum " . $rooms[$a['lehrerKuerzel']] . ") " . ((is_array($lehrerKind[$a['lehrerKuerzel']])) ? ("<b>" . implode(", ", $lehrerKind[$a['lehrerKuerzel']]) . "</b>") : ("")) . "</option>";
					}
					
					$table .= "</select></td></tr><tr><td colspan=\"2\"><button type=\"submit\" class=\"btn btn-primary\"><i class=\"fa fa-save\"></i> Zeitfenster buchen</button></td></tr></table></form></td></tr>";
				}
				
				if(!$this->sprechtagIsBuchbar && !$found) {
					$table .= "<td>&nbsp;</td>";
				}
			}
			else if($slotData[$i]['slotIsOnlineBuchbar'] == 0) {
			    $table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
			    $table .= "<td><i>Nicht online buchbar</i></td></tr>";
			}
			else {
				$table .= "<tr><td>" . date("H:i",$slotData[$i]['slotStart']) . " Uhr bis " . date("H:i",$slotData[$i]['slotEnde']) . " Uhr</td>";
				$table .= "<td>Pause / Puffer</td></tr>";
			}
		}
		
		if($this->sprechtagIsBuchbar) {
		    $teacherSelect = "";
		    
		    $lehrer = lehrer::getAll();
		    
		    for($i = 0; $i < sizeof($lehrer); $i++) {
		        $teacherSelect .= "<option value=\"" . $lehrer[$i]->getKuerzel() . "\">" . $lehrer[$i]->getKuerzel() . " - " . $lehrer[$i]->getDisplayNameMitAmtsbezeichnung() . "</option>";
		    }
		}
		
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternsprechtag/eltern") . "\");");
		PAGE::kill(true);
			//exit(0);
	}
	
	private function teacherView() {
		$kuerzel = DB::getSession()->getTeacherObject()->getKuerzel();
		
		$table = "";
		
		$data = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN sprechtag_slots NATURAL LEFT JOIN schueler WHERE sprechtagID='" . $this->currentSprechtagID . "' AND lehrerKuerzel LIKE '" . $kuerzel . "' ORDER BY slotStart ASC");
		
		while($d = DB::getDB()->fetch_array($data)) {
			
			$table .= "<tr><td width=\"10%\">" . date("H:i",$d['slotStart']) . " Uhr bis " . date("H:i",$d['slotEnde']) . " Uhr";
			
			if($d['slotIsOnlineBuchbar'] == 0) {
			    $table .= "<br /><small>Nicht online buchbar</small>";
			}
			
			$table .= "</td>";
			
			if($d['slotIsPause'] > 0) {
				$table .= "<td><i>Pause / Puffer</i></td>";
			}
			
			else if($d['isBuchbar'] == 1) {
				if($d['schuelerAsvID'] != "") {
					$table .= "<td>" . $d['schuelerName'] . ", " . $d['schuelerRufname'] . " (Klasse " . $d['schuelerKlasse'] .")</td>";
				}
				else $table .= "<td><b>Frei</b></td>";
			}
			else {
				$table .= "<td><i>Nicht buchbar / abwesend</i></td>";
			}
		}
		
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternsprechtag/teacher") . "\");");
		PAGE::kill(true);
			//exit(0);
	}
	
	public static function displayAdministration($selfURL) {
		
		
	    if($_REQUEST['sprechtagID'] > 0) self::$currentSprechtagID = $_REQUEST['sprechtagID'];
	    else self::$currentSprechtagID = 0;
		
	    if(self::$currentSprechtagID > 0) {
	        self::$currentSprechtag = DB::getDB()->query_first("SELECT * FROM sprechtag WHERE sprechtagID='" . self::$currentSprechtagID . "'");
	    }
		
	    if(self::$currentSprechtagID > 0 && !(self::$currentSprechtag['sprechtagID'] > 0)) {
		    new errorPage("Ungültiger Sprechtag ausgewählt!");
		}
				
		if(self::$currentSprechtag['sprechtagBuchbarBis'] != "") {
			if(DateFunctions::isSQLDateTodayOrLater(self::$currentSprechtag['sprechtagBuchbarBis'])) {
				self::$sprechtagIsBuchbar = true;
			}
		}
		
		switch($_GET['mode']) {
			case "deleteSprechtag":
				if(self::$currentSprechtagID > 0) return self::deleteSprechtag($selfURL);
			break;
			
			case "editSlots":
				return self::editSlots($selfURL);				
			break;
			
			case 'editSprechtag':
			    return self::editSprechtag($selfURL);
			break;
			
			case "deleteslot":
				return self::deleteSlot($selfURL);
			break;
			
			case "addSlot":
				return self::addSlot($selfURL);
			break;
			
			case "editTeacher":
				return self::editTeacher($selfURL);
			break;
			
			case "addSprechtag":
				return self::addSprechtag($selfURL);
			break;
			
			case "unmarkBuchbarkeit":
				return self::unmarkBuchbarkeit($selfURL);
			break;
			
			case "markBuchbarkeit":
				return self::markBuchbarkeit($selfURL);
			break;
			
			case "massEditTeacher":
				return self::massEditTeacher($selfURL);
			break;
			
			case "printZettel":
				return self::printZettel($selfURL);
			break;
			
			case "activateSprechtag":
				return self::activateSprechtag($selfURL);
			break;
			
			case "deactivateSprechtag":
			    return self::deactivateSprechtag($selfURL);
			break;
			
			case 'changeSlotOnlineBookable':
			    return self::setOnlineBookableStateOfSlot($selfURL);
			break;
			
			case 'changeSlotIsPause':
			    return self::setIsPause($selfURL);
			break;
			
			case 'resetSprechtag':
			    return self::resetSprechtag($selfURL);
			break;
			
			case 'exportCSV':
			    return self::createCSV($selfURL);
			break;
			
			default:
				return self::showAdminIndex($selfURL);
			break;
		}
		
		return "Interner Fehler aufgetreten.";
	}
	
	
	private static function resetSprechtag($selfURL) {
	    DB::getDB()->query("UPDATE sprechtag_buchungen SET schuelerAsvID='', elternUserID=0 WHERE slotID IN (SELECT slotID FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "')");
	    
	    header("Location: $selfURL&sprechtagID=" . self::$currentSprechtagID);
	    exit(0);
	}
	
	private static function setOnlineBookableStateOfSlot($selfURL) {
	    DB::getDB()->query("UPDATE sprechtag_slots SET slotIsOnlineBuchbar='" . intval($_REQUEST['state']) . "' WHERE slotID='" . intval($_REQUEST['slotID']) . "'");
	    
	    
	    header("Location: $selfURL&mode=editSlots&sprechtagID=" . self::$currentSprechtagID);
	    exit();
	}
	
	private static function setIsPause($selfURL) {
	    DB::getDB()->query("UPDATE sprechtag_slots SET slotIsOnlineBuchbar='1', slotIsPause='" . intval($_REQUEST['state']) . "' WHERE slotID='" . intval($_REQUEST['slotID']) . "'");
	    
	    if($_REQUEST['state'] == 1) {
	        DB::getDB()->query("DELETE FROM sprechtag_buchungen WHERE slotID='" . intval($_REQUEST['slotID']) . "'");
	    }
	    else {
	        // Lehrer hinzufügen
	        $lehrer = lehrer::getAll();
	        
	        for($i = 0; $i < sizeof($lehrer); $i++) {
	            DB::getDB()->query("INSERT INTO sprechtag_buchungen (lehrerKuerzel, sprechtagID, slotID, isBuchbar) values('" . $lehrer[$i]->getKuerzel() . "','" . self::$currentSprechtagID . "','" .  intval($_REQUEST['slotID']) . "',1)");
	        }
	    }
	    
	    header("Location: $selfURL&mode=editSlots&sprechtagID=" . self::$currentSprechtagID);
	    exit();
	}
	
	private static function editSprechtag($selfURL) {
	    
	    $newName = $_POST['sprechtagName'];
	    
	    $date = DateFunctions::getMySQLDateFromNaturalDate($_POST['sprechtagDate']);
	    
	    
	    $bookAbleFromDate = explode("-",DateFunctions::getMySQLDateFromNaturalDate($_POST['sprechtagBuchbarAbDatum']));
	    $bookAbleFromTime = explode(":",$_POST['sprechtagBuchbarAbUhrzeit']);
	    $bookAbleFromTimestamp = mktime($bookAbleFromTime[0],$bookAbleFromTime[1],0,$bookAbleFromDate[1],$bookAbleFromDate[2],$bookAbleFromDate[0]);
	    
	    // Has Slots?
	    $slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "'");
	    if(DB::getDB()->num_rows($slots) == 0) {
	        $beginFirstSlotTime = explode(":",$_POST['sprechtagFirstSlotTime']);
	        $beginFirstSlotTimestamp = mktime($beginFirstSlotTime[0],$beginFirstSlotTime[1],0,3,25,1986);      // Erste Slot Daten immer am 25.03.1986 (Keine Verschiebung wegen Sommer/Winterzeit!)
	    }
	    else {
	        $beginFirstSlotTimestamp = self::$currentSprechtag['sprechtagBeginTime'];
	    }
	    
	    $bookAbleUntil = DateFunctions::getMySQLDateFromNaturalDate($_POST['sprechtagBuchbarBis']);
	    
	    $klassen = $_POST['sprechtagKlassen'];
	    
	    $saveKlassen = "";
	    if(sizeof($klassen) > 0) {
	        $saveKlassen = implode(";;;",$klassen);
	    }
	    
	    $perCentBookable = intval($_POST['sprechtagPercentSlotsOnlineBuchbar']);
	    
	    DB::getDB()->query("UPDATE sprechtag SET
            sprechtagName='" . DB::getDB()->escapeString($newName) . "',
            sprechtagDate='" . DB::getDB()->escapeString($date) . "',
            sprechtagBuchbarBis='" . DB::getDB()->escapeString($bookAbleUntil) . "',
	        sprechtagBeginTime='" . DB::getDB()->escapeString($beginFirstSlotTimestamp) . "',
            sprechtagBuchbarAb='" . DB::getDB()->escapeString($bookAbleFromTimestamp) . "',
            sprechtagKlassen='" . DB::getDB()->escapeString($saveKlassen) . "'

        WHERE sprechtagID='" . self::$currentSprechtagID . "'");
	    
	    header("Location: $selfURL&sprechtagID=" . self::$currentSprechtagID);
	    exit();
	}
	
	private static function activateSprechtag($selfURL) {
		DB::getDB()->query("UPDATE sprechtag SET sprechtagIsActive=1 WHERE sprechtagID='" . self::$currentSprechtagID . "'");
		
		header("Location: $selfURL&sprechtagID=" . self::$currentSprechtagID);
		exit();
	}
	
	private static function deactivateSprechtag($selfURL) {
	    DB::getDB()->query("UPDATE sprechtag SET sprechtagIsActive=0 WHERE sprechtagID='" . self::$currentSprechtagID . "'");
	    
	    header("Location: $selfURL&sprechtagID=" . self::$currentSprechtagID);
	    exit();
	}
	
	private static function createCSV($selfURL) {
	    $print = new PrintNormalPageA4WithHeader('Aushangezettel' . ($_REQUEST['noNames'] == 1) ? " ohne Namen" : "");
	    $print->showHeaderOnEachPage();
	    
	    
	    $lehrerMitSlotsSQL = DB::getDB()->query("SELECT * FROM lehrer WHERE lehrerKuerzel IN (SELECT DISTINCT lehrerKuerzel FROM  sprechtag_buchungen WHERE sprechtagID='" . self::$currentSprechtagID . "' AND isBuchbar=1) ORDER BY lehrerName ASC, lehrerRufname ASC");
	    
	    $lehrerMitSlots = array();
	    
	    while($l = DB::getDB()->fetch_array($lehrerMitSlotsSQL)) {
	        $lehrerMitSlots[] = $l;
	    }
	    
	    // Räume
	    $rooms = array();
	    $roomsql = DB::getDB()->query("SELECT * FROM sprechtag_raeume WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY lehrerKuerzel ASC");
	    while($s = DB::getDB()->fetch_array($roomsql)) {
	        $rooms[$s['lehrerKuerzel']] = $s['raumName'];
	    }
	    
	    
	    // Slots
	    $slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY slotStart ASC");
	    $slotData = array();
	    while($s = DB::getDB()->fetch_array($slots)) {
	        $slotData[] = $s;
	    }
	    
	    
	    $alleLehrer = "";
	    
	    $table = "";
	    
	    $anzahl = 0;
	    
	    for($i = 0; $i < sizeof($lehrerMitSlots); $i++) {
	        $slotsAnData = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN sprechtag_slots NATURAL LEFT JOIN schueler LEFT JOIN users ON sprechtag_buchungen.elternUserID=users.userID WHERE sprechtagID='" . self::$currentSprechtagID . "' AND lehrerKuerzel='" . $lehrerMitSlots[$i]['lehrerKuerzel'] . "' ORDER BY slotStart");
	        
	        while($s = DB::getDB()->fetch_array($slotsAnData)) {
	            $table .= $lehrerMitSlots[$i]['lehrerKuerzel'] . ";";
	            $table .=  $rooms[$lehrerMitSlots[$i]['lehrerKuerzel']] . ";";
	            
	            $table .= date("H:i",$s['slotStart']) . ";" . date("H:i",$s['slotEnde']);
	            $table .= ";";
	            
	            if($s['slotIsPause'] > 0) {
	                $table .= "Pause / Puffer;;;;";
	            }
	            else {
	               	                
	                
	                if($s['schuelerAsvID'] != "" && $_REQUEST['noNames'] != 1) {
	                    $table .= $s['schuelerName'] . ", " . $s['schuelerRufname'] . ";" . $s['schuelerKlasse'] . ";";
	                    $table .= $s['userEMail'] . ";";
	                }
                
	                
	                
	                if($s['isBuchbar'] > 0 && $s['schuelerAsvID'] == "") $table .= "frei;;;;";
	                else if($s['isBuchbar'] > 0) $table .= "";
	                else $table .= "nicht buchbar;;;;";
	            }
	            $table .= "\r\n";
	            
	        }
	        
	        


	        
	    }
	    
	    header("Content-type: text/csv");
	    header('Content-disposition: filename="Slots.csv"');
	    
	    echo utf8_decode($table);
	    exit(0);
	    
	}
	
	private static function printZettel($selfURL) {
		$print = new PrintNormalPageA4WithoutHeader('Aushangezettel' . ($_REQUEST['noNames'] == 1) ? " ohne Namen" : "");
		$print->showHeaderOnEachPage();

		
		$lehrerMitSlotsSQL = DB::getDB()->query("SELECT * FROM lehrer WHERE lehrerKuerzel IN (SELECT DISTINCT lehrerKuerzel FROM  sprechtag_buchungen WHERE sprechtagID='" . self::$currentSprechtagID . "' AND isBuchbar=1) ORDER BY lehrerName ASC, lehrerRufname ASC");
		
		$lehrerMitSlots = array();
		
		while($l = DB::getDB()->fetch_array($lehrerMitSlotsSQL)) {
			$lehrerMitSlots[] = $l;
		}
				
		// Räume	
		$rooms = array();
		$roomsql = DB::getDB()->query("SELECT * FROM sprechtag_raeume WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY lehrerKuerzel ASC");
		while($s = DB::getDB()->fetch_array($roomsql)) {
			$rooms[$s['lehrerKuerzel']] = $s['raumName'];
		}
		
		
		// Slots
		$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY slotStart ASC");
		$slotData = array();
		while($s = DB::getDB()->fetch_array($slots)) {
			$slotData[] = $s;
		}
		
		
		$alleLehrer = "";
		
		$table = "";
		
		$anzahl = 0;
		
		for($i = 0; $i < sizeof($lehrerMitSlots); $i++) {
			$slotsAnData = DB::getDB()->query("SELECT * FROM sprechtag_buchungen NATURAL JOIN sprechtag_slots NATURAL LEFT JOIN schueler LEFT JOIN users ON sprechtag_buchungen.elternUserID=users.userID WHERE sprechtagID='" . self::$currentSprechtagID . "' AND lehrerKuerzel='" . $lehrerMitSlots[$i]['lehrerKuerzel'] . "' ORDER BY slotStart");
			
			$table = "";
			while($s = DB::getDB()->fetch_array($slotsAnData)) {
				$table .= "<tr><td width=\"30%\" valign=\"center\">";
				
				$table .= date("H:i",$s['slotStart']) . " Uhr bis " . date("H:i",$s['slotEnde']) . " Uhr";
				$table .= "</td>";
				
				if($s['slotIsPause'] > 0) {
					$table .= "<td width=\"70%\"><i>Pause / Puffer</i></td></tr>";
				}
				else {
				    
					$table .= "<td width=\"70%\">";
					
					
					if($s['schuelerAsvID'] != "" && $_REQUEST['noNames'] != 1) {
						$table .= "<b>" . $s['schuelerName'] . ", " . $s['schuelerRufname'] . " (Klasse " . $s['schuelerKlasse'] . ")</b>";
						if($_REQUEST['showMail'] > 0) {
						    $table .= "<br />" . $s['userEMail'];
						}
					}
					
					if($s['schuelerAsvID'] != "" && $_REQUEST['noNames'] == 1) {
					    $table .= "<b>Belegt</b>";
					}
					
					
					
					if($s['isBuchbar'] > 0 && $s['schuelerAsvID'] == "") $table .= "&nbsp;</td></tr>";
					else if($s['isBuchbar'] > 0) $table .= "&nbsp;</td></tr>";
					else $table .= "<i>Nicht anwesend</i></td></tr>";
				}
				
			}
			


			eval("\$htmlLehrer = \"" . DB::getTPL()->get("elternsprechtag/admin/print_lehrer") . "\";");
			
			$print->setHTMLContent($htmlLehrer);
			
			// echo($lehrer);
			// $anzahl++;
			// echo("$anzahl<br />");
			
			// if($i != (sizeof($lehrerMitSlots)-1)) $mpdf->AddPage();
			
		}
		
		$print->send();
		
		exit(0);
		

	}
	
	private static function massEditTeacher($selfURL) {
		// Räume speichern
		
		$teacher = DB::getDB()->query("SELECT * FROM lehrer ORDER BY lehrerKuerzel ASC");
			
		$teacherData = array();
		while($t = DB::getDB()->fetch_array($teacher)) {
			DB::getDB()->query("INSERT INTO sprechtag_raeume (sprechtagID, lehrerKuerzel, raumname) values('" . self::$currentSprechtagID . "','" . $t['lehrerKuerzel'] . "','" . DB::getDB()->escapeString($_POST['raum_' . $t['lehrerKuerzel']]) . "') ON DUPLICATE KEY UPDATE raumName='" . DB::getDB()->escapeString($_POST['raum_' . $t['lehrerKuerzel']]) . "'");
		}
		
		
		// Slots
		
		$teacher = DB::getDB()->query("SELECT * FROM lehrer ORDER BY lehrerKuerzel ASC");
			
		$teacherData = array();
		while($t = DB::getDB()->fetch_array($teacher)) {
			$rooms[$t['lehrerKuerzel']] = "";
			$teacherData[] = $t;
		}
			
		$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY slotStart ASC");
		$slotData = array();
		while($s = DB::getDB()->fetch_array($slots)) {
			$slotData[] = $s;
		}
		
		
		for($i = 0; $i < sizeof($teacherData); $i++) {
			
			if($_POST["select_" . $teacherData[$i]['lehrerKuerzel']] > 0) {
				for($s = 0; $s < sizeof($slotData); $s++) {
				    
				    // Buchbarer Slot vorhanden?
				    
				    $singleSlot = DB::getDB()->query_first("SELECT * FROM sprechtag_buchungen WHERE sprechtagID='" . intval(self::$currentSprechtagID) . "' AND slotID='" . $slotData[$s]['slotID'] . "' AND lehrerKuerzel='" . $teacherData[$i]['lehrerKuerzel'] . "'");
				    
				    if($singleSlot['buchungID'] > 0) {
				        if($_POST["slot_" . $slotData[$s]['slotID']] > 0) {
				            
				            //
				            DB::getDB()->query("UPDATE sprechtag_buchungen SET isBuchbar=1 WHERE sprechtagID='" . intval(self::$currentSprechtagID) . "' AND slotID='" . $slotData[$s]['slotID'] . "' AND lehrerKuerzel='" . $teacherData[$i]['lehrerKuerzel'] . "'");
				        }
				        else {
				            DB::getDB()->query("UPDATE sprechtag_buchungen SET isBuchbar=0, schuelerAsvID='', elternUserID=0 WHERE sprechtagID='" . intval(self::$currentSprechtagID) . "' AND slotID='" . $slotData[$s]['slotID'] . "' AND lehrerKuerzel='" . $teacherData[$i]['lehrerKuerzel'] . "'");
				        }
				    }
				    else {
				        DB::getDB()->query("INSERT INTO sprechtag_buchungen (lehrerKuerzel, sprechtagID, slotID, isBuchbar) values(

                            '" . DB::getDB()->escapeString($teacherData[$i]['lehrerKuerzel']) . "',
                            '" . intval(self::$currentSprechtagID) . "',
                            '" . $slotData[$s]['slotID'] . "',
                            '" . ($_POST["slot_" . $slotData[$s]['slotID']] > 0) . "'
                        )");
				    }
				    

				}
			}
			
		}
	
		header("Location: $selfURL&mode=editTeacher&sprechtagID=" . self::$currentSprechtagID);
		exit();
		
	}
	
	private static function unmarkBuchbarkeit($selfURL) {
		DB::getDB()->query("UPDATE sprechtag_buchungen SET isBuchbar=0, schuelerAsvID='', elternUserID=0 WHERE buchungID='" . intval($_GET['buchungID']) . "'");
		
		header("Location: $selfURL&mode=editTeacher");
	}
	
	private static function markBuchbarkeit($selfURL) {
		DB::getDB()->query("UPDATE sprechtag_buchungen SET isBuchbar=1, schuelerAsvID='', elternUserID=0 WHERE buchungID='" . intval($_GET['buchungID']) . "'");
	
		header("Location: $selfURL&mode=editTeacher");
	}
	
	private static function editTeacher($selfURL) {
		if(self::$currentSprechtagID > 0) {
			
			$rooms = array();
			
			$newSlotsCreated = false;
				
			
			$teachers = lehrer::getAll();
			
			$teacherData = array();
			for($i = 0; $i < sizeof($teachers); $i++) {
			    $t = $teachers[$i]->getDataArray();
				$rooms[$t['lehrerKuerzel']] = "";
				$teacherData[] = $t;
			}
			
			$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY slotStart ASC");
			$slotData = array();
			while($s = DB::getDB()->fetch_array($slots)) {
				$slotData[] = $s;
			}
			
			// Räume
			
			$roomsql = DB::getDB()->query("SELECT * FROM sprechtag_raeume WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY lehrerKuerzel ASC");
			while($s = DB::getDB()->fetch_array($roomsql)) {
				$rooms[$s['lehrerKuerzel']] = $s['raumName'];
			}
			
			
			$table = "<tr><th><input type=\"checkbox\" class=\"icheckteacher\" id=\"checkAllTeacher\"></th>";
			$table .= "<th>&nbsp;</th>";
			$table .= "<th style=\"min-width:100px\">Raum</th>";
			
			for($i = 0; $i < sizeof($slotData); $i++) {
			    $table .= "<th><small>" . date("H:i",$slotData[$i]['slotStart']) . " - " . date("H:i",$slotData[$i]['slotEnde']) . (($slotData[$i]['slotIsPause'] > 0) ? ("<br /><b>Pause</b>") : ("")) . (($slotData[$i]['slotIsOnlineBuchbar'] == 0) ? ("<br /><b>Nicht online buchbar</b>") : ("")) . "</small></th>";
			}
			
			$table .= "</tr>";
			
			for($i = 0; $i < sizeof($teacherData); $i++) {
				
				$table .= "<tr><td>";
				
				$table .= "<input type=\"checkbox\" name=\"select_" . $teacherData[$i]['lehrerKuerzel'] . "\" value=\"1\" class=\"icheckteacher\"></td>";
				
		
				$table .= "<td>" . $teacherData[$i]['lehrerKuerzel'] . "<br /><small>" . $teacherData[$i]['lehrerName'] . ", " . $teacherData[$i]['lehrerRufname'] . "</small></td>";
				
				$table .= "<td><input type=\"text\" name=\"raum_" . $teacherData[$i]['lehrerKuerzel'] . "\" class=\"form-control\" placeholder=\"Raum\" value=\"" . $rooms[$teacherData[$i]['lehrerKuerzel']] . "\"></td>";
				
				$slotsTeacher = DB::getDB()->query("SELECT * FROM sprechtag_buchungen WHERE lehrerKuerzel LIKE '" . $teacherData[$i]['lehrerKuerzel'] . "'");
				$slotDataTeacher = array();
				
				while($sdt = DB::getDB()->fetch_array($slotsTeacher)) $slotDataTeacher[] = $sdt;
				
				for($s = 0; $s < sizeof($slotData); $s++) {
					if($slotData[$s]['slotIsPause'] > 0) {
						$table .= "<td>--</td>";
					}
					else {
						$table .= "<td>";
						
						$slotFound = false;
						
						for($g = 0; $g < sizeof($slotDataTeacher); $g++) {
							if($slotDataTeacher[$g]['slotID'] == $slotData[$s]['slotID']) {
								$table .= (($slotDataTeacher[$g]['isBuchbar'] > 0) ? "<font color=\"green\"><i class=\"fa fa-check\"></i></font>" : "<font color=\"red\"><i class=\"fa fa-ban\"></i></font>");
								if($slotDataTeacher[$g]['schuelerAsvID'] != "") $table .= "<br /><small>Bereits belegt</small>";
								$slotFound = true;
								break;
							}
						}
						
						
						if(!$slotFound) {
						    DB::getDB()->query("INSERT INTO sprechtag_buchungen (lehrerKuerzel, sprechtagID, slotID, isBuchbar) values(
                                    '" . $teacherData[$i]['lehrerKuerzel'] . "',
                                    '" . self::$currentSprechtagID . "',
                                    '" . $slotData[$s]['slotID'] . "',1)"
						    );
						    $newSlotsCreated = true;
						}
						
						
						$table .= "</td>";
					}
				}
				
			}
			
			if($newSlotsCreated) {
			    header("Location: index.php?page=administrationmodule&module=elternsprechtag&mode=editTeacher&sprechtagID=" . self::$currentSprechtagID);
			    exit(0);
			}
			
			$formTable = "<tr>";
			
			for($i = 0; $i < sizeof($slotData); $i++) {
			    $formTable .= "<td>" . date("H:i",$slotData[$i]['slotStart']) . " - " . date("H:i",$slotData[$i]['slotEnde']) . (($slotData[$i]['slotIsPause'] > 0) ? ("<br /><b>Pause</b>") : ("")) . (($slotData[$i]['slotIsOnlineBuchbar'] == 0) ? ("<br /><b>Nicht online buchbar</b>") : ("")) . "</td>";
			}
			
			$formTable .= "</tr><tr>";
				
			
			// Formulartabelle
			for($s = 0; $s < sizeof($slotData); $s++) {
				if($slotData[$s]['slotIsPause'] > 0) {
					$formTable .= "<td>--</td>";
				}
				else {
					$formTable .= "<td>";
			
					$formTable .= "<input type=\"checkbox\" name=\"slot_" . $slotData[$s]['slotID'] . "\" value=\"1\" class=\"icheckteacher\" checked=\"checked\">";
								
					$table .= "</td>";
				}
			}
			
			$formTable .= "</tr>";
			
			$html = "";
			
			$sprechtagID = self::$currentSprechtagID;
			
			eval("\$html = \"" . DB::getTPL()->get("elternsprechtag/admin/editteacher") . "\";");
			return $html;
		}
	}
	
	private static function addSlot($selfURL) {
		$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY slotStart ASC");
						
		$lastUhrzeit = self::$currentSprechtag['sprechtagBeginTime'];
		while($slot = DB::getDB()->fetch_array($slots)) {
			$lastUhrzeit = $slot['slotEnde'];
		}
		
		DB::getDB()->query("INSERT INTO sprechtag_slots (sprechtagID,slotStart, slotEnde, slotIsPause) values('" . self::$currentSprechtagID . "','" . $lastUhrzeit . "','" . ($lastUhrzeit + (intval($_POST['slotTime']) * 60)) . "','" . (($_POST['isPause'] > 0) ? ("1") : ("0")) . "')");
	
		$slotID = DB::getDB()->insert_id();
		
		// Lehrer hinzufügen
		$lehrer = lehrer::getAll();
		
		for($i = 0; $i < sizeof($lehrer); $i++) {
		    DB::getDB()->query("INSERT INTO sprechtag_buchungen (lehrerKuerzel, sprechtagID, slotID, isBuchbar) values('" . DB::getDB()->escapeString($lehrer[$i]->getKuerzel()) . "','" . self::$currentSprechtagID . "','" . $slotID . "',1)");
		}
		
		header("Location: $selfURL&mode=editSlots&sprechtagID=" . self::$currentSprechtagID);
		exit();
	}
	
	private static function deleteSlot($selfURL) {
		DB::getDB()->query("DELETE FROM sprechtag_slots WHERE slotID='" . intval($_REQUEST['slotID']) . "'");
		DB::getDB()->query("DELETE FROM sprechtag_buchungen WHERE slotID='" . intval($_REQUEST['slotID']) . "'");
		
		header("Location: $selfURL&mode=editSlots&sprechtagID=" . self::$currentSprechtagID);
		exit();
		
	}
	
	private static function editSlots($selfURL) {
		if(self::$currentSprechtagID > 0) {
						
			$slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "' ORDER BY slotStart ASC");
			
			$slotHTML .= "";
			
			
			$lastUhrzeit = self::$currentSprechtag['sprechtagBeginTime'];
			
			while($slot = DB::getDB()->fetch_array($slots)) {
				$slotHTML .= "<tR><td>" . date("H:i",$slot['slotStart']) . " Uhr - " . date("H:i",$slot['slotEnde']) . " Uhr";
				if($slot['slotIsPause'] > 0) $slotHTML .= " <b>Pause / Puffer (Nicht buchbar)</b>";				
				
				if($slot['slotIsOnlineBuchbar'] == 0) $slotHTML .= " <b>Online nicht buchbar</b>";
				
				
				$slotHTML .= "</td>";
				
				
				if($slot['slotIsPause'] > 0) {
				    $slotHTML .= "<td><i>entfällt</i></td>";
				}
				else {
				
    				if($slot['slotIsOnlineBuchbar'] > 0) {
    				    $slotHTML .= "<td><i class=\"fa fa-check\"></i> Online buchbar <a href=\"$selfURL&mode=changeSlotOnlineBookable&sprechtagID=" . self::$currentSprechtagID . "&slotID=" . $slot['slotID'] . "&state=0\" class=\"btn btn-primary btn-sm\"><i class=\"fa fas fa-sync-alt\"></i></a>";
    				}
    				else {
    				    $slotHTML .= "<td><i class=\"fa fa-ban\"></i> Online <u>nicht</u> buchbar <a href=\"$selfURL&mode=changeSlotOnlineBookable&sprechtagID=" . self::$currentSprechtagID . "&slotID=" . $slot['slotID'] . "&state=1\" class=\"btn btn-primary btn-sm\"><i class=\"fa fas fa-sync-alt\"></i></a>";
    				    
    				}
    				
				}
				if($slot['slotIsPause'] > 0) {
				    $slotHTML .= "<td><i class=\"fa fa-check\"></i> Ist Pause <a href=\"$selfURL&mode=changeSlotIsPause&sprechtagID=" . self::$currentSprechtagID . "&slotID=" . $slot['slotID'] . "&state=0\" class=\"btn btn-primary btn-sm\"><i class=\"fa fas fa-sync-alt\"></i></a>";
				}
				else {
				    $slotHTML .= "<td><i class=\"fa fa-ban\"></i> Ist <u>nicht</u> Pause <a href=\"$selfURL&mode=changeSlotIsPause&sprechtagID=" . self::$currentSprechtagID . "&slotID=" . $slot['slotID'] . "&state=1\" class=\"btn btn-primary btn-sm\"><i class=\"fa fas fa-sync-alt\"></i></a>";
				    
				}
				
				
				$slotHTML .= "<td><a href=\"$selfURL&mode=deleteslot&slotID=" . $slot['slotID'] . "&sprechtagID=" . self::$currentSprechtagID . "\" class=\"btn btn-danger\"><i class=\"fa fa-trash\"></i></a></td></tr>";
				$lastUhrzeit = $slot['slotEnde'];
			}
			
			$sprechtagID = self::$currentSprechtagID;
			
			$html = "";
			eval("\$html = \"" . DB::getTPL()->get("elternsprechtag/admin/editslots") . "\";");
			return $html;
		}
	}
	
	private static function showAdminIndex($selfURL) {
	    
	    
		$html = "";
		
		$sprechtagID = self::$currentSprechtagID;
		
		
		
		if(self::$currentSprechtagID == 0) {
		    $selectSprechtagListe = "";
		    
		    $sprechtagSQL = DB::getDB()->query("SELECT * FROM sprechtag");
		    while($s = DB::getDB()->fetch_array($sprechtagSQL)) {
		        $selectSprechtagListe .= "&raquo; <a href=\"$selfURL&sprechtagID=" . $s['sprechtagID'] . "\">" . $s['sprechtagName'] . " am " . DateFunctions::getNaturalDateFromMySQLDate($s['sprechtagDate']) . "</a><br />";
		    }
		}
		else {
		
		    $klassenOptionen = "";
		    
		    $isAllGrade = self::$currentSprechtag['sprechtagKlassen'] == 0;
		
		    $selected = explode(";;;",self::$currentSprechtag['sprechtagKlassen']);
		    
		    $klassen = klasse::getAllKlassen();
		    
		    for($i = 0; $i < sizeof($klassen); $i++) {
		        $klassenOptionen .= "<option value=\"" . $klassen[$i]->getKlassenName() . "\"" . (($isAllGrade || in_array($klassen[$i]->getKlassenName(), $selected)) ? "selected=\"selected\"" : "") . ">" . $klassen[$i]->getKlassenName() . "</option>";
		    }
		    
		    $slots = DB::getDB()->query("SELECT * FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "'");
		    if(DB::getDB()->num_rows($slots) > 0) {
		        $hasSlots = true;
		    }
		    else $hasSlots = false;
		    
		    if(self::$currentSprechtag['sprechtagBuchbarAb'] == 0) {
		        self::$currentSprechtag['sprechtagBuchbarAb'] = time();
		    }
		    
		
		}
		
		
		
		
		
		
		eval("\$html = \"" . DB::getTPL()->get("elternsprechtag/admin/index") . "\";");
		return $html;
	}
	
	private static function addSprechtag($selfURL) {
        DB::getDB()->query("INSERT INTO sprechtag (sprechtagName,sprechtagIsActive, sprechtagDate, sprechtagBuchbarBis) values('" . DB::getDB()->escapeString($_POST['sprechtagName']) . "',0, CURDATE(), CURDATE())");
				header("Location: $selfURL");
				exit(0);
	}
	
	private function deleteSprechtag($selfURL) {
		DB::getDB()->query("DELETE FROM sprechtag WHERE sprechtagID='" . self::$currentSprechtagID . "'");
		DB::getDB()->query("DELETE FROM sprechtag_slots WHERE sprechtagID='" . self::$currentSprechtagID . "'");
		DB::getDB()->query("DELETE FROM sprechtag_buchungen WHERE sprechtagID='" . self::$currentSprechtagID . "'");
		
		header("Location: $selfURL");
		exit(0);
	}
	
	private function noCurrentDay() {
		eval("DB::getTPL()->out(\"" . DB::getTPL()->get("elternsprechtag/noday") . "\");");
		PAGE::kill(true);
			//exit(0);
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
		return 'Elternsprechtag';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];	
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function hasAdmin() {
		return true;
	}
	
	public static function getAdminMenuGroup() {
		return 'Kleinere Module';
	}
	
	public static function getAdminGroup() {
		return 'Webportal_Elternsprechtag_Admin';
	}
	
}


?>