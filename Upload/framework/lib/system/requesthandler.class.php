<?php

/**
 * Verwaltet die Aktionen des Benutzers.
 * @author Christian Spitschka
 */
class requesthandler {
    /**
     * Erlaubte Aktionen.
     * @var array
     */
  private static $actions = [
  	'notenverwaltung' => [
  		'NotenverwaltungIndex',
  		'NotenEingabe',
  	    'NotenverwaltungZeugnisse',
  	    'NotenverwaltungRecoverZuordnung',
  	    'NotenZeugnisMV',
  	    'NotenWahlunterricht',
  	    'NotenZeugnisKlassenleitung',
  	    'NotenBerichte',
  	    'NotenRespizienz'
  	],
    'ausweis' => [
        'Ausweis'
    ],
      /**  'bibliothek' => [
      'Bibliothek'
      ],**/
      'oauth2' => [
          'oAuth2Auth'
      ],
  	'files' => [
  		'FileDownload'
  	],
  	'skin' => [
  		'SkinSettings'
  	],
    /**  'nextcloud' => [
        'nextcloud'
    ], **/
    'json' => [
        'jsonApi'
    ],
    /*'allinklmail' => [
        'AllInkMail'
    ],*/
    'absenzen' => [
      'absenzenberichte',
      'absenzenlehrer',
      'absenzensekretariat',
      'absenzenstatistik',
      'absenzenschueler',
        'AbsenzenMain'
    ],
  	'messages' => [
  		'MessageInbox',
  		'MessageRead',
  		'MessageCompose',
  		'MessageSendRights',
  		'MessageAttachmentDownload',
  		'MessageConfirm'
  	],
    'kondolenzbuch' => [
        'kondolenzbuch'
    ],
    'administration' => [
      'administration',
      'administrationactivatepages',
      'administrationasvimport',
      'administrationbadmails',
      'administrationcreateusers',
      'administrationimportmgsd2',
      'administrationsettings',
      'administrationunknownmails',
      'administrationusermatch',
      'administrationusers',
      'administrationusersync',
      'administrationmodule',
      'administrationcron',
      'administrationgroups',
      'AdminMailSettings',
      'AdminUpdate',
      'AdminBackup',
      'AdminDatabase',
      'AdministrationEltern',
      'AdminDatabaseUpdate',
      'AdminExtensions',
      'AdminMenu',
      'AdminWidgets'
    ],
    'aufeinenblick' => [
      'aufeinenblick',
    ],
    'ausleihe' => [
      'ausleihe',
      'ausleiheMonitor',
    ],
  	'beurlaubung' => [
  	    'beurlaubungantrag'
  	],
  	'digitalSignage' => [
  		'digitalSignage',
  		'digitalSignageLayoutPowerpoints',
  	    'digitalSignageWebsites'
  	],
    'beobachtungsbogen' => [
      'beobachtungsbogen',
      'beobachtungsbogenadmin',
      'beobachtungsbogenklassenleitung',
    ],
    'dokumente' => [
      'dokumente',
    ],
    'elternsprechtag' => [
      'elternsprechtag',
    ],
  	'schaukasten' => [
  		'schaukasten'
  	],

    'respizienz' => [
        'respizienz'
    ],
  		
    'kalender' => [
      'klassenkalender',
      'extKalender',
      'andereKalender',
      'geticsfeed',
      'terminuebersicht',
      'kalenderAllInOne'
    ],
    'klassenlisten' => [
      'klassenlisten',
    ],
    'ganztags' => [
      'ganztags',
      'ganztagsEdit',
      'ganztagsCalendar'
    ],
    'krankmeldung' => [
      'krankmeldung',
    ],
    'laufzettel' => [
      'laufzettel',
    ],
    'legal' => [
      'impressum',
      'datenschutz'
    ],
    'loginlogout' => [
      'login',
      'logout',
    ],
    'mebis' => [
      'mebis',
    ],
    'mensa' => [
      'mensaSpeiseplan'
    ],
    'office365' => [
      'office365',
      'office365users',
      'office365info',
        'Office365Meetings'
    ],
    'oldpages' => [
      'homeuseprogram',
    ],
    'projektverwaltung' => [
      'projektverwaltung',
    ],
    'register' => [
      'elternregister',
    ],
      'raumplan' => [
          'raumplan',
      ],
    'stundenplan' => [
        'stundenplan'

    ],
    'system' => [
      'errorPage',
      'GetMathCaptcha',
      'index',
      'info',
      'Update',
      'Backup'
    ],
    'userprofile' => [
      'changeuseridinsession',
      'forgotPassword',
      'userprofile',
      'userprofilemylogins',
      'userprofilepassword',
      'userprofilesettings',
      'userprofileuserimage',
        'TwoFactor'
    ],
      'lerntutoren' => [
          'Lerntutoren'
      ],
    'vplan' => [
      'updatevplan',
      'vplan'
    ],
  	'test' => [
  		'test'
  	],
  	'klassentagebuch' => [
  		'klassentagebuch',
  	    'klassentagebuchauswertung'
  	],
  	'print' => [
  		'printSettings'
  	],
  	'schulinfo' => [
  		'schulinfo'
  	],
  	'schulbuch' => [
  		'schulbuecher'
  	],
  	'schuelerinfo' => [
  		'schuelerinfo',
  		'AngemeldeteEltern'
  	],
    'support' => [
        'adminsupport'
    ],
    'wlan' => [
        'WLanTickets'
    ]

  ];


  private static $allAdminGroups = [];

