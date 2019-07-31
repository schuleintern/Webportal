<?php 


/**
 * Sendet E-Mailnachrichten
 * @author Christian
 *
 */

class FerienDownloader extends AbstractCron {
	
	public function __construct() {
		
	}
	
	public function execute() {
   
	    DB::getDB()->query("DELETE FROM kalender_ferien");
	    
	    $feriendata = file(DB::getGlobalSettings()->ferienURL);

	    if(sizeof($feriendata) > 0) {

            for ($i = 0; $i < sizeof($feriendata); $i++) {
                $ferien = explode(";", str_replace("\r\n", "", $feriendata[$i]));

                DB::getDB()->query("INSERT INTO kalender_ferien

                (ferienName, ferienStart, ferienEnde, ferienSchuljahr) values (
                    '" . DB::getDB()->escapeString($ferien[2]) . "',
                    '" . DateFunctions::getMySQLDateFromNaturalDate($ferien[0]) . "',
                    '" . DateFunctions::getMySQLDateFromNaturalDate($ferien[1]) . "',
                    '" . DB::getDB()->escapeString($ferien[3]) . "'


                )


            ");
            }

        }
	}
	
	public function getName() {
		return "Ferien anlegen";
	}
	
	public function getDescription() {
		return "Legt Ferientermine im Kalender an.";
	}
	
	/**
	 *
	 *
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public function getCronResult() {
		return ['success' => 1, 'resultText' => 'Erfolgreich'];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
	    return 1209600;		// Alle 2 Wochen ausführen.
	}
}



?>