<?php



class extPodcastAdminList extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-microphone-alt"></i> Podcast - Bearbeiten';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        $acl = $this->getAcl();



        if (!$this->canWrite()) {
            new errorPage('Kein Zugriff');
        }

        $this->render([
            "tmpl" => "default",
            "scripts" => [
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/chunk-vendors.js',
                PATH_EXTENSION . 'tmpl/scripts/list/dist/js/app.js'
            ],
            "data" => [
                "selfURL" => URL_SELF,
                "apiURL" => "rest.php/podcast",
                "acl" => $acl['rights']
            ]

        ]);

    }

    public function taskUploadAudio($data) {

        $file = $_FILES['file'];
        $info = pathinfo($file['name']);
        if ($info['extension'] != 'mp3') {
            return [
                'error' => true,
                'msg' => 'Wrong Format'
            ];
        }

        $this->upload($data);
        exit;
    }

    public function taskUploadCover($data) {

        $file = $_FILES['file'];
        $info = pathinfo($file['name']);
        if ($info['extension'] != 'png' && $info['extension'] != 'jpg') {
            return [
                'error' => true,
                'msg' => 'Wrong Format'
            ];
        }

        $this->upload($data);
        exit;
    }

    public function upload($data) {

        $id = (int)$data['id'];
        if (!$id) {
            return false;
        }
        $file = $_FILES['file'];
        if (!$file) {
            return [
                'error' => true,
                'msg' => 'Missing Files'
            ];
        }
        $target_Path = PATH_ROOT . 'data' . DS . 'ext_podcast' . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }
        $target_Path = $target_Path . DS . $id . DS;
        if (!file_exists($target_Path)) {
            mkdir($target_Path);
        }
        $info = pathinfo($file['name']);
        $newname = 'podcast_' . time() . rand(100, 999) . '.' . $info['extension'];

        if (!move_uploaded_file($file['tmp_name'], $target_Path . $newname)) {
            echo 'Error'; exit;
        }
        echo $newname;
        exit;
    }


}
