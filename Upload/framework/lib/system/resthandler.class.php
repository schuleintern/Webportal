<?php


if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array ();
        foreach ($_SERVER as $name => $value)  {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }
        return $headers;
    }
} 

class resthandler {
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
  
  
    public function __construct() {

        error_reporting(E_ERROR);

        include_once(PATH_LIB."rest/AbstractRest.class.php");

        $authHeaderFound = false;
        $allowed = false;
        $type = false;

        $headers = getallheaders();
        $method = $_SERVER['REQUEST_METHOD']; // GET or POST
        $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));


        if(sizeof($request) == 0) {
            $result = [
                'error' => 1,
                'errorText' => 'No Action given'
            ];
            $this->answer($result, 404);
        }

        if(in_array($request[0],self::$actions)) {
            if (file_exists(PATH_LIB."rest".DS."Rest" . $request[0] . ".class.php")) {
                include_once(PATH_LIB."rest".DS."Rest" . $request[0] . ".class.php");
                $allowed = true;
                $classname = 'Rest' . $request[0];
                $type = 'page';
            }

        } 

        if ( $allowed == false && $request[1] ) {
            $module = DB::getDB()->query_first("SELECT `id`,`name`,`folder` FROM extensions WHERE `folder` = '".$request[0]."'" );
            if ($module) {
              if (file_exists(PATH_EXTENSIONS.$module['folder'].'/rest/'.$request[1].'.php')) {
                include_once(PATH_EXTENSIONS.$module['folder'].'/rest/'.$request[1].'.php');
                $allowed = true;
                $classname = $request[1];
                $type = 'extension';
                define("PATH_EXTENSION", PATH_EXTENSIONS.$module['folder'].DS);
              }
            }
        }

        // Keine Action/Modul gefunden
        if ( $allowed == false ) {
            $result = [
                'error' => 3,
                'errorText' => 'Unknown Action'
            ];
            $this->answer($result, 404);
        }


        if ($allowed) {

            PAGE::setFactory( new FACTORY() );

            $action = new $classname($type);

            if($method != $action->getAllowedMethod()) {
                $result = [
                    'error' => 2,
                    'errorText' => 'method not allowed'
                ];
                $this->answer($result, 405);
            }

            if($action->needsUserAuth()) {
                if (isset ( $_COOKIE ['schuleinternsession'] )) {
                    DB::initSession ( $_COOKIE ['schuleinternsession'] );
                    if (! DB::isLoggedIn ()) {
                        if (isset ( $_COOKIE ['schuleinternsession'] )) {
                            setcookie ( "schuleinternsession", null );
                        }
                        $this->answer([], 401);
                    } else {
                        if($action->needsAdminAuth()) {
                            if ( !DB::getSession()->getUser()->isAdmin() ) {
                                $this->answer([], 401);
                            }
                        }
                        DB::getSession()->update ();
                        $action->user = DB::getSession()->getUser();
                        /*
                        if ($action->aclModuleName()) {
                            $action->setAclGroup($action->aclModuleName());
                        }
                        */
                        $action->acl();
                    }
                } else {
                    $this->answer([], 401);
                }
            } else {

                foreach($headers as $headername => $headervalue) {
                    if(strtolower($headername) == 'authorization') {
                        $authHeaderFound = true;
                    }
                }

                // Check Auth
                if ($action->needsSystemAuth()) {
                    $apiKey = null;
                    foreach ($headers as $headername => $headervalue) {
                        if (strtolower($headername) == 'authorization') {
                            $authHeaderFound = true;
                            $apiKey = substr($headervalue, 7);
                        }
                    }
                    if ($apiKey != null && $apiKey != DB::getGlobalSettings()->apiKey) {
                        $result = [
                            'error' => 1,
                            'errorText' => 'Auth Failedd',
                            'SERVER' => $apiKey
                        ];
                        $this->answer($result, 401);
                    }
                } else {
                    if (!$action->checkAuth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                        $result = [
                            'error' => 1,
                            'errorText' => 'Auth Failed'
                        ];
                        $this->answer($result, 401);
                    }
                }
            }


            $input = self::getPostData();

            // Execute wird nur aufgerufen, wenn die Authentifizierung erfolgreich war.
            $result = $action->execute($input, $request);

            if(!is_array($result) && !is_object($result)) {
                $result = [
                    'error' => 1,
                    'errorText' => 'Missing Return'
                ];
                
                if($action->getStatusCode() == 200) {
                    // Interner Fehler, da kein Status gesetzt wurde
                    $this->answer($result, 500);
                } else {
                    $this->answer($result, $action->getStatusCode());
                }
                
            } else {
                $this->answer($result,$action->getStatusCode());
            }
        }

    }

    private function answer($result, $statusCode) {

        header("Content-type: application/json");
        http_response_code($statusCode);
        print(json_encode($result));
        exit(0);
    }

    public static function __htmlspecialchars($data) {
        if (is_array($data)) {
            foreach ( $data as $key => $value ) {
                $data[htmlspecialchars($key)] = self::__htmlspecialchars($value);
            }
        } else if (is_object($data)) {
            $values = get_class_vars(get_class($data));
            foreach ( $values as $key => $value ) {
                $data->{htmlspecialchars($key)} = self::__htmlspecialchars($value);
            }
        } else {
            $data = stripslashes(strip_tags(htmlspecialchars($data)));
        }
        return $data;
    }

    public static function getPostData() {
        $postData = [];
        if ($_POST) {
            foreach($_POST as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = self::__htmlspecialchars($val);
            }
        }
        $_post = json_decode(file_get_contents("php://input"), TRUE);
        if ($_post) {
            foreach($_post as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = self::__htmlspecialchars($val);
            }
        }
        if ($_GET) {
            foreach($_GET as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = self::__htmlspecialchars($val);
            }
        }
        return $postData;
    }

}