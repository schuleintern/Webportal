<?php

class AdminExtensions extends AbstractPage {

	private $info;
	
	private $adminGroup = 'Webportal_Administrator';
	
	
	public function __construct() {
		die();	
	}

	public function execute() {
			new errorPage();
	}

	public static function siteIsAlwaysActive() {
		return true;
	}

	public static function hasSettings() {
		return false;
	}

	public static function getSettingsDescription() {
		return array();
	}
	
	public static function getSiteDisplayName() {
		return 'Erweiterungen';
	}



	public static function displayAdministration($selfURL) {

		$html = '';

		$pathExtensions    = PATH_EXTENSIONS; //'../extensions/';
		$extensionsServer = DB::getGlobalSettings()->extensionsServer;

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

		/**
		 * UPDATE
		 */
		if ($_REQUEST['task'] == 'update') {
			if ($_REQUEST['uniqid']) {

				$extension = DB::getDB()->query_first("SELECT `id`,`version` FROM extensions WHERE `uniqid` = '".$_REQUEST['uniqid']."'" );
				if (!$extension['version']) {
					$retun = ['error' => true, 'msg' => 'Missing Extension'];
					echo json_encode($retun); exit;
				}

				$extStore = file_get_contents($extensionsServer."extensions/".$_REQUEST['uniqid']);
				if ($extStore) {
					$extStore = json_decode($extStore);
				}

				if (!$extStore || !$extStore->version || !$extStore->url || $extStore->url == '') {
					$retun = ['error' => true, 'msg' => 'Missing URL'];
					echo json_encode($retun); exit;
				}
				
				if ( intval($extension['version']) >= intval($extStore->version)) {
					$retun = ['error' => true, 'msg' => 'Nothing to Update'];
					echo json_encode($retun); exit;
				}

				$filename = uniqid(rand(), true) . '.zip';

				file_put_contents("../tmp/".$filename, fopen($extStore->url, 'r'));

				$zip = new ZipArchive;
				if ($zip->open("../tmp/".$filename) === TRUE) {

					$foldername = substr($zip->getNameIndex(0), 0, -1);

					if (file_exists($pathExtensions.$foldername)) {
						FILE::removeFolder($pathExtensions.$foldername);
					}

					if ( !$zip->extractTo($pathExtensions) ) {
						unlink("../tmp/".$filename);
						$retun = ['error' => true, 'msg' => 'Error Unpack'];
						echo json_encode($retun); exit;
					}
					$zip->close();

					unlink("../tmp/".$filename);

					// Get Extension JSON
					if ( file_exists($pathExtensions.$foldername.'/extension.json') ) {

						$modulJSON = json_decode( file_get_contents($pathExtensions.$foldername.'/extension.json') );

						if ( !$modulJSON->name || !$modulJSON->version || !$modulJSON->uniqid  ) {
							FILE::removeFolder($pathExtensions.$foldername);
							$retun = ['error' => true, 'msg' => 'Missing Data'];
							echo json_encode($retun); exit;
						}

						// Schule-intern is needed Version ???
						if ( $modulJSON->requiredVersion ) {
							if ( version_compare($modulJSON->requiredVersion, DB::getVersion(), '>') ) {
								FILE::removeFolder($pathExtensions.$foldername);
								$retun = ['error' => true, 'msg' => 'System has wrong Version'];
								echo json_encode($retun); exit;
							}
						}


						// Install Extension DB
						if ( file_exists($pathExtensions.$foldername.'/install/database.sql') ) {
							$sql = file_get_contents($pathExtensions.$foldername.'/install/database.sql');
							$sqlCommands = explode(';', $sql);
							foreach($sqlCommands as $foo) {
								$foo = trim($foo);
								if ($foo) {
									DB::getDB()->query($foo);
								}
							}
						}

						// Update Extension
						DB::getDB()->query("UPDATE `extensions` SET 
							`name` = '".$modulJSON->name."',
							`folder` = '".$foldername."',
							`version` = '".$modulJSON->version."'
							WHERE `uniqid` =  '".$_REQUEST['uniqid']."'");


						$retun = ['error' => false];
						echo json_encode($retun); exit;
							
						
					} else {
						$retun = ['error' => true, 'msg' => 'Misson Extension JSON'];
						echo json_encode($retun); exit;
					}


				} else {
					$retun = ['error' => true, 'msg' => 'Error Zip Open'];
					echo json_encode($retun); exit;
				}

			} else {
				$retun = ['error' => true, 'msg' => 'Missing UniqID'];
				echo json_encode($retun); exit;
			}

			exit;
		}


		/**
		 * INSTALL
		 */
		if ($_REQUEST['task'] == 'install') {
			if ($_REQUEST['uniqid']) {

				//$extStore = file_get_contents($extensionsServer."extensions/".$_REQUEST['uniqid']);
                $extStore = file_get_contents($extensionsServer."extensions/".$_REQUEST['uniqid'], false, stream_context_create($arrContextOptions));
				if ($extStore) {
					$extStore = json_decode($extStore);
				}

				if (!$extStore || !$extStore->url || $extStore->url == '') {
					$retun = ['error' => true, 'msg' => 'Missing URL'];
					echo json_encode($retun); exit;
				}

				$filename = uniqid(rand(), true) . '.zip';

				file_put_contents("../tmp/".$filename, file_get_contents($extStore->url, false, stream_context_create($arrContextOptions)));

                if (!file_exists("../tmp/".$filename)) {
                    unlink("../tmp/".$filename);
                    $retun = ['error' => true, 'msg' => 'Missing Donwload Zip.'];
                    echo json_encode($retun); exit;
                }

				$zip = new ZipArchive;
				if ($zip->open("../tmp/".$filename) === TRUE) {

					$foldername = substr($zip->getNameIndex(0), 0, -1);

					if (file_exists($pathExtensions.$foldername)) {
						unlink("../tmp/".$filename);
						$retun = ['error' => true, 'msg' => 'Extension still exist.'];
						echo json_encode($retun); exit;
					}

					if ( !$zip->extractTo($pathExtensions) ) {
						unlink("../tmp/".$filename);
						$retun = ['error' => true, 'msg' => 'Error Unpack'];
						echo json_encode($retun); exit;
					}
					$zip->close();

					unlink("../tmp/".$filename);

					// Get Extension JSON
					if ( file_exists($pathExtensions.$foldername.'/extension.json') ) {

						$modulJSON = json_decode( file_get_contents($pathExtensions.$foldername.'/extension.json') );

						if ( !$modulJSON ) {
							FILE::removeFolder($pathExtensions.$foldername);
							$retun = ['error' => true, 'msg' => 'Missing extension.json Data'.$pathExtensions.$foldername.'/extension.json'];
							echo json_encode($retun); exit;
						}

						if ( !$modulJSON->name || !$modulJSON->version || !$modulJSON->uniqid  ) {
							FILE::removeFolder($pathExtensions.$foldername);
							$retun = ['error' => true, 'msg' => 'Missing JSON Data'];
							echo json_encode($retun); exit;
						}

						// Schule-intern is needed Version ???
						if ( $modulJSON->requiredVersion ) {
							if ( version_compare($modulJSON->requiredVersion, DB::getVersion(), '>') ) {
								FILE::removeFolder($pathExtensions.$foldername);
								$retun = ['error' => true, 'msg' => 'System has wrong Version'];
								echo json_encode($retun); exit;
							}
						}

						// Install Extension DB
						if ( file_exists($pathExtensions.$foldername.'/install/database.sql') ) {
							$sql = file_get_contents($pathExtensions.$foldername.'/install/database.sql');
							$sqlCommands = explode(';', $sql);
							foreach($sqlCommands as $foo) {
								$foo = trim($foo);
								if ($foo) {
									DB::getDB()->query($foo);
								}
							}
						}

						// Insert and Activate Extension
						DB::getDB()->query("INSERT INTO `extensions` (
							`name`,
							`active`,
							`folder`,
							`version`,
							`uniqid`,
							`menuCat`
							) VALUES (
								'".$modulJSON->name."',
								1,
								'".$foldername."',
								".$modulJSON->version.",
								'".$modulJSON->uniqid."',
								'".$modulJSON->menuCat."'
						);");
						
						$retun = ['error' => false];
						echo json_encode($retun); exit;
							
						
					} else {
						FILE::removeFolder($pathExtensions.$foldername);
						$retun = ['error' => true, 'msg' => 'Missing Extension JSON'];
						echo json_encode($retun); exit;
					}


				} else {
					$retun = ['error' => true, 'msg' => 'Error Zip Open'];
					echo json_encode($retun); exit;
				}

			} else {
				$retun = ['error' => true, 'msg' => 'Missing UniqID'];
				echo json_encode($retun); exit;
			}

			exit;
		}

		/**
		 * REMOVE
		 */
		if ($_REQUEST['task'] == 'remove') {
			if ($_REQUEST['uniqid']) {

				$extension = DB::getDB()->query_first("SELECT `id`,`folder` FROM extensions WHERE `uniqid` = '".$_REQUEST['uniqid']."'" );
				if ($extension['folder'] && file_exists($pathExtensions.$extension['folder']) ) {

					FILE::removeFolder($pathExtensions.$extension['folder']);
					DB::getDB()->query("DELETE FROM `extensions` WHERE `uniqid` = '".$_REQUEST['uniqid']."'" );

					$retun = ['error' => false];
					echo json_encode($retun); exit;
				} else {
					$retun = ['error' => true, 'msg' => 'Missing Folder'];
					echo json_encode($retun); exit;
				}

			} else {
				$retun = ['error' => true, 'msg' => 'Missing UniqID'];
				echo json_encode($retun); exit;
			}
		}


		/**
		 * TOGGLE ACTIVE
		 */
		if ($_REQUEST['task'] == 'toggleActive') {
			if ($_REQUEST['uniqid']) {

				$extension = DB::getDB()->query_first("SELECT `id`,`active` FROM extensions WHERE `uniqid` = '".$_REQUEST['uniqid']."'" );
				if ($extension['id']) {

					if ($extension['active'] == 1) {
						$active = 0;
					} else {
						$active = 1;
					}

					DB::getDB()->query("UPDATE `extensions` SET 
							`active` = '".$active."'
							WHERE `uniqid` =  '".$_REQUEST['uniqid']."'");
					
					$retun = ['error' => false];
					echo json_encode($retun); exit;


				} else {
					$retun = ['error' => true, 'msg' => 'Missing Extension'];
					echo json_encode($retun); exit;
				}

			} else {
				$retun = ['error' => true, 'msg' => 'Missing UniqID'];
				echo json_encode($retun); exit;
			}
		}


        $extStore = file_get_contents($extensionsServer."extensions.json", false, stream_context_create($arrContextOptions));
        if (!$extStore) {
            $extStore = "false";
        }

		/**
		 * GET INSTALLED EXTENSIONS
		 */
		if ($_REQUEST['task'] == 'api-extensions') {

			$extAvailable = json_decode($extStore);

			$extInstalled = array();
			$result = DB::getDB()->query('SELECT `name`,`active`,`uniqid`,`version` FROM `extensions` ');
			while($row = DB::getDB()->fetch_array($result)) {

				if ( self::checkUpdate($extAvailable, $row) ) {
					$row['update'] = true;
				}
				$extInstalled[] = $row;

			}
			header('Content-Type: application/json');
			echo json_encode($extInstalled);
			exit;
		}


		eval("\$html = \"" . DB::getTPL()->get("administration/extensions/list") . "\";");

		return $html;
	}

	static function checkUpdate($arr, $row ) {

		foreach($arr as $ext) {
			if ($ext->uniqid == $row['uniqid']) {
				if ( intval($ext->version) > intval($row['version']) ) {
					return true;
				}
			}
		}
		return false;

	}


}


?>