<?php
/////////////////////////////////////////////
// NAME: 			library_parseJSON_tweets.php
// VERSION: 		1.4
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x
// DESCRIPTION: 	This is simply a collection 
//                  of useful PHP functions
/////////////////////////////////////////////

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
