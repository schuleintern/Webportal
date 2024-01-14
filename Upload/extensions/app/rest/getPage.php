<?php

class getPage extends AbstractRest {
	
	protected $statusCode = 200;


	public function execute($input, $request) {

        $ext = $_POST['ext'];
        $page = $_POST['view'];

        if ($ext && $page) {

            $classname = 'ext'.ucfirst($ext).'App'.ucfirst($page);

            $filepath = PATH_EXTENSIONS.$ext.'/app/'.$page.'.php';

            if (file_exists($filepath)) {
                

                // AUtoloader
                //include_once(PATH_LIB . "app/AbstractApp.class.php");

                include_once($filepath);

                //return [$ext, $page, $classname];


                $class = new $classname();

                $ret = $class->execute();

                
                //return $classname->execute();

                $retScript = [];
                foreach($ret['scripts'] as $script) {
                    $retScript[] = FILE::getScript($script);
                }
                //$scriptHTML = FILE::getScripts($ret['scripts']);

                //return [PATH_EXTENSIONS.$ext.'/app/'.$page.'.php', $classname, $ret['scripts'], $scriptHTML];

                return [
                    "ext" => $ext,
                    "page" => $page,
                    "scripts" => $retScript,
                    "globals" => $ret['data']
                ];
                
            }
        }

        return [
            'error' => true,
            'msg' => 'Error'
        ];

	}


	/**
	 * Set Allowed Request Method
	 * (GET, POST, ...)
	 * 
	 * @return String
	 */
	public function getAllowedMethod() {
		return 'POST';
	}


    /**
     * Muss der Benutzer eingeloggt sein?
     * Ist Eine Session vorhanden
     * @return Boolean
     */
    public function needsUserAuth() {
        return false;
    }

    /**
     * Ist eine Admin berechtigung nötig?
     * only if : needsUserAuth = true
     * @return Boolean
     */
    public function needsAdminAuth()
    {
        return false;
    }
    /**
     * Ist eine System Authentifizierung nötig? (mit API key)
     * only if : needsUserAuth = false
     * @return Boolean
     */
    public function needsSystemAuth() {
        return false;
    }

    public function needsAppAuth()
    {
        return true;
    }


}	

?>