<?php

class RestGetKalender extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {

        $this->setAclGroup($this->aclModuleName());
        $this->acl();

		$acl = $this->getAclAll();



		if ($acl['user']['admin'] == 0 && $acl['rights']['read'] != 1) {
			return [
				'error' => true,
				'msg' => 'Keine Leserechte!'
			];
		}

		$kalender = [];
		$result = DB::getDB()->query("SELECT a.* FROM kalender_allInOne as a ORDER BY a.kalenderSort");
		while($row = DB::getDB()->fetch_array($result)) {
			
			$item = [
				'kalenderID' => intval($row['kalenderID']),
				'kalenderName' => $row['kalenderName'],
				'kalenderColor' => $row['kalenderColor'],
				'kalenderSort' => intval($row['kalenderSort']),
				'kalenderPreSelect' => intval($row['kalenderPreSelect']),
				'kalenderAcl' => $this->getAclByID($row['kalenderAcl']),
				'kalenderFerien' => intval($row['kalenderFerien']),
                'kalenderPublic' => intval($row['kalenderPublic'])
			];

			if (!$item['kalenderColor']) {
				$item['kalenderColor'] = '#ff22cc';
			}

			if (!$item['kalenderAcl']) {
				$item['kalenderAcl'] = $this->getAclAll();
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
		return 'kalenderAllInOne';
	}

	public static function getAdminGroup() {
    return 'Webportal_Kalender_allInOne_Admin';
	}
	
}	

?>