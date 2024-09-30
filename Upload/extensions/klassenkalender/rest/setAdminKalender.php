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


        $id = (int)$input['id'];

        $title = $input['title'];
        if ( !$title ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }

        if ( !$input['color'] || $input['color'] == 'undefined' ) {
            $input['color'] = '';
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

        $input['createdTime'] = date('Y-m-d H:i:s', time());

        $input['acl'] = 0;
        if ($_POST['acl']) {
            $acl = json_decode($_POST['acl']);
            if ($acl && $acl->aclID) {
                $input['acl'] = $acl->aclID;
            }
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Kalender.class.php';
        $class = new extKlassenkalenderModelKalender();

        if ( $insert_id = $class->save($input) ) {

            $acl = json_decode($_POST['acl']);
            if ( $acl ) {
                $acl->aclModuleClassParent = 'ext_klassenkalender';
                if ( $return = ACL::setAcl($acl) ) {

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