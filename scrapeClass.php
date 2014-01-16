<?php
class webScrapper {
	function setErrorLvl($level) {
		switch($level) {
			case 'debug':
				ini_set('error_reporting', E_ALL);
				ini_set('display_errors', 1);
				ini_set('log_errors', 0);
				break;
			case 'prod':
				ini_set('error_reporting', E_ALL ^ E_NOTICE);
				ini_set('display_errors', 0);
				ini_set('log_errors', 1);
				break;
		}
	}

    function getData($url, $start, $end, $fields='') {
        $ch = curl_init();
        $timeout = 5;
        $userAgent = 'Chrome/31.0.1650.57 Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Safari/537.36';

        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, "http://www.google.com/bot.html");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
        if(!empty($fields)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

        $data = curl_exec($ch);

        if (curl_errno($ch)> 0){
            echo 'There was a cURL error: ' . curl_error($ch)." on url: ".$url."<br />";
        } else {
            $data = @explode($start, $data);
            $data = @explode($end, $data[1]);

            return utf8_encode($data[0]);
        }
        curl_close($ch);
    }

    function getURI($url) {
        $ch = curl_init();
        $timeout = 5;
        $userAgent = 'Chrome/31.0.1650.57 Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Safari/537.36';

        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

        $data = curl_exec($ch);
        curl_close($ch);

        return utf8_encode($data);
    }

    function getBetween($content, $start, $end){
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }

    function escapeString($string){
        return mysql_real_escape_string($string);
    }
}
