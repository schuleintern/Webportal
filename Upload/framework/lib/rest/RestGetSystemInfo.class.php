<?php

class RestGetSystemInfo extends AbstractRest {
    protected $statusCode = 200;
	
	public function execute($input, $request) {
	    
	    return DB::getGlobalSettings();
	}
	
	public function getAllowedMethod() {
	    return 'GET';
	}

	protected function malformedRequest() {
	    $this->statusCode = 400;
	}
	
	/**
	 * Überprüft, ob ein Modul eine System Authentifizierung benötigt. (z.B. zum Abfragen aller Schülerdaten)
	 * @return boolean
	 */
	public function needsSystemAuth() {
	    return true;
	}
}	

?>