<?php

class TagebuchKlasseEntry {

  private $data;

  public function __construct($data) {
    $this->data = $data;
  }

  public function getID() {
  	return $this->data['entryID'];
  }

  public function getDate() {
    return $this->data['entryDate'];
  }

  public function getGrade() {
    return $this->data['entryGrade'];
  }

  public function getFach() {
    return $this->data['entryFach'];
  }

  public function getStunde() {
    return $this->data['entryStunde'];
  }

  public function getStoff() {
    return $this->data['entryStoff'];
  }

  public function getTeacher() {
    return $this->data['entryTeacher'];
  }

  public function getHausaufgabe() {
  	return $this->data['entryHausaufgabe'];
  }

  public function getNotizen() {
  	return $this->data['entryNotizen'];
  }

  public function isAusfall() {
  	return $this->data['entryIsAusfall'] > 0;
  }

  public function isVertretung() {
  	return $this->data['entryIsVertretung'] > 0;
  }

  public function delete() {
  	DB::getDB()->query("DELETE FROM klassentagebuch_klassen WHERE entryID='" . $this->getID() . "'");
  }

  public function updateIsVertretung($val) {
  	$this->updateField("entryIsVertretung", $val ? 1 : 0);
  }

  public function updateFach($val) {
  	$this->updateField("entryFach", $val);
  }

  public function updateStoff($val) {
  	$this->updateField("entryStoff", $val);
  }

  public function updateHausaufgaben($val) {
  	$this->updateField("entryHausaufgabe", $val);
  }

  public function updateNotizen($val) {
  	$this->updateField("entryNotizen", $val);
  }

  /**
   *
   * @param FileUpload[] $uploads
   */
  public function updatePublicFiles($uploads) {
      $ids = [];

      for($i = 0; $i < sizeof($uploads); $i++) {
          if($uploads[$i] != null) $ids[] = $uploads[$i]->getID();
      }

      $this->updateField("entryFilesPublic", implode(",",$ids));
  }

  /**
   *
   * @param FileUpload[] $uploads
   */
  public function updatePrivateFiles($uploads) {
      $ids = [];

      for($i = 0; $i < sizeof($uploads); $i++) {
          if($uploads[$i] != null) $ids[] = $uploads[$i]->getID();
      }

      $this->updateField("entryFilesPrivate", implode(",",$ids));
  }

  private function updateField($name, $val) {
  	DB::getDB()->query("UPDATE klassentagebuch_klassen SET $name='" . $val . "' WHERE entryID='" . $this->getID() . "'");
  }


  /**
   *
   * @return FileUpload[]
   */
  public function getPublicFiles() {
  	$files = explode(",",$this->data['entryFilesPublic']);

  	$filesObjects = [];

  	for($i = 0; $i < sizeof($files); $i++) {
  		$object = FileUpload::getByID($files[$i]);
  		if($object != null) $filesObjects[] = $object;
  	}

  	return $filesObjects;
  }

  /**
   *
   * @return FileUpload[]
   */
  public function getPrivateFiles() {
  	$files = explode(",",$this->data['entryFilesPrivate']);

  	$filesObjects = [];

  	for($i = 0; $i < sizeof($files); $i++) {
  		$object = FileUpload::getByID($files[$i]);
  		if($object != null) $filesObjects[] = $object;
  	}

  	return $filesObjects;
  }

  public function removePrivateFile($fileID) {
      $files = explode(",",$this->data['entryFilesPrivate']);

      $fileIDs = [];

      for($i = 0; $i < sizeof($files); $i++) {
          if($files[$i] != $fileID) {
              $fileIDs[] = $files[$i];
          }
      }

      $this->updateField("entryFilesPrivate", implode(",",$fileIDs));
  }

  public function removePublicFile($fileID) {
      $files = explode(",",$this->data['entryFilesPublic']);

      $fileIDs = [];

      for($i = 0; $i < sizeof($files); $i++) {
          if($files[$i] != $fileID) {
              $fileIDs[] = $files[$i];
          }
      }

      $this->updateField("entryFilesPublic", implode(",",$fileIDs));
  }


  /**
   *
   */
    public function showEntryNow()
    {

        if (DB::getSettings()->getBoolean('klassentagebuch-view-entries-all-times')) {
            return true;
        }
        // Für den Lehrer selbst immer anzeigen
        if (DB::getSession()->isTeacher() && $this->getTeacher() == DB::getSession()->getTeacherObject()->getKuerzel()) return true;

        // Alte Einträge vor heute anzeigen
        if (DateFunctions::isSQLDateBeforeToday($this->getDate())) {
            return true;
        } else if (DateFunctions::getTodayAsSQLDate() == $this->getDate()) {
            // Heute: Erst Anzeigen, wenn Stunde zu Ende
            if (DB::getSettings()->getBoolean('klassentagebuch-view-entries-begin-day')) {
                return true;
            }
            $stunde = $this->getStunde();
            $tag = DateFunctions::getWeekDayFromSQLDateISO($this->getDate());
            $time = stundenplan::getEndTimeStunde($tag, $stunde);
            if ( !$stunde || !$tag || !$time || $time == '00:00') {
                return false;
            }
            $time = explode(":", $time);
            $timestamp = mktime($time[0] * 1, $time[1] * 1);
            return time() > $timestamp;
        } else {
            // Zukunft
            return false;
        }
    }

