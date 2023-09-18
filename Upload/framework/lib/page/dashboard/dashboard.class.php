<?php


class dashboard extends AbstractPage
{


    public function __construct()
    {

        parent::__construct(array("Dashboard", "Dashboard"));
        $this->checkLogin();

    }

    public function execute()
    {

        $script = '';
        $list = [];
        $result = DB::getDB()->query('SELECT a.id, a.param, a.uniqid, b.access
            FROM dashboard as a 
            LEFT JOIN widgets as b ON a.widget_id = b.id
            WHERE a.user_id = "default" ');

        while ($row = DB::getDB()->fetch_array($result, true)) {
            if ($row['id']) {

                $userTyp = DB::getSession()->getUser()->getUserTyp(true);


                $ok = false;
                if ( DB::getSession()->getUser()->isAdmin() ) {
                    $userTyp = 'isAdmin';
                    $ok = true;
                }


                $access = json_decode($row['access']);
                if ($access) {
                    if ($access->pupil == 1 && $userTyp == 'isPupil') {
                        $ok = true;
                    }
                    if ($access->parents == 1 && $userTyp == 'isEltern') {
                        $ok = true;
                    }
                    if ($access->other == 1 && $userTyp == 'isNone') {
                        $ok = true;
                    }
                    if ($access->teacher == 1 && $userTyp == 'isTeacher') {
                        $ok = true;
                    }
                    if ($access->admin == 1 && $userTyp == 'isAdmin') {
                        $ok = true;
                    }
                }


                $temp = json_decode($row['param']);
                $temp->i = $row['id'];
                $temp->uniqid = $row['uniqid'];
                $temp->html = '';

                $uidClass = explode('.', $temp->uniqid);
                if ($uidClass[0] && $uidClass[1]) {

                    if ( $ok == false && $access->adminGroup == 1 ) {
                        $ok = false;
                        $json = FILE::getExtensionJSON(PATH_EXTENSIONS . $uidClass[0] . DS . 'extension.json');
                        if ($json['adminGroupName'] && DB::getSession()->getUser()->isMember($json['adminGroupName']) ) {
                            $ok = true;
                        }
                    }
                    if ($ok) {
                        $filepath = PATH_EXTENSIONS . $uidClass[0] . DS . 'widgets' . DS . $uidClass[1] . DS . 'widget.php';
                        if (file_exists($filepath)) {
                            include_once $filepath;
                            $className = 'ext' . ucfirst($uidClass[0]) . 'Widget' . ucfirst($uidClass[1]);


                            $class = new $className([
                                "path" => PATH_EXTENSIONS . $uidClass[0]
                            ]);

                            if ($class && method_exists($class, 'render')) {
                                $temp->html = $class->render(true);
                                if (method_exists($class, 'getScriptData')) {
                                    $varname = 'globals_widget_' . $uidClass[0] . '_' . $uidClass[1];
                                    $script .= $this->getScriptData($class->getScriptData(), $varname);
                                }
                                if (method_exists($class, 'getScripts')) {
                                    $script .= FILE::getScripts($class->getScripts());
                                }
                            }

                        }
                        $list[] = $temp;
                    }
                }
            }
        }


        $list = json_encode($list);

        eval("echo(\"" . DB::getTPL()->get("dashboard/index") . "\");");

    }


    public static function hasSettings()
    {
        return false;
    }

    public static function getSettingsDescription()
    {
        return false;
    }


    public static function getSiteDisplayName()
    {
        return 'Dashboard';
    }

    public static function siteIsAlwaysActive()
    {
        return false;
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups()
    {
        return array();

    }

    public static function hasAdmin()
    {
        return true;
    }

    public static function getAdminGroup()
    {
        return false;
        //return 'Webportal_Klassenlisten_Admin';
    }


    public static function getAdminMenuGroup()
    {
        return 'Seiteneinstellungen';
    }


    public static function getAdminMenuIcon()
    {
        return 'fa fa-th';
    }


    public static function displayAdministration($selfURL)
    {

        /**
         * REMOVE WIDGET
         */
        if ($_REQUEST['task'] == 'removeWidget') {

            $id =  (int)$_REQUEST['id'];
            if ($id) {
                DB::getDB()->query("DELETE FROM dashboard WHERE id = $id");
                echo json_encode(['error' => false]);
                exit;
            } else {
                echo json_encode(['error' => true, 'msg' => 'Error with ID!']);
                exit;
            }

            echo json_encode(['error' => true, 'msg' => 'Error!']);
            exit;

        }


        /**
         * ADD WIDGET
         */
        if ($_REQUEST['task'] == 'addWidget') {

            header('Content-Type: application/json');
            http_response_code(200);

            $param = (object)[ "x" => 0, "y" => 0, "w" => 1, "h" => 1 ];

            $uniqid = trim($_REQUEST['uniqid']);
            $widget_id = (int)trim($_REQUEST['wid']);
            if ($uniqid && $uniqid != 'undefined' && $widget_id ) {

                $title = '';
                $extPath = explode('.', $_REQUEST['uniqid'] );
                if ($extPath[0]) {
                    $json = FILE::getExtensionJSON(PATH_EXTENSIONS.$extPath[0].DS.'extension.json');
                    if ($json['widgets']) {
                        foreach ($json['widgets'] as $widget) {
                            if ($widget->uniqid == $uniqid) {
                                $param->minW = $widget->params->minW;
                                $param->minH = $widget->params->minH;
                                $param->w = $param->minW;
                                $param->h = $param->minH;
                                $title = $widget->title;
                            }
                        }
                    }
                }



                $paramStr = json_encode($param);

                DB::getDB()->query("INSERT INTO `dashboard` (
                            `title`,
                            `uniqid`,
                            `user_id`,
                            `param`,
                            `widget_id`
                            ) VALUES (
                                '".$title."',
                                '" . DB::getDB()->escapeString($uniqid) . "',
                                'default',
                                '".$paramStr."',
                                ".(int)DB::getDB()->escapeString($widget_id)."
                        );");
                $id = DB::getDB()->insert_id();

                $widgetDB = DB::getDB()->query_first('SELECT access FROM `widgets` WHERE `id` = '.$widget_id, true);
                $param->access = json_decode($widgetDB['access']);

                echo json_encode(['error' => false, 'id' => $id, 'uniqid' => $uniqid, 'title' => $title,'param'=> $param ]);
                exit;

            }
            echo json_encode(['error' => true, 'msg' => 'Error with ID!']);
            exit;
        }

        /**
         * SAVE LAYOUT
         */
        if ($_REQUEST['task'] == 'saveLayout') {

            header('Content-Type: application/json');
            http_response_code(200);

            if ($_POST['layout']) {
                $layout = json_decode($_POST['layout']);
            }
            if (!$layout) {
                echo json_encode(['error' => true, 'msg' => 'Error with Data!']);
                exit;
            }

            foreach ($layout as $widget) {

                if ($widget->uniqid && $widget->i) {
                    $param = new stdClass();
                    $param->x = $widget->x;
                    $param->y = $widget->y;
                    $param->w = $widget->w;
                    $param->h = $widget->h;
                    $param->minH = $widget->minH;
                    $param->minW = $widget->minW;
                    DB::getDB()->query("UPDATE dashboard
                SET param='" . json_encode($param) . "'
                WHERE uniqid= '".$widget->uniqid."' AND id=".(int)$widget->i);
                }


            }

            echo json_encode(['error' => false]);
            exit;

            exit;
        }


        /**
         * GET LIST FOR ADD
         */
        if ($_REQUEST['task'] == 'formWidgets') {

            $addWidgetList = [];
            $db = [];
            $result = DB::getDB()->query('SELECT * FROM `widgets` WHERE `position` = "dashboard" OR `position` = "header" ');
            while ($row = DB::getDB()->fetch_array($result, true)) {
                $db[] = $row;
            }
            $list = [];
            $result = DB::getDB()->query('SELECT * FROM `dashboard` WHERE `user_id` = "default" ');
            while ($row = DB::getDB()->fetch_array($result, true)) {
                $list[] = $row;
            }
            $folders = FILE::getFilesInFolder(PATH_EXTENSIONS);
            foreach ($folders as $folder) {
                $extPath = PATH_EXTENSIONS . $folder['filename'];
                if (is_dir($extPath)) {
                    $json = FILE::getExtensionJSON($extPath . DS . 'extension.json');
                    if ($json['widgets']) {
                        foreach ($json['widgets'] as $widget) {
                            if ($widget->position == 'dashboard' || $widget->position == 'header') {
                                $arr = [
                                    "ext" => $json['name'],
                                    "status" => 0,
                                    "title" => $widget->title,
                                    "uniqid" => $widget->uniqid,
                                    "params" => $widget->params
                                ];
                                foreach ($db as $dbItem) {
                                    if ($dbItem['uniqid'] == $widget->uniqid) {
                                        $arr["status"] = 1;
                                        $arr["widget_id"] = $dbItem['id'];
                                        $arr["access"] = json_decode($dbItem['access']);
                                    }
                                }
                                if ($arr["status"] == 1) {

                                    foreach ($list as $itemList) {
                                        if ($itemList['uniqid'] == $widget->uniqid) {
                                            $arr["active"] = 1;
                                        }
                                    }
                                    $addWidgetList[] = $arr;
                                }

                            }
                        }
                    }
                }
            }

            echo json_encode(['error' => false, 'list' => $addWidgetList ]);
            exit;

        }

        $html = '';
        $list = [];
        $result = DB::getDB()->query('SELECT a.*, b.access
            FROM dashboard as a 
            LEFT JOIN widgets as b ON a.widget_id = b.id
            WHERE a.user_id = "default" ');
        while ($row = DB::getDB()->fetch_array($result, true)) {
            if ($row['id']) {

                $temp = json_decode($row['param']);
                $temp->i = $row['id'];
                $temp->uniqid = $row['uniqid'];
                $temp->title = $row['title'];
                $temp->widget_id = $row['widget_id'];
                $temp->access = json_decode($row['access']);
                $list[] = $temp;


            }

        }



        /*

        echo '<pre>';
        print_r($addWidgetList);
        echo '</pre>';

        echo '<pre>';
        print_r($activeWidgets);
        echo '</pre>';
        */

        //$addWidgetList = json_encode($addWidgetList);
        $list = json_encode($list);

        eval("\$html = \"" . DB::getTPL()->get("dashboard/admin") . "\";");

        return $html;
    }
}


?>