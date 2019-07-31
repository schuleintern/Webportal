<?php 



class UpdateProcess {

    private $updateToVersion = "";

    /**
     *
     * @param $updateToVersion Zielversion
     */
	public function __construct($updateToVersion) {
	    $this->updateToVersion = $updateToVersion;
	}

    /**
     * @return boolean|string
     */
	public function updateReady(){
        // Überprüft, ob das Update möglich ist.

        $errors = [];

        if(!is_writable("../framework")) {
            $errors[] = "Das Verzeichnis 'framework' ist nicht beschreibbar. (Rechte 666)";
        }

        if(sizof($errors) == 0) {
            return true;
        }
        else return implode("\n",$errors);
    }

    public function doUpdate() {

	    // Templates zurücksetzen
        DB::getDB()->query("TRUNCATE templates");



	    if(DB::getVersion() == "1.0.0" && $this->updateToVersion == "1.0.1") {
            return $this->update_100_to_101();
        }

	    return true;
    }

    private function update_100_to_101() {



	    return true;
    }

}