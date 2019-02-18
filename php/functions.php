<?php
/////////////////////////////////////////////
// NAME: 			functions.php
// VERSION: 		1.4
// ORIG DATE: 		October 7, 2014
// AUTHOR: 		    Timothy David Bowman
// REQUIREMENTS:	PHP 5.x
// DESCRIPTION: 	This is simply a collection 
//                  of useful PHP functions
/////////////////////////////////////////////

////////////////////////////////////////////
/// RETRIEVE  THE  HTML  PAGE  FUNCTION 
/// FROM THE INTERNET VIA HTTP REQUEST 
////////////////////////////////////////////
function get_data($url) {
	$ch = curl_init();
	$timeout = 0;
	$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
	
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_COOKIEFILE, "");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);

	// for JSONP
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
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

// http://au1.php.net/manual/en/function.checkdate.php#113205
function validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
//http://stackoverflow.com/questions/2162497/efficiently-counting-the-number-of-lines-of-a-text-file-200mb
function getLines($file) {
    $f = fopen($file, 'rb');
    $lines = 0;

    while (!feof($f)) {
        $lines += substr_count(fread($f, 8192), "\n");
    }

    fclose($f);

    return $lines;
}


function add_data($content, $url, $ch, $search) {
    
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
    if ($httpcode !== 200) {
        print "Fetch error $httpcode for '$url'\n";
        return;
    }

    $responseobject = json_decode($content, true);
    if (empty($responseobject['responseData']['results'])) {
        print "No results found for '$search'\n";
        return;
    }

    print "********\n";
    print "$search:\n";
    print "********\n";

    $altmetric_id = $responseobject['altmetric_id'];
   	$score = $responseobject['score'];
	
	echo "<li>";
	echo $almtetric_id;
	echo "<br>";
	echo $score;
	echo "</li>";
}


// http://www.php.net/manual/en/function.is-writable.php#41194
function file_write($filename, &$content) {
	if (!is_writable($filename)) {
		if (!chmod($filename, 0666)) {
			 echo "Cannot change the mode of file ($filename)";
			 exit;
		};
	}
	if (!$fp = @fopen($filename, "w")) {
		echo "Cannot open file ($filename)";
		exit;
	}
	if (fwrite($fp, $content) === FALSE) {
		echo "Cannot write to file ($filename)";
		exit;
	}
	if (!fclose($fp)) {
		echo "Cannot close file ($filename)";
		exit;
	}
} 
