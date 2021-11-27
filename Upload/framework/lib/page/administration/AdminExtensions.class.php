<?php

class AdminExtensions extends AbstractPage {

	private $info;
	

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

    public static function getAdminGroup() {
        return 'Webportal_Extensions_Admin';
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
         * UPLOAD AND INSTALL
         */
        if ($_REQUEST['task'] == 'uploadInstall') {

            $uploadfile = PATH_TMP . 'upload-extension.zip';
            @unlink($uploadfile);

            if ( !move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                $retun = ['error' => true, 'msg' => 'Error: Upload'];
                echo json_encode($retun); exit;
            }

            if ( !file_exists($uploadfile) ) {
                $retun = ['error' => true, 'msg' => 'Error: Missing File'];
                echo json_encode($retun); exit;
            }

            $retun = self::unpackAndInstallZip('upload-extension.zip');
            echo json_encode($retun); exit;
        }


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

				//$extStore = file_get_contents($extensionsServer."extensions/".$_REQUEST['uniqid']);
                $extStore = file_get_contents($extensionsServer."extensions/".$_REQUEST['uniqid'].'?task=update', false, stream_context_create($arrContextOptions));

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

				//file_put_contents(PATH_TMP.$filename, fopen($extStore->url, 'r'), false, stream_context_create($arrContextOptions));


                file_put_contents(PATH_TMP.$filename, file_get_contents($extStore->url, false, stream_context_create($arrContextOptions)));

                if (!file_exists(PATH_TMP.$filename)) {
                    unlink(PATH_TMP.$filename);
                    $retun = ['error' => true, 'msg' => 'Missing Donwload Zip.'];
                    echo json_encode($retun); exit;
                }


				$zip = new ZipArchive;
				if ($zip->open(PATH_TMP.$filename) === TRUE) {

					$foldername = substr($zip->getNameIndex(0), 0, -1);

                    if (!$foldername) {
                        $retun = ['error' => true, 'msg' => 'Missing Foldername'];
                        echo json_encode($retun); exit;
                    }

					if (file_exists($pathExtensions.$foldername)) {
						FILE::removeFolder($pathExtensions.$foldername);
					}

					if ( !$zip->extractTo($pathExtensions) ) {
						unlink(PATH_TMP.$filename);
						$retun = ['error' => true, 'msg' => 'Error Unpack'];
						echo json_encode($retun); exit;
					}
					$zip->close();

                    if (file_exists(PATH_TMP.$filename)) {
                        unlink(PATH_TMP . $filename);
                    }

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
                $extStore = file_get_contents($extensionsServer."extensions/".$_REQUEST['uniqid'].'?task=install', false, stream_context_create($arrContextOptions));
				if ($extStore) {
					$extStore = json_decode($extStore);
				}

				if (!$extStore || !$extStore->url || $extStore->url == '') {
					$retun = ['error' => true, 'msg' => 'Missing URL'];
					echo json_encode($retun); exit;
				}

				$filename = uniqid(rand(), true) . '.zip';

                if ( !is_dir(PATH_TMP)) {
                    mkdir(PATH_TMP);
                }
				file_put_contents(PATH_TMP.$filename, file_get_contents($extStore->url, false, stream_context_create($arrContextOptions)));

                if (!file_exists(PATH_TMP.$filename)) {
                    unlink(PATH_TMP.$filename);
                    $retun = ['error' => true, 'msg' => 'Missing Donwload Zip.'];
                    echo json_encode($retun); exit;
                }

                $retun = self::unpackAndInstallZip($filename);
                echo json_encode($retun); exit;

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

                    // TODO: DELETE ext_database_tabeles ?
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

				$extension = DB::getDB()->query_first("SELECT `id`,`active`,`folder` FROM extensions WHERE `uniqid` = '".$_REQUEST['uniqid']."'" );
				if ($extension['id']) {

					if ($extension['active'] == 1) {
						$active = 0;
					} else {
						$active = 1;
					}
                    $extensionJSON = self::getExtensionJSON(PATH_EXTENSIONS.$extension['folder'].DS.'extension.json');
                    if ($extensionJSON->dependencies) {
                        foreach ($extensionJSON->dependencies as $dep) {
                            $extension_dep = DB::getDB()->query_first("SELECT `id`,`active` FROM extensions WHERE `uniqid` = '".$dep."'" );
                            if ($extension_dep['active'] != 1) {
                                $retun = ['error' => true, 'msg' => 'Dependencies: Missing Extension - ('.$dep.')'];
                                echo json_encode($retun); exit;
                            }
                        }
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





		/**
		 * GET INSTALLED EXTENSIONS
		 */
		if ($_REQUEST['task'] == 'api-extensions') {

            $extStore = file_get_contents($extensionsServer."extensions.json?task=checkVersion", false, stream_context_create($arrContextOptions));
			$extAvailable = json_decode($extStore);

			$extInstalled = array();
			$result = DB::getDB()->query('SELECT `name`,`active`,`uniqid`,`version`,`folder` FROM `extensions` ');
			while($row = DB::getDB()->fetch_array($result)) {

				if ( self::checkUpdate($extAvailable, $row) ) {
					$row['update'] = true;
				}
                $row['json'] = self::getExtensionJSON(PATH_EXTENSIONS.$row['folder'].DS.'extension.json');
				$extInstalled[] = $row;

			}
			header('Content-Type: application/json');
			echo json_encode($extInstalled);
			exit;
		}


        $extStore = file_get_contents($extensionsServer."extensions.json?task=list", false, stream_context_create($arrContextOptions));
        if (!$extStore) {
            $extStore = "false";
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

    static function unpackAndInstallZip($filename) {

        $zip = new ZipArchive;
        if ($zip->open(PATH_TMP.$filename) === TRUE) {

            $foldername = substr($zip->getNameIndex(0), 0, -1);

            if (file_exists(PATH_EXTENSIONS.$foldername)) {
                unlink(PATH_TMP.$filename);
                return ['error' => true, 'msg' => 'Extension still exist.'];
            }

            if ( !$zip->extractTo(PATH_EXTENSIONS) ) {
                unlink(PATH_TMP.$filename);
                return ['error' => true, 'msg' => 'Error Unpack'];
            }
            $zip->close();

            unlink(PATH_TMP.$filename);

            // Get Extension JSON
            if ( file_exists(PATH_EXTENSIONS.$foldername.'/extension.json') ) {

                $modulJSON = json_decode( file_get_contents(PATH_EXTENSIONS.$foldername.'/extension.json') );

                if ( !$modulJSON ) {
                    FILE::removeFolder(PATH_EXTENSIONS.$foldername);
                    return ['error' => true, 'msg' => 'Missing extension.json Data'.PATH_EXTENSIONS.$foldername.'/extension.json'];
                }

                if ( !$modulJSON->name || !$modulJSON->version || !$modulJSON->uniqid  ) {
                    FILE::removeFolder(PATH_EXTENSIONS.$foldername);
                    return ['error' => true, 'msg' => 'Missing JSON Data'];
                }

                // Schule-intern is needed Version ???
                if ( $modulJSON->requiredVersion ) {
                    if ( version_compare($modulJSON->requiredVersion, DB::getVersion(), '>') ) {
                        FILE::removeFolder(PATH_EXTENSIONS.$foldername);
                        return ['error' => true, 'msg' => 'System has wrong Version'];
                    }
                }

                // Install Extension DB
                if ( file_exists(PATH_EXTENSIONS.$foldername.'/install/database.sql') ) {
                    $sql = file_get_contents(PATH_EXTENSIONS.$foldername.'/install/database.sql');
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
							`uniqid`
							) VALUES (
								'".$modulJSON->name."',
								0,
								'".$foldername."',
								".$modulJSON->version.",
								'".$modulJSON->uniqid."'
						);");

                // $modulJSON->menu->categorie
                MenueItems::setItem([
                    "title" => $modulJSON->name,
                    "page" => 'ext_'.$foldername,
                    "menu_id" => 0,
                    "parent_id" => $modulJSON->menu->categorie || 0,
                    "icon" => $modulJSON->menu->icon | '',
                    "params" => $modulJSON->menu->params || '',
                    "active" => 0,
                    "access" => '{"admin":1,"adminGroup":0,"teacher":1,"pupil":1,"parents":1,"other":1}'
                ]);

                return ['error' => false];


            } else {
                FILE::removeFolder(PATH_EXTENSIONS.$foldername);
                return ['error' => true, 'msg' => 'Missing Extension JSON'];
            }


        } else {
            return ['error' => true, 'msg' => 'Error Zip Open'];
        }


    }


}


?>