<?php 

// Composer Autoloader laden --> Läd auch den SchuleIntern Autoloader

include_once("../framework/lib/composer/vendor/autoload.php");
include_once("../framework/lib/system/schuleinternautoloader.php");


spl_autoload_register("schuleinternautoloader");

