<?php

class setBoard extends AbstractRest
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

        $state = (int)$input['state'];
        if ( !$state ) {
            $state = 0;
        }
        $title = (string)$input['title'];
        if ( !$title ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        $cat_id = (int)$input['cat_id'];
        if ( !$cat_id ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Kategorie'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS .'Board.class.php';
        $class = new extBoardModelBoard();

        if ( $class->save([
            'id' => $id,
            'state' => $state,
            'title' => $title,
            'cat_id' => $cat_id,
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