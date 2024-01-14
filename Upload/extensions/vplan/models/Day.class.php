<?php
/**
 *
 */
class extVplanModelDay
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
        $this->setData($data);
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
    public function getData() {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getDate() {
        return $this->data['date'];
    }
    public function getText() {
        return $this->data['text'];
    }


    public function getCollection() {

        $collection = [
            "id" => $this->getID(),
            "date" => $this->getDate(),
            "text" => $this->getText()
        ];

        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getByDate($date = false) {

        if (!$date) {
            return false;
        }

        $dataSQL = DB::getDB()->query_first("SELECT * FROM ext_vplan_day WHERE `date` = '".$date."'");
        return new self($dataSQL);


    }


}