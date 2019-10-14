<?php


class CronGarbageCollector extends AbstractCron {

	private $createdVPlans= "";
	
	public function __construct() {
	}

	public function execute() {

        /**
         * Alte
         */
		DB::getDB()->query("DELETE FROM cron_execution WHERE cronStartTime < (UNIX_TIMESTAMP() - 259200)");

        // Briefe löschen, die älter als 30 Minuten sind und nicht dauerhaft gespeichert werden sollen.
        DB::getDB()->query("DELETE FROM schueler_briefe WHERE briefSaveLonger > 0 AND UNIX_TIMESTAMP() > (briefSaveLonger+30*60)");

    }
	
	public function getName() {
		return "Datenbank aufräumen";
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
            'resultText' => 'Aufgeräumt.'
        ];
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 21600;		// Zwei mal am Tag ausführen
	}
}



?>