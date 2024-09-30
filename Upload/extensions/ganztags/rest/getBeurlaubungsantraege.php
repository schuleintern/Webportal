<?php

class getBeurlaubungsantraege extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }
        $pupilID = $request[2];
        if (!$pupilID) {
            return [
                'error' => true,
                'msg' => 'Missing Schueler ID'
            ];
        }

        $acl = $this->getAcl();
        if ( !$this->canAdmin() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $ret = [];
        

        include_once (PATH_LIB.'data/extensions/ExtensionsPages.php');
        if ( $extension = ExtensionsPages::isActive('ext.zwiebelgasse.beurlaubung') ) {
            ExtensionsPages::loadModules( $extension['folder'] );


            $class = new extBeurlaubungModelAntrag();
            $data = $class->getByUserID($pupilID);

            if (count($data) > 0) {
                foreach ($data as $item) {
    
                    $ret[] = $item->getCollection();
                }
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