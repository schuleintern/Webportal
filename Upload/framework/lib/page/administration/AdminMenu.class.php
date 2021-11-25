<?php

class AdminMenu extends AbstractPage {

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
		return 'Menu';
	}
    public static function getAdminMenuIcon() {
        return 'fa fas fa-ellipsis-v';
    }

    public static function getAdminMenuGroupIcon() {
        return 'fa fas fa-bars';
    }

    public static function getAdminMenuGroup() {
        return 'Navigation';
    }

    public static function hasAdmin() {
        return true;
    }

	public static function displayAdministration($selfURL) {


        /**
         * All
         */
        if ($_REQUEST['task'] == 'api-all') {

            $menuAll =  Menue::getAll();

            /*echo '<pre>';
            print_r($menuAll);
            echo '</pre>';*/

            echo json_encode($menuAll); exit;
            exit;
        }

        /**
         * All Items
         */
        if ($_REQUEST['task'] == 'api-items') {

            if (!$_REQUEST['id']) {
                return false;
            }
            $menu =  Menue::getFromAlias($_REQUEST['id']);
            $menuAll =  $menu->getItemsDeep(false);

            /*echo '<pre>';
            print_r($menuAll);
            echo '</pre>';*/

            echo json_encode($menuAll); exit;
            exit;
        }

        /**
         * Item Submit
         */
        if ($_REQUEST['task'] == 'item-submit') {

            if (!$_POST['id']) {
                echo json_encode(['error' => true, 'msg' => 'Missing UniqID']); exit;
            }
            if (!$_POST['title']) {
                echo json_encode(['error' => true, 'msg' => 'Missing Title']); exit;
            }
            if ( (int)$_REQUEST['id'] !== (int)$_POST['id']) {
                echo json_encode(['error' => true, 'msg' => 'Wrong ID']); exit;
            }
            if ( Menue::setItem([
                "id" => $_POST['id'],
                "title" => $_POST['title'],
                "icon" => $_POST['icon'],
                "params" => $_POST['params'],
                "page" => $_POST['pageurl'],
                "parent_id" => $_POST['parent_id'],
                "access" => $_POST['access']
            ]) ) {
                echo json_encode(['error' => false]); exit;
            }

            echo json_encode(['error' => true, 'msg' => 'ERROR!']);
            exit;
        }

        /**
         * Item Delete
         */
        if ($_REQUEST['task'] == 'item-delete') {

            if (!$_POST['id']) {
                echo json_encode(['error' => true, 'msg' => 'Missing UniqID']); exit;
            }

            if ( Menue::removeItem($_POST['id']) ) {
                echo json_encode(['error' => false]); exit;
            }

            echo json_encode(['error' => true, 'msg' => 'ERROR!']);
            exit;
        }


        /**
         * Item Active
         */
        if ($_REQUEST['task'] == 'item-active') {

            if (!$_POST['id']) {
                echo json_encode(['error' => true, 'msg' => 'Missing UniqID']); exit;
            }

            if ($_POST['active'] == 1) {
                $active = 0;
            } else {
                $active = 1;
            }
            if ( MenueItems::setItemActive([
                "id" => $_POST['id'],
                "active" => $active
            ]) ) {
                echo json_encode(['error' => false, 'active' => $active]); exit;
            }

            echo json_encode(['error' => true, 'msg' => 'ERROR!']);
            exit;
        }

        /**
         * Item Sort
         */
        if ($_REQUEST['task'] == 'item-sort') {

            if (!$_POST['items']) {
                echo json_encode(['error' => true, 'msg' => 'Missing Data']); exit;
            }

            if ( MenueItems::setItemsSort( json_decode($_POST['items']) ) ) {
                echo json_encode(['error' => false]); exit;
            }

            echo json_encode(['error' => true, 'msg' => 'ERROR!']);
            exit;
        }



        $html = '';

        include_once ('../framework/lib/data/extensions/ExtensionsPages.php');
        $pages = json_encode(ExtensionsPages::getPages());
        
		eval("\$html = \"" . DB::getTPL()->get("administration/menu/index") . "\";");

		return $html;
	}

}


?>