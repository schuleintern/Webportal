<?php

/**
 *
 */
class extInboxModelMessageIsREad extends ExtensionModel
{

    static $table = 'ext_inbox_message_isread';

    static $fields = [
        'id',
        'message_id',
        'isRead',
        'isReadUser'
    ];

    
    static $defaults = [

    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, self::$table ? self::$table : false, [
            'parent_id' => 'message_id'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $withText = false)
    {

        $collection = parent::getCollection();

        if ($collection['isRead']) {
            $collection['isReadDate'] = date('d.m.Y H:i',$collection['isRead']);
        }
        if ($collection['isReadUser']) {
            $collection['isReadUser'] = User::getCollectionByID($collection['isReadUser']);
        }

        return $collection;
    }


    public function getByMessageAndUser( $message_id = false, $user_id = false)
    {
        if (!$message_id || !$user_id) {
            return false;
        }
        if (!self::$table) {
            return false;
        }

        $ret = [];
        $data = DB::run('SELECT  a.* FROM  ' . $this->getModelTable() . ' AS `a` 
          WHERE a.message_id = :message_id AND a.isReadUser = :user_id; ', ['message_id' => (int)$message_id, 'user_id' => (int)$user_id])->fetchAll();

        foreach ($data as $item) {
            $ret[] = new self($item);
        }
        return $ret;
    }



}
