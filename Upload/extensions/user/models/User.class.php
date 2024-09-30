<?php

/**
 *
 */
class extUserModelUser extends ExtensionModel
{

    static $table = 'users';

    static $fields = [
        'userID',
        'userName',
        'userEMail',
        'userSignature',
        'userAutoresponse',
        'userAutoresponseText'
    ];


    static $defaults = [
        'userID' => 0,
        'userName' => '',
        'userEMail' => '',
        'userSignature' => '',
        'userAutoresponse' => 0,
        'userAutoresponseText' => ''
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct(
            $data,
            self::$table ? self::$table : false,
            ['table_id' => 'userID']
        );
        self::setModelFields(self::$fields, self::$defaults);
    }



    public function getCollection($full = false)
    {

        $collection = parent::getCollection([]);

        if ($collection['userID']) {
            $user = User::getUserByID($collection['userID']);
            if ($user) {
                $collection['user'] = $user->getCollection(true, true, true, true);

                if ( $collection['user']['type'] == 'isEltern' ) {
                    $obj = $user->getElternObject();

                    /*
                    $collection['adressen'] = [];
                    $adressen = $user->getAdressen();
                    foreach ($adressen as $adresse) {
                        $collection['adressen'][] = $adresse->getCollection(true);
                    }
                    */
                }

                if ( $collection['user']['type'] == 'isPupil' ) {

                    $obj = $user->getPupilObject();
                    $collection['user']['geburtstag'] = $obj->getGeburtstagAsNaturalDate();
                    $collection['user']['alter'] = $obj->getAlter();
                    $collection['user']['ort'] = $obj->getWohnort();
                    $collection['user']['bekenntnis'] = $obj->getBekenntnis();
                    $collection['user']['ausbildungsrichtung'] = $obj->getAusbildungsrichtung();

                    $collection['adressen'] = [];
                    $adressen = $obj->getAdressen();
                    foreach ($adressen as $adresse) {
                        $collection['adressen'][] = $adresse->getCollection(true);
                    }

                    $collection['emails'] = [];
                    $emails = $obj->getElternEMail();
                    foreach ($emails as $email) {
                        if ($email) {
                            $collection['emails'][] = $email->getCollection();
                        }
                    }

                }
            }
        }

        //$collection['userAutoresponse'] = (int)$collection['userAutoresponse']; // ? true : false;

        return $collection;
    }
}
