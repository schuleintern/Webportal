<?php

/**
 *
 */
class extFilecollectModelCollection
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

    public function getUserID()
    {
        return $this->data['user_id'];
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


    public function getCollection($full = false)
    {
        $collection = [
            "id" => $this->getID(),
            "user_id" => $this->getUserID(),
            "title" => $this->getTitle(),
            "info" => $this->getInfo(),
            "members" => json_decode($this->getMembers()),
            "endDate" => $this->getEndDate()
        ];
        if ($collection['members']) {
            $arr = [];
            foreach ($collection['members'] as $member) {
                $user = user::getUserByID($member);
                if ($user) {
                    $arr[] = $user->getCollection();
                }
            }
            $collection['members'] = $arr;
        }
        return $collection;
    }


    /**
     * @return Array[]
     */
    public static function getByID($id = false)
    {
        if (!$id) {
            return false;
        }
        $data = DB::getDB()->query_first("SELECT *  FROM ext_filecollect_collection WHERE id = ".(int)$id);
        return new self($data);
    }

    /**
     * @return Array[]
     */
    public static function getAll()
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_collection");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByUserID($id = false)
    {
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_collection WHERE user_id = ".$id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }



    /**
     * @return Array[]
     */
    public static function set($data = false, $id = false )
    {

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return false;
        }

        if (!$data['title']) {
            return false;
        }

        if ( (int)$data['endDate'] == 0 || $data['endDate'] == '0' || !$data['endDate']) {
            $data['endDate'] = 'NULL';
        } else {
            $data['endDate'] = "'".date('Y-m-d H:i:s', $data['endDate'])."'";
        }

        if (!$id) {
            if (!DB::getDB()->query("INSERT INTO ext_filecollect_collection
				(
				    createdTime,
				    user_id,
					title,
				    info,
				    members,
				    endDate
				) values(
				    '".date('Y-m-d H:i', time())."',
				    ".$userID.",
					'" . DB::getDB()->escapeString($data['title']) . "',
					'" . DB::getDB()->escapeString($data['info']) . "',
					'" . (string)DB::getDB()->escapeString($data['members']) . "',
					" . $data['endDate'] . "
				)
		    ")) {
                return false;
            }
           return DB::getDB()->insert_id();

        } else {
            if (!DB::getDB()->query("UPDATE ext_filecollect_collection SET
                        title = '" . DB::getDB()->escapeString($data['title']) . "',
                        info = '" . DB::getDB()->escapeString($data['info']) . "',
                        members = '" . (string)DB::getDB()->escapeString($data['members']) . "',
                        endDate = " . $data['endDate'] . "
                        WHERE id = " . (int)$id)) {
                return false;
            }
            return $id;
        }

        return false;

    }


    public static function downloadFilesAsZip($collection_id = false, $user_id = false)
    {

        if (!(int)$collection_id) {
            return false;
        }
        if (!(int)$user_id) {
            return false;
        }

        $access = false;
        $dataCollection = DB::getDB()->query_first("SELECT *  FROM ext_filecollect_collection WHERE id = " . (int)$collection_id);
        if ($dataCollection['id'] && $dataCollection['user_id']) {
            if ($dataCollection['user_id'] == $user_id) {
                $access = true;
            }
        }

        if ($access)  {
            $tempFile = tempnam(PATH_TMP, "zip");
            $zip = new ZipArchive();
            $zip->open($tempFile, ZipArchive::CREATE);

            include_once PATH_EXTENSION . 'models' . DS . 'Folder.class.php';
            $folders = extFilecollectModelFolder::getByCollectionID($dataCollection['id']);

            include_once PATH_EXTENSION . 'models' . DS . 'File.class.php';
            $target_Path = PATH_ROOT . 'data' . DS . 'ext_filecollect' . DS;

            foreach($folders as $folder) {
                $zip->addEmptyDir( $folder->getTitle() );

                $files = extFilecollectModelFile::getByFolderID($folder->getID());
                foreach ($files as $file) {
                    if (file_exists($target_Path . $file->getFileID())) {
                        $zip->addFile($target_Path . $file->getFileID(), $folder->getTitle().DS.$file->getFilename());
                    }
                }
            }

            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($tempFile));
            header('Content-Disposition: attachment; filename="'.$dataCollection['title'].'-'.date('Y-m-d', time()).'.zip"');
            readfile($tempFile);
            unlink($tempFile);
        }
        return false;


    }


}