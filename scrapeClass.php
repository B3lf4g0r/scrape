<?php
include('config.inc');
class webScrapper {
    function __construct() {
        $link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        if (!$link) { die('Could not connect: '. mysql_error()); }
        mysql_select_db(DB_DATABASE);
    }
	function setErrorLvl($level) {
		switch($level) {
			case 'debug':
		                @apache_setenv('no-gzip', 1);
		                @ini_set('zlib.output_compression', 0);
		                @ini_set('implicit_flush', 1);
		                for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
		                ob_implicit_flush(1);
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

    public static function getData($url, $start='', $end='', $bot = false, $fields='') {
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml, text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.5";

        $ch = curl_init();
        $timeout = 5;
        (!$bot) ? $userAgent = 'Chrome/31.0.1650.57 Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Safari/537.36' : $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
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
            webScrapper::error('There was a cURL error: ' . curl_error($ch)." on url: ".$url);
        } else {
            if(!empty($start)) {
                $data = @explode($start, $data);
                $data = @explode($end, $data[1]);

                return utf8_encode($data[0]);
            }
            return utf8_encode($data);

        }
        curl_close($ch);
    }

    public static function getBetween($content, $start, $end){
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }

    public static function cleanString($string, $tags = false, $escape = false, $spaces = false) {
        //Remove comments
        $string = preg_replace('#<!--.*?-->#', '', $string);
        //Remove unwanted break lines
        $string = preg_replace('#\v+#', '', $string);
        //Trim string of unwanted white spaces
        $string = preg_replace('#&nbsp;#', ' ', $string);
        //Remove multiple whitespaces
        $string = preg_replace("#\s\s+#", " ", $string);
        $string = trim(ltrim(rtrim($string)));
        //Clear HTML tags
        $string = (($tags)) ? strip_tags($string) : $string;
        //Clean string for MySQL insertion
        $string = (($escape)) ? mysql_real_escape_string($string) : $string;
        //Some strings may require removing all spaces
        $string = (($spaces)) ? preg_replace('#\s#', '', $string) : $string;
        return $string;
    }

    public static function error($error) {
        file_put_contents("errors.txt", '['.date('Y-m-d H:i:s').'] '.$error."\n", FILE_APPEND);
    }

    public static function updateTable($item) {
        //Create query
        foreach($item as $key=>$value) {
            $_fields[] = "`".$key."`='".$value."'";
        }
        $fields = implode( ",", $_fields);
        //Check if product exists in database
        $q = mysql_query("SELECT * FROM ".TABLE_NAME." WHERE `item_id`='".$item['item_id']."'");
        if(mysql_num_rows($q) == 0) {
            $_query = "INSERT INTO ".TABLE_NAME." SET ".$fields;
        } else {
            $_query = "UPDATE ".TABLE_NAME." SET ".$fields." WHERE `item_id`='".$item['item_id']."'";
        }
        mysql_query($_query) or webScrapper::error("Error on update: ".mysql_error(). ". Query: <b>".$_query."</b>. URL: ".$item['item_url']);
        unset($_fields);
        unset($_query);
        unset($fields);
        unset($item);
    }
}
