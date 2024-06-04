<?php

class getList extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {



        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return [
                'error' => true,
                'msg' => 'Missing User ID'
            ];
        }

        $acl = $this->getAcl();
        if ( !$this->canRead() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }
   
        
        $dayShow = (int)DB::getSettings()->getValue('extVplan-days-show');
        if (!$dayShow) {
            $dayShow = 2;
        }
        if ($dayShow <= 0) {
            return PAGE::exitJsonError();
        }
       
        $ret = [];

        include_once PATH_EXTENSION . 'models' . DS . 'List.class.php';
        include_once PATH_EXTENSION . 'models' . DS . 'Day.class.php';


        $date = DateFunctions::getTodayAsSQLDate();

        // $data = extVplanModelList::getByDate($date);


        for ($i = 1; $i <= $dayShow; $i++) {

            
            //echo DateFunctions::getWeekDayFromSQLDate($date).'#';

            

            // 	0 (für Sonntag) bis 6 (für Samstag)
            if (DateFunctions::getWeekDayFromSQLDate($date) == 0 || DateFunctions::getWeekDayFromSQLDate($date) == 6) {
                $i--;
            } else {
                $tmp_data = extVplanModelList::getByDate($date);
                $tmp_item = [];
                foreach ($tmp_data as $item) {
                    $tmp_item[] = $item->getCollection();
                }
    
                $day_data = extVplanModelDay::getByDate($date);
    
                $strDate = DateFunctions::getNaturalDateFromMySQLDateShort($date);
                if ($i == 1) {
                    $strDate = 'Heute - '.$strDate;
                } else if ($i == 2) {
                    $strDate = 'Morgen - '.$strDate;
                } else {
                    $days = ['So','Mo','Di','Mi','Do','Fr','Sa'];
                    
                    $strDate =  $days[DateFunctions::getWeekDayFromSQLDate($date)].' - '.$strDate;
                }
                $ret[] = [
                    "date" => $strDate,
                    "data" => $tmp_item,
                    "day" => $day_data->getCollection()
                ];
            }
            $date = DateFunctions::addOneDayToMySqlDate($date);
            
        }





        return $ret;

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'GET';
	}


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
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
    public function needsSystemAuth() {
        return false;
    }

}	

?>