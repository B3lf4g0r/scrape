<?php
$link = "http://localhost/scrape/multics.html";
$username = '';
$password = '';

function get_data($url, $username, $password) {
	$ch = curl_init();
	$userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5';
	
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);  
	
	$data = curl_exec($ch);
	
	if (curl_errno($ch)> 0){
		die('There was a cURL error: ' . curl_error($ch));
	} else {
		curl_close($ch);
		return $data;
	}
}

function get_between($content, $start, $end){
	$r = explode($start, $content);
	if (isset($r[1])){
		$r = explode($end, $r[1]);
		return $r[0];
	}
	return '';
}

$data = get_data($link, $username, $password);
$start = '<table class=maintable width=100%>';
$end = '<tr class=alt3>';
$data = get_between($data, $start, $end);
$rows = preg_split('/<tr id="Row/', $data);
array_shift($rows);
foreach($rows as $row) {
	$cols = preg_split('/><td/', $row);
	$peer = preg_replace('/:/', ' ', ltrim(rtrim(trim(substr(strip_tags($cols[1]), 1)))));
	$program = ltrim(rtrim(trim(substr(strip_tags($cols[3]), 1))));
	if(!empty($program)) {
		echo 'CACHE PEER: '.$peer."<br />\n";
	}
}
