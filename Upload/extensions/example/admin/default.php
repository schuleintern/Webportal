<?php

class extExampleAdminDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Example Module - Admin Einstellungen';
	}

	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, $request, $extension);
		$this->checkLogin();
	}

	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
		
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
				'name' => "example-speiseplan-days",
				'typ' => "NUMBER",
				'title' => "Wie viele Tage vorher muss gebucht werden?",
				'desc' => "Default: 1"
			),
			array(
				'name' => "test",
				'typ' => "BOOLEAN",
				'title' => "Montag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "example-speiseplan-day-di",
				'typ' => "BOOLEAN",
				'title' => "Dienstag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "example-speiseplan-day-mi",
				'typ' => "BOOLEAN",
				'title' => "Mittwoch anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "example-speiseplan-day-do",
				'typ' => "BOOLEAN",
				'title' => "Donnerstag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "example-speiseplan-day-fr",
				'typ' => "BOOLEAN",
				'title' => "Freitag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "example-speiseplan-day-sa",
				'typ' => "SELECT",
				'title' => "Samstag anzeigen?",
				'desc' => "",
				'options' => [
					[
						'title' => 'Auswahl 1',
						'value' => 1
					],
					[
						'title' => 'Auswahl 2',
						'value' => 2
					]
				]
			),
			array(
				'name' => "example-speiseplan-day-so",
				'typ' => "STRING",
				'title' => "Sonntag anzeigen?",
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
