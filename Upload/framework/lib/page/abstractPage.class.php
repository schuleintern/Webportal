<?php



/**
 * Abstrakte Seite auf der alle andere Seiten aufbauen.
 * @author Christian Spitschka
 */
abstract class AbstractPage {
    
    /**
     * Titel der Seite.
     * @var unknown
     */
	private $title;

    /**
     * @var string
     */
	public $header = "";

	// public $footer = ""; // moved to PAGE.class.php

	/**
	 * @deprecated Eigener Requesthandler
	 * @see apihandler
	 * @var string
	 */
	protected $isAPI = false;

	protected $apiIsSessionOK = false;

	protected $sitename = "index";

	protected $messageItem = "";
	protected $taskItem = "";
	protected $loginStatus = "";
	protected $userImage = "";
	protected $eltermailPopup = "";
	
	protected $helpTopic = "";

	private static $activePages = array();
	
	private $acl = false;

    /**
     * Ist das Modul im Beta Status?
     * @var bool
     */
	protected $isBeta = false;

	public function __construct($pageline, $ignoreSession = false, $isAdmin = false, $isNotenverwaltung = false) {

		header("X-Frame-Options: deny");

		$this->sitename = addslashes ( trim ( $_REQUEST ['page'] ) );
				
		if ($this->sitename != "" && in_array($this->sitename, requesthandler::getAllowedActions()) && !self::isActive ( $this->sitename )) {
			// TODO: Sinnvolle Fehlermeldung
			die ( "Die angegebene Seite ist leider nicht aktiviert" );
		}
		
		// Seite ohne Session aufrufen?
		if (! $ignoreSession) {
			$this->title = $title;
			$this->sitename = $sitename;
			
			if (isset ( $_COOKIE ['schuleinternsession'] )) {
				
				DB::initSession ( $_COOKIE ['schuleinternsession'] );
				
				if (! DB::isLoggedIn ()) {
					if (isset ( $_COOKIE ['schuleinternsession'] ))
						setcookie ( "schuleinternsession", null );

					$message = "<div class=\"callout callout-danger\"><p><strong>Sie waren leider zu lange inaktiv. Sie k&ouml;nnen dauerhaft angemeldet bleiben, wenn Sie den Haken bei \"Anmeldung speichern\" setzen. </strong></p></div>";
					
					eval ( "echo(\"" . DB::getTPL ()->get ( "login/index" ) . "\");" );
					
					exit ();
				} else {
					DB::getSession ()->update ();
				}
			}
			
			
			// 2 Faktor

			$needTwoFactor = false;
			
			if(DB::isLoggedIn() && TwoFactor::is2FAActive() && TwoFactor::enforcedForUser(DB::getSession()->getUser())) {
                $needTwoFactor = true;
            }

			$pagesWithoutTwoFactor = [
			    'login',
			    'logout',
			    'TwoFactor'
			];
			
			
			if($needTwoFactor || ($this->need2Factor() && TwoFactor::is2FAActive())) {
			    $currentPage = $_REQUEST['page'];
			    
			    if(!DB::getSession()->is2FactorActive() && !in_array($currentPage, $pagesWithoutTwoFactor)) {
			        header("Location: index.php?page=TwoFactor&action=initSession&gotoPage=" . urlencode($currentPage));
			        exit(0);			        
			    }			    
			}

			
			
			// Wartungsmodus
			
			$infoWartungsmodus = "";
			
			if (DB::getSettings ()->getValue ( "general-wartungsmodus" ) && $_REQUEST ['page'] != "login" && $_REQUEST ['page'] != "logout" && $_REQUEST ['page'] != "impressum") {
				if (! DB::isLoggedIn () || ! DB::getSession ()->isAdmin ()) {
					eval ( "echo(\"" . DB::getTPL ()->get ( "wartungsmodus/index" ) . "\");" );
					exit ();
				} else {
					$infoWartungsmodus = "<div class=\"callout callout-danger\"><i class=\"fa fa-cogs\"></i> Die Seite befindet sich im Wartungsmodus! Bitte unter den <a href=\"index.php?page=administrationmodule&module=index\">Einstellungen</a> wieder deaktivieren!</div>";
				}
			}
		
			
			// /Wartungsmodus


			// Datenschutz
			
			
			if (DB::isLoggedIn() && datenschutz::needFreigabe(DB::getSession()->getUser()) && !datenschutz::isFreigegeben(DB::getSession()->getUser()) && $_REQUEST ['page'] != "login" && $_REQUEST ['page'] != "logout" && $_REQUEST ['page'] != "impressum" && $_REQUEST ['page'] != "datenschutz") {
				header("Location: index.php?page=datenschutz&confirmPopUp=1");
				exit(0);
			}
			
			
			
			// /Datenschutz
			
			$this->prepareHeaderBar ();
			
						
			$menu = new menu ($isAdmin, $isNotenverwaltung);
			$menuHTML = $menu->getHTML ();
			
			$sitemapline = "";
			
			for($i = 0; $i < sizeof ( $pageline ); $i ++) {
				$sitemapline .= '<li class="active">' . $pageline [$i] . '</li>';
			}
			
			$siteTitle = $pageline [sizeof ( $pageline ) - 1];
			
			// Login Status
			
			if (DB::isLoggedIn ()) {
				$displayName = DB::getSession ()->getData ( 'userFirstName' ) . " " . DB::getSession ()->getData ( 'userLastName' );
				if (DB::isLoggedIn () && DB::getSession ()->isTeacher ())
					$mainGroup = "Lehrer";
				else if (DB::isLoggedIn () && DB::getSession ()->isPupil ())
					$mainGroup = "Schüler (Klasse " . DB::getSession ()->getPupilObject ()->getGrade () . ")";
				else if (DB::isLoggedIn () && DB::getSession ()->isEltern ())
					$mainGroup = "Eltern";
				else
					$mainGroup = "Sonstiger Benutzer";
			} else {
				$displayName = "Nicht angemeldet";
				$mainGroup = "";
			}
			
			$skinColor = DB::$mySettings ['skinColor'];
			
			
			if(DB::getSettings()->getValue('global-skin-default-color') != '') {
				if(DB::getSettings()->getBoolean('global-skin-force-color')) {
					$skinColor = DB::getSettings()->getValue('global-skin-default-color');
				}
				else if($skinColor == '') $skinColor = DB::getSettings()->getValue('global-skin-default-color');
			}
			
			
			if($skinColor == "") $skinColor = "green";		// Default für alle: Grün
			
			// Laufzettel Info
			
			if ($this->isActive("laufzettel") && DB::isLoggedIn () && DB::getSession ()->isTeacher ()) {
				$zuBestaetigen = DB::getDB ()->query_first ( "SELECT COUNT(laufzettelID) AS zubestaetigen FROM laufzettel WHERE laufzettelDatum >= CURDATE() AND laufzettelID IN (SELECT laufzettelID FROM laufzettel_stunden WHERE laufzettelLehrer LIKE '" . DB::getSession ()->getTeacherObject ()->getKuerzel () . "' AND laufzettelZustimmung=0)" );
				
				if ($zuBestaetigen [0] > 0) {
					if ($zuBestaetigen [0] == 1) {
						$nummer = "Ein";
						$verb = "wartet";
					} else {
						$nummer = $zuBestaetigen [0];
						$verb = "warten";
					}
					
					$infoLaufzettel = "<a href=\"index.php?page=laufzettel&mode=myLaufzettel\" class=\"btn btn-xs btn-info\"><i class=\"fa fa-check\"></i> " . $nummer . " Laufzettel $verb auf Ihre Zustimmung</a>";
				} else
					$infoLaufzettel = "";
			} else {
				$infoLaufzettel = "";
			}
			
			
			$infoMessages = "";
			
			// Debugger::debugObject(htmlspecialchars(DB::getTPL ()->get ( 'header/header' )),true);
			
			if(DB::isLoggedIn() && Message::userHasUnreadMessages()) {
			    
			    $countMessage = Message::getUnreadMessageNumber(DB::getSession()->getUser(), "POSTEINGANG", 0);
			    
			    if(DB::getSettings()->getBoolean('messages-banner-new-messages')) $infoMessages = "<a href=\"index.php?page=MessageInbox&folder=POSTEINGANG\" class=\"btn btn-danger btn-xs\"><i class=\"fa fa-envelope fa-spin\"></i> $countMessage ungelesene Nachricht" . (($countMessage > 1) ? "en" : "") . "</a>";
			    else $infoMessages = "";
			}
			else {
                $countMessage = 0;
            }
			
			// Fremdsession
			
			if(DB::isLoggedIn()) {
    			$fremdlogin = Fremdlogin::getMyFremdlogin();
    			
    			if($fremdlogin != null) {
    			    if($fremdlogin->getAdminUser() != null)
    			         $fremdloginUser = $fremdlogin->getAdminUser()->getDisplayNameWithFunction();
    			    else $fremdloginUser = "n/a";
    			    
    			    
    			    if($fremdlogin->getAdminUser() != null)
    			        $fremdloginUserID = $fremdlogin->getAdminUser()->getUserID();
    			        else $fremdloginUserID = "n/a";
    			        
    			        
    			    $fremdloginNachricht = $fremdlogin->getMessage();
    			    $fremdloginTime = functions::makeDateFromTimestamp($fremdlogin->getTime());
    			    $fremdloginID = $fremdlogin->getID();
    			    
    			}
    			
    			if(DB::getSession()->isDebugSession()) {
    			    $debugSession = true;
    			}
    			else {
    			    $debugSession = false;
    			}
			}
			
			
			if(DB::isLoggedIn() && $this->hasAdmin() && (DB::getSession()->isAdmin() || DB::getSession()->isMember($this->getAdminGroup()))) {
				$isAdmin = true;
			}
			else $isAdmin = false;
			
            // TODO: IF kann mit Version 1.2.3 entfernt werden.
			if($_REQUEST['page'] != "Update")
			    $this->acl();

			eval ( "\$this->header =  \"" . DB::getTPL ()->get ( 'header/header' ) . "\";" );
			
			/*
				 moved to PAGE.class.php
			// eval ( "\$this->footer =  \"" . DB::getTPL ()->get ( 'footer' ) . "\";" );
			*/
		}
	}