  public function __construct($action, $_request = []) {

    PAGE::setFactory( new FACTORY() );

    $allowed = false;
    $type = false;
    
    // First: Load Page
    $allowed = self::loadPage($action);
    if ($allowed) {
      $type = 'page';
    }

    // Second: Load extensions
    if (!$allowed) {
      $view = 'default';
      if ($_request['view']) {
        $view = $_request['view'];
      }
      if (substr($action, 0, 4) === 'ext_') {
          $extension = self::loadExtensions($action, $view, $_request['admin']);
          if ($extension['allowed'] == true && $extension['classname']) {
              $allowed = true;
              $type = 'extension';
              $action = $extension['classname'];
              if ($_request['admin']) {
                  define("PATH_EXTENSION", PATH_EXTENSIONS.$extension['folder'].DS."admin".DS);
              } else {
                  define("PATH_EXTENSION", PATH_EXTENSIONS.$extension['folder'].DS);
              }
          }
      }

    }

    if($allowed) {
      try {
        $page = new $action($_request, $extension);

        if ($type == 'extension' && $_request['task']) {
          
          $taskMethod = 'task'.ucfirst($_request['task']);
          if ( method_exists($page, 'task'.ucfirst($_request['task']) )) {
            $postData= self::getPostData();
            $page->$taskMethod($postData);
            exit;
          } else {
            new errorPage('Task was not found! <br> ( task: '.$taskMethod.' )');
            exit;
          }
        } else {
            if ($type == 'extension') {

                self::loadFileFromFolder( PATH_EXTENSION.'models');

                if ($extension['json'] && $extension['json']->dependencies) {
                    foreach($extension['json']->dependencies as $dependencies) {
                        $where[] = '`uniqid` = "'.$dependencies.'"';
                    }
                    $result = DB::getDB()->query('SELECT `folder` FROM `extensions` WHERE '.implode(' OR ', $where) );
                    while($row = DB::getDB()->fetch_array($result)) {
                        self::loadFileFromFolder(PATH_EXTENSIONS.$row['folder'].DS.'models');
                    }
                }

            }
          $page->execute();
        }
      }
      catch(Throwable $e) {
        echo "<b>!!!" . $e->getMessage() . "</b> in Line " . $e->getLine()  . " in " . $e->getFile() . "<br />";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
      }
    } else {
      new errorPage('Die Seite existiert nicht!');
      die();
    }
    PAGE::kill(true);
    
  }

  /**
   * Erlaute Aktion am RequestHandler
   * 
   * @return array
   */
  public static function getAllowedActions() {
    $ps = [];
    // Active Pages
    foreach(self::$actions as $f => $pages) {
      for($p = 0; $p < sizeof($pages); $p++) {
        $ps[] = $pages[$p];
      }
    }
    // Active Modules
    // $result = DB::getDB()->query('SELECT `id`,`folder` FROM `extensions` WHERE `active` = 1 ');
		// while($row = DB::getDB()->fetch_array($result)) {
    //   $ps[] = $row['folder'];
    // }

    return $ps;
  }

    public static function getAllAdminGroups() {
        if(sizeof(self::$allAdminGroups) == 0) {
            $allPages = self::getAllowedActions();
            for($i = 0; $i < sizeof($allPages); $i++) {
                if($allPages[$i]::getAdminGroup() != "") self::$allAdminGroups[] = $allPages[$i]::getAdminGroup();
            }
        }

        return self::$allAdminGroups;
    }
  
  /**
   * Load Page
   * 
   * @param unknown $action
   * @return boolean
   */
  public static function loadPage($action) {
      
      $allowed = false;

      foreach(self::$actions as $f => $pages) {
        for($p = 0; $p < sizeof($pages); $p++) {
            
          if($pages[$p] == $action) {
            require_once (PATH_PAGE.'abstractPage.class.php');
            include_once(PATH_PAGE . $f . '/' . $pages[$p] . '.class.php');
            $allowed = true;
          }
        }
      }
      return $allowed;
  }

  /**
   * Load Extensions
   * 
   * @param unknown $action
   * @return boolean
   */
  public static function loadExtensions($action, $view = 'default', $admin = false) {
      
    $allowed = false;

    $realname = str_replace('ext_','',$action);
    $extension = DB::getDB()->query_first("SELECT `id`,`folder` FROM extensions WHERE `folder` = '".$realname."' AND `active` = 1 " );
    if ($extension && $extension['folder'] && $view) {
      if ($admin) {
        $path = PATH_EXTENSIONS.$extension['folder'].DS.'admin'.DS.$view.'.php';
      } else {
        $path = PATH_EXTENSIONS.$extension['folder'].DS.$view.'.php';
      }
      if (file_exists($path)) {
        require_once (PATH_PAGE.'abstractPage.class.php');
        include_once($path);
        $allowed = true;
      }
    }

    if ($allowed) {
      if ($admin) {
        $classname = 'ext'.ucfirst($extension['folder']).'Admin'.ucfirst($view);
      } else {
        $classname = 'ext'.ucfirst($extension['folder']).ucfirst($view);
      }
      $json = AbstractPage::getExtensionJSON(PATH_EXTENSIONS.$extension['folder'].DS.'extension.json');
      return [
        'allowed' => true,
        'classname' => $classname,
        'folder' => $extension['folder'],
        'view' => $view,
        'json' => $json
      ];
    }
    return false;
  }



  public static function loadFileFromFolder($path = false) {
      if ( $path && is_dir($path)) {
          $scanned_directory = FILE::getFilesInFolder($path);
          foreach($scanned_directory as $file) {
              if (file_exists($path.DS.$file['filename'])) {
                  include_once($path.DS.$file['filename']);
              }
          }
      }
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

?>
