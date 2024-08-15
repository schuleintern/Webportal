<?php

class setAnswer extends AbstractRest
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




        
        $childs = $_POST['childs'];
        if ($childs) {
            $childs = json_decode($childs);
        }


        include_once PATH_EXTENSION . 'models' . DS .'Answer.class.php';

        $class = new extUmfragenModelAnswer();

        $count = 0;

        foreach($childs as $child) {

            if ( $class->save([
                'list_id' => $child->list_id,
                'item_id' => $child->id,
                'content' => $child->value,
                'createdTime' => date('Y-m-d H:i', time()),
                'createdUserID' => $user->getUserID()
            ]) ) {
                $count++;
            }
        }


        if ( $count == count($childs) ) {

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