	private function prepareHeaderBar() {
		if(DB::isLoggedIn()) {

			$displayName = DB::getSession()->getData('userFirstName') . " " . DB::getSession()->getData('userLastName');
			if(DB::isLoggedIn() && DB::getSession()->isTeacher()) $mainGroup = "Lehrer";
			else if(DB::isLoggedIn() && DB::getSession()->isPupil()) $mainGroup = "Schüler (Klasse " . DB::getSession()->getPupilObject()->getGrade() . ")";
			else if(DB::isLoggedIn() && DB::getSession()->isEltern()) $mainGroup = "Eltern";
			else $mainGroup = "Anderer Benutzer";

			if(DB::isLoggedIn()) {
				$image = DB::getDB()->query_first("SELECT uploadID FROM image_uploads WHERE uploadUserName LIKE '" . DB::getSession()->getData("userName") . "'");



				if($image['uploadID'] > 0) $this->userImage = "index.php?page=userprofileuserimage&getImage=profile";
				else $this->userImage = "cssjs/images/userimages/default.png";
			}

			eval("\$this->loginStatus = \"" . DB::getTPL()->get("header/loginStatusLoggedIn") . "\";");
		}
		else {
			$this->displayName = "Nicht angemeldet";

			eval("\$this->loginStatus = \"" . DB::getTPL()->get("header/loginStatusNotLoggedIn") . "\";");
		}
	}

