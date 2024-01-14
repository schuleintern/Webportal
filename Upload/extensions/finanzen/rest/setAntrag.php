<?php

class setAntrag extends AbstractRest {
	
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
        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $title = (string)$input['title'];
        if ( !$title || $title == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Empfänger'
            ];
        }
        
        $payee = (string)$input['payee'];
        if ( !$payee || $payee == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Empfänger'
            ];
        }

        $users = (string)$input['users'];
        if ( !$users || $users == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Benutzer*innen'
            ];
        }

        $amount = (string)$input['amount'];
        if ( !$amount || $amount == 'undefined' ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Betrag'
            ];
        }

        $dueDate = (string)$input['dueDate'];
        if (!$dueDate || $dueDate == 'undefined') {
            $dueDate = date('Y-m-d', time());
        }
        $receipt = (string)$input['receipt'];
        if (!$receipt || $receipt == 'undefined') {
            $receipt = 0;
        }

        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        $class = new extFinanzenModelAntrag();
    

        if ( $class->save([
            'state' => 1,
            'title' => $title,
            'payee' => $payee,
            'users' => $users,
            'amount' => $amount,
            'dueDate' => $dueDate,
            'receipt' => $receipt,
            'createdUserID' => $userID,
            'createdTime' => date( 'Y-m-d H:i:s', time() ),
            ]) ) {

     
            return [
                'error' => false,
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
	public function getAllowedMethod() {
		return 'POST';
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