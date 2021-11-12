<?php



class MessageConfirm extends AbstractPage {
	
	public function __construct() {		
		parent::__construct ( array (
			"Nachricht","Empfang bestätigen"
		) );
			
	}
	public function execute() {
		$mailID = $_GET['mailID'];
		$code = $_GET['a'];
		
		$mail = Message::getByID(intval($mailID));
		if($mail != null && $mail->needConfirmation() && $mail->getConfirmationSecret() == $code) {

		    if($mail->getUser() != null) {
		        $userID = $mail->getUser()->getUserID();
		        
		        if(!$mail->hasQuestions()) {
                    if(DB::getSettings()->getBoolean('messages-hook-sent-mail-confirm')) {
                        $mail->confirmMessage('MAIL');
                    }
                    if(DB::getSettings()->getBoolean('messages-hook-sent-mail-read')) {
                        $mail->setRead();
                    }
                    $append = "&confirmSuccess=1";
		        }
		        else {
		            $append = "&toggleQuestionConfirmation=1";
		        }
		        
		        $redirectURL = "index.php?page=MessageRead&messageID=38032&messageID=" . $mail->getID() . $append;
		        
		        
		        if(!DB::isLoggedIn() && $userID > 0) {
                    session::loginAndCreateSession($userID, false);


                    header("Location: " . $redirectURL);
                    exit(0);
		        }
		        else {
		          header("Location: " . $redirectURL);
		          exit(0);
		            
		        }
		    }
		    else {
		        new errorPage("Kein Benutzer");
		    }
		}
		
		die("Falscher Zugriff!");
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
		return '';
	}
	
	/**
	 * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
	 * @return array(array('groupName' => '', 'beschreibung' => ''))
	 */
	public static function getUserGroups() {
		return [];
	
	}
	
	public static function siteIsAlwaysActive() {
		return true;
	}
}

?>