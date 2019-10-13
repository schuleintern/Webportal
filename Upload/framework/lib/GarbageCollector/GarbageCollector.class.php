<?php

class GarbageCollector {
	private function __construct() {
		
	}
	
	/**
	 * Garbage Collection, die vor dem Ausführen jedes Requests ausgeführt wird.
	 * Keine aufwändigen Garbage Collections. Dafür die stündliche GarbageCollection nehmen!
     * @deprecated Alles nur noch im Cron aufräumen.
	 */
	public static function EveryRequest() {
	    // nichts
	}
}