  public static function getAllForDateAndGrade($date, $grade) {

    $data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryDate='" . $date . "' AND entryGrade LIKE '" . $grade . "%'");

    $all = [];

    while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);

    return $all;
  }
  
  public static function getAllForDateAndGradeStrict($date, $grade) {
      
      $data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryDate='" . $date . "' AND entryGrade='" . $grade . "'");
      
      $all = [];
      
      while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);
      
      return $all;
  }

  /**
   *
   * @param unknown $date
   * @param String[] $grades
   * @return TagebuchKlasseEntry[]
   */
  public static function getAllForDateAndGrades($date, $grades) {

  	$allEntries = [];

  	for($i = 0; $i < sizeof($grades); $i++) {
  		$allEntries = array_merge($allEntries,self::getAllForDateAndGrade($date, $grades[$i]));
  	}

  	return $allEntries;
  }

  public static function getAllForTeacherAndStunde($date, $stunde, $lehrer) {
  	$data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryDate='" . $date . "' AND entryTeacher LIKE '" . DB::getDB()->escapeString($lehrer) . "' AND entryStunde='$stunde'");

  	$all = [];

  	while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);


  	return $all;
  }

    /**
     * @param $teacher
     * @return TagebuchKlasseEntry[]
     */
  public static function getAllForTeacher($teacher) {
      $data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryTeacher LIKE '" . $teacher . "' ORDER BY entryDate ASC, entryGrade ASC, entryStunde ASC");

      $all = [];

      while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);

      return $all;
  }

  public static function getAllForDateAndTeacher($date, $teacher) {
      $data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryDate='" . $date . "' AND entryTeacher LIKE '" . $teacher . "'");

      $all = [];

      while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);

      return $all;
  }

  /**
   *
   * @param String $grade
   * @param String $subject
   */
  public static function getAllForGradeAndSubject($grade, $subject) {
  	$data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryFach LIKE '$subject' AND entryGrade LIKE '$grade' ORDER BY entryDate DESC, entryStunde DESC");

  	$all = [];

  	while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);


  	return $all;
  }

  /**
   *
   * @param unknown $grade
   * @param unknown $date
   * @param unknown $stunden
   * @param unknown $fach
   * @param unknown $stoff
   * @param unknown $teacher
   * @param unknown $hausaufgaben
   * @param unknown $isAusfall
   * @param unknown $isVertretung
   * @param unknown $notizen
   * @param int[] $dateiIDsPublic
   * @param int[] $dateiIDsPrivate
   * @return boolean
   */
  public static function createEntry($grade,$date,$stunden,$fach,$stoff,$teacher,$hausaufgaben, $isAusfall, $isVertretung, $notizen, $dateiIDsPublic, $dateiIDsPrivate) {

  	for($i = 0; $i < sizeof($stunden); $i++) {
  	    
  	    DB::getDB()->query("DELETE FROM klassentagebuch_fehl WHERE fehlKlasse='" . DB::getDB()->escapeString($grade) . "' AND fehlDatum='" . DB::getDB()->escapeString($date) . "' AND fehlFach='" . DB::getDB()->escapeString($fach) . "' AND fehlStunde='" . $stunden[$i] . "'");

	  	DB::getDB()->query("INSERT INTO klassentagebuch_klassen
	  			(
	  			entryGrade,
	  			entryDate,
	  			entryStunde,
	  			entryFach,
	  			entryStoff,
	  			entryTeacher,
	  			entryHausaufgabe,
	  			entryIsAusfall,
	  			entryIsVertretung,
				entryNotizen,
				entryFilesPrivate,
				entryFilesPublic
	  			)
	  			values(
	  			'" . DB::getDB()->escapeString($grade) . "',
	  			'" . DB::getDB()->escapeString($date) . "',
	  			'" . DB::getDB()->escapeString($stunden[$i]) . "',
	  			'" . DB::getDB()->escapeString($fach) . "',
	  			'" . DB::getDB()->escapeString($stoff) . "',
	  			'" . DB::getDB()->escapeString($teacher) . "',
	  			'" . DB::getDB()->escapeString($hausaufgaben) . "',
	  			'" . ($isAusfall ? '1' : '0') . "',
	  			'" . ($isVertretung ? '1' : '0') . "',
				'" . DB::getDB()->escapeString($notizen) . "',
				'" . implode(",",$dateiIDsPrivate) . "',
				'" . implode(",",$dateiIDsPublic) . "'
	  			)

	  	");
  	}

  	return true;
  }

  /**
   *
   * @param unknown $id
   * @return TagebuchKlasseEntry|NULL
   */
  public static function getEntryByID($id) {
  	$entry = DB::getDB()->query_first("SELECT * FROM klassentagebuch_klassen WHERE entryID='" . intval($id) . "'");
  	if($entry['entryID'] > 0) return new TagebuchKlasseEntry($entry);
  	else return null;
  }
  
  
  public static function getAllForGrade($grade) {
      $data = DB::getDB()->query("SELECT * FROM klassentagebuch_klassen WHERE entryGrade='" . $grade . "' ORDER BY entryGrade, entryDate DESC, entryStunde DESC");
      
      $all = [];
      
      while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchKlasseEntry($d);
      
      
      return $all;
  }


}

