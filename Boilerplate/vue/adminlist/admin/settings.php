<?php

class extPodcastAdminSettings extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-microphone-alt"></i> Podcast - Einstellungen';
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
                'name' => "extBeurlaubung-form-info-required",
                'typ' => 'BOOLEAN',
                'title' => "Formular - Begründung als Pfichtfeld",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-klassenleitung-freigabe",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubungen müssen von der Klassenleitung freigegeben werden?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-klassenleitung-nachricht",
                'typ' => 'BOOLEAN',
                'title' => "Klassenleitung bei neuen Anträgen per Nachricht informieren?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-schulleitung-freigabe",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubungen müssen von der Schulleitung freigegeben werden?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-schulleitung-nachricht",
                'typ' => 'BOOLEAN',
                'title' => "Schulleitung bei neuen Anträgen per Nachricht informieren?",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-volljaehrige-schueler",
                'typ' => 'BOOLEAN',
                'title' => "Beurlaubung durch volljährige Schüler aktivieren",
                'desc' => "Ist diese Einstellung aktiv, können sich volljährige Schüler selbst krank melden."
            ],
            [
                'name' => "extBeurlaubung-antrag-open",
                'typ' => 'TEXT',
                'title' => "Hinweistext - Neuen Antrag stellen",
                'desc' => ""
            ],
            [
                'name' => "extBeurlaubung-antrag-finish",
                'typ' => 'TEXT',
                'title' => "Hinweistext - Nach stellen des Antrags",
                'desc' => ""
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
