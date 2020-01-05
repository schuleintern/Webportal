<?php



class administrationcreateusers extends AbstractPage {

  public function __construct() {
    $this->needLicense = false;

    parent::__construct(array("Administration", "Benutzeradministration", "Initalpasswörter"));

    new errorPage();
  }
  
  private static $showMessage;

  public static function hasAdmin() {
  	return true;
  	if(DB::getGlobalSettings()->lehrerUserMode == "ASV" || DB::getGlobalSettings()->schuelerUserMode == "ASV" || DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
  		return true;
  	}
  	else return false;
  }
  
  public static function getAdminGroup() {
  	return 'Webportal_Initialpasswords';
  }
  
  public function execute(){}
  
  public static function displayAdministration($selfURL) {
  	self::createUsers();

    if(DB::getGlobalSettings()->lehrerUserMode == "ASV" || DB::getGlobalSettings()->schuelerUserMode == "ASV" || DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {

      if(isset($_GET['action']) && $_GET['action'] == "printLetter") {
       self::printLetterForUserID();
        exit(0);
      }

      if(isset($_GET['action']) && $_GET['action'] == "printAll") {
        self::printAll();
        exit(0);
      }

      if(isset($_GET['action']) && $_GET['action'] == "printAllNotPrinted") {
        self::printAll(true);
        exit(0);
      }
      
      if(isset($_GET['action']) && $_GET['action'] == "regenerateAllElternCodes" && DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
          DB::getDB()->query("TRUNCATE eltern_codes");
          
          header("Location: $selfURL&network=ELTERN");
          exit(0);
      }
      
      
      if(isset($_GET['action']) && $_GET['action'] == "regenerateCode" && DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
          DB::getDB()->query("DELETE FROM eltern_codes WHERE codeSchuelerAsvID='" . DB::getDB()->escapeString($_REQUEST['schuelerAsvID']) . "'");          
          
          header("Location: $selfURL&network=ELTERN");
          exit(0);
      }

      $tabs = "";

      $network = DB::getDB()->escapeString($_GET['network']);

      $networks = array();

      if(DB::getGlobalSettings()->lehrerUserMode == "ASV") $networks[] = "SCHULEINTERN_LEHRER";
      if(DB::getGlobalSettings()->schuelerUserMode == "ASV") $networks[] = "SCHULEINTERN_SCHUELER";
      if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") $networks[] = "ELTERN";


      $first = true;
      for($i = 0; $i < sizeof($networks); $i++) {
        if($first) {
          if($network == "") $network = $networks[$i];

          $first = false;
        }
        $tabs .= "<li" . (($networks[$i] == $network) ? " class=\"active\"" : "") . "><a href=\"$selfURL&network=" . $networks[$i] . "\"><i class=\"fa fa-users\"></i> " . $networks[$i] . "</a></li>\r\n";
      }

      if($network == "SCHULEINTERN_SCHUELER") {
        $users = DB::getDB()->query("SELECT * FROM users JOIN initialpasswords ON userID=initialPasswordUserID LEFT JOIN schueler ON userID=schuelerUserID WHERE userNetwork = '" . $network . "' ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");
      }
      else if($network == "SCHULEINTERN_LEHRER") {
        $users = DB::getDB()->query("SELECT * FROM users JOIN initialpasswords ON userID=initialPasswordUserID LEFT JOIN lehrer ON userID=lehrerUserID WHERE userNetwork = '" . $network . "' ORDER BY lehrerKuerzel ASC, lehrerName ASC, lehrerRufname ASC");
      }
      else {
      	$users = DB::getDB()->query("SELECT * FROM eltern_codes JOIN schueler ON schuelerAsvID=codeSchuelerAsvID ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");
      }



      $userHTML = "";
      while($user = DB::getDB()->fetch_array($users)) {
        if($network == "SCHULEINTERN_SCHUELER") {
        	$klassekuerzel = $user['schuelerKlasse'];
        	eval("\$userHTML .= \"" . DB::getTPL()->get("administration/initialpasswords/user_bit") . "\";");
        }
        else if($network == "SCHULEINTERN_LEHRER") {
        	$klassekuerzel = $user['lehrerKuerzel'];
        	eval("\$userHTML .= \"" . DB::getTPL()->get("administration/initialpasswords/user_bit") . "\";");
        }
        else {
        	$klassekuerzel = "Eltern";
        	eval("\$userHTML .= \"" . DB::getTPL()->get("administration/initialpasswords/user_bit_eltern") . "\";");
        }
      }

      if(self::$showMessage != "") {
        self::$showMessage = "<div class=\"callout callout-info\">" . self::$showMessage . "</div>";
      }

      eval("\$html = \"" . DB::getTPL()->get("administration/initialpasswords/index") . "\";");
      return $html;
    }
    else {
    	return "Für keine Benutzergruppe sind Benutzer aus der ASV vorgehsehen.";
    }
  }

  private static function printLetterForUserID() {
  	
  	if($_GET['schuelerAsvID'] != "") {
  		// Schüler Brief soll gedruckt werden.
  		$user = DB::getDB()->query_first("SELECT * FROM eltern_codes JOIN schueler ON codeSchuelerAsvID=schuelerAsvID WHERE schuelerAsvID='" . DB::getDB()->escapeString($_GET['schuelerAsvID']) . "'");
  		
  		if($user['schuelerAsvID'] != "") {
  		
  			$text = DB::getSettings()->getValue("createusers-letterneweltern");
  		
  			$text = str_replace("{SCHUELERNAME}", $user['schuelerName'] . ", " . $user['schuelerRufname'], $text);
  			$text = str_replace("{KLASSE}", $user['schuelerKlasse'],$text);
  			$text = str_replace("{CODE}", $user['codeText'],$text);
  		
  			DB::getDB()->query("UPDATE eltern_codes SET codePrinted=UNIX_TIMESTAMP() WHERE codeSchuelerAsvID='{$user['schuelerAsvID']}'");
  		
  		  		
  			$letter = new PrintLetterWithWindowA4("Registrierungscode");
  			$letter->setBetreff("Registrierungscode für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
  			
  			$adresse = DB::getDB()->query_first("SELECT * FROM eltern_adressen WHERE adresseSchuelerAsvID='" . $user['schuelerAsvID'] . "' ORDER BY adresseIsHauptansprechpartner DESC LIMIT 1");
  			
  			$briefAdresse = "";
  			if($adresse['adresseID'] > 0) {
  				$briefAdresse = $adresse['adresseAnschrifttext'] . "\r\n" . $adresse['adresseStrasse'] . " " . $adresse['adresseNummer'] . "\r\n" . $adresse['adressePostleitzahl'] . " " . $adresse['adresseOrt'];
  			}
  			$letter->setDatum(DateFunctions::getTodayAsNaturalDate());
  			
  			$letter->addLetter($briefAdresse, $text);
  			
  			$letter->send();  		
  			exit(0);
  		}
  		else new errorPage("Das Passwort für diesen Benutzer ist nicht (mehr) verfügbar!");
  	}
  	else {
    $user = DB::getDB()->query_first("SELECT * FROM users LEFT JOIN schueler ON schuelerUserID=userID LEFT JOIN lehrer ON lehrerUserID=userID JOIN initialpasswords ON userID=initialPasswordUserID WHERE userID='" . intval($_GET['userID']) . "'");
    if($user['userID'] > 0 && $user['initialPassword'] != "") {

    	$letter = new PrintLetterWithWindowA4("Zugangsdaten  " . $user['userName']);
    	$letter->setBetreff("Zugangsdaten für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
    	$letter->setDatum(DateFunctions::getTodayAsNaturalDate());
    	 
    	
      if($user['schuelerAsvID'] != "") {
        $text = DB::getSettings()->getValue("createusers-letternewschueler");

        $text = str_replace("{SCHUELERNAME}", $user['schuelerName'] . ", " . $user['schuelerRufname'], $text);
        $text = str_replace("{KLASSE}", $user['schuelerKlasse'],$text);
        $text = str_replace("{BENUTZERNAME}", $user['userName'],$text);
        $text = str_replace("{PASSWORT}", $user['initialPassword'],$text);
                
        
        $briefAdresse = $user['schuelerName'] . ", " . $user['schuelerRufname'] . "\r\nKlasse " . $user['schuelerKlasse'];
                
      }
      else {
        $text = DB::getSettings()->getValue("createusers-letternewlehrer");

        $text = str_replace("{LEHRERNAME}", $user['lehrerName'] . ", " . $user['lehrerRufname'], $text);
        $text = str_replace("{BENUTZERNAME}", $user['userName'],$text);
        $text = str_replace("{PASSWORT}", $user['initialPassword'],$text);
        
        
        $briefAdresse = $user['lehrerName'] . ", " . $user['lehrerRufname'];        
      }

      DB::getDB()->query("UPDATE initialpasswords SET passwordPrinted=UNIX_TIMESTAMP() WHERE initialPasswordUserID={$user['userID']}");
           
        			
  	  $letter->addLetter($briefAdresse, $text);
  			
  	  $letter->send();  

      exit(0);
    }
    else new errorPage("Das Passwort für diesen Benutzer ist nicht (mehr) verfügbar!");
  	}
  }

  private static function printAll($notPrinted = false) {

    $network = DB::getDB()->escapeString($_GET['network']);

    $letter = new PrintLetterWithWindowA4("Initalpasswoerter");

    if($network == "SCHULEINTERN_SCHUELER") {
      $users = DB::getDB()->query("SELECT * FROM users JOIN initialpasswords ON userID=initialPasswordUserID LEFT JOIN schueler ON userID=schuelerUserID WHERE userNetwork = '" . $network . "' " . (($notPrinted) ? (" AND passwordPrinted=0") : ("")) . " ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");
    }
    else if($network == "SCHULEINTERN_LEHRER") {
      $users = DB::getDB()->query("SELECT * FROM users JOIN initialpasswords ON userID=initialPasswordUserID LEFT JOIN lehrer ON userID=lehrerUserID WHERE userNetwork = '" . $network . "' " . (($notPrinted) ? (" AND passwordPrinted=0") : ("")) . " ORDER BY lehrerKuerzel ASC, lehrerName ASC, lehrerRufname ASC");
    }
	else {
		// Eltern
		$users = DB::getDB()->query("SELECT * FROM eltern_codes JOIN schueler ON codeSchuelerAsvID=schuelerAsvID " . (($notPrinted) ? ( " WHERE codePrinted=0 ") : ("")) . " ORDER BY length(schuelerKlasse) ASC, schuelerKlasse ASC, schuelerName ASC, schuelerRufname ASC");
	}


    $userHTML = "";
    while($user = DB::getDB()->fetch_array($users)) {
      if($network == "SCHULEINTERN_SCHUELER") $klassekuerzel = $user['schuelerKlasse'];
      else if($network == "SCHULEINTERN_ELETRN") $klassekuerzel = "Eltern";
      else $klassekuerzel = $user['lehrerKuerzel'];


      if($user['codeText'] != "") {
      	$text = DB::getSettings()->getValue("createusers-letterneweltern");
      	
      	$text = str_replace("{SCHUELERNAME}", $user['schuelerName'] . ", " . $user['schuelerRufname'], $text);
      	$text = str_replace("{KLASSE}", $user['schuelerKlasse'],$text);
      	$text = str_replace("{CODE}", $user['codeText'],$text);
      	
      	DB::getDB()->query("UPDATE eltern_codes SET codePrinted=UNIX_TIMESTAMP() WHERE codeSchuelerAsvID='{$user['schuelerAsvID']}'");
      	
      	$letter->setBetreff("Registrierungscode für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
      	 
      	$adresse = DB::getDB()->query_first("SELECT * FROM eltern_adressen WHERE adresseSchuelerAsvID='" . $user['schuelerAsvID'] . "' ORDER BY adresseIsHauptansprechpartner DESC LIMIT 1");
      	 
      	$briefAdresse = "";
      	if($adresse['adresseID'] > 0) {
      		$briefAdresse = $adresse['adresseAnschrifttext'] . "\r\n" . $adresse['adresseStrasse'] . " " . $adresse['adresseNummer'] . "\r\n" . $adresse['adressePostleitzahl'] . " " . $adresse['adresseOrt'];
      	}
      	$letter->setDatum(DateFunctions::getTodayAsNaturalDate());
      	 
      	$letter->addLetter($briefAdresse, $text);
      	 
      }
      
      else if($user['schuelerAsvID'] != "") {
        $text = DB::getSettings()->getValue("createusers-letternewschueler");

        $text = str_replace("{SCHUELERNAME}", $user['schuelerName'] . ", " . $user['schuelerRufname'], $text);
        $text = str_replace("{KLASSE}", $user['schuelerKlasse'],$text);
        $text = str_replace("{BENUTZERNAME}", $user['userName'],$text);
        $text = str_replace("{PASSWORT}", $user['initialPassword'],$text);
        
        DB::getDB()->query("UPDATE initialpasswords SET passwordPrinted=UNIX_TIMESTAMP() WHERE initialPasswordUserID={$user['userID']}");
        
        $letter->setBetreff("Zugangsdaten für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
        

        $briefAdresse = $user['schuelerName'] . ", " . $user['schuelerRufname'] . "\r\nKlasse " . $user['schuelerKlasse'];
        $letter->setDatum(DateFunctions::getTodayAsNaturalDate());
        
        $letter->addLetter($briefAdresse, $text);

      }
      else {
        $text = DB::getSettings()->getValue("createusers-letternewlehrer");

        $text = str_replace("{LEHRERNAME}", $user['lehrerName'] . ", " . $user['lehrerRufname'], $text);
        $text = str_replace("{BENUTZERNAME}", $user['userName'],$text);
        $text = str_replace("{PASSWORT}", $user['initialPassword'],$text);
        
        
        $letter->setBetreff("Zugangsdaten für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
        
        
        $briefAdresse = $user['lehrerName'] . ", " . $user['lehrerRufname'];
        $letter->setDatum(DateFunctions::getTodayAsNaturalDate());
        
        $letter->addLetter($briefAdresse, $text);
        
        DB::getDB()->query("UPDATE initialpasswords SET passwordPrinted=UNIX_TIMESTAMP() WHERE initialPasswordUserID={$user['userID']}");
        
      }
    }


    $letter->send();

    exit(0);

  }

  private static function createUsers() {
        if(DB::getGlobalSettings()->lehrerUserMode == "ASV") {
            
            $lehrer = lehrer::getAll(true);

            for($i = 0; $i < sizeof($lehrer); $i++) {
                
                if($lehrer[$i]->getUserID() == 0 && $lehrer[$i]->istActive()) {
                
                      $newPassword = substr(md5(rand()), 1, 10);
                      
        
                      // Benutzeranlegen
                      DB::getDB()->query("INSERT INTO users
                        (
                          userName,
                          userFirstName,
                          userLastName,
                          userCachedPasswordHash,
                          userCachedPasswordHashTime,
                          userNetwork,
                          userAsvID
                        ) values(
                          '" . DB::getDB()->escapeString($lehrer[$i]->getKuerzel()) . "',
                          '" . DB::getDB()->escapeString($lehrer[$i]->getRufname()) . "',
                          '" . DB::getDB()->escapeString($lehrer[$i]->getName()) . "',
                          '" . login::hash($newPassword) . "',
                          UNIX_TIMESTAMP(),
                          'SCHULEINTERN_LEHRER',
                          '" . DB::getDB()->escapeString($lehrer[$i]->getAsvID()) . "'
                        ) ON DUPLICATE KEY UPDATE userAsvID='" . DB::getDB()->escapeString($lehrer[$i]->getAsvID()) . "'
                      ");
        
                      $userID = DB::getDB()->insert_id();
        
                      DB::getDB()->query("UPDATE lehrer SET lehrerUserID='" . $userID . "' WHERE lehrerID='" . $lehrer[$i]->getXMLID() . "'");
                      DB::getDB()->query("INSERT INTO initialpasswords (initialPasswordUserID, initialPassword) values('" . $userID . "','" . $newPassword . "')");
                }
                else if($lehrer[$i]->getUser() != null && !$lehrer[$i]->istActive()) {
                    $lehrer[$i]->getUser()->deleteUser();
                }
                else if($lehrer[$i]->getUserID() > 0 && !$lehrer[$i]->istActive()) {            // UserID vorhanden, aber kein Benutzer
                    $lehrer[$i]->setUserID();
                }
            }
            
            
            // Alte Benutzer löschen
            
            DB::getDB()->query("DELETE FROM users WHERE userID NOT IN (SELECT lehrerUserID FROM lehrer) AND userNetwork='SCHULEINTERN_LEHRER'");


        }

        if(DB::getGlobalSettings()->schuelerUserMode == "ASV") {
          $schuelerOhneKennungSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerUserID=0");

          $schuelerOhneKennungData = array();

          while($l = DB::getDB()->fetch_array($schuelerOhneKennungSQL)) $schuelerOhneKennungData[] = $l;


          for($i = 0; $i < sizeof($schuelerOhneKennungData); $i++) {
            $newPassword = substr(md5(rand()), 1, 10);

            // Benutzeranlegen
            DB::getDB()->query("INSERT INTO users
                (
                  userName,
                  userFirstName,
                  userLastName,
                  userCachedPasswordHash,
                  userCachedPasswordHashTime,
                  userNetwork,
                  userAsvID
                ) values(
                  '_PLACEHOLDER_',
                  '" . DB::getDB()->escapeString($schuelerOhneKennungData[$i]['schuelerRufname']) . "',
                  '" . DB::getDB()->escapeString($schuelerOhneKennungData[$i]['schuelerName']) . "',
                  '" . login::hash($newPassword) . "',
                  UNIX_TIMESTAMP(),
                  'SCHULEINTERN_SCHUELER',
                  '" . DB::getDB()->escapeString($schuelerOhneKennungData[$i]['schuelerAsvID']) . "'
                ) ON DUPLICATE KEY UPDATE userAsvID='" . DB::getDB()->escapeString($schuelerOhneKennungData[$i]['schuelerAsvID']) . "'
              ");

            $userID = DB::getDB()->insert_id();

            DB::getDB()->query("UPDATE schueler SET schuelerUserID='" . $userID . "' WHERE schuelerAsvID='" . $schuelerOhneKennungData[$i]['schuelerAsvID'] . "'");
            DB::getDB()->query("INSERT INTO initialpasswords (initialPasswordUserID, initialPassword) values('" . $userID . "','" . $newPassword . "')");
          }


          DB::getDB()->query("UPDATE users SET userName = CONCAT('S', userID) WHERE userName='_PLACEHOLDER_'");
          
          DB::getDB()->query("DELETE FROM users WHERE userID NOT IN (SELECT schuelerUserID FROM schueler WHERE schuelerAustrittDatum IS NULL OR schuelerAustrittDatum > CURDATE()) AND userNetwork='SCHULEINTERN_SCHUELER'");
          
        }

        if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
          $schuelerOhneCodeSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerAsvID NOT IN (SELECT codeSchuelerAsvID FROM eltern_codes)");

          while($schueler = DB::getDB()->fetch_array($schuelerOhneCodeSQL)) {
            $code = substr(md5($schueler['schuelerAsvID']),0,5) . "-" . substr(md5(rand()),0,10);
            DB::getDB()->query("INSERT INTO eltern_codes (codeSchuelerAsvID, codeText, codeUserID) values('" . $schueler['schuelerAsvID'] . "','" . $code . "',0)");
          }
        }

  }

  public static function hasSettings() {
    return sizeof(self::getSettingsDescription()) > 0;
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
      $settings = array();

      if(DB::getGlobalSettings()->schuelerUserMode == "ASV") {
    $settings[] =
        array(
            'name' => 'createusers-letternewschueler',
            'typ' => 'HTML',
            'titel' => 'Brief für neue Schüler',
            'text' => 'Text des Briefes für die Schüler<br />{SCHUELERNAME} für den Schülernamen; {BENUTZERNAME} für den Benutzernamen; {PASSWORT} für das Passwort; {KLASSE} für die Klasse'
        );
      }

    if(DB::getGlobalSettings()->lehrerUserMode == "ASV") {
        $settings[] =	    array(
            'name' => 'createusers-letternewlehrer',
            'typ' => 'HTML',
            'titel' => 'Brief für neue Lehrer',
            'text' => 'Text des Briefes für die Lehrer<br />{LEHRERNAME} für den Schülernamen; {BENUTZERNAME} für den Benutzernamen; {PASSWORT} für das Passwort'
        );

    }
      if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {

        $settings[] = array(
            'name' => 'createusers-letterneweltern',
            'typ' => 'HTML',
            'titel' => 'Brief für neue Eltern',
            'text' => 'Text des Briefes für die Eltern (Codes)<br />{SCHUELERNAME} für den Schülernamen; {CODE} für den Registrierungscode; {KLASSE} für die Klasse'
        );
        
        $settings[] =  array(
        	'name' => "elternregister-mailtext",
        	'typ' => "TEXT",
        	'titel' => "Text der Verifikationsmail",
        	'text' => "{LINK} ist der Link zur Bestätigung der E-Mailadresse."
        );
      }

      return $settings;
  }


  public static function getSiteDisplayName() {
    return 'Initialpasswörter';
  }

  /**
   * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
   * @return array(array('groupName' => '', 'beschreibung' => ''))
   */
  public static function getUserGroups() {
    return array();

  }

  public static function siteIsAlwaysActive() {
    return true;
  }
  
  public static function getAdminMenuGroup() {
  	return 'Benutzerverwaltung';
  }
  
  public static function getAdminMenuGroupIcon() {
  	return 'fa fa-users';
  }
  
  public static function getAdminMenuIcon() {
  	return 'fa fa-key';
  }
  
  public static function need2Factor() {
      return true;
  }

}


?>
