<?php


/**
 * Aktualisiert den VPLan aus Time heraus
 * @author Christian
 *
 */
class TIMEUpdate {
	public static function updateLehrerFromFile($datei) {
		if(!file_exists($datei)) return;
		
		$data = self::getFileContents($datei);
		
		$plaene = self::getPlaene($data);
		
		// header("Content-type: text/plain");
		
		
		if(sizeof($plaene) > 0) {
			// Lehrer Heute
			$datum = self::getDatumFromPlan($plaene[0]);
			$info = self::getInfoBlockLehrer($plaene[0]);
			$plan = self::getVPlanFromPlan($plaene[0]);
			self::updatePlan('lehrerheute', $datum, $plan, $plan, $info);
		}
		
		if(sizeof($plaene) > 1) {
			// Lehrer Morgen
			$datum = self::getDatumFromPlan($plaene[1]);
			$info = self::getInfoBlockLehrer($plaene[1]);
			$plan = self::getVPlanFromPlan($plaene[1]);
			self::updatePlan('lehrermorgen', $datum, $plan, $plan, $info);
		}	}
	
	public static function updateSchuelerFromFile($datei) {
		if(!file_exists($datei)) return;
				
		$data = self::getFileContents($datei);
		
		$plaene = self::getPlaene($data);
		
		// header("Content-type: text/plain");
		
		
		if(sizeof($plaene) > 0) {
			// Lehrer Heute
			$datum = self::getDatumFromPlan($plaene[0]);
			$info = self::getInfoBlockLehrer($plaene[0]);
			$plan = self::getVPlanFromPlan($plaene[0], DB::getSettings()->getBoolean('vplan-censor-schuelerheute'));
			self::updatePlan('schuelerheute', $datum, $plan, $plan, $info);
		}
		
		if(sizeof($plaene) > 1) {
			// Lehrer Morgen
			$datum = self::getDatumFromPlan($plaene[1]);
			$info = self::getInfoBlockLehrer($plaene[1]);
			$plan = self::getVPlanFromPlan($plaene[1], DB::getSettings()->getBoolean('vplan-censor-schuelermorgen'));
			self::updatePlan('schuelermorgen', $datum, $plan, $plan, $info);
		}
	}
	
	
	private function updatePlan($name, $datum, $content, $contentUncensored, $info) {
		DB::getDB()->query("UPDATE vplan SET
                vplanContent='" . DB::getDB()->escapeString($content) . "',
                vplanContentUncensored = '" . DB::getDB()->escapeString($contentUncensored) . "',
                vplanUpdate='" . date("d.m.Y H:i") . "',vplanInfo='" . DB::getDB()->escapeString($info) . "',
                vplanDate='" . DB::getDB()->escapeString($datum) ."'
                     WHERE vplanName='$name'
                    ");
	}
	
	private static function getVPlanFromPlan($plan, $censor=false) {
		
		$save = false;
		$isVplanBlock = false;
		
		$data = [];
		
		$numberOfLineInVPlanBlock = -1;
				
		for($i = 0; $i < sizeof($plan); $i++) {
			
			$plan[$i] = str_replace("\r\n","",$plan[$i]);
			
			if($plan[$i] == "</table>\r\n" && $save) {
				$data[] = $plan[$i];
				$save = false;
			}
			
			if(strpos($plan[$i],'VBlock') > 0) {
				$save = true;
				$isVplanBlock = true;
			}
			
			if($save) {
			    $line = preg_replace("/ class=\"(.)*\"/", "", $plan[$i]);
			   			    
			    			    			    
			    if($censor && $isVplanBlock) {
			        $numberOfLineInVPlanBlock++;
			        
			        if($numberOfLineInVPlanBlock == 4) {
			            if($line != "<th>Lehrer/Fach</th>") {
			                $lineData = explode("/ ",$line);
			                $line = "<td>---*/" . $lineData[1];
			            }
			        }
			        
			        if($numberOfLineInVPlanBlock == 5) {
			            if($line != "<th>vertr. durch/Fach</th>") {
			                $line = "<td>---*</td>";
			            }
			        }
			        
			        if($numberOfLineInVPlanBlock == 2) {
			            // Klassenbezeichnungen trennen
			            if($line != "<th>Klasse</th>") {
			                $klasse = str_replace("<td>","",str_replace("</td>","",$line));
			                
			                if(strpos($klasse, ",") > 0) {
			                    
			                    $klassen = explode(",",$klasse);
			                    
			                    $jgs = 0;
			                    
			                    for($s = 4; $s <= 13; $s++) {
			                        // 13 Klassen suchen
			                        if($s < 10) $length = 1;
			                        else $length = 2;
			                        
			                        if(substr($klasse,0,$length) == $s) {
			                            // $s ist die Klasse
			                            $jgs = $s;
			                            break;
			                        }
			                    }
			                    
			                    for($b = 1; $b < sizeof($klassen); $b++) {
			                        $klassen[$b] = strtoupper($jgs . $klassen[$b]);
			                    }
			                    
			                    $klassen[0] = strtoupper($klassen[0]);
			                    
			                    
			                    $line = "<td>" . implode(", ",$klassen) . "</td>";
			                }
			                else $line = "<td>" . strtoupper($klasse) . "</td>";
			            }

			        }
			        
			        if($numberOfLineInVPlanBlock == 9) $numberOfLineInVPlanBlock = 0;
			    }
			    			    
			    // if($isVplanBlock && $firstInVplanBlock) $firstInVplanBlock = false;
			    
			    			    
			    $data[] = $line;
			}
			
		}
		
		$data = implode("",$data);
		$data = str_replace("<table>", "<table class=\"table table-bordered\">\r\n", $data);
		$data = str_replace("</tr>", "</tr>\r\n", $data);
		
		return $data;
	}
	
	
	private static function getFileContents($datei) {		
		$data = file($datei);
		
		for($i = 0; $i < sizeof($data); $i++) $data[$i] = utf8_encode($data[$i]);
		
		return $data;
	}
	
	private static function getDatumFromPlan($plan) {
		for($i = 0; $i < sizeof($plan); $i++) {
			if(strpos($plan[$i], 'Datum ohneumbruch') > 0) {
				return str_replace("\r\n","",str_replace("<td class=\"Datum ohneumbruch\">","",str_replace("</td>","",$plan[$i])));
			}
		}
	}
	
	private static function getInfoBlockLehrer($plan) {
		
		$save = false;
		
		$data = [];
		
		
		for($i = 0; $i < sizeof($plan); $i++) {
			
			if($plan[$i] == "</table>\r\n" && $save) {
				$data[] = $plan[$i];
				$save = false;
			}
			
			if(strpos($plan[$i],'VorspannBlock') > 0) {
				$save = true;
			}
			
			if(strpos($plan[$i],'BitteBeachtenBlock') > 0) {
				$save = true;
			}
			
			if($save) {
				$data[] = preg_replace("/ class=\"(.)*\"/", "", $plan[$i]);
			}
			
		}
		
		$data = implode("",$data);
		$data = str_replace("<table>", "<table class=\"table table-bordered\">", $data);
		
		return $data;
	}
	
	private static function getInfoBlockSchueler($plan) {
		
		$save = false;
		
		$data = [];
		
		
		for($i = 0; $i < sizeof($plan); $i++) {
			
			if($plan[$i] == "</table>\r\n" && $save) {
				$data[] = $plan[$i];
				$save = false;
			}

			if(strpos($plan[$i],'BitteBeachtenBlock') > 0) {
				$save = true;
			}
			
			if($save) {
				$data[] = preg_replace("/ class=\"(.)*\"/", "", $plan[$i]);
			}
			
		}
		
		$data = implode("",$data);
		$data = str_replace("<table>", "<table class=\"table table-bordered\">", $data);
		
		return $data;
	}
	
	private static function getPlaene($data) {
		$currentData = [];
		
		$plaene = [];
				
	
		$first = true;
		for($i = 0; $i < sizeof($data); $i++) {
			if($data[$i] == "<div>\r\n" || $data[$i] == "</div>\r\n") continue;
			
			if($data[$i] == "</html>\r\n" || $data[$i] == "</body>\r\n") continue;
			if(strpos($data[$i], "KBlock Kopf") > 0) {
				// Startpunkt
				if(sizeof($currentData) > 0) {
					
					if(!$first)	$plaene[] = $currentData;
					$currentData = [];
					$first = false;
				}
			}
			
			$currentData[] = $data[$i];
		}
		
		if(sizeof($currentData) > 0) {
			$plaene[] = $currentData;
			$currentData = [];
		}
		
		return $plaene;
	}
}