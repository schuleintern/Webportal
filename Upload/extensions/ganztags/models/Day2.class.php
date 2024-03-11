<?php


class extGanztagsModelDay2 extends ExtensionModel
{

    static $table = 'ext_ganztags_day';

    static $fields = [
        'id',
        'date',
        'type',
        'title',
        'info',
        'room',
        'color',
        'leader_id',
        'group_id',
        'duration',
        'createdBy',
        'createdTime'
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
        parent::__construct($data, self::$table ? self::$table : false);
        self::setModelFields(self::$fields, self::$defaults);
    }





    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($collection['type']) {
            $collection['type'] = 'day-'.$collection['type'];
        }
        if ($full) {
            if ($collection['leader_id']) {
                include_once 'Leaders2.class.php';
                $Leaders = new extGanztagsModelLeaders2();
                $leader = $Leaders->getByID($collection['leader_id']);
                if ($leader) {
                    $collection['leader'] = $leader->getCollection();
                }
            }
        }
        if ($this->schueler) {
            $collection['schueler'] = $this->schueler;
        }
        return $collection;
    }



    public function getByDate($date = false)
    {
        if (!$date) {
            return false;
        }
        $ret = [];
        $data = DB::run("SELECT *  FROM ext_ganztags_day WHERE date = '" . $date . "'")->fetchAll();
        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }

    public function getSchueler($day = false) // day mo,di,mi,...
    {
        if (!$day) {
            return false;
        }
        include_once 'Schueler2.class.php';
        $class = new extGanztagsModelSchueler2();
        $dataDB = $class->getAll();

        //$str = '"group":'.$this->getData('group_id').'';
        $this->schueler = [];
        //$dataDB = DB::run("SELECT * FROM ext_ganztags_schueler WHERE days LIKE '%".$str."%'  ")->fetchAll();
        //$dataSQL = DB::getDB()->query("SELECT *  FROM ext_ganztags_schueler WHERE days LIKE '%$str%' ");
        //while ($data = DB::getDB()->fetch_array($dataSQL, true)) {

        foreach($dataDB as $data) {
            if ($data->getData('days')) {
                $days = json_decode($data->getData('days'));
                if ( $days->{$day} && $days->{$day}->group == $this->getData('group_id') ) {
                    //$foo = new extGanztagsModelSchueler2($data);
                    $this->schueler[] = $data->getCollection(true);
                }
            }

        }
        return $this;
    }



}
