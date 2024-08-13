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


    public function getCollection($full = false, $withCheck = false)
    {
        $collection = parent::getCollection();

        if ($collection['timeStart']) {
            $arr = explode(':', $collection['timeStart']);
            $collection['timeStart'] = $arr[0] . ':' . $arr[1];
        }

        if ($collection['timeEnd']) {
            $arr = explode(':', $collection['timeEnd']);
            $collection['timeEnd'] = $arr[0] . ':' . $arr[1];
        }


        if ($full) {

            if ($collection['kalender_id']) {
                $collection['calenderID'] = $collection['kalender_id'];
            }
            if ($collection['user_id']) {
                $collection['user'] = user::getCollectionByID($collection['user_id']);
            }

        }

        if ($withCheck) {

            if ($collection['teacher']) {
                $teacher = lehrer::getByXMLID($collection['teacher']);
                if ($teacher) {
                    $collection['teacherUser'] = $teacher->getUser()->getCollection();
                }

            }

            if ($collection['art']) {
                include_once PATH_EXTENSIONS.'klassenkalender'.DS . 'models' . DS . 'Lnw.class.php';
                $class = new extKlassenkalenderModelLnws();
                $lnw = $class->getByID($collection['art']);
                $collection['lnw'] = $lnw->getCollection();
            }

            if ($collection['kalender_id']) {
                include_once PATH_EXTENSIONS.'klassenkalender'.DS . 'models' . DS . 'Kalender.class.php';
                $class = new extKlassenkalenderModelKalender();
                $calender = $class->getByID($collection['kalender_id']);
                $collection['kalender'] = [
                    'title' => $calender->getData('title'),
                    'color' => $calender->getData('color')
                ];
            }


            if ($collection['stunde']) {
                $anzStunden = DB::getSettings()->getValue("ext-stundenplan-anzahlstunden");
                if (!$anzStunden) {
                    $anzStunden = 6;
                }
                $stundenZeiten = [];
                for ($i = 1; $i < 6; $i++) {
                    if (DB::getSettings()->getValue("ext-stundenplan-everydayothertimes") > 0 || $i == 1) {
                        for ($s = 1; $s <= $anzStunden; $s++) {
                            $stundenZeiten[] = [
                                'begin' => DB::getSettings()->getValue("ext-stundenplan-stunde-$i-$s-start"),
                                'ende' => DB::getSettings()->getValue("ext-stundenplan-stunde-$i-$s-ende")
                            ];
                        }
                    }
                }
                if ($stundenZeiten[$collection['stunde']]) {
                    $collection['timeStart'] = $stundenZeiten[$collection['stunde']]['begin'];
                    $collection['timeEnd'] = $stundenZeiten[$collection['stunde']]['ende'];
                }
            }







            if (DB::getSession()->getUser()->isAdmin()) {
                return $collection;
            } else {
                $user = DB::getSession()->getUser()->getCollection(true);
                $userType = DB::getSession()->getUser()->getUserTyp(true);
                if ($userType == 'isTeacher') {
                    if ($user['klassen'] && is_array($user['klassen']) && $collection['kalender'] && $collection['kalender']['title'] ) {
                        if ( in_array($collection['kalender']['title'], $user['klassen']) ) {
                            if ($collection['typ'] == 'event') {
                                return $collection;
                            } else if ($collection['typ'] == 'lnw') {
                                return $collection;
                            }
                        }
                    }
                } else if ($userType == 'isPupil' || $userType == 'isEltern') {
                    if ($user['klassen'] && is_array($user['klassen']) && $collection['kalender'] && $collection['kalender']['title'] ) {
                        if ( in_array($collection['kalender']['title'], $user['klassen']) ) {
                            if ($collection['typ'] == 'event') {
                                return $collection;
                            } else if ($collection['typ'] == 'lnw') {
                                if ($collection['lnw'] && $collection['lnw']['isPublic'] == 1) {
                                    return $collection;
                                }
                            }
                        }
                    }
                }
            }
            return false; // nicht ausgeben da keine Rechte

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