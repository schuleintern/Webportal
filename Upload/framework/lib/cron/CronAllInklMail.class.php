<?php


/**
 * Unvollständig implementiert.
 */

class CronAllInklMail extends AbstractCron {
    
    /**
     * Anzahl der durchgeführten Aktionen
     * @var integer
     */
    private $actionsDone = 0;
    
    private $logFile = "";
    
    private $currentMail = [];
    
	
	public function __construct() {
	    
	}

	public function execute() {
	    
	    if(DB::getSettings()->getBoolean('all-inkl-mail-active') && DB::getGlobalSettings()->kasAccount['Username'] != "") {
	        
	        // Try Login
	        
	        try
	        {
	            $SoapLogon = new SoapClient('https://kasapi.kasserver.com/soap/wsdl/KasAuth.wsdl');
	            $CredentialToken = $SoapLogon->KasAuth(json_encode(array(
	                'KasUser' => DB::getGlobalSettings()->kasAccount['Username'],
	                'KasAuthType' => 'sha1',
	                'KasPassword' => sha1(DB::getGlobalSettings()->kasAccount['Password']),
	                'SessionLifeTime' => 600,
	                'SessionUpdateLifeTime' => "Y"
	            )));
	            
	            
	            
	            
	            
	        }       
	        catch (SoapFault $fault) {
	            $this->logFile .= "Fehlernummer: {$fault->faultcode},
	            Fehlermeldung: {$fault->faultstring},
	            Verursacher: {$fault->faultactor},
	            Details: {$fault->detail}";
	            
	            return;
	        }
	        
	        
	        try
	        {
	            $Params = array();
	            
	            $SoapRequest = new SoapClient('https://kasapi.kasserver.com/soap/wsdl/KasApi.wsdl');
	            $req = $SoapRequest->KasApi(json_encode(array(
	                'KasUser' => DB::getGlobalSettings()->kasAccount['Username'],                 // KAS-User
	                'KasAuthType' => 'session',             // Auth per Sessiontoken
	                'KasAuthData' => $CredentialToken,      // Auth-Token
	                'KasRequestType' => 'get_mailaccounts',     // API-Funktion
	                'KasRequestParams' => $Params           // Parameter an die API-Funktion
	            )));
	            
	            
	            for($i = 0; $i < sizeof($req['Response']['ReturnInfo']); $i++) {
	                $this->currentMail[] = $req['Response']['ReturnInfo'][$i]['mail_adresses'];
	            }
	        }
	        
	        catch (SoapFault $fault)
	        {
	            $this->logFile .= "Fehlernummer: {$fault->faultcode},
	            Fehlermeldung: {$fault->faultstring},
	            Verursacher: {$fault->faultactor},
	            Details: {$fault->detail}";
	            
	            return;
	        }
	        
	        sleep(1);      // API Flood Control
	        
	        
	        $lehrer = lehrer::getAll();
	        
	        $lehrer = [lehrer::getByKuerzel("ma")];
	        
	        
	        
	        for($i = 0; $i < sizeof($lehrer); $i++) {
	            if($lehrer[$i]->getUser() != null) {
	                $user = $lehrer[$i]->getUser();
	                	                
	                if($user->getAllInklMail() == null) {
	                    
	                    $mail = $this->getMailLocalPart($lehrer[$i]);
	                    
	                    if($mail != null) {
	                        	                        
	                        try
	                        {
	                            $password = substr(base64_encode(random_bytes(100)), 2,20);
	                            
// 	                            $kasConfiguration = new KasApi\KasConfiguration(DB::getGlobalSettings()->kasAccount['Username'], DB::getGlobalSettings()->kasAccount['Password'], "sha1");
	                            
// 	                            $kasApi = new KasApi\KasApi($kasConfiguration);
	                            
// 	                            $kasApi->add_mailaccount( [
// 	                                'mail_password' => $password,
// 	                                'local_part' => explode("@", $mail)[0],
// 	                                'domain_part' => explode("@", $mail)[1]
// 	                            ]);
	                            	                     
	                            
	                            $Params = array(
	                             	                                'mail_password' => $password,
	                             	                                'local_part' => explode("@", $mail)[0],
	                             	                                'domain_part' => explode("@", $mail)[1]
	                                
	                            );
	                            
	                            
	                            
	                            
	                            $SoapRequest = new SoapClient('https://kasapi.kasserver.com/soap/wsdl/KasApi.wsdl');
	                            $req = $SoapRequest->KasApi(json_encode(array(
	                                'KasUser' => DB::getGlobalSettings()->kasAccount['Username'],                // KAS-User
	                                'KasAuthType' => 'session',             // Auth per Sessiontoken
	                                'KasAuthData' => $CredentialToken,      // Auth-Token
	                                'KasRequestType' => 'add_mailaccount',      // API-Funktion
	                                'KasRequestParams' => $Params          // Parameter an die API-Funktion
	                                
	                            )));
	                            
	                            
	                            $user->setAllInklMailCreated($mail, $password);
	                           $this->logFile = "Account für " . $lehrer[$i]->getDisplayNameMitAmtsbezeichnung() . " angelegt.";
	                            
	                            
	                            return;     // Nur einen pro Durchgang, um doppelte zu vermeiden.
	                        }
	                        
	                        // Fehler abfangen und ausgeben
	                        catch (SoapFault $fault)
	                        {
	                            $this->logFile .= "Fehlernummer: {$fault->faultcode},
	                            Fehlermeldung: {$fault->faultstring},
	                            Verursacher: {$fault->faultactor},
	                            Details: {$fault->detail}";
	                            
	                            return;
	                        }
	                    }
	                    else {
	                        $this->logFile = "Kein Format für Mailadresse gesetzt!";
	                        return;
	                    }
	                    
	                }
	            }
	        }
	        
	        
	        

	        
	    }
	    else {
	        $this->logFile = "Nicht aktiv oder KAS Zugangsdaten fehlen.";
	        return;
	    }    
	    
	}
	
	
	/**
	 * 
	 * @param lehrer $lehrer
	 */
	private function getMailLocalPart(lehrer $lehrer, $skip = 0) {
	    
	    $vorname = iconv("UTF-8", "ASCII//TRANSLIT", $lehrer->getRufname());
	    $nachname = iconv("UTF-8", "ASCII//TRANSLIT", $lehrer->getName());
	    $kuerzel = iconv("UTF-8", "ASCII//TRANSLIT", $lehrer->getKuerzel());
	    
	    
	    if(DB::getSettings()->getValue("all-inkl-mail-format") == 'vorname.nachname'){
	        $mail = strtolower($vorname . "." . $nachname . "@" . DB::getGlobalSettings()->kasMailDomain);
	        if($skip > 0) {
	            $mail = strtolower($vorname . "." . $nachname . "_" . $skip . "@" . DB::getGlobalSettings()->kasMailDomain);
	        }
	        
	        if(!in_array($mail, $this->currentMail)) return $mail;
	        else return $this->getMailLocalPart($lehrer, $skip + 1);
	    }
	    
	    if(DB::getSettings()->getValue("all-inkl-mail-format") == 'v.nachname'){
	        
	        $mail = strtolower(substr($vorname,0,$skip+1) . "." . $nachname . "@" . DB::getGlobalSettings()->kasMailDomain);
	        
	        if(!in_array($mail, $this->currentMail)) return $mail;
	        else return $this->getMailLocalPart($lehrer, $skip + 1);
	    }
	    
	    
	    if(DB::getSettings()->getValue("all-inkl-mail-format") == 'k'){
	        
	        $mail = strtolower($kuerzel . "@" . DB::getGlobalSettings()->kasMailDomain);
	        
	        if($skip > 0) {
	            $mail = strtolower($kuerzel . "_" . $skip . "@" . DB::getGlobalSettings()->kasMailDomain);
	        }
	        
	        if(!in_array($mail, $this->currentMail)) return $mail;
	        else return $this->getMailLocalPart($lehrer, $skip + 1);
	    }
	    
	    return null;
	    
	}
	
	public function getName() {
		return "AllInkl Mailadressen erstellen";
	}
	
	public function getDescription() {
		return "";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
        return [
            'success' => true,
            'resultText' => $this->logFile
        ];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 900;		// Stündlich
	}
}



?>