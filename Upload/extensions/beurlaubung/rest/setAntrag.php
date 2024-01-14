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

        $volljaehrige = DB::getSettings()->getBoolean("extBeurlaubung-volljaehrige-schueler");
        if ($volljaehrige == 1) {
            $acl['rights']['write'] = 1;
        }

        if ( !$this->canWrite() ) {
            return [
                'error' => true,
                'msg' => 'Kein Zugriff'
            ];
        }

        $info = trim(nl2br($input['info']));
        if ( DB::getSettings()->getBoolean("extBeurlaubung-form-info-required") && ( !$info || $info == '' ) ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Info'
            ];
        }

        $schueler = (int)$input['schueler'];
        if ( !$schueler ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Schueler'
            ];
        }

        $date = explode(',', $input['date']);
        if ( !$date || !$date[0] ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: date'
            ];
        }


        $stunden = $input['stunden'];
        if ( !$stunden ) {
            return [
                'error' => true,
                'msg' => 'Missing Data: Stunden'
            ];
        }


        include_once PATH_EXTENSION . 'models' . DS . 'Antrag.class.php';

        $status = 1;
        $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");
        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        if ($freigabeKL == 0 && $freigabeSL == 0) {
            // Automatisch Freigeben
            $status = 2;
        }
        if ( extBeurlaubungModelAntrag::setAntrag($userID, $schueler, $date, $stunden, $info, $status) ) {

            if ($freigabeSL && DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-nachricht") ) {

                $messageSender = new MessageSender();
                $recipientHandler = new RecipientHandler("");
                $recipientHandler->addRecipient(new SchulleitungRecipient());
                $messageSender->setRecipients($recipientHandler);
                $messageSender->setSender(new user(['userID' => 0]));
                $messageSender->setSubject('Neuer Beurlaubungsantrag');
                $messageSender->setText('Es liegt ein neuer Beurlaubungsantrag vor. Bitte prüfen.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
                $messageSender->send();
            }

            if ($freigabeKL && DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-nachricht") ) {

                $schuelerUser = user::getUserByID($schueler);
                $schuelerUser = $schuelerUser->getCollection(true, false);
                $klasse = $schuelerUser['klasse'];
                if ($klasse) {
                    $messageSender = new MessageSender();
                    $recipientHandler = new RecipientHandler("");
                    $recipientHandler->addRecipient(new KlassenleitungRecipient( $klasse ));
                    $messageSender->setRecipients($recipientHandler);
                    $messageSender->setSender(new user(['userID' => 0]));
                    $messageSender->setSubject('Neuer Beurlaubungsantrag');
                    $messageSender->setText('Es liegt ein neuer Beurlaubungsantrag vor. Bitte prüfen.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
                    $messageSender->send();
                }

            }

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