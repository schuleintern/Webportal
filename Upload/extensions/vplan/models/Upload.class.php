<?php

/**
 *
 */
class extVplanModelUpload
{


    public static function addFile($file = false, $override = false, $ext = false)
    {

        if (!$file) {
            return false;
        }
        if (!file_exists($file)) {
            return false;
        }



        

        // Untis
        if (DB::getGlobalSettings()->stundenplanSoftware == "UNTIS") {

            

            $settings = [];
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-stunde') -1] = 'stunde';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-klasse') -1] = 'klasse';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-user_alt') -1] = 'user_alt';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-user_neu') -1] = 'user_neu';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-fach_alt') -1] = 'fach_alt';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-fach_neu') -1] = 'fach_neu';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-raum_alt') -1] = 'raum_alt';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-raum_neu') -1] = 'raum_neu';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-info_1') -1] = 'info1';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-info_2') -1] = 'info2';
            $settings[(int)DB::getSettings()->getValue('extVplan-import-untis-col-info_3') -1] = 'info3';
            
            if ( count($settings) < 1) {
                return false;
            }


            /*
            $settings = [
                10 => 'info3',
                9 => 'info2',
                8 => 'info1',
                7 => 'fach_neu',
                6 => 'raum',
                5 => false,
                4 => 'fach_alt',
                3 => 'klasse',
                2 => 'stunde',
                1 => 'user_alt',
                0 => 'user_neu'
            ];
            */


            // UNTIS HTML Export
            if ($ext == 'htm' || $ext == 'html') {

                $info = [];
                $data = [];
                $day = false;

                $content = file_get_contents($file);
               // $content = str_replace('charset=iso-8859-1','charset=utf-8',$content);               

                $dom = new DOMDocument();
                $dom->loadHTML($content);
                //$dom->loadHTMLFile($file);

                $divs = $dom->getElementsByTagName('div');
                foreach( $divs as $div ) {
                    if ( $div->getAttribute('class') == 'mon_title') {
                        $foo = explode(' ',trim($div->textContent));
                        $foo = explode('.',$foo[0]);
                        $day = $foo[2].'-'.$foo[1].'-'.$foo[0];
                    }
                }


                $tables = $dom->getElementsByTagName('table');
                foreach( $tables as $table ) {
                    
                    if ( $table->getAttribute('class') == 'info') {
                        $trs = $table->getElementsByTagName('tr');
                        foreach($trs as $tr) {
                            $line = [];
                            $tds = $tr->getElementsByTagName('td');
                            foreach($tds as $td) {
                            
                                $line[] = $td->textContent;
                            }
                            if (count($line) >= 1) {
                                $info[] = $line;
                            }
                        }
                    }

                    if ( $table->getAttribute('class') == 'mon_list') {
                        $trs = $table->getElementsByTagName('tr');
                        foreach($trs as $tr) {
                            $line = [];
                            $tds = $tr->getElementsByTagName('td');
                            foreach($tds as $key => $td) {
                                if ($settings[$key]) {
                                    $line[$settings[$key]] = trim($td->textContent);
                                }
                            }
                            if (count($line) >= 1) {
                                $data[] = $line;
                            }
                        }
                    }
                }

                /*
                echo $day;

                echo '<pre>';
                print_r($data);
                echo '</pre>';

                echo '<pre>';
                print_r($info);
                echo '</pre>';
                
                exit;
                */

                $overrideDay = false;
                if (strpos($file,'seite1') !== false) {
                    $overrideDay = true;
                }

                if ( self::insertDayData($day, $data, $info, $overrideDay) ) {
                    return true;
                }
            }


            // UNTIS GPU CSV/TEXT EXPORT
            if ($ext == 'txt') {

                $days = [];
                $heute = date('Y-m-d', time());
                $content = file($file);
                for ($i = 0; $i < sizeof($content); $i++) {
                    $line = explode(",", str_replace("\"", "", str_replace("\r", "", str_replace("\n", "", utf8_encode($content[$i])))));
                    $date = substr($line[1], 0, 4) . '-' . substr($line[1], 4, 2) . '-' . substr($line[1], 6, 2);
                    if ($date && $date >= $heute) {
                        if (!is_array($days[$date])) {
                            $days[$date] = [];
                        }
                        $days[$date][] = [
                            "date" => $date,
                            "klasse" => $line[14],
                            "stunde" => $line[2],
                            "user_alt" => $line[5],
                            "user_neu" => $line[6],
                            "fach_neu" => $line[9],
                            "fach_alt" => $line[7],
                            "raum_neu" => $line[12],
                            "info_1" => $line[16]
                        ];
                    }
                }
                
                $info = '';
                $overrideDay = true;
                foreach($days as $day => $data) {
                    self::insertDayData($day, $data, $info, $overrideDay);
                }
                return true;
            }



            return false;


            
        }


        // TimeSub
        if (DB::getGlobalSettings()->stundenplanSoftware == "TIME2007") {

            $content = file_get_contents($file);

            if (!$content) {
                return false;
            }

            $dom = new DOMDocument;
            $dom->loadHTML($content);

            $finder = new DomXPath($dom);
            $classname = "KBlock Kopf";
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");


            $body = $dom->getElementsByTagName('body');
            foreach ($body as $node) {

                //echo $node->nodeValue, PHP_EOL;

                if ($node->hasChildNodes()) {
                    foreach ($node->childNodes as $box) {

                        $date = '';

                        $text = '';

                        if ($box->hasChildNodes()) {
                            //echo 'tag:';

                            foreach ($box->childNodes as $box2) {
                                if ($box2->hasChildNodes()) {


                                    switch ($box2->getAttribute('class')) {
                                        case 'KBlock Kopf':

                                            if (strstr($box2->childNodes[0]->childNodes[0]->getAttribute('class'), 'Datum')) {

                                                $date = explode('.', trim(explode(',', $box2->childNodes[0]->childNodes[0]->textContent)[1]));
                                                $date = $date[2] . '-' . $date[1] . '-' . $date[0];
                                                //echo $date;

                                            }

                                            break;
                                        case 'VorspannBlock':

                                            $text .= nl2br($box2->nodeValue);



                                            break;
                                        case 'BitteBeachtenBlock':
                                            break;
                                        case 'VBlock':

                                            foreach ($box2->childNodes as $node) {
                                                if ($date) {
                                                    $line = ['date' => $date];
                                                    $i = 0;

                                                    if ($node->hasChildNodes()) {
                                                        foreach ($node->childNodes as $node2) {

                                                            if ($node2->nodeType == 1 && $node2->nodeName == 'td') {

                                                                //echo $i.'-'.$node2->nodeValue.' * ';
                                                                switch ($i) {
                                                                    case 0:
                                                                        $line['user_neu'] = $node2->nodeValue;
                                                                        break;
                                                                    case 1:
                                                                        $line['stunde'] = intval(explode(' ', $node2->nodeValue)[1]);
                                                                        break;
                                                                    case 2:
                                                                        $line['klasse'] = $node2->nodeValue;
                                                                        break;
                                                                    case 3:
                                                                        $line['fach_alt'] = $node2->nodeValue;
                                                                        break;
                                                                    case 4:
                                                                        $line['raum_neu'] = $node2->nodeValue;
                                                                        break;
                                                                    case 5:
                                                                        $line['user_alt'] = $node2->nodeValue;
                                                                        break;
                                                                    case 6:
                                                                        $line['info'] = $node2->nodeValue;
                                                                        break;
                                                                }
                                                                $i++;
                                                            }
                                                        }
                                                    }
                                                }

                                                self::insertData($line);
                                            }

                                            break;
                                    }
                                }
                            }

                            self::insertDay($date, $text);
                        }
                    }
                }
            }
        }
        return true;
    }

    private static function insertDayData($day = false, $data = false, $info = false, $overrideDay = true)
    {

        if (!$day || !$data) {
            return false;
        }

        
        if ($overrideDay) {

            self::truncateTable($day);

            $infoHTML = '';
            if ($info) {
                $infoHTML = '<div class="info flex">';
                foreach($info as $tr) {

                    $infoHTML .= '<div  class="flex-1 flex-row">';
                    $i = 0;
                    foreach($tr as $td) {
                        $flex = 1;
                        $i++;
                        if ($i == 2) {
                            $flex = 2;
                        }
                        $infoHTML .= '<div class="flex-'.$flex.'">'.$td.'</div>';
                    }
                    $infoHTML .= '</div>';

                }
                $infoHTML .= '</div>';
            }
            
            self::insertDay($day, $infoHTML);
        }
        

        foreach($data as $item) {
            self::insertData([
                "date" => $day,
                "klasse" => $item['klasse'],
                "stunde" => $item['stunde'],
                "user_alt" => $item['user_alt'],
                "user_neu" => $item['user_neu'],
                "fach_neu" => $item['fach_neu'],
                "fach_alt" => $item['fach_alt'],
                "raum_neu" => $item['raum_neu'],
                "raum_alt" => $item['raum_alt'],
                "info_1" => (string)trim($item['info1']),
                "info_2" => (string)trim($item['info2']),
                "info_3" => (string)trim($item['info3'])
            ]);
        }

        return true;
        
    }

    private static function truncateTable($day = false)
    {

        if ($day) {

            DB::getDB()->query("DELETE FROM `ext_vplan_day` WHERE `date` = '".$day."' ;");

            if (DB::getDB()->query("DELETE FROM `ext_vplan_list` WHERE `date` = '".$day."' ;")) {
                return true;
            }
        } else {
            if (DB::getDB()->query("TRUNCATE TABLE `ext_vplan_list`;")) {
                return true;
            }
        }
        
        return false;
    }

    private static function insertData($data = false)
    {

        if (!$data || !$data['klasse']) {
            return false;
        }

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return false;
        }

        if (DB::getDB()->query("INSERT INTO ext_vplan_list
            (
                createdTime,
                createdUser,
                date,
                klasse,
                stunde,
                user_alt,
                user_neu,
                fach_neu,
                fach_alt,
                raum_neu,
                raum_alt,
                info_1,
                info_2,
                info_3
            ) values(
                '" . date('Y-m-d H:i', time()) . "',
                " . $userID . ",
                '" . DB::getDB()->escapeString($data['date']) . "',
                '" . DB::getDB()->escapeString($data['klasse']) . "',
                '" . DB::getDB()->escapeString($data['stunde']) . "',
                '" . DB::getDB()->escapeString($data['user_alt']) . "',
                '" . DB::getDB()->escapeString($data['user_neu']) . "',
                '" . DB::getDB()->escapeString($data['fach_neu']) . "',
                '" . DB::getDB()->escapeString($data['fach_alt']) . "',
                '" . DB::getDB()->escapeString($data['raum_neu']) . "',
                '" . DB::getDB()->escapeString($data['raum_alt']) . "',
                '" . DB::getDB()->escapeString($data['info_1']) . "',
                '" . DB::getDB()->escapeString($data['info_2']) . "',
                '" . DB::getDB()->escapeString($data['info_3']) . "'
            )
		    ")) {
            return true;
        }
        return false;
    }

    private static function insertDay($date = false, $text = false)
    {

        if (!$date) {
            return false;
        }

        $userID = DB::getSession()->getUser()->getUserID();
        if (!$userID) {
            return false;
        }

        if (DB::getDB()->query("INSERT INTO ext_vplan_day
            (
                createdTime,
                createdUser,
                date,
                text
            ) values(
                '" . date('Y-m-d H:i', time()) . "',
                " . $userID . ",
                '" . DB::getDB()->escapeString($date) . "',
                '" . DB::getDB()->escapeString($text) . "'
            )
		    ")) {
            return true;
        }
        return false;
    }
}
