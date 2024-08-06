<?php

namespace users\rest;
use AbstractRest;
use DB;
use extUsersModelTeacher;

class setTeacher extends AbstractRest
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

        $userid = (int)$input['userid'];

        $asvid = (string)$input['asvid'];
        if (!$asvid) {
            return [
                'error' => true,
                'msg' => 'Missing Data: ASV ID'
            ];
        }
        $vorname = (string)$input['vorname'];
        if (!$vorname) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Vorname'
            ];
        }
        $nachname = (string)$input['nachname'];
        if (!$nachname) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Nachname'
            ];
        }
        $short = (string)$input['short'];
        if (!$short) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Kürzel'
            ];
        }
        $rufname = (string)$input['rufname'];
        if (!$rufname) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Rufname'
            ];
        }
        $gender = (string)$input['gender'];
        if (!$gender) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Geschlecht'
            ];
        }
        $zeugniss = (string)$input['zeugniss'];
        if (!$zeugniss) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Zeugnissunterschrift'
            ];
        }
        $amtbez = (int)$input['amtbez'];
        if (!$amtbez) {
            $amtbez = 0;
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Teacher.class.php';
        $class = new extUsersModelTeacher();

        if ($class->save([
            'lehrerID' => $id,
            'lehrerAsvID' => $asvid,
            'lehrerKuerzel' => $short,
            'lehrerVornamen' => $vorname,
            'lehrerName' => $nachname,
            'lehrerKuerzel' => $short,
            'lehrerRufname' => $rufname,
            'lehrerGeschlecht' => $gender,
            'lehrerZeugnisunterschrift' => $zeugniss,
            'lehrerAmtsbezeichnung' => $amtbez,
            'lehrerUserID' => $userid
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