<?php

class extUsersModelEltern extends ExtensionModel
{

    static $table = 'eltern_email';

    static $fields = [
        'elternEMail',
        'elternSchuelerAsvID',
        'elternUserID'
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
                'parent_id' => 'elternUserID'
            ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $url = false)
    {

        $collection = parent::getCollection();

        if ($full) {
            if ($this->getData('elternUserID')) {
                $collection['elternUser'] = user::getCollectionByID($this->getData('elternUserID'));
                if ($collection['elternUser']) {
                    $collection['elternUserName'] = $collection['elternUser']['name'];
                }
            }
            if ($this->getData('elternSchuelerAsvID')) {
                $user = user::getByASVID($this->getData('elternSchuelerAsvID'));
                if ($user) {
                    $collection['schuelerUser'] = $user->getCollection();
                    if ($collection['schuelerUser']) {
                        $collection['schuelerUserName'] = $collection['schuelerUser']['name'];
                    }
                }
            }
        }

        return $collection;
    }




}
