<?php

class RestGetSettingsValue extends AbstractRest {
    protected $statusCode = 200;
	
	public function execute($input, $request) {
	    $name = $request[1];
	    
	    $settingsValue = DB::getSettings()->getValue($name);
	    
	    if($settingsValue != "") {
	        return [
	            'name' => $name,
	            'value' => $settingsValue
	        ];
	    }
	    else {
	        $this->statusCode = 404;
	        return [
	            'name' => $name,
	            'value' => $settingsValue
	        ];
	    }
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