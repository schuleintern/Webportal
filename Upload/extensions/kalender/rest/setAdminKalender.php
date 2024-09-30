<?php

class setAdminKalender extends AbstractRest {
	
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
        /*
        if ((int)$acl['rights']['write'] !== 1 || DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) !== true ) {
        //if ((int)$acl['rights']['write'] !== 1 && (int)DB::getSession()->getUser()->isAnyAdmin() !== 1 ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
        */


        $id = (int)$input['id'];


        $title = $input['title'];
        if ( !$title ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }

        $admins = $_POST['admins'];
        if ( $admins ) {
            $foo = [];
            $admins = json_decode($admins);
            foreach ($admins as $admin) {
                if ($admin->id) {
                    $foo[] = $admin->id;
                }
            }
            $input['admins'] = json_encode($foo);
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';

        if ( $insert_id = extKalenderModelKalender::submitData($input) ) {

            $acl = json_decode($_POST['acl']);
            if ( $acl ) {
                $acl->aclModuleClassParent = 'ext_kalender';
                if ( $return = ACL::setAcl($acl) ) {
                    extKalenderModelKalender::updateAcl($id, $return['aclID']);
                }
            }

            return [
                'success' => true,
                'id' => $insert_id
            ];
        }

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
        return true;
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