<?php

/**
 * Globale Page class
 * 
 * @author: Christian Marienfeld
 */

class PAGE {


  /**
   * Beeendet die Seite und gibt vorher den Footer aus
   * 
   * @author: Christian Marienfeld
   * 
   */

	public static function kill($showFooter = true) {

    if ($showFooter) {

      $devTools = Debugger::getDevTools();
       
      $footer = DB::getTPL()->get ( 'footer' );
      eval ( "\$footer =  \"" . DB::getTPL ()->get ( 'footer' ) . "\";" );
      echo $footer;
      
    }

    exit(0);
	}




}




?>