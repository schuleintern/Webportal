<?php

class TagebuchTeacherEntry {

  private $data;

  public function __construct($data) {
    $this->data = $data;
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

  public function getKommentar() {
    return $this->data['entryKommentar'];
  }

  public static function getAllForDateAndTeacher($date, $teacher) {
    $data = DB::getDB()->query("SELECT * FROM klassentagebuch_klasse WHERE entryDate='" . $date . "' AND entryTeacher LIKE '" . $teacher . "'");

    $all = [];

    while($d = DB::getDB()->fetch_array($data)) $all[] = new TagebuchTeacherEntry($d);

    return $all;
  }

}

