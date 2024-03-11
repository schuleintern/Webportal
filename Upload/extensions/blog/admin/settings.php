<?php

 

class extFinanzenAdminSettings extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-plug"></i> Finanzen - Einstellungen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        if ( !$this->canAdmin() ) {
            new errorPage('Kein Zugriff');
        }
		
		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
                PATH_COMPONENTS.'system/adminSettings2/dist/js/chunk-vendors.js',
                PATH_COMPONENTS.'system/adminSettings2/dist/js/app.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"settings" => $this->getSettings()
			]

		]);

	}


    public static function getSettingsDescription() {

        $userGroups = usergroup::getAllOwnGroups();

        $options = [];

        for($i = 0; $i < sizeof($userGroups); $i++) {
            $options[] = [
                'value' => md5($userGroups[$i]->getName()),
                'name' => $userGroups[$i]->getName()
            ];
        }

        $settings =  [

            [
                'name' => "extFinanzen-antrag-freigeben",
                'typ' => 'BOOLEAN',
                'title' => "Anträge werden automatisch freigegeben",
                'desc' => ""
            ],
            [
                'name' => "extFinanzen-bank-empfaenger",
                'typ' => 'STRING',
                'title' => "Überweisungsdaten - Empfänger",
                'desc' => ""
            ],
            [
                'name' => "extFinanzen-bank-name",
                'typ' => 'STRING',
                'title' => "Überweisungsdaten - Bankname",
                'desc' => ""
            ],
            [
                'name' => "extFinanzen-bank-iban",
                'typ' => 'STRING',
                'title' => "Überweisungsdaten - IBAN",
                'desc' => ""
            ],
            [
                'name' => "extFinanzen-bank-bic",
                'typ' => 'STRING',
                'title' => "Überweisungsdaten - BIC",
                'desc' => ""
            ],
            [
                'name' => "extFinanzen-ordernumber-prefix",
                'typ' => 'STRING',
                'title' => "Buchungs Nummer - Prefix",
                'desc' => "Default: si-"
            ],

            
            
            
        ];
        return $settings;

    }

    public function taskSave($postData) {

        $request = $this->getRequest();
        if ($request['page'] && $postData['settings']) {
            foreach($postData['settings'] as $item) {

                echo "INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" .DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'";

                DB::getDB()->query("INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" .DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'");
            }
            echo json_encode(['done' => 'true']);
        } else {
            echo json_encode(['error' => 'Fehler beim Speichern!']);
        }
    }



}
