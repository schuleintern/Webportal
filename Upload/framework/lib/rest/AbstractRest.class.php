<?php

abstract class AbstractRest {
    protected $statusCode = 200;

	public function __construct() {
		
	}
	
	public abstract function execute($input, $request);
	
	public abstract function getAllowedMethod();
	
	public function getStatusCode() {
	    return $this->statusCode;
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
	
	/**
	 * Überprüft die Authentifizierung
	 * @param String $username Benutzername
	 * @param String $password Passwort
	 * @return boolean Login erfolgreich
	 */
	public function checkAuth($username, $password) {
	    return false;
	}
	
}	

?>