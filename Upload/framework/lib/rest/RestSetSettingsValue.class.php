<?php

class RestSetSettingsValue extends AbstractRest {
    protected $statusCode = 200;
	
	public function execute($input, $request) {
	    $name = $request[1];
	    
	    DB::getSettings()->setValue($name, $input['newValue']);
	    
	    
	    return [
            'name' => $name,
	        'newValue' => $input['newValue']
	    ];
	}
	
	public function getAllowedMethod() {
	    return 'PATCH';
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