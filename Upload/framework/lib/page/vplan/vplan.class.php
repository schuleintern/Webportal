<?php

class vplan extends AbstractPage {
	
	private $noSearch = false;
	
	public function __construct() {
		parent::__construct ( array("Vertretungsplan"));
		
		$this->checkLogin();
		
		$access = $this->checkAccess();
		
		if(!$access) new errorPage();
				
	}
	
	private function checkAccess() {
	    if(DB::getSession()->isAdmin()) {
	        return true;
	    }
	    
	    if(DB::getSession()->isMember('Webportal_VPLanSehen')) {
	        return true;
	    }
	    
	    if(!DB::getSettings()->getBoolean("vplan-schueleractive")) {
	        if(DB::getSession()->isTeacher()) {
	            return true;
	        }
	    }
	    
	    if(DB::getSession()->isPupil() || DB::getSession()->isEltern()) {
	        return true;
	    }
	    
	    if(DB::getSession()->isTeacher()) return true;
	    
	    return false;
	}
	
	public function execute() {
		
		$see = array (
				'Schueler Heute',
				'Schueler Morgen' 
		);
		
		$vplanTH = "";

		
		if (DB::getSession ()->isTeacher() || (!DB::getSession()->isPupil() && !DB::getSession()->isEltern() && !DB::getSession()->isTeacher())) {
			$see [] = 'Lehrer Heute';
			$see [] = "Lehrer Morgen";
			if(DB::getSettings()->getValue("vplan-schueleractive") != 0) {
				$vplanTH = "<tr><th colspan=\"2\" style=\"padding: 10px; border-right: 1px solid; border-bottom: 1px solid; text-align: middle\"><i class=\"fa fa-child\"></i> Schülerplan</th><th colspan=\"2\" style=\"padding: 10px; border-bottom: 1px solid\"><i class=\"fa fa-male\"></i> Lehrerplan</th></tr>";
			}
			else {
				$vplanTH = "<tr><th colspan=\"2\" style=\"padding: 10px; border-bottom: 1px solid\"><i class=\"fa fa-male\"></i> Lehrerplan</th></tr>";
			}
		}
		
		if(!DB::getSession()->isPupil() && !DB::getSession()->isEltern() && !DB::getSession()->isTeacher()) $this->noSearch = true;
		

		if($_GET['vplanid'] == "") {
			if(DB::getSession()->isTeacher()) {
				$_GET['vplanid'] = 2;
			}
			else {
				$_GET['vplanid'] = 0;
			}
		}
		
		
		$check[] = array();
		
		for($i = 0; $i < sizeof ( $see ); $i ++) {
			if($i == 0 || $i == 1) {
				if(DB::getSettings()->getValue("vplan-schueleractive") == 0) continue;
			}
			if($see[$i] == 'Schueler Heute') $check[] = "schuelerheute";
			if($see[$i] == 'Schueler Morgen') $check[] = "schuelermorgen";
			if($see[$i] == 'Lehrer Heute') $check[] = "lehrerheute";
			if($see[$i] == 'Lehrer Morgen') $check[] = "lehrermorgen";
			
			$planname = $check[sizeof($check)-1];
			
			$plan = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='" . $check[sizeof($check)-1] . "'");
			$lastUpdate = $plan['vplanUpdate'];
			
			
			$date = $plan['vplanDate'];
            if ($date) {
                $date = explode(", ",$date);

                list($day, $month, $year) = explode(".",$date[1]);

                $timevplan = mktime(23,59,59,$month,$day,$year);

                if($timevplan > time()) {
                    $listPlan .= "<td width=\"" . ((DB::getSession ()->isTeacher() || (!DB::getSession()->isPupil() && !DB::getSession()->isEltern() && !DB::getSession()->isTeacher())) ? ("25%") : ("50%")) . "\" align=\"center\"" . (($_GET ['vplanid'] == $i) ? (" style=\"background-color:#D1D1D1; padding:10px" . (($i == 2) ? ("; border-left: 1px solid") : ("")) . "\"") : ((($i == 2) ? (" style=\"border-left: 1px solid\"") : ("")))) . "><a href=\"index.php?page=vplan&gplsession=&vplanid=$i\"><b>" . $plan['vplanDate'] . "</b><font size=\"-1\"><br />Aktualisierung: " . $lastUpdate . "</a></font></td>\n";
                } else {
                    if($planname == "schuelerheute" && $_GET['vplanid'] == 0) $_GET['vplanid'] = 1;
                    else if($planname == "schuelermorgen" && $_GET['vplanid'] == 1) $_GET['vplanid'] = -1;
                    else if($planname == "lehrerheute" && $_GET['vplanid'] == 2) $_GET['vplanid'] = 3;
                    else if($planname == "lehrermorgen" && $_GET['vplanid'] == 3) $_GET['vplanid'] = -1;

                    $listPlan .= "<td width=\"" . ((DB::getSession ()->isTeacher() == 'Lehrer') ? ("25%") : ("50%")) . "\" align=\"center\"> <i class=\"fa fa-ban\"></i></td>";
                }
            }
			
			
		}
		
		if(isset($_GET ['vplanid'] ) && $_GET ['vplanid'] > -1) {
			$deleteOwnDataTable = false;
			switch ($_GET['vplanid']) {
				case 0 :
					$name = "schuelerheute";
					if(DB::getSettings()->getBoolean('vplan-censor-schuelerheute'))
					    $censor = "<p>* Leider dürfen die Lehrerkürzel aus datenschutzrechtlichen Gründen im Internet nicht veröffentlicht werden.</p>";
				break;
				
				case 1 :
					$name = "schuelermorgen";
					if(DB::getSettings()->getBoolean('vplan-censor-schuelermorgen'))
					    $censor = "<p>* Leider dürfen die Lehrerkürzel aus datenschutzrechtlichen Gründen im Internet nicht veröffentlicht werden.</p>";
				break;
				
				case 2 :
					$name = "lehrerheute";
					if(DB::getSettings()->getBoolean('vplan-censor-lehrerheute'))
					    $censor = "<p>* Leider dürfen die Lehrerkürzel aus datenschutzrechtlichen Gründen im Internet nicht veröffentlicht werden.</p>";
				break;
				
				case 3 :
					$name = "lehrermorgen";
					if(DB::getSettings()->getBoolean('vplan-censor-lehrermorgen'))
					    $censor = "<p>* Leider dürfen die Lehrerkürzel aus datenschutzrechtlichen Gründen im Internet nicht veröffentlicht werden.</p>";
				break;
			}
			
			if(in_array($name, $check)) {
				$content = DB::getDB()->query_first("SELECT vplanContent FROM vplan WHERE vplanname='$name'");
				$content = ($content['vplanContent']);
				
				$info = DB::getDB()->query_first("SELECT vplanInfo FROM vplan WHERE vplanname='$name'");
				$info = $info['vplanInfo'];
				
				$ownData = array();
				
				$multisearch = false;
				
				// Eigene Daten suchen
				if($name == "lehrerheute" || $name == "lehrermorgen") {
					
					if(DB::getSession()->isTeacher()) {
						if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") $search = ">" . DB::getSession()->getTeacherObject()->getKuerzel() . "<";
						if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
							$search = ">" . DB::getSession()->getTeacherObject()->getName() . "<";
							if(DB::getGlobalSettings()->stundenplanSoftwareVersion == "2" || DB::getGlobalSettings()->stundenplanSoftwareVersion == "1") $search = DB::getSession()->getTeacherObject()->getKuerzel() . "<";
						}
						
						if(DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {
							$search = DB::getSession()->getTeacherObject()->getName();
						}

						if(DB::getGlobalSettings()->stundenplanSoftware == 'WILLI') {
                            $multisearch = true;
						    $search = [
						        DB::getSession()->getTeacherObject()->getKuerzel(),
                                DB::getSession()->getTeacherObject()->getName() . " " . DB::getSession()->getTeacherObject()->getRufname(),
                            ];
                        }
						
					}
					else $search = "";
					
					if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
					
						$header = "<table class=\"mon_list\" >
						<tr><td colspan=\"10\" align=\"center\"><p><font size=\"+2\">Vertretungen für Lehrer " . strtoupper(substr($search,1,1)) . substr($search,2,strlen($search)-3) . "</font></p></td></tr>";
						// $header .= '<tr class="list"><th class="list" align="center">Vertreter</th><th class="list" align="center">Stunde</th><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>';
						$header .= explode("\n",$content)[1];
						
					}
					else if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
      					$header = "<h4>Meine Vertretungen</h4><table class=\"table table-striped\"></tr><tr><th>Vertretung</th><th>Stunde</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</tr>\r\n</th></tr>";
					}
					
					else if(DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {
						$header = "<h4>Meine Vertretungen</h4><table class=\"table table-bordered\"><tr><th>Lehrer</th><th>Std.</th><th>Klasse</th><th>Fach</th><th>Raum</th><th>für</th><th>Bemerkung</th></tr>";
					}

                    else if(DB::getGlobalSettings()->stundenplanSoftware == "WILLI") {
                        $header = "<h4>Meine Vertretungen</h4><table class=\"table table-bordered\"><tr><th>Lehrkraft</th><th>Stunde</th><th>Klasse</th><th>Raum</th><th>Kommentar</th></tr>";
                    }
				}
				else {
						
					if(DB::getSession()->isPupil()) {
						$search = DB::getSession()->getSchuelerObject()->getKlasse();
					}
					else if(DB::getSession()->isEltern()) {
						$search = DB::getSession()->getElternObject()->getKlassenAsArray();
						$multisearch = true;
					}
					else {
						$deleteOwnDataTable = true;
					}
					
					if($multisearch) $searchText = implode(", ",$search);
					else $searchText = $search;
					
					$searchText = strtoupper($searchText);
					
					
					if(DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {
							
						$header = "<table class=\"mon_list\" >
						<tr><td colspan=\"10\" align=\"center\"><p><font size=\"+2\">Vertretungen für $searchText</font></td></tr>";
						// $header .= '<tr class="list"><th class="list" align="center">Klasse(n)</th><th class="list" align="center">Stunde</th><th class="list" align="center">Vertreter</th><th class="list" align="center">Fach</th><th class="list" align="center">Raum</th><th class="list" align="center">Art</th><th class="list" align="center">(Fach)</th><th class="list" align="center">(Lehrer)</th><th class="list" align="center">Vertr. von</th><th class="list" align="center">(Le.) nach</th></tr>';
						$header .= explode("\n",$content)[1];
					
					}
					else if(DB::getGlobalSettings()->stundenplanSoftware == "SPM++") {
						$header = "<h4>Meine Vertretungen</h4><table class=\"table table-striped\"><tr><th>Klasse</th><th>Vertretung</th><th>Stunde</th><th>Fach</th><th>Lehrer</th><th>Raum</th><th>Sonstiges</th></tr>";
					}
					
					else if(DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {
						$header = "<h4>Meine Vertretungen</h4><table class=\"table table-bordered\"><tr><th>Klasse</th><th>Std.</th><th>Lehrer/Fach</th><th>vertr. durch</th><th>Fach</th><th>Raum</th><th>Bemerkung</th></tr>";
					}

                    else if(DB::getGlobalSettings()->stundenplanSoftware == "WILLI") {
                        $header = "<h4>Meine Vertretungen</h4><table class=\"table table-bordered\"><tr><th>Klasse</th><th>Lehrkraft</th><th>Stunde</th><th>Vertreten durch</th><th>Raum</th></tr>";
                    }
					
					
					
					
				}
				
				$cont = explode("\n",str_replace("\r","",($content)));
				
				if(!$deleteOwnDataTable && !$this->noSearch ) {
					for($c = 0; $c < sizeof($cont); $c++) {
						if($multisearch) {
							for($s = 0; $s < sizeof($search); $s++) {
								if(strpos(strtolower($cont[$c]), strtolower($search[$s])) > 0) {
									$ownData[] = $cont[$c];
								}
							}
						}
						else {
							if(strpos(strtolower($cont[$c]), strtolower($search)) > 0) {
								$ownData[] = $cont[$c];
							}
						}
					}
					
				}
				if(sizeof($ownData) == 0) {
					$ownData = "<tr><td align=\"center\" colspan=\"10\">-- Keine --</td></tr>";
				}
				else {
					$ownData = implode("\r\n",$ownData);
				}
				
				$ownData .= "</table><br />";
				
				if($deleteOwnDataTable || $this->noSearch) {
					$ownData = "";
					$header = "";
				}
				
				if(is_array($search)) {
					for($i = 0; $i < sizeof($search); $i++) {
						$search[$i] = strtoupper($search[$i]);
					}
				}else $search = strtoupper($search);
				
				
				$content = ($content);
				
				$images = $content;
				
			}
			else {
				$images = "";
			}
		} else {
			$images = "&nbsp;";
		}
		
		eval ( "echo(\"" . DB::getTPL ()->get ( "vplan" ) . "\");" );
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
		return 'Vertretungsplan';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return array();
	
	}
	
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	public static function hasAdmin() {
		return false;
	}
	
	public static function userHasAccess($user) {
	    return DB::getSession()->isAdmin() || DB::getSession()->isPupil() || DB::getSession()->isEltern() || DB::getSession()->isTeacher() || DB::getSession()->isMember('Webportal_VPLanSehen');
	}
}
