<?php

class oAuth2Auth extends AbstractPage {

    public function __construct() {
        parent::__construct(['oAuth2', 'oAuth2'],true,false,true);
    }


    public function execute() {

        if(!self::isSSOConfigured()) {
            new errorPage("SSO nicht konfiguriert!");
        }


        session_start();

        if($_REQUEST['keeplogin'] > 0) {
            $_SESSION['keepLogin'] = 1;
        }

        $loginData = Office365Login::triggerLogin();

        if($loginData == null) new errorPage("Leider ist kein Login mit Office 365 möglich!");

        $asvID = $loginData['asvID'];
        $username = $loginData['username'];

        // Update UserASV ID
        $user = user::getByUsername($username);
        if($user != null) {
            DB::getDB()->query("UPDATE users SET userAsvID='" . DB::getDB()->escapeString($asvID) . "' WHERE userName='" . DB::getDB()->escapeString($username) . "'");
        }

        if($asvID == "" || $username == "") {
            new errorPage("Leider ist kein Login mit Office 365 möglich! (Keine Daten übermittelt.)");
        }

        if(oAuth2Auth::ssoTeacherActive()) {
            // Lehrer suchen
            $lehrer = lehrer::getByASVId($asvID);

            if($lehrer != null) {
                $user = $lehrer->getUser();

                if($user != null && $user->getUserID() > 0) {
                    $userID = $user->getUserID();
                    $this->doLogin($userID, $username);
                }

                else {

                    $user2SQL = DB::getDB()->query_first("SELECT * FROM users WHERE userAsvID='" . DB::getDB()->escapeString($asvID) . "'");

                    if($user2SQL['userID'] > 0) {
                        $user = user::getUserByID($user2SQL['userID']);

                        $lehrer->setUserID($user->getUserID());

                        $userID = $user->getUserID();
                        $this->doLogin($userID, $username);
                    }
                    else {
                        new errorPage("Für Ihren Lehrer ist leider noch kein Lehrerbenutzer verfügbar!");
                    }

                }


            }
        }

        if(oAuth2Auth::ssoSchuelerActive()) {
            // Lehrer suchen
            $schueler = schueler::getByAsvID($asvID);

            if($schueler != null) {
                $user = $schueler->getUser();
                if($user != null) {
                    $userID = $user->getUserID();
                    $this->doLogin($userID, $username);
                }
                else {

                    $user2SQL = DB::getDB()->query_first("SELECT * FROM users WHERE userAsvID='" . DB::getDB()->escapeString($asvID) . "'");

                    if($user2SQL['userID'] > 0) {
                        $user = user::getUserByID($user2SQL['userID']);

                        $schueler->setUserID($user->getUserID());

                        $userID = $user->getUserID();
                        $this->doLogin($userID, $username);
                    }
                    else {
                        new errorPage("Für Ihren Schülerdatensatz ist leider noch kein Lehrerbenutzer verfügbar!");
                    }
                }

            }
        }


        $user = user::getByASVID($asvID);
        if($user != null) {
            $this->doLogin($user->getUserID(), $username);
        }


        new errorPage("Leider ist kein Login mit Office 365 möglich, da kein Benutzerdatensatz zu Ihnen gefunden wurde! Ihre ID: <code>$employeeID</code>");


        $session = session::loginAndCreateSession($user->getUserID(), $_SESSION['keepLogin'] > 0);

        header("Location: index.php");
        exit(0);

       // Debugger::debugObject($_REQUEST,1);
    }

    private function doLogin($userID, $username) {

        DB::getDB()->query("UPDATE users SET userName='" . DB::getDB()->escapeString($username) . "' WHERE userID='" . $userID . "'");

        $session = session::loginAndCreateSession($userID, $_SESSION['keepLogin'] > 0);

        session_destroy();

        header("Location: index.php");
        exit(0);        // Wichtig, abbruch!
    }

    public static function hasSettings() {
        return true;
    }

    public static function getSiteDisplayName() {
        return 'Office 365 Single Sign On';
    }

    public static function siteIsAlwaysActive() {
        return true;
    }

    public static function getAdminGroup() {
        return 'Webportal_oauth2_Admin';
    }

    public static function need2Factor() {
        return false;
    }

    public static function hasAdmin() {
        return true;
    }

    public static function getAdminMenuIcon() {
        return 'fa fas fa-key';
    }

    public static function getAdminMenuGroup() {
        return "Benutzerverwaltung";
    }

    public static function getAdminMenuGroupIcon() {
        return "fa far fa-file-word";
    }
    public static function displayAdministration($selfURL) {

        if($_REQUEST['action'] == 'activateLehrerSingleSignOn') {
            DB::getSettings()->setValue("office365-single-sign-on-lehrer", true);
            header("Location: $selfURL");
            exit();
        }

        if($_REQUEST['action'] == 'deactivateLehrerSingleSignOn') {
            DB::getSettings()->setValue("office365-single-sign-on-lehrer", false);
            header("Location: $selfURL");
            exit();
        }

        if($_REQUEST['action'] == 'activateSchuelerSingleSignOn') {
            DB::getSettings()->setValue("office365-single-sign-on-schueler", true);
            header("Location: $selfURL");
            exit();
        }
        if($_REQUEST['action'] == 'deactivateSchuelerSingleSignOn') {
            DB::getSettings()->setValue("office365-single-sign-on-schueler", false);
            header("Location: $selfURL");
            exit();
        }

        $html = "";

        $userModeSchueler = DB::getGlobalSettings()->schuelerUserMode;
        $userModeLehrer = DB::getGlobalSettings()->lehrerUserMode;

        $singleSignOnStatusLehrer = DB::getSettings()->getBoolean("office365-single-sign-on-lehrer");
        $singleSignOnStatusSchueler = DB::getSettings()->getBoolean("office365-single-sign-on-schueler");

        $isConfigured = DB::getSettings()->getValue("office365-single-sign-on-app-id") != "" && DB::getSettings()->getValue("office365-single-sign-on-app-secret");

        $redirectURL = DB::getGlobalSettings()->urlToIndexPHP . "?page=oAuth2Auth";

        eval("\$html = \"" . DB::getTPL()->get("oauth2/admin/index") . "\";");

        return $html;
    }

    public static function ssoTeacherActive() {
        return DB::getSettings()->getValue("office365-single-sign-on-app-id") != "" && DB::getSettings()->getValue("office365-single-sign-on-app-secret") && DB::getSettings()->getBoolean("office365-single-sign-on-lehrer");
    }

    public static function ssoSchuelerActive() {
        return DB::getSettings()->getValue("office365-single-sign-on-app-id") != "" && DB::getSettings()->getValue("office365-single-sign-on-app-secret") && DB::getSettings()->getBoolean("office365-single-sign-on-schueler");
    }

    public static function isSSOConfigured() {
        return DB::getSettings()->getValue("office365-single-sign-on-app-id") != "" && DB::getSettings()->getValue("office365-single-sign-on-app-secret");
    }

    public static function getSettingsDescription() {
        return [
            [
                'name' => 'office365-single-sign-on-app-id',
                'titel' => 'AnwendungsID',
                'typ' => 'ZEILE'
            ],
            [
                'name' => 'office365-single-sign-on-app-secret',
                'titel' => 'Anwendungsgeheimnis (Secret)',
                'typ' => 'ZEILE'
            ]
        ];
    }

}


?>
