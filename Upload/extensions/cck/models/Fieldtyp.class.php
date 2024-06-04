<?php

/**
 *
 */
class extCckModelFieldtyp
{

    /**
     * @var data []
     */
    private $data = [];



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


    public function getCollection() {

        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle(),
            "template" => $this->getTemplate()
        ];

        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getAll() {

        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT * FROM cck_fieldtyp ");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }



}