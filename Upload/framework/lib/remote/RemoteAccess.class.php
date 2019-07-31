<?php 


/**
 * Zugriffsbibliothek für einen Remotezugriff in die Schule.
 * @author Christian Spitschka
 * @version 1.0
 */
class RemoteAccess {
	private $data;
	
	public function __construct($name) {
				
		$this->data = DB::getDB()->query_first("SELECT * FROM remote_usersync WHERE syncName='" . $name . "'");
		
		$context = stream_context_create(array('ssl'=>array(
				'verify_peer' => false,
		        'verify_peer_name' => false
		)));
		
		libxml_set_streams_context($context);
	}
	
	public function getAllUsers() {	

	    
	    $arrContextOptions=array(
	        "ssl"=>array(
	            "verify_peer"=>false,
	            "verify_peer_name"=>false,
	        ),
	    );
	    
	    
	    $response = file_get_contents($this->data['syncURL'] . "?key=" . $this->data['syncSecret'] . "&action=getAllUsers&syncName=" . $this->data['syncName'], false, stream_context_create($arrContextOptions));
	    
	    
	    // $response = utf8_decode($response);
	    
	    $data = simplexml_load_string($response);
	    
	    
	    if(!is_object($data)) {
	        $data = simplexml_load_string(utf8_decode($response));
	    }
	    
	    
	   //  $data = simplexml_load_file($this->data['syncURL'] . "?key=" . $this->data['syncSecret'] . "&action=getAllUsers&syncName=" . $this->data['syncName'], "SimpleXMLElement");

	    
		if(isset($data->error)) {
			return strval($data->error);
		}
		
		return $data;
	}
	
	public function getAllUsersRaw() {
	    $arrContextOptions=array(
	        "ssl"=>array(
	            "verify_peer"=>false,
	            "verify_peer_name"=>false,
	        ),
	    );
	    	    
	    
	    $response = file_get_contents($this->data['syncURL'] . "?key=" . $this->data['syncSecret'] . "&action=getAllUsers&syncName=" . $this->data['syncName'], false, stream_context_create($arrContextOptions));
	    
	    
	    return $response;
	}
	
	public function checkPassword($username,$password) {
		
		if($this->data['syncLoginDomain'] != "") {
			$username = str_replace('@' . $this->data['syncLoginDomain'], "", $username);
		}
		
		$username = ($username);
		$password = ($password);
		

		$username = base64_encode($username);
		$password = base64_encode($password);
		
		$data = @simplexml_load_file($this->data['syncURL'] . "?key=" . $this->data['syncSecret'] . "&action=checkPassword&syncName=" . $this->data['syncName'] . "&username=" . urlencode($username) . "&password=" . urlencode($password));
		
		if(isset($data->error)) {
			return false;
		}
		
		if(isset($data->checkpassword->result) && $data->checkpassword->result > 0) {
			return true;
		}
	
		return false;
	}
	
	public function getDirectoryType() {
		return $this->data['syncDirType'];
	}
		
}


?>