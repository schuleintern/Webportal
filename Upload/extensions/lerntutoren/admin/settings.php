<?php

 

class extLerntutorenAdminSettings extends AbstractPage {
	
	public static function getSiteDisplayName() {
		return '<i class="fa fa-graduation-cap"></i> Texte - Admin';
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
                'name' => "ext-lerntutoren-min-pro-einheit",
                'typ' => "NUMBER",
                'title' => "Minuten pro Einheit",
                'desc' => "(Default: 60)"
            ),
            array(
                'name' => "ext-lerntutoren-disclaimer",
                'typ' => "HTML",
                'title' => "Generelle Hinweise",
                'desc' => ""
            ),
            array(
                'name' => "ext-lerntutoren-index-add-text",
                'typ' => "HTML",
                'title' => "Hinweistext fürs Hinzufügen (Startseite)",
                'desc' => ""
            ),
            array(
                'name' => "ext-lerntutoren-form-text",
                'typ' => "HTML",
                'title' => "Hinweis über dem Erstellen Formular",
                'desc' => ""
            ),
            array(
                'name' => "ext-lerntutoren-show-text",
                'typ' => "HTML",
                'title' => "Hinweis auf der Detailansicht",
                'desc' => ""
            ),
            array(
                'name' => "ext-lerntutoren-myslots-hinweis",
                'typ' => "HTML",
                'title' => "Hinweis auf meiner Übersicht",
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
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'; ");
            }
            echo json_encode(['done' => 'true']);
        } else {
            echo json_encode(['error' => 'Fehler beim Speichern!']);
        }
    }

}
