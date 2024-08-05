<?php

class NotenFremdsprachenNiveaustufen {

    private $text = "";



    /**
     * NotenFremdsprachenNiveaustufen constructor.
     * @param int $jgs
     * @param NoteZeugnis[] $zeugnisnoten
     */
    public function __construct($jgs, $zeugnisnoten) {
        $kompetenzen = [];

        if($jgs == 9) {
            if($this->checkFach("E", $zeugnisnoten)) $kompetenzen[] = "Englisch: B1";
            if($this->checkFach("F", $zeugnisnoten)) $kompetenzen[] = "Französisch: B1";
            if($this->checkFach("Sp", $zeugnisnoten)) $kompetenzen[] = "Spanisch: A2+";
        }

        if($jgs == 10) {
            if($this->checkFach("E", $zeugnisnoten)) $kompetenzen[] = "Englisch: B1+";
            if($this->checkFach("F", $zeugnisnoten)) $kompetenzen[] = "Französisch: B1+";
            if($this->checkFach("Sp", $zeugnisnoten)) $kompetenzen[] = "Spanisch: B1, Leseverstehen B1+";
        }

        if($jgs == 11) {
            if($this->checkFach("E", $zeugnisnoten)) $kompetenzen[] = "Englisch: B1+/B2";
            if($this->checkFach("F", $zeugnisnoten)) $kompetenzen[] = "Französisch: B1+";
            if($this->checkFach("Sp", $zeugnisnoten)) $kompetenzen[] = "Spanisch: B1+";
        }

        if(sizeof($kompetenzen) > 0) {
            $this->text = " Dieses Zeugnis schließt Kompetenzen entsprechend dem Gemeinsamen Europäischen Referenzrahmen für Sprachen auf folgenden Niveaustufen ein: ";

            for($i = 0; $i < sizeof($kompetenzen); $i++) {
                $this->text .= $kompetenzen[$i] .  (($i < (sizeof($kompetenzen)-1)) ? "; " : "");
            }

            $this->text .= ".";
        }
    }

    /**
     * Erfüllt das Fach die Anforderungen?
     * @param string $fach
     * @param NoteZeugnisNote[] $noten
     * @return boolean
     */
    private function checkFach($fach, $noten) {
        for($i = 0; $i < sizeof($noten); $i++) {
            if($noten[$i]->getFach()->getKurzform() == $fach && $noten[$i]->getWert() <= 4 && $noten[$i]->getWert() > 0) return true;
        }

        return false;
    }

    public function getText() {
        return $this->text;
    }
}
