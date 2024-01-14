<?php

class getMy extends AbstractRest
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

        include_once PATH_EXTENSION . 'models' . DS .'List.class.php';
        
        $class = new extUmfragenModelList();
        $tmp_data = $class->getMy( $user->getUserID() );


        include_once PATH_EXTENSION . 'models' . DS .'Answer.class.php';
        $sub = new extUmfragenModelAnswer();

        $ret = [];
        if ($tmp_data) {
            foreach ($tmp_data as $item) {
                $foo = $item->getCollection(true, false, true);

                $tmp_answers = $sub->getByParentAndUserID($foo['id'], $user->getUserID());
                if ($tmp_answers) {
                    $answers = [];
                    foreach ($tmp_answers as $answer) {
                        $answers[] = $answer->getCollection();
                    }
                    $foo['answers'] = $answers;
                }
                $foo['userlist'] = false;
                $ret[] = $foo;
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

}

?>