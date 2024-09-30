<?php


class extKrankmeldungAdminSettings extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-plug"></i> Krankmeldung - Einstellungen';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        //$this->getAcl();

        if (!$this->canWrite()) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
            "scripts" => [
                PATH_COMPONENTS . 'system/adminSettings2/dist/js/chunk-vendors.js',
                PATH_COMPONENTS . 'system/adminSettings2/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "settings" => $this->getSettings()
            ]

        ]);

    }


    public static function getSettingsDescription()
    {

        $userGroups = usergroup::getAllOwnGroups();

        $options = [];

        for ($i = 0; $i < sizeof($userGroups); $i++) {
            $options[] = [
                'value' => md5($userGroups[$i]->getName()),
                'name' => $userGroups[$i]->getName()
            ];
        }

        $settings = [

            [
                'name' => "extKrankmeldung-form-hinweis",
                'typ' => 'TEXT',
                'title' => "Hinweistext über dem Formular",
                'desc' => ""
            ],
            [
                'name' => "extKrankmeldung-form-tage-max",
                'typ' => 'NUMBER',
                'title' => "Maximale Anzahl der Tage einer Krankmeldung",
                'desc' => ""
            ],
            [
                'name' => "extKrankmeldung-form-info-status",
                'typ' => 'BOOLEAN',
                'title' => "Bemerkungsfeld deaktivieren",
                'desc' => ""
            ],
            [
                'name' => "extKrankmeldung-form-info-hinweis",
                'typ' => 'TEXT',
                'title' => "Hinweistext unter dem Bemerkungsfeld im Formular",
                'desc' => ""
            ],
            [
                'name' => "extKrankmeldung-form-volljaehrige",
                'typ' => 'BOOLEAN',
                'title' => "Krankmeldung durch volljährige Schüler aktivieren",
                'desc' => ""
            ]
        ];
        return $settings;

    }

    public function taskSave($postData)
    {

        $request = $this->getRequest();
        if ($request['page'] && $postData['settings']) {
            foreach ($postData['settings'] as $item) {

                echo "INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" . DB::getDB()->escapeString($item['name']) . "',
				'" . DB::getDB()->escapeString(($item['value'])) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item['value']) . "'";

                DB::getDB()->query("INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" . DB::getDB()->escapeString($item['name']) . "',
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
