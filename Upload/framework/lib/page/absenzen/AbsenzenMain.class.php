<?php


/**
 * Version 2.0 der Absenzen als SPA
 */
class AbsenzenMain extends AbstractPage {

    public function __construct() {
        parent::__construct(array("Absenzenverwaltung", "Hauptansicht"));

        $this->checkLogin();

        if(!DB::getSession()->isAdmin()) $this->checkAccessWithGroup("Webportal_Absenzen_Sekretariat");

        $this->stundenplan = stundenplandata::getCurrentStundenplan();
    }

    public function execute() {
        if($_REQUEST['ajax'] > 0) {

        }
        else {
            $this->showIndex();
        }
    }

    private function showIndex() {
        eval("echo(\"" . DB::getTPL()->get("absenzenv2/sekretariat/index") . "\");");
    }

    public static function hasSettings() {
        return true;
    }

    /**
     * @return array
     */
    public static function getSettingsDescription() {
        return array(
            /*array(
              'name' => "absenzen-attestnachdreitagen",
              'typ' => BOOLEAN,
              'titel' => "Attest nach 3 Tagen fordern?",
              'text' => "Soll ein Attest nach drei Tagen Abwesenheit gefordert werden?"
            ),*/
            array(
                'name' => "absenzen-meldungaktivieren",
                'typ' => BOOLEAN,
                'titel' => "Meldung im Sekretariat aktivieren?",
                'text' => "Dadurch wird es möglich einen Haken zu setzen, der anzeigt, ob der Klassentagebuchführer schon im Sekretariat gemeldet hat."
            ),
            [
                'name' => "absenzen-sekretariat-fotos-anzeigen",
                'typ' => BOOLEAN,
                'titel' => "Fotos der Schüler in der Sekretariatansicht aktivieren?",
                'text' => ""
            ],
            array(
                'name' => "absenzen-merkeraktivieren",
                'typ' => BOOLEAN,
                'titel' => "Merker im Sekretariat aktivieren?",
                'text' => "Mit dieser Option ist es möglich kleine Erinnerungen zu einzelnen Schülern zu setzen (für einen einzelnen Tag. z.B. 2. Pause Sekretariat)"
            ),
            array(
                'name' => "absenzen-schriftlicheentschuldigung-sek",
                'typ' => BOOLEAN,
                'titel' => "Anzeige Schriftlifliche Entschuldigung im Sekretariat aktivieren?",
                'text' => "Sollen die Haken, ob eine schriftliche Entschuldigung vorliegt angezeigt werden?"
            ),
            array(
                'name' => "absenzen-generelleattestpflicht",
                'typ' => BOOLEAN,
                'titel' => "Generelle Attestpflicht aktivieren?",
                'text' => ""
            ),
            array(
                'name' => "absenzen-fristabgabe-schriftliche-entschuldigung",
                'typ' => 'NUMMER',
                'titel' => "Frist zur Abgabe von schriftlichen Entschuldigungen",
                'text' => "Wenn hier eine Zahl größer Null angegeben wird, können schriftliche Entschuldigungen nur innerhalb dieser Tagesfrist als abgegeben markiert werden."
            ) ,
            array(
                'name' => "absenzen-keine-schriftlichen-entschuldigungen",
                'typ' => 'BOOLEAN',
                'titel' => "Keine schriftlichen Entschuldigungen fordern?",
                'text' => "Wenn diese Option aktiv ist, dann werden keine schriftlichen Entschuldingen mehr gefordert."
            ),
            array(
                'name' => "absenzen-keine-schriftlichen-entschuldigungen-befreiungen",
                'typ' => 'BOOLEAN',
                'titel' => "Keine schriftlichen Entschuldigungen bei Befreiungen fordern?",
                'text' => "Wenn diese Option aktiv ist, dann werden keine schriftlichen Entschuldingen bei Befreiungen mehr gefordert. (Kein Rücklauf des Befreiungszettels mehr)"
            ),
            array(
                'name' => "absenzen-keine-schriftlichen-entschuldigungen-nur-portal",
                'typ' => 'BOOLEAN',
                'titel' => "Keine schriftlichen Entschuldigungen fordern? (Auf Portalentschuldigungen einschränken)",
                'text' => "Wenn diese Option aktiv ist, dann werden nur bei Krankmeldungen über das Portal keine schriftlichen Entschuldigungen mehr gefordert."
            ),
            [
                'name' => "krankmeldung-hinweis-lnw",
                'typ' => 'BOOLEAN',
                'titel' => "Attestpflicht bei angekkündigten LNW?",
                'text' => "Bei aktivierter Option erhalten die Eltern einen Hinweis, dass ein Attest benötigt wird, wenn da an dem Tag ein angekündigter Leistungsnachweis im Klassenkalender eingetragen ist."
            ],
            [
                'name' => "absenzen-no-duplicate",
                'typ' => 'BOOLEAN',
                'titel' => "In der Absenzenstatistik nur einen Tag pro Absenzentag zählen?",
                'text' => "Ist diese Option aktiv, dann wird nur ein Absenzentag pro Tag gezählt. Sind an einem Tag mehrere Absenzen eingetragen, wird nur ein Tag gezählt."
            ],
            [
                'name' => "absenzen-count-absenz-with-minimumhours",
                'typ' => 'NUMMER',
                'titel' => "Ab wie vielen Stunden Absenz soll ein Tag als absent zählen?",
                'text' => "Bei 0 oder kein Eintrag: Ab 1 Stunde Absenz; bei z.B. 5 werden Absenzen mit nur 1 bis 4 Stunden als nicht absenz gezählt."
            ]
        );
    }

