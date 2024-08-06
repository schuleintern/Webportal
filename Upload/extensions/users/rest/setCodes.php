<?php

namespace users\rest;
use AbstractRest;
use DB;
use extUsersModelCodes;
use extUsersModelSchueler;

class setCodes extends AbstractRest
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

        $userID = (int)$input['userID'];
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ID'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Schueler.class.php';
        $Schueler = new extUsersModelSchueler();
        $schueler = $Schueler->getByParentID($userID);


        if ($schueler[0]) {
            $schueler = $schueler[0];
        }
        $asvID = $schueler->getData('schuelerAsvID');
        if (!$asvID) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ASV ID'
            ];
        }

        $code = substr(md5($asvID), 0, 5) . "-" . substr(md5(rand()), 0, 10);


        include_once PATH_EXTENSION . 'models' . DS . 'Codes.class.php';
        $class = new extUsersModelCodes();


        if ($class->save([
            'codeSchuelerAsvID' => $asvID,
            'codeText' => $code,
            'codeUserID' => 0,
            'codePrinted' => 0
        ])) {

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