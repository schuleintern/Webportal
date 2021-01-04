<?php

class adminExampleDefault extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return 'Admin - Settings';
	}


	public function __construct($request = [], $extension = []) {
		parent::__construct(array( self::getSiteDisplayName() ), false, false, false, 'module', $request, $extension);
		$this->checkLogin();
	}


	public function execute() {

		//$this->getRequest();
		//$this->getAcl();
		

		$this->render([
			//"tmpl" => "default",
			"tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
			"scripts" => [
				PATH_COMPONENTS.'system/adminSettings/dist/main.js'
			],
			"data" => [
				"selfURL" => URL_SELF,
				"settings" => $this->getSettings()
			],
			"submenu" => [
				[
					"url" => "index.php?page=example",
					"title" => "Default",
					"icon" => "fa fa-cogs"
				],
				[
					"url" => "index.php?page=example&view=list",
					"title" => "List",
					"icon" => "fa fa-book"
				],

				[
					"admin" => true,
					"url" => "index.php?page=example&view=default&admin=true",
					"title" => "Einstellungen",
					"icon" => "fa fa-book"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=acl&admin=true",
					"title" => "Benutzerrechte",
					"icon" => "fa fa-book"
				],
				[
					"admin" => true,
					"url" => "index.php?page=example&view=custom&admin=true",
					"title" => "Admin Custom",
					"icon" => "fa fa-book"
				]
			]
		]);

	}


	public function taskSave() {

		$request = $this->getRequest();
		$_post = json_decode(file_get_contents("php://input"), TRUE);

		if ($request['page'] && $_post['settings']) {
			foreach($_post['settings'] as $item) {
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


	public static function getSettingsDescription() {
		//return array();

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
				'typ' => "BOOLEAN",
				'title' => "Samstag anzeigen?",
				'desc' => ""
			),
			array(
				'name' => "example-speiseplan-day-so",
				'typ' => "BOOLEAN",
				'title' => "Sonntag anzeigen?",
				'desc' => ""
			)
		);
		return $settings;

	}

}
