<?php

/**
 *
 */
class extImportModelASV extends ExtensionModel
{

    static $table = '';
    static $fields = [
    ];
    static $defaults = [
    ];


    private $faecher = [];
    private $klassen = [];


    private $log = [];

    private $schulnummer = false;

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
            'msg' => $msg
            //,'data' => $data
        ];

    }

    private function getLog()
    {
        $ret = [];
        foreach ($this->log as $log) {
            $ret[] = [
                'msg' => $log['msg']
                //,'data' => $log['data']
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


        $this->schulnummer = (int)$simpleXML->schulen[0]->schule->schulnummer;
        //$schulnummer = '0740';


        if (!DB::isSchulnummern($this->schulnummer)) {
            return [
                'error' => true,
                'msg' =>"Die Schulnummer in der Exportdatei (" . $this->schulnummer . ") stimmt nicht mit der Schulnummer der Installation (" . DB::getSchulnummern(true) . ") überein!"
            ];
        }

        $tables = [
            'eltern_telefon',
            'eltern_adressen',
            'eltern_email',
            'eltern_codes',
            'faecher',
            'unterricht',
            'schulen',
            'klassen',
            'lehrer',
            'klassenleitung',
            'schueler',
            'unterricht_besuch',
            'schueler_fremdsprache',
            'schueler',
            'schueler',
        ];

        if ( $this->backupTables($tables) ) {
            $this->log('<h4>Datenbank Backup erfolgreich!</h4>' );
        }


        if ( $this->loadFaecher($simpleXML) ) {
            $this->log('<b>ERFOLGREICH:</b> Faecher eingelesen', $this->faecher );
        }

        if ( $this->loadUnterricht($simpleXML) ) {
            $this->log('<b>ERFOLGREICH:</b> Unterrichte eingelesen', $this->unterricht );
        }

        if ( $retSchulen = $this->loadSchulen($simpleXML) ) {
            $this->log('<b>ERFOLGREICH:</b> Schulen eingelesen', $retSchulen );
        }

        if ( $retKlassen = $this->loadKlassen($simpleXML) ) {
            $this->log('<b>ERFOLGREICH:</b> Klassen eingelesen.', $retKlassen );
        } // muss vor loadSchueler() !!!!

        if ( $this->loadLehrer($simpleXML) ) {
            $this->log('<b>ERFOLGREICH:</b> Lehrer eingelesen.', $this->lehrer );
        }

        if ( $retLeitung = $this->loadKlassenleitung() ) {
            $this->log('<b>ERFOLGREICH:</b> Klassenleitungen eingelesen.', $retLeitung );
        }

        if ( $retSchueler = $this->loadSchueler() ) {
            $this->log('<b>ERFOLGREICH:</b> Schüler eingelesen.', $retSchueler );
        }

        if ( count(DB::getSchulnummern()) <= 1) {
            if ( $retKontakt = $this->loadDeleteElternMail() ) {
                $this->log('<b>ERFOLGREICH: (ASV_MAIL)</b> Unnötige Kontaktdaten der Eltern gelöscht.', $retKontakt );
            }
        } else {
            $this->log('<b>Wegen doppelter Schulnummer keine Elternkontakt gelöscht!</b>');
        }


        if ( $retAdressen = $this->loadElternAdressen($simpleXML) ) {
            $this->log('<b>ERFOLGREICH:</b> Adressen der Eltern eingelesen.', $retAdressen );
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
            $count = 0;
            $log = '';
            while($schueler = DB::getDB()->fetch_array($schuelerOhneCodeSQL)) {
                $code = substr(md5($schueler['schuelerAsvID']),0,5) . "-" . substr(md5(rand()),0,10);
                DB::getDB()->query("INSERT INTO eltern_codes (codeSchuelerAsvID, codeText, codeUserID) values('" . $schueler['schuelerAsvID'] . "','" . $code . "',0)");
                $ret[] = [
                    'schuelerAsvID' => $schueler['schuelerAsvID'],
                    'codeUserID' => 0
                ];
                $count++;
                $log .= '<br>Hinzugefügt: '.$schueler['schuelerAsvID'];

            }
            $this->log('<h4>Eltern Codes: (ASV_CODE)</h4><i>(Status: Schueler ASV ID )</i>'. $log);
            $this->log($count.' Eltern Codes hinzugefügt' );

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
        /*
        if ( DB::getDB()->query("DELETE FROM faecher") ) {
            $this->log('Alle Faecher gelöscht' );
        }
        */

        $log = '';
        $a = 0;
        $b = 0;
        for ($i = 0; $i < sizeof($this->faecher); $i++) {
            $data = DB::run('SELECT * FROM faecher WHERE fachID = :id', [ 'id' => $this->faecher[$i]['id'] ])->fetch();
            if ($data && $data['fachASDID'] == $this->faecher[$i]['asdid'] ) {

                DB::getDB()->query("UPDATE faecher SET
                    fachKurzform = '" . DB::getDB()->escapeString($this->faecher[$i]['kurzform']) . "',
                    fachLangform = '" . DB::getDB()->escapeString($this->faecher[$i]['langform']) . "',
                    fachASDID = " . DB::getDB()->escapeString($this->faecher[$i]['asdid']) . ",
                    fachIstSelbstErstellt = " . DB::getDB()->escapeString($this->faecher[$i]['istselbsterstellt']) . ",
                    schulnummer = " . $this->schulnummer . "
                    WHERE fachID = ".(int)$this->faecher[$i]['id']);
                $b++;
                $log .= '<br>Vorhanden: '.$this->faecher[$i]['asdid'].' - '.$this->faecher[$i]['kurzform'].' - '.$this->faecher[$i]['langform'];

            } else {

                DB::getDB()->query("INSERT INTO faecher (fachID, fachKurzform, fachLangform, fachASDID, fachIstSelbstErstellt, schulnummer)
                        values (
                            '" . $this->faecher[$i]['id'] . "',
                            '" . DB::getDB()->escapeString($this->faecher[$i]['kurzform']) . "',
                            '" . DB::getDB()->escapeString($this->faecher[$i]['langform']) . "',
                            '" . DB::getDB()->escapeString($this->faecher[$i]['asdid']) . "',
                            " . DB::getDB()->escapeString($this->faecher[$i]['istselbsterstellt']) . ",
                            ".$this->schulnummer."
                        )");
                $a++;
                $log .= '<br>Hinzugefügt: '.$this->faecher[$i]['asdid'].' - '.$this->faecher[$i]['kurzform'].' - '.$this->faecher[$i]['langform'];
            }
        }

        $this->log('<h4>Faecher:</h4><i>(Status: ASV ID, Kurzform, Title )</i>'. $log);
        $this->log($i.' Faecher durchlaufen:<br>'.$a.' Faecher hinzugefügt<br>'.$b.' Faecher waren vorhanden' );


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
            //if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) $unterrichtsart[strval($bg->schluessel)] = strval($bg->kurzform);
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
        /*
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
        */

        $a = 0;
        $b = 0;
        $count = 0;
        $log = '';
        if ($this->unterricht && is_array($this->unterricht)) {
            for ($i = 0; $i < sizeof($this->unterricht); $i++) {
                if (sizeof($this->unterricht[$i]) > 0) {
                    if ($this->unterricht[$i]['id']) {

                        $count++;
                        $data = DB::run('SELECT * FROM unterricht WHERE unterrichtID = :id ', ['id' => $this->unterricht[$i]['id']])->fetch();
                        if ($data && $data['unterrichtElementASVID'] == $this->unterricht[$i]['ueid'] ) {

                            DB::getDB()->query("UPDATE unterricht SET
                                unterrichtLehrerID = " . (int)$this->unterricht[$i]['lehrer'] . ",
                                unterrichtFachID = '" . $this->unterricht[$i]['fachid'] . "',
                                unterrichtBezeichnung = '" . $this->unterricht[$i]['bezeichnung'] . "',
                                unterrichtArt = '" . $this->unterricht[$i]['unterrichtsart'] . "',
                                unterrichtStunden = '" . $this->unterricht[$i]['stunden'] . "',
                                unterrichtIsWissenschaftlich = " . (($this->unterricht[$i]['wissenschaftlich'] != '') ? $this->unterricht[$i]['wissenschaftlich'] : 0) . ",
                                unterrichtStart = '" . $this->unterricht[$i]['startdatum'] . "',
                                unterrichtEnde = '" . $this->unterricht[$i]['enddatum'] . "',
                                unterrichtIsKlassenunterricht = " . (($this->unterricht[$i]['klassenunterricht'] != '') ? $this->unterricht[$i]['klassenunterricht'] : 0) . ",
                                unterrichtKoppelText = " . (($this->unterricht[$i]['koppeltext'] != '') ? "'" . $this->unterricht[$i]['koppeltext'] . "'" : 'null') . ",
                                unterrichtKoppelIsPseudo = '" . $this->unterricht[$i]['pseudokoppel'] . "',
                                unterrichtElementASVID = '" . DB::getDB()->escapeString($this->unterricht[$i]['ueid']) . "',
                                unterrichtKlassen = '" . $this->unterricht[$i]['klassen'] . "',
                                schulnummer = " . $this->schulnummer . "
                                WHERE unterrichtID = ".(int)$this->unterricht[$i]['id']);

                            $b++;
                            $log .= '<br>Vorhanden: '.$this->unterricht[$i]['id'].' - '.$this->unterricht[$i]['bezeichnung'].' - '.$this->unterricht[$i]['klassen'];

                        } else {
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
                                unterrichtKlassen,
                                schulnummer
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
                                '" . $this->unterricht[$i]['klassen'] . "',
                                ".$this->schulnummer."
                            )");
                            $a++;
                            $log .= '<br>Hinzugefügt: '.$this->unterricht[$i]['id'].' - '.$this->unterricht[$i]['bezeichnung'].' - '.$this->unterricht[$i]['klassen'];
                        }

                        DB::getSettings()->setValue("schulinfo-fach-" . $this->unterricht[$i]['fachid'] . "-unterrichtet", 1);
                    }
                }
            }
        }
        $this->log('<h4>Unterichte:</h4><i>(Status: ID, Bezeichnung, Klasse )</i>'. $log);
        $this->log($count.' Unterichte durchlaufen:<br>'.$a.' Unterichte hinzugefügt<br>'.$b.' Unterichte waren vorhanden' );

        return true;
    }

    private function loadSchulen($simpleXML)
    {

        $log = '';
        $a = 0;
        $b = 0;
        $count = 0;
        $ret = [];
        // Schulen einlesen
        $ausbSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Schulart_rechtlich_(2104).xml', null, LIBXML_NOCDATA);

        $schularten = [];
        foreach ($ausbSXML->eintrag as $bg) {
            // print_r($bg);
            $schularten[strval($bg->schluessel) * 1] = strval($bg->kurzform);
        }
        //DB::getDB()->query("DELETE FROM schulen");
        foreach ($simpleXML->schulverzeichnis_liste->schulverzeichniseintrag as $schule) {
            $count++;
            $data = DB::run('SELECT * FROM schulen WHERE schuleID = :id', ['id' => (int)$schule->xml_id ])->fetch();
            if ($data && $data['schuleID'] == $schule->xml_id ) {

                DB::getDB()->query("UPDATE schulen SET
                    schuleNummer = '" . DB::getDB()->escapeString(strval($schule->schulnummer)) . "',
                    schuleArt = '" . DB::getDB()->escapeString($schularten[(int)$schule->schulart]) . "',
                    schuleName = '" . DB::getDB()->escapeString(strval($schule->dienststellenname)) . "'
                    WHERE schuleID = ".(int)$schule->xml_id);

                $b++;
                $log .= '<br>Vorhanden: '.$schule->xml_id.' - '.$schule->schulnummer.' - '.$schule->dienststellenname;

            } else {
                DB::getDB()->query("INSERT INTO schulen (schuleID, schuleNummer, schuleArt, schuleName) values(

				'" . DB::getDB()->escapeString((int)$schule->xml_id) . "',
				'" . DB::getDB()->escapeString(strval($schule->schulnummer)) . "',
				'" . DB::getDB()->escapeString($schularten[(int)$schule->schulart]) . "',
				'" . DB::getDB()->escapeString(strval($schule->dienststellenname)) . "'
			)");
                $ret[] = $schule;
                $a++;
                $log .= '<br>Hinzugefügt: '.$schule->xml_id.' - '.$schule->schulnummer.' - '.$schule->dienststellenname;
            }
        }
        $this->log('<h4>Schuldaten:</h4><i>(Status: ID, Schulnummer, Titel )</i>'. $log);
        $this->log($count.' Schulen durchlaufen:<br>'.$a.' Schulen hinzugefügt<br>'.$b.' Schulen waren vorhanden' );

        return $ret;

    }

    private function loadKlassen($simpleXML)
    {

        // Klassen laden
        $ausbSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Bildungsgang_(1010).xml', null, LIBXML_NOCDATA);
        $ausbs = [];
        foreach ($ausbSXML->eintrag as $bg) {
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
            //if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) {
                $jahrgangsstufen[strval($bg->schluessel) * 1] = strval($bg->kurzform);
            //}
        }
        $relSXML = simplexml_load_file(PATH_ROOT.'framework'.DS.'asvwerte/Unterrichtsfach_(1041).xml', null, LIBXML_NOCDATA);


        $unterrichtsfaecher = [];
        foreach ($relSXML->eintrag as $bg) {
            //if (strval($bg->schulart_kurzform) == strtoupper(DB::getSettings()->getValue("schulinfo-schultyp"))) {
                $unterrichtsfaecher[strval($bg->schluessel) * 1] = strval($bg->anzeigeform);
            //}
        }

        //$anzahlSchueler = 0;

        /*
        if ( DB::getDB()->query("DELETE FROM klassen") ) {
            $this->log('Alle Klassen gelöscht');
        }
        */

        $count = 0;
        $a = 0;
        $b = 0;
        $log = '';
        $logSchueler = '';

        foreach ($simpleXML->schulen[0]->schule->klassen->klasse as $klasse) {

            $kls = array();
            foreach ($klasse->klassenleitungen->klassenleitung as $klassenleitung) {
                if ((int)$klassenleitung->lehrkraft_id) {
                    $kls[] = array(
                        'lehrerID' => strval($klassenleitung->lehrkraft_id),
                        'art' => (strval($klassenleitung->klassenleitung_art) == "K") ? 1 : 2
                    );
                }
            }


            $count++;

            if ($klasse->klassenname) {

                $data = DB::run("SELECT * FROM klassen WHERE klassenname = :klassenname ", ['klassenname' => strval($klasse->klassenname) ])->fetch();
                if ($data ) {

                    DB::getDB()->query("UPDATE klassen SET
                    klassenname = '" . DB::getDB()->escapeString(strval($klasse->klassenname))  . "',
                    klassenname_lang = '" . DB::getDB()->escapeString(strval($klasse->klassenname_lang))  . "',
                    klassenname_naechstes_schuljahr = '" . DB::getDB()->escapeString(strval($klasse->klassenname_naechstes_schuljahr))  . "',
                    klassenname_zeugnis = '" . DB::getDB()->escapeString(strval($klasse->klassenname_zeugnis))  . "',
                    klassenart = '" . DB::getDB()->escapeString(strval($klasse->klassenart))  . "',
                    ausgelagert = '" . DB::getDB()->escapeString(strval($klasse->ausgelagert))  . "',
                    aussenklasse = '" . DB::getDB()->escapeString(strval($klasse->aussenklasse))  . "',
                    schulnummer = " . (int)$this->schulnummer  . "
                    WHERE id = ".$data['id']);

                    $b++;
                    $log .= '<br>Vorhanden: '.$klasse->klassenname.' - '.$klasse->klassenname_lang;
                } else {

                    DB::getDB()->query("INSERT INTO klassen (
                         klassenname, klassenname_lang, klassenname_naechstes_schuljahr,
                         klassenname_zeugnis, klassenart, ausgelagert, aussenklasse, schulnummer) values(
    
                    '" . DB::getDB()->escapeString(strval($klasse->klassenname)) . "',
                    '" . DB::getDB()->escapeString(strval($klasse->klassenname_lang)) . "',
                    '" . DB::getDB()->escapeString(strval($klasse->klassenname_naechstes_schuljahr)) . "',
                    '" . DB::getDB()->escapeString(strval($klasse->klassenname_zeugnis)) . "',
                    '" . DB::getDB()->escapeString(strval($klasse->klassenart)) . "',
                    '" . DB::getDB()->escapeString(strval($klasse->ausgelagert)) . "',
                    '" . DB::getDB()->escapeString(strval($klasse->aussenklasse)) . "',
                    ".(int)$this->schulnummer."
                )");
                    $a++;
                    $log .= '<br>Hinzugefügt: '.$klasse->klassenname.' - '.$klasse->klassenname_lang;
                }
            }


            $schuelerAnzahl = 0;
            $schuelerListe = array();



            // Jede Klassengruppe einzeln.
            foreach ($klasse->klassengruppen->klassengruppe as $klassengruppe) {

                $ausbildungsrichtung = strval($klassengruppe->bildungsgang);
                $ausbildungsrichtung = $ausbs[$ausbildungsrichtung];
                $jahrgangsstufe = $jahrgangsstufen[(int)strval($klassengruppe->jahrgangsstufe) * 1];

                $logSchueler .= '<br><b>Jahrgangsstufe: </b>'.$jahrgangsstufe;

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
                        //$anzahlSchueler++;
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

            $logSchueler .= '<br><b>   Klasse: </b>'.$klassenName.' - Anzahl Schueler: '.$schuelerAnzahl;


            $this->klassen[] = array(
                "name" => $klassenName,
                "klassenleitung" => $kls,
                "schueler" => $schuelerListe
            );


        }


        $this->log('<h4>Klassen:</h4><i>(Status: Name, Langform )</i>'. $log);
        $this->log($count.' Klassen durchlaufen:<br>'.$a.' Klassen hinzugefügt<br>'.$b.' Klassen waren vorhanden' );

        $this->log('<h4>Schueler in Klassen:</h4>'. $logSchueler);

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
        /*
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
        */
        // Lehrer anlegen

        $lehrer = $this->lehrer;


        /*
        for($i = 0; $i < sizeof($fach->unterrichtselemente->unterrichtselement_id); $i++) {
            $unterrichtListe[] = strval($fach->unterrichtselemente->unterrichtselement_id[$i]);
        }
        */

        $a = 0;
        $b = 0;
        $count = 0;
        $log = '';
        if ($lehrer && is_array($lehrer)) {
            for ($i = 0; $i < sizeof($lehrer); $i++) {
                $count++;
                $data = DB::run('SELECT * FROM lehrer WHERE lehrerAsvID = :id', ['id' => $lehrer[$i]['asvid'] ])->fetch();
                if ($data) {

                    DB::getDB()->query("UPDATE lehrer SET
                    lehrerKuerzel = '" . DB::getDB()->escapeString($lehrer[$i]['kuerzel']) . "',
                    lehrerName = '" . DB::getDB()->escapeString($lehrer[$i]['name']) . "',
                    lehrerVornamen = '" . DB::getDB()->escapeString($lehrer[$i]['vornamen']) . "',
                    lehrerRufname = '" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
                    lehrerGeschlecht = '" . DB::getDB()->escapeString($lehrer[$i]['geschlecht']) . "',
                    lehrerZeugnisunterschrift = '" . DB::getDB()->escapeString($lehrer[$i]['zeugnisname']) . "',
                    lehrerAmtsbezeichnung = '" . DB::getDB()->escapeString($lehrer[$i]['amtsbezeichnung']) . "',
                    lehrerNameVorgestellt = '" . DB::getDB()->escapeString($lehrer[$i]['namevorgestellt']) . "',
                    lehrerNameNachgestellt = '" . DB::getDB()->escapeString($lehrer[$i]['namenachgestellt']) . "',
                    schulnummer = " . (int)$this->schulnummer  . "
                    WHERE lehrerID = ".$data['lehrerID']);


                    // Update USERS Table
                    DB::getDB()->query("UPDATE users SET 
                             userFirstName = '" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
                             userLastName = '" . DB::getDB()->escapeString($lehrer[$i]['name']) . "'
                             WHERE userAsvID = '" . $lehrer[$i]['asvid'] . "'");

                    $b++;
                    $log .= '<br>Vorhanden: '.$lehrer[$i]['xmlid'].' - '.$lehrer[$i]['kuerzel'].' - '.$lehrer[$i]['vorname'].' '.$lehrer[$i]['name'];


                } else {
                    DB::getDB()->query("
                        INSERT INTO lehrer
                            (
                                lehrerAsvID,
                                lehrerKuerzel,
                                lehrerName,
                                lehrerVornamen,
                                lehrerRufname,
                                lehrerGeschlecht,
                                lehrerZeugnisunterschrift,
                                lehrerAmtsbezeichnung,
                                lehrerNameVorgestellt,
                                lehrerNameNachgestellt,
                                schulnummer
                            ) values(
                                '" . DB::getDB()->escapeString($lehrer[$i]['asvid']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['kuerzel']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['name']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['vornamen']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['rufname']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['geschlecht']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['zeugnisname']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['amtsbezeichnung']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['namevorgestellt']) . "',
                                '" . DB::getDB()->escapeString($lehrer[$i]['namenachgestellt']) . "',
                                '".(int)$this->schulnummer."'
                            ) 
                    ");
                    $log .= '<br>Hinzugefügt: '.$lehrer[$i]['xmlid'].' - '.$lehrer[$i]['kuerzel'].' - '.$lehrer[$i]['vorname'].' '.$lehrer[$i]['name'];
                    $a++;

                }
            }
        }

        $this->log('<h4>Lehrer:</h4><i>(Status: ID, Kuerzel, Name )</i>'. $log);
        $this->log($count.' Lehrer durchlaufen:<br>'.$a.' Lehrer hinzugefügt<br>'.$b.' Lehrer waren vorhanden' );

        return true;

    }

    private function loadKlassenleitung()
    {
        // Klassenleitung
        $ret = [];
        //DB::getDB()->query("DELETE FROM klassenleitung");

        $count = 0;
        $a = 0;
        $b = 0;
        $log = '';
        for ($i = 0; $i < count($this->klassen); $i++) {
            for ($k = 0; $k < count($this->klassen[$i]['klassenleitung']); $k++) {
                $ret[] = [
                    'klasseName' => $this->klassen[$i]['name'],
                    'lehrerID' => $this->klassen[$i]['lehrerID'],
                    'klassenleitungArt' => $this->klassen[$i]['art']
                ];

                $count++;
                $data = DB::run('SELECT * FROM klassenleitung WHERE klasseName = :name AND klassenleitungArt = :art ', ['name' => $this->klassen[$i]['name'],'art' => $this->klassen[$i]['klassenleitung'][$k]['art'] ])->fetch();
                if ($data ) {

                    DB::getDB()->query("UPDATE klassenleitung SET
                    lehrerID = " . DB::getDB()->escapeString($this->klassen[$i]['klassenleitung'][$k]['lehrerID']) . "
                    WHERE klasseName = '".$this->klassen[$i]['name']."' AND klassenleitungArt = ".$this->klassen[$i]['klassenleitung'][$k]['art']);

                    $b++;
                    $log .= '<br>Vorhanden: '.$this->klassen[$i]['name'].' - '.$this->klassen[$i]['klassenleitung'][$k]['lehrerID'].' - '.$this->klassen[$i]['klassenleitung'][$k]['art'];

                } else {
                    DB::getDB()->query("INSERT INTO klassenleitung (klasseName, lehrerID, klassenleitungArt)  values('" . DB::getDB()->escapeString($this->klassen[$i]['name']) . "','" . $this->klassen[$i]['klassenleitung'][$k]['lehrerID'] . "','" . $this->klassen[$i]['klassenleitung'][$k]['art'] . "');");
                    $a++;
                    $log .= '<br>Hinzugefügt: '.$this->klassen[$i]['name'].' - '.$this->klassen[$i]['klassenleitung'][$k]['lehrerID'].' - '.$this->klassen[$i]['klassenleitung'][$k]['art'];
                }
            }
        }
        $this->log('<h4>Klassenleitung:</h4><i>(Status: Klassenname, Lehrer ID, Art )</i>'. $log);
        $this->log($count.' Klassenleitungen durchlaufen:<br>'.$a.' Klassenleitungen hinzugefügt<br>'.$b.' Klassenleitungen waren vorhanden' );

        //$this->log($i.' Klassenleitung hinzugefügt oder aktualisiert');

        return $ret;
    }

    private function loadSchueler()
    {



        $ret = [];

        // Lï¿½schen?
        /*
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
                // Besuchten Unterricht löschen
                if ( DB::getDB()->query("DELETE FROM unterricht_besuch  WHERE schuelerAsvID='" . $ss['schuelerAsvID'] . "'") ) {
                    $this->log('Alle Unterrichtsbesuchte gelöscht');
                }
            }
        }
        */
        //


        for ($i = 0; $i < sizeof($this->klassen); $i++) {
            $count = 0;
            $a = 0;
            $b = 0;
            $log = '';
            for ($s = 0; $s < sizeof($this->klassen[$i]['schueler']); $s++) {
                // DB::getDB()->query("DELETE FROM schueler WHERE schuelerAsvID='" . $this->klassen[$i]['schueler'][$s]['asvid'] . "'");



                $data = explode(".", $this->klassen[$i]['schueler'][$s]['geburtsdatum']);
                $gebdatum = $data[2] . '-' . $data[1] . '-' . $data[0];

                if ($this->klassen[$i]['schueler'][$s]['austrittsdatum'] != "") {
                    $data = explode(".", $this->klassen[$i]['schueler'][$s]['austrittsdatum']);
                    $austrittsdatum = "'" . $data[2] . '-' . $data[1] . '-' . $data[0] . "'";
                } else {
                    $austrittsdatum = "NULL";
                }

                $count++;
                $data = DB::run('SELECT * FROM schueler WHERE schuelerAsvID = :id ', ['id' => $this->klassen[$i]['schueler'][$s]['asvid'] ])->fetch();
                if ($data ) {

                    DB::getDB()->query("UPDATE schueler SET
                    schuelerName = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['name']) . "',
                    schuelerVornamen = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['vornamen']) . "',
                    schuelerRufname = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['rufname']) . "',
                    schuelerGeschlecht = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geschlecht']) . "',
                    schuelerGeburtsdatum = '" . $gebdatum . "',
                    schuelerKlasse = '" . $this->klassen[$i]['name'] . "',
                    schuelerAustrittDatum = " . $austrittsdatum . ",
                    schuelerBekenntnis = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['bekenntnis']) . "',
                    schuelerAusbildungsrichtung = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ausbildungsrichtung']) . "',
                    schuelerGeburtsort = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geburtsort']) . "',
                    schuelerGeburtsland = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['geburtsland']) . "',
                    schuelerJahrgangsstufe = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['jahrgangsstufe']) . "',
                    schulerEintrittJahrgangsstufe = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['jahrgangsstufeeintritt']) . "',
                    schuelerEintrittDatum = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['eintrittsdatum']) . "',
                    schuelerNameVorgestellt = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['namevorgestellt']) . "',
                    schuelerNameNachgestellt = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['namenachgestellt']) . "',
                    schuelerGanztagBetreuung = " . (($this->klassen[$i]['schueler'][$s]['ganztag_betreuung'] != '') ? DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ganztag_betreuung']) : 0) . ",
                    schulnummer = '" . (int)$this->schulnummer . "'
                    WHERE schuelerAsvID = '".$this->klassen[$i]['schueler'][$s]['asvid']."'" );

                    $b++;
                    $log .= '<br>Vorhanden: '.$this->klassen[$i]['schueler'][$s]['asvid'].' - '.$this->klassen[$i]['schueler'][$s]['vornamen'].' - '.$this->klassen[$i]['schueler'][$s]['name'];

                    // Update USER data
                    DB::getDB()->query("UPDATE users SET 
                         userFirstName = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['rufname']) . "',
                         userLastName = '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['name']) . "'
                         WHERE userAsvID = '" . $this->klassen[$i]['schueler'][$s]['asvid']."'" );

                } else {
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
                            schuelerGanztagBetreuung,
						    schulnummer

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
							" . (($this->klassen[$i]['schueler'][$s]['ganztag_betreuung'] != '') ? DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['ganztag_betreuung']) : 0) . ",
							".(int)$this->schulnummer."

						)");


                    // unterricht_besuch
                    DB::getDB()->query("DELETE FROM unterricht_besuch WHERE schuelerAsvID='" . $this->klassen[$i]['schueler'][$s]['asvid'] . "'");
                    $values = "";
                    for ($u = 0; $u < sizeof($this->klassen[$i]['schueler'][$s]['unterricht']); $u++) {
                        if ($u > 0) $values .= ",";
                        $values .= "('" . $this->klassen[$i]['schueler'][$s]['unterricht'][$u] . "','" . $this->klassen[$i]['schueler'][$s]['asvid'] . "')";
                    }
                    if ($values != "") {
                        DB::getDB()->query("INSERT INTO unterricht_besuch (unterrichtID, schuelerAsvID) values " . $values);
                    }
                    // Ganztags
                    if ($this->klassen[$i]['schueler'][$s]['ganztag_betreuung'] != "") {
                        DB::getDB()->query("INSERT INTO unterricht_besuch (unterrichtID, schuelerAsvID) values ('1','" . $this->klassen[$i]['schueler'][$s]['asvid'] . "')");
                    }

                    // fremdspreachen
                    $insertsFremdsprachen = [];
                    for ($j = 0; $j < sizeof($this->klassen[$i]['schueler'][$s]['sprachen']); $j++) {
                            $sprache = $this->klassen[$i]['schueler'][$s]['sprachen'][$j];
                            $insertsFremdsprachen[] = "(
                            '" . DB::getDB()->escapeString($this->klassen[$i]['schueler'][$s]['asvid']) . "',
                            '" . DB::getDB()->escapeString($sprache['sortierung']) . "',
                            '" . DB::getDB()->escapeString($sprache['jahrgangsstufe']) . "',
                            '" . DB::getDB()->escapeString($sprache['unterrichtsfach']) . "',
                            '" . DB::getDB()->escapeString($sprache['feststellungspruefung']) . "')";
                    }

                    DB::getDB()->query("DELETE FROM schueler_fremdsprache WHERE schuelerAsvID='" . $this->klassen[$i]['schueler'][$s]['asvid'] . "'");
                    if (sizeof($insertsFremdsprachen) > 0) {
                        DB::getDB()->query("INSERT INTO schueler_fremdsprache (schuelerAsvID, spracheSortierung, spracheAbJahrgangsstufe, spracheFach, spracheFeststellungspruefung) values " . implode(",", $insertsFremdsprachen));
                    }
                    //$this->log(sizeof($insertsFremdsprachen). ' Schueler-Fremdsprechen hinzugefügt');

                    $a++;
                    $log .= '<br>Hinzugefügt: '.$this->klassen[$i]['schueler'][$s]['asvid'].' - '.$this->klassen[$i]['schueler'][$s]['vornamen'].' - '.$this->klassen[$i]['schueler'][$s]['name'].' -  '.$u.' - '.sizeof($insertsFremdsprachen);

                }





            }
            $ret[] = [
                "klasse" => $this->klassen[$i]['name'],
                "anz" => $s
            ];

            $this->log('<h4>Schueler der Klasse '.$this->klassen[$i]['name'].' :</h4><i>(Status: ASV ID, Vorname, Name, Anzahl Unterichte, Anzahl Fremdsprachen )</i>'. $log);
            $this->log($count.' Schueler durchlaufen:<br>'.$a.' Schueler hinzugefügt<br>'.$b.' Schueler waren vorhanden' );


            //$this->log($s.' Schueler der Klasse '.$this->klassen[$i]['name'].' hinzugefügt oder aktualisiert');
        }

        return $ret;
    }

    private function loadDeleteElternMail()
    {
        $ret = [];
        // Kontaktdaten der Eltern

        // Syncen, nicht löschen, da UserIDs gespeichert.
        // DB::getDB()->query("DELETE FROM eltern_email");

        if ( DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
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
        /*
        if ( DB::getDB()->query("TRUNCATE TABLE eltern_adressen") ) {
            $this->log('Alle Eltern Adressen gelöscht');
        }
        if ( DB::getDB()->query("DELETE FROM eltern_telefon") ) {
            $this->log('Alle Eltern Telefonnummern gelöscht');
        }
        */

        $insertsAdressen = array();
        $insertsEmails = array();
        $insertsTelefon = array();

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

                    /*
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
                    */
                    $insertsAdressen[] = [
                        "schuelerASV" => $this->klassen[$i]['schueler'][$s]['asvid'],
                        "wessen" => $wessen,
                        "auskunftsberechtigt" => (($this->klassen[$i]['schueler'][$s]['adressen'][$a]['auskunftsberechtigt'] == "false") ? 0 : 1),
                        "hauptansprechpartner" => (($this->klassen[$i]['schueler'][$s]['adressen'][$a]['hauptansprechpartner'] == "true") ? 1 : 0),
                        "strasse" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['strasse'],
                        "nummer" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['nummer'],
                        "ortsbezeichnung" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['ortsbezeichnung'],
                        "postleitzahl" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['postleitzahl'],
                        "anredetext" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['anredetext'],
                        "anschrifttext" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['anschrifttext'],
                        "familienname" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['familienname'],
                        "vornamen" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['vornamen'],
                        "anrede" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['anrede'],
                        "personentyp" => $this->klassen[$i]['schueler'][$s]['adressen'][$a]['personentyp']
                    ];


                    for ($k = 0; $k < sizeof($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt']); $k++) {


                        if (filter_var(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']), FILTER_VALIDATE_EMAIL) !== false) {

                            if (DB::getGlobalSettings()->elternUserMode == "ASV_MAIL") {
                                $insertsEmails[] = [
                                    "email" => strtolower(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert'])),
                                    "schuelerASV" => $this->klassen[$i]['schueler'][$s]['asvid'],
                                    "id" => $id
                                ];
                                /*
                                DB::getDB()->query("INSERT INTO eltern_email (elternEMail, elternSchuelerAsvID, elternAdresseID) values(
									'" . DB::getDB()->escapeString(strtolower(trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']))) . "',
									'" . $this->klassen[$i]['schueler'][$s]['asvid'] . "'
							,$id) ON DUPLICATE KEY UPDATE elternSchuelerAsvID=elternSchuelerAsvID, elternAdresseID=$id");
                                */
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
                                $insertsTelefon[] = [
                                    "nummer" => trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']),
                                    "schuelerASV" => $this->klassen[$i]['schueler'][$s]['asvid'],
                                    "telefonTyp" => $art,
                                    "kontaktTyp" => trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['anschrifttyp']),
                                    "id" => $id
                                ];
                                /*
                                DB::getDB()->query("INSERT INTO eltern_telefon (telefonNummer, schuelerAsvID, telefonTyp, kontaktTyp, adresseID) values
								(
								'" . trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['wert']) . "',
								'" . $this->klassen[$i]['schueler'][$s]['asvid'] . "',
								'" . $art . "',
								'" . trim($this->klassen[$i]['schueler'][$s]['adressen'][$a]['kontakt'][$k]['anschrifttyp']) . "',$id

								) ON DUPLICATE KEY UPDATE telefonNummer=telefonNummer");
                                */
                            }
                        }


                    }


                }
            }

        }

        //$this->log($count_tel. ' Eltern Telefonnummern hinzugefügt');


        $count = 0;
        $a = 0;
        $b = 0;
        $log = '';
        if (sizeof($insertsAdressen) > 0) {
            foreach ($insertsAdressen as $item) {
                $count++;
                $data = DB::run('SELECT * FROM eltern_adressen WHERE adresseSchuelerAsvID = :id AND adresseWessen = :wessen ', [ 'id' => $item['schuelerASV'], 'wessen' => $item['wessen'] ])->fetch();
                if ($data ) {

                    DB::getDB()->query("UPDATE eltern_adressen SET
                    adresseIsAuskunftsberechtigt = " . DB::getDB()->escapeString($item['auskunftsberechtigt']) . ",
                    adresseIsHauptansprechpartner = " . DB::getDB()->escapeString($item['hauptansprechpartner']) . ",
                    adresseStrasse = '" . DB::getDB()->escapeString($item['strasse']) . "',
                    adresseNummer = '" . DB::getDB()->escapeString($item['nummer']) . "',
                    adresseOrt = '" . DB::getDB()->escapeString($item['ortsbezeichnung']) . "',
                    adressePostleitzahl = '" . DB::getDB()->escapeString($item['postleitzahl']) . "',
                    adresseAnredetext = '" . DB::getDB()->escapeString($item['anredetext']) . "',
                    adresseFamilienname = '" . DB::getDB()->escapeString($item['familienname']) . "',
                    adresseVorname = '" . DB::getDB()->escapeString($item['vornamen']) . "',
                    adresseAnrede = '" . DB::getDB()->escapeString($item['anrede']) . "',
                    adressePersonentyp = '" . DB::getDB()->escapeString($item['personentyp']) . "'
                    WHERE adresseID = ".$data['adresseID']);

                    $b++;
                    $log .= '<br>Vorhanden: '.$item['schuelerASV'].' - '.$item['wessen'].' - '.$item['familienname'].' '.$item['vornamen'];

                } else {
                    $a++;

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
							) VALUES ( NULL,
							'".$item['schuelerASV']."',
							'".$item['wessen']."',
							".$item['auskunftsberechtigt'].",
							".$item['hauptansprechpartner'].",
							'".$item['strasse']."',
							'".$item['nummer']."',
							'".$item['ortsbezeichnung']."',
							'".$item['postleitzahl']."',
							'".$item['anredetext']."',
							'".$item['anschrifttext']."',
							'".$item['familienname']."',
							'".$item['vornamen']."',
							'".$item['anrede']."',
							'".$item['personentyp']."'
							 )
					");

                    $log .= '<br>Hinzugefügt: '.$item['schuelerASV'].' - '.$item['wessen'].' - '.$item['familienname'].' '.$item['vornamen'];


                }
            }
            $this->log('<h4>Eltern Adressen:</h4><i>(Status: Schueler ASV ID, Art, Name )</i>'. $log);
            $this->log($count.' Eltern Adressen durchlaufen:<br>'.$a.' Eltern Adressen hinzugefügt<br>'.$b.' Eltern Adressen waren vorhanden' );


            /*
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
            */
        }
        //$this->log(sizeof($insertsAdressen). ' Eltern Adressen hinzugefügt');

        $count = 0;
        $a = 0;
        $b = 0;
        $log = '';
        if (sizeof($insertsTelefon) > 0) {
            foreach ($insertsTelefon as $item) {
                $count++;
                $data = DB::run('SELECT * FROM eltern_telefon WHERE schuelerAsvID = :id AND telefonTyp = :typ AND adresseID = :aid ', ['id' => $item['schuelerASV'], 'aid' => $item['id'], 'typ' => $item['telefonTyp'] ])->fetch();
                if ($data) {

                    DB::getDB()->query("UPDATE eltern_telefon SET
                    telefonNummer = '" . DB::getDB()->escapeString($item['nummer']) . "',
                    kontaktTyp = '" . DB::getDB()->escapeString($item['kontaktTyp']) . "'
                    WHERE schuelerAsvID = '".$item['schuelerASV']."' AND telefonTyp = '".$item['telefonTyp']."' AND adresseID = ".(int)$item['id'] );

                    $b++;
                    $log .= '<br>Vorhanden: ' . $item['schuelerASV'] . ' - ' . $item['nummer']. ' - ' . $item['kontaktTyp'] . ' - ' . $item['id'];
                } else {
                    $a++;
                    DB::getDB()->query("
						INSERT INTO eltern_telefon
							(
								`telefonNummer`,
								`schuelerAsvID`,
								`telefonTyp`,
								`kontaktTyp`,
								`adresseID`
							) VALUES (
							'" . $item['nummer'] . "',
							'" . $item['schuelerASV'] . "',
							'" . $item['telefonTyp'] . "',
							'" . $item['kontaktTyp'] . "',
							" . $item['id'] . "
							 )
					");

                    $log .= '<br>Hinzugefügt: ' . $item['schuelerASV'] . ' - ' . $item['nummer']. ' - ' . $item['kontaktTyp'] . ' - ' . $item['id'];

                }
            }
            $this->log('<h4>Eltern Telefon:</h4><i>(Status: Schueler ASV ID, Nummer, Typ, ID )</i>' . $log);
            $this->log($count . ' Eltern Telefon durchlaufen:<br>' . $a . ' Eltern Telefon hinzugefügt<br>' . $b . ' Eltern Telefon waren vorhanden');
        }


        $count = 0;
        $a = 0;
        $b = 0;
        $log = '';
        if (sizeof($insertsEmails) > 0) {
            foreach ($insertsEmails as $item) {
                $count++;
                $data = DB::run('SELECT * FROM eltern_email WHERE elternSchuelerAsvID = :id AND elternEMail = :email ', ['id' => $item['schuelerASV'], 'email' => $item['email']])->fetch();
                if ($data) {

                    DB::getDB()->query("UPDATE eltern_email SET
                    elternAdresseID = " . DB::getDB()->escapeString($item['id']) . "
                    WHERE elternSchuelerAsvID = '".$item['schuelerASV']."' AND elternEMail = '".$item['email']."' " );


                    $b++;
                    $log .= '<br>Vorhanden: ' . $item['schuelerASV'] . ' - ' . $item['email'] . ' - ' . $item['id'];
                } else {
                    $a++;
                    DB::getDB()->query("
						INSERT INTO eltern_email
							(
								`elternEMail`,
								`elternSchuelerAsvID`,
								`elternAdresseID`
							) VALUES (
							'".$item['email']."',
							'".$item['schuelerASV']."',
							".$item['id']."
							 )
					");

                    $log .= '<br>Hinzugefügt: ' . $item['schuelerASV'] . ' - ' . $item['email'] . ' - ' . $item['id'];

                }
            }
            $this->log('<h4>Eltern E-Mails: (ASV_MAIL)</h4><i>(Status: Schueler ASV ID, E-Mail, ID )</i>'. $log);
            $this->log($count.' Eltern E-Mails durchlaufen:<br>'.$a.' Eltern E-Mails hinzugefügt<br>'.$b.' Eltern E-Mails waren vorhanden' );


        }

        return $insertsAdressen;
    }



    private function backupTables($tables = [])
    {
        foreach($tables as $table)
        {
            $result = DB::getDB()->query('SELECT * FROM '.$table);
            $num_fields = mysqli_num_fields($result);
            $num_rows = mysqli_num_rows($result);

            $return.= 'DROP TABLE IF EXISTS '.$table.';';
            $row2 = mysqli_fetch_row(DB::getDB()->query( 'SHOW CREATE TABLE '.$table));
            $return.= "\n\n".$row2[1].";\n\n";
            $counter = 1;

            //Over tables
            for ($i = 0; $i < $num_fields; $i++)
            {   //Over rows
                while($row = mysqli_fetch_row($result))
                {
                    if($counter == 1){
                        $return.= 'INSERT INTO '.$table.' VALUES(';
                    } else{
                        $return.= '(';
                    }

                    //Over fields
                    for($j=0; $j<$num_fields; $j++)
                    {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n","\\n",$row[$j]);
                        if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                        if ($j<($num_fields-1)) { $return.= ','; }
                    }

                    if($num_rows == $counter){
                        $return.= ");\n";
                    } else{
                        $return.= "),\n";
                    }
                    ++$counter;
                }
            }
            $return.="\n\n\n";
        }

        //save file
        $path = PATH_DATA.'ext_import'.DS;
        if (!is_dir($path)) {
            mkdir($path);
        }
        $path .= 'backups'.DS;
        if (!is_dir($path)) {
            mkdir($path);
        }
        $fileName = $path.'db-backup_'.date('Y-m-d_H-i',time()).'.sql';
        $handle = fopen($fileName,'w+');
        fwrite($handle,$return);
        if(fclose($handle)){
            return true;
        }
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
