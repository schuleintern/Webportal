<?php

/**
 *
 */
class extFinanzenModelAntrag extends ExtensionModel
{

    static $table = 'ext_finanzen_antrag';

    static $fields = [
        'title',
        'payee',
        'users',
        'amount',
        'dueDate',
        'receipt',
        'createdTime',
        'createdUserID',
        'state'
    ];

    
    static $defaults = [
        'state' => 1
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

        $collection = parent::getCollection();

        if ($collection['amount']) {
            $collection['amount'] = str_replace('.',',',$collection['amount']);
        }
        if ($collection['createdTime']) {
            $t = explode(' ', $collection['createdTime']);
            $t = explode('-', $t[0]);
            $collection['createdTime'] = $t[2].'.'.$t[1].'.'.$t[0];
        }
        if ($collection['dueDate']) {
            $t = explode('-', $collection['dueDate']);
            $collection['dueDate'] = $t[2].'.'.$t[1].'.'.$t[0];
        }

        if ($full) {
            if ($this->getData('createdUserID')) {
                $collection['createdUser'] = user::getCollectionByID($this->getData('createdUserID'));
                if ($collection['createdUser']) {
                    $collection['createdUserName'] = $collection['createdUser']['name'];
                }
            }
            if ($this->getData('user_id')) {
                $collection['user'] = user::getCollectionByID($this->getData('user_id'));
                if ($collection['user']) {
                    $collection['userName'] = $collection['user']['name'];
                }
            }

            if ($this->getData('users')) {
                $collection['userlist'] = [];
                $users = explode(',', $this->getData('users'));
                if ($users && count($users) >= 1) {
                    include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Buchung.class.php';
                    $Buchung = new extFinanzenModelBuchung();

                    $userAnz = count($users);
                    $buchungAnz = 0;
                    foreach ($users as $u) {
                        $userCollection = user::getCollectionByID($u);
                        $buchung = $Buchung->getStatausByAntragAndUser($this->getID(), $u);
                        if ($buchung) {
                            $userCollection['buchung_state'] = $buchung;
                            if ($buchung == 2) { // State ist bezahlt
                                $buchungAnz++;
                            }
                        }
                        $collection['userlist'][] = $userCollection;
                    }

                }
                $collection['buchnungDone'] = 0;
                if ($userAnz > 0 && $buchungAnz > 0) {
                    $collection['buchnungDone'] = (int)($buchungAnz/$userAnz *100);
                }
            }

        }

        return $collection;
    }



    public static function getMyByStatus($status = [1])
    {
        if (!$status || !is_array($status)) {
            return false;
        }
        $createdUserID = DB::getSession()->getUserID();
        if (!$createdUserID) {
            return false;
        }
        $where = '';
        foreach ($status as $s) {
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " `state` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_finanzen_antrag WHERE (" . $where . ") AND createdUserID = ".$createdUserID." ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
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
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " `state` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_finanzen_antrag WHERE " . $where . " ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }




    /**
     * @return Array[]
     */
    public function changeState($status = false)
    {
        $status = (int)$status;
        if (!$status) {
            return false;
        }

        if ( parent::setState($status) ) {

            if ($status == 2) { // freigegeben

                if ( !$this->toBuchungen() ) {
                    return false;
                }
                
                
                
            }

            return true;
        }
        return false;
    }



    public function toBuchungen()
    {

        //include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Buchung.class.php';

        $users = $this->getData('users');

        $users = explode(',', (string)$users);
        if ($users && count($users) >= 1) {
            foreach ($users as $userID) {
                if ( !$this->setAntragToBuchung($userID)) {
                    return false;
                }
            }
        }
        return true;
    }


    public function setAntragToBuchung($userID = false)
    {

        if (!$userID || !$this->getID()) {
            return false;
        }

        $myUserID = DB::getSession()->getUser()->getUserID();

        include_once PATH_EXTENSIONS . 'finanzen' . DS . 'models' . DS . 'Buchung.class.php';


        $data =  [
            'orderNr' => extFinanzenModelBuchung::getOrderNrPrefix().$this->getID().'-'.$userID,
            'antrag_id' => $this->getID(),
            'user_id' => $userID,
            'title' => $this->getData('title'),
            'amount' => $this->getData('amount'),
            'createdTime' => date( 'Y-m-d H:i:s', time() ),
            'createdUserID' => $myUserID
        ];

        $class = new extFinanzenModelBuchung();

        if ( $class->save($data) ) {
            return true;
        }
        return false;
    }


}
