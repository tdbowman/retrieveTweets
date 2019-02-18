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

// http://www.php.net/manual/en/function.is-writable.php#41194
function fileWrite($filename, &$content, $mode) {
	if (!$fp = @fopen($filename, $mode)) {
		return "Cannot open file ($filename)";
	}
	if (!is_writable($filename)) {
		if (!chmod($filename, 0666)) {
			 return "Cannot change the mode of file ($filename)";
			 
			 
		};
	}
	if (file_put_contents($filename, $content) === FALSE) {
		return "Cannot write to file ($filename)";
		
	}
	if (!fclose($fp)) {
		return "Cannot close file ($filename)";
		
	}
	
	return TRUE;
} 


function retrieve100Tweets($connection, $dir, $ids, $entities=true) {
	// https://developer.twitter.com/en/docs/tweets/post-and-engage/api-reference/get-statuses-lookup

	$file_flag = 1;
	
	if($ids == '' || !$connection) {
		echo 'Error 0: No ids sent to the function'; 
		return false;
	}
	
    $local_ids = $ids;
    $local_dir = $dir;
    $local_connection = $connection;
    
	$options = array();
	$options['id'] = $local_ids;
	$options['include_entities'] = (bool)trim($entities);
	$options['trim_user'] = false;
	$options['map'] = true;
	$options['include_ext_alt_text'] = true;
	
	$tweets = $local_connection->get('statuses/lookup', $options);

	$encoded = json_encode($tweets);
	$decoded = json_decode($encoded, true);
	
	# FOR ERROR
	if(array_key_exists('errors', $decoded)) {
        // error - tweets not found
		if($decoded['errors'][0]['code'] == '17') {
			echo "ERROR 1: 17".PHP_EOL;
			$tweets = NULL;
			
			$data = str_getcsv($options['id']);
			for($i=0; $i<count($data); $i++) {
					$tweets[]=$data[$i];
			}
			
			$encoded = json_encode($tweets);
			$file_flag = 2;
            
		} elseif ($decoded['errors'][0]['code'] == '130') {
        // error - Twitter API over capacity
            
            
            echo "IDs:".$local_ids.PHP_EOL;
            
            echo "ERROR 2: Twitter Over Capacity; sleeping 15 seconds.".PHP_EOL;
            sleep(15); // wait 15 seconds
            retrieve100Tweets($local_connection, $local_dir, $local_ids); // recursion
            
		} elseif ($decoded['errors'][0]['code'] == '88') {
        // error - Twitter API over capacity
            
            
            echo "IDs:".$local_ids.PHP_EOL;
            
                // TAKE A BREAK 
                // CHECK API Status
                $status = checkAPIStatus($local_connection, $options);
                //print_r($status);

                $api_status = $status->resources->statuses->{'/statuses/lookup'}; 
                # $status->resources->users->{'/users/lookup'}
                $api_status = json_decode(json_encode($api_status), true);
                //print_r($api_status);

                echo "ERROR 4: Rating Limit Met!".PHP_EOL;
                // if zero remaining API calls, sleep
                echo "Remaining calls: ".$api_status['remaining'].PHP_EOL;
                if($api_status['remaining'] == 0) {
                    $now = time();
                    $reset_time = $api_status['reset'];
                    $difference = $reset_time - $now;

                    echo PHP_EOL."WAITING ".date("i",$difference)." min for API...".PHP_EOL;
                    $minutes = date("i",$difference);
                    $seconds = $minutes*60;
                    sleep($seconds);
                    
                    
                    retrieve100Tweets($local_connection, $local_dir, $local_ids); // recursion
                    
                } 

            
        } else {
        // error - Some other error
            
            echo "ERROR 3: ".$decoded['errors'][0]['code'].PHP_EOL;
			$tweets = NULL;
			
			$data = str_getcsv($options['id']);
			for($i=0; $i<count($data); $i++) {
					$tweets[]=$data[$i];
			}
			
			$encoded = json_encode($tweets);
			$file_flag = 2;
        }
	}
	
	
	// write to file
	$filename = NULL;
	if($file_flag == 2) {
		$filename = $local_dir.'/'.time().'-tweets.notfound.json';
	} else {
		$filename = $local_dir.'/'.time().'-tweets.json';
	}
	$fileWritten = fileWrite($filename, $encoded, "w");
	
	if($fileWritten) {
		return true;				 
	} else {
		return false;
	}
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
