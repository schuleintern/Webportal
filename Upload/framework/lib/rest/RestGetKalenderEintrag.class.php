<?php

class RestGetKalenderEintrag extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {

		$kalenderIDs = explode('-', $request[1]);
		$userID = intval($request[2]);

		if (!$userID) {
			return [
				'error' => true,
				'msg' => 'Fehlende User ID'
			];
		}
		// $user = new user(array('userID' => $userID));
		// if (!$user) {
		// 	return [
		// 		'error' => true,
		// 		'msg' => 'Fehlender User'
		// 	];
		// }
		if (count($kalenderIDs) <= 0) {
			return [
				'error' => true,
				'msg' => 'Fehlende Kalender IDs'
			];
		}

		$ret = [];
		$where = '';
		foreach ($kalenderIDs as $value) {
			if (intval($value) > 0) {
				if ($where != '') { $where .= ' OR '; }
				$where .= 'kalenderID = '. intval($value);
			}
		}
		$result = DB::getDB()->query("SELECT * FROM kalender_api_eintrag WHERE ".$where);
		while($row = DB::getDB()->fetch_array($result)) {
			
			$createdUser = new user(array('userID' => intval($row['eintragUser']) ));

			$item = [
				'eintragID' => $row['eintragID'],
				'kalenderID' => $row['kalenderID'],
				'eintragKategorieID' => $row['eintragKategorieID'],
				'eintragTitel' => $row['eintragTitel'],
				'eintragDatumStart' => $row['eintragDatumStart'],
				'eintragDatumEnde' => $row['eintragDatumEnde'],
				'eintragOrt' => $row['eintragOrt'],
				'eintragKommentar' => $row['eintragKommentar'],
				'eintragCreatedTime' => $row['eintragCreatedTime'],
				'eintragModifiedTime' => $row['eintragModifiedTime'],
				'eintragUserID' => $row['eintragUser'],
				'eintragUserName' => $createdUser->getDisplayName()
			];

			$ret[] = $item;
		}

		if(count($ret) > 0) {

			return [
				'list' => $ret
			];

		} else {
			return [
				'error' => true,
				'msg' => 'Es konnte kein Eintrag geladen werden!'
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