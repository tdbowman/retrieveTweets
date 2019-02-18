<?php


// Variables
$test = FALSE;
$e = 0;
$errors = array();

$dir = __DIR__."/data"; # Where JSON files were stored
$dir2 = __DIR__."/processed"; # Where parsed JSON files are moved
$schema = "`db`"; # CHANGEME -> db name


/////////////////////////////////////////////
// CONNECTION CREDENTIALS
/////////////////////////////////////////////
$server =	'localhost'; #CHANGEME -> database host
$user =		'user'; #CHANGEME -> database user
$pass =		'password'; #CHANGEME -> database password
$db =		'database'; #CHANGEME -> database name