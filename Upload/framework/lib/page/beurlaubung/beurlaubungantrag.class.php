<?php


class beurlaubungantrag extends AbstractPage {
	
	/**
	 * Schüler, die der Benutzer beurlauben darf.
	 * @var schueler[]
	 */
	private $schueler = [];
		
	
	private $isSchulleitung = false;
	
	
	
	public function __construct() {
		parent::__construct ( array (
			"Beurlaubungsantrag" 
		) );
		
		$this->checkLogin();
		
		$accessOK = false;
		
		if(DB::getSession()->isEltern()) {
			$this->schueler = DB::getSession()->getElternObject()->getMySchueler();
			$accessOK = true;
		}
		
		if(DB::getSession()->isTeacher()) {
		    $this->isSchulleitung = DB::getSession()->getTeacherObject()->isSchulleitung();
		    $accessOK = true;
		}

		if(DB::getSession()->isAdmin() || DB::getSession()->isMember(self::getAdminGroup()) ) {
			$this->schueler = schueler::getAll('length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC');
			$accessOK = true;
		}

		if(DB::getSession()->isPupil()) {
			if(DB::getSettings()->getBoolean("beurlaubung-volljaehrige-schueler")) {
				$alter = DB::getSession()->getPupilObject()->getAlter();
				if($alter < 18 && !DB::getSettings()->getBoolean("beurlaubung-schueler")) {
					new errorPage("Schüler noch nicht volljährig!");
				}
				else {
					$this->schueler = [DB::getSession()->getPupilObject()];
					$accessOK = true;
				}
			}
			
			if(DB::getSettings()->getBoolean("beurlaubung-schueler")) {
				$this->schueler = [DB::getSession()->getPupilObject()];
			}
		}

		if(!$accessOK) {
			new errorPage();
		}
	}
	
	public function execute() {		
        switch($_REQUEST['mode']) {
						case 'schulleitung':
                $this->schulleitung();
            break;
            
            case 'schulleitungGenehmigung':
                $this->schulleitungGenehmigung();
            break;
            
            case 'klassenleitung':
                $this->klassenleitung();
            break;
            
            case 'KLGenehmigen':
                $this->klassenleitungGenehmigung(true);
            break;
            
            case 'KLNichtGenehmigen':
                $this->klassenleitungGenehmigung(false);
            break;
            
            
            case 'SLGenehmigen':
                $this->schulleitungGenehmigung(true);
            break;
                
            case 'SLNichtGenehmigen':
                $this->schulleitungGenehmigung(false);
            break;
            
            case 'addBeurlaubung':
                if(DB::getSession()->isEltern() || DB::getSession()->isPupil()) $this->addBeurlaubung();
            break;
            
            case 'printBeurlaubung':
                if(DB::getSession()->isEltern() || DB::getSession()->isPupil()) $this->printBeurlaubung();
            break;
            
            case 'deleteAntrag':
                if(DB::getSession()->isEltern() || DB::getSession()->isPupil()) $this->deleteBeurlaubung();
            break;
            
            default:
                $this->meineBeurlaubungen();
            break;
        }
	}
	
