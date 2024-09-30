<?php

/**
 * Globale EXTENSION class
 * 
 * @author: Christian Marienfeld
 */

class EXTENSION
{


  public static function isActive($uniqid = false, $return = false) {


    $data = DB::run( 'SELECT * FROM extensions WHERE uniqid = :uniqid AND active = 1', ["uniqid" => (string)$uniqid] )->fetch();

    if ($data) {
        if ($return) {
            $data['json'] = self::getJSON(PATH_EXTENSIONS.$data['folder'].DS.'extension.json');
            return $data;
        }
        return true;
    }
    return false;

  }
  
  /**
   * @author: Christian Marienfeld
   *
   * get Extension JSON
   *
   */
  public static function getJSON($path = false)
  {

    if (!$path) {
      if (!PATH_EXTENSION || PATH_EXTENSION == 'PATH_EXTENSION') {
        return false;
      }
      $path = PATH_EXTENSION . DS . 'extension.json';
    }
    if (file_exists($path)) {
      $file = file_get_contents($path);
      $json = (array)json_decode($file);
      if ($json) {
        return $json;
      }
    }
    return false;
  }


}
