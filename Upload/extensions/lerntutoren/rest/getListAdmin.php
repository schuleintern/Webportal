<?php

class getListAdmin extends AbstractRest
{

    protected $statusCode = 200;


    public function execute($input, $request)
    {

        //$acl = $this->getAcl();
        if ( !$this->canAdmin() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Slot.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Tutoren.class.php';

        $ret = [];
        $items = extLerntutorenModelTutoren::getAllByStatus();

        foreach ($items as $item) {


            $arr = [
                "id" => $item->getID(),
                "fach" => $item->getFach(),
                "jahrgang" => $item->getJahrgang(),
                "einheiten" => $item->getEinheiten(),
                "status" => $item->getStatus(),
                "user" => false,
                "slots" => $item->getSlotsCollection(),
                "diff" => $item->getSlotsDiff()
                
            ];
            $user = $item->getTutor();
            if ($user) {
	            $arr['user'] = $user->getCollection();
            }
            
            $ret[] = $arr;

        }


/*
        echo '<pre>';
        print_r($items);
        echo '</pre>';
        exit;
*/
        if (count($ret) > 0) {
            return $ret;
        }


        return [];

        /*
        return [
            'error' => true,
            'msg' => 'Error'
        ];
        */

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