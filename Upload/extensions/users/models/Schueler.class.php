<?php

class extUsersModelSchueler extends ExtensionModel
{

    static $table = 'schueler';

    static $fields = [
        'schuelerAsvID',
        'schuelerName',
        'schuelerVornamen',
        'schuelerRufname',
        'schuelerGeschlecht',
        'schuelerGeburtsdatum',
        'schuelerKlasse',
        'schuelerJahrgangsstufe',
        'schuelerAustrittDatum',
        'schuelerUserID',
        'schuelerBekenntnis',
        'schuelerAusbildungsrichtung',
        'schuelerGeburtsort',
        'schuelerGeburtsland',
        'schulerEintrittJahrgangsstufe',
        'schuelerEintrittDatum',
        'schuelerFoto',
        'schuelerGanztagBetreuung',
        'schuelerNameVorgestellt',
        'schuelerNameNachgestellt'
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
                'table_id' => 'schuelerAsvID',
                'parent_id' => 'schuelerUserID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('schuelerUserID')) {
                $collection['user'] = user::getCollectionByID($this->getData('schuelerUserID'));
            }
        }

        return $collection;
    }




}
