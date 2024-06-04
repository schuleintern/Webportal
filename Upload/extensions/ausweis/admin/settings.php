<?php



class extAusweisAdminSettings extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-address-card"></i> Ausweis - Einstellungen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        if ( !$this->canWrite() ) {
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

        $settings =  [

            [
                'name' => "extAusweis-antrag-freigeben",
                'typ' => 'BOOLEAN',
                'title' => "Anträge werden automatisch freigegeben",
                'desc' => ""
            ],
            [
                'name' => "extAusweis-antrag-print-size",
                'typ' => 'STRING',
                'title' => "Ausweisdruck - Dokumentengröße",
                'desc' => "1083,680 = Breite,Höhe in px "
            ],
            [
                'name' => "extAusweis-antrag-print-name",
                'typ' => 'STRING',
                'title' => "Ausweisdruck - Name",
                'desc' => "100,200 = x,y "
            ],
            [
                'name' => "extAusweis-antrag-print-profil",
                'typ' => 'STRING',
                'title' => "Ausweisdruck - Profilbild",
                'desc' => "0 = nein; 100,200,150,200 = x,y,Breite,Höhe "
            ],
            [
                'name' => "extAusweis-antrag-print-city",
                'typ' => 'STRING',
                'title' => "Ausweisdruck - Stadt",
                'desc' => "0 = nein; 100,200 = x,y "
            ],
            [
                'name' => "extAusweis-antrag-print-birthday",
                'typ' => 'STRING',
                'title' => "Ausweisdruck - Geburtstag",
                'desc' => "0 = nein; 100,200 = x,y "
            ],
            [
                'name' => "extAusweis-antrag-print-layer_1",
                'typ' => 'BILD',
                'title' => "Ausweisdruck - Layer Hintergund",
                'desc' => "Path: ./data/ext_ausweis/vorlagen/"
            ],
            [
                'name' => "extAusweis-antrag-print-layer_2",
                'typ' => 'BILD',
                'title' => "Ausweisdruck - Layer Vordergrund",
                'desc' => "Path: ./data/ext_ausweis/vorlagen/"
            ]

            
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
