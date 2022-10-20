<?php
//ini_set('display_errors', true);
error_reporting(0);

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

if (DB::isDebug()) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}

if($_SERVER['SERVER_PORT'] != 443 && $_request['page'] != "updatevplan" && $_request['page'] != "digitalSignage" && !DB::isDebug()) {
    if(isset($_request['ssl']) && $_request['ssl'] == 1) {
        new errorPage('Der Zugriff auf das Portal ist nur über SSL möglich. <br /><br ><br ><br><br><pre>Im Debug Modus ist auch ein Zugriff ohne SSL möglich.</pre>');
        exit();
    }
  header("Location: " . DB::getGlobalSettings()->urlToIndexPHP . "?ssl=1");
}



new requesthandler((isset($_request['page']) && $_request['page'] != "") ? $_request['page'] : 'index', $_request);

