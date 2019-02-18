#!/usr/bin/php
<?php
/*****************************************
SETTINGS & INCLUDES
*****************************************/
set_time_limit (0); // TIME LIMIT
error_reporting(E_ERROR | E_PARSE); // no warnings
//error_reporting(E_ALL);
date_default_timezone_set('UTC');	 // TIME ZONE
header('Content-Type: text/json; charset=utf-8');


require_once __DIR__."/php/mysql_connection.php";

// Variables
$test = FALSE;
$e = 0;
$errors = array();

$dir = __DIR__."/data"; # Where JSON files were stored
$dir2 = __DIR__."/processed"; # Where parsed JSON files are moved
$schema = "`db`"; # CHANGEME -> db name

function jsonDecode($json, $assoc = true) {
    $ret = json_decode($json, $assoc);
    if ($error = json_last_error())
    {
        $errorReference = [
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
            JSON_ERROR_SYNTAX => 'Syntax error.',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.',
        ];
        $errStr = isset($errorReference[$error]) ? $errorReference[$error] : "Unknown error ($error)";
        throw new \Exception("JSON decode error ($error): $errStr");
    }
    return $ret;
}

function convertDate($twitter_datetime = '') {
	$mysql_format = date("Y-m-d H:i:s", strtotime($twitter_datetime)); 
	return $mysql_format;
}

//http://stackoverflow.com/questions/1115835/php-function-convert-array-to-string
function makestring($array) {
  $outval = '';
  foreach($array as $key=>$value) {
    if(is_array($value)) {
     	 $outval .= makestring($value);
      } else {
      	$outval .= str_replace("\r", "", str_replace("\n", '', trim($value)))."|";
      }
    }
  rtrim($outval, '|');
  return $outval;
  }


