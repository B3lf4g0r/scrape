<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');
ini_set('auto_detect_line_endings', true);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

define('DB_HOST', ''); //MySQL server host
define('DB_PORT', ''); //MySQL server port
define('DB_USER', ''); //MySQL server username
define('DB_PASS', ''); //MySQL server password
define('DB_DATABASE', ''); //MySQL database
define('DB_TABLE', ''); //MySQL table

$host = DB_PORT ? DB_HOST.':'.DB_PORT : DB_HOST;

//MySQL connection to database
$link = mysql_connect($host, DB_USER, DB_PASS);
if (!$link) {
    die('Error: '.mysql_errno().' '.mysql_error());
}
if (!mysql_select_db(DB_DATABASE)) {
    die('Error: '.mysql_errno().' '.mysql_error());
}

//JSON link
$url = file_get_contents('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20(%22USDHKD%22,%20%22USDTHB%22,%20%22USDSGD%22,%20%22USDMYR%22,%20%22HKDTHB%22,%20%22HKDSGD%22,%20%22HKDUSD%22,%20%22HKDMYR%22)&format=json&env=store://datatables.org/alltableswithkeys&callback=');

//Transform json response to array
function getValues($url)
{
    $data = json_decode($url, true);
    $data = $data['query'];

    return (insertValues($data)) ? false : true;
}

//Insert values to DB
function insertValues($data)
{
    $query = 'INSERT INTO '.DB_TABLE.' SET ';
    foreach ($data['results']['rate'] as $result) {
        //Transform to appropiate column names. Ex: USDHKD to usd-hkd
        $first = substr($result['id'], 0, -3);
        $column = strtolower(preg_replace('#'.$first.'#', $first.'-', $result['id']));

        //Insert details into database
        $query .= '`'.$column."`='".$result['Rate']."', ";
    }
    $query .= "`entrydate`='".$data['created']."' ";

    if (mysql_query($query)) {
        echo 'Details inserted sucessfully. Query: '.$query;

        return true;
    } else {
        return false;
    }
}

while (getValues($url) != false) {
    sleep(5);
    echo 'Retrying.<br />';
    flush();
    $data = getValues($url);
}
