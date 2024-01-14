<?php

class setAntrag extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $image = $input['image'];
        if (!$image || $image == 'undefined') {
            return [
                'error' => true,
                'msg' => 'Missing Data: Image'
            ];
        }



        $user_id = (int)$input['user_id'];
        if (!$user_id || $user_id == 'undefined') {
            return [
                'error' => true,
                'msg' => 'Missing Data: User ID'
            ];
        }



        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        if (extAusweisModelAntrag::save($user_id, $image)) {

            return [
                'success' => true
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
