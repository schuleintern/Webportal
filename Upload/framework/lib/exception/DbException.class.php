<?php

class dbException extends Exception {
	public function __construct($message) {
		new errorPage($message);
		exit(0);
	}
}

?>