	/**
	 * Hilfsfunktion für die Seiten, um zu überprüfen, ob der aktuelle Benutzerzugriff hat, wenn der die Gruppe $groupName braucht
	 * @param unknown $groupName Benötigte Gruppe
	 */
	protected function checkAccessWithGroup($groupName) {
		$hasAccess = false;

		if(DB::isLoggedIn()) {
			if(in_array($groupName, DB::getSession()->getGroupNames())) $hasAccess = true;
		}

		if(!$hasAccess) {
			header("Location: index.php");
		}
	}

	/**
	 * Prüft, ob eine Person angemeldet ist.
	 */
	protected function checkLogin() {
		// Prüft, ob eine Person angemeldet ist.

		if(!DB::isLoggedIn()) {
			$page = $_REQUEST['page'];

			if(in_array($page, requesthandler::getAllowedActions())) {
				$redirectPage = $page;
			}
			else {
				$redirectPage = "index";
			}

			if($_REQUEST['message'] != "") {
				$message = "<div class=\"callout\">
         			<p><strong>" . addslashes($_REQUEST['message']) . "</strong></p>
        		</div>";
			}

			$valueusername = "";

			eval("echo(\"".DB::getTPL()->get("login/index")."\");");
			PAGE::kill(false);
      //exit(0);
		}
	}

