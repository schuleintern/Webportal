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

            $menu =  Menue::getFromAlias('main');
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

            if ( Menue::setItem([
                "id" => $_POST['id'],
                "title" => $_POST['title'],
                "icon" => $_POST['icon'],
                "params" => $_POST['params']
            ]) ) {
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



        $html = '';
		eval("\$html = \"" . DB::getTPL()->get("administration/menu/index") . "\";");

		return $html;
	}

}


?>