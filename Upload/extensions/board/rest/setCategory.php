<?php

class setCategory extends AbstractRest
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
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];

        $title = (string)$input['title'];
        if ( !$title ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        $state = (int)$input['state'];
        if ( !$state ) {
            $state = 0;
        }

        $acl = (string)$_POST['acl'];
        if ( !$acl ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ACL'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS .'Category.class.php';
        $class = new extBoardModelCategory();

        if ( $class->save([
            'id' => $id,
            'title' => $title,
            'state' => $state,
            'acl' => $acl,
            'createdTime' => date('Y-m-d', time()),
            'createdUserID' => $user->getUserID()
        ]) ) {

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
        return 'POST';
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