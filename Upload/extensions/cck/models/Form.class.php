<?php

/**
 *
 */
class extCckModelForm
{

    /**
     * @var data []
     */
    private $data = [];

    private $fields = false;



    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->data = $data;
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getTitle() {
        return $this->data['title'];
    }
    public function getTemplate() {
        return $this->data['template'];
    }

    public function getFields() {
        if ($this->fields == false) {
            $this->fields = self::getAllFields($this->getID());
        }
        return $this->fields;
    }


    public function getCollection($full = false) {

        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle(),
            "template" => $this->getTemplate()
        ];
        if ($full) {
            $collection["fields"] = $this->getFields();
        }

        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getAll() {

        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM cck_forms ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    public static function getByID($id = false) {

        if (!(int)$id) {
            return false;
        }
        $dataSQL = DB::getDB()->query_first("SELECT * FROM cck_forms WHERE id = ".(int)$id, true);
        return new self($dataSQL);
    }

    public static function getAllFields($form_id = false) {

        if (!(int)$form_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT a.id, a.form_id, a.field_id, b.title
            FROM cck_formfields as a 
            LEFT JOIN cck_fieldtyp as b ON a.field_id = b.id
            WHERE a.form_id = ".(int)$form_id);

        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = $data;
        }
        return $ret;
    }



}