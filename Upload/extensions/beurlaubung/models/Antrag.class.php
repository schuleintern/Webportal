<?php

/**
 *
 */
class extBeurlaubungModelAntrag
{


    /**
     * @var data []
     */
    private $data = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
    }

    /**
     * @return boolean
     */
    public static function isVisible()
    {

        return true;
    }


    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID()
    {
        return $this->data['id'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getCreatedTime()
    {
        return $this->data['createdTime'];
    }

    public function getCreatedUserID()
    {
        return $this->data['createdUserID'];
    }

    public function getUserID()
    {
        return $this->data['userID'];
    }

    public function getDatumStart()
    {
        return $this->data['datumStart'];
    }

    public function getDatumEnde()
    {
        return $this->data['datumEnde'];
    }

    public function getStunden()
    {
        return $this->data['stunden'];
    }

    public function getInfo()
    {
        return $this->data['info'];
    }
    public function getDoneInfo()
    {
        return $this->data['doneInfo'];
    }
    public function getDoneDate()
    {
        return $this->data['doneDate'];
    }
    public function getDoneUser()
    {
        return $this->data['doneUser'];
    }

    public function getDoneKL()
    {
        return $this->data['doneKL'];
    }

    public function getDoneKLDate()
    {
        return $this->data['doneKLDate'];
    }

    public function getDoneKLInfo()
    {
        return $this->data['doneKLInfo'];
    }

    public function getDoneSL()
    {
        return $this->data['doneSL'];
    }

    public function getDoneSLDate()
    {
        return $this->data['doneSLDate'];
    }

    public function getDoneSLInfo()
    {
        return $this->data['doneSLInfo'];
    }


    public function getCollection($full = false)
    {

        $dateStart = DateTime::createFromFormat('Y-m-d',$this->getDatumStart());
        $dateEnde = DateTime::createFromFormat('Y-m-d',$this->getDatumEnde());

        $doneDate = DateTime::createFromFormat('Y-m-d H:i:s',$this->getDoneDate());

        $collection = [
            "id" => $this->getID(),
            "status" => $this->getStatus(),
            "createdTime" => date('d.m.Y', $this->getCreatedTime()),
            "createdUserID" => $this->getCreatedUserID(),
            "userID" => $this->getUserID(),
            "datumStart" => $dateStart->format('d.m.Y' ),
            "datumEnde" => $dateEnde->format('d.m.Y' ),
            "stunden" => $this->getStunden(),
            "info" => $this->getInfo(),
            "doneInfo" => $this->getDoneInfo() ? $this->getDoneInfo() : '',
            "doneUser" => $this->getDoneuser(),
            "doneDate" => $doneDate ? $doneDate->format('d.m. H:i') : 0 ,
            "doneKL" => $this->getDoneKL(),
            "doneKLDate" => $this->getDoneKLDate(),
            "doneKLInfo" => $this->getDoneKLInfo(),
            "doneSL" => $this->getDoneSL(),
            "doneSLDate" => $this->getDoneSLDate(),
            "doneSLInfo" => $this->getDoneSLInfo(),
        ];

        if ( $this->getCreatedTime() >= $dateStart  ) {
            $diff = $dateStart->diff( new DateTime(date('Y-m-d', $this->getCreatedTime())) );
            if ($diff) {
                $collection['diff'] = $diff->days;
            }
        }
        


        if ($full) {
            if ( $this->getUserID() ) {
                $temp_user_1 = user::getUserByID($this->getUserID());
                if ($temp_user_1) {
                    $collection['user'] = $temp_user_1->getCollection(true);
                    $collection['username'] = $collection['user']['name'];
                }

            }
            if ( $this->getDoneuser() ) {
                $temp_user_2 = user::getUserByID($this->getDoneuser());
                if ($temp_user_2) {
                    $collection['doneUserUser'] = $temp_user_2->getCollection(true);
                }
            }
        }


        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getByStatus($status = [1])
    {
        if (!$status || !is_array($status)) {
            return false;
        }
        $where = '';
        foreach ($status as $s) {
            if ($where != '') { $where .= " OR "; }
            $where .= " `status` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_beurlaubung_antrag WHERE " .$where." ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;

    }


    /**
     * @return Array[]
     */
    public static function getByUserID($userID = false)
    {
        if (!$userID) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_beurlaubung_antrag WHERE `createdUserID` = " . (int)$userID ." OR `userID` = " . (int)$userID );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByUserIDAndStatus($userID = false, $status = 1)  // 1- offen  2- ja  3- nein
    {
        if (!$userID) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_beurlaubung_antrag WHERE (`createdUserID` = " . (int)$userID ." OR `userID` = " . (int)$userID.") AND `status` = ".(int)$status );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    public static function getGenehmigtNichtVerarbeitete() {

        $sql = DB::getDB()->query("SELECT id AS antragID, userID AS antragUserID, datumStart AS antragDatumStart , datumEnde AS antragDatumEnde,
        stunden AS antragStunden, info AS antragBegruendung, doneInfo AS antragKLKommentar, doneInfo AS antragSLKommentar, userID
        FROM ext_beurlaubung_antrag WHERE status = 2 AND ( doneKL=1 OR doneSL=1) ");
        $allD = [];
        while($b = DB::getDB()->fetch_array($sql)) {
            $b['extension'] = true;
            $b['antragIsVerarbeitet'] = 0;
            $user = user::getUserByID($b['userID']);
            $userCollection =  $user->getCollection(true);
            if ($userCollection['asvid']) {
                $b['antragSchuelerAsvID'] = $userCollection['asvid'];
            }
            $allD[] = new AbsenzBeurlaubungAntrag($b);
        }
        return $allD;
    }


    /**
     * @return Array[]
     */
    public static function setDone($id = false, $status = false, $info = false, $userID = false, $doneInfoIntern = '')
    {
        if (!$id || !$status || !$userID) {
            return false;
        }
        if ($doneInfoIntern == 'undefined') {
            $doneInfoIntern = '';
        }

        $sql = false;
        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        if ($freigabeSL) {
            $schulleitung = schulinfo::getSchulleitungLehrerObjects();
            foreach ($schulleitung as $sl) {
                if ($sl->getUserID() == $userID) {
                    $sql = 'doneSL = 1, doneSLDate = "'.date('Y-m-d H:i', time()).'" , doneSLInfo = "'.DB::getDB()->escapeString($doneInfoIntern).'" ';
                }
            }
        }
        $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");
        if ( $freigabeKL ) {
            $user = DB::getSession()->getUser();
            if ( $user->isTeacher() ) {
                $teacherID = $user->getTeacherObject()->getID();
            }
            if ($teacherID) {
                $klassen = klasseDB::getAll();
                foreach ($klassen as $klasse) {
                    $leitungen = klasse::getKlassenleitungAll($klasse->getKlassenname());
                    foreach ($leitungen as $leitung) {
                        if ($leitung['lehrerID'] == $teacherID) {
                            $sql = 'doneKL = 1, doneKLDate = "'.date('Y-m-d H:i', time()).'" , doneKLInfo = "'.DB::getDB()->escapeString($doneInfoIntern).'" ';
                        }
                    }

                }
            }
        }
        if ( $sql == false ) {
            $sql = 'doneKL = 1, doneKLDate = "'.date('Y-m-d H:i', time()).'" , doneKLInfo = "'.DB::getDB()->escapeString($doneInfoIntern).'" ';
        }



        if (DB::getDB()->query("UPDATE ext_beurlaubung_antrag
                SET status=" . DB::getDB()->escapeString((int)$status) . ",
                doneInfo='" . DB::getDB()->escapeString($info) . "',
                doneUser=" . DB::getDB()->escapeString($userID) . ",
                doneDate = '".date('Y-m-d H:i', time())."'
                , $sql
                WHERE id=".$id
        )) {

            if ($status == 3) { // abgelehnt

                $dataDB = DB::getDB()->query_first("SELECT createdUserID FROM ext_beurlaubung_antrag WHERE id = ".(int)$id );
                if ($dataDB['createdUserID']) {
                    $empfaenger = user::getUserByID($dataDB['createdUserID']);
                    if ($empfaenger) {
                        $messageSender = new MessageSender();
                        $recipientHandler = new RecipientHandler("");
                        $recipientHandler->addRecipient(new UserRecipient($empfaenger));
                        $messageSender->setRecipients($recipientHandler);
                        $messageSender->setSender(new user(['userID' => 0]));
                        $messageSender->setSubject('Ihr Beurlaubungsantrag wurde abgelehnt');
                        $messageSender->setText('Guten Tag ' . $empfaenger->getFirstName().' '.$empfaenger->getLastName().',<br />Ihr Antrag auf Beurlaubung wurde nicht genehmigt.<br />Für Details melden Sie sich bitte im Portal an und überprüfen Sie die Beurlaubung.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>');
                        $messageSender->send();
                    }
                }
                

    
            }
            


            return true;
        }
        return false;
    }


    /**
     * @return Array[]
     */
    public static function setAntrag($userID = false, $schueler = false, $date = false, $stunden = false, $info = '', $status = 1)
    {
        if (!$userID || !$schueler || !$date || !is_array($date) || !$date[0] || !$stunden) {
            return false;
        }

        if (!$date[1]) {
            $date[1] = $date[0];
        }
        if (DB::getDB()->query("INSERT INTO ext_beurlaubung_antrag
            (
                status,
                createdTime,
                createdUserID,
                userID,
                datumStart,
                datumEnde,
                stunden,
                info
            ) values(
            ".(int)$status.",
            " .  time() . ",
            " .  DB::getDB()->escapeString($userID) . ",
            " .  DB::getDB()->escapeString($schueler) . ",
            '" .  DB::getDB()->escapeString($date[0]) . "',
            '" . DB::getDB()->escapeString($date[1]) . "',
            '" . DB::getDB()->escapeString($stunden) . "',
            '" . DB::getDB()->escapeString($info) . "'
            )
                ")) {
            return true;
        }
        return false;

    }


    public static function getFreigabeBy($collection, $user) {

        if ( $user->isTeacher() ) {
            $teacherID = $user->getTeacherObject()->getID();
        }

        $freigabe = false;
        $freigabeSL = DB::getSettings()->getBoolean("extBeurlaubung-schulleitung-freigabe");
        if ($freigabeSL) {
            $schulleitung = schulinfo::getSchulleitungLehrerObjects();
            foreach ($schulleitung as $sl) {
                if ($sl->getUserID() == $user->getUserID()) {
                    $freigabe = true;
                }
            }
        }

        if ($freigabe !== true) {
            $freigabeKL = DB::getSettings()->getBoolean("extBeurlaubung-klassenleitung-freigabe");
            if ($freigabeKL && $teacherID ) {
                $arr = [];
                foreach ($collection as $item) {
                    $klasse = $item['user']['klasse'];
                    $leitungen = klasse::getKlassenleitungAll($klasse);
                    $ok = false;
                    foreach ($leitungen as $leitung) {
                        if ($leitung['lehrerID'] == $teacherID) {
                            $ok = true;
                        }
                    }
                    if ($ok == true) {
                        $arr[] = $item;
                    }
                }
                $ret = $arr;
                $freigabe = true;
            }
        }


        return $freigabe;
    }

}