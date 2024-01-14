<?php

/**
 *
 */
class extFilecollectModelFile
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

    public function getTime()
    {
        return $this->data['time'];
    }

    public function getUserID()
    {
        return $this->data['user_id'];
    }

    public function getFolderID()
    {
        return $this->data['folder_id'];
    }

    public function getFilename()
    {
        return $this->data['filename'];
    }

    public function getFileID()
    {
        return $this->data['fileid'];
    }


    public function getCollection($full = false)
    {
        $collection = [
            "id" => $this->getID(),
            "time" => $this->getTime(),
            "user_id" => $this->getUserID(),
            "folder_id" => $this->getFolderID(),
            "filename" => $this->getFilename(),
            "fileid" => $this->getFileID()
        ];
        if ($full == true) {
            $user = User::getUserByID($this->getUserID());
            if ($user) {
                $collection['user'] = $user->getCollection();
            }
        }

        return $collection;
    }

    /**
     * @return Array[]
     */
    public static function getByFileID($file_id = false)
    {
        if (!$file_id) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_file WHERE fileid = '" . $file_id. "'");
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            return new self($data);
        }
        return false;
    }


    /**
     * @return Array[]
     */
    public static function getByFolderID($folder_id = false)
    {
        if (!$folder_id) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_file WHERE folder_id = " . $folder_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByUserID($user_id = false, $folder_id = false)
    {
        if (!$user_id) {
            return false;
        }
        if (!$folder_id) {
            return false;
        }
        $ret = [];
        $dataSQL = DB::getDB()->query("SELECT *  FROM ext_filecollect_file WHERE user_id = " . $user_id . " AND folder_id = " . $folder_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    public static function open($fileID = false)
    {
        if (!$fileID) {
            return false;
        }

        $target_Path = PATH_ROOT . 'data' . DS . 'ext_filecollect' . DS;
        if (!file_exists($target_Path.$fileID)) {
            return false;
        }
        $fileinfo = FILE::getFileInfo($target_Path.$fileID);
        $file_data = self::getByFileID($fileID);

        header('Content-Description: Dateidownload');
        header('Content-Type: ' . $fileinfo['extension']);
        header('Content-Disposition: attachment; filename="'. $file_data->getFilename() . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($target_Path.$fileID));
        ob_clean();
        flush();
        $fp = fopen($target_Path.$fileID, 'rb');		// READ / BINARY
        fpassthru($fp);
        exit;

    }
    public static function upload($user_id = false, $files = false, $folder_id = false)
    {

        if (!$user_id) {
            return false;
        }

        $target_Path = PATH_ROOT . 'data' . DS . 'ext_filecollect' . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }
        foreach ($files['tmp_name'] as $key => $file) {
            $filename = $files['name'][$key];
            $info = pathinfo($files['name'][$key]);
            $newname = $user_id . time() . rand(100, 999) . '.' . $info['extension'];
            if (!move_uploaded_file($file, $target_Path . $newname)) {
                return false;
            }

            if (!DB::getDB()->query("INSERT INTO ext_filecollect_file
				(
				    time,
				    user_id,
				    filename,
				    fileid,
					folder_id
				) values(
				    '" . date('Y-m-d H:i', time()) . "',
				    " . (int)$user_id . ",
					'" . $filename . "',
					'" . $newname . "',
					'" . $folder_id . "'
				)
		    ")) {
                return false;
            }


        }

        return true;
    }

    public static function delete($id = false) {

        if (!(int)$id) {
            return false;
        }

        $dataSQL = DB::getDB()->query_first("SELECT *  FROM ext_filecollect_file WHERE id = " . (int)$id);
        $file = new self($dataSQL);

        $target_Path = PATH_ROOT . 'data' . DS . 'ext_filecollect' . DS;
        if (file_exists($target_Path.$file->getFileID())) {
            unlink($target_Path.$file->getFileID());
        }

        if (!DB::getDB()->query("DELETE FROM ext_filecollect_file WHERE id = " . (int)$id)) {
            return false;
        }
        return true;

    }


}