<?php

class setAdminSlots extends AbstractRest
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


        if ( DB::getSession()->isAdminOrGroupAdmin($this->extension['adminGroupName']) !== true ) {
            if ((int)$acl['rights']['write'] !== 1) {
                return [
                    'error' => true,
                    'msg' => 'Kein Zugriff'
                ];
            }
        }



        $items = json_decode($_POST['slots']);
        if (count($items) < 1) {
            return [
                'error' => true,
                'msg' => 'Missing Items'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Slots.class.php';

        foreach ($items as $item) {
            if ( extFehltageModelSlots::submit($item) == false ) {
                return [
                    'error' => true,
                    'msg' => 'Fehler beim Speichern!'
                ];
            }
        }


        $data = extFehltageModelSlots::getAll();

        $ret = [];
        foreach($data as $item) {
            $ret[] = $item->getCollection();
        }

        return [
            'error' => false,
            'insert' => true,
            'data' => $ret
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