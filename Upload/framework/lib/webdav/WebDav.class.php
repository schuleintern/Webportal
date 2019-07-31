<?php


use Sabre\DAV\Client;

include_once("../framework/lib/webdav/SabreDav/vendor/autoload.php");

class WebDav {
	public static function createFolder($server, $username, $password, $foldername) {
		$client = self::getWebdavClient($server, $username, $password);
		if($client != null) {
			$result = $client->request("MKCOL", $foldername);
			
			print_r($result);
			
			return $result['statusCode'] == 201;
		}
		
		return false;
	}
	
	private static function getWebdavClient($server, $username, $password) {
		$settings = array(
				'baseUri' => $server . '/remote.php/dav/files/' . $username . '/',
				'userName' => $username,
				'password' => $password
		);
		
		$client = new Client($settings);
		return $client;
	}
}

