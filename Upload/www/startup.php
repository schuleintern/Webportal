<?php


/**
 * Define Default Folders Path and URLs
 */
define("DS", '/');
define("URL_ROOT", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"  );
define("URL_SELF", URL_ROOT.$_SERVER[REQUEST_URI]);

define("PATH_WWW", '.'.DS);
define("PATH_ROOT", PATH_WWW.'..'.DS);
define("PATH_EXTENSIONS", PATH_ROOT.'extensions'.DS);
define("PATH_TMP", PATH_ROOT.'tmp'.DS);
define("PATH_LIB", PATH_ROOT.'framework'.DS.'lib'.DS);
define("PATH_PAGE", PATH_ROOT.'framework'.DS.'lib'.DS.'page'.DS);
define("PATH_COMPONENTS", PATH_WWW.'components'.DS);
define("PATH_TMPL_OVERRIGHTS", PATH_WWW.'tmpl'.DS);
// PATH_EXTENSION (set by abstractPage.class.php)


include("../framework/lib/system/autoloader.php");

// Datenbank verbinden
DB::start();
session::cleanSessions();

// Garbage Collection durchführen
GarbageCollector::EveryRequest();

// if(DB::isloggedin() && (!isset($_GET['page']) || $_GET['page'] == "")) $_GET['page'] = "vplan";
