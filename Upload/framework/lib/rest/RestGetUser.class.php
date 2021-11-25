<?php

class RestGetUser extends AbstractRest {
	protected $statusCode = 200;

	public function execute($input, $request) {


        /*
		$acl = $this->getAclAll();

		if ($acl['user']['admin'] == 0 && $acl['rights']['read'] != 1) {
			return [
				'error' => true,
				'msg' => 'Keine Leserechte!'
			];
		}
        */

        if (!$request[1]) {
            return false;
        }

		$items = [];
		$result = DB::getDB()->query("SELECT a.userID
                    FROM users as a
                    WHERE userFirstName LIKE '%".$request[1]."%'
                    OR userLastName LIKE '%".$request[1]."%' 
                    OR userName LIKE '%".$request[1]."%' 
                    OR userEMail LIKE '%".$request[1]."%' 
                    ORDER BY a.userLastName");
		while($row = DB::getDB()->fetch_array($result)) {

            $user = user::getUserByID(intval($row['userID']));
			/*$item = [
				'id' => $user->getUserID(),
				'vorname' => $user->getFirstName(),
				'nachname' => $user->getFirstName(),
                'userName' => $row['userLastName'],
                'userEMail' => $row['userLastName'],
				'name' => $row['userFirstName'].' '.$row['userLastName']
			];*/

            $items[] = $user->getCollection();
		}

		if(count($items) > 0) {

			return $items;

		} else {
			return [
				'error' => true,
				'msg' => 'Es konnte kein Benutzer geladen werden!'
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

	public function needsUserAuth() {
		return false;
	}

	public function aclModuleName() {
		return false;
	}

	public static function getAdminGroup() {
    return false;
	}
	
}	

?>