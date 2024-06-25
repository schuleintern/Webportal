<?php

class setUpload extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {



        $folderID = $request[2];
        if (!$folderID || $folderID == 'undefined') {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing Folder'
            ];
        }
        
        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ( !$this->canWrite() ) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }



        $file = $_FILES['file'];
        if (!$file) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Missing Files'
            ];
        }
        $info = pathinfo($file['name']);

        $allowedString = DB::getSettings()->getValue("extFileshare-extension-allowed");
        if (!$allowedString) {
            $allowedString = '';
        }
        $allowed = explode(',',$allowedString );

        if ( !in_array( $info['extension'], $allowed) ) {
            $this->statusCode = 400;
            return [
                'error' => true,
                'msg' => 'Falsches Dateiformat'
            ];
        }

        /*
        if (!$folderID) {
            include_once PATH_EXTENSION . 'models' . DS .'List.class.php';
            $class = new extUmfragenModelList();
            $db = $class->save([
                'title' => 'none',
                'state' => 1,
                'createdTime' => date('Y-m-d H:i', time()),
                'createdUserID' => $userID
            ]);
            $folderID = $db->lastID;
        }
        */
        
        $target_Path = PATH_DATA . 'ext_fileshare'.DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }

        $target_Path = $target_Path . DS. $folderID . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }

        include_once PATH_EXTENSION . 'models' . DS .'List.class.php';
        $classList = new extFileshareModelList();
        $list = $classList->getByFolderID($folderID);

        if ( !$list ) {
            $foo = $classList->save([
                'folder' => $folderID,
                'title' => 'Kein Titel',
                'createdUserID' => $userID
            ]);
            $lastID = $foo->lastID;
        } else {
            if ($userID != $list->getCreatedUserID()) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff'
                ];
            }
            $lastID = $list->getID();
        }

        //$newname = 'user_'.$userID.'_'.time().'.' . $info['extension'];

        if (move_uploaded_file($file['tmp_name'], $target_Path . $file['name'])) {

            include_once PATH_EXTENSION . 'models' . DS .'Item.class.php';
            $class = new extFileshareModelItem();
            $class->save([
                'folder' => $folderID,
                'title' => $file['name'],
                'filename' => $file['name'],
                'sort' => 0,
                'createdUserID' => $userID,
                'list_id' => $lastID
            ]);

            return [
                'error' => false,
                'filename' => $target_Path . $file['name'],
                'parentID' => $folderID
                //'path' => URL_ROOT. DS.'tmp'.DS.'ext_ausweis'.DS.$file['name']
            ];
        }

        
/*

        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        if ( extFinanzenModelAntrag::save($userID, $title, $payee, $users, $amount, $dueDate, $receipt ) ) {

     
            return [
                'error' => false,
                'success' => true
            ];
        }
*/
        return [
            'error' => true,
            'msg' => 'Error'
        ];

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'POST';
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