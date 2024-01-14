<?php

 

class extVplanAdminSettings extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fas fa-cogs"></i> Vertretungsplan - Admin Einstellungen';
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

        $settings = array(
            array(
                'name' => "extVplan-days-show",
                'typ' => "NUMBER",
                'title' => "Wie viele Tage sollen angezeigt werden?",
                'desc' => "Default: 2 (Heute und Morgen)"
            ),

            
            array(
                'name' => "extVplan-col-show-klasse",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Klasse",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-user_neu",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Vertreterin",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-user_alt",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Lehrerin (Absenz)",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-fach_alt",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Fach (alt)",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-fach_neu",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Fach (neu)",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-raum_alt",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Raum (alt)",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-raum_neu",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Raum (neu)",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-info_1",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Info 1",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-info_2",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Info 2",
                'desc' => ""
            ),
            array(
                'name' => "extVplan-col-show-info_3",
                'typ' => "ACL",
                'title' => "Spalte anzeigen: Info 3",
                'desc' => ""
            ),
            

            array(
                'name' => "extVplan-import-untis-col-stunde",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Stunde",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-klasse",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Klasse",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-user_alt",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Lehrerin (Absenz)",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-user_neu",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Vertreterin",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-fach_alt",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Fach (alt)",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-fach_neu",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Fach (neu)",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-raum_alt",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Raum (alt)",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-raum_neu",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Raum (neu)",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-info_1",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Infospalte 1",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-info_2",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Infospalte 2",
                'desc' => "UNTIS - HTML Import"
            ),
            array(
                'name' => "extVplan-import-untis-col-info_3",
                'typ' => "NUMBER",
                'title' => "In welcher Spalte steht: Infospalte 3",
                'desc' => "UNTIS - HTML Import"
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
