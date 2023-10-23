<?php

/**
 * Globale Page class
 * 
 * @author: Christian Marienfeld
 */

class PAGE
{

  private $logoURL = '';


  public static function exitJsonError($msg = 'Fehler!')
  {
    echo json_encode([
      'error' => true,
      'msg' => $msg
    ]);
    exit;
  }

  public static function exitJsonDone($msg)
  {
    echo json_encode([
      'error' => false,
      'msg' => $msg
    ]);
    exit;
  }


    /**
   * Beeendet die Seite und gibt vorher den Footer aus
   */

   public static function kill($showFooter = true)
   {
 
     if ($showFooter) {
 
       $devTools = Debugger::getDevTools();
 
       $footer = DB::getTPL()->get('footer');
       eval("\$footer =  \"" . DB::getTPL()->get('footer') . "\";");
       echo $footer;
     }
 
     exit(0);
   }




  public static function logo()
  {

    $upload = DB::getSettings()->getUpload('global-logo');
    if ($upload) {
      if ($url = $upload->getThumb('logo', 'logo')) {
        return $url;
      }
    }

    return false;
  }

  public static function logoPrint()
  {

    $upload = DB::getSettings()->getUpload('print-header');
    if ($upload) {
      if ($url = $upload->getThumb('logo', 'print')) {
        return $url;
      }
    }

    return "/cssjs/images/Briefkopf.jpg";
  }



  public static function setFactory($factory)
  {
    $GLOBALS['factory'] = $factory;
  }

  public static function getFactory()
  {
    return $GLOBALS['factory'];
  }


}
