<?php


class userprofilesettings extends AbstractPage
{


    public function __construct()
    {
        parent::__construct(array("Benutzerprofil", "Seiteneinstellungen"));
        $this->checkLogin();
    }

    public function execute()
    {

        if (!DB::getSession()->getUser()->userCanChangePassword()) {
            new errorPage("Kein Zugriff auf das Profil.");
            exit();
        }

        if ($_REQUEST['save'] > 0) {
            if (!DB::getSettings()->getBoolean('global-skin-force-color')) {
                if (!in_array($_POST['skinColor'], array(
                        "blue",
                        "black",
                        "purple",
                        "yellow",
                        "red",
                        "green"
                    )
                )
                ) {
                    new error("Malformed request. Color not in range!");
                    exit(0);
                }
            }

            if (!in_array($_POST['startPage'], array(
                    "aufeinenblick",
                    "vplan",
                    "stundenplan",
                    "dashboard"
                )
            )
            ) {
                new error("Malformed request. Page not in range!");
                exit(0);
            }


            if ($_REQUEST['autoLogout'] == '') {
                $autoLogout = "null";
            } else {
                $autoLogout = (int)trim($_REQUEST['autoLogout']);
            }

            if (DB::$mySettings['userID'] > 0) {
                // UPDATE

                DB::getDB()->query("UPDATE user_settings SET
					
					skinColor='" . addslashes($_REQUEST['skinColor']) . "',
					startPage='" . addslashes($_REQUEST['startPage']) . "',
					autoLogout= " . $autoLogout . "
						
					WHERE userID='" . DB::getUserID() . "'
				");

            } else {
                DB::getDB()->query("INSERT INTO user_settings (userID, skinColor, startPage, autoLogout) values(
						'" . DB::getUserID() . "',
						'" . addslashes($_REQUEST['skinColor']) . "',
						'" . addslashes($_REQUEST['startPage']) . "',
						" . $autoLogout . ")
				");
            }

            header("Location: index.php?page=userprofilesettings&saved=2");
            exit(0);
        }

        $selectedColor = array(
            "blue" => "",
            "black" => "",
            "purple" => "",
            "yellow" => "",
            "red" => "",
            "green" => ""
        );

        $selectedPage = array(
            "index" => "",
            "vplan" => "",
            "stundenplan" => "",
            "dashboard" => ""
        );

        $selectedColor[DB::$mySettings['skinColor']] = " selected=\"selected\"";
        $selectedPage[DB::$mySettings['startPage']] = " selected=\"selected\"";

        $selectedAutoLogout = DB::$mySettings['autoLogout'];
        $savedMessage = "";

        if ($_GET['saved'] > 0) {
            $savedMessage = "<div class=\"callout callout-success\"><p>Die Einstellungen wurden erfolgreich gespeichert!</p></div>";
        }

        eval("echo(\"" . DB::getTPL()->get("userprofile/settings/index") . "\");");
    }

    public static function hasSettings()
    {
        return false;
    }

    /**
     * Stellt eine Beschreibung der Einstellungen bereit, die für das Modul nötig sind.
     * @return array(String, String)
     * array(
     *       array(
     *        'name' => "Name der Einstellung (z.B. formblatt-isActive)",
     *        'typ' => ZEILE | TEXT | NUMMER | BOOLEAN,
     *      'titel' => "Titel der Beschreibung",
     *      'text' => "Text der Beschreibung"
     *     )
     *     ,
     *     .
     *     .
     *     .
     *  )
     */
    public static function getSettingsDescription()
    {
        return array();
    }


    public static function getSiteDisplayName()
    {
        return '';
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups()
    {
        return array();

    }

    public static function siteIsAlwaysActive()
    {
        return true;
    }
}


?>