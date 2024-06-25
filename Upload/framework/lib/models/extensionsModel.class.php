<?php

/**
 *
 */
abstract class ExtensionModel
{

    private string $_table = '';
    private string $_table_id = 'id';
    private string $_table_parent_id = 'parent_id';

    protected array $_fields = [
        'state',
        'createdTime',
        'createdUserID'
    ];

    protected array $_defaults = [];

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
        if (isset($this->_defaults['createdTime']) && $this->_defaults['createdTime'] == 0) {
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
            /*
            $collection = [
                //"id" => $this->getID(),
                "state" => $this->getState(),
                "createdTime" => $this->getCreatedTime(),
                "createdUserID" => $this->getCreatedUserID()
            ];
            */
            $_fields = $this->getModelFields();
            if (in_array('id',$_fields)) {
                $collection["id"] = $this->getID();
            }
            if (in_array('state',$_fields)) {
                $collection["state"] = $this->getState();
            }
            if (in_array('createdTime',$_fields)) {
                $collection["createdTime"] = $this->getCreatedTime();
            }
            if (in_array('createdUserID',$_fields)) {
                $collection["createdUserID"] = $this->getCreatedUserID();
            }

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

    public function getCount()
    {
        $all = $this->getAll();
        if ($all) {
            return count($all);
        }
        return false;
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



    public function getByState($status = [1], $order = false)
    {
        if (!$this->_table) {
            return false;
        }
        if (!$order) {
            $order = 'createdTime';
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
        $dataSQL = DB::getDB()->query("SELECT * FROM " . $this->_table . " WHERE " . $where . " ORDER BY ".$order);
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

    public function setSort($sort = false)
    {

        if (!$this->_table) {
            return false;
        }
        $id = (int)$this->getID();
        if (!$id || !$sort) {
            return false;
        }

        if ( DB::run("UPDATE " . $this->_table . " SET sort = :sort WHERE ".$this->_table_id." = :id", ['id' => $id, 'sort' => $sort]) ) {
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
                $data[$field] = isset($_defaults[$field]) ? $_defaults[$field] : NULL;
            }
        }
        foreach ($_defaults as $key => $item) {
            if (!isset($data[$key])) {
                $data[$key] = $item;
            }
        }

        if ( !$data[$this->_table_id] || $data[$this->_table_id] == 0 )  {
            return DB::run('INSERT INTO ' . $this->_table . ' (' . implode(',', array_keys($data)) . ') values(:' . implode(', :', array_keys($data)) . ');', $data);
        } else {
            $sql = [];
            foreach($data as $k => $o) {
                if ($k != $this->_table_id  ) {
                    $sql[] = $k.' = :'.$k;
                }
            }
            return DB::run("UPDATE " . $this->_table . " SET ".implode(', ', $sql)." WHERE ".$this->_table_id." = :".$this->_table_id, $data);
        }
    }

    public function update($data = false)
    {
        if ( !$data[$this->_table_id] || $data[$this->_table_id] == 0 )  {
            return false;
        }
        $sql = [];
        foreach($data as $k => $o) {
            if ($k != $this->_table_id  ) {
                $sql[] = $k.' = :'.$k;
            }
        }
        return DB::run("UPDATE " . $this->_table . " SET ".implode(', ', $sql)." WHERE ".$this->_table_id." = :".$this->_table_id, $data);

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


    public function uploadFile($file = false, $newname = false, $folders = false) {



        if (!$file || !$newname) {
            return false;
        }

        $target_Path = PATH_TMP;
        if ($folders) {
            $target_Path .= $folders.DS;
            if (!file_exists($target_Path)) {
                mkdir($target_Path,0777,true);
            }
        }

        $info = pathinfo($file['name']);
        $newname = $newname . '.' . $info['extension'];

        if (move_uploaded_file($file['tmp_name'], $target_Path . $newname)) {
            return [
                'path' => $target_Path,
                'filename' => $newname
            ];
        }

        return false;

    }

    public function uploadMove($file = false, $tmp_id = false)
    {
        if (!$file) {
            return false;
        }
        $newFile = basename($file);
        $newFolder = str_replace(PATH_TMP,'',$file);
        $newFolder = str_replace($newFile,'',$newFolder);

        $newFolders = explode('/', $newFolder);
        $target_Path = PATH_ROOT . 'data' . DS;
        foreach ($newFolders as $folder) {
            if ($folder) {
                if ($tmp_id) {
                    $folder = str_replace('__tmp',$tmp_id, $folder);
                }
                $target_Path .= $folder.DS;
                if (!file_exists($target_Path)) {
                    mkdir($target_Path,0777,true);
                }
            }
        }

        $newPath = $target_Path.$newFile;
        if (!file_exists($target_Path)) {
            return false;
        }
        if (file_exists($newPath)) {
            return true; // Datei wurde schon verschoben
        }
        if (!file_exists($file)) {
            return false;
        }
        if ( rename($file, $newPath) ) {
            return $newPath;
        }
        return false;
    }

    public function uploadMoveOrDelete($data = false, $oldData = false, $tmp_id = false)
    {

        $ret = false;
        if ( $data ) {
            $ret = $data;

            if (!$oldData || $oldData != $data) {
                if ( $newPath = $this->uploadMove($data, $tmp_id) ) {
                    $ret = $newPath;
                } else {
                    return false;
                }
            }

        } else if ( $data === '' && $oldData ) {
            if (file_exists($oldData)) {
                if (unlink($oldData)) {
                    $ret = '';
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        if ( $data === '' ) {
            return '';
        }
        return $ret;


    }
}
