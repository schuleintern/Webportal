<?php

class CronStatMaker extends AbstractCron {

	private $isSuccess = true;
	private $listDeleted = "";
	private $listCreated = "";

	private $cronResult = ['success' => true, 'resultText' => ""];

	
	public function __construct() {
	}

	public function execute() {
	    UserLoginStat::createStatItem();
	}
	
	public function getName() {
		return "Login Statistik erstellen";
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
	    if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
	    	return $this->cronResult;
	    }
	    else {
	    	return ['success' => true, 'resultText' => ''];
	    	
	    }
	}
	
	public function informAdminIfFail() {
		return false;
	}
	
	public function executeEveryXSeconds() {
		return 300;		// Einmal alle zwei Stunden ausführen
	}
}



?>