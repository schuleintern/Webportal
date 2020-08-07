<?php 

// Composer Autoloader laden --> Läd auch den SchuleIntern Autoloader

include_once("../framework/lib/composer/vendor/autoload.php");
include_once("../framework/lib/system/schuleinternautoloader.php");


spl_autoload_register("schuleinternautoloader");


/** 
*  Global DUMP function
*
*  @autor: Christian Marienfeld
*/

if (!function_exists('dump')) {

  function dump($args) {
      $args = func_get_args();
      Debugger::debugObject($args);
  }

}

