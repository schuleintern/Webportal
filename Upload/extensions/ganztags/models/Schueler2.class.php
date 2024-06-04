<?php

/**
 *
 */
class extGanztagsModelSchueler2 extends ExtensionModel
{

    static $table = 'ext_ganztags_schueler';

    static $fields = [
        'id',
        'days',
        'info',
        'user_id',
        'anz'
    ];


    static $defaults = [
        'anz' => 0
    ];



    /**
     * @var schueler []
     */
    private $schueler = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'type']);
        self::setModelFields(self::$fields, self::$defaults);
    }





    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($collection['days']) {
            $days_arr = json_decode($collection['days']);
            $collection['days'] = ['mo'=>$days_arr->mo,'di'=>$days_arr->di,'mi'=>$days_arr->mi,'do'=>$days_arr->do,'fr'=>$days_arr->fr,'sa'=>$days_arr->sa,'so'=>$days_arr->so];

            if ($collection['anz']) {
                $days = 0;
                foreach(array_values($collection['days']) as $day) {
                    if ($day) {
                        $days++;
                    }
                }
                $diff = $days - $collection['anz'];
                if ($diff) {
                    $collection['diff'] = $diff;
                }
            }

        }

        if ($full) {

            if ($collection['user_id']) {
                $userCollection = user::getCollectionByID($collection['user_id'], true);
                if ($userCollection) {
                    $collection['vorname'] = $userCollection['vorname'];
                    $collection['nachname'] = $userCollection['nachname'];
                    $collection['klasse'] = $userCollection['klasse'];
                    $collection['gender'] = $userCollection['gender'];
                    $collection['asvid'] = $userCollection['asvid'];
                } else {
                    $collection['vorname'] = ' - ';
                    $collection['nachname'] = ' - Ausgetreten - ';

                }

            }

        }

        return $collection;
    }



    public  function getUnsigned()
    {
        $ret = [];
        $all = $this->getAll();


        $dataDB = DB::run("SELECT *  FROM schueler WHERE schuelerGanztagBetreuung != 0")->fetchAll();

        foreach ($dataDB as $data) {
            $found = false;
            foreach ($all as $item) {
                if ($item->getData('user_id') == (int)$data['schuelerUserID']) {
                    $found = true;
                }
            }
            if ($found === false) {
                $schueler = new schueler($data);
                $ret[] = $schueler;
            }
        }
        return $ret;
    }


    public function setAllUnsigned()
    {

        $all = $this->getUnsigned();

        foreach ($all as $schueler) {

            if ($schueler->getUserID()) {

                DB::run("INSERT INTO ext_ganztags_schueler
                    (user_id, days, info, anz) values(:user_id, :days, :info, :anz );", [
                    'user_id' => $schueler->getUserID(),
                    'days' => '{}',
                    'info' => NULL,
                    'anz' => NULL
                ]);
            }

        }
        return true;

    }





}
