<?php

class RestSetAcl extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {
	
	
		if ( $input['acl'] ) {
			$row = $input['acl'];
		} else {
			return [
				'error' => true,
				'msg' => 'Fehlende Daten'
			];
		}
		$module = $request[1];
		if (!$module) {
			return [
				'error' => true,
				'msg' => 'Fehlendes Modul!'
			];
		}

		$return = ACL::setAcl($row);

		if ( $return ) {
			return [
				'error' => false,
				'done' => true
			];
		} else {
			return $return;
		}

		

		exit;
	}

	public function getAllowedMethod() {
		return 'POST';
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

	public function needsUserAuth() {
		return true;
	}


}	

?>