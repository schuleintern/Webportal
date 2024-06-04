<?php

class getStundenplan extends AbstractRest
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

        //$currentPlanID = stundenplandata::getCurrentStundenplanID();

        //echo $currentPlanID;

        $currentPlan = stundenplandata::getCurrentStundenplan();


        $title = '';
        if ($input['key'] && $input['value']) {
            //$plan = $currentPlan->getPlan( [$input['key'], $input['value']] );
            //return $plan;
            $key = (string)$input['key'];
            $value = (string)$input['value'];
        } else {
            if ( $user->isPupil() ) {
                $key = 'grade';
                $value = $user->getPupilObject()->getKlasse();
                
            }
            if ( $user->isTeacher() ) {
                $key = 'teacher';
                $value = $user->getTeacherObject()->getKuerzel();
            }
            if ( $user->isEltern() ) {
                $key = 'grade';
                $klassen = $user->getElternObject()->getKlassenAsArray();
                if ($klassen && $klassen[0]) {
                    $value = $klassen[0];
                }
            }
    
        }

        
        if ($key && $value) {
            $plan = $currentPlan->getPlan( [$key, $value] );
            
            if ($key == 'grade') {
                $title = 'Klasse: '.$value;
            } else if ($key == 'teacher') {
                $title = 'Lehrer*in: '.$value;
            } else if ($key == 'subject') {
                $title = 'Fach: '.$value;
            } else if ($key == 'room') {
                $title = 'Raumplan: '.$value;
            }

            if ( EXTENSION::isActive('ext.zwiebelgasse.vplan') ) {
               
                include_once PATH_EXTENSIONS . 'vplan' . DS. 'models' . DS . 'List.class.php';
                $date = DateFunctions::getTodayAsSQLDate();
                $tmp_data = extVplanModelList::getByDate($date);

                $vplan = [];
                foreach($tmp_data as $item) {
                    $temp = $item->getCollection();
                    //$date = DATE('N', DateFunctions::getUnixTimeFromMySQLDate($item->getDate()));
                    $temp['daynum'] = DATE('N', DateFunctions::getUnixTimeFromMySQLDate($item->getDate()));

                    $vplan[] = $temp;
                }
            }
            

            return [
                'plan' => $plan,
                'title' => $title,
                'active' => $value,
                'vplan' => $vplan
            ];
        }



        return [];
       


        /*
        include_once PATH_EXTENSION . 'models' . DS . 'Stundenplan.class.php';

        $class = new extStundenplanModelStundenplan;
        $tmp_data = $class->getAll();

        echo '<pre>';
        print_r($tmp_data);
        echo '</pre>';

        $ret = [];
        foreach ($tmp_data as $item) {
            $collection = $item->getCollection(true);
            $ret[] = $collection;
        }
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
        return 'POST';
    }


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth()
    {
        return false;
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

    public function needsAppAuth()
    {
        return true;
    }

    
}

?>