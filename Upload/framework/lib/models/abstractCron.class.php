<?php


abstract class AbstractCron {

	public function __construct() {
		
	}
	
	public abstract function execute();
	
	public abstract function getName();
	
	public abstract function getDescription();
		
	/**
	 * 
	 * 
	 * @return ['success' => 'true/false', 'resultText' => 'Text, der in der Administration angezeigt wird.']
	 */
	public abstract function getCronResult();

    /**
     * Not implemented
     * @deprecated
     * @return boolean
     */
	public abstract function informAdminIfFail();
	
	public abstract function executeEveryXSeconds();
	
	
	/**
	 * Soll der Cron nur separat ausgeführt werden? (Aufruf muss über cron.php?cronkey=...&cronName=[NAME] erfolgen. Ausführungszeit wird ignoriert.
	 * @return boolean
	 */
	public function onlyExecuteSeparate() {
	    return false;
	}
}	

?>