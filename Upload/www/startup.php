<?php


include("../framework/lib/system/errorhandler.php");

set_error_handler('schuleinternerrorhandler',E_ALL);

include("../framework/lib/system/autoloader.php");

// Datenbank verbinden
DB::start();
session::cleanSessions();

// Garbage Collection durchführen
GarbageCollector::EveryRequest();


// if(DB::isloggedin() && (!isset($_GET['page']) || $_GET['page'] == "")) $_GET['page'] = "vplan";

?>
