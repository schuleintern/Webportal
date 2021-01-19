<?php

class Notenbogen {

    /**
     *
     * @var schueler
     */
    private $schueler;

    /**
     * Absenztage
     * @var int[]
     */
    private $absenzTage;

    /**
     *
     * @var SchuelerUnterricht[]
     */
    private $unterricht = [];

    /**
     *
     * @var UnterrichtsNoten[]
     */
    private $unterrichtsNoten = [];


    /**
     *
     * @param schueler $schueler
     */
    public function __construct($schueler) {


        include_once("../framework/lib/data/absenzen/Absenz.class.php");
        include_once("../framework/lib/data/absenzen/AbsenzBefreiung.class.php");
        include_once("../framework/lib/data/absenzen/AbsenzBeurlaubung.class.php");
        include_once("../framework/lib/data/absenzen/AbsenzSchuelerInfo.class.php");
        include_once("../framework/lib/system/DateFunctions.class.php");

        $this->schueler = $schueler;
        $absenzen = Absenz::getAbsenzenForSchueler($this->schueler);

        $this->absenzTage = [
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => 0,
            12 => 0,
            'gesamt' => 0
        ];



        $absenzenCalculator = new AbsenzenCalculator($absenzen);

        $this->absenzTage = $absenzenCalculator->getNotenmanagerStat();

        $this->absenzTage['gesamt'] = 0;
        
        for($i = 1; $i <= 12; $i++) $this->absenzTage['gesamt'] += $this->absenzTage[$i];

        $this->unterricht = SchuelerUnterricht::getUnterrichtForSchueler($this->schueler);

        for($i = 0; $i < sizeof($this->unterricht); $i++) {
            $this->unterrichtsNoten[] = new UnterrichtsNoten($this->unterricht[$i], $this->schueler);
        }

        // TEMP ISGY
        // TODO: Wieder weg machen

        $unterrichteArbeitenSchueler = DB::getDB()->query("SELECT DISTINCT arbeitUnterrichtName FROM noten_arbeiten JOIN noten_noten ON noten_noten.noteArbeitID=noten_arbeiten.arbeitID WHERE noten_noten.noteSchuelerASVID='" . $schueler->getAsvID() . "'");

        $alleUnterrichtSchuelerMitNoten = [];
        while($unterrichtElement = DB::getDB()->fetch_array($unterrichteInSchueler)) {
            $alleUnterrichtSchuelerMitNoten[] = $unterrichtElement['arbeitUnterrichtName'];
        }

        $fehlendeUnterrichte = [];
        for($i = 0; $i < sizeof($alleUnterrichtSchuelerMitNoten); $i++) {
            $found = false;

            for($u = 0; $u < sizeof($this->unterricht); $u++) {
                if($this->unterricht[$u]->getBezeichnung() == $alleUnterrichtSchuelerMitNoten[$i]) $found = true;
            }

            if(!$found) {
                $fehlendeUnterrichte[] = $alleUnterrichtSchuelerMitNoten[$i];
            }

        }

        $unterrichtID = 99999999;

        for($j = 0; $j < sizeof($fehlendeUnterrichte); $j++) {
            $data = DB::getDB()->query_first("SELECT * FROM noten_arbeiten JOIN noten_noten ON noten_noten.noteArbeitID=noten_arbeiten.arbeitID WHERE noten_noten.noteSchuelerASVID='" . $schueler->getAsvID() . "' AND noten_arbeiten.arbeitUnterrichtName='" . $fehlendeUnterrichte[$j] . "'");

            $fachID = DB::getDB()->query_first("SELECT fachID FROM faecher WHERE fachKurzform='" . $data['arbeitFachKurzform'] . "'");
            $fachID = $fachID['fachID'];

            $lehrerID = DB::getDB()->query_first("SELECT lehrerID FROM lehrer WHERE lehrerKuerzel='" . $data['arbeitLehrerKuerzel'] . "'");
            $lehrerID = $lehrerID['lehrerID'];

            $fakeUnterricht = new SchuelerUnterricht([
                'unterrichtFachID' => $fachID,
                'unterrichtLehrerID' => $lehrerID,

                'unterrichtID' => $unterrichtID,
                'unterrichtBezeichnung' => $fehlendeUnterrichte[$j],
                'unterrichtArt' => 'Pflichtunterricht',
                'unterrichtIsWissenschaftlich' => 1

            ], true);

            $unterrichtID++;

            $this->unterricht[] = $fakeUnterricht;
            $this->unterrichtsNoten[] = new UnterrichtsNoten($fakeUnterricht, $this->schueler);
        }


        // Ende Temp Änderung ISGY

        // alte Unterrichte dazu laden


        // Sortiere nach Datenbank.


        // Reihenfolge der Fächer: K, Ev, Eth, D, E, L, F, Sp, M, Ph, C, B, NuT, G, Sk, Geo, WR, WIn, Ku, Mu, Sm, Sw, soweit überhaupt Noten erteilt
    }

