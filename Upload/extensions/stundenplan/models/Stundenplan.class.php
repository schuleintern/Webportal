<?php

/**
 *
 */
class extStundenplanModelStundenplan extends ExtensionModel
{

    static $table = 'stundenplan_stunden';

    static $fields = [
        'stundeID',
        'stundenplanID',
        'stundeKlasse',
        'stundeLehrer',
        'stundeFach',
        'stundeRaum',
        'stundeTag',
        'stundeStunde'
    ];


    static $defaults = [
        'stundeKlasse' => '',
        'stundeLehrer' => '',
        'stundeFach' => '',
        'stundeRaum' => ''
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, [
            'table_id' => 'stundeID',
            'parent_id' => 'stundenplanID'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {

        }


        $day = '';
        switch ($collection['stundeTag']) {
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

        return [
            "id" => $collection['stundeID'],
            "stundenplanID" => $collection['stundenplanID'],
            "klasse" => $collection['stundeKlasse'],
            "teacher" => $collection['stundeLehrer'],
            "fach" => $collection['stundeFach'],
            "room" => $collection['stundeRaum'],
            "day" => $collection['stundeTag'],
            "dayLabel" => $day,
            "hour" => $collection['stundeStunde']
        ];


        //return $collection;
    }


}
