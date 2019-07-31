<?php



class digitalSignage extends AbstractPage {
	
	public function __construct() {
		parent::__construct ( array (
			"Digitaler Schaukasten" 
		),true );
		
	}
	
	public function execute() {
		$xml = new SimpleXMLElement('<schuleinterndigitalsignage/>');

		
		$key = DB::getSettings()->getValue("digitalSignage-key");
		
		$deviceName = DB::getDB()->escapeString($_GET['deviceName']);
		$deviceID = DB::getDB()->escapeString($_GET['deviceID']);
		$resolutionX = intval($_GET['resolutionX']);
		$resolutionY = intval($_GET['resolutionY']);
		
		
		if($_GET['DSKey'] != $key) {
			$xml->addChild("errorID",9);
			$xml->addChild("errorText","invalid Key");
		}
		
		$xml->addChild("deviceID",$deviceID);
		$xml->addChild("deviceName",$deviceName);
		$xml->addChild("resolutionX",$resolutionX);
		$xml->addChild("resolutionY",$resolutionY);
		
		$screen = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenSystemName='" . $deviceName . "' AND schaukastenSystemID='" . $deviceID . "'");
		
		if($screen['schaukastenID']  > 0) {
			if($resolutionX > $resolutionY) $mode = "L";
			else $mode = "P";
					
			DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenLastUpdate=UNIX_TIMESTAMP(), schaukastenHasPPT='" . ($_REQUEST['hasPPT']>0 ? 1 : 0) . "', schaukastenResolutionX='" . $resolutionX . "', schaukastenResolutionY='" . $resolutionY . "', schaukastenMode='$mode' WHERE schaukastenID='" . $screen['schaukastenID'] . "'");
		}
		
		switch($_GET['action']) {
			case 'registerScreen':
				if($screen['schaukastenID'] == "") {
					// Anlegen
					DB::getDB()->query("INSERT INTO schaukasten_bildschirme (
							schaukastenSystemName,
							schaukastenSystemID,
							schaukastenName,
							schaukastenResolutionX,
							schaukastenResolutionY,
							schaukastenAdded,
                            schaukastenHasPPT
						) values (
							'" . $deviceName . "',
							'" . $deviceID . "',
							'n/a',
							'$resolutionX',
							'$resolutionY',
							UNIX_TIMESTAMP(),
					       '" . ($_REQUEST['hasPPT']>0 ? 1 : 0)  . "'
						)");

				}
				
				$xml->addChild("registerOK");
			break;
			
			case 'getCurrentScreen':
				if($screen['schaukastenID'] > 0) {
					if($screen['schaukastenIsActive'] > 0) {
	
						$recreateFile = false;
						
						if($screen['schaukastenLastContentUpdate'] == 0 || $screen['schaukastenLastContentUpdate'] == '') {
							$recreateFile = true;
						}
						
						$sectionContents = [];
						
						
						switch($screen['schaukastenLayout']) {
							case 'layout1':
								$anzahlFelder = 1;
								break;
								
							case 'layout2':
								$anzahlFelder = 2;
								break;
								
							case 'layout3':
								$anzahlFelder = 3;
								break;
						}
						
						
						
						// if(!$recreateFile) {
							// Inhalt aktueller?
							
							$contentSQL = DB::getDB()->query("SELECT * FROM schaukasten_inhalt WHERE schaukastenID='" . $screen['schaukastenID'] . "'");
							$content = [];
							
							while($c = DB::getDB()->fetch_array($contentSQL)) $content[$c['schaukastenPosition']] = $c['schaukastenContent'];
							
							$contentFiles = [];
							
                            $pptFile = null;
							
							for($f = 1; $f <= $anzahlFelder; $f++) {
							    
							    $name = $content[$f];
							    
							    if(substr($name, 0, 3) == 'PPT') {
							        $ppt = DB::getDB()->query_first("SELECT * FROM schaukasten_powerpoint WHERE powerpointID='" . substr($name, 3) . "'");
							        if($ppt['powerpointID'] > 0) {	
							            $pptFile2 = FileUpload::getByID($ppt['uploadID']);
							            							           
							            if($pptFile2 != null) $pptFile = $pptFile2;
							           							            
							            if($ppt['lastUpdate'] > $screen['schaukastenLastContentUpdate']) {
							                if($pptFile2 != null) {
							                    $pptFile = $pptFile2;
							                    $recreateFile = true;
							                }
							            }							            
							        }
							        
							        $sectionContents[] = $name;
							        break;   // Nur ein Feld bei PPT
							    }
							    else if(substr($name, 0, 2) == 'WS') {
							        $website = DB::getDB()->query_first("SELECT * FROM schaukasten_website WHERE websiteID='" . substr($name, 2) . "'");
							        if($website['websiteID'] > 0) {
							            
							            
							           if($website['websiteLastUpdate'] > $screen['schaukastenLastContentUpdate']) {
							                
							                
							                
							                $websiteURL = $website['websiteURL'];
							                
							                $milliseconds = 0;
							                
							                if($website['websiteRefreshSeconds'] > 0) {
							                    $doReload = true;
							                    $milliseconds = $website['websiteRefreshSeconds'] * 1000;
							                }
							                else {
							                    $doReload = false;
							                }
							                
							                eval("\$contentFiles[$f] = \"" . DB::getTPL()->get("digitalSignage/display/website") . "\";");
							                
							                $recreateFile = true;
							           }
							        }
							        
							        
							        $sectionContents[] = $content[$f];
							    }
							    else {
							        $sectionContents[] = $content[$f];
							        
    								switch($content[$f]) {
    									case 'lehrerheute':
    									case 'lehrermorgen':
    									
    									case 'schuelerheute':
    									case 'schuelermorgen':
    									
    										$plan = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='" . $content[$f] . "'");
    										if($plan['vplanUpdateTime'] > $screen['schaukastenLastContentUpdate']) {
    											$recreateFile = true;
    										}
    										
    										eval("\$contentFiles[$f] = \"" . DB::getTPL()->get("digitalSignage/display/vplan") . "\";"); // $plan['vplanContentUncensored'];	")								
    									break;
    									
    									case 'lehrerheutemorgen':
    										$plan1 = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='lehrerheute'");
    										$plan2 = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='lehrermorgen'");
    										if($plan1['vplanUpdateTime'] > $screen['schaukastenLastContentUpdate']) {
    											$recreateFile = true;
    										}
    										
    										if($plan2['vplanUpdateTime'] > $screen['schaukastenLastContentUpdate']) {
    											$recreateFile = true;
    										}
    										
    										eval("\$contentFiles[$f] = \"" . DB::getTPL()->get("digitalSignage/display/vplandoppel") . "\";"); // $plan['vplanContentUncensored'];	")	
    									break;
    									
    									case 'schuelerheutemorgen':
    										$plan1 = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='schuelerheute'");
    										$plan2 = DB::getDB()->query_first("SELECT * FROM vplan WHERE vplanName='schuelermorgen'");
    										
    										if($plan1['vplanUpdateTime'] > $screen['schaukastenLastContentUpdate']) {
    											$recreateFile = true;
    										}
    										
    										if($plan2['vplanUpdateTime'] > $screen['schaukastenLastContentUpdate']) {
    											$recreateFile = true;
    										}
    										
    										eval("\$contentFiles[$f] = \"" . DB::getTPL()->get("digitalSignage/display/vplandoppel") . "\";"); // $plan['vplanContentUncensored'];	")	
    									break;
    								}
							    }
								
							}
							
						// }
						
						
						$dir = 'dscontent/' . $screen['schaukastenSystemID'];
						
						if(!is_dir($dir)) $recreateFile = true;
										
						
						if($recreateFile && $pptFile == null) {       // Zip nur erstellen, wenn keine Powerpoint aktiv.
							
							// Daten zusammenstellen und eine ZIP Datei erstellen
							
							@unlink($dir . ".zip");
							
							$zip = new ZipArchive();
							
							if ($zip->open($dir . ".zip", ZipArchive::CREATE)!==TRUE) {
								exit("cannot open <$dir.zip>\n");
							}
							
							
							if(!is_dir($dir)) {
								mkdir($dir);
								chmod($dir,0777);
							}
							
							$newTime = time();
							DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenLastContentUpdate=$newTime WHERE schaukastenID='" . $screen['schaukastenID'] . "'");
							
							$screen['schaukastenLastContentUpdate'] = $newTime;
							
							for($f = 1; $f <= $anzahlFelder; $f++) {
							    							    
								@unlink($dir . "/" . $f . ".html");
								file_put_contents($dir . "/" . $f . ".html",$contentFiles[$f]);
								$zip->addFile($dir . "/" . $f . ".html",$f . ".html");
							}
							
							eval("\$indexPage = \"" . DB::getTPL()->get("digitalSignage/display/" . $screen['schaukastenLayout']) . "\";");
							
							@unlink($dir . "/index.html");
							file_put_contents($dir . "/index.html",$indexPage);
							
							
							$zip->addFile($dir . "/index.html","index.html");
							$zip->addFile("../framework/templates/digitalSignage/display/jquery.min.js","jquery.min.js");
							$zip->addFile("../framework/templates/digitalSignage/display/scrolling.js","scrolling.js");
							
							$zip->close();
							
							$path = DB::getGlobalSettings()->urlToIndexPHP;
							$path = str_replace("index.php", $dir . ".zip", $path);
							$path = str_replace("https", "http", $path);
							
							$xml->addChild("downloadURL", $path);
							$xml->addChild('showPPT', 0);
						}
						else if($recreateFile) {
						    $newTime = time();
						      DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenLastContentUpdate=$newTime WHERE schaukastenID='" . $screen['schaukastenID'] . "'"); 
						      $xml->addChild('showPPT', 1);
						      $xml->addChild('pptFile',base64_encode($pptFile->getURLToFile()));
						      
						}
						else if($pptFile != null) {
						      $xml->addChild('showPPT', 1);
						      $xml->addChild('pptFile',base64_encode($pptFile->getURLToFile()));
						}
						else {
						    $path = DB::getGlobalSettings()->urlToIndexPHP;
						    $path = str_replace("index.php", $dir . ".zip", $path);
						    $path = str_replace("https", "http", $path);
						    
						    $xml->addChild("downloadURL", $path);
						    $xml->addChild('showPPT', "0");
						}

						
						$xml->addChild("isActive",1);
						
						$sectionXML = $xml->addChild('sectionContents');
						
						for($i = 0; $i < sizeof($sectionContents); $i++) {
						    
						  $section = $sectionXML->addChild('section');
						  $section->addChild('sectionNumber',$i);
						  $section->addChild('sectionContent',$sectionContents[$i]);
						}
						
						// $xml->addChild('sectionContents',implode(",",$sectionContents));
						
						$xml->addChild("screenIsOn","1");
						$xml->addChild("updateTime", $screen['schaukastenLastContentUpdate']);
						
					}
					else {
						$xml->addChild("notActive");
					}
				}
			break;
			
			case 'updateScreenShot':
				if($screen['schaukastenID'] > 0) {
					
					$data = file_get_contents($_FILES['imageFile']['tmp_name']);
					
					DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenScreenShot='" . DB::getDB()->escapeString($data). "' WHERE schaukastenID='" . $screen['schaukastenID'] . "'");
					$xml->addChild("success","1");
				}
			break;
		}
		
		
		
		header("Content-type: text/xml");
		echo $xml->asXML();
		exit(0);
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
		return 'Bildschirme';
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
	
	public static function getAdminGroup() {
		return "Webportal_Digitaler_Schaukasten_Admin";
	}
	
	public static function displayAdministration($selfURL) {
		
		if($_GET['action'] == 'changeLayout') {

			$display = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenID='" . intval($_GET['screenID']) . "'");
			if($display['schaukastenID'] > 0) {
				DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenLayout='" . $_POST['layout'] . "' WHERE schaukastenID='" . $display['schaukastenID'] . "'");
				DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenLastContentUpdate=0 WHERE schaukastenID='" . $display['schaukastenID'] . "'");
				
				if( $_POST['layout'] != 'layout1') { // Eventuell PPTs löschen
				    DB::getDB()->query("DELETE FROM schaukasten_inhalt WHERE schaukastenContent LIKE 'PPT%' AND schaukastenID='" . $display['schaukastenID'] . "'");
				}
				
				
			}
			
			
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_GET['action'] == 'deleteScreen') {
			
			$display = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenID='" . intval($_GET['screenID']) . "'");
			if($display['schaukastenID'] > 0) {
				DB::getDB()->query("DELETE FROM schaukasten_bildschirme WHERE schaukastenID='" . $display['schaukastenID'] . "'");
				DB::getDB()->query("DELETE FROM schaukasten_inhalt WHERE schaukastenID='" . $display['schaukastenID'] . "'");
				
			}
			
			
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_GET['action'] == 'changeContent') {
			$display = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenID='" . intval($_GET['screenID']) . "'");
			if($display['schaukastenID'] > 0) {
				switch($display['schaukastenLayout']) {
					case 'layout1':
						$anzahlFelder = 1;
						break;
						
					case 'layout2':
						$anzahlFelder = 2;
						break;
						
					case 'layout3':
						$anzahlFelder = 3;
						break;
				}
				
				for($f = 1; $f <= $anzahlFelder; $f++) {
					DB::getDB()->query("INSERT INTO schaukasten_inhalt (
							schaukastenID,
							schaukastenPosition,
							schaukastenContent
						) values
							(
								'" . $display['schaukastenID'] . "',
								'" . $f . "',
								'" . DB::getDB()->escapeString($_POST['bereich_' . $f]) . "'
							)
						ON DUPLICATE KEY UPDATE schaukastenContent='" . DB::getDB()->escapeString($_POST['bereich_' . $f]) . "'
					");
				}
				
				DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenLastContentUpdate=0 WHERE schaukastenID='" . $display['schaukastenID'] . "'");
				
			}
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_GET['action'] == 'getScreenScreenShot') {
			$display = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenID='" . intval($_GET['screenID']) . "'");
			if($display['schaukastenID'] > 0) {
				header('Content-Type: image/jpeg');
				echo $display['schaukastenScreenShot'];
				exit(0);
			}
		}
		
		if($_GET['action'] == 'activateScreen') {
			$display = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenID='" . intval($_GET['screenID']) . "'");
			if($display['schaukastenID'] > 0) {
				DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenIsActive=1 WHERE schaukastenID='" . $display['schaukastenID'] . "'");
			}
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_GET['action'] == 'deactivateScreen') {
			$display = DB::getDB()->query_first("SELECT * FROM schaukasten_bildschirme WHERE schaukastenID='" . intval($_GET['screenID']) . "'");
			if($display['schaukastenID'] > 0) {
				DB::getDB()->query("UPDATE schaukasten_bildschirme SET schaukastenIsActive=0 WHERE schaukastenID='" . $display['schaukastenID'] . "'");
			}
			header("Location: $selfURL");
			exit(0);
		}
		
		if(DB::getSettings()->getValue("digitalSignage-key") == null) {
			$key = strtoupper(md5(rand()));
			DB::getSettings()->setValue("digitalSignage-key", $key);
			header("Location: $selfURL");
			exit(0);
		}
		
		if($_GET['action'] == 'downloadSettings') {
			header('Content-disposition: attachment; filename=settings.properties');
			header('Content-type: text/plain');
			echo("URLToSchuleIntern=" . str_replace("https", "http", DB::getGlobalSettings()->urlToIndexPHP) . "\r\n");
			echo("digitalSignageKey=" . DB::getSettings()->getValue("digitalSignage-key") . "\r\n");
			echo("pathToPowerpoint=");
			exit(0);
		}
		
		$bildSchirmeHTML = "";
		
		$bildschirmeSQL= DB::getDB()->query("SELECT * FROM schaukasten_bildschirme");
		while($b = DB::getDB()->fetch_array($bildschirmeSQL)) {
			$isActive = (time() - $b['schaukastenLastUpdate']) < 300;
			if($isActive) $isActive = "<span class=\"label label-success\">ONLINE</span>";
			else $isActive = "<span class=\"label label-danger\">OFFLINE</span>";
			
			$lastContact = functions::makeDateFromTimestamp($b['schaukastenLastUpdate']);
			
			$anzahlFelder = 0;
			
			switch($b['schaukastenLayout']) {
				case 'layout1':
					$layoutImage = 'data:image/jpeg;base64,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ZeAf+yq6d/6aNYoA9/8A+DXH/lBR8DP+4/8A+pBqdff9fAH/AAa4/wDKCj4Gf9x//wBSDU6+/wCgD8Af+D5z/m13/ua//cLX6/f8Enf+UWX7NP8A2Srwv/6aLWvyB/4PnP8Am13/ALmv/wBwtfr9/wAEnf8AlFl+zT/2Srwv/wCmi1oA+gKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAK/IH/g9W/5RZeAf+yq6d/6aNYr9fq/IH/g9W/5RZeAf+yq6d/6aNYoA9//AODXH/lBR8DP+4//AOpBqdff9fAH/Brj/wAoKPgZ/wBx/wD9SDU6+/6APwB/4PnP+bXf+5r/APcLX6/f8Enf+UWX7NP/AGSrwv8A+mi1r8gf+D5z/m13/ua//cLX6/f8Enf+UWX7NP8A2Srwv/6aLWgD6AooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr8gf8Ag9W/5RZeAf8Asqunf+mjWK/X6vyB/wCD1b/lFl4B/wCyq6d/6aNYoA9//wCDXH/lBR8DP+4//wCpBqdff9fAH/Brj/ygo+Bn/cf/APUg1Ovv+gD8Af8Ag+c/5td/7mv/ANwtfr9/wSd/5RZfs0/9kq8L/wDpota/IH/g+c/5td/7mv8A9wtfr9/wSd/5RZfs0/8AZKvC/wD6aLWgD6AooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr8gf+D1b/AJRZeAf+yq6d/wCmjWK/X6vyB/4PVv8AlFl4B/7Krp3/AKaNYoA9/wD+DXH/AJQUfAz/ALj/AP6kGp19/wBfAH/Brj/ygo+Bn/cf/wDUg1Ovv+gD8Af+D5z/AJtd/wC5r/8AcLX6/f8ABJ3/AJRZfs0/9kq8L/8Apota/IH/AIPnP+bXf+5r/wDcLX6/f8Enf+UWX7NP/ZKvC/8A6aLWgD6AooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr8gf+D1b/lFl4B/7Krp3/po1iv1+r8gf+D1b/lFl4B/7Krp3/po1igD3/wD4Ncf+UFHwM/7j/wD6kGp19/18Af8ABrj/AMoKPgZ/3H//AFINTr7/AKAPwB/4PnP+bXf+5r/9wtfr9/wSd/5RZfs0/wDZKvC//pota/IH/g+c/wCbXf8Aua//AHC1+v3/AASd/wCUWX7NP/ZKvC//AKaLWgD6AooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAr8gf+D1b/lFl4B/7Krp3/po1iv1+r8gf+D1b/lFl4B/7Krp3/po1igD3/8A4Ncf+UFHwM/7j/8A6kGp19/18Af8GuP/ACgo+Bn/AHH/AP1INTr7/oA/AH/g+c/5td/7mv8A9wtfr9/wSd/5RZfs0/8AZKvC/wD6aLWvyB/4PnP+bXf+5r/9wtfr9/wSd/5RZfs0/wDZKvC//potaAPoCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACvyB/wCD1b/lFl4B/wCyq6d/6aNYr9fq/IH/AIPVv+UWXgH/ALKrp3/po1igD3//AINcf+UFHwM/7j//AKkGp19/18Af8GuP/KCj4Gf9x/8A9SDU6+/6APwB/wCD5z/m13/ua/8A3C1+v3/BJ3/lFl+zT/2Srwv/AOmi1r80f+DyD9in4uftV+Ff2fdY+GPw58YfEKz8KXevWWrR+G9Mk1S6sXvE057ctbwhpvLYWdwDIEMaFVVmVpIw3wf8J/22v+CunwO+FnhnwT4W8G/t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';
					$anzahlFelder = 1;
					break;
				
				case 'layout2':
					$layoutImage = 'data:image/jpeg;base64,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';
					$anzahlFelder = 2;
					break;
				
				case 'layout3':
					$layoutImage = 'data:image/jpeg;base64,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';
					$anzahlFelder = 3;
				break;
			}
			
			$selectFields = "";
			
			$contentSQL = DB::getDB()->query("SELECT * FROM schaukasten_inhalt WHERE schaukastenID='" . $b['schaukastenID'] . "'");
			$content = [];
			
			while($c = DB::getDB()->fetch_array($contentSQL)) $content[$c['schaukastenPosition']] = $c['schaukastenContent'];
			
			$contentList = "";
			
			for($f = 1; $f <= $anzahlFelder; $f++) {
				$selectFields .= "<b>Bereich $f:</b><select name=\"bereich_$f\" class=\"form-control\">";
				
				$currentContent = "n/a";
				
				$possContent = self::getAllPossibleContent($b['schaukastenLayout'], $b['schaukastenHasPPT'] > 0);
				for($h = 0; $h < sizeof($possContent); $h++) {
					$selectFields .= "<option value=\"" . $possContent[$h]['name'] . "\"";
					if($content[$f] == $possContent[$h]['name']) {
						$selectFields .= " selected=\"selected\"";
						$currentContent = $possContent[$h]['displayName'];
					}
					$selectFields .= ">" . $possContent[$h]['displayName'] . "</option>";
				}
				
				$selectFields .= "</select>";
				
				$contentList .= "$f: " . $currentContent . "<br />";
				
			}
			
			
			
			eval("\$bildSchirmeHTML .= \"" . DB::getTPL()->get("digitalSignage/admin/bit") . "\";");
		}
		
		eval("\$html = \"". DB::getTPL()->get("digitalSignage/admin/index") . "\";");
		return $html;
	}
	
	public static function getAllPossibleContent($layoutID, $hasPPT) {
		$content = [
				[
						'name' => 'lehrerheute',
						'displayName' => 'Vertretungsplan Lehrer heute'
				],	
				
				[
						'name' => 'lehrermorgen',
						'displayName' => 'Vertretungsplan Lehrer morgen'
				],
				
				[
						'name' => 'schuelerheute',
						'displayName' => 'Vertretungsplan Schüler heute'
				],
				[
						'name' => 'schuelermorgen',
						'displayName' => 'Vertretungsplan Schüler morgen'
				],
				[
						'name' => 'lehrerheutemorgen',
						'displayName' => 'Vertretungsplan Lehrer heute und morgen'
				],	
				[
						'name' => 'schuelerheutemorgen',
						'displayName' => 'Vertretungsplan Schüler heute und morgen'
				],	
		];
		
		if($layoutID == 'layout1' && $hasPPT) {
		    // Powerpoints laden
		    
		    $ppts = DB::getDB()->query('SELECT * FROM schaukasten_powerpoint');
		    
		    while($ppt = DB::getDB()->fetch_array($ppts)) {
		        $content[] = [
		            'name' => 'PPT' . $ppt['powerpointID'],
		            'displayName' => 'Powerpoint: ' . $ppt['powerpointName']
		        ];
		    }
		}
		
		$websites = DB::getDB()->query('SELECT * FROM schaukasten_website');
		
		while($ws = DB::getDB()->fetch_array($websites)) {
		    $content[] = [
		        'name' => 'WS' . $ws['websiteID'],
		        'displayName' => 'Webseite: ' . $ws['websiteName']
		    ];
		}
		
		return $content;
	}
	
	public static function getAdminMenuGroup() {
		return 'Digitaler Schaukasten';
	}
	
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-television';
	}
	
	public static function getAdminMenuIcon() {
		return 'fa fa-television';
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>