    public function getSchueler() {
        return $this->schueler;
    }

    public function getAbsenzen() {
        return $this->absenzTage;
    }

    public function getUnterricht() {
        return $this->unterricht;
    }

    public function getUnterrichtsNoten() {
        return $this->unterrichtsNoten;
    }

    public function getMaxAnzahlSchulaufgaben() {
        $max = 0;
        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            if(sizeof($this->unterrichtsNoten[$i]->getSchulaufgaben()) > $max) $max = sizeof($this->unterrichtsNoten[$i]->getSchulaufgaben());
        }

        return $max;
    }

    public function getMaxAnzahlKurzarbeiten() {
        $max = 0;
        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            if(sizeof($this->unterrichtsNoten[$i]->getKurzarbeiten()) > $max) $max = sizeof($this->unterrichtsNoten[$i]->getKurzarbeiten());
        }

        return $max;
    }

    public function getMaxAnzahlExen() {
        $max = 0;
        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            if(sizeof($this->unterrichtsNoten[$i]->getExen()) > $max) $max = sizeof($this->unterrichtsNoten[$i]->getExen());
        }

        return $max;
    }

    public function getMaxAnzahlMuendlich() {
        $max = 0;

        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            if(sizeof($this->unterrichtsNoten[$i]->getMuendlich()) > $max) $max = sizeof($this->unterrichtsNoten[$i]->getMuendlich());
        }

        return $max;
    }

    public function getNotentabelle() {
        $sa = $this->getMaxAnzahlSchulaufgaben();
        $saBreite = ($sa == 0) ? 1 : $sa;

        $ka = $this->getMaxAnzahlKurzarbeiten();
        $kaBreite = ($ka == 0) ? 1 : $ka;

        $ex = $this->getMaxAnzahlExen();
        $exBreite = ($ex == 0) ? 1 : $ex;

        $mdl = $this->getMaxAnzahlMuendlich();
        $mdlBreite = ($mdl == 0) ? 1 : $mdl;

        $table  = "<tr><th>&nbsp;</th><th colspan=\"$saBreite\">Große Leistungsnachweise</th>";
        $table .= "<th colspan=\"" . ($kaBreite + $exBreite + $mdlBreite) . "\">Kleine Leistungsnachweise</th>";

        $table .= "<th>Schnitt</th>";

        // TODO: ZB

        $table .= "</tr>";

        $table .= "<tr><th>&nbsp;</th><th colspan=\"$saBreite\">Schulaufgaben</th>";
        $table .= "<th colspan=\"$kaBreite\">Kurzarbeiten</th>";
        $table .= "<th colspan=\"$exBreite\">Stegreifaufgaben</th>";
        $table .= "<th colspan=\"$mdlBreite\">mündliche Noten</th><th>&nbsp;</th></tr>";

        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            $table .= "<tr><td>";

            $table .= $this->unterrichtsNoten[$i]->getUnterricht()->getBezeichnung();
            
            $table .= " (" . $this->unterrichtsNoten[$i]->getUnterricht()->getFach()->getKurzform() . ")";

            $name = ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer() != null ? ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer()->getGeschlecht() == 'w' ? "Frau" : "Herr") : "n/a");
            $name .= " " . ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer() != null ? ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer()->getName()) : ("n/a"));

            $table .= "<br /><small>" . $name. "</small>";

            $table .= "</td>";

            for($j = 0; $j < 4; $j++) {
                if($j == 0) $arbeiten = $this->unterrichtsNoten[$i]->getSchulaufgaben();
                if($j == 1) $arbeiten = $this->unterrichtsNoten[$i]->getKurzarbeiten();
                if($j == 2) $arbeiten = $this->unterrichtsNoten[$i]->getExen();
                if($j == 3) $arbeiten = $this->unterrichtsNoten[$i]->getMuendlich();


                if($j == 0) $maxInSpalte = $saBreite;
                if($j == 1) $maxInSpalte = $kaBreite;
                if($j == 2) $maxInSpalte = $exBreite;
                if($j == 3) $maxInSpalte = $mdlBreite;


                for($a = 0; $a < $maxInSpalte; $a++) {
                    $arbeit = $arbeiten[$a];

                    if($arbeit != null) {
                        $note = $arbeit->getNoteForSchueler($this->schueler);

                        $table .= "<td align=\"center\" valign=\"center\">";

                        if($note != null) {

                            $table .= $note->getDisplayWert();
                        }
                        else {
                            $table .= "X";
                        }

                        $art = $arbeit->isMuendlich() ? 'm' : 's';

                        $table .= "<br /><small>$art: " . number_format($arbeit->getGewichtung(),2,",",".") . "</small>";
                        $table .= "</td>";
                    }
                    else {
                        // Arbeit nicht vorhanden
                        $table .= "<td>&nbsp;</td>";
                    }
                }
            }

            $schnitt = $this->unterrichtsNoten[$i]->getSchnitt();
            $isNotenschutz = $this->unterrichtsNoten[$i]->isNotenschutzberechnung();

            if(!$this->unterrichtsNoten[$i]->hasNoten()) $schnitt = "--";
            else {
                $schnitt = number_format($schnitt,2,",",".");
                if($isNotenschutz) $schnitt .= "<br /><small>§34 Abs. 7 Nr. 2 BaySchO</small>";
            }



            $table .= "<td valign=\"center\" align=\"center\">" . $schnitt . "</td>";
            $table .= "</tr>";


        }
        return $table;
    }

    public function getNotentabelleZwischenbericht() {
        $sa = $this->getMaxAnzahlSchulaufgaben();

        $ka = $this->getMaxAnzahlKurzarbeiten();

        $ex = $this->getMaxAnzahlExen();

        $mdl = $this->getMaxAnzahlMuendlich();

       $table = "";



        $hasAnySchulaufgaben = false;
        $hasAnyKurzarbeiten = false;
        $hasAnyExen = false;
        $hasAnyMuendlich = false;

        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            if(!$this->unterrichtsNoten[$i]->hasNoten()) continue;

            if(!$hasAnyExen && sizeof($this->unterrichtsNoten[$i]->getExen()) > 0) {
                $hasAnyExen = true;
            }

            if(!$hasAnyKurzarbeiten && sizeof($this->unterrichtsNoten[$i]->getKurzarbeiten()) > 0) {
                $hasAnyKurzarbeiten = true;
            }

            if(!$hasAnySchulaufgaben && sizeof($this->unterrichtsNoten[$i]->getSchulaufgaben()) > 0) {
                $hasAnySchulaufgaben = true;
            }

            if(!$hasAnyMuendlich && sizeof($this->unterrichtsNoten[$i]->getMuendlich()) > 0) {
                $hasAnyMuendlich = true;
            }


        }



        for($i = 0; $i < sizeof($this->unterrichtsNoten); $i++) {
            if(!$this->unterrichtsNoten[$i]->hasNoten()) continue;

            $table .= "<tr><td>";

            $table .= $this->unterrichtsNoten[$i]->getUnterricht()->getFach()->getKurzform();

            $name = ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer() != null ? ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer()->getGeschlecht() == 'w' ? "Frau" : "Herr") : ("n/a"));
            $name .= ($this->unterrichtsNoten[$i]->getUnterricht()->getLehrer() != null ? (" " . $this->unterrichtsNoten[$i]->getUnterricht()->getLehrer()->getName()) : (""));

            $table .= "<br /><small>" . $name. "</small>";

            $table .= "</td>";

            for($j = 0; $j < 4; $j++) {
                if($j == 0) $arbeiten = $this->unterrichtsNoten[$i]->getSchulaufgaben();
                if($j == 1) $arbeiten = $this->unterrichtsNoten[$i]->getKurzarbeiten();
                if($j == 2) $arbeiten = $this->unterrichtsNoten[$i]->getExen();
                if($j == 3) $arbeiten = $this->unterrichtsNoten[$i]->getMuendlich();



                $notenInTable = "";

                for($a = 0; $a < sizeof($arbeiten); $a++) {
                    $arbeit = $arbeiten[$a];

                    if($arbeit != null) {
                        $note = $arbeit->getNoteForSchueler($this->schueler);


                        if($note != null) {


                            $breite = strlen($note->getDisplayWert());

                            $notenInTable .= $note->getDisplayWert();

                            for($x = $breite; $x <= 3; $x++) $notenInTable .= "&nbsp;";

                            // $table .= "<br /><small>" . number_format($arbeit->getGewichtung(),2,",",".") . "</small>";
                            // $table .= "</div>";

                        }


                    }
                }

                if($notenInTable != "") $notenInTable .= "<br /><small>";

                for($a = 0; $a < sizeof($arbeiten); $a++) {
                    $arbeit = $arbeiten[$a];

                    if($arbeit != null) {
                        $note = $arbeit->getNoteForSchueler($this->schueler);


                        if($note != null) {

                            $gewicht = number_format($arbeit->getGewichtung(),2,",",".");

                            $breite = strlen($gewicht);

                            $notenInTable .= $gewicht;

                            for($x = $breite; $x <= 5; $x++) $notenInTable .= "&nbsp;";

                            // $table .= "<br /><small>" .  . "</small>";
                            // $table .= "</div>";

                        }


                    }
                }

                // if($notenInTable != "") $notenInTable .= "</small></font>";


                if($notenInTable != "") {
                    $table .= "<td align=\"left\"><font face=\"Courier\">" . $notenInTable;
                    $table .= "&nbsp;</small></font>";
                    $table .= "</td>";
                } else {
                    if($j == 0 && $hasAnySchulaufgaben) {
                        $table .= "<td align=\"left\">&nbsp;";
                        $table .= "</td>";
                    }

                    if($j == 1 && $hasAnyKurzarbeiten) {
                        $table .= "<td align=\"left\">&nbsp;";
                        $table .= "</td>";
                    }

                    if($j == 2 && $hasAnyExen) {
                        $table .= "<td align=\"left\">&nbsp;";
                        $table .= "</td>";
                    }

                    if($j == 3 && $hasAnyMuendlich) {
                        $table .= "<td align=\"left\">&nbsp;";
                        $table .= "</td>";
                    }
                }



                if($j == 0 && schulinfo::isGymnasium()) {
                    // Schnitt große LNW einfügen

                    if($this->unterrichtsNoten[$i]->getSchnittGross() > 0) {
                        $table .= "<td align=\"left\"><font face=\"Courier\">" . number_format($this->unterrichtsNoten[$i]->getSchnittGross(),2,",",".") . "<br /><small>" . $this->unterrichtsNoten[$i]->getGewichtGross();
                        $table .= "</small></font></td>";
                    }
                    else if($hasAnySchulaufgaben) {
                        $table .= "<td align=\"left\">&nbsp;";
                        $table .= "</td>";

                    }


                }

                if($j == 3 && schulinfo::isGymnasium()) {

                    if($this->unterrichtsNoten[$i]->getSchnittKlein() > 0) {



                        $table .= "<td align=\"left\"><font face=\"Courier\">" . number_format($this->unterrichtsNoten[$i]->getSchnittKlein(),2,",",".") . "<br /><small>" . $this->unterrichtsNoten[$i]->getGewichtKlein();
                        $table .= "</small></font></td>";

                    }

                    else {
                        $table .= "<td align=\"left\">&nbsp;";
                        $table .= "</td>";

                    }
                }



            }

            $schnitt = $this->unterrichtsNoten[$i]->getSchnitt();
            $isNotenschutz = $this->unterrichtsNoten[$i]->isNotenschutzberechnung();

            if(!$this->unterrichtsNoten[$i]->hasNoten()) $schnitt = "--";
            else {
                $schnitt = number_format($schnitt,2,",",".");
                if($isNotenschutz) $schnitt .= " *";
            }



            $table .= "<td valign=\"center\" align=\"center\"><font face=\"Courier\">" . $schnitt . "</font></td>";
            $table .= "</tr>";




        }





        $tableHeader = "<tr><th style=\"width:12%\"><small>&nbsp;</small></th>";

        $width = 30;
        if(schulinfo::isGymnasium()) $width -= 8;

        if($hasAnySchulaufgaben) $tableHeader  .= "<th style=\"width:$width%\"><small>Große <br />Leistungsnachweise</small></th>";


        if(schulinfo::isGymnasium()) {
            if($hasAnySchulaufgaben) $tableHeader  .= "<th style=\"width:8%\">&Oslash;<br /><small>Große</small></th>";
        }


        $colspan = 0;

        if($hasAnyExen) $colspan++;
        if($hasAnyKurzarbeiten) $colspan++;
        if($hasAnyMuendlich) $colspan++;

        $width = 50;
        if(schulinfo::isGymnasium()) $width -= 8;


        if($colspan > 1) $tableHeader .= "<th colspan=\"$colspan\" style=\"width:$width%\"><small>Kleine <br />Leistungsnachweise</small></th>";

        if(schulinfo::isGymnasium()) {
            if($colspan) $tableHeader  .= "<th style=\"width:8%\">&Oslash;<br /><small>Kleine</small></th>";
        }

        $tableHeader .= "<th style=\"width:8%\">&Oslash;<br /><small>Gesamt</small></th>";

        // TODO: ZB

        $tableHeader .= "</tr>";

        $tableHeader .= "<tr><th><small>&nbsp;</small></th>";

        if($hasAnySchulaufgaben) $tableHeader .= "<th><small>Schulaufgaben</small></th>";

        if(schulinfo::isGymnasium()) {
            if($hasAnySchulaufgaben) $tableHeader  .= "<th><small>&nbsp;</small></th>";
        }


        if($hasAnyKurzarbeiten) $tableHeader .= "<th><small>Kurzarbeiten</small></th>";

        if($hasAnyExen) $tableHeader .= "<th><small>Stegreifaufgaben</small></th>";
        if($hasAnyMuendlich) $tableHeader .= "<th><small>mündliche Noten</small></th>";

        if(schulinfo::isGymnasium()) {
            if($colspan > 0) $tableHeader  .= "<th><small>&nbsp;</small></th>";
        }

        $tableHeader .= "<th><small>&nbsp;</small></th></tr>";

        return $tableHeader . $table;
    }

    public function getZwischenbericht() {

        return $html;
    }
}
