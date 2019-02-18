#!/usr/bin/php
<?php
//////////////////////////////////////////////////////////
#NAME: 			retreive_100Tweets_usingTweetID.php
#VERSION: 		1.4
#ORIG DATE: 	June 15, 2017
#AUTHOR: 		Timothy David Bowman
#REQUIREMENTS:	PHP 5.x, MySQL
#DESCRIPTION: 	This sends 100 tweet IDs to Twitter API
#               and returns JSON files
//////////////////////////////////////////////////////////
set_time_limit(0);
error_reporting(E_ERROR);
date_default_timezone_set('UTC');
header('Content-Type: application/json');

///////////////////////////////////////////
// INCLUDE LIBRARIES AND SET DEFAULT VARS 
///////////////////////////////////////////
require_once __DIR__."/config/retrieve100Tweets_config.php"; // php
require_once __DIR__."/config/retrieve100Tweets_TwitterAPI_config.php"; // Twitter API
require_once __DIR__."/php/mysql_connection.php"; // mysql
require_once __DIR__."/php/library_Twitter_100Tweets.php"; // php
require_once __DIR__."/php/library_time.php"; // php 
// Require the TwitterOAuth library. http://github.com/abraham/twitteroauth
require_once __DIR__."/twitteroauth/twitteroauth.php";

// CONNECT TO TWITTER API
$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);


if($test) {
	
	// CHECK API Status
	$status = checkAPIStatus($connection, $options);
	print_r($status);
	
	$api_status = $status->resources->statuses->{'/statuses/lookup'}; 
	$api_status = json_decode(json_encode($api_status), true);
	print_r($api_status);

	// if zero remaining API calls, sleep
	echo "Remaining: ".$api_status['remaining'].PHP_EOL;
	if($api_status['remaining'] == 0) {
		$now = time();
		$reset_time = $api_status['reset'];
		$difference = $reset_time - $now;

		echo PHP_EOL."WAITING ".date("i",$difference)." min for API...".PHP_EOL;
		$minutes = date("i",$difference);
		echo $minutes;
	} else {
		echo "Remaining: ".$api_status['remaining'].PHP_EOL;
	}
    
    exit;
	
}

//////////////////////////////
// CHECK NUMBER FOR EXIT
//////////////////////////////

	$sql_exit_check = "SELECT `id` from `".$schema."`.`".$tweeters."` WHERE `flag`=1;";
	if ($result_check = $mysqli->query($sql_exit_check)) {
		if($result_check->num_rows == 0) {

			echo "None to Process.".PHP_EOL;
			$execution_time = totalTime($time_start);
			echo "Total minutes running: ".round($execution_time,2).PHP_EOL;
			echo "END".PHP_EOL;
			// free result set
			$result_check->close();
			// QUIT PROGRAM
			exit;
		}
	}

//////////////////////////////
// PREPARE QUERY
//////////////////////////////
if (!($stmt_update = $mysqli->prepare("UPDATE `".$schema."`.`".$tweeters."` SET `flag`=2 WHERE `id`=?"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}

//////////////////////////////
// ERRROR CHECK
//////////////////////////////
if(count($errors) > 0) {
	print_r($errors);
	$execution_time = totalTime($time_start);
	echo "Total minutes running: ".round($execution_time,2).PHP_EOL;
	echo "END".PHP_EOL;
	exit;
	exit;
}



//////////////////////////////
// RETRIEVE TWEET IDS
//////////////////////////////
$sql_twitter_handles = "SELECT `id`, `posts:author:tweet_id` 
FROM `".$schema."`.`".$tweeters."` 
WHERE `flag`=1
ORDER BY `id` ASC LIMIT 90000;";

// Run query
if ($result_query = $mysqli->query($sql_twitter_handles)) {
   while($row = $result_query->fetch_assoc()) {
	   $id_array[] = $row['id'];
	   $tweeter_ids[] = $row['posts:author:tweet_id'];   
   }
}

// CREATE CHUNKS of 100 for TWITTER API CALL
$chunks = array_chunk($tweeter_ids, 100);
$chunks_pks = array_chunk($id_array, 100);
$count_chunks = count($chunks);



	// give us a count
	echo PHP_EOL;
	echo "Chunks: ".$count_chunks.PHP_EOL;
	echo PHP_EOL."Total loops of 100 Tweets: ".$count_chunks.PHP_EOL;
	echo PHP_EOL;



////////////////////////////////////
// LOOP THROUGH CHUNKS OF 100 IDs
////////////////////////////////////
if($count_chunks >= 1) {
	for($i=0; $i<=$count_chunks; $i++) {

		// get profiles
		$list_of_ids = implode(',', $chunks[$i]);

		// get Tweets
		// lookup has limit of 900 per 15 minutes
		$success = retrieve100Tweets($connection, $dir, $list_of_ids);
		$success = (bool)$success;
		
		if($success) {
			// loop through chunks
			foreach($chunks_pks[$i] as $val) {

				
			   if(!$test) {		
					if (!$stmt_update->bind_param("i", $val)) {
						$errors[]="E".$e++." -> Main binding parameters failed for update: (" . $stmt_update->errno . ") " . $stmt_update->error . "<br/>";
					}
					if (!$stmt_update->execute()) {
						$errors[]="E".$e++." -> Main execute failed for update: (" . $stmt_update->errno . ") " . $stmt_update->error . "<br/>";
					}
					$stmt_update -> free_result();
				}	

			}

			if(count($errors) > 0) {
				print_r($errors);
				$execution_time = totalTime($time_start);
				echo "Total minutes running: ".round($execution_time,2).PHP_EOL;
				echo "END".PHP_EOL;
				exit;
			}	
		} else {
			print "ERROR: Could not write file of tweets. There was an error with 'retrieve100Tweets()' function.";
			continue;
		}
		
		$list_of_ids=NULL;
		$success = NULL;
	

	}
} else {
	
	echo "PROBLEM WITH CHUNKS!";
	$execution_time = totalTime($time_start);
	echo "Total minutes running: ".round($execution_time,2).PHP_EOL;
	echo "END".PHP_EOL;
	exit;
}

//////////////////////////////////////////
// OUTPUT TOTAL EXECUTTION TIME FOR LOG
//////////////////////////////////////////
$execution_time = totalTime($time_start);
echo "Total minutes running: ".round($execution_time,2).PHP_EOL;
echo "END".PHP_EOL;
?>
