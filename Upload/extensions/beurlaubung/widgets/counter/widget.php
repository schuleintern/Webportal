<?php

/**
 *
 */
class extBeurlaubungWidgetCounter extends Widget
{


    public function render($dashboard = false)
    {

        include_once($this->getData()['path'] . DS . 'models' . DS . 'Antrag.class.php');
        $class = new extBeurlaubungModelAntrag();

        if (DB::getSession()->isPupil() || DB::getSession()->isEltern()) {

            $data = $class->getByUserIDAndStatus(DB::getSession()->getUserID(), 1); // 1- offen  2- ja  3- nein
            $count = count($data);
            if ($count > 0) {
                $html = '<a href="index.php?page=ext_beurlaubung&view=default" class="btn"><i class="fa fa-sun"></i>';
                $html .= '<span class="label bg-red">' . $count . '</span>';
                if ($dashboard) {
                    $html .= ' Beurlaubung';
                }
                $html .= '</a>';
                return $html;
            }

        } else if ( DB::getSession()->isAdmin() || DB::getSession()->isAdminOrGroupAdmin('Admin_Ext_Beurlaubung') === true  ) {


            $status = [1];
            $tmp_data = $class->getByStatus($status);
            $count = count($tmp_data);
            if ($count > 0) {
                $html = '<a href="index.php?page=ext_beurlaubung&view=open" class="btn"><i class="fa fa-sun"></i>';
                $html .= '<span class="label bg-red">' . $count . '</span>';
                if ($dashboard) {
                    $html .= ' Beurlaubung';
                }
                $html .= '</a>';
                return $html;
            }

        }


        return '';

    }

}