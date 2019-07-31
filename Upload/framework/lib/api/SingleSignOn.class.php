<?php

/**
 * @deprecated Do not use.
 */
class SingleSignOn extends AbstractApi {	
	public function __construct() {
	}
	
	public function execute() {
		error_reporting(E_ERROR);
		
		header("Content-type: text/xml");
				
		echo('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
		
		
		echo("<schuleintern>");
		
		if($_GET['action'] == "GetLoginKey") {
			$user = DB::getDB()->escapeString($_GET['username']);
			
			$user = DB::getDB()->query_first("SELECT * FROM users WHERE userName LIKE '" . $user . "'");
			if($user['userID'] > 0) {
				$loginkey = sha1(rand()) . sha1(rand()) . sha1(rand());
				
				DB::getDB()->query("INSERT INTO singlesignon_loginkeys (loginkeyKey, loginKeyValidUntil, loginkeyUserID) values('" . $loginkey . "','" . (time()+10) . "','" . $user['userID'] . "')");
				
				echo("<username>" . $user['userName'] . "</username>");
				echo("<loginkey>" . $loginkey . "</loginkey>");
				echo("<valid>10</valid>");
			}
			else {
				echo("<error>Unknown user</error>");
				echo("<errorid>2</errorid>");
			}
			
		}
		else {
			echo("<error>Unknown action</error>");
			echo("<errorid>1</errorid>");
		}
		
		
		
		echo("</schuleintern>");
		exit(0);
		
		
	}
}



?>