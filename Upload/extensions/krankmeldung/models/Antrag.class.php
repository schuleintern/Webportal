<?php

/**
 *
 */
class extKrankmeldungModelAntrag extends ExtensionModel
{


    static $table = 'ext_krankmeldung_antrag';

    static $fields = [
        'id',
        'state',
        'createdTime',
        'createdUserID',
        'user_id',
        'asv_id',
        'dateStart',
        'dateEnd',
        'days',
        'info',
        'absenzID'
    ];


    static $defaults = [
        'createdTime' => '0000-00-00',
        'state' => 0,
        'asv_id' => 0,
        'absenzID' => 0,
        'days' => 0
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'absenzID']);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false)
    {
        $collection = parent::getCollection();

        if ($full) {

            if ($collection['user_id']) {
                $collection['user'] = user::getCollectionByID($collection['user_id'], true, true);
                $collection['userName'] = $collection['user']['name'];
            }

            if ($collection['createdUserID']) {
                $collection['createdUserUser'] = user::getCollectionByID($collection['createdUserID']);
            }

            if ($collection['dateStart']) {
                $collection['dateStartDate'] = DateFunctions::getNaturalDateFromMySQLDate($collection['dateStart']);
            }
            if ($collection['dateEnd']) {
                $collection['dateEndDate'] = DateFunctions::getNaturalDateFromMySQLDate($collection['dateEnd']);
            }
            if ($collection['createdTime']) {
                $collection['createdTime'] = DateFunctions::getNaturalDateTimeFromMySQLDate($collection['createdTime']);
            }

        }


        return $collection;
    }


    public function getByDate($date = false)
    {

        if (!$this->getModelTable()) {
            return false;
        }
        if (!$date) {
            return false;
        }
        $ret = [];

        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE dateStart <= :dateStart AND dateEnd >= :dateEnd ', ['dateStart' => $date, 'dateEnd' => $date])->fetchAll();
        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;

    }

    public function getByCreatedUserID($userID = false)
    {

        if (!$this->getModelTable()) {
            return false;
        }
        if (!$userID) {
            return false;
        }
        $ret = [];

        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE createdUserID = :userID', ['userID' => $userID])->fetchAll();
        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;

    }

    public function getUntouched()
    {

        if (!$this->getModelTable()) {
            return false;
        }

        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE state = 0 && absenzID = 0')->fetchAll();
        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;

    }


    public function add($userID = false, $user = false, $dateStart = false, $dateEnd = false, $dateAdd = false, $info = '')
    {

        if (!$userID || !$user || !$dateStart || !$dateEnd || !$dateAdd) {
            return false;
        }

        $asv_id = 0;
        $userObj = user::getUserByID($user);
        if ($userObj->isPupil()) {
            $asv_id = $userObj->getPupilObject()->getAsvID();
        }
        $data = [
            'user_id' => $user,
            'asv_id' => $asv_id,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'info' => $info,
            'createdTime' => date('Y-m-d H:i', time()),
            'createdUserID' => $userID,
            'days' => $dateAdd
        ];

        if ($this->save($data)) {
            return true;
        }
        return false;

    }

}