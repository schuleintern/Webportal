<?php

/**
 * Globale Filesystem class
 * 
 * @author: Christian Marienfeld
 */

class FILE
{

  
  public static function makeThumb($file = false, $newFilename = false, $folder = false)
  {

    if (!$file || !$newFilename) {
      return false;
    }
    if (!file_exists($file)) {
      return false;
    }


    $path = PATH_WWW_TMP;
    if ($folder) {
      $path = $path.$folder.DS;
    }
    if (!is_dir($path)) {
      mkdir($path);
    }

    $stats = self::getFileInfo($file);

    $new = $path.$newFilename.'.'.$stats['extension'];

    if (file_exists($new)) {
      return $new;
    }

    copy($file, $new);

    if (file_exists($new)) {
      return $new;
    }
    return false;
  }
  

  public static function getFile($path, $filename = false)
  {

    if (!$path) {
      return false;
    }
      if (!$filename) {
          $filename = $path;
      }

    if (!file_exists($path)) {
      return false;
    }


    header('Content-Description: Dateidownload');
    header('Content-Type: ' . mime_content_type($path));
    header('Content-Disposition: inline; filename="' . $filename . '"');
    //header('Expires: 0');
    //header('Cache-Control: must-revalidate');
    //header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    //header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60))); // 1 hour
    //header('Cache-Control: no-cache');
    header("Content-Transfer-Encoding: chunked");

    ob_clean();
    flush();

    $fp = fopen($path, 'rb');        // READ / BINARY

    fpassthru($fp);
  }

  /**
   * Get JavaScript Scripts Files
   *
   * @param page String
   * @param scripts Array
   */
  public static function getScripts($scripts)
  {
    if (!$scripts || count($scripts) <= 0) {
      return false;
    }
    $html = '';
    foreach ($scripts as $script) {
      $script = trim($script);
      if (file_exists($script)) {
        $file = file_get_contents($script);
        if ($file) {
          $html .= '<script>' . $file . '</script>';
        }
      }
    }
    return $html;
  }

  public static function getScript($script)
  {
    if (!$script) {
      return false;
    }
    $script = trim($script);
    if (file_exists($script)) {
      $file = file_get_contents($script);
      if ($file) {
        return $file;
      }
    }

    return false;
  }

  /**
   * @author: Christian Marienfeld
   *
   * get Extension JSON
   *
   */
  public static function getExtensionJSON($path = false)
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


  /**
   * 
   * 
   * @author: Christian Marienfeld
   * 
   */

  public static function getFilesInFolder($folder, $showStats = false, $filterExt = false)
  {

    if ($folder) {

      $files = array_diff(scandir($folder), array('..', '.'));

      if (count($files) < 1) {
        return false;
      }

      if ($filterExt) {
        $filter = [];
        foreach ($files as $file) {
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
      foreach ($files as $file) {
        $foo = array(
          'filename' => $file
        );
        if ($showStats) {
          $stats = FILE::getFileInfo($folder . '/' . $file);
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

  public static function getFileInfo($filepath)
  {

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
      $foo['filepathAbsolute'] = str_replace("\\", '/', "http://" . $_SERVER['HTTP_HOST'] . substr(getcwd(), strlen($_SERVER['DOCUMENT_ROOT']))) . '/' . $filepath;
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

  public static function formatBytes($size, $precision = 2)
  {
    $base = log($size, 1024);
    $suffixes = array('', 'kb', 'mb', 'g', 't');

    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
  }

  /**
   * 
   * 
   * @author: Christian Marienfeld
   * 
   */

  public static function removeFolder($dir)
  {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dir . "/" . $object) == "dir")
            FILE::removeFolder($dir . "/" . $object);
          else unlink($dir . "/" . $object);
        }
      }
      reset($objects);
      rmdir($dir);
      return true;
    }
    return false;
  }
}
