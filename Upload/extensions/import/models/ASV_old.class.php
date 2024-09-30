<?php

/**
 *
 */
class extImportModelASV_old extends ExtensionModel
{

    static $table = '';
    static $fields = [
    ];
    static $defaults = [
    ];


    private $faecher = [];
    private $klassen = [];


    private $log = [];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        parent::__construct($data, $this->table ? $this->table : false, ['parent_id' => 'inbox_id']);
        self::setModelFields($this->fields, $this->defaults);
    }


    public function getCollection($full = false)
    {

        $collection = parent::getCollection();

        if ($full) {

        }

        return $collection;
    }


    private function log($msg, $data = false)
    {
        if (!$msg) {
            return false;
        }
        $this->log[] = [
            'msg' => $msg,
            'data' => $data
        ];

    }

    private function getLog()
    {
        $ret = [];
        foreach ($this->log as $log) {
            $ret[] = [
                'msg' => $log['msg']
            ];
        }
        return $ret;
    }
    private function saveLog()
    {
        $data = [
            'log' => json_encode($this->log),
            'createdTime' => date('Y-m-d H:i:s', time()),
            'createdUserID' => DB::getSession()->getUserID()
        ];
        DB::run("INSERT INTO ext_import_asv_log  ( log, createdTime, createdUserID ) values( :log, :createdTime, :createdUserID );", $data);
    }





    public function handleXML($simpleXML)
    {


        $schulnummer = $simpleXML->schulen[0]->schule->schulnummer;
        //$schulnummer = '0740';



        if (!DB::isSchulnummern($schulnummer)) {
            return [
                'error' => true,
                'msg' =>"Die Schulnummer in der Exportdatei (" . $schulnummer . ") stimmt nicht mit der Schulnummer der Installation (" . DB::getSchulnummern(true) . ") überein!"
            ];
        }

        if ( $this->loadFaecher($simpleXML) ) {
            $this->log('ERFOLGREICH: Faecher eingelesen', $this->faecher );
        }


        if ( $this->loadUnterricht($simpleXML) ) {
            $this->log('ERFOLGREICH: Unterricht eingelesen', $this->unterricht );
        }

        if ( $retSchulen = $this->loadSchulen($simpleXML) ) {
            $this->log('ERFOLGREICH: Schulen eingelesen', $retSchulen );
        }

        if ( $retKlassen = $this->loadKlassen($simpleXML) ) {
            $this->log('ERFOLGREICH: Klassen eingelesen.', $retKlassen );
        }

        if ( $this->loadLehrer($simpleXML) ) {
            $this->log('ERFOLGREICH: Lehrer eingelesen.', $this->lehrer );
        }

        if ( $retLeitung = $this->loadKlassenleitung() ) {
            $this->log('ERFOLGREICH: Klassenleitungen eingelesen.', $retLeitung );
        }

        if ( $retSchueler = $this->loadSchueler() ) {
            $this->log('ERFOLGREICH: Schüler eingelesen.', $retSchueler );
        }

        if ( $retKontakt = $this->loadKontaktdaten() ) {
            $this->log('ERFOLGREICH: Kontaktdaten der Eltern gelöscht.', $retKontakt );
        }

        if ( $retAdressen = $this->loadElternAdressen($simpleXML) ) {
            $this->log('ERFOLGREICH: Adressen der Eltern eingelesen.', $retAdressen );
        }


        $matcher = new MatchUserFunctions();
        if (DB::getGlobalSettings()->lehrerUserlehrerUserModeMode == "SYNC") {
            $ret = $matcher->matchLehrer();
            $this->log('ERFOLGREICH: Sync Lehrer mit User-Tabelle', $ret);
        }
        if (DB::getGlobalSettings()->schuelerUserMode == "SYNC") {
            $ret = $matcher->matchSchueler();
            $this->log('ERFOLGREICH: Sync Schueler mit User-Tabelle', $ret);
        }



        if(DB::getGlobalSettings()->elternUserMode == "ASV_CODE") {
            $schuelerOhneCodeSQL = DB::getDB()->query("SELECT * FROM schueler WHERE schuelerAsvID NOT IN (SELECT codeSchuelerAsvID FROM eltern_codes)");
            $ret = [];
            while($schueler = DB::getDB()->fetch_array($schuelerOhneCodeSQL)) {
                $code = substr(md5($schueler['schuelerAsvID']),0,5) . "-" . substr(md5(rand()),0,10);
                DB::getDB()->query("INSERT INTO eltern_codes (codeSchuelerAsvID, codeText, codeUserID) values('" . $schueler['schuelerAsvID'] . "','" . $code . "',0)");
                $ret[] = [
                    'schuelerAsvID' => $schueler['schuelerAsvID'],
                    'codeUserID' => 0
                ];
            }
            if ($ret) {
                $this->log('ERFOLGREICH: Elternbenutzer Codes angelegt', $ret);
            }

        }



        $this->saveLog();

        return $this->getLog();
    }





    private function loadFaecher($simpleXML) {

        // Faecher laden
        foreach ($simpleXML->schulen[0]->schule->faecher->fach as $fach) {
            $this->faecher[] = array(
                "id" => strval($fach->xml_id),
                'asdid' => strval($fach->asd_fach),
                "kurzform" => strval($fach->kurzform),
                "langform" => strval($fach->langform),
                'istselbsterstellt' => strval($fach->ist_selbst_erstellt)
            );
        }

        // Ganztags
        $this->faecher[] = array(
            "id" => 1,
            'asdid' => 0,
            "kurzform" => "OGS",
            "langform" => "Ganztags Betreuung",
            'istselbsterstellt' => 1
        );

        // Ordnungszahlen retten
        $ordnungszahlen = [];

        $faecher = DB::run('SELECT * FROM faecher  ')->fetchAll();
        foreach ($faecher as $f) {
            $ordnungszahlen[] = [
                'asdID' => $f['fachASDID'],
                'ordnung' => $f['fachOrdnung']
            ];
        }


        // Faecher in DB Schreiben
        if ( DB::getDB()->query("DELETE FROM faecher") ) {
            $this->log('Alle Faecher gelöscht' );
        }


        for ($i = 0; $i < sizeof($this->faecher); $i++) {
            DB::getDB()->query("INSERT INTo faecher (fachID, fachKurzform, fachLangform, fachASDID, fachIstSelbstErstellt)
					values (
						'" . $this->faecher[$i]['id'] . "',
						'" . DB::getDB()->escapeString($this->faecher[$i]['kurzform']) . "',
						'" . DB::getDB()->escapeString($this->faecher[$i]['langform']) . "',
                        '" . DB::getDB()->escapeString($this->faecher[$i]['asdid']) . "',
                        " . DB::getDB()->escapeString($this->faecher[$i]['istselbsterstellt']) . "
					)");
        }
        $this->log($i.' Faecher hinzugefügt' );


        for ($i = 0; $i < sizeof($ordnungszahlen); $i++) {
            DB::getDB()->query("UPDATE faecher SET fachOrdnung='" . $ordnungszahlen[$i]['ordnung'] . "' WHERE fachASDID='" . $ordnungszahlen[$i]['asdID'] . "'");
        }
        $this->log('Update '.$i.' Faecher-Ordnung' );
        

        return true;

    }

    private function loadUnterricht($simpleXML)
    {



        $ausbSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Unterrichtsart_(2129).xml', null, LIBXML_NOCDATA);

        $unterrichtsartLang = [];
        foreach ($ausbSXML->eintrag as $bg) {
            // print_r($bg);
            if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsart[strval($bg->schluessel)] = strval($bg->kurzform);
            if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsartLang[strval($bg->schluessel)] = strval($bg->langform);
        }

        $_klassengruppen = [];

        // Klassengruppen laden
        foreach ($simpleXML->schulen[0]->schule->klassen->klasse as $klasse) {
            foreach ($klasse->klassengruppen->klassengruppe as $klassengruppe) {
                $_klassengruppen[(int)$klassengruppe->xml_id] = [
                    'name' => strval($klasse->klassenname)
                ];
            }
        }

        // Unterricht laden
        foreach ($simpleXML->schulen[0]->schule->unterrichtselemente->unterrichtselement as $unterricht) {

            $koppelText = '';
            $isPseudoKoppel = 0;

            if (is_object($unterricht->koppel)) {
                $koppelText = strval($unterricht->koppel->kurzform);
                if (strval($unterricht->koppel->is_pseudokoppel) == 'true') $isPseudoKoppel = 1;
            }

            $unterrichtKlasse = $_klassengruppen[(int)strval($unterricht->klassengruppe_id)];

            $this->unterricht[] = array(
                "id" => strval($unterricht->xml_id),
                "lehrer" => strval($unterricht->lehrkraft_id),
                "fachid" => strval($unterricht->fach_id),
                "bezeichnung" => (strval($unterricht->bezeichnung)),
                "unterrichtsart" => $unterrichtsartLang[strval($unterricht->unterrichtsart)],
                "stunden" => strval($unterricht->stunden),
                "wissenschaftlich" => strval($unterricht->ist_wissenschaftlich) == 'true',
                "startdatum" => DateFunctions::getMySQLDateFromNaturalDate(strval($unterricht->von)),
                "enddatum" => DateFunctions::getMySQLDateFromNaturalDate(strval($unterricht->bis)),
                'klassenunterricht' => strval($unterricht->in_matrix) == 'true',
                'koppeltext' => $koppelText,
                'pseudokoppel' => $isPseudoKoppel,
                'ueid' => strval($unterricht->ueid),
                'klassen' => $unterrichtKlasse['name']

            );
        }

        // Ganztags
        $this->unterricht[] = array(
            "lehrer" => 0,
            "fachid" => 1,
            "bezeichnung" => "Ganztags",
            "unterrichtsart" => '',
            "stunden" => 12,
            "wissenschaftlich" => 0,
            "startdatum" => 0,
            "enddatum" => 0,
            'klassenunterricht' => 0,
            'koppeltext' => '',
            'pseudokoppel' => 0,
            'ueid' => '',
            'klassen' => ''
        );




        // Unterricht synchronisieren
        $currentUnterricht = DB::getDB()->query("SELECT * FROM unterricht");
        while ($u = DB::getDB()->fetch_array($currentUnterricht)) {
            $found = false;
            for ($i = 0; $i < sizeof($this->unterricht); $i++) {
                if ($this->unterricht[$i]['id'] == $u['unterrichtID']) {
                    // $this->unterricht[$i] = array();
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                DB::getDB()->query("DELETE FROM unterricht WHERE unterrichtID='" . $u['unterrichtID'] . "'");
                $this->log('Unterricht '.$u['unterrichtBezeichnung'].' gelöscht' );
            }
        }

        $count = 0;
        for ($i = 0; $i < sizeof($this->unterricht); $i++) {
            if (sizeof($this->unterricht[$i]) > 0) {
                if ($this->unterricht[$i]['id']) {
                    $count++;
                    DB::getDB()->query("INSERT INTO unterricht
                        (
                            unterrichtID,
                            unterrichtLehrerID,
                            unterrichtFachID,
                            unterrichtBezeichnung,
                            unterrichtArt,
                            unterrichtStunden,
                            unterrichtIsWissenschaftlich,
                            unterrichtStart,
                            unterrichtEnde,
                            unterrichtIsKlassenunterricht,
                            unterrichtKoppelText,
                            unterrichtKoppelIsPseudo,
                            unterrichtElementASVID,
                            unterrichtKlassen
                        )
                            values
                        (
                            " . (int)$this->unterricht[$i]['id'] . ",
                            " . (int)$this->unterricht[$i]['lehrer'] . ",
                            '" . $this->unterricht[$i]['fachid'] . "',
                            '" . $this->unterricht[$i]['bezeichnung'] . "',
                            '" . $this->unterricht[$i]['unterrichtsart'] . "',
                            '" . $this->unterricht[$i]['stunden'] . "',
                            " . (($this->unterricht[$i]['wissenschaftlich'] != '') ? $this->unterricht[$i]['wissenschaftlich'] : 0) . ",
                            '" . $this->unterricht[$i]['startdatum'] . "',
                            '" . $this->unterricht[$i]['enddatum'] . "',
                            " . (($this->unterricht[$i]['klassenunterricht'] != '') ? $this->unterricht[$i]['klassenunterricht'] : 0) . ",
                            " . (($this->unterricht[$i]['koppeltext'] != '') ? "'" . $this->unterricht[$i]['koppeltext'] . "'" : 'null') . ",
                            '" . $this->unterricht[$i]['pseudokoppel'] . "',
                            '" . DB::getDB()->escapeString($this->unterricht[$i]['ueid']) . "',
                            '" . $this->unterricht[$i]['klassen'] . "'
                        ) ON DUPLICATE KEY UPDATE
                            unterrichtID=" . (int)$this->unterricht[$i]['id'] . ",
                            unterrichtLehrerID=" . (($this->unterricht[$i]['lehrer'] != '') ? $this->unterricht[$i]['lehrer'] : 0) . ",
                            unterrichtFachID='" . $this->unterricht[$i]['fachid'] . "',
                            unterrichtBezeichnung='" . $this->unterricht[$i]['bezeichnung'] . "',
                            unterrichtArt='" . $this->unterricht[$i]['unterrichtsart'] . "',
                            unterrichtStunden='" . $this->unterricht[$i]['stunden'] . "',
                            unterrichtIsWissenschaftlich=" . (($this->unterricht[$i]['wissenschaftlich'] != '') ? $this->unterricht[$i]['wissenschaftlich'] : 0) . ",
                            unterrichtStart='" . $this->unterricht[$i]['startdatum'] . "',
                            unterrichtEnde='" . $this->unterricht[$i]['enddatum'] . "',
                            unterrichtIsKlassenunterricht='" . (($this->unterricht[$i]['klassenunterricht'] != '') ? $this->unterricht[$i]['klassenunterricht'] : 0) . "',
                            unterrichtKoppelText = " . (($this->unterricht[$i]['koppeltext'] != '') ? "'" . $this->unterricht[$i]['koppeltext'] . "'" : 'null') . ",
                            unterrichtKoppelIsPseudo='" . $this->unterricht[$i]['pseudokoppel'] . "',
                            unterrichtElementASVID='" . DB::getDB()->escapeString($this->unterricht[$i]['ueid']) . "',
                            unterrichtKlassen='" . DB::getDB()->escapeString($this->unterricht[$i]['klassen']) . "'
                    ");
                }
            }
        }
        $this->log( $count.' Unterrichte hinzugefügt oder aktualisiert' );
        // Unterricht Ende

        return true;
    }

    private function loadSchulen($simpleXML)
    {

        $ret = [];
        // Schulen einlesen
        $ausbSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Schulart_rechtlich_(2104).xml', null, LIBXML_NOCDATA);

        $schularten = [];
        foreach ($ausbSXML->eintrag as $bg) {
            // print_r($bg);
            $schularten[strval($bg->schluessel) * 1] = strval($bg->kurzform);
        }
        DB::getDB()->query("DELETE FROM schulen");
        foreach ($simpleXML->schulverzeichnis_liste->schulverzeichniseintrag as $schule) {
            DB::getDB()->query("INSERT INTO schulen (schuleID, schuleNummer, schuleArt, schuleName) values(

				'" . DB::getDB()->escapeString(strval($schule->xml_id) * 1) . "',
				'" . DB::getDB()->escapeString(strval($schule->schulnummer)) . "',
				'" . DB::getDB()->escapeString($schularten[strval($schule->schulart) * 1]) . "',
				'" . DB::getDB()->escapeString(strval($schule->dienststellenname)) . "'
			)");
            $ret[] = $schule;
        }

        return $ret;

    }

    private function loadKlassen($simpleXML)
    {

        // Besuchten Unterricht lï¿½schen
        if ( DB::getDB()->query("DELETE FROM unterricht_besuch") ) {
            $this->log('Alle Unterrichtsbesuchte gelöscht');
        }

        // Klassen laden
        $ausbSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Bildungsgang_(1010).xml', null, LIBXML_NOCDATA);

        $ausbs = [];
        foreach ($ausbSXML->eintrag as $bg) {
            // print_r($bg);
            $ausbs[strval($bg->schluessel)] = strval($bg->kurzform);
        }


        $relSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Religionszugehoerigkeit.xml', null, LIBXML_NOCDATA);
        $religion = [];
        foreach ($relSXML->eintrag as $bg) {
            $religion[strval($bg->schluessel)] = strval($bg->kurzform);
        }

        $relSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Staat_(2118).xml', null, LIBXML_NOCDATA);
        $staaten = [];
        foreach ($relSXML->eintrag as $bg) {
            $staaten[strval($bg->schluessel)] = strval($bg->langform);
        }
        $relSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Jahrgangsstufe_(1015).xml', null, LIBXML_NOCDATA);


        $jahrgangsstufen = [];
        foreach ($relSXML->eintrag as $bg) {
            if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $jahrgangsstufen[strval($bg->schluessel) * 1] = strval($bg->kurzform);
        }
        $relSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Unterrichtsfach_(1041).xml', null, LIBXML_NOCDATA);


        $unterrichtsfaecher = [];
        foreach ($relSXML->eintrag as $bg) {
            if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsfaecher[strval($bg->schluessel) * 1] = strval($bg->anzeigeform);
        }

        $anzahlSchueler = 0;

        if ( DB::getDB()->query("DELETE FROM klassen") ) {
            $this->log('Alle Klassen gelöscht');
        }

        $count = 0;
        foreach ($simpleXML->schulen[0]->schule->klassen->klasse as $klasse) {

            /*
            $klassenName = strval($klasse->klassenname);


            $fieldCheck = str_replace(" ", "_", $klassenName);
            $fieldCheck2 = str_replace(".", "_", $klassenName);

            if ($_REQUEST[md5($fieldCheck)] > 0) {
                // Import starten
            } elseif ($_REQUEST[md5($fieldCheck2)] > 0) {
                // :-)
            } else {
                //$doneActions .= $klassenName . " übersprungen.\r\n";
                continue;
            }
            */

            $kls = array();
            foreach ($klasse->klassenleitungen->klassenleitung as $klassenleitung) {
                if (strval($klassenleitung->lehrkraft_id) != "") {
                    $kls[] = array(
                        'lehrerID' => strval($klassenleitung->lehrkraft_id),
                        'art' => (strval($klassenleitung->klassenleitung_art) == "K") ? 1 : 2
                    );
                }
            }

            DB::getDB()->query("INSERT INTO klassen (
                     klassenname, klassenname_lang, klassenname_naechstes_schuljahr,
                     klassenname_zeugnis, klassenart, ausgelagert, aussenklasse) values(

				'" . DB::getDB()->escapeString(strval($klasse->klassenname)) . "',
				'" . DB::getDB()->escapeString(strval($klasse->klassenname_lang)) . "',
				'" . DB::getDB()->escapeString(strval($klasse->klassenname_naechstes_schuljahr)) . "',
				'" . DB::getDB()->escapeString(strval($klasse->klassenname_zeugnis)) . "',
				'" . DB::getDB()->escapeString(strval($klasse->klassenart)) . "',
				'" . DB::getDB()->escapeString(strval($klasse->ausgelagert)) . "',
				'" . DB::getDB()->escapeString(strval($klasse->aussenklasse)) . "'
			)");

            $count++;

            $schuelerAnzahl = 0;

            $schuelerListe = array();


            // Jede Klassengruppe einzeln.
            foreach ($klasse->klassengruppen->klassengruppe as $klassengruppe) {

                $ausbildungsrichtung = strval($klassengruppe->bildungsgang);
                $ausbildungsrichtung = $ausbs[$ausbildungsrichtung];
                $jahrgangsstufe = $jahrgangsstufen[(int)strval($klassengruppe->jahrgangsstufe) * 1];

                foreach ($klassengruppe->schuelerliste->schuelerin as $schueler) {

                    $adressen = array();
                    $sprachen = [];

                    foreach ($schueler->schueleranschriften->schueleranschrift as $schueleranschrift) {

                        $kontakt = [];
                        for ($i = 0; $i < count((array)$schueleranschrift->kommunikationsdaten->kommunikation); $i++) {

                            $kontakt[] = array(
                                "typ" => strval($schueleranschrift->kommunikationsdaten->kommunikation[$i]->typ),
                                "wert" => (strval($schueleranschrift->kommunikationsdaten->kommunikation[$i]->nummer_adresse)),
                                "anschrifttyp" => strval($schueleranschrift->anschriftstyp)
                            );
                        }


                        $wessenText = "";
                        switch (strval($schueleranschrift->anschrift_wessen)) {
                            case '1':
                            default:
                                $wessenText = 'eb';
                                break;

                            case '2':
                                $wessenText = 'web';
                                break;

                            case '3':
                                $wessenText = 's';
                                break;

                            case '4':
                                $wessenText = 'w';
                                break;
                        }


                        $adressen[] = array(
                            "wessen" => (strval($schueleranschrift->anschrift_wessen)),
                            "wessenText" => $wessenText,
                            "anschriftstyp" => (strval($schueleranschrift->anschriftstyp)),
                            "auskunftsberechtigt" => (strval($schueleranschrift->auskunftsberechtigt)),
                            "hauptansprechpartner" => (strval($schueleranschrift->hauptansprechpartner)),
                            "strasse" => (strval($schueleranschrift->anschrift->strasse)),
                            "nummer" => (strval($schueleranschrift->anschrift->nummer)),
                            "ortsbezeichnung" => (strval($schueleranschrift->anschrift->ortsbezeichnung)),
                            "postleitzahl" => (strval($schueleranschrift->anschrift->postleitzahl)),
                            "anredetext" => (strval($schueleranschrift->anschrift->anredetext)),
                            "anschrifttext" => (strval($schueleranschrift->anschrift->anschrifttext)),
                            "familienname" => (strval($schueleranschrift->person->familienname)),
                            "vornamen" => (strval($schueleranschrift->person->vornamen)),
                            "anrede" => (strval($schueleranschrift->person->anrede)),
                            "personentyp" => (strval($schueleranschrift->person->personentyp)),
                            "kontakt" => $kontakt
                        );

                    }

                    $unterrichtListe = array();

                    if ($schueler->besuchte_faecher->besuchtes_fach) {
                        for ($f = 0; $f < sizeof($schueler->besuchte_faecher->besuchtes_fach); $f++) {
                            $fach = $schueler->besuchte_faecher->besuchtes_fach[$f];
                            if ($fach && $fach->unterrichtselemente && $fach->unterrichtselemente->unterrichtselement_id) {
                                for ($i = 0; $i < sizeof($fach->unterrichtselemente->unterrichtselement_id); $i++) {
                                    $unterrichtListe[] = strval($fach->unterrichtselemente->unterrichtselement_id[$i]);
                                }
                            }
                        }
                    }


                    $import = true;

                    /* if(strval($schueler->austrittsdatum) != "") {

                        $austrittsdatum = explode(".",strval($schueler->austrittsdatum));
                        $timeAustritt = mktime(10,10,10,$austrittsdatum[1],$austrittsdatum[0],$austrittsdatum[2]);

                        if($timeAustritt < time()) $import = false;
                    } */

                    if (strval($schueler->eintrittsdatum) != "") {
                        $eintrittsdatum = explode(".", strval($schueler->eintrittsdatum));
                    }

                    foreach ($schueler->fremdsprachen->fremdsprache as $sprache) {
                        $sprachen[] = [
                            'jahrgangsstufe' => $jahrgangsstufen[strval($sprache->von_jahrgangsstufe) * 1],
                            'feststellungspruefung' => ((strval($sprache->feststellungspruefung) == 'true') ? 1 : 0),
                            'unterrichtsfach' => $unterrichtsfaecher[strval($sprache->unterrichtsfach) * 1],
                            'sortierung' => strval($sprache->sortierung)
                        ];
                    }

                    if ($import) {
                        $anzahlSchueler++;
                        $schuelerListe[] = array(

                            "asvid" => strval($schueler->lokales_differenzierungsmerkmal),
                            "name" => (strval($schueler->familienname)),
                            "vornamen" => (strval($schueler->vornamen)),
                            "rufname" => (strval($schueler->rufname)),
                            "namevorgestellt" => (strval($schueler->namensbestandteil_vorangestellt)),
                            "namenachgestellt" => (strval($schueler->namensbestandteil_nachgestellt)),
                            "geschlecht" => (strval($schueler->geschlecht)) == "1" ? "m" : "w",
                            "geburtsdatum" => (strval($schueler->geburtsdatum)),
                            "austrittsdatum" => strval($schueler->austrittsdatum),
                            "bekenntnis" => $religion[strval($schueler->religionszugehoerigkeit)],
                            'geburtsort' => strval($schueler->geburtsort),
                            'geburtsland' => $staaten[strval($schueler->Geburtsland)],
                            'jahrgangsstufe' => $jahrgangsstufe,
                            'jahrgangsstufeeintritt' => $jahrgangsstufen[(int)strval($schueler->eintritt_jahrgangsstufe) * 1],
                            'eintrittsdatum' => DateFunctions::getMySQLDateFromNaturalDate(strval($schueler->eintrittsdatum)),
                            // "kontakt" => $kontakt,
                            "adressen" => $adressen,
                            "unterricht" => $unterrichtListe,
                            "ausbildungsrichtung" => $ausbildungsrichtung,
                            'sprachen' => $sprachen,
                            "ganztag_betreuung" => (strval($schueler->ganztag_betreuung)),
                        );


                        $schuelerAnzahl++;
                    }
                }
            }

            $klassenName = strval($klasse->klassenname);

            for ($k = 0; $k < 10; $k++) {
                $klassenName = str_replace("0" . $k, $k, $klassenName);
            }

            $this->klassen[] = array(
                "name" => $klassenName,
                "klassenleitung" => $kls,
                "schueler" => $schuelerListe
            );
        }

        $this->log($count.' Klassen hinzugefügt');

        return $this->klassen;

    }

    private function loadLehrer($simpleXML)
    {
        // Lehrer laden


        foreach ($simpleXML->schulen[0]->schule->lehrkraefte->lehrkraft as $lehrer) {

            $datenID = intval($lehrer->lehrkraftdaten_nicht_schulbezogen_id);

            $name = "";
            $vornamen = "";
            $rufname = "";
            $geschlecht = "";
            $zeugnisname = "";
            $amtsbezeichnungID = 0;
            $asvID = "";

            foreach ($simpleXML->lehrkraftdaten_nicht_schulbezogen_liste->lehrkraftdaten_nicht_schulbezogen as $daten) {
                if (intval($daten->xml_id) == $datenID) {
                    $name = strval($daten->familienname);
                    $vornamen = strval($daten->vornamen);
                    $rufname = strval($daten->rufname);
                    $geschlecht = ((strval($daten->geschlecht) == "2") ? "w" : "m");
                    $zeugnisname = strval($daten->zeugnisname_1);
                    $amtsbezeichnungID = intval($daten->amtsbezeichnung);
                    $asvID = strval($daten->lokales_differenzierungsmerkmal);
                    $nameNachgestellt = $daten->namensbestandteil_nachgestellt;
                    $nameVorgestellt = $daten->namensbestandteil_vorangestellt;
                    break;
                }
            }


            if (strval($lehrer->namenskuerzel) != "")
                $this->lehrer[] = array(
                    "xmlid" => intval($lehrer->xml_id),
                    "datenid" => intval($lehrer->lehrkraftdaten_nicht_schulbezogen_id),
                    "kuerzel" => (strval($lehrer->namenskuerzel)),
                    "name" => ($name),
                    "namevorgestellt" => $nameVorgestellt,
                    "namenachgestellt" => $nameNachgestellt,
                    "vornamen" => ($vornamen),
                    "rufname" => ($rufname),
                    "geschlecht" => $geschlecht,
                    "zeugnisname" => ($zeugnisname),
                    "amtsbezeichnung" => $amtsbezeichnungID,
                    "asvid" => $asvID
                );
        }


        // Sync Lehrer

        // Welche Lehrer löschen?
        $lehrer = DB::getDB()->query("SELECT * FROM lehrer");
        while ($l = DB::getDB()->fetch_array($lehrer)) {
            $found = false;
            for ($i = 0; $i < sizeof($this->lehrer); $i++) {
                if ($this->lehrer[$i]['asvid'] == $l['lehrerAsvID']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ll = DB::getDB()->query_first("SELECT * FROM lehrer WHERE lehrerAsvID='" . $l['lehrerAsvID'] . "'");

                if ( DB::getDB()->query("DELETE FROM lehrer WHERE lehrerAsvID='" . $ll['lehrerAsvID'] . "'") ) {
                    $this->log($ll['lehrerAsvID'].'-'.$ll['lehrerName'].' Lehrer gelöscht');
                }

                if (DB::getGlobalSettings()->lehrerUserMode == "ASV" && $ll['lehrerUserID'] > 0) {
                    DB::getDB()->query("DELETE FROM users WHERE userID='" . $ll['lehrerUserID'] . "'");
                    DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . $ll['lehrerUserID'] . "'");
                    $this->log($ll['lehrerUserID'].'-'.$ll['lehrerName'].' Benutzer gelöscht');
                }
            }
        }
        // Lehrer anlegen

        $lehrer = $this->lehrer;


        /*
        for($i = 0; $i < sizeof($fach->unterrichtselemente->unterrichtselement_id); $i++) {
            $unterrichtListe[] = strval($fach->unterrichtselemente->unterrichtselement_id[$i]);
        }
        */

        for ($i = 0; $i < sizeof($lehrer); $i++) {
            DB::getDB()->query("
				INSERT INTO lehrer
					(
						lehrerID,
						lehrerAsvID,
						lehrerKuerzel,
						lehrerName,
						lehrerVornamen,
						lehrerRufname,
						lehrerGeschlecht,
						lehrerZeugnisunterschrift,
						lehrerAmtsbezeichnung,
						lehrerNameVorgestellt,
						lehrerNameNachgestellt
					) values(
						'" . DB::getDB()->escapeString($lehrer[$i]['xmlid']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['asvid']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['kuerzel']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['name']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['vornamen']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['geschlecht']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['zeugnisname']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['amtsbezeichnung']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['namevorgestellt']) . "',
						'" . DB::getDB()->escapeString($lehrer[$i]['namenachgestellt']) . "'
					) ON DUPLICATE KEY UPDATE
						lehrerID='" . DB::getDB()->escapeString($lehrer[$i]['xmlid']) . "',
						lehrerKuerzel='" . DB::getDB()->escapeString($lehrer[$i]['kuerzel']) . "',
						lehrerName='" . DB::getDB()->escapeString($lehrer[$i]['name']) . "',
						lehrerVornamen='" . DB::getDB()->escapeString($lehrer[$i]['vornamen']) . "',
						lehrerRufname='" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
						lehrerGeschlecht='" . DB::getDB()->escapeString($lehrer[$i]['geschlecht']) . "',
						lehrerZeugnisunterschrift='" . DB::getDB()->escapeString($lehrer[$i]['zeugnisname']) . "',
						lehrerAmtsbezeichnung='" . DB::getDB()->escapeString($lehrer[$i]['amtsbezeichnung']) . "',
						lehrerUserID=lehrerUserID,
						lehrerNameVorgestellt='" . DB::getDB()->escapeString($lehrer[$i]['namevorgestellt']) . "',
						lehrerNameNachgestellt='" . DB::getDB()->escapeString($lehrer[$i]['namenachgestellt']) . "'
			");

            // Update USERS Table
            // TODO: besser in der UserMatch Class?
            DB::getDB()->query("UPDATE users SET 
                         userFirstName = '" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
                         userLastName = '" . DB::getDB()->escapeString($lehrer[$i]['name']) . "'
                         WHERE userAsvID = '" . $lehrer[$i]['asvid']."'" );

        }

        $this->log($i.' Lehrer hinzugefügt');

        return true;

    }

    private function loadKlassenleitung()
    {
        // Klassenleitung
        $ret = [];
        DB::getDB()->query("DELETE FROM klassenleitung");
        for ($i = 0; $i < count($this->klassen); $i++) {
            for ($k = 0; $k < count($this->klassen[$i]['klassenleitung']); $k++) {
                $ret[] = [
                    'klasseName' => $this->klassen[$i]['name'],
                    'lehrerID' => $this->klassen[$i]['lehrerID'],
                    'klassenleitungArt' => $this->klassen[$i]['art']
                ];

                DB::getDB()->query("INSERT INTO klassenleitung (klasseName, lehrerID, klassenleitungArt)  values('" . DB::getDB()->escapeString($this->klassen[$i]['name']) . "','" . $this->klassen[$i]['klassenleitung'][$k]['lehrerID'] . "','" . $this->klassen[$i]['klassenleitung'][$k]['art'] . "') ON DUPLICATE KEY UPDATE lehrerID=lehrerID");

            }
        }
        $this->log($i.' Klassenleitung hinzugefügt oder aktualisiert');

        return $ret;
    }

    private function loadSchueler()
    {

        $ret = [];

        // Lï¿½schen?
        $alleSchueler = DB::getDB()->query("SELECT * FROM schueler");
        while ($schueler = DB::getDB()->fetch_array($alleSchueler)) {
            $found = false;

            for ($i = 0; $i < sizeof($this->klassen); $i++) {
                for ($s = 0; $s < sizeof($this->klassen[$i]['schueler']); $s++) {
                    if ($this->klassen[$i]['schueler'][$s]['asvid'] == $schueler['schuelerAsvID']) {
                        $found = true;
                    }
                }
            }

            if (!$found) {
                $ss = DB::getDB()->query_first("SELECT * FROM schueler WHERE schuelerAsvID='" . $schueler['schuelerAsvID'] . "'");



                if ( DB::getDB()->query("DELETE FROM schueler WHERE schuelerAsvID='" . $ss['schuelerAsvID'] . "'") ) {
                    $this->log($ss['schuelerAsvID'].'-'.$ss['schuelerName'].' Schueler gelöscht');

                }
                if (DB::getGlobalSettings()->schuelerUserMode == "ASV" && $ss['schuelerUserID'] > 0) {
                    DB::getDB()->query("DELETE FROM users WHERE userID='" . $ss['schuelerUserID'] . "'");
                    DB::getDB()->query("DELETE FROM users_groups WHERE userID='" . $ss['schuelerUserID'] . "'");
                    $this->log($ss['schuelerUserID'].'-'.$ss['schuelerName'].' Benutzer gelöscht');
                }
            }
        }
        //
        $insertsFremdsprachen = [];
        for ($i = 0; $i < sizeof($this->klassen); $i++) {
            for ($s = 0; $s < sizeof($this->klassen[$i]['schueler']); $s++) {
                // DB::getDB()->query("DELETE FROM schueler WHERE schuelerAsvID='" . $this->klassen[$i]['schueler'][$s]['asvid'] . "'");

                $data = explode(".", $this->klassen[$i]['schueler'][$s]['geburtsdatum']);

                $gebdatum = $data[2] . '-' . $data[1] . '-' . $data[0];

                if ($this->klassen[$i]['schueler'][$s]['austrittsdatum'] != "") {
                    $data = explode(".", $this->klassen[$i]['schueler'][$s]['austrittsdatum']);
                    $austrittsdatum = "'" . $data[2] . '-' . $data[1] . '-' . $data[0] . "'";
                } else $austrittsdatum = "NULL";

                DB::getDB()->query("INSERT INTO schueler
						(
							schuelerAsvID,
							schuelerName,
							schuelerVornamen,
							schuelerRufname,
							schuelerGeschlecht,
							schuelerGeburtsdatum,
							schuelerKlasse,
							schuelerAustrittDatum,
							schuelerBekenntnis,
							schuelerAusbildungsrichtung,
							schuelerGeburtsort,
							schuelerGeburtsland,
							schuelerJahrgangsstufe,
							schulerEintrittJahrgangsstufe,
							schuelerEintrittDatum, 
							schuelerNameVorgestellt,
							schuelerNameNachgestellt,
                            schuelerGanztagBetreuung

						) values (
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['asvid']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['name']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['vornamen']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['rufname']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geschlecht']) . "',
							'" . $gebdatum . "',
							'" . $this->klassen[$i]['name'] . "',
						    " . $austrittsdatum . ",
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['bekenntnis']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ausbildungsrichtung']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geburtsort']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geburtsland']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['jahrgangsstufe']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['jahrgangsstufeeintritt']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['eintrittsdatum']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['namevorgestellt']) . "',
							'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['namenachgestellt']) . "',
							" . (($this->klassen[$i]['schueler'][$s]['ganztag_betreuung'] != '') ? DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ganztag_betreuung']) : 0) . "

						) ON DUPLICATE KEY UPDATE
							schuelerAsvID='" . $this->klassen[$i]['schueler'][$s]['asvid'] . "',
							schuelerName='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['name']) . "',
							schuelerVornamen='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['vornamen']) . "',
							schuelerRufname='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['rufname']) . "',
							schuelerGeschlecht='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geschlecht']) . "',
							schuelerGeburtsdatum='" . $gebdatum . "',
							schuelerKlasse='" . $this->klassen[$i]['name'] . "',
							schuelerAustrittDatum=" . $austrittsdatum . ",
							schuelerBekenntnis = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['bekenntnis']) . "',
							schuelerAusbildungsrichtung = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ausbildungsrichtung']) . "',
							schuelerGeburtsort='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geburtsort']) . "',
							schuelerGeburtsland='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geburtsland']) . "',
							schuelerJahrgangsstufe = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['jahrgangsstufe']) . "',
							schulerEintrittJahrgangsstufe='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['jahrgangsstufeeintritt']) . "',
							schuelerEintrittDatum='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['eintrittsdatum']) . "',
							schuelerNameVorgestellt='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['namevorgestellt']) . "',
							schuelerNameNachgestellt='" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['namenachgestellt']) . "',
							schuelerGanztagBetreuung=" . (($this->klassen[$i]['schueler'][$s]['ganztag_betreuung'] != '') ? DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ganztag_betreuung']) : 0) . "
						");

                $values = "";

                for ($j = 0; $j < sizeof($this->klassen[$i]['schueler'][$s]['sprachen']); $j++) {
                    $sprache = $this->klassen[$i]['schueler'][$s]['sprachen'][$j];
                    $insertsFremdsprachen[] = "(
				'" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['asvid']) . "',
				'" . DB::getDB()->escapeString($sprache['sortierung']) . "',
				'" . DB::getDB()->escapeString($sprache['jahrgangsstufe']) . "',
				'" . DB::getDB()->escapeString($sprache['unterrichtsfach']) . "',
				'" . DB::getDB()->escapeString($sprache['feststellungspruefung']) . "')";
                }


                for ($u = 0; $u < sizeof($this->klassen[$i]['schueler'][$s]['unterricht']); $u++) {
                    if ($u > 0) $values .= ",";
                    $values .= "('" . $this->klassen[$i]['schueler'][$s]['unterricht'][$u] . "','" . $this->klassen[$i]['schueler'][$s]['asvid'] . "')";
                }
                if ($values != "") DB::getDB()->query("INSERT INTO unterricht_besuch (unterrichtID, schuelerAsvID) values " . $values);

                // Ganztags
                if ($this->klassen[$i]['schueler'][$s]['ganztag_betreuung'] != "") DB::getDB()->query("INSERT INTO unterricht_besuch (unterrichtID, schuelerAsvID) values ('1','" . $this->klassen[$i]['schueler'][$s]['asvid'] . "')");


                // Update USER data
                DB::getDB()->query("UPDATE users SET 
                         userFirstName = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['rufname']) . "',
                         userLastName = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['name']) . "'
                         WHERE userAsvID = '" . $this->klassen[$i]['schueler'][$s]['asvid']."'" );
            }
            $ret[] = [
                "klasse" => $this->klassen[$i]['name'],
                "anz" => $s
            ];
            $this->log($s.' Schueler der Klasse '.$this->klassen[$i]['name'].' hinzugefügt oder aktualisiert');
        }


        if ( DB::getDB()->query("DELETE FROM schueler_fremdsprache") ) {
            $this->log('Alle Schueler-Fremdsprechen gelöscht');
        }
        if (sizeof($insertsFremdsprachen) > 0) {
            DB::getDB()->query("INSERT INTO schueler_fremdsprache (schuelerAsvID, spracheSortierung, spracheAbJahrgangsstufe, spracheFach, spracheFeststellungspruefung) values " . implode(",", $insertsFremdsprachen));
        }
        $this->log(sizeof($insertsFremdsprachen). ' Schueler-Fremdsprechen hinzugefügt');
        return $ret;
    }

    private function loadKontaktdaten()
    {
        $ret = [];
        // Kontaktdaten der Eltern

        // Syncen, nicht löschen, da UserIDs gespeichert.
        // DB::getDB()->query("DELETE FROM eltern_email");

        if (DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
            $count = 0;
            $elternMails = DB::getDB()->query("SELECT * FROM eltern_email");

            // Debugger::debugObject($this->klassen,1);

            while ($elternMail = DB::getDB()->fetch_array($elternMails)) {
                $found = false;


                for ($i = 0; $i < sizeof($this->klassen); $i++) {
                    for ($s = 0; $s < sizeof($this->klassen[$i]['schueler']); $s++) {
                        for ($a = 0; $a < sizeof($this->klassen[$i]['schueler'][$s]['adressen']); $a++) {
                            for ($k = 0; $k < sizeof($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt']); $k++) {


                                if (($this->klassen[$i]['schueler'][$s]['adressen'][$a]['wessenText'] == 'eb' || $this->klassen[$i]['schueler'][$s]['adressen'][$a]['wessenText'] == 'web') && filter_var(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']), FILTER_VALIDATE_EMAIL)) {
                                    if (strtolower(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert'])) == strtolower($elternMail['elternEMail'])) {
                                        if ($this->klassen[$i]['schueler'][$s]['asvid'] == $elternMail['elternSchuelerAsvID']) {
                                            $found = true;
                                            break;
                                        }
                                    }

                                }
                                if ($found) break;
                            }
                        }
                        if ($found) break;
                    }
                    if ($found) break;
                }

                if (!$found) {
                    $ret[] = [
                        'elternEMail' => $elternMail['elternEMail'],
                        'elternSchuelerAsvID' => $elternMail['elternSchuelerAsvID']
                    ];
                    DB::getDB()->query("DELETE FROM eltern_email WHERE elternEMail LIKE '" . $elternMail['elternEMail'] . "' AND elternSchuelerAsvID LIKE '" . $elternMail['elternSchuelerAsvID'] . "'");
                    $count++;
                }
            }
            $this->log($count.' Elternkontakt gelöscht');
        }

        return $ret;
    }

    private function loadElternAdressen($simpleXML)
    {
        if ( DB::getDB()->query("TRUNCATE TABLE eltern_adressen") ) {
            $this->log('Alle Eltern Adressen gelöscht');
        }
        if ( DB::getDB()->query("DELETE FROM eltern_telefon") ) {
            $this->log('Alle Eltern Telefonnummern gelöscht');
        }

        $insertsAdressen = array();
        $id = 0;

        $count_tel = 0;

        for ($i = 0; $i < sizeof($this->klassen); $i++) {
            for ($s = 0; $s < sizeof($this->klassen[$i]['schueler']); $s++) {
                for ($a = 0; $a < sizeof($this->klassen[$i]['schueler'][$s]['adressen']); $a++) {

                    $wessen = "";
                    switch ($this->klassen[$i]['schueler'][$s]['adressen'][$a]['wessen']) {
                        case '1':
                        default:
                            $wessen = 'eb';
                            break;

                        case '2':
                            $wessen = 'web';
                            break;

                        case '3':
                            $wessen = 's';
                            break;

                        case '4':
                            $wessen = 'w';
                            break;
                    }

                    $id++;

                    $insertsAdressen[] = "(
								NULL,
								'" . $this->klassen[$i]['schueler'][$s]['asvid'] . "',
								'$wessen',
								'" . (($this->klassen[$i]['schueler'][$s]['adressen'][$a]['auskunftsberechtigt'] == "false") ? 0 : 1) . "',
								'" . (($this->klassen[$i]['schueler'][$s]['adressen'][$a]['hauptansprechpartner'] == "true") ? 1 : 0) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['strasse']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['nummer']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['ortsbezeichnung']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['postleitzahl']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['anredetext']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['anschrifttext']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['familienname']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['vornamen']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['anrede']) . "',
								'" . addslashes($this->klassen[$i]['schueler'][$s]['adressen'][$a]['personentyp']) . "'
							)";


                    for ($k = 0; $k < sizeof($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt']); $k++) {


                        if (filter_var(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']), FILTER_VALIDATE_EMAIL) !== false) {
                            if (DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {

                                DB::getDB()->query("INSERT INTO eltern_email (elternEMail, elternSchuelerAsvID, elternAdresseID) values(
									'" . DB::getDB()->escapeString(strtolower(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']))) . "',
									'" . $this->klassen[$i]['schueler'][$s]['asvid'] . "'
							,$id) ON DUPLICATE KEY UPDATE elternSchuelerAsvID=elternSchuelerAsvID, elternAdresseID=$id");
                            }
                        } else {
                            $art = "";

                            if ((int)$this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['typ'] * 1 == 3) {
                                // Fax
                                $art = "fax";
                            }

                            if ((int)$this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['typ'] * 1 == 2) {
                                // Handy
                                $art = "mobiltelefon";
                            }

                            if ((int)$this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['typ'] * 1 == 1) {
                                // Handy
                                $art = "telefon";
                            }

                            if ($art != "") {
                                $count_tel++;
                                DB::getDB()->query("INSERT INTO eltern_telefon (telefonNummer, schuelerAsvID, telefonTyp, kontaktTyp, adresseID) values
								(
								'" . trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']) . "',
								'" . $this->klassen[$i]['schueler'][$s]['asvid'] . "',
								'" . $art . "',
								'" . trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['anschrifttyp']) . "',$id

								) ON DUPLICATE KEY UPDATE telefonNummer=telefonNummer");
                            }
                        }


                    }


                }
            }

        }

        $this->log($count_tel. ' Eltern Telefonnummern hinzugefügt');

        if (sizeof($insertsAdressen) > 0) {
            DB::getDB()->query("
						INSERT INTO eltern_adressen
							(
								`adresseID`,
								`adresseSchuelerAsvID`,
								`adresseWessen`,
								`adresseIsAuskunftsberechtigt`,
								`adresseIsHauptansprechpartner`,
								`adresseStrasse`,
								`adresseNummer`,
								`adresseOrt`,
								`adressePostleitzahl`,
								`adresseAnredetext`,
								`adresseAnschrifttext`,
								`adresseFamilienname`,
								`adresseVorname`,
								`adresseAnrede`,
								`adressePersonentyp`
							) VALUES
					" . implode(", ", $insertsAdressen));
        }

        $this->log(sizeof($insertsAdressen). ' Eltern Adressen hinzugefügt');

        return $insertsAdressen;
    }




    public function uploadZip($post_file = false, $zip_Path = false)
    {

        if (move_uploaded_file($post_file['tmp_name'], $zip_Path)) {

            $zip = new ZipArchive;
            $res = $zip->open($zip_Path);
            if ($res === TRUE) {
                $zip->close();
                return true;
            }

        }
        return false;
    }

    public function unZip($zip_Path = false, $pass = false, $xml_Path = false)
    {

        if (!$zip_Path || !$pass || !$xml_Path) {
            return false;
        }
        if (file_exists($zip_Path)) {

            $zip = new ZipArchive;
            $res = $zip->open($zip_Path);
            if ($res === TRUE) {
                if ($zip->setPassword((string)$pass)) {
                    if ($zip->extractTo($xml_Path)) {
                        $zip->close();
                        return true;
                    }
                }
                $zip->close();
                return false;
            }

        }
        return false;
    }


}
