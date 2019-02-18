<?php
/////////////////////////////////////////////
// NAME: 			library_twitter.php
// VERSION: 		1.4
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x
// DESCRIPTION: 	This is simply a collection 
//                  of useful PHP functions
/////////////////////////////////////////////


function retrieveIndividualTweets($connection, $id, $stmt_update) {	
	if(trim($id) == '' || !$connection) {
		return 'Error 1: Retrieve Individual Tweets function';
		exit;
	}
	
	$options = array();
	$options['id'] = trim($id);
	$options['trim_user'] = 'false';
	$options['include_my_retweet'] = 'true';
	$options['include_entities'] = 'true';
	$options['map'] = 'true';
	$options['include_ext_alt_text'] = 'true'; 

	$tweet = $connection->get('statuses/lookup', $options);
	return tweet;	
	exit;
	
	
	
	if (!$stmt_update->bind_param("sis", $tweet, $flag, $id)) {
		$errors[]="E".$e++." -> Main binding parameters failed for update: (" . $stmt_update->errno . ") " . $stmt_update->error . "<br/>";
	}
	if (!$stmt_update->execute()) {
		$errors[]="E".$e++." -> Main execute failed for update: (" . $stmt_update->errno . ") " . $stmt_update->error . "<br/>";
	}
	$stmt_update -> free_result();
	
	return true;
}


function retrieveTweets($connection, $user, $last=NULL, $limit=200) {	
	if(trim($user) == '' || !$connection) {
		return 'Error 1: Retrieve Tweets function';
		exit;
	}
	
	$options = array();
	$options['screen_name'] = trim($user);
	$options['include_rts'] = 'true';
	$options['count'] = (int)trim($limit);	 
	
	if($last != NULL) {
		$options['max_id'] = (int)trim($last);	
		$filelast = ''.trim($last);
	} else {
		$filelast = '0';	
	}

	$tweets = $connection->get('statuses/user_timeline', $options);

	// write to file
	$filename = NULL;
	$filename = 'data/'.trim($user).'-'.$filelast.'.json';
	$encoded = json_encode($tweets);
	$message = fileWrite($filename, $encoded, "w");
	
	if($message == TRUE) {
		return $tweets;	
	} else {
		return $message;
	}
}

function retrieveProfile($connection, $user, $fileWrite, $entities=false) {
	if($user == '' || !$connection) {
		return 'Error: Retrieve profile function';
		exit;
	}
	$options = array();
	$options['screen_name'] = trim($user);
	$options['include_entities'] = (bool)trim($entities);
	
	$profile = $connection->get('users/show', $options);
	
	// write to file
	if($fileWrite) {
		$filename = NULL;
		$filename = 'data/'.trim($user).'-profile.json';
		$encoded = json_encode($profile);
		$message = fileWrite($filename, $encoded, "w");
	}
	
	return $profile;
}

function retrieveProfiles($connection, $users, $entities=false) {
	if($users == '' || !$connection) {
		return 'Error: Retrieve profile function';
		exit;
	}
	$options = array();
	$options['screen_name'] = trim($users);
	$options['include_entities'] = (bool)trim($entities);
	
	$profiles = $connection->get('users/lookup', $options);
		
	return $profiles;
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
	
	if($tweets->error || $tweets->errors) {
		return NULL;
		break;
	}
	
	$count = count($tweets);
	$goBack = (int)$count-1;
	
	$tweet_id = $tweets[$goBack]->id; // get id
	return (int)$tweet_id;
}

