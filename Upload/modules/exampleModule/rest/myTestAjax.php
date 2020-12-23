<?php

class myTestAjax extends AbstractRest {
	
	protected $statusCode = 200;

	public function execute($input, $request) {

		// Mach hier etwas cooles!

		return [
			'error' => true,
			'msg' => 'Return Data!'
		];

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'GET';
	}

	/**
	 * Ist eine System Authentifizierung nötig? (mit API key)
	 * @return Boolean
	 */
	public function needsSystemAuth() {
		return false;
	}

	/**
	 * Muss der Benutzer eingeloggt sein?
	 * @return Boolean
	 */
	public function needsUserAuth() {
		return true;
	}

}	

?>