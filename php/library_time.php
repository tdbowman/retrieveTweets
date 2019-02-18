<?php
/////////////////////////////////////////////
// NAME: 			library_time.php
// VERSION: 		1.3
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x, MySQL
// DESCRIPTION: 	This connects to MySQL db
/////////////////////////////////////////////

function totalTime($time_start) {
	/*****************************
	END TRACK TIME
	*****************************/
	$time_end = microtime(true);
	$total_time = ($time_end - $time_start)/60;
	
	return $total_time;
}

/*****************************
TRACK TIME
*****************************/
$time_start = microtime(true);
