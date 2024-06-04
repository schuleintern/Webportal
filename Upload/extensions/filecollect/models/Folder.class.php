<?php

/**
 *
 */
class extFilecollectModelFolder
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

    public function getInfo()
    {
        return $this->data['info'];
    }

    public function getMembers()
    {
        return $this->data['members'];
    }

    public function getEndDate()
    {
        return $this->data['endDate'];
    }

    public function getAnzahl()
    {
        return $this->data['anzahl'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getCollectionID()
    {
        return $this->data['collection_id'];
    }

    public function getCollectionTitle()
    {
        return $this->data['collection_title'];
    }

    public function getCollectionInfo()
    {
        return $this->data['collection_info'];
    }


    public function getCollection($full = false)
    {
        $collection = [
            "id" => $this->getID(),
            "c_id" => $this->getCollectionID(),
            "c_title" => $this->getCollectionTitle(),
            "c_info" => $this->getCollectionInfo(),
            "title" => $this->getTitle(),
            "info" => $this->getInfo(),
            "members" => json_decode($this->getMembers()),
            "endDate" => $this->getEndDate(),
            "anzahl" => $this->getAnzahl(),
            "status" => $this->getStatus()
        ];
        if ($collection['members']) {
            $arr = [];
            foreach ($collection['members'] as $member) {
                $user = user::getUserByID($member);
                if ($user) {
                    $arr[] = $user->getCollection(false, true);
                }
            }
            $collection['members'] = $arr;
        }
        return $collection;
    }


    /**
     * @return Array[]
     */

    public static function getByUserID($id = false)
    {
        if (!$id) {
            return false;
        }
        $now = new DateTime();
        $now = $now->format("Y-m-d H:i:s");

        $dataSQL = DB::getDB()->query("SELECT a.*, b.title AS collection_title, b.info AS collection_info  FROM ext_filecollect_folders AS a
        LEFT JOIN ext_filecollect_collection AS b
        ON a.collection_id = b.id
        WHERE a.members LIKE '%$id%' AND a.status = 1 AND a.endDate >= '" . $now . "' ORDER BY a.endDate");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */

    public static function getByCollectionID($id = false)
    {
        if (!$id) {
            return false;
        }
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_folders WHERE collection_id = " . $id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    /**
     * @return Array[]
     */
    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_folders");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }


    /**
     * @return Array[]
     */
    public static function set($data = false)
    {

        $data = (array)$data;

        if (!$data) {
            return false;
        }

        if ($data['members'] && is_array($data['members'])) {
            $arr = [];
            foreach ($data['members'] as $member) {
                $arr[] = $member->id;
            }
            $data['members'] = json_encode($arr);
        }
        if ($data['endDate'] == '0' || !$data['endDate']) {
            $data['endDate'] = 'NULL';
        } else {
            $date = new DateTime($data['endDate']);
            $data['endDate'] = "'" . $date->format('Y-m-d H:i:s') . "'";
        }

        if (!$data['id']) {

            if (!$data['collection_id']) {
                return false;
            }

            if (!DB::getDB()->query("INSERT INTO ext_filecollect_folders
				(
				    collection_id,
					title,
				    info,
                    status,
                    anzahl,
				    endDate,
				    members
				) values(
				    " . DB::getDB()->escapeString($data['collection_id']) . ",
					'" . DB::getDB()->escapeString($data['title']) . "',
					'" . DB::getDB()->escapeString($data['info']) . "',
					" . DB::getDB()->escapeString($data['status']) . ",
					" . DB::getDB()->escapeString($data['anzahl']) . ",
					" . $data['endDate'] . ",
					'" . $data['members'] . "'
					
				)
		    ")) {
                return false;
            }
            return DB::getDB()->insert_id();

        } else {
            if (!DB::getDB()->query("UPDATE ext_filecollect_folders SET
                        title = '" . DB::getDB()->escapeString($data['title']) . "',
                        info = '" . DB::getDB()->escapeString($data['info']) . "',
                        members = '" . $data['members'] . "',
                        status = " . DB::getDB()->escapeString($data['status']) . ",
                        endDate = " . $data['endDate'] . ",
                        anzahl = " . DB::getDB()->escapeString($data['anzahl']) . "
                        WHERE id = " . (int)$data['id'])) {
                return false;
            }
            return $data['id'];
        }


        return false;

    }

    public static function downloadFilesAsZip($folder_id = false, $user_id = false)
    {

        if (!(int)$folder_id) {
            return false;
        }
        if (!(int)$user_id) {
            return false;
        }

        $access = false;
        $dataFolder = DB::getDB()->query_first("SELECT *  FROM ext_filecollect_folders WHERE id = " . (int)$folder_id);
        if ($dataFolder['collection_id']) {
            $dataCollection = DB::getDB()->query_first("SELECT *  FROM ext_filecollect_collection WHERE id = " . (int)$dataFolder['collection_id']);
            if ($dataCollection['user_id'] == $user_id) {
                $access = true;
            }
        }

        if ($access)  {
            $tempFile = tempnam(PATH_TMP, "zip");
            $zip = new ZipArchive();
            $zip->open($tempFile, ZipArchive::CREATE);

            include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';
            $files = extFilecollectModelFile::getByFolderID($folder_id);
            $target_Path = PATH_ROOT . 'data' . DS . 'ext_filecollect' . DS;
            foreach ($files as $file) {
                if (file_exists($target_Path . $file->getFileID())) {
                    $zip->addFile($target_Path . $file->getFileID(), $file->getFilename());
                }
            }
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($tempFile));
            header('Content-Disposition: attachment; filename="'.$dataCollection['title'].'-'.$dataFolder['title'].'.zip"');
            readfile($tempFile);
            unlink($tempFile);
        }
        return false;
    }


}