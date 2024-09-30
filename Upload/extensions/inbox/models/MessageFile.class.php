<?php

/**
 *
 */
class extInboxModelMessageFile extends ExtensionModel
{

    static $table = 'ext_inbox_message_file';

    static $fields = [
        'id',
        'body_id',
        'file',
        'name',
        'uniqid'
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
            'parent_id' => 'body_id'
        ]);
        self::setModelFields(self::$fields, self::$defaults);
    }


    public function getCollection($full = false, $withText = false)
    {

        $collection = parent::getCollection();

        return $collection;
    }


    public function getByUniqidID($id = false)
    {
        if (!self::$table) {
            return false;
        }
        if (!$id) {
            return false;
        }
        $data = DB::run('SELECT * FROM '.$this->getModelTable().' WHERE uniqid = :uniqid  ', ['uniqid' => $id])->fetch();
        if ($data) {
            return new self($data);
        }
        return false;
    }

    public function deleteWithFile()
    {
        if ($this->getData('file')) {
            if (file_exists($this->getData('file'))) {
                if ( !unlink($this->getData('file')) ) {
                    return false;
                }
            }
            $folder = explode('/', $this->getData('file'));
            array_pop($folder);
            $folder = implode('/', $folder);
            if (file_exists($folder) && is_dir($folder)) {
                if ( count(scandir($folder)) <= 2) {
                    FILE::removeFolder($folder);
                }

            }
        }
        if ( $this->delete() ) {
            return true;
        }

        return false;
    }






}
