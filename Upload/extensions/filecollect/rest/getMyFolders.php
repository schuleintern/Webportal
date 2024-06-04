<?php

class getMyFolders extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ((int)$acl['rights']['read'] !== 1 && (int)DB::getSession()->isMember($this->extension['adminGroupName']) !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Folder.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';

        $data = extFilecollectModelFolder::getByUserID($userID);
        $now = new DateTime();

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                $foo = $item->getCollection();
                $future_date = new DateTime($foo['endDate']);
                $interval = $future_date->diff($now);
                $foo['endDateNow'] = $interval->format("%a Tage %h Stunden %i Minuten");
                $foo['endDate'] = $future_date->format("d.m.Y H:i");
                $foo['files'] = [];
                $foo['members'] = false;
                $files = extFilecollectModelFile::getByUserID($userID, $foo['id'] );
                foreach ($files as $file) {
                    $foo['files'][] = $file->getCollection();
                }
                $foo['anzahl'] = $foo['anzahl'] - count($foo['files']);
                if ($foo['anzahl'] == 0) {
                    $foo['done'] = true;
                }
                $ret[] = $foo;
            }
        }
        return $ret;
	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'GET';
	}


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
        return true;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }
    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth() {
        return false;
    }

}	

?>