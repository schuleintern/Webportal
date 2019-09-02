<?php


if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = array ();
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }
        return $headers;
    }
} 

class resthandler {
  private static $actions = [
     'GetSystemInfo',
     'GetSettingsValue',
     'SetSettingsValue',
     'GetAllSettings',
     'GetUserCount',
  ];
  
  

  public function __construct() {
      
      include_once("../framework/lib/rest/AbstractRest.class.php");

      $authHeaderFound = false;

      $headers = getallheaders();

      foreach($headers as $headername => $headervalue) {
          if(strtolower($headername) == 'authorization') {
              $authHeaderFound = true;
          }
      }

      if (!isset($_SERVER['PHP_AUTH_USER']) && !$authHeaderFound) {
          $result = [
              'error' => 1,
              'errorCode' => '401',
              'errorText' => 'Auth Failed!',
          ];
          
          $this->answer($result, 401);
          exit();
      }

     // print_r($headers);
      
      if(!isset($headers['schuleinternapirequest']) || $headers['schuleinternapirequest'] != true) {
          $result = [
              'error' => 1,
              'errorText' => 'schuleinternapirequest header not set. '
          ];
          $this->answer($result, 400);
      }
      
      $method = $_SERVER['REQUEST_METHOD'];
      
      $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
      
      $input = json_decode(file_get_contents('php://input'),true);
      
      if(sizeof($request) == 0) {
          $result = [
              'error' => 1,
              'errorText' => 'No Action given'
          ];
          $this->answer($result, 404);
      }
      
      if(in_array($request[0],self::$actions)) {
          
          include_once("../framework/lib/rest/Rest" . $request[0] . ".class.php");
          
          $classname = 'Rest' . $request[0];
          
          /**
           * 
           * @var AbstractRest $action
           */
          $action = new $classname();
          
          
          if($method != $action->getAllowedMethod()) {
              $result = [
                  'error' => 1,
                  'errorText' => 'method not allowed'
              ];
              $this->answer($result, 405);
          }
                
                // Check Auth
            if ($action->needsSystemAuth()) {

                $apiKey = null;

                foreach($headers as $headername => $headervalue) {
                    if(strtolower($headername) == 'authorization') {
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
                    exit();
                }
            }
            else {
                if (!$action->checkAuth($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])) {
                    $result = [
                        'error' => 1,
                        'errorText' => 'Auth Failed'
                    ];
                    
                    $this->answer($result, 401);
                    exit();
                }
            }

          // Execute wird nur aufgerufen, wenn die Authentifizierung erfolgreich war.
          $result = $action->execute($input, $request);
      
          if(!is_array($result) && !is_object($result)) {
              $result = [
                  'error' => 1
              ];
              
              if($action->getStatusCode() == 200) {
                  // Interner Fehler, da kein Status gesetzt wurde
                  $this->answer($result, 500);
              }
              else {
                  $this->answer($result, $action->getStatusCode());
              }
              
          }
          else {
              $this->answer($result,$action->getStatusCode());
          }
      }
      else {
          $result = [
              'error' => 1,
              'errorText' => 'Unknown Action'
          ];
          $this->answer($result, 404);
      }
  }
  
  private function answer($result, $statusCode) {
      header("Content-type: application/json");
      
      http_response_code($statusCode);
          
      print(json_encode($result));
      
      exit(0);
  }

}