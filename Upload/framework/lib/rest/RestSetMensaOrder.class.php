<?php

class RestSetMensaOrder extends AbstractRest {
	
	protected $statusCode = 200;
	
	public function execute($input, $request) {

		$acl = $this->getAcl();

		if ($acl['rights']['read'] != 1) {
			return [
				'error' => true,
				'msg' => 'Keine Leserechte!'
			];
		}

		
		$data = $input['data'];

		$data['id'] = intval($data['id']);

		$userID = $this->user->getUserID();

		if ( !$data['id'] || !$userID ) {
			return [
				'error' => true,
				'msg' => 'Fehlende Daten'
			];
		}

		$dbRow = DB::getDB()->query_first("SELECT * FROM mensa_order WHERE userID = " . $userID . " AND speiseplanID = " . $data['id'] );

		if ( $dbRow['userID'] && $dbRow['speiseplanID'] ) {

			DB::getDB()->query("DELETE FROM mensa_order WHERE userID = " . $userID . " AND speiseplanID = " . $data['id'] );

			return [
				'error' => false,
				'done' => true,
				'booked' => 0,
				'msg' => 'Sie haben das Essen abbestellt.'
			];

		} else {

			$now = new DateTime();
			$now = $now->format('Y-m-d H:i:s');

			DB::getDB()->query("INSERT INTO mensa_order (
				`userID`,
				`speiseplanID`,
				`time`
				) values (
				'".DB::getDB()->escapeString($userID)."',
				'".DB::getDB()->escapeString($data['id'])."',
				'".$now."'
			);");

			return [
				'error' => false,
				'done' => true,
				'booked' => 1,
				'msg' => 'Das Essen wurde gebucht.'
			];

		}


		return [];
		

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
		return 'mensaSpeiseplan';
	}
	
}	

?>