<?php

class getFolders extends AbstractRest {
	
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

        $collection_id = (int)$request[2];
        if (!$collection_id) {
            return [
                'error' => true,
                'msg' => 'Missing Data'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Folder.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';
        $data = extFilecollectModelFolder::getByCollectionID($collection_id);

        $ret = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                $arr = $item->getCollection();

                $files = extFilecollectModelFile::getByFolderID($arr['id']);
                $arr['files'] = [];
                foreach ($files as $file) {
                    $arr['files'][] = $file->getCollection(true);
                }


                $ret[] = $arr;
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