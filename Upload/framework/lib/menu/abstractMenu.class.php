<?php

abstract class AbstractMenu
{

    public function getDBMenuItems($item_id)
    {
        $html = '';
        if ($item_id) {
            $menu_items = $this->menu->getCatsDeep($item_id);
            foreach ($menu_items as $item) {
                if ((int)$item['active'] === 1 && $item['items']) {
                    $arr = [$item['page']];
                    $sub = '';
                    foreach ($item['items'] as $child) {
                        $arr[] = $child['page'];
                        $sub .= $this->getDBMenuItem($child);
                    }
                    $html .= $this->startDropDown($arr, $item['title'], $item['icon']);
                    $html .= $this->getDBMenuItem($item);
                    $html .= $sub;
                    $html .= $this->endDropDown();
                } else {
                    $html .= $this->getDBMenuItem($item);
                }
            }
        }
        return $html;
    }

    public function getDBMenuItem($item)
    {
        $html = '';
        if ((int)$item['active'] !== 1) {
            return $html;
        }
        $icon = $item['icon'];
        if (!$icon) {
            $icon = 'fa fa-file';
        }
        $target = '_self';
        if ($item['target']) {
            $target = '_blank';
        }
        $params = (array)json_decode($item['params']);

        $path = str_replace('ext_', '', PATH_EXTENSIONS . $item['page'] . DS);
        $json = FILE::getExtensionJSON($path . 'extension.json');

        if ($item['access']) {

            $do = false;

            if ($item['access']->other) {
                if (DB::isLoggedIn() && DB::getSession()->isNone()) {
                    $do = true;
                }
            }
            if ($item['access']->parents) {
                if (DB::isLoggedIn() && DB::getSession()->isEltern()) {
                    $do = true;
                }
            }
            if ($item['access']->pupil) {
                if (DB::isLoggedIn() && DB::getSession()->isPupil()) {
                    $do = true;
                }
            }
            if ($item['access']->teacher) {
                if (DB::isLoggedIn() && DB::getSession()->isTeacher()) {
                    $do = true;
                }
            }
            if ($item['access']->adminGroup && $item['page']) {

                if ($json && $json['adminGroupName']) {
                    if (in_array($json['adminGroupName'], DB::getSession()->getGroupNames())) {
                        $do = true;
                    }
                }
            }
            if ($item['access']->admin) {
                if (DB::isLoggedIn() && DB::getSession()->isAdmin()) {
                    $do = true;
                }
            }
            if ($item['options']) {
                $options = json_decode($item['options']);
                foreach ($options as $key => $option) {
                    if ($option->value) {
                        $params[$key] = $option->value;
                    }
                }
            }
            if ($do) {
                if (isset($json['menu']->class) && $json['menu']->class) {
                    $menuClassPath = PATH_EXTENSIONS . $path . DS . 'menu' . DS . 'menu.php';
                    if (file_exists($menuClassPath)) {
                        include_once($menuClassPath);
                    }
                    $menuClass = new $json['menu']->class();
                    $html .= $menuClass->render();
                } else {
                    $html .= $this->getMenuItem($item['page'], $item['title'], $icon, $params, false, $target);
                }
            }
        } else {
            $html .= $this->getMenuItem($item['page'], $item['title'], $icon, $params, false, $target);
        }
        return $html;
    }


    public function startDropDown($pages, $title, $icon, $addParams = [], $infoNumber = 0)
    {

        $active = false;



        if (sizeof($addParams) > 0) {
            foreach ($addParams as $name => $value) {
                if (is_array($value) && in_array($_REQUEST['page'], $pages)) {
                    if ($value[0] == 'ISPRESENT' && $_REQUEST[$name] != "") {
                        $active = true;
                    } else {
                        $active = in_array($_REQUEST[$name], $value);
                    }
                }
            }
        } else {
            $active = in_array($_REQUEST['page'], $pages);
        }

        return '<li class="' . (($active) ? ("active ") : ("")) . 'treeview">
              <a href="#">
                <i class="' . $icon . '"></i> <span>' . $title . '</span> ' . (($infoNumber > 0) ? (' <span class="pull-right-container">
              <span class="label label-primary">' . $infoNumber . '</span>
            </span>') : ('')) . ' <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">';
    }

    public function getMenuItem($page, $title, $icon, $addParams = [], $infoNumber = 0, $target = '_self')
    {
        $isActive = false;
        $classLi = '';
        $addParamString = "";
        if (sizeof($addParams) == 0) {
            if ($_REQUEST['page'] == $page) {
                $isActive = true;
            }
        } else {
            foreach ($addParams as $name => $value) {
                $addParamString .= "&";
                $addParamString .= $name . "=" . urlencode($value);
                if ($_REQUEST[$name] == $value) {
                    $isActive = true;
                } else {
                    $isActive = false;
                }
            }
            if ($_REQUEST['page'] == $page && $isActive) {
                $isActive = true;
                $classLi = 'class="active"';
            } else {
                $isActive = false;
            }
        }

        return '<li ' . $classLi . '><a href="index.php?page=' . $page . $addParamString . '" target="' . $target . '"><i class="' . $icon . '"></i><span> ' . $title . '</span>' . (($infoNumber > 0) ? ('            <span class="pull-right-container">
              <span class="label label-primary pull-right">' . $infoNumber . '</span>
            </span>') : ('')) . '</a></li>';
    }

    public function endDropDown()
    {
        return '</ul></li>';
    }

    public function isActive($page)
    {
        return AbstractPage::isActive($page);
    }

    public static function siteIsAlwaysActive()
    {
        return true;
    }
}