	private function schulleitungGenehmigung($genehmigen) {
	    $antrag = AbsenzBeurlaubungAntrag::getByID($_REQUEST['antragID']);
	    
	    $antrag->setSLGenehmigung($genehmigen, '');
	    
	    if($_REQUEST['informGroup'] != "") {
	        	        
	        $groupIDs = DB::getSettings()->getSelectedItems("beurlaubung-genehmigung-benachrichtigung-gruppen");
	        
	        /**
	         * 
	         * @var usergroup $groups
	         */
	        $groups = [];
	        
	        for($g = 0; $g < sizeof($groupIDs); $g++) {
	            $gr = usergroup::getGroupByChecksum($groupIDs[$g]);
	            
	            if($gr != null && $genehmigen && ($_REQUEST['informGroup'] == $groupIDs[$g] || $_REQUEST['informGroup'] == "all")) {
	                $groups[] = $gr;
	            }
	        }
	        
	        for($i = 0; $i < sizeof($groups); $i++) {
	            
	            $messageSender = new MessageSender();
	            $recipientHandler = new RecipientHandler("");
	            $recipientHandler->addRecipient(new GroupRecipient($groups[$i]->getName()));
	            
	            $messageSender->setRecipients($recipientHandler);
	            
	            $messageSender->setSender(new user(['userID' => 0]));
	            
	            $messageSender->setSubject('Neue genehmigte Beurlaubung');
	            $messageSender->setText('Folgende Beurlaubung wurde genehmigt:<br />Schüler:

                     ' . $antrag->getSchueler()->getCompleteSchuelerName() . ' (Klasse ' . $antrag->getSchueler()->getKlasse() . ')<br />
                    Datum: ' . DateFunctions::getNaturalDateFromMySQLDate($antrag->getStartDatumAsSQLDate()) . ' bis ' . DateFunctions::getNaturalDateFromMySQLDate($antrag->getEndDatumAsSQLDate()) . '<br />
                    Stunden: ' . implode(", ", $antrag->getStunden()) . '<br />
Begründung: ' . $antrag->getBegruendung() . "<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>");
	            
	            $messageSender->send();
	        }
	        
	        

	    }
	    
	    $messageSender = new MessageSender();
	    $recipientHandler = new RecipientHandler("");
	    $recipientHandler->addRecipient(new ParentRecipient($antrag->getSchueler()));
	        
	    $messageSender->setRecipients($recipientHandler);
	      
	    $messageSender->setSender(new user(['userID' => 0]));
	      
	    $messageSender->setSubject('Ihr Beurlaubungsantrag');
	    $messageSender->setText('Sehr geehrte Eltern von ' . $antrag->getSchueler()->getCompleteSchuelerName() . ',<br />Ihr Antrag auf Beurlaubung wurde ' . (!$genehmigen ? ' <b>nicht</b> ' : '') . ' von der Schulleitung genehmigt.<br />Für Details melden Sie sich bitte im Portal an und überprüfen Sie die Beurlaubung.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
	    
	    $messageSender->send();
	    
	    header("Location: index.php?page=beurlaubungantrag&mode=schulleitung");
	}
	
	private function klassenleitungGenehmigung($genehmigen) {
	    $antrag = AbsenzBeurlaubungAntrag::getByID($_REQUEST['antragID']);
	    
	    $antrag->setKLGenehmigung($genehmigen, '');
	    
	    $messageSender = new MessageSender();
	    $recipientHandler = new RecipientHandler("");
	    $recipientHandler->addRecipient(new ParentRecipient($antrag->getSchueler()));
	    
	    $messageSender->setRecipients($recipientHandler);
	    
	    $messageSender->setSender(new user(['userID' => 0]));
	    
	    $messageSender->setSubject('Ihr Beurlaubungsantrag');
	    $messageSender->setText('Sehr geehrte Eltern von ' . $antrag->getSchueler()->getCompleteSchuelerName() . ',<br />Ihr Antrag auf Beurlaubung wurde ' . (!$genehmigen ? ' <b>nicht</b> ' : '') . ' von der Klassenleitung genehmigt.<br />Für Details melden Sie sich bitte im Portal an und überprüfen Sie die Beurlaubung.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
	    
	    $messageSender->send();
	    
	    header("Location: index.php?page=beurlaubungantrag&mode=klassenleitung");	    
	}
	
	private function klassenleitung() {
	    if(DB::getSession()->isTeacher()) {
	        $meine = AbsenzBeurlaubungAntrag::getAllForKlassenleitung();
	        
	        $htmlMeine = "";
	        
	        for($i = 0; $i < sizeof($meine); $i++) {
	            	            
	            $schueler = schueler::getByAsvID($meine[$i]->getSchuelerAsvID());
	            
	            if($schueler != null) {
	                
	                $klasse = $schueler->getKlassenObjekt();
	                
	                if($klasse == null || !$klasse->isKlassenLeitung(DB::getSession()->getTeacherObject())) continue;
	                
	                
	                $schuelerName = $schueler->getCompleteSchuelerName() . " (Klasse " . $schueler->getKlasse() . ")";
	                
	                $datum = DateFunctions::getNaturalDateFromMySQLDate($meine[$i]->getStartDatumAsSQLDate());
	                
	                $begruendung = nl2br($meine[$i]->getBegruendung());
	                
	                $KLOK = false;
	                $SLOK = false;
	                $failed = false;
	                
	                if(DB::getSettings()->getBoolean("beurlaubung-klassenleitung-freigabe")) {
	                    if($meine[$i]->isKLDecisionMade()) {
	                        if($meine[$i]->isKLGenehmigt()) {
	                            $kl = "<label class=\"label label-success\">Genehmigt</label>";
	                            $kl .= "<form><button type=\"button\" class=\"btn btn-xs\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=KLNichtGenehmigen&antragID=" . $meine[$i]->getID() . "'\">Nicht Genehmigen</button></form>";
	                            
	                            $KLOK = true;
	                        }
	                        else {
	                            $failed = true;
	                            $kl = "<label class=\"label label-danger\">Nicht genehmigt</label>";
	                            $kl .= "<form><button type=\"button\" class=\"btn btn-xs\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=KLGenehmigen&antragID=" . $meine[$i]->getID() . "'\">Genehmigen</button></form>";
	                            
	                        }
	                        
	                        $kl .= "<br >" . nl2br($meine[$i]->getKLKommentar());
	                    }
	                    else {
	                        $kl = "<label class=\"label label-warning\">Genehmigung offen</label>";
	                        
	                        $kl .= "<form><button type=\"button\" class=\"btn btn-xs\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=KLGenehmigen&antragID=" . $meine[$i]->getID() . "'\">Genehmigen</button></form>";
	                        $kl .= "<form><button type=\"button\" class=\"btn btn-xs\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=KLNichtGenehmigen&antragID=" . $meine[$i]->getID() . "'\">Nicht Genehmigen</button></form>";
	                        
	                    }
	                    
	                }
	                else {
	                    $kl = "<label class=\"label label-success\">Nicht nötig</label>";
	                    $KLOK = true;
	                }
	                
	                if(DB::getSettings()->getBoolean("beurlaubung-schulleitung-freigabe")) {
	                    if($meine[$i]->isSLDecisionMade()) {
	                        if($meine[$i]->isSLGenehmigt()) {
	                            $sl = "<label class=\"label label-success\">Genehmigt</label>";
	                            $SLOK = true;
	                        }
	                        else {
	                            $failed = true;
	                            $sl = "<label class=\"label label-danger\">Nicht genehmigt</label>";
	                        }
	                        
	                        $sl .= "<br >" . nl2br($meine[$i]->getSLKommentar());
	                    }
	                    else $sl = "<label class=\"label label-warning\">Genehmigung offen</label>";
	                }
	                else {
	                    $sl = "<label class=\"label label-success\">Nicht nötig</label>";
	                    $SLOK = true;
	                }
	                
	                if($meine[$i]->getEndDatumAsSQLDate() != $meine[$i]->getStartDatumAsSQLDate()) {
	                    $datum .= " bis " . DateFunctions::getNaturalDateFromMySQLDate($meine[$i]->getEndDatumAsSQLDate());
	                }
	                
	                $status = "";
	                
	                $termine = Leistungsnachweis::getByClass([$schueler->getKlasse()], $meine[$i]->getStartDatumAsSQLDate(), $meine[$i]->getEndDatumAsSQLDate() );
	                
	                $termineHTML = '';
	                for($t = 0; $t < sizeof($termine); $t++) {
	                    if($termine[$t]->showForNotTeacher()){
	                        $hasTermin = true;
	                        $termineHTML .= "<li>" . DateFunctions::getNaturalDateFromMySQLDate($termine[$t]->getDatumStart()) . ": " . $termine[$t]->getArtLangtext() . " in " . $termine[$t]->getFach() . " bei " . $termine[$t]->getLehrer() . "</li>";
	                    }
	                }
	                
	                if($KLOK && $SLOK && $meine[$i]->isVerarbeitet()) $status = "Abgeschlossen.";
	                else if($KLOK && $SLOK && !$meine[$i]->isVerarbeitet()) $status = "Genehmigt. Noch nicht verarbeitet.";
	                else if($failed) $status = "Abgeschlossen. Nicht genehmigt.";
	                else $status = "Genehmigungen ausstehend.";
	                
	                $stunden = implode(", ", $meine[$i]->getStunden());
	                    
	                eval("\$meineHTML .= \"" . DB::getTPL()->get("absenzen/beurlaubungantrag/kl/bit") . "\";");
	            }
	            
	            
	        }
	        
	        $currentDate = DateFunctions::getTodayAsNaturalDate();
	        
	        $selectOptionsSchueler = "";
	        
	        for($i = 0; $i < sizeof($this->schueler); $i++) {
	            $selectOptionsSchueler .= "<option value=\"" . $this->schueler[$i]->getAsvID() . "\">" . $this->schueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
	        }
	        
	        $stundenauswahl = $this->getStundenAuswahl(functions::getIntArrayFromTill(1, DB::getSettings()->getValue("stundenplan-anzahlstunden")),true);
	        
	        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/beurlaubungantrag/kl/index") . "\");");
	        
	    }
	    else {
	        header("Location: index.php?page=beurlaubungantrag&mode=klassenleitung");
	        exit();
	    }
	}
	
	private function schulleitung() {
		if(DB::getSession()->isTeacher() || DB::getSession()->isMember(self::getAdminGroup()) ) {
            $meine = AbsenzBeurlaubungAntrag::getAllForSchulleitungOrKlassenleitung();

            $htmlMeine = "";

            for($i = 0; $i < sizeof($meine); $i++) {

                $schueler = schueler::getByAsvID($meine[$i]->getSchuelerAsvID());

                if($schueler != null) {

                    $klasse = $schueler->getKlassenObjekt();

                    $schuelerName = $schueler->getCompleteSchuelerName() . " (Klasse " . $schueler->getKlasse() . ")";

                    $datum = DateFunctions::getNaturalDateFromMySQLDate($meine[$i]->getStartDatumAsSQLDate());

                    $begruendung = nl2br($meine[$i]->getBegruendung());

                    $KLOK = false;
                    $SLOK = false;
                    $failed = false;

                    if(DB::getSettings()->getBoolean("beurlaubung-klassenleitung-freigabe")) {
                        if($meine[$i]->isKLDecisionMade()) {
                            if($meine[$i]->isKLGenehmigt()) {
                                $kl = "<label class=\"label label-success\">Genehmigt</label>";

                                $KLOK = true;
                            }
                            else {
                                $failed = true;
                                $kl = "<label class=\"label label-danger\">Nicht genehmigt</label>";

                            }

                            $kl .= "<br >" . nl2br($meine[$i]->getKLKommentar());
                        }
                        else {
                            $kl = "<label class=\"label label-warning\">Genehmigung offen</label>";


                        }

                    }
                    else {
                        $kl = "<label class=\"label label-success\">Nicht nötig</label>";
                        $KLOK = true;
                    }

                    if(DB::getSettings()->getBoolean("beurlaubung-schulleitung-freigabe")) {
                        if($meine[$i]->isSLDecisionMade()) {
                            if($meine[$i]->isSLGenehmigt()) {
                                $sl = "<label class=\"label label-success\">Genehmigt</label>";
                                $sl .= "<form><button type=\"button\" class=\"btn btn-xs\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLNichtGenehmigen&antragID=" . $meine[$i]->getID() . "'\">Nicht Genehmigen</button></form>";

                                $SLOK = true;
                            }
                            else {
                                $failed = true;
                                $sl = "<label class=\"label label-danger\">Nicht genehmigt</label>";
                                $sl .= "<form><button type=\"button\" class=\"btn btn-xs\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLGenehmigen&antragID=" . $meine[$i]->getID() . "'\">Genehmigen</button></form>";

                            }

                            $sl .= "<br >" . nl2br($meine[$i]->getSLKommentar());
                        }
                        else {



                            $groupIDs = DB::getSettings()->getSelectedItems("beurlaubung-genehmigung-benachrichtigung-gruppen");

                            $groups = [];

                            for($g = 0; $g < sizeof($groupIDs); $g++) {
                                $gr = usergroup::getGroupByChecksum($groupIDs[$g]);

                                if($gr != null) {
                                    $groups[] = $gr;
                                }
                            }




                            $sl = "<label class=\"label label-warning\">Genehmigung offen</label>";


                            for($g = 0; $g < sizeof($groups); $g++) {
                                $sl .= "<form><button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLGenehmigen&antragID=" . ($meine[$i]->getID()) . "&informGroup=" . md5($groups[$g]->getName()) . "'\"><i class=\"fa fa-check\"></i> Genehmigen und \"" . $groups[$g]->getName() . "\" benachrichtigen</button></form>";

                            }

                            if(sizeof($groups) > 0) {
                                $sl .= "<form><button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLGenehmigen&antragID=" . ($meine[$i]->getID()) . "&informGroup=all'\"><i class=\"fa fa-check\"></i> Genehmigen und alle obigen Gruppen benachrichtigen</button></form>";
                                $sl .= "<form><button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLGenehmigen&antragID=" . $meine[$i]->getID() . "'\"><i class=\"fa fa-check\"></i> Genehmigen (Ohne Benachrichtigung)</button></form>";

                            }
                            else {
                                $sl .= "<form><button type=\"button\" class=\"btn btn-xs btn-success\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLGenehmigen&antragID=" . $meine[$i]->getID() . "'\"><i class=\"fa fa-check\"></i> Genehmigen</button></form>";

                            }




                            $sl .= "<form><button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"window.location.href='index.php?page=beurlaubungantrag&mode=SLNichtGenehmigen&antragID=" . $meine[$i]->getID() . "'\"><i class=\"fa fa-ban\"></i> Nicht Genehmigen</button></form>";

                        }

                    }
                    else {
                        $sl = "<label class=\"label label-success\">Nicht nötig</label>";
                        $SLOK = true;
                    }

                    if($meine[$i]->getEndDatumAsSQLDate() != $meine[$i]->getStartDatumAsSQLDate()) {
                        $datum .= " bis " . DateFunctions::getNaturalDateFromMySQLDate($meine[$i]->getEndDatumAsSQLDate());
                    }

                    $status = "";

                    $termine = Leistungsnachweis::getByClass([$schueler->getKlasse()], $meine[$i]->getStartDatumAsSQLDate(), $meine[$i]->getEndDatumAsSQLDate() );

                    $termineHTML = '';
                    for($t = 0; $t < sizeof($termine); $t++) {
                        if($termine[$t]->showForNotTeacher()){
                            $hasTermin = true;
                            $termineHTML .= "<li>" . DateFunctions::getNaturalDateFromMySQLDate($termine[$t]->getDatumStart()) . ": " . $termine[$t]->getArtLangtext() . " in " . $termine[$t]->getFach() . " bei " . $termine[$t]->getLehrer() . "</li>";
                        }
                    }

                    if($KLOK && $SLOK && $meine[$i]->isVerarbeitet()) $status = "Abgeschlossen.";
                    else if($KLOK && $SLOK && !$meine[$i]->isVerarbeitet()) $status = "Genehmigt. Noch nicht verarbeitet.";
                    else if($failed) $status = "Abgeschlossen. Nicht genehmigt.";
                    else $status = "Genehmigungen ausstehend.";

                    $stunden = implode(", ", $meine[$i]->getStunden());

                    eval("\$meineHTML .= \"" . DB::getTPL()->get("absenzen/beurlaubungantrag/kl/bit") . "\";");
                }


            }

            $meineAlt = AbsenzBeurlaubungAntrag::getAllInPastForSchulleitungOrKlassenleitung();

            $htmlErledigt = "";

            for($i = 0; $i < sizeof($meineAlt); $i++) {

                $schueler = schueler::getByAsvID($meineAlt[$i]->getSchuelerAsvID());

                if($schueler != null) {

                    $klasse = $schueler->getKlassenObjekt();

                    $schuelerName = $schueler->getCompleteSchuelerName() . " (Klasse " . $schueler->getKlasse() . ")";

                    $datum = DateFunctions::getNaturalDateFromMySQLDate($meineAlt[$i]->getStartDatumAsSQLDate());

                    $begruendung = nl2br($meineAlt[$i]->getBegruendung());

                    $KLOK = false;
                    $SLOK = false;
                    $failed = false;

                    if(DB::getSettings()->getBoolean("beurlaubung-klassenleitung-freigabe")) {
                        if($meineAlt[$i]->isKLDecisionMade()) {
                            if($meineAlt[$i]->isKLGenehmigt()) {
                                $kl = "<label class=\"label label-success\">Genehmigt</label>";

                                $KLOK = true;
                            }
                            else {
                                $failed = true;
                                $kl = "<label class=\"label label-danger\">Nicht genehmigt</label>";

                            }

                            $kl .= "<br >" . nl2br($meineAlt[$i]->getKLKommentar());
                        }
                        else {
                            $kl = "<label class=\"label label-warning\">Genehmigung offen</label>";


                        }

                    }
                    else {
                        $kl = "<label class=\"label label-success\">Nicht nötig</label>";
                        $KLOK = true;
                    }

                    if(DB::getSettings()->getBoolean("beurlaubung-schulleitung-freigabe")) {
                        if($meineAlt[$i]->isSLDecisionMade()) {
                            if($meineAlt[$i]->isSLGenehmigt()) {
                                $sl = "<label class=\"label label-success\">Genehmigt</label>";

                                $SLOK = true;
                            }
                            else {
                                $failed = true;
                                $sl = "<label class=\"label label-danger\">Nicht genehmigt</label>";

                            }

                            $sl .= "<br >" . nl2br($meineAlt[$i]->getSLKommentar());
                        }
                        else {
                            $groupIDs = DB::getSettings()->getSelectedItems("beurlaubung-genehmigung-benachrichtigung-gruppen");

                            $groups = [];

                            for($g = 0; $g < sizeof($groupIDs); $g++) {
                                $gr = usergroup::getGroupByChecksum($groupIDs[$g]);

                                if($gr != null) {
                                    $groups[] = $gr;
                                }
                            }




                            $sl = "<label class=\"label label-warning\">Genehmigung offen</label>";

                        }

                    }
                    else {
                        $sl = "<label class=\"label label-success\">Nicht nötig</label>";
                        $SLOK = true;
                    }

                    if($meineAlt[$i]->getEndDatumAsSQLDate() != $meineAlt[$i]->getStartDatumAsSQLDate()) {
                        $datum .= " bis " . DateFunctions::getNaturalDateFromMySQLDate($meineAlt[$i]->getEndDatumAsSQLDate());
                    }

                    $status = "";

                    $termine = Leistungsnachweis::getByClass([$schueler->getKlasse()], $meineAlt[$i]->getStartDatumAsSQLDate(), $meineAlt[$i]->getEndDatumAsSQLDate() );

                    $termineHTML = '';
                    for($t = 0; $t < sizeof($termine); $t++) {
                        if($termine[$t]->showForNotTeacher()){
                            $hasTermin = true;
                            $termineHTML .= "<li>" . DateFunctions::getNaturalDateFromMySQLDate($termine[$t]->getDatumStart()) . ": " . $termine[$t]->getArtLangtext() . " in " . $termine[$t]->getFach() . " bei " . $termine[$t]->getLehrer() . "</li>";
                        }
                    }

                    if($KLOK && $SLOK && $meineAlt[$i]->isVerarbeitet()) $status = "Abgeschlossen.";
                    else if($KLOK && $SLOK && !$meineAlt[$i]->isVerarbeitet()) $status = "Genehmigt. Noch nicht verarbeitet.";
                    else if($failed) $status = "Abgeschlossen. Nicht genehmigt.";
                    else $status = "Genehmigungen ausstehend.";

                    $stunden = implode(", ", $meineAlt[$i]->getStunden());

                    eval("\$htmlErledigt .= \"" . DB::getTPL()->get("absenzen/beurlaubungantrag/kl/bit") . "\";");
                }


            }
	        
	        $currentDate = DateFunctions::getTodayAsNaturalDate();
	        
	        $selectOptionsSchueler = "";
	        
	        for($i = 0; $i < sizeof($this->schueler); $i++) {
	            $selectOptionsSchueler .= "<option value=\"" . $this->schueler[$i]->getAsvID() . "\">" . $this->schueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
	        }
	        
	        $stundenauswahl = $this->getStundenAuswahl(functions::getIntArrayFromTill(1, DB::getSettings()->getValue("stundenplan-anzahlstunden")),true);
	        
	        eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/beurlaubungantrag/kl/index") . "\");");
	        
	    } else {
	        header("Location: index.php?page=beurlaubungantrag&mode=schulleitung");
	        exit();
	    }
	}
	
	private function deleteBeurlaubung() {
	    $antrag = AbsenzBeurlaubungAntrag::getByID($_GET['antragID']);
	    
	    if($antrag == null) new errorPage();
	    if($antrag->getUserID() != DB::getSession()->getUserID()) new errorPage();
	    
	    $antrag->delete();
	    
	    header("Location: index.php?page=beurlaubungantrag");
	    exit(0);
	}
	
	private function printBeurlaubung() {
	    $antrag = AbsenzBeurlaubungAntrag::getByID($_GET['antragID']);
	    
	    if($antrag == null) new errorPage();
	    if($antrag->getUserID() != DB::getSession()->getUserID()) new errorPage();
	    
	    eval("\$html = \"" . DB::getTPL()->get('absenzen/beurlaubungantrag/eltern/print') . "\";");
	    
	    $print = new PrintNormalPageA4WithHeader('Antrag Beurlaubung');
	    $print->setHTMLContent($html);
	    $print->setPrintedDateInFooter();
	    $print->send();	    
	}
	
	private function addBeurlaubung() {
	    $schueler = null;
	    for($i = 0; $i < sizeof($this->schueler); $i++) {
	        if($_REQUEST['schuelerAsvID'] == $this->schueler[$i]->getAsvID()) {
	            $schueler = $this->schueler[$i];
	            break;
	        }
	    }
	    
	    if($schueler == null) new errorPage();
	    
	    $success = false;
	    
	    $datum = $_REQUEST['bu_zeit'];
	    
	    $dates = explode(" bis ",$datum);
	    
	    if(sizeof($dates) != 2) new errorPage("Ungültige Datumsangabe (2)");
	    
	    if(!DateFunctions::isNaturalDate($dates[0]) || !DateFunctions::isNaturalDate($dates[1])) {
	        new errorPage("Ungültige Datumsangabe");
	    }
	    
	    if(!DateFunctions::isNaturalDateAfterAnother($dates[1], $dates[0])) {
	        new errorPage("Ungültige Datumsangabe (Endtag nicht vor Starttag!)");
	    }
	    
	    if(!DateFunctions::isNaturalDateTodayOrLater($dates[0]) || !DateFunctions::isNaturalDateTodayOrLater($dates[1])) {
	        new errorPage("Zeitangaben müssen in der Zukunft liegen");
	    }
	    
	    $termine = Leistungsnachweis::getByClass([$schueler->getKlasse()], $dates[0], $dates[1] );
	    
	    $termineHTML = '';
	    
	    $hasTermin = false;
	    
	    for($i = 0; $i < sizeof($termine); $i++) {
	        if($termine[$i]->showForNotTeacher()){
	            $hasTermin = true;
	            $termineHTML .= "<li>" . DateFunctions::getNaturalDateFromMySQLDate($termine[$i]->getDatumStart()) . ": " . $termine[$i]->getArtLangtext() . " in " . $termine[$i]->getFach() . " bei " . $termine[$i]->getLehrer() . "</li>";
	        }
	    }
	    
	    if(DB::getSettings()->getBoolean('beurlaubung-lnw-sperre') && $hasTermin) {
	       // Kein Antrag, da LNW eingetragen
	       $success = false;	       
	    }
	    else $success = true;
	    
	    $needZustimmung = false;
	    
	    if(DB::getSettings()->getBoolean('beurlaubung-klassenleitung-freigabe') || DB::getSettings()->getBoolean('beurlaubung-schulleitung-freigabe')) {
	        $needZustimmung = true;
	    }
	    	    
	    $needPrint = DB::getSettings()->getBoolean('beurlaubung-ausdruck-erforderlich');
	    
	    
	    $stunden = [];
	    for($i = 1; $i <= DB::getSettings()->getValue("stundenplan-anzahlstunden"); $i++) if($_REQUEST['stunde'.$i] > 0) $stunden[] = $i;
	    
	    
	    $newID = AbsenzBeurlaubungAntrag::create(DateFunctions::getMySQLDateFromNaturalDate($dates[0]), DateFunctions::getMySQLDateFromNaturalDate($dates[1]), $schueler->getAsvID(), $_POST['begruendung'], $stunden);
	    
	    $stundenDisplay = implode(", ",$stunden);
	    $begruendung = htmlspecialchars($_POST['begruendung']);
	    
	   
	    if(DB::getSettings()->getBoolean('beurlaubung-schulleitung-freigabe') && DB::getSettings()->getBoolean('beurlaubung-schulleitung-nachricht')) {
	        $messageSender = new MessageSender();
	        $recipientHandler = new RecipientHandler("");
	        $recipientHandler->addRecipient(new SchulleitungRecipient());
	        
	        $messageSender->setRecipients($recipientHandler);
	        
	        $messageSender->setSender(new user(['userID' => 0]));
	        
	        $messageSender->setSubject('Neuer Beurlaubungsantrag');
	        $messageSender->setText('Es liegt ein neuer Beurlaubungsantrag vor. Bitte prüfen.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
	        $messageSender->send();
	    }
	    
	    if(DB::getSettings()->getBoolean('beurlaubung-klassenleitung-freigabe') && DB::getSettings()->getBoolean('beurlaubung-klassenleitung-nachricht')) {
	        $messageSender = new MessageSender();
	        $recipientHandler = new RecipientHandler("");
	        $recipientHandler->addRecipient(new KlassenleitungRecipient($schueler->getKlasse()));
	        
	        $messageSender->setRecipients($recipientHandler);
	        
	        $messageSender->setSender(new user(['userID' => 0]));
	        
	        $messageSender->setSubject('Neuer Beurlaubungsantrag');
	        $messageSender->setText('Es liegt ein neuer Beurlaubungsantrag vor. Bitte prüfen.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
	        $messageSender->send();
	    }
	    
	    
	    
	    
	    eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/beurlaubungantrag/eltern/result") . "\");");
	    
	    // header("Location: index.php?page=beurlaubungantrag");
	    PAGE::kill(true);
			//exit(0);
	}
	
	private function meineBeurlaubungen() {
	    if(DB::getSession()->isEltern()) {
	        $meine = AbsenzBeurlaubungAntrag::getAllForUser(DB::getSession()->getUserID());
	        
            $htmlMeine = "";
            
            for($i = 0; $i < sizeof($meine); $i++) {
                $schueler = schueler::getByAsvID($meine[$i]->getSchuelerAsvID());
                
                if($schueler != null) {
                    $schuelerName = $schueler->getCompleteSchuelerName() . " (Klasse " . $schueler->getKlasse() . ")";
                    
                    $datum = DateFunctions::getNaturalDateFromMySQLDate($meine[$i]->getStartDatumAsSQLDate());
                    
                    $begruendung = nl2br($meine[$i]->getBegruendung());
                    
                    $KLOK = false;
                    $SLOK = false;
                    $failed = false;
                    
                    if(DB::getSettings()->getBoolean("beurlaubung-klassenleitung-freigabe")) {
                        if($meine[$i]->isKLDecisionMade()) {
                            if($meine[$i]->isKLGenehmigt()) {
                                $kl = "<label class=\"label label-success\">Genehmigt</label>";
                                $KLOK = true;
                            }
                            else {
                                $failed = true;
                                $kl = "<label class=\"label label-danger\">Nicht genehmigt</label>";
                            }
                            
                            $kl .= "<br >" . nl2br($meine[$i]->getKLKommentar());
                        }
                        else $kl = "<label class=\"label label-warning\">Genehmigung offen</label>";
                    }
                    else {
                        $kl = "<label class=\"label label-success\">Nicht nötig</label>";
                        $KLOK = true;
                    }
                    
                    if(DB::getSettings()->getBoolean("beurlaubung-schulleitung-freigabe")) {
                        if($meine[$i]->isSLDecisionMade()) {
                            if($meine[$i]->isSLGenehmigt()) {
                                $sl = "<label class=\"label label-success\">Genehmigt</label>";
                                $SLOK = true;
                            }
                            else {
                                $failed = true;
                                $sl = "<label class=\"label label-danger\">Nicht genehmigt</label>";
                            }
                            
                            $sl .= "<br >" . nl2br($meine[$i]->getSLKommentar());
                        }
                        else $sl = "<label class=\"label label-warning\">Genehmigung offen</label>";
                    }
                    else {
                        $sl = "<label class=\"label label-success\">Nicht nötig</label>";
                        $SLOK = true;
                    }
                    
                    if($meine[$i]->getEndDatumAsSQLDate() != $meine[$i]->getStartDatumAsSQLDate()) {
                        $datum .= " bis " . DateFunctions::getNaturalDateFromMySQLDate($meine[$i]->getEndDatumAsSQLDate());
                    }
                    
                    $status = "";
                    
                    if($KLOK && $SLOK && $meine[$i]->isVerarbeitet()) $status = "Abgeschlossen.";
                    else if($KLOK && $SLOK && !$meine[$i]->isVerarbeitet()) $status = "Genehmigt. Noch nicht verarbeitet.";
                    else if($failed) $status = "Abgeschlossen. Nicht genehmigt.";
                    else $status = "Genehmigungen ausstehend.";
                    
                    $stunden = implode(", ", $meine[$i]->getStunden());
                    
                    if(!$SLOK || !$KLOK || !$meine[$i]->isVerarbeitet()) 
                        $cancel = "<form><button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"confirmAction('Soll der Antrag wirklich zurückgezogen bzw. gelöscht werden?','index.php?page=beurlaubungantrag&mode=deleteAntrag&antragID={$meine[$i]->getID()}')\"><i class=\"fa fa-trash\"></i> Antrag zurückziehen</button></form>";
                    else $cancel = "";
                    
                    if(DB::getSettings()->getBoolean('beurlaubung-ausdruck-erforderlich')) {
                        $ausdruck = '<button type="butto"n class="btn btn-xs btn-info" onclick="window.location.href=\'index.php?page=beurlaubungantrag&mode=printBeurlaubung&antragID=' . $meine[$i]->getID() . '\'"><i class="fa fa-print"></i> Antrag ausdrucken</button>';
                    }
                    else {
                        $ausdruck = '';
                    }
                    
                    eval("\$meineHTML .= \"" . DB::getTPL()->get("absenzen/beurlaubungantrag/eltern/bit") . "\";");
                }
                
                
            }
	        
            $currentDate = DateFunctions::getTodayAsNaturalDate();
            
            $selectOptionsSchueler = "";
            
            for($i = 0; $i < sizeof($this->schueler); $i++) {
                $selectOptionsSchueler .= "<option value=\"" . $this->schueler[$i]->getAsvID() . "\">" . $this->schueler[$i]->getCompleteSchuelerName() . "</option>\r\n";
            }
            
            $stundenauswahl = $this->getStundenAuswahl(functions::getIntArrayFromTill(1, DB::getSettings()->getValue("stundenplan-anzahlstunden")),true);
            
            eval("DB::getTPL()->out(\"" . DB::getTPL()->get("absenzen/beurlaubungantrag/eltern/index") . "\");");        
	        
	    }
	    else {
	        if(DB::getSession()->isTeacher()) {
	            if(DB::getSession()->getTeacherObject()->isSchulleitung()) {
	                header("Location: index.php?page=beurlaubungantrag&mode=schulleitung");
	                exit();
	            }
	            else {
    	           header("Location: index.php?page=beurlaubungantrag&mode=klassenleitung");
    	           exit();
	            }
	        }
	        else {
	            new errorPage("Keine Möglichkeit.");
            }
	    }
	}
	
	public static function hasSettings() {
		return true;
	}
	
	public static function getSettingsDescription() {
	    
	    $userGroups = usergroup::getAllOwnGroups();
	    
	    $options = [];
	    
	    for($i = 0; $i < sizeof($userGroups); $i++) {
	        $options[] = [
	            'value' => md5($userGroups[$i]->getName()),
	            'name' => $userGroups[$i]->getName()
	        ];
	    }
	    
	    
		return [
			[
				'name' => "beurlaubung-volljaehrige-schueler",
				'typ' => 'BOOLEAN',
				'titel' => "Beurlaubung durch volljährige Schüler aktivieren",
				'text' => "Ist diese Einstellung aktiv, können sich volljährige Schüler selbst krank melden."
			],
			[
				'name' => "beurlaubung-schueler",
				'typ' => 'BOOLEAN',
				'titel' => "Beurlaubung durch Schüler aktivieren",
				'text' => "Ist diese Einstellung aktiv, können sich Schüler selbst krank melden. (Auch die unter 18 Jahren!)"
			],
			[
				'name' => "beurlaubung-lnw-sperre",
				'typ' => 'BOOLEAN',
				'titel' => "An Tagen mit angekündigtem Leistungsnachweis keine Beurlaubung erlauben",
				'text' => "Wenn diese Option aktiv ist, dann kann für Tage, an denen ein Leistungsnachwei angekündigt ist, keine Beurlaubung eingereicht werden."
			],
		    [
		        'name' => "beurlaubung-termin-sperre",
		        'typ' => 'BOOLEAN',
		        'titel' => "An Tagen mit Klassenterminen keine Beurlaubung erlauben",
		        'text' => "Wenn diese Option aktiv ist, dann kann für Tage, an denen ein Klassentermin angekündigt ist, keine Beurlaubung eingereicht werden."
		    ],
		    [
		        'name' => "beurlaubung-klassenleitung-freigabe",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beurlaubungen müssen von der Klassenleitung freigegeben werden?",
		        'text' => ""
		    ],
		    [
		        'name' => "beurlaubung-klassenleitung-nachricht",
		        'typ' => 'BOOLEAN',
		        'titel' => "Klassenleitung bei neuen Anträgen per Nachricht informieren?",
		        'text' => ""
		    ],
		    [
		        'name' => "beurlaubung-schulleitung-freigabe",
		        'typ' => 'BOOLEAN',
		        'titel' => "Beurlaubungen müssen von der Schulleitung freigegeben werden?",
		        'text' => ""
		    ],
		    [
		        'name' => "beurlaubung-schulleitung-nachricht",
		        'typ' => 'BOOLEAN',
		        'titel' => "Schulleitung bei neuen Anträgen per Nachricht informieren?",
		        'text' => ""
		    ],
		    
		    [
		        'name' => "beurlaubung-ausdruck-erforderlich",
		        'typ' => 'BOOLEAN',
		        'titel' => "Muss nach dem Beurlaubungsantrag ein schriftlicher Antrag ausgedruckt und eingereicht werden?",
		        'text' => ""
		    ],
		    
		    [
		        'name' => "beurlaubung-genehmigung-benachrichtigung-gruppen",
		        'typ' => 'SELECT',
		        'options' => $options,
		        'multiple' => true,
		        'titel' => "Folgende Gruppen können optional bei der Genehmigung über eine Beurlaubung informiert werden.",
		        'text' => "Sind hier Gruppen ausgewählt, kann der Schulleitung bei der Eingabe einer Genehmigung option die Infos zur Beurlaubung an diese Gruppen automatisch senden. (z.B. zur Information der Nachmittagsbetreuung)"
		    ],
		];
	}
	
	
	public static function getSiteDisplayName() {
		return 'Online Beurlaubung';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	}
	
	public static function hasAdmin() {
		return true;
	}

	public static function getAdminMenuGroup() {
		return "Absenzenverwaltung";
	}

	public static function dependsPage() {
		return ['absenzensekretariat', 'absenzenberichte','absenzenstatistik'];
	}
	
	public static function userHasAccess($user) {
	    
	    if(DB::getSession()->isTeacher()) {
	        return true;
	    }
	
		if(DB::getSession()->isEltern()) {
			return true;
		}
		
		if(DB::getSession()->isAdmin()) {
			return true;
		}
		
		if(DB::getSession()->isPupil()) {
			
			if(DB::getSettings()->getBoolean("beurlaubung-schueler")) {
				return true;
			}
			
			if(DB::getSettings()->getBoolean("beurlaubung-volljaehrige-schueler")) {
				$alter = DB::getSession()->getPupilObject()->getAlter();
				return $alter >= 18;
			}
			

		}
		
		return false;
	}
	
	private $idStundeSelect = 1;
	
	private function getStundenAuswahl($selected=array(), $hideButtons=false) {
	    $html = "<table class=\"table table-striped\"><tr><td>";
	    $maxStunden = DB::getSettings()->getValue("stundenplan-anzahlstunden");
	    
	    
	    $html .= "Vormittag <button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectVormittag(" . $this->idStundeSelect . "," . $maxStunden . ");\">Auswählen</button><br />";
	    
	    
	    
	    
	    
	    
	    for($i = 1; $i <= 6; $i++) {
	        $html .= "
            <input type=\"checkbox\" name=\"stunde" . $i . "\" value=\"1\" id=\"stunde_" . $i . "_" . $this->idStundeSelect . "\"" . (in_array($i,$selected) ? ("checked=\"checked\"") : ("")) . "> <label for=\"stunde_" . $i . "_" . $this->idStundeSelect . "\">$i. Stunde</label>
            ";
	    }
	    
	    $html .= "</td></tr><tr><td>";
	    
	    $html .= "Nachmittag <button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectNachmittag(" . $this->idStundeSelect . "," . $maxStunden . ");\">auswählen</button><br />";
	    
	    for($i = 7; $i <= $maxStunden; $i++) {
	        $html .= "
            <input type=\"checkbox\" name=\"stunde" . $i . "\" value=\"1\" id=\"stunde_" . $i . "_" . $this->idStundeSelect . "\"" . (in_array($i,$selected) ? ("checked=\"checked\"") : ("")) . "> <label for=\"stunde_" . $i . "_" . $this->idStundeSelect . "\">$i. Stunde</label>
            ";
	    }
	    
	    $html .= "</td></tr>";
	    
	    if(!$hideButtons) {
	        
	        $html .= "<tr><td>";
	        $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectNothing(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-ban\"></i> Keine Stunden auswählen</button> ";
	        $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:bisJetzt(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-left\"></i> Bis jetzt</button> ";
	        $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:abJetzt(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-right\"></i> Ab jetzt</button> ";
	        $html .= "<button type=\"button\" class=\"btn btn-xs\" onclick=\"javascript:selectAll(" . $this->idStundeSelect . "," . $maxStunden . ");\"><i class=\"fa fa-arrow-up\"></i> Alle auswählen</button></td></tr>";
	        
	    }
	    
	    $html .= "</table>";
	    
	    $this->idStundeSelect++;
	    
	    
	    return $html;
	}


	public static function getAdminGroup() {
		return 'Webportal_Beurlaubungantrag_Admin';
	}

}

?>