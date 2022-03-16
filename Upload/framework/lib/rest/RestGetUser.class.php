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
            return [
                'error' => true,
                'msg' => 'Missing Data.'
            ];
        }


        $searchs = explode(' ', trim((string)$request[1]) );
        if (!is_array($searchs) || count($searchs) <= 0) {
            return [];
        }
        $filterType = trim((string)$request[2]);
        if (!$filterType || $filterType == '') {
            $filterType = false;
        }
        $user_collection = [];

        // Lade ALLE User )=
        $users = user::getAll();
        foreach ($users as $user) {
            $user_collection[] = $user->getCollection();
        }


       /*
        echo '<pre>';
        print_r($klassen);
        echo '</pre>';

        exit;
       */

        $items = [];


        // Search
        foreach ($user_collection as $collection) {

            $found = 0;
            foreach($searchs as $search) {
                $search = strtolower($search);
                if ($filterType) {
                    if ($collection['type'] && $collection['type'] == $filterType) {
                        if ( $this->searchInUserCollection($collection, $search) ) {
                            $found++;
                        }
                    }
                } else {
                    if ( self::searchInUserCollection($collection, $search) == true ) {
                        $found++;
                    }
                }
            }
            if ( $found > 0 && count($searchs) != $found) {
                $found = false;
            }
            if ($found != 0) {
                $items[] = $collection;
            }
        }

        usort($items, function($a, $b) {
            return $a['nachname'] <=> $b['nachname'];
        });

		if(count($items) > 0) {

			return $items;

		} else {
            return [];
            /*
			return [
				'error' => true,
				'msg' => 'Es konnte kein Benutzer geladen werden!'
			];
            */
		}


		exit;
	}

    public static function searchInUserCollection($collection, $search) {

        if (!$collection || !$search) {
            return false;
        }

        if (strpos(strtolower($collection['vorname']), $search) !== false) {
            return true;
        } else if (strpos(strtolower($collection['nachname']), $search) !== false) {
            return true;
        } else if (isset($collection['klasse']) && strpos(strtolower($collection['klasse']), $search) !== false) {
            return true;
        }
        return false;

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