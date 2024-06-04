<?php

/**
 *
 */
class extAusweisWidgetCounter extends Widget
{


    public function render($dashboard = false)
    {

        include_once($this->getData()['path'] . DS . 'models' . DS . 'Antrag.class.php');

        if (DB::getSession()->isTeacher()) {
            $arr = [];
            $count = 0;
            $user = DB::getSession()->getUser();
            $userObj = $user->getTeacherObject();
            $klassen = $userObj->getKlassenMitKlasseleitung();

            if ($klassen) {
                foreach($klassen as $klasse) {
                    $arr[] = $klasse->getKlassenName();
                }
                $count = extAusweisModelAntrag::getByKlassenCount($arr);
            }

            if ($count > 0) {
                $html = '<a href="index.php?page=ext_ausweis&view=open" class="btn"><i class="fa fa-address-card"></i>';
                $html .= '<span class="label bg-red">' . $count . '</span>';
                if ($dashboard) {
                    $html .= ' Ausweis';
                }
                $html .= '</a>';
                return $html;
            }
        }


        return false;
    }

}