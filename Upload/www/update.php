<?php

@error_reporting(E_ERROR);

/**
 *
 * Software is licensed unter GPLv2
 *
 * (c) Christian Spitschka, 2012-2019
 *
 * _______  _______                    _        _______ _________ _       _________ _______  _______  _
 * (  ____ \(  ____ \|\     /||\     /|( \      (  ____ \\__   __/( (    /|\__   __/(  ____ \(  ____ )( (    /|
 * | (    \/| (    \/| )   ( || )   ( || (      | (    \/   ) (   |  \  ( |   ) (   | (    \/| (    )||  \  ( |
 * | (_____ | |      | (___) || |   | || |      | (__       | |   |   \ | |   | |   | (__    | (____)||   \ | |
 * (_____  )| |      |  ___  || |   | || |      |  __)      | |   | (\ \) |   | |   |  __)   |     __)| (\ \) |
 *       ) || |      | (   ) || |   | || |      | (         | |   | | \   |   | |   | (      | (\ (   | | \   |
 * /\____) || (____/\| )   ( || (___) || (____/\| (____/\___) (___| )  \  |   | |   | (____/\| ) \ \__| )  \  |
 * \_______)(_______/|/     \|(_______)(_______/(_______/\_______/|/    )_)   )_(   (_______/|/   \__/|/    )_)
 *
 *
 * Version 1.2.0
 *
 */

$wartungsmodus = file_get_contents("../data/wartungsmodus/status.dat");

if($wartungsmodus != "") {
    // Im Wartungsmodus.
    // Update könnte ausgeführt werden.

    $updateInfo = file_get_contents("../data/update.json");

    if($updateInfo === false) {
        echo("update fail. (not availible)");
        exit(0);
    }

    $updateInfo = json_decode($updateInfo, true);

    if($_REQUEST['key'] == $updateInfo['updateKey']) {

        ?>

        <html>
        <head>
            <title>Update</title>
        </head>
        <body>

        Update wird durchgeführt...<br /><br />

        Sichere alte Anwendungsdaten...<br />
        <?php

        if(rename("../framework", "../framework_old_" . $updateInfo['updateFromVersion'] . "_" . rand(1000,9999))) {
            echo("Alte Version gesichert.<br >");
            echo("Spiele neue Programmversion ein...<br />");
            if(rename("../data/update/Upload/framework", "../framework")) {
                echo("Einspielen der neuen Programmversion erfolgreich.");
                echo("<br />");
                echo("Wartungsmodus wird deaktiviert.<br >");
                file_put_contents("../data/wartungsmodus/status.dat","");
                echo("Wartungsmodus deaktiviert.<br >");
                echo("Installtion abschließen:<br />");

                echo("<a href=\"index.php?page=Update&toVersion=" . urlencode($updateInfo['updateToVersion']) . "&key=" . $updateInfo['updateKey'] . "\">Update abschließen</a>");

            }
            else {
                echo("<font color=\"red\">FEHLER: NEUE VERSION KONNTE NICHT KOPIERT WERDEN!</font><br />");
            }

        }
        else {
            echo("<font color=\"red\">FEHLER: ALTE VERSION KONNTE NICHT GELÖSCHT WERDEN!</font><br />");
        }

        ?>

        </body>


        </html>


        <?php

    }
    else {
        echo("key fail.");
        exit(0);
    }

}

else {
    header("Location: index.php?");
}