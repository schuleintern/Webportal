<?php

/**
 * Globale Page Class für den Admin Bereich
 * 
 * @author Christian Marienfeld
 */


class adminhandler {
  

  private static $allAdminGroups = [];

  public function __construct($action, $_request) {

    PAGE::setFactory( new FACTORY() );

    $allowed = false;
    
    
    // Load extensions
    if (!$allowed) {
      $view = 'default';
      if ($_request['view']) {
        $view = $_request['view'];
      }

      $extension = self::loadExtensions($action, $view);
      if ($extension['allowed'] == true && $extension['classname']) {
        $allowed = true;
        $action = $extension['classname'];
      }
      
      
    }
  
    if($allowed && $extension) {
      try {
        $page = new $action($_request, $extension);
        
        if ($_request['task']) {
          $taskMethod = 'task'.ucfirst($_request['task']);
          if ( method_exists($page, 'task'.ucfirst($_request['task']) )) {
            $postData = [];
            $_post = json_decode(file_get_contents("php://input"), TRUE);
            if ($_post) {
              foreach($_post as $key => $val) {
                $postData[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = $val;
              }
            }
            $page->$taskMethod($postData);
            exit;
          } else {
            new errorPage('Task was not found! <br> ( task: '.$taskMethod.' )');
            exit;
          }
        } else {
          
          $page->execute();
        }
      }
      catch(Throwable $e) {
        echo "<b>!!!" . $e->getMessage() . "</b> in Line " . $e->getLine()  . " in " . $e->getFile() . "<br />";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
      }
    } else {
      new errorPage('There is no Page');
      die();
    }
    PAGE::kill(true);
    
  }


  /**
   * Load Extensions
   * 
   * @param unknown $action
   * @return boolean
   */
  public static function loadExtensions($action, $view = 'default') {
    
    if (!$action) {
      return false;
    }
    $allowed = false;
    $module = DB::getDB()->query_first("SELECT `id`,`folder` FROM extensions WHERE `folder` = '".$action."'" );
    if ($module && $module['folder'] && $view) {
      if (file_exists('../extensions/'.$module['folder'].'/admin/'.$view.'.php')) {
        require_once ('../framework/lib/page/abstractPage.class.php');
        include_once('../extensions/'.$module['folder'].'/admin/'.$view.'.php');
        $allowed = true;

      }
    }
    if ($allowed) {
      define("PATH_EXTENSION", PATH_ROOT."extensions".DS.$module['folder'].DS."admin".DS);

      return [
        'allowed' => true,
        'classname' => 'admin'.ucfirst($module['folder']).ucfirst($view),
        'folder' => $module['folder'],
        'view' => $view
        //,'settings' => json_decode($module['settings']),
      ];
    }
    return false;
}

 

}

?>
