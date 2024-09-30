<?php

class setItemRead extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        $user = DB::getSession()->getUser();
        if (!$user) {
            return [
                'error' => true,
                'msg' => 'Missing User'
            ];
        }
        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $item_id = (int)$request[2];
        if ( !$item_id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ID'
            ];
        }

        $user_id = $user->getUserID();
        if ( !$user_id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: User'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS .'ItemRead.class.php';
        $class = new extBoardModelItemRead();

        $data = [
            'item_id' => $item_id,
            'user_id' => $user_id
        ];



        if ( $class->save($data) ) {

            return [
                'success' => true
            ];

        }


        return [
            'error' => true,
            'msg' => 'Nicht Erfolgreich!'
        ];

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
    public function needsSystemAuth()
    {
        return false;
    }

}

?>