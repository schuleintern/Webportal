<?php

class GarbageCollector {
	private function __construct() {
		
	}
	
	/**
	 * Garbage Collection, die vor dem Ausführen jedes Requests ausgeführt wird.
	 * Keine aufwändigen Garbage Collections. Dafür die stündliche GarbageCollection nehmen!
	 */
	public static function EveryRequest() {
		// Informationen über ausgeführte Tasks löschen, die älter als 10 Tage sind.
		DB::getDB()->query("DELETE FROM cron_execution WHERE cronStartTime < (UNIX_TIMESTAMP() - 864000)");
		
		// Briefe löschen, die älter als 30 Minuten sind und nicht dauerhaft gespeichert werden sollen.
		DB::getDB()->query("DELETE FROM schueler_briefe WHERE briefSaveLonger > 0 AND UNIX_TIMESTAMP() > (briefSaveLonger+30*60)");
		
		// "Angekündigt" entfernen bei SA, KA und Modustest
		if(!DB::getSettings()->getBoolean('klassenkalender-update-alwayshow')){
		    DB::getDB()->query("UPDATE kalender_lnw SET eintragAlwaysShow=0 WHERE eintragArt IN('SCHULAUFGABE','KURZARBEIT','MODUSTEST','NACHHOLSCHULAUFGABE')");
		    DB::getSettings()->setValue('klassenkalender-update-alwayshow',true);
		}
	}
}