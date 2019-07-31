<?php

include_once '../data/config/config.php';
include_once '../data/config/userlib.class.php';

include('./startup.php');

error_reporting(E_ALL);

new cronhandler();
