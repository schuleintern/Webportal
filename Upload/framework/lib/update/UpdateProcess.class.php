<?php 



class UpdateProcess {

    private $updateToVersion = "";

    /**
     *
     * @param $updateToVersion Zielversion
     */
	public function __construct($updateToVersion) {
	    $this->updateToVersion = $updateToVersion;
	}

    /**
     * @return boolean|string
     */
	public function updateReady(){
        // Überprüft, ob das Update möglich ist.

        $errors = [];

        if(!is_writable("../framework")) {
            $errors[] = "Das Verzeichnis 'framework' ist nicht beschreibbar. (Rechte 777)";
        }

        if(!is_writable("./")) {
            $errors[] = "Das Verzeichnis './' ist nicht beschreibbar. (Rechte 777)";
        }

        if(sizof($errors) == 0) {
            return true;
        }
        else return implode("\n",$errors);
    }

    public function doUpdate() {

	    // Templates zurücksetzen
        DB::getDB()->query("TRUNCATE templates");



	    if(DB::getVersion() == "1.0.0" && $this->updateToVersion == "1.0.1") {
            return $this->update_100_to_101();
        }

        if( DB::getVersion() == "1.1.1" &&  $this->updateToVersion == "1.2.0") {
            return $this->update_111_to_120();
        }

	    return true;
    }

    private function update_100_to_101() {

	    // Aktionen zum Update durchführen
        // Datenbankupdate auch hier ausführen.



	    return true;
    }


    private function update_111_to_120() {

        DB::getDB()->query("
            ALTER TABLE `users`
            ADD `userAutoresponse` tinyint(1) NOT NULL DEFAULT '0';
            ALTER TABLE `users`
            ADD `userAutoresponseText` longtext NOT NULL;

            -- Create syntax for TABLE 'ganztags_gruppen'
            CREATE TABLE `ganztags_gruppen` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `sortOrder` int(11) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

            -- Create syntax for TABLE 'ganztags_schueler'
            CREATE TABLE `ganztags_schueler` (
            `asvid` varchar(200) NOT NULL DEFAULT '',
            `info` varchar(255) DEFAULT NULL,
            `gruppe` int(11) DEFAULT NULL,
            `tag_mo` tinyint(1) DEFAULT NULL,
            `tag_di` tinyint(1) DEFAULT NULL,
            `tag_mi` tinyint(1) DEFAULT NULL,
            `tag_do` tinyint(1) DEFAULT NULL,
            `tag_fr` tinyint(1) DEFAULT NULL,
            `tag_sa` tinyint(1) DEFAULT NULL,
            `tag_so` tinyint(1) DEFAULT NULL,
            PRIMARY KEY (`asvid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


            ALTER TABLE `schueler`
            ADD `schuelerGanztagBetreuung` int(11) NOT NULL DEFAULT '0';

            ALTER TABLE `messages_messages`
            ADD `messageIsForwardFrom` int(11) NOT NULL DEFAULT '0';
        ");



	    return true;
    }

}