/*****************************************
PREPARED MYSQL STATEMENTS
******************************************/
// INSERT
// tweet (28)
if (!($insert_tweet = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweets`
(`created_at`, `id`, `id_str`, `text`, `source`, `truncated`, `in_reply_to_status_id`, `in_reply_to_status_id_str`, `in_reply_to_user_id`, `in_reply_to_user_id_str`, `in_reply_to_screen_name`, `user_id`, `geo`, `coordinates`, `place`, `quoted_status_id`, `quoted_status_id_str`, `is_quote_status`, `quoted_status`, `retweeted_status`, `quote_count`, `reply_count`,`retweet_count`, `favorite_count`, `entities`, `extended_entities`, `favorited`, `retweeted`, `possibly_sensitive`, `filter_level`, `lang`, `matching_rules`, `scopes`, `withheld_copyright`, `withheld_in_countries`, `withheld_scope`, `hashtags_count`, `symbols_count`, `urls_count`, `user_mentions_count`, `media_count`,`is_rt`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
if (!($insert_null_tweet = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweets`
(`id`) VALUES (?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// hashtag
if (!($insert_hashtags = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_hashtags`
(`text`, `indices`, `tweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// media
if (!($insert_media = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_media`
(`sizes`, `media_url_https`, `expanded_url`, `id_str`, `url`, `id`, `type`, `indices`,  `display_url`, `media_url`, `tweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// user_mentions
if (!($insert_mentions = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_mentions`
(`screen_name`, `name`, `id`, `id_str`, `indices`, `tweet_id`) VALUES (?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// symbols
if (!($insert_symbols = $mysqli->prepare("INSERT INTO ".$schema.".`tweet_symbols`
(`text`, `indices`, `tweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// urls
if (!($insert_urls = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_urls`
(`url`, `expanded_url`, `display_url`, `indices`, `final_url`, `domain`, `tweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}

////////////////////////////
// profile
////////////////////////////
if (!($insert_profile = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`profiles` (
`id`, `id_str`, `name`, `screen_name`, `location`, `url`, `description`, `derived`, `protected`, `verified`, `followers_count`, `friends_count`, `listed_count`, `favourites_count`, `statuses_count`, `created_at`, `utc_offset`, `time_zone`, `geo_enabled`, `lang`, `contributors_enabled`, `profile_background_color`, `profile_background_image_url`, `profile_background_image_url_https`, `profile_image_tile`, `profile_banner_url`, `profile_image_url`, `profile_image_url_https`, `profile_link_color`, `profile_sidebar_border_color`, `profile_sidebar_fill_color`, `profile_text_color`, `profile_use_background_image`, `default_profile`, `default_profile_image`, `withheld_in_countries`, `withheld_scope`,`is_translator`, `following`, `notifications`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}


////////////////////////////
// retweets
////////////////////////////
if (!($insert_retweet = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweets`
(`created_at`, `id`, `id_str`, `text`, `source`, `truncated`, `in_reply_to_status_id`, `in_reply_to_status_id_str`, `in_reply_to_user_id`, `in_reply_to_user_id_str`, `in_reply_to_screen_name`, `user_id`, `geo`, `coordinates`, `place`, `quoted_status_id`, `quoted_status_id_str`, `is_quote_status`, `quoted_status`, `retweeted_status`, `quote_count`, `reply_count`,`retweet_count`, `favorite_count`, `entities`, `extended_entities`, `favorited`, `retweeted`, `possibly_sensitive`, `filter_level`, `lang`, `matching_rules`, `scopes`, `withheld_copyright`, `withheld_in_countries`, `withheld_scope`, `hashtags_count`, `symbols_count`, `urls_count`, `user_mentions_count`, `media_count`,`is_rt`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// hashtag
if (!($insert_rthashtags = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_hashtags`
(`text`, `indices`, `retweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// media
if (!($insert_rtmedia = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_media`
(`sizes`, `media_url_https`, `expanded_url`, `id_str`, `url`, `id`, `type`, `indices`,  `display_url`, `media_url`, `retweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// user_mentions
if (!($insert_rtmentions = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_mentions`
(`screen_name`, `name`, `id`, `id_str`, `indices`, `retweet_id`) VALUES (?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// symbols
if (!($insert_rtsymbols = $mysqli->prepare("INSERT INTO ".$schema.".`retweet_symbols`
(`text`, `indices`, `retweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// urls
if (!($insert_rturls = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_urls`
(`url`, `expanded_url`, `display_url`, `indices`, `final_url`, `domain`, `retweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}

if (!($insert_rtprofile = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`profiles` (
`id`, `id_str`, `name`, `screen_name`, `location`, `url`, `description`, `derived`, `protected`, `verified`, `followers_count`, `friends_count`, `listed_count`, `favourites_count`, `statuses_count`, `created_at`, `utc_offset`, `time_zone`, `geo_enabled`, `lang`, `contributors_enabled`, `profile_background_color`, `profile_background_image_url`, `profile_background_image_url_https`, `profile_image_tile`, `profile_banner_url`, `profile_image_url`, `profile_image_url_https`, `profile_link_color`, `profile_sidebar_border_color`, `profile_sidebar_fill_color`, `profile_text_color`, `profile_use_background_image`, `default_profile`, `default_profile_image`, `withheld_in_countries`, `withheld_scope`,`is_translator`, `following`, `notifications`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}


if(count($errors) > 0) {
	print_r($errors);
	exit;
}

/*****************************************
 FILE READING
*****************************************/
if ($handle = opendir($dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            
            echo PHP_EOL.$entry.PHP_EOL;
            
            $retweet_data_flag = 0;
			$contents = file_get_contents($dir.'/'.$entry);
			$data = jsonDecode($contents);
            $keys = array_keys($data['id']); // changed here because JSON delivers tweet JSON differently
            $count = count($keys);

			// ERRORS
			if($count <= 0) { echo "\NOTHING FOR: ".$entry."\n\n"; continue; }
			if(isset($data['errors'])) { echo "\nPROBLEM WITH: ".$entry."\n\n"; continue; }
			if(isset($data['error'])) { echo "\nPROBLEM WITH: ".$entry." says ".$data['error']."\n\n"; continue; }
		
			// Loop
			for($i=0; $i<$count; $i++) {
				
                                
                // get altmetric id
                $alt_id = $keys[$i];
                
                // QUERY FOR DUPS
                $sql = "SELECT `tid` FROM ".$schema.".`tweets` WHERE `id`=".$alt_id.";";  
                $results = $mysqli->query($sql);
                $num = $results->num_rows;
                if($num == 1) { 
                    echo "DUPLICATE: ".$alt_id.PHP_EOL;    
                    continue; 
                }

                
                if($data['id'][$alt_id] == NULL) {
                    
                    if (!$insert_null_tweet->bind_param("s", $alt_id)) {
                        $errors[]="E".$e++." -> Main binding parameters failed for insert null tweet: (" . $insert_null_tweet->errno . ") " . $insert_null_tweet->error . "<br/>";
                    }
                    if (!$insert_null_tweet->execute()) {
                        $errors[]="E".$e++." -> Main execute failed for insert null tweet: (" . $insert_null_tweet->errno . ") " . $insert_null_tweet->error . "<br/>";
                    }
                    $insert_null_tweet -> free_result();
                    
                    
                    if(count($errors) > 0) {
                        print_r($errors);
                        exit;
                    }
                    
                    
                } else {

                    $insert['created_at'] = convertDate($data['id'][$keys[$i]]['created_at']);
                    $insert['id'] = $keys[$i];
                    $insert['id_str'] = $data['id'][$keys[$i]]['id_str'];
                    $insert['text'] = str_replace(array("\r", "\n", "\t"), " ", $data['id'][$keys[$i]]['text']);
                    $insert['source'] = $data['id'][$keys[$i]]['source'];
                    $insert['truncated'] = $data['id'][$keys[$i]]['truncated'] ? 1:0;
                    $insert['in_reply_to_status_id'] = $data['id'][$keys[$i]]['in_reply_to_status_id'];
                    $insert['in_reply_to_status_id_str'] = $data['id'][$keys[$i]]['in_reply_to_status_id_str']; 
                    $insert['in_reply_to_user_id'] = $data['id'][$keys[$i]]['in_reply_to_user_id'];
                    $insert['in_reply_to_user_id_str'] = $data['id'][$keys[$i]]['in_reply_to_user_id_str'];
                    $insert['in_reply_to_screen_name'] = $data['id'][$keys[$i]]['in_reply_to_screen_name'];
                    $insert['user'] = $data['id'][$keys[$i]]['user']; // user profile
                    $insert['user_id'] = $data['id'][$keys[$i]]['user']['id'];

                    // deprecated as of April 2018
                    if(is_array($data['id'][$keys[$i]]['geo'])) {
                        $insert['geo'] = makestring($data['id'][$keys[$i]]['geo']);
                    } else {
                        $insert['geo'] = $data['id'][$keys[$i]]['geo'];	
                    }
                        // old
                        if(is_array($data['id'][$keys[$i]]['coordinates'])) {
                            $insert['coordinates'] = makestring($data['id'][$keys[$i]]['coordinates']);
                        } else {
                            $insert['coordinates'] = $data['id'][$keys[$i]]['coordinates'];	
                        }
                        //

                    if(is_array($data['id'][$keys[$i]]['place'])) {
                        $insert['place'] = makestring($data['id'][$keys[$i]]['place']);
                    } else {
                        $insert['place'] = $data['id'][$keys[$i]]['place'];
                    }
                    ///////////////////
                    //  new fields
                    ///////////////////
                    $insert['quoted_status_id'] = $data['id'][$keys[$i]]['quoted_status_id'];
                    $insert['quoted_status_id_str'] = $data['id'][$keys[$i]]['quoted_status_id_str'];
                    $insert['is_quote_status'] = $data['id'][$keys[$i]]['is_quote_status'];
                    if(is_array($data['id'][$keys[$i]]['quoted_status'])) {
                        $insert['quoted_status'] = makestring($data['id'][$keys[$i]]['quoted_status']);
                    } else {
                        $insert['quoted_status'] = $data['id'][$keys[$i]]['quoted_status'];
                    }
                    ///////////////////


                        // old
                        $insert['contributors'] = $data['id'][$keys[$i]]['contributors'];
                        //

                    if(is_array($data['id'][$keys[$i]]['retweeted_status'])) {
                        $retweet_data_flag = 1;
                        $insert['retweeted_status'] = $retweet_data_flag;

                    } else {
                        $retweet_data_flag = 0;
                    }

                    ///////////////////
                    //  new fields
                    ///////////////////	
                    $insert['quote_count'] = $data['id'][$keys[$i]]['quote_count'];
                    $insert['reply_count'] = $data['id'][$keys[$i]]['reply_count'];
                    ///////////////////

                    $insert['retweet_count'] = $data['id'][$keys[$i]]['retweet_count'];
                    $insert['favorite_count'] = $data['id'][$keys[$i]]['favorite_count'];
                    //arrays of data
                    if(is_array($data['id'][$keys[$i]]['entities'])) {
                        $insert['entities'] = makestring($data['id'][$keys[$i]]['entities']);
                    } else {
                        $insert['entities'] = $data['id'][$keys[$i]]['entities'];	
                    }

                    ///////////////////
                    //  new fields
                    /////////////////	
                    if(is_array($data['id'][$keys[$i]]['extended_entities'])) {
                        $insert['extended_entities'] = makestring($data['id'][$keys[$i]]['extended_entities']);
                    } else {
                        $insert['extended_entities'] = $data['id'][$keys[$i]]['extended_entities'];	
                    }
                    /////////////////

                    $insert['favorited'] = $data['id'][$keys[$i]]['favorited'] ? 1:0;	
                    $insert['retweeted'] = $data['id'][$keys[$i]]['retweeted'] ? 1:0;				
                    $insert['possibly_sensitive'] = $data['id'][$keys[$i]]['possibly_sensitive'] ? 1:0;

                    ///////////////////
                    //  new fields
                    /////////////////
                    $insert['filter_level'] = $data['id'][$keys[$i]]['filter_level'];
                    /////////////////

                    $insert['lang'] = $data['id'][$keys[$i]]['lang'];							

                    ///////////////////
                    //  new fields
                    /////////////////
                    if(is_array($data['id'][$keys[$i]]['matching_rules'])) {
                        $insert['matching_rules'] = makestring($data['id'][$keys[$i]]['matching_rules']);
                    } else {
                        $insert['matching_rules'] = $data['id'][$keys[$i]]['matching_rules'];	
                    }
                    if(is_array($data['id'][$keys[$i]]['scopes'])) {
                        $insert['scopes'] = makestring($data['id'][$keys[$i]]['scopes']);
                    } else {
                        $insert['scopes'] = $data['id'][$keys[$i]]['scopes'];	
                    }	
                    $insert['withheld_copyright'] = $data['id'][$keys[$i]]['withheld_copyright'];
                    $insert['withheld_in_countries'] = $data['id'][$keys[$i]]['withheld_in_countries'];
                    $insert['withheld_scope'] = $data['id'][$keys[$i]]['withheld_scope'];
                    /////////////////				


                    ///////////////////
                    //  MY COUNTS
                    /////////////////		
                    $insert['hashtags_count'] = count($data['id'][$keys[$i]]['entities']['hashtags']);
                    $insert['symbols_count'] = count($data['id'][$keys[$i]]['entities']['symbols']);
                    $insert['urls_count'] = count($data['id'][$keys[$i]]['entities']['urls']);
                    $insert['user_mentions_count'] = count($data['id'][$keys[$i]]['entities']['user_mentions']);
                    $insert['media_count'] = count($data['id'][$keys[$i]]['entities']['media']);
                    // retweet check
                    if(preg_match('/^RT +@[^ :]+:?/ui', $data['id'][$keys[$i]]['text'])) {
                        $insert['is_rt'] = 1;
                    } else {
                        $insert['is_rt'] = 0;	
                    }


                    /***********************
                    / TWEET INSERTION
                    ***********************/
                    if (!$insert_tweet->bind_param("ssssssssssssssssssssssssssssssssssssssssss", 
                        $insert['created_at'],
                        $insert['id'],
                        $insert['id_str'],
                        $insert['text'],
                        $insert['source'],
                        $insert['truncated'],
                        $insert['in_reply_to_status_id'],
                        $insert['in_reply_to_status_id_str'],
                        $insert['in_reply_to_user_id'],
                        $insert['in_reply_to_user_id_str'],
                        $insert['in_reply_to_screen_name'],
                        $insert['user_id'],
                        $insert['geo'],
                        $insert['coordinates'],
                        $insert['place'],
                        $insert['quoted_status_id'],
                        $insert['quoted_status_id_str'],
                        $insert['is_quoted_status'],
                        $insert['quoted_status'],
                        $insert['retweeted_status'],
                        $insert['quote_count'],
                        $insert['reply_count'],
                        $insert['retweet_count'],
                        $insert['favorite_count'],
                        $insert['entities'],
                        $insert['extended_entities'],
                        $insert['favorited'],
                        $insert['retweeted'],
                        $insert['possibly_sensitive'],
                        $insert['filter_level'],
                        $insert['lang'],
                        $insert['matching_rules'],
                        $insert['scopes'],
                        $insert['withheld_copyright'],
                        $insert['withheld_in_countries'],
                        $insert['withheld_scope'],
                        $insert['hashtags_count'],
                        $insert['symbols_count'],
                        $insert['urls_count'],
                        $insert['user_mentions_count'],
                        $insert['media_count'],
                        $insert['is_rt']
                    )) {
                        $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_tweet->errno . ") " . $insert_tweet->error . "<br/>";
                    }
                    if (!$insert_tweet->execute()) {
                        $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_tweet->errno . ") " . $insert_tweet->error . "<br/>";
                    }
                    $tid = $mysqli->insert_id;
                    $insert_tweet -> free_result();


                        if($tid != 0) {
                            // HASHTAGS
                            if($insert['hashtags_count'] > 0) {
                                $hashtags = ($data['id'][$keys[$i]]['entities']['hashtags']);

                                if($test) {
                                    echo "HASHTAGS\n";
                                    print_r($hashtags);	
                                }
                                for($j=0; $j<count($hashtags); $j++){
                                    if (!$insert_hashtags->bind_param("sss", $hashtags[$j]['text'], makestring($hashtags[$j]['indices']), $insert['id'])) {
                                        $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_hashtags->errno . ") " . $insert_hashtags->error . "<br/>";
                                    }	
                                    if (!$insert_hashtags->execute()) {
                                        $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_hashtags->errno . ") " . $insert_hashtags->error . "<br/>";
                                    }
                                    $insert_hashtags -> free_result();	
                                }
                            }
                            // SYMBOLS
                            if($insert['symbols_count'] > 0) {
                                $symbols = ($data['id'][$keys[$i]]['entities']['symbols']);

                                if($test) {
                                    echo "SYMBOLS\n";
                                    print_r($symbols);
                                }
                                for($j=0; $j<count($symbols); $j++){
                                    if (!$insert_symbols ->bind_param("sss", $symbols[$j]['text'], makestring($symbols[$j]['indices']), $insert['id'])) {
                                        $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_symbols->errno . ") " . $insert_symbols->error . "<br/>";
                                    }	
                                    if (!$insert_symbols->execute()) {
                                        $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_symbols->errno . ") " . $insert_symbols->error . "<br/>";
                                    }
                                    $insert_symbols -> free_result();	
                                }
                            }
                            // URLS

                            if($insert['urls_count'] > 0) {
                                $urls = ($data['id'][$keys[$i]]['entities']['urls']);

                                if($test) {
                                    echo "URLs\n";
                                    print_r($urls);
                                }
                                for($j=0; $j<count($urls); $j++){
                                    if (!$insert_urls ->bind_param("sssssss", $urls[$j]['url'], $urls[$j]['expanded_url'], $urls[$j]['display_url'], makestring($urls[$j]['indices']), $urls[$j]['final_url'], $urls[$j]['domain'],  $insert['id'])) {
                                        $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_urls->errno . ") " . $insert_urls->error . "<br/>";
                                    }	
                                    if (!$insert_urls->execute()) {
                                        $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_urls->errno . ") " . $insert_urls->error . "<br/>";
                                    }
                                    $insert_urls -> free_result();
                                }
                            }

                            // USER MENTIONS
                            if($insert['user_mentions_count'] > 0) {
                                $mentions = ($data['id'][$keys[$i]]['entities']['user_mentions']);

                                if($test) {
                                    echo "MENTIONS\n";
                                    print_r($mentions);
                                }
                                for($j=0; $j<count($mentions); $j++){
                                    if (!$insert_mentions->bind_param("ssssss", $mentions[$j]['screen_name'], $mentions[$j]['name'], $mentions[$j]['id'], $mentions[$j]['id_str'],  makestring($mentions[$j]['indices']), $insert['id'])) {
                                        $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_mentions->errno . ") " . $insert_mentions->error . "<br/>";
                                    }	
                                    if (!$insert_mentions->execute()) {
                                        $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_mentions->errno . ") " . $insert_mentions->error . "<br/>";
                                    }
                                    $insert_mentions -> free_result();			
                                }
                            }

                            // MEDIA
                            if($insert['media_count'] > 0) {
                                $media = ($data['id'][$keys[$i]]['entities']['media']);

                                if($test) {
                                    echo "MEDIA\n";
                                    print_r($media);
                                }
                                for($j=0; $j<count($media); $j++){
                                    if (!$insert_media->bind_param("sssssssssss", makestring($media[$j]['sizes']), $media[$j]['media_url_https'], $media[$j]['expanded_url'], $media[$j]['id_str'], $media[$j]['url'], $media[$j]['id'], $media[$j]['type'], makestring($media[$j]['indices']),  $media[$j]['display_url'], $media[$j]['media_url'], $insert['id'])) {
                                        $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_media->errno . ") " . $insert_media->error . "<br/>";
                                    }	
                                    if (!$insert_media->execute()) {
                                        $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_media->errno . ") " . $insert_media->error . "<br/>";
                                    }
                                    $insert_media -> free_result();		
                                }
                            }





                            /////////////////////
                            // profile data
                            /////////////////////
                            $insertprof = NULL;

                            $insertprof['id']= $data['id'][$keys[$i]]['user']['id']; 
                            $insertprof['id_str']= $data['id'][$keys[$i]]['user']['id_str']; 
                            $insertprof['name']= $data['id'][$keys[$i]]['user']['name']; 
                            $insertprof['screen_name']= $data['id'][$keys[$i]]['user']['screen_name']; 
                            $insertprof['location']= $data['id'][$keys[$i]]['user']['location']; 
                            $insertprof['url']= $data['id'][$keys[$i]]['user']['url']; 
                            $insertprof['description']= str_replace(array("\r", "\n", "\t"), " ", $data['id'][$keys[$i]]['user']['description']); 
                            $insertprof['derived']= makestring($data['id'][$keys[$i]]['user']['derived']); // new
                            $insertprof['protected']= $data['id'][$keys[$i]]['user']['protected'] ? 1:0; 
                            $insertprof['verified']= $data['id'][$keys[$i]]['user']['verified'] ? 1:0; 
                            $insertprof['followers_count']= $data['id'][$keys[$i]]['user']['followers_count']; 
                            $insertprof['friends_count']= $data['id'][$keys[$i]]['user']['friends_count']; 
                            $insertprof['listed_count']= $data['id'][$keys[$i]]['user']['listed_count']; 
                            $insertprof['favourites_count']= $data['id'][$keys[$i]]['user']['favourites_count']; 
                            $insertprof['statuses_count']= $data['id'][$keys[$i]]['user']['statuses_count']; 
                            $insertprof['created_at']= convertDate($data['id'][$keys[$i]]['user']['created_at']); 
                            $insertprof['utc_offset']= $data['id'][$keys[$i]]['user']['utc_offset']; 
                            $insertprof['time_zone']= $data['id'][$keys[$i]]['user']['time_zone']; 
                            $insertprof['geo_enabled']= $data['id'][$keys[$i]]['user']['geo_enabled'] ? 1:0;
                            $insertprof['lang']= $data['id'][$keys[$i]]['user']['lang']; 
                            $insertprof['contributors_enabled']= $data['id'][$keys[$i]]['user']['contributors_enabled'] ? 1:0; 
                            $insertprof['profile_background_color']= $data['id'][$keys[$i]]['user']['profile_background_color']; 
                            $insertprof['profile_background_image_url']= $data['id'][$keys[$i]]['user']['profile_background_image_url']; 
                            $insertprof['profile_background_image_url_https']= $data['id'][$keys[$i]]['user']['profile_background_image_url_https']; 
                            $insertprof['profile_image_tile']= $data['id'][$keys[$i]]['user']['profile_image_tile'] ? 1:0;   // new
                            $insertprof['profile_banner_url']= $data['id'][$keys[$i]]['user']['profile_banner_url']; // new
                            $insertprof['profile_image_url']= $data['id'][$keys[$i]]['user']['profile_image_url']; 
                            $insertprof['profile_image_url_https']= $data['id'][$keys[$i]]['user']['profile_image_url_https']; 
                            $insertprof['profile_link_color']= $data['id'][$keys[$i]]['user']['profile_link_color']; 
                            $insertprof['profile_sidebar_border_color']= $data['id'][$keys[$i]]['user']['profile_sidebar_border_color']; 
                            $insertprof['profile_sidebar_fill_color']= $data['id'][$keys[$i]]['user']['profile_sidebar_fill_color']; 
                            $insertprof['profile_text_color']= $data['id'][$keys[$i]]['user']['profile_text_color']; 
                            $insertprof['profile_use_background_image']= $data['id'][$keys[$i]]['user']['profile_use_background_image'] ? 1:0;

                            $insertprof['default_profile']= $data['id'][$keys[$i]]['user']['default_profile'] ? 1:0; 
                            $insertprof['default_profile_image']= $data['id'][$keys[$i]]['user']['default_profile_image'] ? 1:0; 
                            $insertprof['withheld_in_countries']= makestring($data['id'][$keys[$i]]['user']['withheld_in_countries']); 
                            $insertprof['withheld_scope']= $data['id'][$keys[$i]]['user']['withheld_scope']; 


                            // depracated
                            $insertprof['is_translator']= $data['id'][$keys[$i]]['user']['is_translator'] ? 1:0; 
                            $insertprof['following']= $data['id'][$keys[$i]]['user']['following'] ? 1:0; 
                            $insertprof['notifications']= $data['id'][$keys[$i]]['user']['notifications'] ? 1:0; 


                            if($test) {			
                                echo "\n\n";
                                print_r($insertprof);
                            }



                            /////////////////////
                            // PROFILE data insert
                            /////////////////////
                            if(!$test) {	

                                if (!$insert_profile->bind_param("ssssssssssssssssssssssssssssssssssssssss", 
                                    $insertprof['id'],
                                    $insertprof['id_str'],
                                    $insertprof['name'],
                                    $insertprof['screen_name'],
                                    $insertprof['location'],
                                    $insertprof['url'],
                                    $insertprof['description'],
                                    $insertprof['derived'],
                                    $insertprof['protected'],
                                    $insertprof['verified'],												  
                                    $insertprof['followers_count'],
                                    $insertprof['friends_count'],
                                    $insertprof['listed_count'],
                                    $insertprof['favourites_count'],
                                    $insertprof['statuses_count'],
                                    $insertprof['created_at'],
                                    $insertprof['utc_offset'],
                                    $insertprof['time_zone'],
                                    $insertprof['geo_enabled'],
                                    $insertprof['lang'],
                                    $insertprof['contributors_enabled'],
                                    $insertprof['profile_background_color'],
                                    $insertprof['profile_background_image_url'],
                                    $insertprof['profile_background_image_url_https'],
                                    $insertprof['profile_background_tile'],
                                    $insertprof['profile_banner_url'],
                                    $insertprof['profile_image_url'],
                                    $insertprof['profile_image_url_https'],
                                    $insertprof['profile_link_color'],
                                    $insertprof['profile_sidebar_border_color'],
                                    $insertprof['profile_sidebar_fill_color'],
                                    $insertprof['profile_text_color'],
                                    $insertprof['profile_use_background_image'],
                                    $insertprof['default_profile'],
                                    $insertprof['default_profile_image'],
                                    $insertprof['withheld_in_countries'],
                                    $insertprof['withheld_scope'],							  
                                    $insertprof['is_translator'],
                                    $insertprof['following'],
                                    $insertprof['notifications']
                                )) {
                                    $errors[]="E".$e++." -> Main binding parameters failed for insert profile: (" . $insert_profile->errno . ") " . $insert_profile->error . "<br/>";
                                }
                                if (!$insert_profile->execute()) {
                                    $errors[]="E".$e++." -> Main execute failed for insert profile: (" . $insert_profile->errno . ") " . $insert_profile->error . "<br/>";
                                }
                                $insert_profile -> free_result();
                            }


                            $insertprof = NULL;


                            /////////////////////
                            // retweet data
                            /////////////////////
                            if($retweet_data_flag) {
                                //$insert['retweeted_status'];
                                $insertrt = NULL;

                                $insertrt['created_at'] = convertDate($data['id'][$keys[$i]]['retweeted_status']['created_at']);
                                $insertrt['id'] = $data['id'][$keys[$i]]['retweeted_status']['id'];
                                $insertrt['id_str'] = $data['id'][$keys[$i]]['retweeted_status']['id_str'];
                                $insertrt['text'] = str_replace(array("\r", "\n", "\t"), " ", $data['id'][$keys[$i]]['retweeted_status']['text']);
                                $insertrt['source'] = $data['id'][$keys[$i]]['retweeted_status']['source'];
                                $insertrt['truncated'] = $data['id'][$keys[$i]]['retweeted_status']['truncated'] ? 1:0;
                                $insertrt['in_reply_to_status_id'] = $data['id'][$keys[$i]]['retweeted_status']['in_reply_to_status_id'];
                                $insertrt['in_reply_to_status_id_str'] = $data['id'][$keys[$i]]['retweeted_status']['in_reply_to_status_id_str']; 
                                $insertrt['in_reply_to_user_id'] = $data['id'][$keys[$i]]['retweeted_status']['in_reply_to_user_id'];
                                $insertrt['in_reply_to_user_id_str'] = $data['id'][$keys[$i]]['retweeted_status']['in_reply_to_user_id_str'];
                                $insertrt['in_reply_to_screen_name'] = $data['id'][$keys[$i]]['retweeted_status']['in_reply_to_screen_name'];
                                $insertrt['user_id'] = $data['id'][$keys[$i]]['retweeted_status']['user']['id'];

                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['geo'])) {
                                    $insertrt['geo'] = makestring($data['id'][$keys[$i]]['retweeted_status']['geo']);
                                } else {
                                    $insertrt['geo'] = $data['id'][$keys[$i]]['retweeted_status']['geo'];	
                                }
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['coordinates'])) {
                                    $insertrt['coordinates'] = makestring($data['id'][$keys[$i]]['retweeted_status']['coordinates']);
                                } else {
                                    $insertrt['coordinates'] = $data['id'][$keys[$i]]['retweeted_status']['coordinates'];	
                                }			
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['place'])) {
                                    $insertrt['place'] = makestring($data['id'][$keys[$i]]['retweeted_status']['place']);
                                } else {
                                    $insertrt['place'] = $data['id'][$keys[$i]]['retweeted_status']['place'];
                                }

                                ///////////////////
                                //  new fields
                                ///////////////////
                                $insertrt['quoted_status_id'] = $data['id'][$keys[$i]]['retweeted_status']['quoted_status_id'];
                                $insertrt['quoted_status_id_str'] = $data['id'][$keys[$i]]['retweeted_status']['quoted_status_id_str'];
                                $insertrt['is_quote_status'] = $data['id'][$keys[$i]]['retweeted_status']['is_quote_status'];
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['quoted_status'])) {
                                    $insertrt['quoted_status'] = makestring($data['id'][$keys[$i]]['retweeted_status']['quoted_status']);
                                } else {
                                    $insertrt['quoted_status'] = $data['id'][$keys[$i]]['retweeted_status']['quoted_status'];
                                }
                                ///////////////////



                                $insertrt['contributors'] = $data['id'][$keys[$i]]['retweeted_status']['retweeted_status']['contributors'];
                                $insertrt['retweet_count'] = $data['id'][$keys[$i]]['retweeted_status']['retweet_count'];
                                $insertrt['favorite_count'] = $data['id'][$keys[$i]]['retweeted_status']['favorite_count'];
                                //arrays of data
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['entities'])) {
                                    $insertrt['entities'] = makestring($data['id'][$keys[$i]]['retweeted_status']['entities']);
                                } else {
                                    $insertrt['entities'] = $data['id'][$keys[$i]]['retweeted_status']['entities'];	
                                }

                                ///////////////////
                                //  new fields
                                /////////////////	
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['extended_entities'])) {
                                    $insertrt['extended_entities'] = makestring($data['id'][$keys[$i]]['retweeted_status']['extended_entities']);
                                } else {
                                    $insertrt['extended_entities'] = $data['id'][$keys[$i]]['retweeted_status']['extended_entities'];	
                                }
                                /////////////////


                                ///////////////////
                                //  new fields
                                ///////////////////	
                                $insertrt['quote_count'] = $data['id'][$keys[$i]]['retweeted_status']['quote_count'];
                                $insertrt['reply_count'] = $data['id'][$keys[$i]]['retweeted_status']['reply_count'];
                                ///////////////////


                                $insertrt['favorited'] = $data['id'][$keys[$i]]['retweeted_status']['favorited'] ? 1:0;	
                                $insertrt['retweeted'] = $data['id'][$keys[$i]]['retweeted_status']['retweeted'] ? 1:0;				
                                $insertrt['possibly_sensitive'] = $data['id'][$keys[$i]]['retweeted_status']['possibly_sensitive'] ? 1:0;


                                ///////////////////
                                //  new fields
                                /////////////////
                                $insertrt['filter_level'] = $data['id'][$keys[$i]]['retweeted_status']['filter_level'];
                                /////////////////


                                $insertrt['lang'] = $data['id'][$keys[$i]]['retweeted_status']['lang'];		

                                ///////////////////
                                //  new fields
                                /////////////////
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['matching_rules'])) {
                                    $insertrt['matching_rules'] = makestring($data['id'][$keys[$i]]['retweeted_status']['matching_rules']);
                                } else {
                                    $insertrt['matching_rules'] = $data['id'][$keys[$i]]['retweeted_status']['matching_rules'];	
                                }
                                if(is_array($data['id'][$keys[$i]]['retweeted_status']['scopes'])) {
                                    $insertrt['scopes'] = makestring($data['id'][$keys[$i]]['retweeted_status']['scopes']);
                                } else {
                                    $insertrt['scopes'] = $data['id'][$keys[$i]]['retweeted_status']['scopes'];	
                                }	
                                $insertrt['withheld_copyright'] = $data['id'][$keys[$i]]['retweeted_status']['withheld_copyright'];
                                $insertrt['withheld_in_countries'] = $data['id'][$keys[$i]]['retweeted_status']['withheld_in_countries'];
                                $insertrt['withheld_scope'] = $data['id'][$keys[$i]]['retweeted_status']['withheld_scope'];
                                /////////////////				


                                ///////////////////
                                //  MY COUNTS
                                /////////////////	
                                $insertrt['hashtags_count'] = count($data['id'][$keys[$i]]['retweeted_status']['entities']['hashtags']);
                                $insertrt['symbols_count'] = count($data['id'][$keys[$i]]['retweeted_status']['entities']['symbols']);
                                $insertrt['urls_count'] = count($data['id'][$keys[$i]]['retweeted_status']['entities']['urls']);
                                $insertrt['user_mentions_count'] = count($data['id'][$keys[$i]]['retweeted_status']['entities']['user_mentions']);
                                $insertrt['media_count'] = count($data['id'][$keys[$i]]['retweeted_status']['entities']['media']);
                                // retweet check
                                if(preg_match('/^RT +@[^ :]+:?/ui', $data['id'][$keys[$i]]['retweeted_status']['text'])) {
                                    $insertrt['is_rt'] = 1;
                                } else {
                                    $insertrt['is_rt'] = 0;	
                                }


                                /***********************
                                / RETWEET INSERTION
                                ***********************/
                                if (!$insert_retweet->bind_param("ssssssssssssssssssssssssssssssssssssssssss", 
                                    $insertrt['created_at'],
                                    $insertrt['id'],
                                    $insertrt['id_str'],
                                    $insertrt['text'],
                                    $insertrt['source'],
                                    $insertrt['truncated'],
                                    $insertrt['in_reply_to_status_id'],
                                    $insertrt['in_reply_to_status_id_str'],
                                    $insertrt['in_reply_to_user_id'],
                                    $insertrt['in_reply_to_user_id_str'],
                                    $insertrt['in_reply_to_screen_name'],
                                    $insertrt['user_id'],
                                    $insertrt['geo'],
                                    $insertrt['coordinates'],
                                    $insertrt['place'],
                                    $insertrt['quoted_status_id'],
                                    $insertrt['quoted_status_id_str'],
                                    $insertrt['is_quoted_status'],
                                    $insertrt['quoted_status'],
                                    $insertrt['retweeted_status'],
                                    $insertrt['quote_count'],
                                    $insertrt['reply_count'],
                                    $insertrt['retweet_count'],
                                    $insertrt['favorite_count'],
                                    $insertrt['entities'],
                                    $insertrt['extended_entities'],
                                    $insertrt['favorited'],
                                    $insertrt['retweeted'],
                                    $insertrt['possibly_sensitive'],
                                    $insertrt['filter_level'],
                                    $insertrt['lang'],
                                    $insertrt['matching_rules'],
                                    $insertrt['scopes'],
                                    $insertrt['withheld_copyright'],
                                    $insertrt['withheld_in_countries'],
                                    $insertrt['withheld_scope'],
                                    $insertrt['hashtags_count'],
                                    $insertrt['symbols_count'],
                                    $insertrt['urls_count'],
                                    $insertrt['user_mentions_count'],
                                    $insertrt['media_count'],
                                    $insertrt['is_rt']
                                )) {
                                    $errors[]="E".$e++." -> Main binding parameters failed for insert retweet: (" . $insert_retweet->errno . ") " . $insert_retweet->error . "<br/>";
                                }
                                if (!$insert_retweet->execute()) {
                                    $errors[]="E".$e++." -> Main execute failed for insert retweet: (" . $insert_retweet->errno . ") " . $insert_retweet->error . "<br/>";
                                }
                                $rtid = $mysqli->insert_id;
                                $insert_retweet -> free_result();

                                // if successful insert
                                if($rtid != 0) {
                                    // HASHTAGS
                                    $hashtags = NULL;
                                    if($insertrt['hashtags_count'] > 0) {
                                        $hashtags = ($data['id'][$keys[$i]]['retweeted_status']['entities']['hashtags']);

                                        if($test) {
                                            echo "HASHTAGS\n";
                                            print_r($hashtags);	
                                        }
                                        for($j=0; $j<count($hashtags); $j++){
                                            if (!$insert_rthashtags->bind_param("sss", $hashtags[$j]['text'], makestring($hashtags[$j]['indices']), $insert['id'])) {
                                                $errors[]="E".$e++." -> Main binding parameters failed for insert retweet: (" . $insert_rthashtags->errno . ") " . $insert_rthashtags->error . "<br/>";
                                            }	
                                            if (!$insert_rthashtags->execute()) {
                                                $errors[]="E".$e++." -> Main execute failed for insert retweet: (" . $insert_rthashtags->errno . ") " . $insert_rthashtags->error . "<br/>";
                                            }
                                            $insert_rthashtags -> free_result();	
                                        }
                                    }
                                    // SYMBOLS
                                    $symbols = NULL;
                                    if($insertrt['symbols_count'] > 0) {
                                        $symbols = ($data['id'][$keys[$i]]['retweeted_status']['entities']['symbols']);

                                        if($test) {
                                            echo "SYMBOLS\n";
                                            print_r($symbols);
                                        }
                                        for($j=0; $j<count($symbols); $j++){
                                            if (!$insert_rtsymbols ->bind_param("sss", $symbols[$j]['text'], makestring($symbols[$j]['indices']), $insert['id'])) {
                                                $errors[]="E".$e++." -> Main binding parameters failed for insert retweet: (" . $insert_rtsymbols->errno . ") " . $insert_rtsymbols->error . "<br/>";
                                            }	
                                            if (!$insert_rtsymbols->execute()) {
                                                $errors[]="E".$e++." -> Main execute failed for insert retweet: (" . $insert_rtsymbols->errno . ") " . $insert_rtsymbols->error . "<br/>";
                                            }
                                            $insert_rtsymbols -> free_result();	
                                        }
                                    }
                                    // URLS
                                    $urls = NULL;
                                    if($insertrt['urls_count'] > 0) {
                                        $urls = ($data['id'][$keys[$i]]['retweeted_status']['entities']['urls']);

                                        if($test) {
                                            echo "URLs\n";
                                            print_r($urls);
                                        }
                                        for($j=0; $j<count($urls); $j++){
                                            if (!$insert_rturls ->bind_param("sssssss", $urls[$j]['url'], $urls[$j]['expanded_url'], $urls[$j]['display_url'], makestring($urls[$j]['indices']), $urls[$j]['final_url'], $urls[$j]['domain'],  $insert['id'])) {
                                                $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_rturls->errno . ") " . $insertrt_urls->error . "<br/>";
                                            }	
                                            if (!$insert_rturls->execute()) {
                                                $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_rturls->errno . ") " . $insert_rturls->error . "<br/>";
                                            }
                                            $insert_rturls -> free_result();
                                        }
                                    }

                                    // USER MENTIONS
                                    $mentions = NULL;
                                    if($insertrt['user_mentions_count'] > 0) {
                                        $mentions = ($data['id'][$keys[$i]]['retweeted_status']['entities']['user_mentions']);

                                        if($test) {
                                            echo "MENTIONS\n";
                                            print_r($mentions);
                                        }
                                        for($j=0; $j<count($mentions); $j++){
                                            if (!$insert_rtmentions->bind_param("ssssss", $mentions[$j]['screen_name'], $mentions[$j]['name'], $mentions[$j]['id'], $mentions[$j]['id_str'],  makestring($mentions[$j]['indices']), $insert['id'])) {
                                                $errors[]="E".$e++." -> Main binding parameters failed for insert retweet: (" . $insert_rtmentions->errno . ") " . $insert_rtmentions->error . "<br/>";
                                            }	
                                            if (!$insert_rtmentions->execute()) {
                                                $errors[]="E".$e++." -> Main execute failed for insert retweet: (" . $insert_rtmentions->errno . ") " . $insert_rtmentions->error . "<br/>";
                                            }
                                            $insert_rtmentions -> free_result();			
                                        }
                                    }

                                    // MEDIA
                                    $media = NULL;
                                    if($insertrt['media_count'] > 0) {
                                        $media = ($data['id'][$keys[$i]]['retweeted_status']['entities']['media']);

                                        if($test) {
                                            echo "MEDIA\n";
                                            print_r($media);
                                        }
                                        for($j=0; $j<count($media); $j++){
                                            if (!$insert_rtmedia->bind_param("sssssssssss", makestring($media[$j]['sizes']), $media[$j]['media_url_https'], $media[$j]['expanded_url'], $media[$j]['id_str'], $media[$j]['url'], $media[$j]['id'], $media[$j]['type'], makestring($media[$j]['indices']),  $media[$j]['display_url'], $media[$j]['media_url'], $insert['id'])) {
                                                $errors[]="E".$e++." -> Main binding parameters failed for insert retweet: (" . $insertrt_media->errno . ") " . $insert_rtmedia->error . "<br/>";
                                            }	
                                            if (!$insert_rtmedia->execute()) {
                                                $errors[]="E".$e++." -> Main execute failed for insert retweet: (" . $insert_rtmedia->errno . ") " . $insert_rtmedia->error . "<br/>";
                                            }
                                            $insert_rtmedia -> free_result();		
                                        }
                                    }


                                    // USER PROFILE
                                    $insertprof = NULL;

                                    $insertprof['id']= $data['id'][$keys[$i]]['retweeted_status']['user']['id']; 
                                    $insertprof['id_str']= $data['id'][$keys[$i]]['retweeted_status']['user']['id_str']; 
                                    $insertprof['name']= $data['id'][$keys[$i]]['retweeted_status']['user']['name']; 
                                    $insertprof['screen_name']= $data['id'][$keys[$i]]['retweeted_status']['user']['screen_name']; 
                                    $insertprof['location']= $data['id'][$keys[$i]]['retweeted_status']['user']['location']; 
                                    $insertprof['url']= $data['id'][$keys[$i]]['retweeted_status']['user']['url']; 
                                    $insertprof['description']= str_replace(array("\r", "\n", "\t"), " ", $data['id'][$keys[$i]]['retweeted_status']['user']['description']); 
                                    $insertprof['derived']= makestring($data['id'][$keys[$i]]['user']['derived']); // new
                                    $insertprof['protected']= $data['id'][$keys[$i]]['retweeted_status']['user']['protected'] ? 1:0; 
                                    $insertprof['verified']= $data['id'][$keys[$i]]['retweeted_status']['user']['verified'] ? 1:0; 
                                    $insertprof['followers_count']= $data['id'][$keys[$i]]['retweeted_status']['user']['followers_count']; 
                                    $insertprof['friends_count']= $data['id'][$keys[$i]]['retweeted_status']['user']['friends_count']; 
                                    $insertprof['listed_count']= $data['id'][$keys[$i]]['retweeted_status']['user']['listed_count']; 
                                    $insertprof['favourites_count']= $data['id'][$keys[$i]]['retweeted_status']['user']['favourites_count']; 
                                    $insertprof['statuses_count']= $data['id'][$keys[$i]]['retweeted_status']['user']['statuses_count']; 
                                    $insertprof['created_at']= convertDate($data['id'][$keys[$i]]['retweeted_status']['user']['created_at']); 
                                    $insertprof['utc_offset']= $data['id'][$keys[$i]]['retweeted_status']['user']['utc_offset']; 
                                    $insertprof['time_zone']= $data['id'][$keys[$i]]['retweeted_status']['user']['time_zone']; 
                                    $insertprof['geo_enabled']= $data['id'][$keys[$i]]['retweeted_status']['user']['geo_enabled'] ? 1:0;
                                    $insertprof['lang']= $data['id'][$keys[$i]]['retweeted_status']['user']['lang']; 
                                    $insertprof['contributors_enabled']= $data['id'][$keys[$i]]['retweeted_status']['user']['contributors_enabled'] ? 1:0; 
                                    $insertprof['profile_background_color']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_background_color']; 
                                    $insertprof['profile_background_image_url']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_background_image_url']; 
                                    $insertprof['profile_background_image_url_https']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_background_image_url_https']; 
                                    $insertprof['profile_image_tile']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_image_tile'] ? 1:0;   // new
                                    $insertprof['profile_banner_url']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_banner_url']; // new
                                    $insertprof['profile_image_url']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_image_url']; 
                                    $insertprof['profile_image_url_https']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_image_url_https']; 
                                    $insertprof['profile_link_color']= $data['id'][$keys[$i]]['user']['retweeted_status']['profile_link_color']; 
                                    $insertprof['profile_sidebar_border_color']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_sidebar_border_color']; 
                                    $insertprof['profile_sidebar_fill_color']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_sidebar_fill_color']; 
                                    $insertprof['profile_text_color']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_text_color']; 
                                    $insertprof['profile_use_background_image']= $data['id'][$keys[$i]]['retweeted_status']['user']['profile_use_background_image'] ? 1:0;

                                    $insertprof['default_profile']= $data['id'][$keys[$i]]['retweeted_status']['user']['default_profile'] ? 1:0; 
                                    $insertprof['default_profile_image']= $data['id'][$keys[$i]]['retweeted_status']['user']['default_profile_image'] ? 1:0; 
                                    $insertprof['withheld_in_countries']= makestring($data['id'][$keys[$i]]['retweeted_status']['user']['withheld_in_countries']); 
                                    $insertprof['withheld_scope']= $data['id'][$keys[$i]]['retweeted_status']['user']['withheld_scope']; 


                                    // depracated
                                    $insertprof['is_translator']= $data['id'][$keys[$i]]['retweeted_status']['user']['is_translator'] ? 1:0; 
                                    $insertprof['following']= $data['id'][$keys[$i]]['retweeted_status']['user']['following'] ? 1:0; 
                                    $insertprof['notifications']= $data['id'][$keys[$i]]['retweeted_status']['user']['notifications'] ? 1:0; 


                                    if($test) {			
                                        echo "\n\n";
                                        print_r($insertprof);
                                    }



                                    /////////////////////
                                    // PROFILE data insert
                                    /////////////////////
                                    if(!$test) {		
                                        if (!$insert_rtprofile->bind_param("ssssssssssssssssssssssssssssssssssssssss", 
                                            $insertprof['id'],
                                            $insertprof['id_str'],
                                            $insertprof['name'],
                                            $insertprof['screen_name'],
                                            $insertprof['location'],
                                            $insertprof['url'],
                                            $insertprof['description'],
                                            $insertprof['derived'],
                                            $insertprof['protected'],
                                            $insertprof['verified'],												  
                                            $insertprof['followers_count'],
                                            $insertprof['friends_count'],
                                            $insertprof['listed_count'],
                                            $insertprof['favourites_count'],
                                            $insertprof['statuses_count'],
                                            $insertprof['created_at'],
                                            $insertprof['utc_offset'],
                                            $insertprof['time_zone'],
                                            $insertprof['geo_enabled'],
                                            $insertprof['lang'],
                                            $insertprof['contributors_enabled'],
                                            $insertprof['profile_background_color'],
                                            $insertprof['profile_background_image_url'],
                                            $insertprof['profile_background_image_url_https'],
                                            $insertprof['profile_background_tile'],
                                            $insertprof['profile_banner_url'],
                                            $insertprof['profile_image_url'],
                                            $insertprof['profile_image_url_https'],
                                            $insertprof['profile_link_color'],
                                            $insertprof['profile_sidebar_border_color'],
                                            $insertprof['profile_sidebar_fill_color'],
                                            $insertprof['profile_text_color'],
                                            $insertprof['profile_use_background_image'],
                                            $insertprof['default_profile'],
                                            $insertprof['default_profile_image'],
                                            $insertprof['withheld_in_countries'],
                                            $insertprof['withheld_scope'],							  
                                            $insertprof['is_translator'],
                                            $insertprof['following'],
                                            $insertprof['notifications']
                                        )) {
                                            $errors[]="E".$e++." -> Main binding parameters failed for insert tweet: (" . $insert_rtprofile->errno . ") " . $insert_rtprofile->error . "<br/>";
                                        }
                                        if (!$insert_rtprofile->execute()) {
                                            $errors[]="E".$e++." -> Main execute failed for insert tweet: (" . $insert_rtprofile->errno . ") " . $insert_rtprofile->error . "<br/>";
                                        }
                                        $insert_rtprofile -> free_result();
                                    }


                                    $insertprof = NULL;


                                }

                            }
                        }

				
            } // end else NULL
				
					
			if(count($errors) > 0) {
				print_r($errors);
				exit;
			}
                
            //////////////////////////////
            // MOVE FILE FROM DIRECTORY
            //////////////////////////////
			rename($dir.'/'.$entry, $dir2.'/'.$entry);
			
		} // end for()
             
            
	  } // end if()
    } // end while()
    closedir($handle);
} // end if()


echo "\n\nDONE\n\n";
exit;
?>