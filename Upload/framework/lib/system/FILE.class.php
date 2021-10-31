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
      if ($foo['size']) {
        $foo['formatSize'] =  self::formatBytes($foo['size']);
      }
      if ($foo['mtime']) {
        $foo['formatMtime'] =  date('d.m.Y', $foo['mtime']);
      }
      
      $foo['extension'] = pathinfo($filepath, PATHINFO_EXTENSION);
      $foo['filepath'] = $filepath;
      $foo['filepathAbsolute'] = str_replace("\\",'/',"http://".$_SERVER['HTTP_HOST'].substr(getcwd(),strlen($_SERVER['DOCUMENT_ROOT']))).'/'.$filepath;
      return $foo;
    }
    return false;
  }

  /**
   * 
   * 
   * @author: Christian Marienfeld
   * 
   */

  public static function formatBytes($size, $precision = 2) { 
    $base = log($size, 1024);
    $suffixes = array('', 'kb', 'mb', 'g', 't');   

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];

  } 

  /**
   * 
   * 
   * @author: Christian Marienfeld
   * 
   */

  public function removeFolder($dir) {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dir."/".$object) == "dir") 
            FILE::removeFolder($dir."/".$object); 
          else unlink   ($dir."/".$object);
        }
      }
      reset($objects);
      rmdir($dir);
    }
  }


}




?>