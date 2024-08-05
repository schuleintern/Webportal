<?php

class getMyBoards extends AbstractRest
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

        include_once PATH_EXTENSION . 'models' . DS . 'Board.class.php';

        $class = new extBoardModelBoard();
        $tmp_data = $class->getByState([1], 'sort');

        $ret = [];
        foreach($tmp_data as $item) {
            $collection = $item->getCollection(true, true, true, $this->getAdminGroup() );
            if ($collection) {
                $ret[] = $collection;
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


    public function needsAppAuth() {
        return false;
    }

}

?>