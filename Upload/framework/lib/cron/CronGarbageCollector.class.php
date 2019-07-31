<?php


class CronGarbageCollector extends AbstractCron {

	private $createdVPlans= "";
	
	public function __construct() {
	}

	public function execute() {
		DB::getDB()->query("DELETE FROM mail_send WHERE mailSent < (UNIX_TIMESTAMP() - 259200)");
		DB::getDB()->query("DELETE FROM cron_execution WHERE cronStartTime < (UNIX_TIMESTAMP() - 259200)");
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