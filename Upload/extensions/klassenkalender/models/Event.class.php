<?php

/**
 *
 */
class extKlassenkalenderModelEvent extends ExtensionModel
{


    static $table = 'ext_klassenkalender_events';

    static $fields = [
        'id',
        'createdTime',
        'user_id',
        'status',
        'kalender_id',
        'title',
        'dateStart',
        'timeStart',
        'dateEnd',
        'timeEnd',
        'place',
        'comment',
        'modifiedTime',
        'repeat_type',
        'stunde',
        'typ',
        'art',
        'fach',
        'teacher',
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'kalender_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $withAdmins = false)
    {
        $collection = parent::getCollection();

        if ($full) {


            if ($collection['kalender_id']) {
                $collection['calenderID'] = $collection['kalender_id'];
            }
            if ($collection['user_id']) {
                $collection['user'] = user::getCollectionByID($collection['user_id']);
            }

            if ($collection['timeStart']) {
                $arr = explode(':', $collection['timeStart']);
                $collection['timeStart'] = $arr[0].':'.$arr[1];
            }

            if ($collection['timeEnd']) {
                $arr = explode(':', $collection['timeEnd']);
                $collection['timeEnd'] = $arr[0].':'.$arr[1];
            }


        }


        return $collection;
    }


    public function add($data = false)
    {

        if (!$data) {
            return false;
        }

        if ($this->save($data)) {
            return true;
        }
        return false;

    }

    public static function getDayByKalender($date = false, $kalenderIDs = false) {

        if (!$date) {
            return false;
        }
        if (!$kalenderIDs) {
            return false;
        }

        $where = [];
        foreach($kalenderIDs as $k_id) {
            $where[] = ' kalender_id = '.(int)$k_id['id'];
        }
        $where = implode(' OR ',$where);

        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  a.*
            FROM ext_klassenkalender_events as a
            WHERE (
                ( DATE '".$date."' = dateStart AND dateEnd IS NULL )
                OR
                (  dateStart <= DATE '".$date."' AND DATE '".$date."' <= dateEnd ) 
            ) AND (".$where.") AND ( status = 1 )
            ORDER BY a.dateStart ");


        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;

    }



}