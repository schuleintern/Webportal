<?php


class klasseDB
{


    private $data = [];


    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getKlassenname()
    {
        return $this->data['klassenname'];
    }

    public function getID()
    {
        return $this->data['id'];
    }


    /**
     * @return klasse[] Klassen
     */
    public static function getAll() {

        $ret = [];
        $db = DB::getDB()->query("SELECT * FROM klassen");
        while ($data = DB::getDB()->fetch_array($db, true)) {
            $ret[] = new self($data);
        }

        return $ret;
    }




    public function getByTeacher($teacher = false) {

        if (!$teacher && $teacher->getXMLID()) {
            return false;
        }

        $ret = [];
        $unterrichtDB = DB::getDB()->query("SELECT unterrichtKlassen FROM unterricht WHERE unterrichtLehrerID = " . (int)$teacher->getXMLID() );
        while ($unterricht = DB::getDB()->fetch_array($unterrichtDB, true)) {
            if ($unterricht['unterrichtKlassen']) {
                $ret[$unterricht['unterrichtKlassen']] = true;
            }
        }
        ksort($ret, SORT_NATURAL);
        return array_keys($ret);
    }

    public function getTeachers() {

        $users = [];

        $where = '';
        if ( $this->getKlassenname() ) {
            $where = 'unterrichtKlassen = "'.$this->getKlassenname().'"';
        }
        //$unterricht = SchuelerUnterricht::getUnterrichtForLehrer($teacher, true);       // Kopplungen ignorieren, da hier nur Klassen gesucht werden.
        $unterrichtDB = DB::getDB()->query("SELECT * FROM unterricht WHERE " . $where);

        //echo "SELECT * FROM unterricht WHERE " . $where;

        while ($unterricht = DB::getDB()->fetch_array($unterrichtDB)) {
            if ($unterricht['unterrichtLehrerID']) {

                $lehrerDB = DB::getDB()->query_first("SELECT lehrerUserID FROM lehrer WHERE lehrerID = ".(int)$unterricht['unterrichtLehrerID']);

                if ($lehrerDB['lehrerUserID']) {
                    $users[] = user::getUserByID((int)$lehrerDB['lehrerUserID']);
                }

            }

        }


        return $users;
    }

}


?>
