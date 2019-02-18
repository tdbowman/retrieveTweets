<?php
//////////////////////////////////////////////////////////
#NAME: 			session_start.php
#VERSION: 		1.4
#ORIG DATE: 	December 15, 2016
#AUTHOR: 		Timothy David Bowman
#REQUIREMENTS:	PHP 5.x, MySQL
#DESCRIPTION: 	This starts a PHP session for logging in
//              to applications
//////////////////////////////////////////////////////////

session_save_path('/home/username/tmp/'); #CHANGEME
session_start();
