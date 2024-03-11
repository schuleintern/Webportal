<?php

/**
 *
 */
abstract class ExtensionModel
{

    private string $_table;
    private string $_table_id = 'id';
    private string $_table_parent_id = 'parent_id';

    protected array $_fields = [
        'state',
        'createdTime',
        'createdUserID'
    ];

    protected array $_defaults = [
        'state' => 0,
        'createdTime' => 0,
        'createdUserID' => 0
    ];

    public function __construct($data, $_table = false, $_option = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
        if ($_table) {
            $this->_table = $_table;
        }
        if ($_option) {

            if (isset($_option['table_id'])) {
                $this->_table_id = (string)$_option['table_id'];
            }
            if (isset($_option['parent_id'])) {
                $this->_table_parent_id = (string)$_option['parent_id'];
            }
        }
    }

    /**
     * @var data []
     */
    private $data = [];



    public function setData($data = [])
    {
        $this->data = (array)$data;
        return $this->getData();
    }

    public function getData($value = false)
    {
        if (!$value) {
            return $this->data;
        } else {
            return $this->data[$value];
        }
    }

    public function setValue($key = false, $value = false)
    {
        if ($key && ($value || $value == 0)) {
            $this->data[$key] = $value;
            return true;
        }
        return false;
    }

    public function getModelFields()
    {
        return $this->_fields;
    }

    public function setModelFields($_fields = false, $_defaults = false)
    {
        if ($_fields) {
            $this->_fields = $_fields;
        }
        if ($_defaults) {
            $this->_defaults = $_defaults;
        }
    }


    public  function getModelDefaults()
    {
        if ($this->_defaults['createdTime'] == 0) {
            $this->_defaults['createdTime'] = date('Y-m-d H:i:s', time());
        }
        return $this->_defaults;
    }

    public function getModelTable()
    {
        return $this->_table;
    }


    /**
     * Getter
     */

    public function getID()
    {
        return $this->data['id'];
    }
    public function getState()
    {
        return $this->data['state'];
    }
    public function getCreatedTime()
    {
        return $this->data['createdTime'];
    }
    public function getCreatedUserID()
    {
        return $this->data['createdUserID'];
    }





    public function getCollection($root = false)
    {

        if (is_array($root) ) {
            $collection = $root;
        } else {
            $collection = [
                "id" => $this->getID(),
                "state" => $this->getState(),
                "createdTime" => $this->getCreatedTime(),
                "createdUserID" => $this->getCreatedUserID()
            ];

        }
        

        $_fields = $this->getModelFields();

        foreach ($_fields as $field) {
            if (!isset($collection[$field])) {
                if ( $this->getData($field) || $this->getData($field) === 0 ) {
                    $collection[$field] = $this->getData($field);
                }
            }
        }

        return $collection;
    }



    public function getAll()
    {
        if (!$this->_table) {
            return false;
        }
        $ret = [];
        $data = DB::run('SELECT * FROM ' . $this->_table )->fetchAll();
        if ($data) {
            $class = get_called_class();
            foreach ($data as $item) {
                $ret[] = new $class($item);
            }
        }
        if ($ret[0]) {
            return $ret; 
        }
        return false;
    }


    public function getByID($id = false)
    {
        if (!$this->_table) {
            return false;
        }
        if (!$id) {
            return false;
        }
        $data = DB::run('SELECT * FROM ' . $this->_table . ' WHERE '.$this->_table_id.' = :id', ['id' => $id])->fetch();
        if ($data) {
            $class = get_called_class();
            return new $class($data);
        }
        return false;
    }

    public function getByParentID($id = false)
    {
        if ( !$this->_table || !$this->_table_parent_id) {
            return false;
        }
        if (!$id) {
            $id = 0;
        }
        $data = DB::run('SELECT * FROM ' . $this->_table . ' WHERE '.$this->_table_parent_id.' = :id', ['id' => $id ])->fetchAll();
        if ($data) {
            $class = get_called_class();
            foreach ($data as $item) {
                $ret[] = new $class($item);
            }
        }
        if ($ret[0]) {
            return $ret; 
        }
        return false;
    }



    public function getByState($status = [1])
    {
        if (!$this->_table) {
            return false;
        }
        if (!$status || !is_array($status)) {
            return false;
        }
        $where = '';
        foreach ($status as $s) {
            if ($where != '') {
                $where .= " OR ";
            }
            $where .= " `state` = " . (int)$s;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM " . $this->_table . " WHERE " . $where . " ORDER BY createdTime");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $class = get_called_class();
            $ret[] = new $class($data);
        }
        return $ret;
    }


    public function setState($status = false)
    {

        if (!$this->_table) {
            return false;
        }
        $id = (int)$this->getID();
        if (!$id || !$status) {
            return false;
        }

        if ( DB::run("UPDATE " . $this->_table . " SET state = :status WHERE ".$this->_table_id." = :id", ['id' => $id, 'status' => $status]) ) {
            return true;
        }
        return false;
    }


    public function save($data = false)
    {

        if (!$this->_table) {
            return false;
        }
        if (!$data || !is_array($data)) {
            return false;
        }

        $_fields = $this->getModelFields();
        $_defaults = $this->getModelDefaults();

        foreach ($_fields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = $_defaults[$field] ? $_defaults[$field] : 0;
            }
        }

        if ( !$data['id'] || $data['id'] == 0 )  {
            return DB::run('INSERT INTO ' . $this->_table . ' (' . implode(',', array_keys($data)) . ') values(:' . implode(', :', array_keys($data)) . ');', $data);
        } else {
            $sql = [];
            foreach($data as $k => $o) {
                if ($k != 'id') {
                    $sql[] = $k.' = :'.$k;
                }
            }
            return DB::run("UPDATE " . $this->_table . " SET ".implode(', ', $sql)." WHERE ".$this->_table_id." = :id", $data);
        }
    }

    public function delete()
    {
        if (!$this->_table) {
            return false;
        }
        $id = (int)$this->getID();
        if (!$id) {
            return false;
        }

        if ( DB::run("DELETE FROM " . $this->_table . "  WHERE ".$this->_table_id." = :id", ['id' => $id]) ) {
            return true;
        }
        return false;
    }
}
