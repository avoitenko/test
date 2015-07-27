<?php

include_once ('config.php');
include_once ('base64.php');

error_reporting(E_ALL);

$base64 = new Base64();

//alfa - параметр зашифрованной строки
if (!isset($_REQUEST['alfa'])) {
    exit;
}

//--- получение параметров из строки
$alfa = $_REQUEST['alfa'];
$params = $base64->decode($alfa, $CRYPT_KEY, true);
parse_str($params);

//--- проверка параметров
if (!isset($login) || !isset($password) || !isset($symbol)) {
    $str_result = "error=" . ERR_WRONG_REQUEST;
    $str_result .= "&error_desc=2";
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo 'omega=' . $str_result;
    exit;
}

//--- выборка user из USERS
$query = "SELECT `id`, `exp_time` FROM `" . $DB_TABLE_USERS . "` WHERE `login`='{$login}' AND `password`='{$password}'";
$result = mysql_query($query);
$errno = mysql_errno();

//--- ошибка в запросе
if ($errno > 0) {
    $str_result = "error=" . (ERR_MYSQL_ERROR_FIRST + $errno);
    $str_result .= "&error_desc=" . mysql_error();
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo 'omega=' . $str_result;
    exit;
}

//--- если нет записей, пользователь не найден
if (($result === FALSE) || (mysql_num_rows($result) < 1)) {
    $str_result = "error=" . ERR_INVALID_LOGIN;
    $str_result .= "&error_desc=3";
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo 'omega=' . $str_result;
    exit;
}

//--- пользователь найден
$row = mysql_fetch_assoc($result);
$exp_time = strtotime($row['exp_time']); //unix
$now = time(); //unix
mysql_free_result($result);


//--- подписка не прошла проверку по времени
if ($now > $exp_time) {
    $str_result = "error=" . ERR_ACCOUNT_BLOCKED;
    $str_result .= "&error_desc=4";
    $str_result .= "&exp_time=" . $exp_time;
    $str_result .= "&end2";
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo 'omega=' . $str_result;
    exit;
}

//--- проверка инструмента и чтение тренда
$query = "SELECT `trend_dir`,`trend_time` FROM `" . $DB_TABLE_SIGNALS . "` WHERE `symbol`='{$symbol}'";
$result = mysql_query($query);
$errno = mysql_errno();

//--- ошибка в запросе
if ($errno > 0) {
    $str_result = "error=" . (ERR_MYSQL_ERROR_FIRST + $errno);
    $str_result .= "&error_desc=" . mysql_error();
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo 'omega=' . $str_result;
    exit;
}

//--- если нет записей, инструмент не найден
if (($result === FALSE) || (mysql_num_rows($result) < 1)) {
    $str_result = "error=" . ERR_INVALID_SYMBOL;
    $str_result .= "&error_desc=5";
    $str_result .= "&exp_time=" . $exp_time;
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo 'omega=' . $str_result;
    exit;
}

//--- тренд определен
$row = mysql_fetch_assoc($result);
$trend_dir = $row['trend_dir'];
$trend_time = strtotime($row['trend_time']);
mysql_free_result($result);

//--- возврат результата
$str_result = "error=" . ERR_NO_ERROR;
$str_result .= "&error_desc=0";
$str_result .= "&trend_dir=" . $trend_dir;
$str_result .= "&trend_time=" . $trend_time;
$str_result .= "&exp_time=" . $exp_time;
$str_result .= "&end=" . time();
$str_result = $base64->encode($str_result, $CRYPT_KEY, true);
echo 'omega=' . $str_result;
exit;
?>



