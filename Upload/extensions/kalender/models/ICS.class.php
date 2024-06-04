<?php
/**
 *
 */
class extKalenderModelIcs extends ExtensionModel
{

    static $table = 'ext_kalender_ics';

    static $fields = [
        'createdTime',
        'user_id',
        'keyCode'
    ];


    static $defaults = [
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, ['parent_id' => 'user_id']);
        self::setModelFields(self::$fields, self::$defaults);
    }





    public function getCollection($full = false)
    {

        $collection = parent::getCollection();


        if ($full) {

            if ($this->getData('user_id')) {
                $collection['user'] = user::getCollectionByID($this->getData('user_id'));
                if ($collection['user']) {
                    $collection['userName'] = $collection['user']['name'];
                }
            }

        }

        return $collection;
    }




    public function getByCode($code = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$code ) {
            return false;
        }
        $ret = [];

        $data = DB::run('SELECT * FROM ' . $this->getModelTable() . ' WHERE keyCode = :code', ['code' => $code])->fetch();
        if ($data) {
            return new self($data);
        }
        return false;
    }


}
