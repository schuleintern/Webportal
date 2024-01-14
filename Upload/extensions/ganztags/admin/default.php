<?php

 

class extGanztagsAdminDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fas fa-people-arrows"></i> Ganztags - Admin Einstellungen';
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
                'name' => "ext_ganztags-day-mo",
                'typ' => "BOOLEAN",
                'title' => "Montag anzeigen?",
                'text' => ""
            ),
            array(
                'name' => "ext_ganztags-day-di",
                'typ' => "BOOLEAN",
                'title' => "Dienstag anzeigen?",
                'text' => ""
            ),
            array(
                'name' => "ext_ganztags-day-mi",
                'typ' => "BOOLEAN",
                'title' => "Mittwoch anzeigen?",
                'text' => ""
            ),
            array(
                'name' => "ext_ganztags-day-do",
                'typ' => "BOOLEAN",
                'title' => "Donnerstag anzeigen?",
                'text' => ""
            ),
            array(
                'name' => "ext_ganztags-day-fr",
                'typ' => "BOOLEAN",
                'title' => "Freitag anzeigen?",
                'text' => ""
            ),
            array(
                'name' => "ext_ganztags-day-sa",
                'typ' => "BOOLEAN",
                'title' => "Samstag anzeigen?",
                'text' => ""
            ),
            array(
                'name' => "ext_ganztags-day-so",
                'typ' => "BOOLEAN",
                'title' => "Sonntag anzeigen?",
                'text' => ""
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
