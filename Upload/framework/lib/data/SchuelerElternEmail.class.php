<?php 

class SchuelerElternEmail {
	private $data = array();
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getEMail() {
		return $this->data['elternEMail'];
	}
	
	public function getAdresseID() {
		return $this->data['elternAdresseID'];
	}


    public function getCollection($full = false) {
        $collection = [
            "email" => $this->getEMail(),
            "adresseID" => $this->getAdresseID()
        ];

        return $collection;
    }

}


?>