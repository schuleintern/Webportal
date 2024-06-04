<?php


class extStundenplanAdminSettings extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-plug"></i> Stundenplan - Einstellungen';
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

        if (!$this->canAdmin()) {
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


        $settings = [

            [
                'name' => "ext-stundenplan-everydayothertimes",
                'typ' => 'BOOLEAN',
                'title' => "Verschiedene Zeiten für jeden Tag definieren?",
                'desc' => "Nach dem setzen dieser Einstellung bitte einmal abspeichern und zu den Einstellungen zurückkehren, um die einzelnen Tage zu sehen."
            ],
            [
                'name' => "ext-stundenplan-anzahlstunden",
                'typ' => 'NUMBER',
                'title' => "Maximale Anzahl der Stunden pro Tag",
                'desc' => "Nach dem setzen dieser Einstellung bitte einmal abspeichern und zu den Einstellungen zurückkehren, um die einzelnen Tage zu sehen."
            ]


        ];

        $tage = 1;

        if (DB::getSettings()->getValue("ext-stundenplan-everydayothertimes") == 1) {
            $tage = 5;
        }

        if (DB::getSettings()->getValue("ext-stundenplan-anzahlstunden") > 0) {
            $stunden = DB::getSettings()->getValue("ext-stundenplan-anzahlstunden");
        } else {
            $stunden = 10;
        }

        $tagNamen = array("", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag");


        for ($i = 1; $i <= $stunden; $i++) {
            for ($t = 1; $t <= $tage; $t++) {
                $settings[] = array(
                    'name' => "ext-stundenplan-stunde-$t-$i-start",
                    'typ' => 'UHRZEIT',
                    'title' => (($tage > 1) ? $tagNamen[$t] . "<br />" : "") . "Beginn der $i. Stunde",
                    'desc' => "Format: HH:mm<br /><b>Achten Sie bitte darauf führende Nullen mit einzugeben! Geben Sie bitte keine Leerzeichen ein.</b>",
                    'required' => true
                );
            }

            for ($t = 1; $t <= $tage; $t++) {
                $settings[] = array(
                    'name' => "ext-stundenplan-stunde-$t-$i-ende",
                    'typ' => 'UHRZEIT',
                    'title' => (($tage > 1) ? $tagNamen[$t] . "<br />" : "") . "Ende der $i. Stunde",
                    'desc' => "Format: HH:mm<br /><b>Achten Sie bitte darauf führende Nullen mit einzugeben! Geben Sie bitte keine Leerzeichen ein.</b>",
                    'required' => true
                );
            }

        }

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
