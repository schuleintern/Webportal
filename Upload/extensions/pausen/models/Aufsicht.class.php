<?php

class extPausenModelAufsicht extends ExtensionModel
{

    static $table = 'ext_pausen_aufsicht';

    static $fields = [
        'id',
        'state',
        'createdTime',
        'createdUserID',
        'user_id',
        'second_id',
        'pausen_id',
        'day'
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

    public function getToday()
    {
        $day = date('w');
        $data = DB::run("SELECT * FROM " . $this->getModelTable() . " WHERE day = :day ", ['day' => $day])->fetchAll();

        if ($data) {
            $ret = [];
            foreach ($data as $item) {
                $ret[] = new self($item);
            }
            return $ret;
        }
        return false;
    }

    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($collection['day']) {
                $day = '';
                switch ($collection['day']) {
                    case 1:
                        $day = 'Montag';
                        break;
                    case 2:
                        $day = 'Dienstag';
                        break;
                    case 3:
                        $day = 'Mittwoch';
                        break;
                    case 4:
                        $day = 'Donnerstag';
                        break;
                    case 5:
                        $day = 'Freitag';
                        break;
                }
                $collection['dayTitle'] = $day;
            }
            if ($collection['user_id']) {
                $collection['user'] = user::getCollectionByID($collection['user_id']);
                $collection['userName'] = $collection['user']['name'];
            }
            if ($collection['second_id']) {
                $collection['second'] = user::getCollectionByID($collection['second_id']);
                $collection['secondName'] = $collection['second']['name'];
            }
            if ($collection['pausen_id']) {
                include_once PATH_EXTENSIONS.'pausen'.DS . 'models' . DS .'Pausen.class.php';
                $Pause = new extPausenModelPausen();
                $pause = $Pause->getByID($collection['pausen_id']);
                if ($pause) {
                    $collection['pause'] = $pause->getCollection();
                }

            }
        }

        return $collection;
    }

}
