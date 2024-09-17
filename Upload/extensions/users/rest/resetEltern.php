<?php

class resetEltern extends AbstractRest
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
        if (!$this->canWrite()) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $id = (int)$input['id'];


        $email = (string)$input['email'];
        if (!$email) {
            return [
                'error' => true,
                'msg' => 'Missing Data: E-Mail'
            ];
        }
        $asvid = (string)$input['asvid'];
        if (!$asvid) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ASVID'
            ];
        }



        include_once PATH_EXTENSION . 'models' . DS . 'Eltern.class.php';
        $class = new extUsersModelEltern();
        //$tmp_data = $class->getAll();

        $item = $class->getByParentID($email);

        if ($item) {

            if (DB::run("UPDATE eltern_email SET elternUserID = NULL WHERE elternEMail = :email AND elternSchuelerAsvID = :asv",[
                'email' => $email,
                'asv' => $asvid
            ]) ) {

                return [
                    'success' => true
                ];

            }

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