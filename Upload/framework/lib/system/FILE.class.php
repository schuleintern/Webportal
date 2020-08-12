<?php

/**
 * Globale Filesystem class
 * 
 * @author: Christian Marienfeld
 */

class FILE {


  /**
   * 
   * 
   * @author: Christian Marienfeld
   * 
   */

	public static function getFilesInFolder($folder, $showStats = false, $filterExt = false) {

    if ($folder) {
      
      $files = array_diff(scandir($folder), array('..', '.'));

      if ( count($files) < 1) {
        return false;
      }

      if ($filterExt) {
        $filter = [];
        foreach($files as $file) {
          $extension = pathinfo($file, PATHINFO_EXTENSION);
          if ($extension === $filterExt) {
            $filter[] = $file;
          }
        }
        $files = $filter;
      }

      if ($showStats) {
        
      }

      $temp = [];
      foreach($files as $file) {
        $foo = array(
          'filename' => $file
        );
        if ($showStats) {
          $stats = FILE::getFileInfo($folder.'/'.$file);
          $foo = array_merge($foo, $stats);
        }
        $temp[] = $foo;
      }
      $files = $temp;


      return $files;
      

    }
    return false;
	}


  /**
   * 
   * 
   * @author: Christian Marienfeld
   * 
   */

  public static function getFileInfo($filepath) {
    
    if ($filepath) {
      $foo = stat($filepath);
      $foo['extension'] = pathinfo($filepath, PATHINFO_EXTENSION);
      return $foo;
    }
    return false;
  }



}




?>