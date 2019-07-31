<?php

include_once '../data/settings/userlib.class.php';
include_once '../data/settings/mainsettings.php';

include('./startup.php');

// TODO: SSL in der Api erzwingen, falls nicht bereits vom Server erfolgt. Achtung: Clients müssen das können.


// TODO: ApiHandler auf "Call" statt Page umstellen.
new apihandler((isset($_REQUEST['page']) && $_REQUEST['page'] != "") ? $_REQUEST['page'] : 'index');

?>