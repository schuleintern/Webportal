<?php



class extVplanAdminImport extends AbstractPage
{

    public static function getSiteDisplayName()
    {
        return '<i class="fa fas fa-upload"></i> Vertretungsplan - Import';
    }

    public function __construct($request = [], $extension = [])
    {
        parent::__construct(array(self::getSiteDisplayName()), false, false, false, $request, $extension);
        $this->checkLogin();
    }

    public function execute()
    {

        //$this->getRequest();
        //$this->getAcl();
        //echo URL_SELF;


        $this->render([
            "tmplHTML" => '<div class="box"><div class="box-body"><div id=app></div></div></div>',
            "scripts" => [
                //PATH_COMPONENTS.'system/adminSettings/dist/main.js'
                PATH_EXTENSION . 'tmpl/scripts/import/dist/main.js'
            ],
            "data" => [
                "apiURL" => "rest.php/vplan",
                "selfURL" => URL_SELF,
                "stundenplanSoftware" => DB::getGlobalSettings()->stundenplanSoftware
            ]

        ]);
    }

    public function taskUpload($post)
    {


        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            PAGE::exitJsonError('Missing User ID');
        }

        if ($_FILES && $_FILES['file'] && $_FILES['file']['error'] == 0) {

            $filename = $_FILES['file']['name'];

            if ($filename && ($_FILES['file']['type'] == 'text/plain' || $_FILES['file']['type'] == 'text/html')) {

                $allowed = array('txt', 'html', 'htm');

                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!in_array(strtolower($ext), $allowed)) {
                    PAGE::exitJsonError('Dateityp nicht erlaubt!');
                }
                $dir = PATH_WWW_TMP . 'ext_vplan';
                if ( !is_dir($dir) ) {
                    mkdir($dir);
                  }
                $uploadfile = $dir . DS . date('Y-m-d_H-i', time()).'-'.$filename;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {

                    include_once PATH_EXTENSION . '..' . DS . 'models' . DS . 'Upload.class.php';
                    if (extVplanModelUpload::addFile($uploadfile, $post['override'], $ext)) {
                        PAGE::exitJsonDone('Datei wurde hochgeladen');
                    } else {
                        PAGE::exitJsonError('Fehler beim hinzufügen!');
                    }
                } else {
                    PAGE::exitJsonError('Upload nicht erfolgreich!');
                }
            } else {
                PAGE::exitJsonError('Uploadtyp nicht erlaubt!');
            }
        } else {
            PAGE::exitJsonError('Fehler!');
        }
    }
}
