<?php

class extFaecherModelUnterricht extends ExtensionModel
{

    static $table = 'unterricht';

    static $fields = [
        'unterrichtID',
        'unterrichtElementASVID',
        'unterrichtLehrerID',
        'unterrichtFachID',
        'unterrichtBezeichnung',
        'unterrichtArt',
        'unterrichtStunden',
        'unterrichtIsWissenschaftlich',
        'unterrichtStart',
        'unterrichtEnde',
        'unterrichtIsKlassenunterricht',
        'unterrichtKoppelText',
        'unterrichtKoppelIsPseudo',
        'unterrichtKlassen'
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false,
            [
                'table_id' => 'unterrichtID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        return [
            'id' => $collection['unterrichtID'],
            'asvID' => $collection['unterrichtElementASVID'],
            'teacherID' => $collection['unterrichtLehrerID'],
            'fachID' => $collection['unterrichtFachID'],
            'desc' => $collection['unterrichtBezeichnung'],
            'art' => $collection['unterrichtArt'],
            'stunden' => $collection['unterrichtStunden'],
            'isWissenschaftlich' => $collection['unterrichtIsWissenschaftlich'],
            'start' => $collection['unterrichtStart'],
            'ende' => $collection['unterrichtEnde'],
            'isKlassenunterricht' => $collection['unterrichtIsKlassenunterricht'],
            'koppelText' => $collection['unterrichtKoppelText'],
            'koppelIsPseudo' => $collection['unterrichtKoppelIsPseudo'],
            'klassen' => $collection['unterrichtKlassen']
        ];
    }

}
