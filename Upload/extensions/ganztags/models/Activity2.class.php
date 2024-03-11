<?php

/**
 *
 */
class extGanztagsModelActivity2 extends ExtensionModel
{

    static $table = 'ext_ganztags_groups';

    static $fields = [
        'id',
        'type',
        'title',
        'leader_id',
        'days',
        'room',
        'info',
        'color',
        'duration'
    ];


    static $defaults = [
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
            $collection['days'] = json_decode($collection['days']);
        }

        if ($full) {
            if ($collection['leader_id']) {
                include_once 'Leaders2.class.php';
                $Leaders = new extGanztagsModelLeaders2();
                $leader = $Leaders->getByID($collection['leader_id']);
                if ($leader) {
                    $collection['leader'] = $leader->getCollection();
                }
                //$collection['leader'] = user::getCollectionByID($collection['leader_id']);
            }



            if ($this->schueler && count($this->schueler) > 0 ) {
                foreach ($this->schueler as $schueler) {
                    if ($schueler) {

                            $collection['schueler'][] = $schueler->getCollection();

                    }
                }
            }

        }


        return $collection;
    }


    public function getSchueler()
    {

        include_once 'Schueler2.class.php';
        $class = new extGanztagsModelSchueler2();
        $dataDB = $class->getAll();

        $this->schueler = [];
        foreach($dataDB as $data) {
            if ($data->getData('days')) {
                $days = json_decode($data->getData('days'));

                foreach ($days as $day) {

                    if ( $day->group && (int)$this->getID() &&
                        (int)$day->group == (int)$this->getID() ) {
                        //$foo = new extGanztagsModelSchueler2($data);
                        $this->schueler[] = $data;
                    }
                }

            }

        }
    /*
        //$str = '"group":"'.$this->getID().'"';
        $this->schueler[] = [];
        //$data = DB::run("SELECT * FROM ext_ganztags_schueler WHERE days LIKE '%".$str."%'  ")->fetchAll();
        foreach ($dataDB as $item) {
            $this->schueler[] = new extGanztagsModelSchueler2($item);
        }
        */
        return true;
    }

    public function getWeekSchueler()
    {
        if (!$this->getID()) {
            return false;
        }

        include_once 'Schueler2.class.php';


        $this->week = ['mo' => [], 'di' => [], 'mi' => [], 'do' => [], 'fr' => [], 'sa' => [], 'so' => []];
        //$this->week = ['mo' => 0, 'di' => 0, 'mi' => 0, 'do' => 0, 'fr' => 0, 'sa' => 0, 'so' => 0];

        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_schueler");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {

            if ($data['days']) {
                $days = json_decode($data['days']);

                foreach ($this->week as $day => $week) {
                    if ($days->{$day} && $days->{$day}->group == $this->getID()) {
                        $this->week[$day] = new extGanztagsModelSchueler2($data, user::getUserByID($data['user_id']));
                    }
                }
            }
        }

        return $this;
    }


    /*
    public function getSchueler()
    {
        include_once 'Schueler.class.php';

        $str = '"group":"'.$this->getID().'"';
        $this->schueler[] = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_schueler WHERE days LIKE '%$str%' ");

        echo '<pre>';
        print_r($dataSQL);
        echo '</pre>';
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {

            echo '<pre>';
            print_r($data);
            echo '</pre>';
            $this->schueler[] = new extGanztagsModelSchueler($data, user::getUserByID($data['user_id']));
        }
        return $this;
    }
    */






}
