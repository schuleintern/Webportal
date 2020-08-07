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
    'ffbumfrage' => [
        'ffbumfrage'
    ],
  	'files' => [
  		'FileDownload'
  	],
  	'skin' => [
  		'SkinSettings'
  	],
    'nextcloud' => [
        'nextcloud'
    ],
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
      'absenzenschueler'
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
        'AdministrationEltern'
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
    'datenbanken' => [
      'database',
    ],
    'downloads' => [
      'downloads',
      'downloadsteacher'
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
      'terminuebersicht'
    ],
    'klassenlisten' => [
      'klassenlisten',
    ],
    'ganztags' => [
      'ganztags',
      'ganztagsEdit'
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
    'office365' => [
      'office365',
      'office365users',
      'office365info'
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
    'stundenplan' => [
        'stundenplan'

    ],
    'system' => [
      'errorPage',
      'GetMathCaptcha',
      'index',
      'info',
        'Update'
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

  public function __construct($action) {


    $allowed = false;
    
    require_once ('../framework/lib/page/abstractPage.class.php');

    foreach(self::$actions as $f => $pages) {
      for($p = 0; $p < sizeof($pages); $p++) {
        include_once('../framework/lib/page/' . $f . '/' . $pages[$p] . '.class.php');
        if($pages[$p] == $action) $allowed = true;
      }
    }

    if($allowed) {
      try {
        $page = new $action;
        $page->execute();
      }
      catch(Throwable $e) {
          // TODO: FEHLER abfangen
        echo "<b>" . $e->getMessage() . "</b> in Line " . $e->getLine()  . " in " . $e->getFile() . "<br />";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
      }
    } else {
      new errorPage();
      die();
    }
    PAGE::kill(true);
    
  }

    /**
     * Erlaute Aktion am RequestHandler
     * @return array
     */
  public static function getAllowedActions() {
    $ps = [];
    foreach(self::$actions as $f => $pages) {
      for($p = 0; $p < sizeof($pages); $p++) {
        $ps[] = $pages[$p];
      }
    }
    
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
   * 
   * @param unknown $action
   * @return boolean
   */
  public static function loadPage($action) {
      
      $allowed = false;
      
      require_once ('../framework/lib/page/abstractPage.class.php');
      
      
      foreach(self::$actions as $f => $pages) {
          for($p = 0; $p < sizeof($pages); $p++) {
              
              if($pages[$p] == $action) {
                  include_once('../framework/lib/page/' . $f . '/' . $pages[$p] . '.class.php');
                  $allowed = true;
              }
          }
      }
      
      return $allowed;
      
  }

 

}

?>
