<?php



class extUsersAdminCodes extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-user-shield"></i> Benutzer - Eltern Initial-Codes';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/codes/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/codes/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/users",
                "acl" => $acl['rights']
            ]
        ]);

	}

    public function taskPrint($postData)
    {

        $id = $postData['a'];
        if (!$id) {
            $this->reloadWithoutParam('task');
        }


        $data = DB::run('SELECT * FROM eltern_codes 
                JOIN schueler ON codeSchuelerAsvID=schuelerAsvID 
                 WHERE codeID = :id', ['id' => $id])->fetch();


        if($data['schuelerAsvID'] != "") {

            $text = DB::getSettings()->getValue("createusers-letterneweltern");

            $text = str_replace("{SCHUELERNAME}", $data['schuelerName'] . ", " . $data['schuelerRufname'], $text);
            $text = str_replace("{KLASSE}", $data['schuelerKlasse'],$text);
            $text = str_replace("{CODE}", $data['codeText'],$text);

            DB::getDB()->query("UPDATE eltern_codes SET codePrinted=UNIX_TIMESTAMP() WHERE codeSchuelerAsvID='{$data['schuelerAsvID']}'");


            $letter = new PrintLetterWithWindowA4("Registrierungscode");
            $letter->setBetreff("Registrierungscode für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
            $adresse = DB::getDB()->query_first("SELECT * FROM eltern_adressen WHERE adresseSchuelerAsvID='" . $data['schuelerAsvID'] . "' ORDER BY adresseIsHauptansprechpartner DESC LIMIT 1");
            $briefAdresse = "";
            if($adresse['adresseID'] > 0) {
                $briefAdresse = $adresse['adresseVorname'].' '.$adresse['adresseFamilienname'] . "\r\n" . $adresse['adresseStrasse'] . " " . $adresse['adresseNummer'] . "\r\n" . $adresse['adressePostleitzahl'] . " " . $adresse['adresseOrt'];
            }
            $letter->setDatum(DateFunctions::getTodayAsNaturalDate());
            $letter->addLetter($briefAdresse, $text);
            $letter->send();
            exit(0);
        }


    }

}