	/**
	 * Zeigt die Seite an.
	 */
	public abstract function execute();

	/**
	 * @deprecated
	 */
	public static function notifyUserAdded($userID) {

	}

	/**
	 * @deprecated Soll in einem Cron abgearbeitet werden.
	 */
	public static function notifyUserDeleted($userID) {

	}

	/**
	 * Überprüft, ob der angegebene Klassenname aktiviert ist.
	 * @param String $name Klassenname
	 * @return boolean
	 */
	public static function isActive($name) {

		if(sizeof(self::$activePages) == 0) {
			$pages = DB::getDB()->query("SELECT * FROM site_activation WHERE siteIsActive=1");

			while($p = DB::getDB()->fetch_array($pages)) {
				self::$activePages[] = $p['siteName'];
			}
		}

		if(sizeof($name::onlyForSchool()) > 0) {
			if(!in_array(DB::getGlobalSettings()->schulnummer, $name::onlyForSchool())) {
				return false;
			}
		}

		if($name::siteIsAlwaysActive()) return true;

		return in_array($name, self::$activePages);

	}
	
	public static function getActivePages() {
	    return self::$activePages;
	}


	public static function hasSettings() {
		return false;
	}

	public static function getSettingsDescription() {
		return [];
	}

	/**
	 * Liest den Displaynamen der Seite aus.
	 */
	public abstract static function getSiteDisplayName();

	/**
	 * @deprecated
	 */
	public static function getUserGroups() {
		return [];
	}

	/**
	 * Zeigt an, ob die Seite immer aktiviert sein muss.
	 * @return boolean true: Seite kann nicht deaktiviert werden.
	 */
	public static function siteIsAlwaysActive() {
		return false;
	}
	
	/**
	 * Gibt an, ob eine Seite von anderen abhängig ist. Dadurch können diese nicht deaktiviert werden solange abgeleitete Seiten aktiv sind.
	 * @return String[] Seitennamen
	 */
	public static function dependsPage() {
		return [];
	}

	/**
	 * Liste der Schulnummer, für die diese Funktion exklusiv ist.
	 * @return String[] Liste der Schulnummern, leer wenn für alle
	 */
	public static function onlyForSchool() {
		return [];
	}

	/**
	 * Setzt das Modul in den Auslieferungszustand zurück.
	 * @return boolean Erfolgsmeldung
	 */
	public static function resetPage() {
		return true;	// Sollte eine Seite keine Rücksetzmeldung haben, dann ist das Trotzdem ein Erfolg.
	}
	
