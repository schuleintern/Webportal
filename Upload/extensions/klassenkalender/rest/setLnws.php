<?php

class setLnws extends AbstractRest
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


        $input['id'] = (int)$input['id'];

        if (!$input['title']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Title'
            ];
        }
        if (!$input['short']) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Kurztitel'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Lnw.class.php';
        $class = new extKlassenkalenderModelLnws();
        if ($insert_id = $class->add($input)) {

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