<?php
/////////////////////////////////////////////
// NAME: 			library_dataFunctions.php
// VERSION: 		1.3
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x, MySQL
// DESCRIPTION: 	This connects to MySQL db
/////////////////////////////////////////////

function objectToArray ($object) {
    if(!is_object($object) && !is_array($object))
        return $object;

    return array_map('objectToArray', (array) $object);
}

function findKey($array, $field, $value) {
   
   foreach($array as $key => $row) {
      if ($row[$field] === $value )
         return true;
   }
   return false;

}

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


function errorCheck($errorArray) {
	$count = count($errorArray);
	if ($count > 0) {
		if ($_SESSION['s_Token']) { 
			// unset temp token
			unset($_SESSION['s_Token']); 
		}
		$_SESSION['eArray'] = $errorArray;
		header("Location: ". $_SERVER['HTTP_REFERER']);
		exit();
	}
}

function cleanData($data) {
	$cleaned = trim($data);
	$cleaned = strip_tags($cleaned);
	$cleaned = htmlentities($cleaned);
	$cleaned = stripslashes($cleaned);
	return $cleaned;	
}
function checkForCharactersOnly($string) {
	$string = trim($string);
	$found = preg_match("/^[a-zA-Z]$/", $string);
	return $found;
}
function checkForSpecialCharacters($string) {
	$string = trim($string);
	$found = preg_match("\"[@#$!%^&*()=_+|-]", $string);
	return $found;
}
function validateEmail($string) {
	$string = trim($string);
	$found = preg_match("\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b", $string);
	return $found;													
}
function validatePassword($string) {
	$string = trim($string);
	$found = preg_match("[@#$!%^&*()=_+|-]", $string);
	return $found;	
}
function charCountComparison($string, $num) {
	$return = FALSE;
	
	$string = trim($string);
	$length = strlen($string);
	if($length != $num) {
		$return = TRUE;
	}
	return $return;
}
function charCountMax($string, $max) {
	$return = FALSE;
	
	$string = trim($string);
	$length = strlen($string);
	if($length > $max) {
		$return = TRUE;
	}
	return $return;
}
function charCountRange($Value, $min, $max) {
	$return = FALSE;
	$string = trim($string);
	$length = strlen($string);
	if($length > $max || $length < $min) {
		$return = TRUE;
	}
	return $return;	
}

/*
* Filter an array
* Taken from >> http://php.net/manual/en/function.array-filter.php
*/
function filter_by_value ($array, $index, $value){
	if(is_array($array) && count($array)>0) {
		foreach(array_keys($array) as $key){
			$temp[$key] = $array[$key][$index];
			
			if ($temp[$key] == $value){
				$newarray[$key] = $array[$key];
			}
		}
	}
	return $newarray;
} 
/*  recursive array search for value http://www.php.net/manual/en/function.array-search.php#87227  */
function array_search_recursive($needle, $haystack){
	foreach($haystack as $key => $val) {
		if(is_array($val)){
			$return = array_search_recursive($needle, $val);
		} else {
			if($val == $needle) {
				$return = $key;
			}	
		}
	}
	return $return;
}
/* recursive array search for key
http://www.php.net/manual/en/function.array-key-exists.php#85184 */
function &array_find_element_by_key($key, &$form) {
  if (array_key_exists($key, $form)) {
    $ret =& $form[$key];
    return true;
  }
  foreach ($form as $k => $v) {
    if (is_array($v)) {
      $ret =& array_find_element_by_key($key, $form[$k]);
      if ($ret) {
        return $ret;
      }
    }
  }
  return false;
}
