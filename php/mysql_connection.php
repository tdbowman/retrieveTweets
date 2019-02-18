<?php
/////////////////////////////////////////////
// NAME: 			mysql_connection.php
// VERSION: 		1.3
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x, MySQL
// DESCRIPTION: 	This connects to MySQL db
/////////////////////////////////////////////

// initialize MySQLi
$mysqli = mysqli_init();

if (!$mysqli) {
	die('mysqli_init failed');
}
if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 20)) {
    die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
}
if (!$mysqli->real_connect($server, $user, $pass, $db)) {
	die('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
}
// ensure characterset encoding
$mysqli->set_charset("utf8mb4");