    public static function getSchriftlicheEntschuldigungPolicy() {
        $text = "Für diese Absenzen müssen folgende Dokumente abgegeben werden:<br />";

        $text .= "<li><b>Für genehmigte Beurlaubungen:</b> Keine</li>";

        if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen-befreiungen')) {
            $text .= "<li><b>Für Befreiungen:</b> Keine</li>";
        }
        else {
            if(DB::getSettings()->getBoolean('absenzen-generelleattestpflicht')) {
                $text .= "<li><b>Für Befreiungen:</b> Ärztliches Attest und Rücklauf des Befreiungszettels</li>";
            }
            else {
                $text .= "<li><b>Für Befreiungen:</b> Unterschriebener Rücklauf der ausgestellten Befreiung</li>";
            }
        }

        if(DB::getSettings()->getBoolean('absenzen-generelleattestpflicht')) {
            $text .= "<li><b>Für alle Krankmeldungen:</b> Ärztliches Attest</li>";
        }
        else {
            if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen')) {
                if(DB::getSettings()->getBoolean('absenzen-keine-schriftlichen-entschuldigungen-nur-portal')) {
                    $text .= "<li><b>Für Krankmeldungen über das Portal:</b> Keine</li>";
                    $text .= "<li><b>Für telefonische Krankmeldungen:</b> Schriftliche Entschuldigung</li>";
                }
                else {
                    $text .= "<li><b>Für alle Krankmeldungen:</b> Keine</li>";
                }

            }
            else {
                $text .= "<li><b>Für alle Krankmeldungen:</b> Schriftliche Entschuldigung</li>";
            }


            if(DB::getSettings()->getBoolean('krankmeldung-hinweis-lnw')) $text .= "<li><b>Für Krankmeldungen, an denen ein Leistungsnachweis stattfand:</b> Ärztliches Attest</li>";
        }


        if(DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') > 0) {
            $text .= "<li><b>Endgültige Abgabefrist für Entschuldigungen und Atteste:</b> " . DB::getSettings()->getValue('absenzen-fristabgabe-schriftliche-entschuldigung') . " Werktage</li>";
        }

        $text .= "</ul>";
        return $text;
    }

    public static function getSiteDisplayName() {
        return 'Sekretariatsansicht 2 (Absenzen)';
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups() {
        return array();
    }

    public static function siteIsAlwaysActive() {
        return false;
    }


    public static function getAdminGroup() {
        return 'Webportal_Absenzen_Admin';
    }

    public static function dependsPage() {
        return ['absenzenstatistik','absenzenberichte'];
    }

    public static function hasAdmin() {
        return true;
    }

    public static function displayAdministration($selfURL) {
        $html = "";

        $usergroup = usergroup::getGroupByName('Webportal_Absenzen_Sekretariat');

        if($_REQUEST['action'] == 'addUser') {
            $usergroup->addUser($_POST['userID']);
            header("Location: $selfURL&userAdded=1");
            exit(0);

        }

        if($_REQUEST['action'] == 'removeUser') {
            $usergroup->removeUser($_REQUEST['userID']);
            header("Location: $selfURL&userDeleted=1");
            exit(0);
        }

        // Aktuelle Benutzer suchen, die Zugriff haben

        $currentUserBlock = administrationmodule::getUserListWithAddFunction($selfURL, "sek", "addUser", "removeUser", "Benutzer mit Zugriff auf die Sekretariatsansicht", "Diese Benutzer haben Zugriff auf die komplette Sekretariatsansicht. (Vollzugriff)", 'Webportal_Absenzen_Sekretariat');


        eval("\$html = \"" . DB::getTPL()->get("absenzen/admin/index") . "\";");

        return $html;
    }

    public static function getAdminMenuGroup() {
        return 'Absenzenverwaltung';
    }

    public static function getAdminMenuGroupIcon() {
        return 'fa fas fa-procedures';
    }

    public static function userHasAccess($user) {
        if($user->isAdmin()) return true;

        return $user->isMember("Webportal_Absenzen_Sekretariat");
    }

    public static function getActionSchuljahreswechsel() {
        return 'Absenzen, Verspätungen, Attestpflichten, Befreiungen, Beurlaubungen, Krankmeldungen und Sanizimmer aus dem alten Schuljahr löschen';
    }

    public static function doSchuljahreswechsel($sqlDateFirstSchoolDay) {

        $time = DateFunctions::getUnixTimeFromMySQLDate($sqlDateFirstSchoolDay);

        DB::getDB()->query("DELETE FROM absenzen_absenzen WHERE absenzDatum < '$sqlDateFirstSchoolDay'");
        DB::getDB()->query("DELETE FROM absenzen_attestpflicht");
        DB::getDB()->query("DELETE FROM absenzen_krankmeldungen WHERE krankmeldungDate < '$sqlDateFirstSchoolDay'");
        DB::getDB()->query("DELETE FROM absenzen_verspaetungen WHERE verspaetungDate < '$sqlDateFirstSchoolDay'");
        DB::getDB()->query("DELETE FROM absenzen_sanizimmer WHERE sanizimmerTimeStart < '$time'");


    }

    public static function isBeta() {
        return true;
    }
}


?>
