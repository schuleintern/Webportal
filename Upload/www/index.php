<?php

/**
 *
 * Software is licensed unter GPLv2
 *
 * (c) Christian Spitschka, 2012-2019
 *
 * _______  _______                    _        _______ _________ _       _________ _______  _______  _
 * (  ____ \(  ____ \|\     /||\     /|( \      (  ____ \\__   __/( (    /|\__   __/(  ____ \(  ____ )( (    /|
 * | (    \/| (    \/| )   ( || )   ( || (      | (    \/   ) (   |  \  ( |   ) (   | (    \/| (    )||  \  ( |
 * | (_____ | |      | (___) || |   | || |      | (__       | |   |   \ | |   | |   | (__    | (____)||   \ | |
 * (_____  )| |      |  ___  || |   | || |      |  __)      | |   | (\ \) |   | |   |  __)   |     __)| (\ \) |
 *       ) || |      | (   ) || |   | || |      | (         | |   | | \   |   | |   | (      | (\ (   | | \   |
 * /\____) || (____/\| )   ( || (___) || (____/\| (____/\___) (___| )  \  |   | |   | (____/\| ) \ \__| )  \  |
 * \_______)(_______/|/     \|(_______)(_______/(_______/\_______/|/    )_)   )_(   (_______/|/   \__/|/    )_)
 *
 */

$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");

if($wartungsmodus != "") {
    // Admin Wartungsmodus
    $html = file_get_contents("../data/wartungsmodus/index.htm");
    echo(str_replace("{ENDE}", $wartungsmodus, $html));
    exit(0);
}

// Store and Secure $_REQUEST Variables
$_request = [];
if ($_REQUEST) {
    foreach($_REQUEST as $key => $val) {
        $_request[stripslashes(strip_tags(htmlspecialchars($key, ENT_IGNORE, 'utf-8')))] = stripslashes(strip_tags(htmlspecialchars($val, ENT_IGNORE, 'utf-8')));
    }
}

include_once '../data/config/config.php';
include_once '../data/config/userlib.class.php';

include('./startup.php');

include("../framework/lib/system/errorhandler.php");
set_error_handler('schuleinternerrorhandler',E_ALL);

if($_SERVER['SERVER_PORT'] != 443 && $_request['page'] != "updatevplan" && $_request['page'] != "digitalSignage" && !DB::isDebug()) {
    if(isset($_request['ssl']) && $_request['ssl'] == 1) {
        new errorPage('Der Zugriff auf das Portal ist nur über SSL möglich. <br /><br ><br ><br><br><pre>Im Debug Modus ist auch ein Zugriff ohne SSL möglich.</pre>');
        exit();
    }
  header("Location: " . DB::getGlobalSettings()->urlToIndexPHP . "?ssl=1");
}

define("DS", '/');
define("URL_ROOT", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"  );
define("URL_SELF", URL_ROOT.$_SERVER[REQUEST_URI]);

define("PATH_WWW", '.'.DS);
define("PATH_ROOT", PATH_WWW.'..'.DS);
define("PATH_EXTENSIONS", PATH_ROOT.'extensions'.DS);
define("PATH_LIB", PATH_ROOT.'framework'.DS.'lib'.DS);
define("PATH_COMPONENTS", PATH_WWW.'components'.DS);

// echo URL_ROOT.'<br>'.URL_SELF.'<br>';
// echo '<hr>';
// echo PATH_ROOT.'<br>'.PATH_WWW.'<br>'.PATH_EXTENSIONS.'<br>'.PATH_LIB.'<br>'.PATH_COMPONENTS;

if ($_request['admin'] == true) {
    include_once("../framework/lib/system/adminhandler.class.php");
    new adminhandler((isset($_request['page']) && $_request['page'] != "") ? $_request['page'] : 'index', $_request);
} else {
    new requesthandler((isset($_request['page']) && $_request['page'] != "") ? $_request['page'] : 'index', $_request);
}
