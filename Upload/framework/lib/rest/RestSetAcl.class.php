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


		if ( $row['id'] ) {

			$dbRow = DB::getDB()->query_first("SELECT id FROM acl WHERE id = " . intval($row['id']) . " AND moduleClass = '".$module."'");

			if ( $dbRow['id'] ) {
				
				DB::getDB()->query("UPDATE acl SET
					schuelerRead = ".intval($row['schuelerRead']).",
					schuelerWrite = ".intval($row['schuelerWrite']).",
					schuelerDelete = ".intval($row['schuelerDelete']).",
					elternRead = ".intval($row['elternRead']).",
					elternWrite = ".intval($row['elternWrite']).",
					elternDelete = ".intval($row['elternDelete']).",
					lehrerRead = ".intval($row['lehrerRead']).",
					lehrerWrite = ".intval($row['lehrerWrite']).",
					lehrerDelete = ".intval($row['lehrerDelete']).",
					noneRead = ".intval($row['noneRead']).",
					noneWrite = ".intval($row['noneWrite']).",
					noneDelete = ".intval($row['noneDelete']).",
					owneRead = ".intval($row['owneRead']).",
					owneWrite = ".intval($row['owneWrite']).",
					owneDelete = ".intval($row['owneDelete'])."
					WHERE id = " . intval($row['id']) . ";");
		
			} else {
				return [
					'error' => true,
					'msg' => 'Fehlende ACL Eintrag'
				];
			}

								
		} else {

			DB::getDB()->query("INSERT INTO acl (
				moduleClass,
				schuelerRead,
				schuelerWrite,
				schuelerDelete,
				elternRead,
				elternWrite,
				elternDelete,
        lehrerRead,
        lehrerWrite,
        lehrerDelete,
        noneRead,
        noneWrite,
        noneDelete,
        owneRead,
        owneWrite,
				owneDelete
				) values (
				'".DB::getDB()->encodeString($module)."',
				'".DB::getDB()->encodeString($row['schuelerRead'])."',
				'".DB::getDB()->escapeString($row['schuelerWrite'])."',
				'".DB::getDB()->escapeString($row['schuelerDelete'])."',
				'".DB::getDB()->encodeString($row['elternRead'])."',
				'".DB::getDB()->encodeString($row['elternWrite'])."',
				'".DB::getDB()->encodeString($row['elternDelete'])."',
				'".DB::getDB()->encodeString($row['lehrerRead'])."',
				'".DB::getDB()->encodeString($row['lehrerWrite'])."',
				'".DB::getDB()->encodeString($row['lehrerDelete'])."',
				'".DB::getDB()->encodeString($row['noneRead'])."',
				'".DB::getDB()->encodeString($row['noneWrite'])."',
				'".DB::getDB()->encodeString($row['noneDelete'])."',
				'".DB::getDB()->encodeString($row['owneRead'])."',
				'".DB::getDB()->encodeString($row['owneWrite'])."',
				'".DB::getDB()->encodeString($row['owneDelete'])."'
			);");

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


}	

?>