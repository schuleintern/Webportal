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
	    return false;
	}
	
	/**
	 * Überprüft die Authentifizierung
	 * @param String $username Benutzername
	 * @param String $password Passwort
	 * @return boolean Login erfolgreich
	 */
	public function checkAuth($username, $password) {
	    return $username == 'API_MANAGEMENT_PORTAL' && $password == '82he98nh23f8h234epjf2349jf23409jf42309j243f90';
	}
	
}	

?>