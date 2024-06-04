<?php

class getUser extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        //$user = DB::getSession()->getUser();


        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $user = DB::getSession()->getUser();
   
        if ($user) {
            include_once PATH_EXTENSION . 'models' . DS . 'User.class.php';

            $class = new extUserModelUser();
            $tmp_data = $class->getByID($user->getUserID());
    
            return $tmp_data->getCollection(true);
        }
        return [];
        

    }


    /**
     * Set Allowed Request Method
     * (GET, POST, ...)
     *
     * @return String
     */
    public function getAllowedMethod()
    {
        return 'GET';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return false;
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
    public function needsSystemAuth()
    {
        return false;
    }


    public function needsAppAuth() {
        return true;
    }

}

?>