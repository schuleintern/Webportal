<?php 

class CronVerspaetungAuswertung extends AbstractCron {

	private $result = null;
	
	public function __construct() {
		
	}
	
	public function execute() {
		if(DB::getSettings()->getValue('absenzen-lehrer-schwelle-warnung-verspaetung') > 0) {
		    if(
		        DB::getSettings()->getBoolean('absenzen-lehrer-schwelle-warnung-verspaetung-info-klassenleitung') || 
		        DB::getSettings()->getBoolean('absenzen-lehrer-schwelle-warnung-verspaetung-info-schulleitung')
		     ) {
		         
		         $schwelle = DB::getSettings()->getValue('absenzen-lehrer-schwelle-warnung-verspaetung');
		         
	               $data = DB::getDB()->query("
                        SELECT * FROM schueler WHERE 
                            (SELECT COUNT(*) FROM absenzen_verspaetungen WHERE 	verspaetungSchuelerAsvID=schuelerAsvID AND verspaetungBenachrichtigt=0) >= $schwelle");

	               $schueler = [];
                   while($d = DB::getDB()->fetch_array($data)) {
                       $schueler[] = new schueler($d);
                   }
                   
                   for($i = 0; $i < sizeof($schueler); $i++) {
                       $verspaetungen = AbsenzVerspaetung::getAllForSchueler($schueler[$i]);
                       
                       $verspaetungText = "";
                       
                       for($v = 0; $v < sizeof($verspaetungen); $v++) {
                           if(!$verspaetungen[$v]->isBenachrichtigt()){
                               $verspaetungText .= "<li>" . $verspaetungen[$v]->getMinuten() . " Minuten am " . $verspaetungen[$v]->getDateAsNaturalDate() . " zur " . $verspaetungen[$v]->getStunde() . ". Stunde</li>";
                               $verspaetungen[$v]->setIsBenachrichtigt();
                           }
                       }
                       
                       $text = "Für folgenden Schüler / folgende Schüler ist der Schwellwert von " . $schwelle . " Verspätungen überschritten worden:<br />";
                       $text .= "Name: " . $schueler[$i]->getCompleteSchuelerName() . "<br />";
                       $text .= "Klasse: " . $schueler[$i]->getKlasse() . "<br />";
                       $text .= "Liste der Verspätungen: <br /><ul>" . $verspaetungText . "</ul>";
                       $text .= "<a href=\"" . DB::getGlobalSettings()->urlToIndexPHP . "?page=absenzenlehrer&mode=showSchuelerVerspaetungen&schuelerAsvID=" . $schueler[$i]->getAsvID() . "\">Direkt zu Bearbeitung der Verspätungen.</a><br /><br />";
                       $text .= "<i>Dies ist eine automatisch versendete Nachricht.";
                       
                       
                       $sender = new MessageSender();
                       $recipientHandler = new RecipientHandler("");
                       
                       if(DB::getSettings()->getBoolean('absenzen-lehrer-schwelle-warnung-verspaetung-info-klassenleitung')) {
                           $recipientHandler->addRecipient(new KlassenleitungRecipient($schueler[$i]->getKlasse()));
                       }
                       
                       if(DB::getSettings()->getBoolean('absenzen-lehrer-schwelle-warnung-verspaetung-info-schulleitung')) {
                           $recipientHandler->addRecipient(new SchulleitungRecipient());
                       }
                       
                       $sender->setRecipients($recipientHandler);
                       $sender->setSubject("Verspätungsschwellenüberschreitung - " . $schueler[$i]->getCompleteSchuelerName());
                       $sender->setText($text);
                       $sender->setSender(user::getSystemUser());
                       $sender->send();                    
                       
                   }
		         
		         
		     }
		}
	}
	
	public function getName() {
		return "Verspätungen auswerten";
	}
	
	public function getDescription() {
		return "Wertet die Verspätungen aus und sendet Nachrichten.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		return ['success' => 1, 'resultText' => ""];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
	    return 86400;		// 1 mal am Tag
	}
}



?>