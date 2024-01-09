<?php

header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    die();
}

$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");

if ($wartungsmodus != "") {
    // Admin Wartungsmodus
    $html = file_get_contents("../data/wartungsmodus/index.htm");
    echo (str_replace("{ENDE}", $wartungsmodus, $html));
    exit(0);
}

include_once '../data/config/config.php';
include_once '../data/config/userlib.class.php';

include('./startup.php');

if (DB::isDebug()) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}

new resthandler();
