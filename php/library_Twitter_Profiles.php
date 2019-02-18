<?php
/////////////////////////////////////////////
// NAME: 			library_Twitter_Profiles.php
// VERSION: 		1.4
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x
// DESCRIPTION: 	This is simply a collection 
//                  of useful PHP functions
/////////////////////////////////////////////

function retrieve100Profiles($connection, $dir, $users, $entities=false) {
	// https://dev.twitter.com/rest/reference/get/users/lookup

	$file_flag = 1;
	
	if($users == '' || !$connection) {
		return 'Error: Retrieve 100 profiles function'; 
		exit;
	}
	
	$options = array();
	#$options['screen_name'] = trim($users);
	$options['user_id'] = trim($users);
	$options['include_entities'] = (bool)trim($entities);
	$profiles = $connection->get('users/lookup', $options);

	$encoded = json_encode($profiles);
	$decoded = json_decode($encoded, true);
	
	# FOR ERROR
	if(array_key_exists('errors', $decoded)) {
		
		echo "ERRORS".PHP_EOL;
		print_r($decoded['errors']);
		
		
		if($decoded['errors'][0]['code'] == '17') {
			echo "ERROR: 17".PHP_EOL;
			$profiles = NULL;
			
			$data = str_getcsv($options['user_id']);
			for($i=0; $i<count($data); $i++) {
					$profiles[]=$data[$i];
			}
			
			$encoded = json_encode($profiles);
			$file_flag = 2;
			
		} else {

			// CHECK API Status
			// API status check has limit of 900 per 15 minute
			$status = checkAPIStatus($connection, $options);
			$api_status = $status->resources->users->{'/users/lookup'};
			$api_status = json_decode(json_encode($api_status), true);
			print_r($api_status);

			// if zero remaining API calls, sleep
			echo "Remaining: ".$api_status['remaining'].PHP_EOL;
			if($api_status['remaining'] == 0) {
				$now = time();
				$reset_time = $api_status['reset'];
				$difference = $reset_time - $now;
				$minutes = date("i",$difference);
				echo PHP_EOL."WAITING ".$minutes." min for API...".PHP_EOL;
				sleep($minutes);
			} else {
				echo "Remaining: ".$api_status['remaining'].PHP_EOL;
			}
		
			retrieve100Profiles($connection, $dir, $users, $entities); # recursion
		}
	}
	
	
	// write to file
	$filename = NULL;
	if($file_flag == 2) {
		$filename = $dir.'/'.time().'-profiles.notfound.json';
	} else {
		$filename = $dir.'/'.time().'-profiles.json';
	}
	$message = fileWrite($filename, $encoded, "w");
	
	return array($profiles, $file_flag);				 
}



function checkAPIStatus($connection, $options) {
	if(!$connection) {
		return 'Error: CheckAPIStatus function';
		exit;
	}

	$resources = array();
	$resources = $options;
	$status = $connection->get('application/rate_limit_status', $resources);
	return $status;
}

function getTotalTweetsCount($profile) {
	
	$count = $profile->statuses_count;
	return (int)$count;
}

function getLastDeliveredTweetID($tweets) {
	
	if(is_object($tweets)) {
		if($tweets->error || $tweets->errors) {
			return NULL;
		}
	}
	
	$count = count($tweets);
	$goBack = (int)$count-1;
	
	$tweet_id = $tweets[$goBack]->id; // get id
	return (int)$tweet_id;
}

