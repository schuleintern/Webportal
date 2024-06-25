<?php

/**
 * Abstrakte Seite auf der alle andere Seiten aufbauen.
 * @author Christian Spitschka
 */

abstract class AbstractPage
{


    private $title; // only if $ignoreSession == true

    public $header = "";

    protected $isAPI = false;
    protected $apiIsSessionOK = false;
    protected $sitename = "index";
    protected $messageItem = "";
    protected $taskItem = "";
    protected $loginStatus = "";
    protected $userImage = "";
    protected $eltermailPopup = "";
    protected $helpTopic = "";
    protected static $isBeta = false;

    private static $activePages = array();
    private $acl = false;
    private $request = false;
    public $extension = false;
    private $isAnyAdmin = false;
    public $isMobile = false;

    static $adminGroupName = NULL;
    static $aclGroupName = NULL;


    /**
     *
     * @param pageline Array
     * @param ignoreSession Boolean
     * @param isAdmin Boolean
     * @param isNotenverwaltung Boolean
     * @param request Array ( _GET Parameter)
     * @param extension Array
     */
    public function __construct(
        $pageline,
        $ignoreSession = false,
        $isAdmin = false,
        $isNotenverwaltung = false,
        $request = [],
        $extension = []
    ) {


        //header("X-Frame-Options: deny"); // aus damit print im browser geht - notenverwaltung

        $this->request = $request;
        $this->extension = $extension;

        $this->sitename = addslashes(trim($request['page']));

        if ($this->sitename != "" && in_array($this->sitename, requesthandler::getAllowedActions()) && !self::isActive($this->sitename)) {
            // TODO: Sinnvolle Fehlermeldung
            die("Die angegebene Seite ist leider nicht aktiviert");
        }

        $this->isMobile = $this->isMobileDevice();

        // Load Extension JSON and set Defaults
        if ($this->extension) {

            $path = str_replace(DS . 'admin/', '', PATH_EXTENSION) . DS;


            $this->extension['json'] = self::getExtensionJSON($path . 'extension.json');
            if (isset($this->extension['json'])) {

                // Admin Group
                if ($this->extension['json']['adminGroupName']) {
                    self::setAdminGroup($this->extension['json']['adminGroupName']);
                }

                // ACL Group
                if ($this->extension['json']['aclGroupName']) {
                    self::setAclGroup($this->extension['json']['aclGroupName']);
                }
            }
        }


        // Seite ohne Session aufrufen?
        // TODO: @Spitschka es gibt kein else ???
        if (!$ignoreSession) {

            $this->title = $title;
            $this->sitename = $sitename;

            if (isset($_COOKIE['schuleinternsession'])) {
                DB::initSession($_COOKIE['schuleinternsession']);
                if (!DB::isLoggedIn()) {
                    if (isset($_COOKIE['schuleinternsession'])) {
                        setcookie("schuleinternsession", null);
                    }
                    $message = "<div class=\"callout callout-danger\"><p><strong>Sie waren leider zu lange inaktiv. Sie k&ouml;nnen dauerhaft angemeldet bleiben, wenn Sie den Haken bei \"Anmeldung speichern\" setzen. </strong></p></div>";
                    eval("echo(\"" . DB::getTPL()->get("login/index") . "\");");
                    exit;
                } else {
                    DB::getSession()->update();
                }
            }


            // 2 Faktor

            $needTwoFactor = false;
            if (
                DB::isLoggedIn()
                && TwoFactor::is2FAActive()
                && TwoFactor::enforcedForUser(DB::getSession()->getUser())
            ) {
                $needTwoFactor = true;
            }

            if ($needTwoFactor || ($this->need2Factor() && TwoFactor::is2FAActive())) {
                $pagesWithoutTwoFactor = [
                    'login',
                    'logout',
                    'TwoFactor'
                ];
                $currentPage = $_REQUEST['page'];
                if (
                    !DB::getSession()->is2FactorActive()
                    && !in_array($currentPage, $pagesWithoutTwoFactor)
                ) {

                    $whitelist = TwoFactor::getWhitelist();

                    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                    if ((int)$ip < 1) {
                        // Localhost
                        $ip = getHostByName(getHostName());
                    }

                    $found = false;
                    if ($whitelist && $ip) {

                        foreach ($whitelist as $wip) {
                            if ($wip == $ip) {
                                $found = true;
                            }
                        }
                    }
                    if ($found != true) {
                        header("Location: index.php?page=TwoFactor&action=initSession&gotoPage=" . urlencode($currentPage));
                        exit(0);
                    }
                }
            }



            // Wartungsmodus

            $infoWartungsmodus = "";
            if (
                DB::getSettings()->getValue("general-wartungsmodus")
                && $_REQUEST['page'] != "login"
                && $_REQUEST['page'] != "logout"
                && $_REQUEST['page'] != "impressum"
            ) {
                if (!DB::isLoggedIn() || !DB::getSession()->isAdmin()) {
                    $text = nl2br(DB::getSettings()->getValue("general-wartungsmodus-text"));
                    eval("echo(\"" . DB::getTPL()->get("wartungsmodus/index") . "\");");
                    exit();
                } else {
                    $infoWartungsmodus .= "<div class=\"callout callout-danger\"><i class=\"fa fa-cogs\"></i> Die Seite befindet sich im Wartungsmodus! Bitte unter den <a href=\"index.php?page=administrationmodule&module=index\">Einstellungen</a> wieder deaktivieren!</div>";
                }
            }

            // Internmodus
            if (
                DB::getSettings()->getValue("general-internmodus")
                && $_REQUEST['page'] != "login"
                && $_REQUEST['page'] != "logout"
                && $_REQUEST['page'] != "impressum"
            ) {

                if (DB::isLoggedIn() && (DB::getSession()->isAdmin() || DB::getSession()->isTeacher() || DB::getSession()->isNone())) {
                    if (DB::isLoggedIn()) {
                        $infoWartungsmodus .= "<div class=\"callout callout-danger\"><i class=\"fa fa-cogs\"></i> Die Seite befindet sich im Internmodus!</div>";
                    }
                } else {
                    eval("echo(\"" . DB::getTPL()->get("internmodus/index") . "\");");
                    exit();
                }
            }

            // Datenschutz

            if (
                DB::isLoggedIn()
                && datenschutz::needFreigabe(DB::getSession()->getUser())
                && !datenschutz::isFreigegeben(DB::getSession()->getUser())
                && $_REQUEST['page'] != "login"
                && $_REQUEST['page'] != "logout"
                && $_REQUEST['page'] != "impressum"
                && $_REQUEST['page'] != "datenschutz"
            ) {
                header("Location: index.php?page=datenschutz&confirmPopUp=1");
                exit(0);
            }


            // Check Adminrights

            if (
                DB::isLoggedIn()
                && (DB::getSession()->isAdmin() || DB::getSession()->isMember($this->getAdminGroup()))
            ) {
                $this->isAnyAdmin = true;
            } else {
                $this->isAnyAdmin = false;
            }

            if ($this->request['admin'] && $this->isAnyAdmin == false) {
                new errorPage('Kein Zugriff');
            }

            // Login Status

            if (DB::isLoggedIn()) {
                $displayName = DB::getSession()->getData('userFirstName') . " " . DB::getSession()->getData('userLastName');
                if (DB::isLoggedIn() && DB::getSession()->isTeacher()) {
                    $mainGroup = "Lehrer";
                } else if (DB::isLoggedIn() && DB::getSession()->isPupil()) {
                    $mainGroup = "Schüler (Klasse " . DB::getSession()->getPupilObject()->getGrade() . ")";
                } else if (DB::isLoggedIn() && DB::getSession()->isEltern()) {
                    $mainGroup = "Eltern";
                } else {
                    $mainGroup = "Sonstiger Benutzer";
                }
            } else {
                $displayName = "Nicht angemeldet";
                $mainGroup = "";
            }


            // Header and Menu

            $this->prepareHeaderBar($mainGroup);

            $menu = new menu($isAdmin, $isNotenverwaltung);
            $menuHTML = $menu->getHTML();


            //$sitemapline = "";
            $siteTitle = '';
            if (is_array($pageline)) {
                /*for ($i = 0; $i < sizeof($pageline); $i++) {
                    $sitemapline .= '<li class="active">' . $pageline [$i] . '</li>';
                }
                */
                $siteTitle = $pageline[sizeof($pageline) - 1];
            }




            // Seitennamen durch Menüpunkt-DB-Title ersetzen
            $params = [];
            foreach ($this->request as $request_key => $request) {
                if ($request_key != 'page') {
                    $params[$request_key] = $request;
                }
            }
            $menuItem = MenueItems::getFromPageAndParams($this->request['page'], json_encode($params));
            $icon = 'fa fa-file';
            if ($menuItem['title']) {
                if ($menuItem['icon']) {
                    $icon = $menuItem['icon'];
                }
                $siteTitle = '<i class="' . $icon . '"></i> ' . $menuItem['title'];
            }



            // Page Skin Color

            $skinColor = DB::$mySettings['skinColor'];
            if (DB::getSettings()->getValue('global-skin-default-color') != '') {
                if (DB::getSettings()->getBoolean('global-skin-force-color')) {
                    $skinColor = DB::getSettings()->getValue('global-skin-default-color');
                } else if ($skinColor == '') {
                    $skinColor = DB::getSettings()->getValue('global-skin-default-color');
                }
            }
            // Default Color für alle: Grün
            if ($skinColor == "") $skinColor = "green";


            // Laufzettel Info

            if (
                $this->isActive("laufzettel")
                && DB::isLoggedIn()
                && DB::getSession()->isTeacher()
            ) {
                $zuBestaetigen = DB::getDB()->query_first("SELECT COUNT(laufzettelID) AS zubestaetigen FROM laufzettel WHERE laufzettelDatum >= CURDATE() AND laufzettelID IN (SELECT laufzettelID FROM laufzettel_stunden WHERE laufzettelLehrer LIKE '" . DB::getSession()->getTeacherObject()->getKuerzel() . "' AND laufzettelZustimmung=0)");

                if ($zuBestaetigen[0] > 0) {
                    if ($zuBestaetigen[0] == 1) {
                        $nummer = "Ein";
                        $verb = "wartet";
                    } else {
                        $nummer = $zuBestaetigen[0];
                        $verb = "warten";
                    }
                    $infoLaufzettel = "<a href=\"index.php?page=laufzettel&mode=myLaufzettel\" class=\"btn btn-xs btn-info\"><i class=\"fa fa-check\"></i> " . $nummer . " Laufzettel $verb auf Ihre Zustimmung</a>";
                } else
                    $infoLaufzettel = "";
            } else {
                $infoLaufzettel = "";
            }

            // Message Info

            $infoMessages = "";
            $countMessage = 0;
            if (DB::isLoggedIn() && Message::userHasUnreadMessages()) {
                $countMessage = Message::getUnreadMessageNumber(DB::getSession()->getUser(), "POSTEINGANG", 0);
                if (DB::getSettings()->getBoolean('messages-banner-new-messages')) {
                    $infoMessages = "<a href=\"index.php?page=MessageInbox&folder=POSTEINGANG\" class=\"btn btn-danger btn-xs\"><i class=\"fa fa-envelope fa-spin\"></i> $countMessage ungelesene Nachricht" . (($countMessage > 1) ? "en" : "") . "</a>";
                } else {
                    $infoMessages = "";
                }
            }

            // Fremdsession

            if (DB::isLoggedIn()) {
                $fremdlogin = Fremdlogin::getMyFremdlogin();
                if ($fremdlogin != null) {
                    if ($fremdlogin->getAdminUser() != null) {
                        $fremdloginUser = $fremdlogin->getAdminUser()->getDisplayNameWithFunction();
                    } else {
                        $fremdloginUser = "n/a";
                    }
                    if ($fremdlogin->getAdminUser() != null) {
                        $fremdloginUserID = $fremdlogin->getAdminUser()->getUserID();
                    } else {
                        $fremdloginUserID = "n/a";
                    }
                    $fremdloginNachricht = $fremdlogin->getMessage();
                    $fremdloginTime = functions::makeDateFromTimestamp($fremdlogin->getTime());
                    $fremdloginID = $fremdlogin->getID();
                }
                if (DB::getSession()->isDebugSession()) {
                    $debugSession = true;
                } else {
                    $debugSession = false;
                }
            }


            // Is Admin ?
            if (
                DB::isLoggedIn()
                && $this->hasAdmin()
                && (DB::getSession()->isAdmin() || DB::getSession()->isMember($this->getAdminGroup()))
            ) {
                $isAdmin = true;
            } else {
                $isAdmin = false;
            }


            // Widgets
            $HTML_widgets = [];
            if (DB::isLoggedIn()) {
                $result = DB::getDB()->query('SELECT * FROM `widgets` WHERE position = "header" ');
                while ($row = DB::getDB()->fetch_array($result, true)) {

                    $access = false;
                    $do = true;

                    $ext = explode('.', (string)$row['uniqid']);
                    if ($ext[0] && $ext[1] && is_dir(PATH_EXTENSIONS . $ext[0])) {
                        $json = FILE::getExtensionJSON(PATH_EXTENSIONS . $ext[0] . DS . 'extension.json');

                        // Check Access
                        if ($row['access']) {
                            $do = false;
                            $access = json_decode($row['access']);

                            if ($access) {
                                $do = false;
                                if ($access->other) {
                                    if (DB::getSession()->isNone()) {
                                        $do = true;
                                    }
                                }
                                if ($access->parents) {
                                    if (DB::getSession()->isEltern()) {
                                        $do = true;
                                    }
                                }
                                if ($access->pupil) {
                                    if (DB::getSession()->isPupil()) {
                                        $do = true;
                                    }
                                }
                                if ($access->teacher) {
                                    if (DB::getSession()->isTeacher()) {
                                        $do = true;
                                    }
                                }
                                if ($access->adminGroup) {
                                    if ($json && $json['adminGroupName']) {
                                        if (in_array($json['adminGroupName'], DB::getSession()->getGroupNames())) {
                                            $do = true;
                                        }
                                    }
                                }
                                if ($access->admin) {
                                    if (DB::getSession()->isAdmin()) {
                                        $do = true;
                                    }
                                }
                            }
                        }

                        if ($do == true && $json['widgets']) {
                            foreach ($json['widgets'] as $widget) {

                                if ($widget->uniqid == $row['uniqid']) {
                                    $ext2 = explode('.', (string)$widget->uniqid);
                                    $widgetPath = PATH_EXTENSIONS . $ext2[0] . DS . 'widgets' . DS . $ext2[1] . DS . 'widget.php';
                                    if (file_exists($widgetPath)) {
                                        if ($widget->class) {
                                            if (include_once($widgetPath)) {
                                                $widgetClass = new $widget->class([
                                                    "path" => PATH_EXTENSIONS . $ext2[0]
                                                ]);
                                                if (method_exists($widgetClass, 'globals')) {
                                                    $HTML_widgets[]  = '<script>'.$widgetClass->globals().'</script>';
                                                }
                                                if ($widgetClass && method_exists($widgetClass, 'render')) {
                                                    $HTML_widgets[] = '<li class="dropdown messages-menu">' . $widgetClass->render() . '</li>';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $HTML_widgets = implode('', $HTML_widgets);


            // AutoLogout
            $selectedAutoLogout = DB::$mySettings['autoLogout'];
            if ($selectedAutoLogout == null) {
                $selectedAutoLogout = 30;
            }
            $selectedAutoLogout = $selectedAutoLogout * 60;


            // Push-Nachrichten
            $pushActive = DB::getSettings()->getBoolean('global-push-active');
            if ($pushActive) {
                $pushPublicKey = DB::getSettings()->getValue('global-push-publicKey');
                $pushPrivateKey = DB::getSettings()->getValue('global-push-privateKey');
                if ($pushPublicKey == '' || $pushPrivateKey == '') {
                    $pushActive = false;
                }
            }

            if (DB::getSession()) {
                $userID = DB::getSession()->getUserID();

                // Missing E-Mail
                if ( DB::getSession()->getData('userEMail') == '' ) {
                    $missingEMail = true;
                }
                
            }
            $header_extension = false;
            if ($this->extension) {
                $header_extension = true;
            }

            // Render Header
            eval("\$this->header =  \"" . DB::getTPL()->get('header/header') . "\";");
        }
    }


    public function isMobileDevice()
    {
        return preg_match(
            "/(android|avantgo|blackberry|bolt|boost|cricket|docomo
    |fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
            $_SERVER["HTTP_USER_AGENT"]
        );
    }


    /**
     * Render Extension Template
     *
     * @param page String
     * @param scripts Array
     * @param data Array
     */
    public function render($arg)
    {

        // set default view/tmpl
        if (!$arg['tmpl'] && !$arg['tmplHTML']) {
            $arg['tmpl'] = 'default';
        }
        // Available from tpl files
        $path = PATH_EXTENSION . 'tmpl' . DS;

        if ($arg['tmplHTML'] || file_exists($path . $arg['tmpl'] . '.tmpl.php')) {
            echo $this->header;

            // check if global menu
            if (!isset($arg['submenu'])) {
                if ($this->extension['json']->submenu) {
                    $sub = $this->extension['json']->submenu;
                } else if ($this->extension['json']['submenu']) {
                    $sub = $this->extension['json']['submenu'];
                };
                if (isset($this->extension['json']) && isset($sub)) {
                    $arg['submenu'] = (array)$sub;
                }
            }
            // render submenu and dropdown
            if ($arg['submenu'] || $arg['dropdown']) {
                $defaultSubmenu = $arg['submenu'];
                $called = get_called_class();
                $calledClass = new $called;
                if (method_exists($calledClass,'submenu')) {
                    $newSubmenu = $calledClass->submenu();
                    if ($newSubmenu['data']) {
                        if ($newSubmenu['overright']) {
                            $foo = [];
                            foreach ($defaultSubmenu as $default) {
                                if ($default->admin) {
                                    $foo[] = $default;
                                }
                            }
                            $defaultSubmenu = $foo;
                        }
                        $defaultSubmenu = array_merge($newSubmenu['data'],$defaultSubmenu);
                    }
                }
                echo $this->makeSubmenu($defaultSubmenu, $arg['dropdown']);

            }

            if ($arg['tmplHTML']) {
                echo $arg['tmplHTML'];
            } else {

                // Check for tmpl Overrights
                if (
                    $this->request['page']
                    && file_exists(PATH_TMPL_OVERRIGHTS . 'extensions' . DS . $this->request['page'] . DS . $arg['tmpl'] . '.tmpl.php')
                ) {
                    include_once(PATH_TMPL_OVERRIGHTS . 'extensions' . DS . $this->request['page'] . DS . $arg['tmpl'] . '.tmpl.php');
                } else {
                    if ($arg['vars'] && count($arg['vars']) >= 1) {
                        foreach ($arg['vars'] as $key => $var) {
                            // TODO: better way?
                            if ($key && $var) {
                                switch (gettype($var)) {
                                    case "integer":
                                        eval("\$" . $key . " = " . $var . "; ");
                                        break;
                                    default:
                                    case "string":
                                        eval("\$" . $key . " = '" . $var . "'; ");
                                        break;
                                    case "array":
                                        eval("\$" . $key . " = '" . json_encode($var) . "'; ");
                                        break;
                                    case "boolean":
                                        eval("\$" . $key . " = " . $var . "; ");
                                        break;
                                }
                            }
                        }
                    }
                    include_once($path . $arg['tmpl'] . '.tmpl.php');
                }
            }

            // render Data for JavaScript
            if ($arg['data']) {
                echo $this->getScriptData($arg['data']);
            }

            // import JavaScript Files
            if ($arg['scripts']) {
                echo FILE::getScripts($arg['scripts']);
            }
            // import CSS Files
            if ($arg['style']) {
                echo $this->getStyle($arg['tmpl'], $arg['style']);
            }
        } else {
            new errorPage('Missing Template File');
            exit;
        }
    }


    /**
     * get Extension JSON
     *
     */
    public static function getExtensionJSON($path = false)
    {

        if (!$path) {
            if (!PATH_EXTENSION || PATH_EXTENSION == 'PATH_EXTENSION') {
                return false;
            }
            $path = PATH_EXTENSION . DS . 'extension.json';
        }
        if (file_exists($path)) {
            $file = file_get_contents($path);
            $json = (array)json_decode($file);
            if ($json) {
                return $json;
            }
        }
        return false;
    }


    /**
     * Load PHP Variables to JavaScript
     *
     * @param data Array
     */
    static public function getScriptData($data, $varname = 'globals')
    {
        if ($data) {
            return '<script>var ' . $varname . ' = ' . json_encode($data) . ';</script>';
        }
        return '<script>var ' . $varname . ' = {};</script>';
    }


    /**
     * Get Css Scripts Files
     *
     * @param page String
     * @param scripts Array
     */
    private function getStyle($view, $styles)
    {

        if (!$styles || count($styles) <= 0) {
            return false;
        }
        $html = '';
        foreach ($styles as $style) {
            $style = trim($style);
            if (file_exists($style)) {
                $file = file_get_contents($style);
                if ($file) {
                    $html .= '<style>' . $file . '</style>';
                }
            }
        }
        return $html;
    }

    /**
     * Get JavaScript Scripts Files
     *
     * @param page String
     * @param scripts Array
     */
    /*
    MOVED: to FILE.class.php

    public function getScript($view, $scripts)
    {

        if (!$scripts || count($scripts) <= 0) {
            return false;
        }
        $html = '';
        foreach ($scripts as $script) {
            $script = trim($script);
            if (file_exists($script)) {
                $file = file_get_contents($script);
                if ($file) {
                    $html .= '<script>' . $file . '</script>';
                }
            }
        }
        return $html;
    }
    */


    /**
     * render login Status for Headerbar
     */
    private function prepareHeaderBar($mainGroup)
    {

        if ($mainGroup && DB::isLoggedIn()) {
            $displayName = DB::getSession()->getData('userFirstName') . " " . DB::getSession()->getData('userLastName');
            $image = DB::getDB()->query_first("SELECT uploadID FROM image_uploads WHERE uploadUserName LIKE '" . DB::getSession()->getData("userName") . "'");
            if ($image['uploadID'] > 0) {
                $this->userImage = "index.php?page=userprofileuserimage&getImage=profile";
            } else {
                $this->userImage = "cssjs/images/userimages/default.png";
            }
            eval("\$this->loginStatus = \"" . DB::getTPL()->get("header/loginStatusLoggedIn") . "\";");
        } else {
            $this->displayName = "Nicht angemeldet";
            eval("\$this->loginStatus = \"" . DB::getTPL()->get("header/loginStatusNotLoggedIn") . "\";");
        }
    }


    /**
     * Hilfsfunktion für die Seiten, um zu überprüfen, ob der aktuelle Benutzerzugriff hat, wenn der die Gruppe $groupName braucht
     * @param unknown $groupName Benötigte Gruppe
     */
    protected function checkAccessWithGroup($groupName)
    {

        $hasAccess = false;
        if ($groupName && DB::isLoggedIn()) {
            if (in_array($groupName, DB::getSession()->getGroupNames())) {
                $hasAccess = true;
            }
        }
        if (!$hasAccess) {
            header("Location: index.php");
        }
    }

    protected function canRead()
    {
        if ($this->extension['json']['adminGroupName'] && DB::getSession()->isAdminOrGroupAdmin($this->extension['json']['adminGroupName']) === true) {
            return true;
        }
        if ((int)$this->acl['rights']['read'] === 1) {
            return true;
        }
        return false;
    }

    protected function canWrite()
    {
        if ($this->extension['json']['adminGroupName'] && DB::getSession()->isAdminOrGroupAdmin($this->extension['json']['adminGroupName']) === true) {
            return true;
        }
        if ((int)$this->acl['rights']['write'] === 1) {
            return true;
        }
        return false;
    }

    protected function canDelete()
    {
        if ($this->extension['json']['adminGroupName'] && DB::getSession()->isAdminOrGroupAdmin($this->extension['json']['adminGroupName']) === true) {
            return true;
        }
        if ((int)$this->acl['rights']['delete'] === 1) {
            return true;
        }
        return false;
    }

    protected function canAdmin()
    {
        if ($this->extension['json']['adminGroupName'] && DB::getSession()->isAdminOrGroupAdmin($this->extension['json']['adminGroupName']) === true) {
            return true;
        }
        return false;
    }



    /**
     * Prüft, ob eine Person angemeldet ist.
     */
    protected function checkLogin()
    {

        if (!DB::isLoggedIn()) {
            if (in_array($this->request['page'], requesthandler::getAllowedActions())) {
                $redirectPage = $this->request['page'];
            } else {
                $redirectPage = "index";
            }
            if ($_REQUEST['message'] != "") {
                $message = "<div class=\"callout\"><p><strong>" . addslashes($_REQUEST['message']) . "</strong></p></div>";
            }
            $valueusername = "";
            eval("echo(\"" . DB::getTPL()->get("login/index") . "\");");
            PAGE::kill(false);
        }
    }


    /**
     * Zeigt die Seite an.
     */
    public abstract function execute();

    /**
     * @deprecated
     */
    public static function notifyUserAdded($userID)
    {
    }

    /**
     * @deprecated Soll in einem Cron abgearbeitet werden.
     */
    public static function notifyUserDeleted($userID)
    {
    }

    /**
     * Überprüft, ob der angegebene Classname aktiviert ist.
     * @param String $name Classname
     * @return boolean
     */
    public static function isActive($name)
    {
        if (!$name || !is_object($name) || !method_exists($name, 'siteIsAlwaysActive')) {
            return false;
        }
        if ( $name::siteIsAlwaysActive()) {
            return true;
        }
        if (sizeof(self::$activePages) == 0) {
            // Active Pages
            $pages = DB::getDB()->query("SELECT * FROM site_activation WHERE siteIsActive=1");
            while ($p = DB::getDB()->fetch_array($pages)) {
                self::$activePages[] = $p['siteName'];
            }
            if (sizeof(self::$activePages) == 0) {
                // Active Extensions
                $result = DB::getDB()->query('SELECT `id`,`name` FROM `extensions` WHERE `active` = 1 ');
                while ($row = DB::getDB()->fetch_array($result)) {
                    self::$activePages[] = $row['name'];
                }
            }
        }
        if (sizeof($name::onlyForSchool()) > 0) {
            if (!in_array(DB::getGlobalSettings()->schulnummer, $name::onlyForSchool())) {
                return false;
            }
        }
        return in_array($name, self::$activePages);
    }


    public static function getActivePages()
    {
        return self::$activePages;
    }

    public static function hasSettings()
    {
        return false;
    }

    public static function getSettingsDescription()
    {
        return [];
    }

    /**
     * Return Extension Settings from getSettingsDescription()
     */
    public function getSettings()
    {
        $settings = $this->getSettingsDescription();
        if (count($settings) > 0) {
            foreach ($settings as $key => $item) {
                $result = DB::getDB()->query_first('SELECT `settingValue` FROM `settings` WHERE `settingsExtension` = "ext_' . $this->extension['folder'] . '"  AND `settingName` = "' . $item['name'] . '" ');
                if (isset($result['settingValue'])) {
                    $settings[$key]['value'] = $result['settingValue'];
                }
            }
        } else {
            $result = DB::getDB()->query('SELECT `settingValue`, `settingName` FROM `settings` WHERE `settingsExtension` = "ext_' . $this->extension['folder'] . '" ');
            while ($data = DB::getDB()->fetch_array($result, true)) {

                if (isset($data['settingValue']) && $data['settingName']) {
                    $settings[$data['settingName']] = $data['settingValue'];
                }
            }
        }
        return $settings;
    }

    /**
     * Liest den Displaynamen der Seite aus.
     */
    public abstract static function getSiteDisplayName();


    /**
     * @deprecated
     */
    public static function getUserGroups()
    {
        return [];
    }

    /**
     * Zeigt an, ob die Seite immer aktiviert sein muss.
     * @return boolean true: Seite kann nicht deaktiviert werden.
     */
    public static function siteIsAlwaysActive()
    {
        return false;
    }

    /**
     * Gibt an, ob eine Seite von anderen abhängig ist. Dadurch können diese nicht deaktiviert werden solange abgeleitete Seiten aktiv sind.
     * @return String[] Seitennamen
     */
    public static function dependsPage()
    {
        return [];
    }

    /**
     * Liste der Schulnummer, für die diese Funktion exklusiv ist.
     * @return String[] Liste der Schulnummern, leer wenn für alle
     */
    public static function onlyForSchool()
    {
        return [];
    }

    /**
     * Setzt das Modul in den Auslieferungszustand zurück.
     * @return boolean Erfolgsmeldung
     */
    public static function resetPage()
    {
        return true;    // Sollte eine Seite keine Rücksetzmeldung haben, dann ist das Trotzdem ein Erfolg.
    }

    /**
     * Überprüft, ob die Seite eine Administration hat.
     * @return boolean
     */
    public static function hasAdmin()
    {
        return false;
    }

    /**
     * Icon im Menü
     * @return string
     */
    public static function getAdminMenuIcon()
    {
        return 'fa fa-cogs';
    }

    /**
     * Menügruppe in der das Adminmodul angezeigt wird.
     * @return string
     */
    public static function getAdminMenuGroup()
    {
        return 'NULL';
    }

    /**
     * Icon der Menügruppe
     * @return string
     */
    public static function getAdminMenuGroupIcon()
    {
        return 'fa fa-cogs';    // Zahnrad
    }

    /**
     * Überprüft, ob die Seite eine Benutzeradministration hat.
     * @return boolean
     */
    public static function hasUserAdmin()
    {
        return false;
    }

    /**
     * Liest die Gruppe aus, die Zugriff auf die Administration des Moduls hat.
     * @return String Gruppenname als String
     */
    public static function getAdminGroup()
    {
        return self::$adminGroupName;
    }

    /**
     * Setzt die Admin Gruppe als String
     * @param String Gruppenname als String
     */
    public static function setAdminGroup($str)
    {
        if ($str) {
            self::$adminGroupName = $str;
        }
    }


    /**
     * Gibt den Gruppennamen für die ACL Rechte zurück
     * @return String Gruppenname als String
     */
    public static function getAclGroup()
    {
        if (self::$aclGroupName) {
            return self::$aclGroupName;
        }
        return get_called_class();
    }

    /**
     * Setzt die ACL Gruppe als String
     * @param String Gruppenname als String
     */
    public static function setAclGroup($str)
    {
        if ($str) {
            self::$aclGroupName = $str;
        }
    }


    /**
     * Zeigt die Administration an. (Nur Bereich innerhalb des Main Body)
     * @param $selfURL URL zu sich selbst zurück (weitere Parameter können vom Script per & angehängt werden.)
     * @return HTML
     */
    public static function displayAdministration($selfURL)
    {
        return "";
    }

    /**
     * Zeigt die Benutzeradministration an. (Nur Bereich innerhalb von einem TabbedPane, keinen Footer etc.)
     * @param $selfURL URL zu sich selbst zurück (weitere Parameter können vom Script per & angehängt werden.)
     */
    public static function displayUserAdministration($selfURL)
    {
        return "";
    }

    /**
     * Benötigt das Modul eine zweiFaktor Authentifizierung.
     * <i>Noch nicht implementiert!</i>
     * @return boolean JaNein
     */
    public static function need2Factor()
    {
        return false;
    }

    /**
     * Archiviert das komplette Modul. (Rückgabe frei, je nach Modul)
     * <i>Noch nicht implementiert!</i>
     * @return boolean Erfolgreich?
     */
    public static function archiveDataForSchoolYear()
    {
        return false;
    }

    /**
     * Räumt das Modul regelmäßig per Cron auf.
     * @return Erfolgsmeldung
     */
    public static function cronTidyUp()
    {
        return true;
    }

    /**
     *
     * @param user $user Benutzer
     * @return boolean Zugriff
     */
    public static function userHasAccess($user)
    {
        return false;
    }


    /**
     * Gibt an, welche Aktion beim Schuljahreswechsel durchgeführt wird. (Leer, wenn keine Aktion erfolgt.)
     * @return String
     */
    public static function getActionSchuljahreswechsel()
    {
        return '';
    }

    /**
     * Führt den Schuljahreswechsel durch.
     * @param String $sqlDateFirstSchoolDay Erster Schultag
     */
    public static function doSchuljahreswechsel($sqlDateFirstSchoolDay)
    {
    }


    /**
     * Gibt das Submenu zurück
     * @return Array
     */
    public function getSubmenu()
    {
        $submenuHTML = '';
        if ($this->submenu) {
            foreach ($this->submenu as $item) {
                if ($item['href'] && $item['label']) {
                    $submenuHTML .= '<a href="' . $item['href'] . '" alt="" title="" class="' . $item['labelClass'] . '">' . $item['label'] . '</a>';
                }
            }
        }
        return $submenuHTML;
    }

    /**
     * Speichert das Submenu
     * @return Array
     */
    public function setSubmenu($submenu)
    {
        if ($submenu) {
            $this->submenu = $submenu;
        }
    }


    /**
     * Access Control List
     * @return acl
     */


    /**
     * @deprecated:  use getAclGroup
     */
    public function aclModuleName()
    {
        return get_called_class();
    }

    public function acl()
    {
        if (DB::getSession()) {
            $userID = DB::getSession()->getUser();
        }
        $moduleClass = $this->getAclGroup();
        if ($userID && $moduleClass) {

            $this->acl = ACL::getAcl($userID, $moduleClass, false, $this->getAdminGroup());
        }
    }

    public function getAclAll()
    {
        if (!$this->acl) {
            $this->acl();
        }
        return $this->acl;
    }

    public function getAcl()
    {
        if (!$this->acl) {
            $this->acl();
        }
        return ['rights' => $this->acl['rights'], 'owne' => $this->acl['owne']];
    }

    public function getAclRead()
    {
        return $this->acl['rights']['read'];
    }

    public function getAclWrite()
    {
        return $this->acl['rights']['write'];
    }

    public function getAclDelete()
    {
        return $this->acl['rights']['delete'];
    }

    /**
     * Ist das Modul im Beta Test?
     * @return Boolean
     */
    public static function isBeta()
    {
        return false;
    }


    /**
     * Generiert das Submenu (Array to HTML)
     *
     * @param submenu Array
     * @param dropdown Array
     * @return String (HTML)
     */
    private function makeSubmenu($submenu, $dropdown)
    {

        $html = '<div class="flex-row">';
        // Submenu
        $html .= '<div class="flex-3 page-submenue" style=""><div class="page-submenue-mobile">';
        $temp_arr = [];
        if (is_array($submenu) && count($submenu) >= 1) {
            foreach ($submenu as $item) {
                $kill = false;
                $item = (array)$item;
                if ($item['hidden']) {
                    $kill = true;
                    continue;
                }
                $class = '';
                if ($item['admin'] == 'true' && $this->isAnyAdmin == false) {
                    $kill = true;
                    continue;
                }
                if ($item['acl'] && $this->isAnyAdmin == false) {
                    $userType = DB::getSession()->getUser()->getUserTyp(true);
                    if ($userType && !in_array($userType, (array)$item['acl'])) {
                        $kill = true;
                        continue;
                    }
                }
                if ($kill == false && $item['url'] && $item['title']) {
                    $link = 'index.php?page=' . $item['url']->page;
                    $params_str = [];
                    if ($item['url']->params && count(get_object_vars($item['url']->params))) {
                        foreach ($item['url']->params as $params_key => $params_link) {
                            $params_str[] = $params_key . '=' . $params_link;
                        }
                        $params_str = join('&', $params_str);
                        $link .= '&' . $params_str;
                    }
                    $class = '';
                    if (DS . $link == URL_FILE) {
                        $class .= 'active';
                    }
                    if ($item['admin']) {
                        $class .= ' admin';
                    }
                    $temp_arr[] = [$link, $item['title'], $class, $item['icon']];
                }
            }
        }
        if ($temp_arr && count($temp_arr) >= 1) {
            foreach ($temp_arr as $foo) {
                $html .= '<a href="' . $foo[0] . '"  class=" ' . $foo[2] . '">';
                if ($foo[3]) {
                    $html .= '<i class="margin-r-s fa ' . $foo[3] . '"></i>';
                }
                $html .= $foo[1] . '</a>';
            }
        }
        $html .= '</div></div>';

        // Dropdown
        if (is_array($dropdown) && count($dropdown) >= 1) {
            $html .= '<div class="flex-1 page-dropdownMenue ">
									<button class="dropbtn"><i class="fas fa-ellipsis-v"></i></button>
									<div class="page-dropdownMenue-content">';
            foreach ($dropdown as $item) {
                $html .= '<a href="' . $item['url'] . '"';
                if ($item['target']) {
                    $html .= ' target="_blank" ';
                }
                $html .= 'class="margin-r-xs active">';
                if ($item['icon']) {
                    $html .= '<i class="margin-r-s ' . $item['icon'] . '"></i>';
                }
                $html .= $item['title'] . '</a>';
            }
            $html .= '</div></div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Getter Request
     *
     * @return Array
     */
    public function getRequest()
    {
        if ($this->request) {
            return $this->request;
        }
        return [];
    }

    /**
     * Redirect to same Page without url parameter z.b. &task=...
     *
     * @param String
     */
    public function reloadWithoutParam($str = 'task')
    {

        if ($str) {
            $parsed = parse_url($_SERVER['REQUEST_URI']);
            $query = $parsed['query'];
            parse_str($query, $params);
            unset($params[$str]);
            $string = http_build_query($params);
            header('Location: index.php?' . $string);
        } else {
            exit;
        }
    }


    public function taskSaveAdminACL()
    {
        if (DB::getSession()->isAdmin()) {
            if ($_POST['acl']) {
                echo json_encode(ACL::setAcl(json_decode($_POST['acl'])));
                exit;
            }
        }
        echo json_encode(array("error" => true));
        exit;
    }


    public function taskSaveAdminAdmins()
    {
        if (DB::getSession()->isAdmin() && $_POST['userlist']) {
            $groupName = self::getAdminGroup();
            if ($groupName) {
                DB::getDB()->query("DELETE FROM users_groups WHERE groupName='" . $groupName . "' ");
                $list = json_decode($_POST['userlist']);
                if ($list) {
                    foreach ($list as $user) {
                        $userID = (int)$user->id;
                        if ($userID) {
                            DB::getDB()->query("INSERT INTO users_groups (userID, groupName) values('" . $userID . "','" . $groupName . "') ");
                        }
                    }
                    echo json_encode(array("success" => true, "msg" => "Erfolgreich Gespeichert"));
                    exit;
                } else {
                    echo json_encode(array("success" => true, "msg" => "Erfolgreich Geleert"));
                    exit;
                }
            }
        }
        echo json_encode(array("error" => "Fehler beim Speichern"));
        exit;
    }


    static  function getGroupMembers($groupName)
    {

        if (!$groupName) {
            return false;
        }
        $obj = usergroup::getGroupByName($groupName);
        $list = $obj->getMembers();
        foreach ($list as $key => $item) {
            $list[$key] = $item->getCollection(false, true);
        }
        return $list;
    }

    public function taskSaveAdminSettings()
    {

        if (DB::getSession()->isAdmin()) {
            $settings = json_decode($_POST['settings']);
            $request = $this->getRequest();
            if ($request['page'] && $settings) {
                foreach ($settings as $item) {

                    DB::getDB()->query("INSERT INTO settings (settingName, settingValue, settingsExtension)
				values ('" . DB::getDB()->escapeString($item->name) . "',
				'" . DB::getDB()->escapeString(($item->value)) . "'
				,'" . DB::getDB()->escapeString(($request['page'])) . "')
				ON DUPLICATE KEY UPDATE settingValue='" . DB::getDB()->escapeString($item->value) . "'");
                }
                echo json_encode(['success' => true, "msg" => "Erfolgreich gespeichert!"]);
            } else {
                echo json_encode(['error' => 'Fehler beim Speichern!']);
            }
            exit;
        }
        echo json_encode(array("error" => "Fehler beim Speichern"));
        exit;
    }
}
