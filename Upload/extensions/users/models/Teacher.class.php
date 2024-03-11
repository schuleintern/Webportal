<?php

class extUsersModelTeacher extends ExtensionModel
{

    static $table = 'lehrer';

    static $fields = [
        'lehrerID',
        'lehrerAsvID',
        'lehrerKuerzel',
        'lehrerName',
        'lehrerVornamen',
        'lehrerRufname',
        'lehrerGeschlecht',
        'lehrerZeugnisunterschrift',
        'lehrerAmtsbezeichnung',
        'lehrerUserID',
        'lehrerNameVorgestellt',
        'lehrerNameNachgestellt'
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
                'table_id' => 'lehrerID',
                'parent_id' => 'lehrerUserID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('lehrerUserID')) {
                $collection['user'] = user::getCollectionByID($this->getData('lehrerUserID'));
            }
        }

        return $collection;
    }




}
