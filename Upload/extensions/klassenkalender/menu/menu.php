<?php


 

class extKlassenkalenderMenu extends AbstractMenu
{

    public function render()
    {

        $html = '';


        if (DB::getSession()->isAdmin() && !DB::getSession()->isTeacher() || DB::getSession()->isMember('Webportal_Klassenkalender')) {
            $html .= $this->getMenuItem("klassenkalender", "Alle Klassen", "fa fa-users", ['grade' => 'all_grades']);
        }

        if (DB::getSession()->isTeacher()) {

            $html .= $this->getMenuItem("klassenkalender", "Meine Klassen", "fa fas fa-users", ['grade' => 'allMyGrades']);
            $html .= $this->getMenuItem("klassenkalender", "Alle Klassen", "fa fa-users", ['grade' => 'all_grades']);
            $html .= $this->getMenuItem("klassenkalender", "Von mir eingetragen", "fa fa-users", ['grade' => 'allMyTermine']);

            $grades = klasse::getByUnterrichtForTeacher(DB::getSession()->getTeacherObject());

            $htmlMyGrades = "";

            for ($i = 0; $i < sizeof($grades); $i++) {
                $htmlMyGrades .= $this->getMenuItem("klassenkalender", $grades[$i]->getKlassenName(), "fa fa-child", ['grade' => ($grades[$i]->getKlassenName())]);
            }

            if ($htmlMyGrades != "") {
                $html .= $this->startDropDown(['klassenkalender'], "Meine Klassen", "fa fa-users");

                $html .= $htmlMyGrades;

                $html .= $this->endDropDown();
            }

            if (DB::getSettings()->getBoolean('klassenkalender-fachbetreueransicht')) {

                $htmlFachschaftsleitung = "";

                $faecher = fach::getMyFachschaftsleitungFaecher(DB::getSession()->getTeacherObject());

                for ($i = 0; $i < sizeof($faecher); $i++) {
                    $htmlFachschaftsleitung .= $this->getMenuItem("klassenkalender", $faecher[$i]->getLangform(), "fas fa-briefcase", ['grade' => 'fachbetreuer', 'fachASDID' => urlencode($faecher[$i]->getASDID())]);
                }

                if ($htmlFachschaftsleitung != "") {
                    $html .= $this->startDropDown(['klassenkalender'], "Fachbetreuung", "fas fa-briefcase", ['fachASDID' => ['ISPRESENT']]);

                    $html .= $htmlFachschaftsleitung;

                    $html .= $this->endDropDown();
                }

            }

        } else {

            if (DB::getSession()->isPupil()) {
                $grades = [DB::getSession()->getSchuelerObject()->getKlassenObjekt()];
            } else if (DB::getSession()->isEltern()) {
                $grades = [];

                $grades = DB::getSession()->getElternObject()->getKlassenObjectsAsArray();
            } else $grades = array();

            for ($i = 0; $i < sizeof($grades); $i++) {
                // Klassen aus Stundenplan suchen:
                $klasse = $grades[$i];
                if ($klasse != null) {
                    $html .= $this->getMenuItem("klassenkalender", $klasse->getKlassenName(), "fa fa-child", ['grade' => $klasse->getKlassenName()]);
                }
            }
        }


        return $html;
    }
}