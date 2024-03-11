<?php


class extUsersAdminInitcodes extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fas fa-user-shield"></i> Benutzer - Initialpasswörter für Sus und LuL';
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

        $acl = $this->getAcl();
        //$user = DB::getSession()->getUser();

        if (!$this->canAdmin()) {
            new errorPage('Kein Zugriff');
        }


        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/initcodes/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/initcodes/dist/js/app.js'
            ],
            "data" => [
                "apiURL" => "rest.php/users",
                "acl" => $acl['rights']
            ]
        ]);

    }

    public function taskPrint($postData)
    {

        $userID = $postData['u'];
        if (!$userID) {
            $this->reloadWithoutParam('task');
        }

        $user = DB::getDB()->query_first("SELECT * FROM users LEFT JOIN schueler ON schuelerUserID=userID LEFT JOIN lehrer ON lehrerUserID=userID JOIN initialpasswords ON userID=initialPasswordUserID WHERE userID='" . intval($userID) . "'");
        if ($user['userID'] > 0 && $user['initialPassword'] != "") {

            $letter = new PrintLetterWithWindowA4("Zugangsdaten  " . $user['userName']);
            $letter->setBetreff("Zugangsdaten für das Online Portal " . DB::getGlobalSettings()->siteNamePlain);
            $letter->setDatum(DateFunctions::getTodayAsNaturalDate());


            if ($user['schuelerAsvID'] != "") {
                $text = DB::getSettings()->getValue("createusers-letternewschueler");

                $text = str_replace("{SCHUELERNAME}", $user['schuelerName'] . ", " . $user['schuelerRufname'], $text);
                $text = str_replace("{KLASSE}", $user['schuelerKlasse'], $text);
                $text = str_replace("{BENUTZERNAME}", $user['userName'], $text);
                $text = str_replace("{PASSWORT}", $user['initialPassword'], $text);


                $briefAdresse = $user['schuelerName'] . ", " . $user['schuelerRufname'] . "\r\nKlasse " . $user['schuelerKlasse'];

            } else {
                $text = DB::getSettings()->getValue("createusers-letternewlehrer");

                $text = str_replace("{LEHRERNAME}", $user['lehrerName'] . ", " . $user['lehrerRufname'], $text);
                $text = str_replace("{BENUTZERNAME}", $user['userName'], $text);
                $text = str_replace("{PASSWORT}", $user['initialPassword'], $text);


                $briefAdresse = $user['lehrerName'] . ", " . $user['lehrerRufname'];
            }

            DB::getDB()->query("UPDATE initialpasswords SET passwordPrinted=UNIX_TIMESTAMP() WHERE initialPasswordUserID={$user['userID']}");


            $letter->addLetter($briefAdresse, $text);

            $letter->send();

            exit(0);

        }
    }


}
