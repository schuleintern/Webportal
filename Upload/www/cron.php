<?php


$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");

if($wartungsmodus != "") {
    // Admin Wartungsmodus
    $html = file_get_contents("../data/wartungsmodus/index.htm");
    echo(str_replace("{ENDE}", $wartungsmodus, $html));
    exit(0);
}

include_once '../data/config/config.php';
include_once '../data/config/userlib.class.php';

include('./startup.php');

error_reporting(E_ALL);

new cronhandler();
