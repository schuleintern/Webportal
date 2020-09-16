<?php

class RestGetAcl extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {

		$module = $request[1];
		if (!$module) {
			return [
				'error' => true,
				'msg' => 'Fehlendes Modul!'
			];
		}
		$result = DB::getDB()->query_first("SELECT * FROM acl WHERE moduleClass = '".$module."'");
		
		if( $result['id'] ) {

			return [
				'acl' => $result
			];

		} else {
			return [
				'error' => true,
				'msg' => 'Es konnte keine ACL gefunden werden!'
			];
		}

		exit;
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
		return false;
	}

	public function needsUserAuth() {
		return true;
	}


}	

?>