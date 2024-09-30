<?php

/**
 *
 */
class extBeurlaubungModelAntrag extends ExtensionModel
{


    static $table = 'ext_beurlaubung_antrag';

    static $fields = [
        'id',
        'status',
        'createdTime',
        'createdUserID',
        'userID',
        'datumStart',
        'datumEnde',
        'stunden',
        'info',
        'doneInfo',
        'doneUser',
        'doneDate',
        'doneKL',
        'doneKLDate',
        'doneKLInfo',
        'doneSL',
        'doneSLDate',
        'doneSLInfo',
    ];


    static $defaults = [

    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false);
        self::setModelFields(self::$fields, self::$defaults);
    }




    public function getCollection($full = false)
    {

        $dateStart = DateTime::createFromFormat('Y-m-d',$this->getData('datumStart'));
        $dateEnde = DateTime::createFromFormat('Y-m-d',$this->getData('datumEnde'));

        $doneDate = DateTime::createFromFormat('Y-m-d H:i',$this->getData('doneDate'));

        $collection = [
            "id" => $this->getID(),
            "status" => $this->getData('status'),
            "createdTime" => date('d.m.Y H:i', $this->getData('createdTime')),
            "createdUserID" => $this->getData('createdUserID'),
            "userID" => $this->getData('userID'),
            "datumStart" => $dateStart->format('d.m.Y' ),
            "datumEnde" => $dateEnde->format('d.m.Y' ),
            "stunden" => $this->getData('stunden'),
            "info" => $this->getData('info'),
            "doneInfo" => $this->getData('doneInfo') ? $this->getData('doneInfo') : '',
            "doneUser" => $this->getData('doneUser'),
            "doneDate" => $doneDate ? $doneDate->format('d.m. H:i') : 0 ,
            "doneKL" => $this->getData('doneKL'),
            "doneKLDate" => $this->getData('doneKLDate'),
            "doneKLInfo" => $this->getData('doneKLInfo'),
            "doneSL" => $this->getData('doneSL'),
            "doneSLDate" => $this->getData('doneSLDate'),
            "doneSLInfo" => $this->getData('doneSLInfo'),
        ];

        if ( $this->getCreatedTime() >= $dateStart  ) {
            $diff = $dateStart->diff( new DateTime(date('Y-m-d', $this->getCreatedTime())) );
            if ($diff) {
                $collection['diff'] = $diff->days;
            }
        }



        if ($full) {
            if ( $this->getData('userID') ) {
                $temp_user_1 = user::getUserByID((int)$this->getData('userID'));
                if ($temp_user_1) {
                    $collection['user'] = $temp_user_1->getCollection(true);
                    $collection['username'] = $collection['user']['name'];
                }

            }
            if ( $this->getData('doneUser') ) {
                $temp_user_2 = user::getUserByID((int)$this->getData('doneUser'));
                if ($temp_user_2) {
                    $collection['doneUserUser'] = $temp_user_2->getCollection(true);
                }
            }
        }


        return $collection;
    }




    public  function getByStatus($status = [1])
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


    public  function getByUserID($userID = false)
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


    public  function getByUserIDAndStatus($userID = false, $status = 1)  // 1- offen  2- ja  3- nein
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


    public  function setDone($id = false, $status = false, $info = false, $userID = false, $doneInfoIntern = '')
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
                $classKlasse = new klasse();
                foreach ($klassen as $klasse) {
                    $leitungen = $classKlasse->getKlassenleitungAll($klasse->getKlassenname());
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

                        if (EXTENSION::isActive('ext.zwiebelgasse.inbox')) {

                            include_once PATH_EXTENSIONS. 'inbox' .DS . 'models' . DS . 'Inbox2.class.php';
                            $Inbox = new extInboxModelInbox2();
                            $inbox = $Inbox->getByUserIDFirst($dataDB['createdUserID']);
                            include_once PATH_EXTENSIONS. 'inbox' .DS . 'models' . DS . 'Message2.class.php';
                            $class = new extInboxModelMessage2();
                            if (!$class->sendMessage([
                                'receiver' => '[{"typ":"user","content":"' . $dataDB['createdUserID'] . '","inboxs":["' . $inbox->getID() . '"]}]',
                                'sender_id' => 1,
                                'subject' => 'Ihr Beurlaubungsantrag wurde abgelehnt',
                                'text' => 'Guten Tag ' . $empfaenger->getFirstName().' '.$empfaenger->getLastName().',<br />Ihr Antrag auf Beurlaubung wurde nicht genehmigt.<br />Für Details melden Sie sich bitte im Portal an und überprüfen Sie die Beurlaubung.<br /><br /><i>Dies ist eine automatisch versendete Nachricht.</i>',
                                'noAnswer' => true
                            ])) {
                                return false;
                            }
                            return true;

                        } else {

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



            }



            return true;
        }
        return false;
    }


    public  function setAntrag($userID = false, $schueler = false, $date = false, $stunden = false, $info = '', $status = 1)
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


    public  function getFreigabeBy($collection, $user) {

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
                $classKlasse = new klasse();
                foreach ($collection as $item) {
                    $klasse = $item['user']['klasse'];
                    $leitungen = $classKlasse->getKlassenleitungAll($klasse);
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