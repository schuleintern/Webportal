<?php

/**
 *
 */
class extExampleModelItem
{

    /**
     * @var data []
     */
    private $data = [1,2];

    /**
     * @var static List []
     */
    private static $staticList = [2,3];


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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    public static function getStaticData() {
        return self::$staticList;
    }

}