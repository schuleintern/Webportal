<?php

class RestDeleteKalenderEintrag extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {


		$acl = $this->getAcl();

		if ($acl['rights']['delete'] != 1) {
			return [
				'error' => true,
				'msg' => 'Keine Löschrechte!'
			];
		}

		$row = [];

		if ( $input['data'] ) {
			$ID = intval($input['data']);
		} else {
			return [
				'error' => true,
				'msg' => 'Fehlende Daten'
			];
		}

		if ( !$ID ) {
			return [
				'error' => true,
				'msg' => 'Fehlende Kalender Eintrag ID'
			];
		}
		

		if ( $ID ) {

			$dbRow = DB::getDB()->query_first("SELECT eintragID FROM kalender_api_eintrag WHERE eintragID = " . $ID . "");

			if ( $dbRow['eintragID'] == $ID ) {

				DB::getDB()->query("DELETE FROM kalender_api_eintrag WHERE eintragID= " . $ID . "");


			} else {
				return [
					'error' => true,
					'msg' => 'Fehlende Kalender Eintrag'
				];
			}

								
		} 

		return [
			'error' => false,
			'done' => true
		];

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

	public function aclModuleName() {
		return 'apiKalender';
	}

}	

?>