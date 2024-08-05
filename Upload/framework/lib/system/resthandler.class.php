<?php


if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }
        return $headers;
    }
}

class resthandler
{
    private static $actions = [
        'GetSettingsValue',
        'SetSettingsValue',
        'GetAllSettings',
        'GetUserCount',
        'SetMensaMeal',
        'SetMensaOrder',
        'GetAcl',
        'SetAcl',
        'GetKalender',
        'GetKalenderEintrag',
        'SetKalenderEintrag',
        'DeleteKalenderEintrag',
        //'GetKalenderIcsFeed',
        'GetUser'
    ];

    private $extension = false;

    public function __construct()
    {

        error_reporting(E_ERROR);

        include_once(PATH_LIB.'models'.DS ."abstractRest.class.php");
        

        $allowed = false;
        $type = false;

        $adminGroupName = false;

        $headers = getallheaders();
        $method = $_SERVER['REQUEST_METHOD']; // GET or POST
        $request = explode('/', trim($_SERVER['PATH_INFO'], '/'));


        if (sizeof($request) == 0) {
            $result = [
                'error' => 1,
                'errorText' => 'No Action given'
            ];
            $this->answer($result, 404);
        }

        if (in_array($request[0], self::$actions)) {
            if (file_exists(PATH_LIB . "rest" . DS . "Rest" . $request[0] . ".class.php")) {
                include_once(PATH_LIB . "rest" . DS . "Rest" . $request[0] . ".class.php");
                $allowed = true;
                $classname = 'Rest' . $request[0];
                $type = 'page';
            }
        }

        if ($allowed == false && $request[1]) {
            $module = DB::getDB()->query_first("SELECT `id`,`name`,`folder` FROM extensions WHERE `folder` = '" . $request[0] . "'");
            if ($module) {
                define("PATH_EXTENSION", PATH_EXTENSIONS . $module['folder'] . DS);
                define("PATH_EXTENSION_ROOT", PATH_EXTENSIONS . $module['folder'] . DS);

                if (file_exists(PATH_EXTENSIONS . $module['folder'] . '/rest/' . $request[1] . '.php')) {
                    
                    if (file_exists(PATH_LIB . 'models' . DS . 'extensionsModel.class.php')) {
                        include_once(PATH_LIB . 'models' . DS . 'extensionsModel.class.php');
                    }

                    include_once(PATH_EXTENSIONS . $module['folder'] . '/rest/' . $request[1] . '.php');
                    $allowed = true;
                    $classname = $request[1];
                    $type = 'extension';

                    $this->extension = FILE::getExtensionJSON(PATH_EXTENSION . 'extension.json');
                    if (isset($this->extension)) {
                        $adminGroupName = $this->extension['adminGroupName'];
                    }
                }
            }
        }

        // Keine Action/Modul gefunden
        if ($allowed == false) {
            $result = [
                'error' => 3,
                'errorText' => 'Unknown Action'
            ];
            $this->answer($result, 404);
        }


        if ($allowed) {

            PAGE::setFactory(new FACTORY());

            $do = false;
            $action = new $classname($type);

            if ($method != $action->getAllowedMethod()) {
                $result = [
                    'error' => 2,
                    'errorText' => 'method not allowed'
                ];
                $this->answer($result, 405);
            }
            

            if ($action->needsUserAuth()) {
                if (isset($_COOKIE['schuleinternsession'])) {
                    DB::initSession($_COOKIE['schuleinternsession']);
                    if (!DB::isLoggedIn()) {
                        if (isset($_COOKIE['schuleinternsession'])) {
                            setcookie("schuleinternsession", null);
                        }
                        $this->answer([], 401);
                    } else {
                        if ($action->needsAdminAuth()) {
                            if (DB::getSession()->isAdminOrGroupAdmin($adminGroupName) !== true) {
                                $this->answer([], 401);
                            }
                        }
                        DB::getSession()->update();
                        $action->user = DB::getSession()->getUser();
                        /*
                        if ($action->aclModuleName()) {
                            $action->setAclGroup($action->aclModuleName());
                        }
                        */
                        $action->acl();
                        $do = true;
                    }
                } else {
                    $this->answer([], 401);
                }
            } 

            // Check Auth
            if ($action->needsSystemAuth()) {
                $apiKey = null;
                foreach ($headers as $headername => $headervalue) {
                    if (strtolower($headername) == 'authorization' && $headervalue) {
                        $apiKey = $headervalue;
                    }
                }

                if (!(string)$apiKey || (string)$apiKey !== (string)DB::getGlobalSettings()->apiKey) {
                    $result = [
                        'error' => 1,
                        'errorText' => 'Auth Failedd'
                    ];
                    $this->answer($result, 401);
                }

                $do = true;
            } 

            

            // APP API Auth
            if (!$do && $action->needsAppAuth()) {

                $appKey = false;
                $appSession = false;

                foreach ($headers as $headername => $headervalue) {
                    if (strtolower($headername) == 'auth-app' && $headervalue) {
                        $appKey = (string)$headervalue;
                    }
                    if (strtolower($headername) == 'auth-session' && $headervalue) {
                        $appSession = (string)trim($headervalue);
                    }
                }


                if (!$appKey || $appKey !== (string)DB::getGlobalSettings()->apiKey) {
                    $result = [
                        'error' => 1,
                        'errorText' => 'App Auth Failed '.$appKey
                    ];
                    $this->answer($result, 401);
                }

                

                if ($appSession && ( $appSession != 'null' && $appSession != null) ) {

                    DB::initSession($appSession);
                    $action->user = DB::getSession()->getUser();
                    $action->acl();
                    $do = true;

                } else {

                    if (isset($_COOKIE['schuleinternsession'])) {
                        DB::initSession($_COOKIE['schuleinternsession']);
                        if (DB::isLoggedIn()) {
                            $action->user = DB::getSession()->getUser();
                            $action->acl();
                            $do = true;
                        }
                    }

                }

            }

            if ($do != true) {
                if (!$action->checkAuth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                    $result = [
                        'error' => 1,
                        'errorText' => 'Auth Failed'
                    ];
                    $this->answer($result, 401);
                }
                $do = true;
            }

            


            $input = self::getPostData();

            // Execute wird nur aufgerufen, wenn die Authentifizierung erfolgreich war.
            if ($do == true) {
                $result = $action->execute($input, $request);
            }
            
            if (!is_array($result) && !is_object($result)) {
                $result = [
                    'error' => 1,
                    'errorText' => 'Missing Return'
                ];

                if ($action->getStatusCode() == 200) {
                    // Interner Fehler, da kein Status gesetzt wurde
                    $this->answer($result, 500);
                } else {
                    $this->answer($result, $action->getStatusCode());
                }
            } else {
                $this->answer($result, $action->getStatusCode());
            }
        }
    }

    private function answer($result, $statusCode)
    {

        header("Content-type: application/json");
        http_response_code($statusCode);
        print(json_encode($result));
        exit(0);
    }

    public static function __htmlspecialchars($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[htmlspecialchars($key)] = self::__htmlspecialchars($value);
            }
        } else if (is_object($data)) {
            $values = get_class_vars(get_class($data));
            foreach ($values as $key => $value) {
                $data->{htmlspecialchars($key)} = self::__htmlspecialchars($value);
            }
        } else {
            $data = stripslashes(strip_tags(htmlspecialchars($data)));
        }
        return $data;
    }

    public static function getPostData()
    {
        $postData = [];
        if ($_POST) {
            foreach ($_POST as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = self::__htmlspecialchars($val);
            }
        }
        $_post = json_decode(file_get_contents("php://input"), TRUE);
        if ($_post) {
            foreach ($_post as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = self::__htmlspecialchars($val);
            }
        }
        if ($_GET) {
            foreach ($_GET as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = self::__htmlspecialchars($val);
            }
        }
        return $postData;
    }
}
