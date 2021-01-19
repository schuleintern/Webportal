<?php 


/**
 * AllInOne Kalender Set Ferien
 * @author Christian Marienfeld
 *
 */

class AllInOneKalenderFerien extends AbstractCron {
	
	public function __construct() {
		
	}
	
	public function execute() {
		
		$ferien_kalender = DB::getDB()->query_first("SELECT kalenderID FROM kalender_allInOne WHERE kalenderFerien = 1");

		if ( !intval($ferien_kalender['kalenderID']) ) {
			return false;
		}
		
	  DB::getDB()->query("DELETE FROM kalender_allInOne_eintrag WHERE kalenderID = ".intval($ferien_kalender['kalenderID']) );
		

		$feriendata = file(DB::getGlobalSettings()->ferienURL);

		$now = new DateTime();
		$now = $now->format('Y-m-d H:i:s');

		if(sizeof($feriendata) > 0) {

			for ($i = 0; $i < sizeof($feriendata); $i++) {
					
				$ferien = explode(";", str_replace("\r\n", "", $feriendata[$i]));

				DB::getDB()->query("INSERT INTO kalender_allInOne_eintrag (
					kalenderID,
					eintragTitel,
					eintragDatumStart,
					eintragDatumEnde,
					eintragTimeStart,
					eintragTimeEnde,
					eintragOrt,
					eintragKommentar,
					eintragUserID,
					eintragCreatedTime
					) values (
					".intval($ferien_kalender['kalenderID']).",
					'".DB::getDB()->escapeString($ferien[2])."',
					'".DateFunctions::getMySQLDateFromNaturalDate($ferien[0])."',
					'".DateFunctions::getMySQLDateFromNaturalDate($ferien[1])."',
					'00:00:00',
					'00:00:00',
					'',
					'',
					0,
					'".$now."'
				);");

			}

		}

	}
	
	public function getName() {
		return "AllInOne Kalender Ferien anlegen";
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