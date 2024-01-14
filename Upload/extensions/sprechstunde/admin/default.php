<?php



class extSprechstundeAdminDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-people-arrows"></i> Sprechstunde - Admin Einstellungen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();

        $user = DB::getSession()->getUser();

        if ( !$user->isAnyAdmin() ) {
            new errorPage('Kein Zugriff');
        }

		$this->render([
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
				PATH_COMPONENTS.'system/adminSettings/dist/main.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"settings" => $this->getSettings()
			]

		]);

	}


	public static function getSettingsDescription() {

		$settings = array(
			array(
				'name' => "extSprechstunde-day-mo",
				'typ' => "BOOLEAN",
				'title' => "Montag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "extSprechstunde-day-di",
				'typ' => "BOOLEAN",
				'title' => "Dienstag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "extSprechstunde-day-mi",
				'typ' => "BOOLEAN",
				'title' => "Mittwoch anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "extSprechstunde-day-do",
				'typ' => "BOOLEAN",
				'title' => "Donnerstag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "extSprechstunde-day-fr",
				'typ' => "BOOLEAN",
				'title' => "Freitag anzeigen?",
				'desc' => ""
			),
            array(
                'name' => "extSprechstunde-day-sa",
                'typ' => "BOOLEAN",
                'title' => "Samstag anzeigen?",
                'desc' => ""
            ),
            array(
                'name' => "extSprechstunde-day-so",
                'typ' => "BOOLEAN",
                'title' => "Sonntag anzeigen?",
                'desc' => ""
            ),
            array(
                'name' => "extSprechstunde-time-start",
                'typ' => "STRING",
                'title' => "Uhrzeit - Start",
                'desc' => "Format: 00:00"
            ),
            array(
                'name' => "extSprechstunde-time-end",
                'typ' => "STRING",
                'title' => "Uhrzeit - Ende",
                'desc' => "Format: 00:00"
            ),
            array(
                'name' => "extSprechstunde-calendar-info-head",
                'typ' => "HTML",
                'title' => "Kalender - Hinweis",
                'desc' => ""
            ),
            array(
                'name' => "extSprechstunde-planer-info-head",
                'typ' => "HTML",
                'title' => "Planer - Hinweis",
                'desc' => ""
			),
			array(
                'name' => "extSprechstunde-form-medium-phone",
                'typ' => "BOOLEAN",
                'title' => "Termin Buchen: Telefon anzeigen?",
                'desc' => ""
            ),
			array(
                'name' => "extSprechstunde-form-medium-viko",
                'typ' => "BOOLEAN",
                'title' => "Termin Buchen: Videokonferenz anzeigen?",
                'desc' => ""
            )

		);
		return $settings;

	}

	public function taskSave($postData) {

		$request = $this->getRequest();
		if ($request['page'] && $postData['settings']) {
			foreach($postData['settings'] as $item) {
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
