<?php

class RestGetKalender extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {

		$userID = intval($request[1]);

		if (!$userID) {
			return [
				'error' => true,
				'msg' => 'Fehlende User ID'
			];
		}

		$kalender = [];
		$result = DB::getDB()->query("SELECT * FROM kalender_api ");
		while($row = DB::getDB()->fetch_array($result)) {
			
			$item = [
				'kalenderID' => $row['kalenderID'],
				'kalenderName' => $row['kalenderName'],
				'kalenderColor' => $row['kalenderColor']
			];

			$kalender[] = $item;
		}

		if(count($kalender) > 0) {

			return [
				'list' => $kalender
			];

		} else {
			return [
				'error' => true,
				'msg' => 'Es konnte kein Kalender geladen werden!'
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
		return true;
	}

}	

?>