<?php

require_once ('config.php');
error_reporting(E_ALL);


// alfa - параметр зашифрованной строки
if (!isset($_REQUEST['alfa'])) {
    exit;
}
//--- получение параметров из строки
$alfa = $_REQUEST['alfa'];
$base64 = new Base64();
$params = $base64->decode($alfa, $CRYPT_KEY, true);
parse_str($params);

//--- обработка параметров
if (!isset($func)) {
    $str_result = "error=" . ERR_PARAMETERS;
    $str_result .= "&error_desc=2";
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo $str_result;
    exit;
}

//---
$func = intval($func);

//--- проверка логина
if ($func === 1) {

    if (!isset($login) || !isset($password)) {
        $str_result = "error=" . ERR_PARAMETERS;
        $str_result .= "&error_desc=3";
        $str_result .= "&end=" . time();
        $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
        echo $str_result;
        exit;
    }

//--- выборка user из USERS
    $query = "SELECT `id`, `session_key`, `session_time` FROM `" . $DB_TABLE_USERS . "` WHERE `login`='{$login}' AND `password`='{$password}'";
    $result = mysql_query($query);
    $errno = mysql_errno();

//--- ошибка в запросе
    if ($errno > 0) {
        $str_result = "error=" . (ERR_MYSQL_ERROR_FIRST + $errno);
        $str_result .= "&error_desc=" . mysql_error();
        $str_result .= "&end=" . time();
        $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
        echo $str_result;
        exit;
    }

//--- если нет записей, пользователь не найден
    if (($result === FALSE) || (mysql_num_rows($result) < 1)) {
        $str_result = "error=" . ERR_NO_SUCH_USER;
        $str_result .= "&error_desc=4";
        $str_result .= "&end=" . time();
        $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
        echo $str_result;
        exit;
    }

//--- пользователь найден, проверяем его KEY
    $row = mysql_fetch_assoc($result);
    $id = $row['id'];
    $session_key = $row['session_key'];
    $session_time = $row['session_time'];
    mysql_free_result($result);

//--- если KEY старый, генерируем новый
    $time_level = strtotime($session_time) + $SESSION_LIFETIME_SEC;
    $current_time = time();
//--- обновляем key и time
    if ($current_time > $time_level) {
        $session_key = GetSessionKey();
        $session_time = date("Y-m-d H:i:s");
        $query = "UPDATE `" . $DB_TABLE_USERS . "` SET `session_key`='{$session_key}', `session_time`='{$session_time}' WHERE `id`='{$id}'";
        mysql_query($query);
        $errno = mysql_errno();

        //---ошибка в запросе
        if ($errno > 0) {
            $str_result = "error=" . (ERR_MYSQL_ERROR_FIRST + $errno);
            $str_result .= "&error_desc=5";
            $str_result .= "&end=" . time();
            $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
            echo $str_result;
            exit;
        }
//--- отправляем новый key
        $str_result = "error=" . ERR_NEW_KEY;
        $str_result .= "&error_desc=" . $current_time . " " . $time_level;
        $str_result .= "session_key=" . $session_key;
        $str_result .= "&end=" . time();
        $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
        echo $str_result;
        exit;
    }

//--- положительный ответ
    $str_result = "error=" . ERR_SUCCESS;
    $str_result .= "&error_desc=0";
    $str_result .= "&end=" . time();
    $str_result = $base64->encode($str_result, $CRYPT_KEY, true);
    echo $str_result;
    exit;
}//END func=1
?>