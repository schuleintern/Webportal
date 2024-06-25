<?php

$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");
if($wartungsmodus != "") {
    // Admin Wartungsmodus
    $html = file_get_contents("../data/wartungsmodus/index.htm");
    echo(str_replace("{ENDE}", $wartungsmodus, $html));
    exit(0);
}

include_once '../data/config/config.php';
include('./startup.php');

if (DB::isDebug()) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}


if ($_POST["sub"] && $_POST['uid']) {

    if ( PUSH::subscribe((string)$_POST['sub'], (int)$_POST['uid']) ) {
        //echo true;
        //PUSH::send($_POST['uid']);
    }
    //echo false;

}

