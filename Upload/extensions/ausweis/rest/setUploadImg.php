<?php

class setUploadImg extends AbstractRest {
	
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
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }



        $file = $_FILES['file'];
        if (!$file) {
            return [
                'error' => true,
                'msg' => 'Missing Files'
            ];
        }
        $info = pathinfo($file['name']);
        if ($info['extension'] != 'png' && $info['extension'] != 'jpg') {
            return [
                'error' => true,
                'msg' => 'Falsches Bildformat'
            ];
        }

        
        $target_Path = PATH_WWW_TMP . 'ext_ausweis'.DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }
        /*
        $target_Path = $target_Path . DS. 'user_'.$userID . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }*/
        $newname = 'user_'.$userID.'_'.time().'.' . $info['extension'];

        if (move_uploaded_file($file['tmp_name'], $target_Path . $newname)) {
            return [
                'error' => false,
                'filename' => $target_Path . $newname,
                'path' => URL_ROOT. DS.'tmp'.DS.'ext_ausweis'.DS.$newname
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