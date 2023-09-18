<?php

include_once './Folder.class.php';


/**
 *
 */
class extPodcastModelList
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
    public function getData()
    {
        return $this->data;
    }


    /**
     * Getter
     */
    public function getID()
    {
        return $this->data['id'];
    }
    public function getTitle()
    {
        return $this->data['title'];
    }




    public function getCollection($full = false, $folder = false)
    {

        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle()
        ];

        if ($full) {

        }
        return $collection;
    }


    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_podcast_items " );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    public static function getByID($id = false)
    {
        if (!$id) {
            return false;
        }
        $data = DB::getDB()->query_first("SELECT a.* FROM ext_podcast_items AS a WHERE a.id = " . (int)$id , true);
        if ($data['id']) {
            return new self($data);
        }
        return false;
    }


    public static function setItem($id, $title, $cover, $file)
    {

        echo $id.$cover;


        if (!$id) {

            if (!DB::getDB()->query("INSERT INTO ext_podcast_items
				(
				    title, cover, file
				) values(
					'" .DB::getDB()->escapeString($title) . "',
					'" .DB::getDB()->escapeString($cover) . "',
					'" .DB::getDB()->escapeString($file) . "'
					
				)
		    ")) {
                return false;
            }

            return DB::getDB()->insert_id();

        } else {

            if (!DB::getDB()->query("UPDATE ext_podcast_items SET
                        title = '" . DB::getDB()->escapeString($title) . "',
                        cover = '" . DB::getDB()->escapeString($cover) . "',
                        file = '" . DB::getDB()->escapeString($file) . "'
                        WHERE id = " . (int)$id)) {
                return false;
            }
            return $id;

        }


        return false;
    }

    public static function deleteFromID( $id ) {

        if (!$id) {
            return false;
        }

        if (!DB::getDB()->query("DELETE FROM ext_podcast_items WHERE id=".(int)$id)) {
            return false;
        }
        return true;

    }



}