<?php

class RestGetKalender extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {

		$acl = $this->getAcl();

		if ($acl['rights']['read'] != 1) {
			return [
				'error' => true,
				'msg' => 'Keine Leserechte!'
			];
		}

		$kalender = [];
		$result = DB::getDB()->query("SELECT * FROM kalender_api ORDER BY kalenderSort");
		while($row = DB::getDB()->fetch_array($result)) {
			
			$item = [
				'kalenderID' => $row['kalenderID'],
				'kalenderName' => $row['kalenderName'],
				'kalenderColor' => $row['kalenderColor'],
				'kalenderSort' => $row['kalenderSort']
			];

			if (!$item['kalenderColor']) {
				$item['kalenderColor'] = '#ff22cc';
			}

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
		return false;
	}

	public function needsUserAuth() {
		return true;
	}

	public function aclModuleName() {
		return 'apiKalender';
	}

}	

?>