<?php

class AdminWidgets extends AbstractPage {


	
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
		return 'Widgets';
	}

    public static function getAdminGroup() {
        return 'Webportal_Wdigets_Admin';
    }



	public static function displayAdministration($selfURL) {

		$html = '';



/*
        echo '<pre>';
        print_r($ret);
        echo '</pre>';
        */

        /**
         * Item Submit
         */
        if ($_REQUEST['task'] == 'item-submit') {

            header('Content-Type: application/json');
            http_response_code(200);

            if (!$_POST['id']) {
                echo json_encode(['error' => true, 'msg' => 'Missing UniqID']); exit;
            }
            if (!$_POST['uniqid']) {
                echo json_encode(['error' => true, 'msg' => 'Missing Uniq']); exit;
            }
            if ( (int)$_REQUEST['id'] !== (int)$_POST['id']) {
                echo json_encode(['error' => true, 'msg' => 'Wrong ID']); exit;
            }

            $result = DB::getDB()->query_first('SELECT * FROM `widgets` WHERE id = '.(int)$_POST['id']);

            if ($result['id']) {

                if ( DB::getDB()->query("UPDATE widgets SET 
                         access='" . DB::getDB()->escapeString($_POST['access']) . "'
                         WHERE id='" . (int)$_POST['id'] . "'") ) {
                    echo json_encode(['error' => false]);
                    exit;
                }

            } else {

                if ( DB::getDB()->query("INSERT INTO `widgets` (
							`uniqid`,
							`position`,
                             `access`
							) VALUES (
								'".DB::getDB()->escapeString((string)$_POST['uniqid'])."',
								'".DB::getDB()->escapeString((string)$_POST['position'])."',
								'".DB::getDB()->escapeString((string)$_POST['access'])."'
						);") ) {
                    echo json_encode(['error' => false]);
                    exit;
                }

            }

            echo json_encode(['error' => true, 'msg' => 'ERROR!']);
            exit;
        }


        /**
         * TOOGLE
         */
        if ($_REQUEST['task'] == 'api-toggle-active') {

            header('Content-Type: application/json');
            http_response_code(200);

            if ($_REQUEST['uniqid']) {
                $extension = DB::getDB()->query_first("SELECT `id` FROM widgets WHERE `uniqid` = '".$_REQUEST['uniqid']."'" );
                if ($extension['id']) {
                    // REMOVE
                    DB::getDB()->query("DELETE FROM widgets WHERE `id` = '".$extension['id']."'" );
                    echo json_encode(['error' => false]);
                    exit;
                } else {
                    // INSERT
                    DB::getDB()->query("INSERT INTO `widgets` (
							`uniqid`,
							`position`,
							`access`
							) VALUES (
								'".DB::getDB()->escapeString($_REQUEST['uniqid'])."',
								'".DB::getDB()->escapeString($_REQUEST['position'])."',
								'".DB::getDB()->escapeString($_REQUEST['access'])."'
						);");
                    echo json_encode(['error' => false]);
                    exit;
                }
            }
            echo json_encode(['error' => true, 'msg' => 'Strange Error!']);
            exit;
        }


		/**
		 * GET WIDGETS
		 */
		if ($_REQUEST['task'] == 'api-list') {

            header('Content-Type: application/json');
            http_response_code(200);

            $ret = [];
            $db = [];
            $result = DB::getDB()->query('SELECT * FROM `widgets` ');
            while($row = DB::getDB()->fetch_array($result, true)) {
                $db[] = $row;
            }
            $folders = FILE::getFilesInFolder(PATH_EXTENSIONS);
            foreach($folders as $folder) {
                $extPath = PATH_EXTENSIONS.$folder['filename'];
                if (is_dir($extPath)) {
                    $json = FILE::getExtensionJSON($extPath.DS.'extension.json');
                    if ($json['widgets']) {
                        $arr = [
                            "title" => $json['name'],
                            "widgets" => []
                        ];
                        foreach($json['widgets'] as $widget) {
                            $widget->status = 0;
                            $widget->access = [
                                "admin" => 0,
                                "adminGroup" => 0,
                                "teacher" => 0,
                                "pupil" => 0,
                                "parents" => 0,
                                "other" => 0
                            ];
                            // Find in DB
                            foreach($db as $dbItem) {
                                if ($dbItem['uniqid'] == $widget->uniqid ) {
                                    $widget->status = 1;
                                    if ($dbItem['access']) {
                                        $widget->access = json_decode($dbItem['access']);
                                    }
                                    $widget->id = $dbItem['id'];
                                }
                            }
                            $arr['widgets'][] = $widget;
                        }
                        $ret[] = $arr;
                    }
                }
            }
			echo json_encode($ret);
			exit;
		}


		eval("\$html = \"" . DB::getTPL()->get("administration/widgets/list") . "\";");

		return $html;
	}


}


?>