	/**
	 * Überprüft, ob die Seite eine Administration hat.
	 * @return boolean
	 */
	public static function hasAdmin() {
		return false;
	}
	
	/**
	 * Icon im Menü
	 * @return string
	 */
	public static function getAdminMenuIcon() {
		return 'fa fa-cogs';
	}
	
	/**
	 * Menügruppe in der das Adminmodul angezeigt wird.
	 * @return string
	 */
	public static function getAdminMenuGroup() {
		return 'NULL';
	}
	
	/**
	 * Icon der Menügruppe
	 * @return string
	 */
	public static function getAdminMenuGroupIcon() {
		return 'fa fa-cogs';	// Zahnrad
	}
	
	/**
	 * Überprüft, ob die Seite eine Benutzeradministration hat.
	 * @return boolean
	 */
	public static function hasUserAdmin() {
		return false;
	}
	
	/**
	 * Liest die Gruppe aus, die Zugriff auf die Administration des Moduls hat.
	 * @return String Gruppenname als String
	 */
	public static function getAdminGroup() {
		return NULL;
	}
	
	/**
	 * Zeigt die Administration an. (Nur Bereich innerhalb des Main Body)
	 * @param $selfURL URL zu sich selbst zurück (weitere Parameter können vom Script per & angehängt werden.)
	 * @return HTML
	 */
	public static function displayAdministration($selfURL) {
		return "";
	}
	
	/**
	 * Zeigt die Benutzeradministration an. (Nur Bereich innerhalb von einem TabbedPane, keinen Footer etc.)
	 * @param $selfURL URL zu sich selbst zurück (weitere Parameter können vom Script per & angehängt werden.)
	 */
	public static function displayUserAdministration($selfURL) {
		return "";
	}
	
	/**
	 * Benötigt das Modul eine zweiFaktor Authentifizierung.
	 * <i>Noch nicht implementiert!</i>
	 * @return boolean JaNein
	 */
	public static function need2Factor() {
		return false;
	}
	
	/**
	 * Archiviert das komplette Modul. (Rückgabe frei, je nach Modul)
	 * <i>Noch nicht implementiert!</i>
	 * @return boolean Erfolgreich?
	 */
	public static function archiveDataForSchoolYear() {
		return false;
	}
	
	/**
	 * Räumt das Modul regelmäßig per Cron auf.
	 * @return Erfolgsmeldung
	 */
	public static function cronTidyUp() {
		return true;
	}
	
	/**
	 * 
	 * @param user $user Benutzer
	 * @return boolean Zugriff
	 */
	public static function userHasAccess($user) {
		return false;
	}
	
	
	/**
	 * Gibt an, welche Aktion beim Schuljahreswechsel durchgeführt wird. (Leer, wenn keine Aktion erfolgt.)
	 * @return String
	 */
	public static function getActionSchuljahreswechsel() {
		return '';
	}
	
	/**
	 * Führt den Schuljahreswechsel durch.
	 * @param String $sqlDateFirstSchoolDay Erster Schultag
	 */
	public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {
		
	}


	/**
	 * Access Control List
	 * @return acl
	 */

	public function aclModuleName() {
		return get_called_class();
	}

	public function acl() {
		if (DB::getSession()) {
			$userID = DB::getSession()->getUser();
		}
		$moduleClass = get_called_class();
		if ($userID && $moduleClass) {
			$this->acl = ACL::getAcl($userID, $moduleClass, false);
		}
	}

	public function getAclAll() {
		return $this->acl;
	}

	public function getAcl() {
		return [ 'rights' => $this->acl['rights'], 'owne' => $this->acl['owne'] ];
	}

	public function getAclRead() {
		return $this->acl['rights']['read'];
	}

	public function getAclWrite() {
		return $this->acl['rights']['write'];
	}

	public function getAclDelete() {
		return $this->acl['rights']['delete'];
	}

}
