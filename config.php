<?php

//--- временная зона
date_default_timezone_set('UTC');

$CRYPT_KEY = "222f40a7068d337550b428f477e40b7a";
$SESSION_LIFETIME_SEC = 86400; // Время жизни сессии (в секундах).
//--- настройка кодировки Unicode
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

//--- настройки базы данных
$DB_LOGIN = 'root';
$DB_PASSWORD = 'root';
$DB_NAME = 'mt4ind';
/*
  $DB_LOGIN = 'avaticks_test'; //root
  $DB_PASSWORD = 'r3vcbcxa'; //root
  $DB_NAME = 'avaticks_test'; //mt4ind
 */
$DB_HOST = 'localhost';
$DB_TABLE_USERS = 'users';
$DB_TABLE_SIGNALS = 'signals';
$DEBUG = FALSE;

//+------------------------------------------------------------------+
//--- коды ошибок
define("ERR_HTTP_ERROR_FIRST", 100000); // начало списка ошибок
define("ERR_MYSQL_ERROR_FIRST", 85000); // начало списка ошибок
define("ERR_USER_ERROR_FIRST", 65536); // начало списка ошибок

define("ERR_NO_ERROR", 0);  // нет ошибки
define("ERR_UNKNOWN_ERROR", ERR_USER_ERROR_FIRST + 1); // неизвестная ошибка
define("ERR_INVALID_LOGIN", ERR_USER_ERROR_FIRST + 2);
define("ERR_ACCOUNT_BLOCKED", ERR_USER_ERROR_FIRST + 3);
define("ERR_INVALID_SYMBOL", ERR_USER_ERROR_FIRST + 4);
define("ERR_NOT_CONNECTED", ERR_USER_ERROR_FIRST + 5);
define("ERR_WRONG_REQUEST", ERR_USER_ERROR_FIRST + 6); // ошибка в запросе
//+------------------------------------------------------------------+
mysql_connect($DB_HOST, $DB_LOGIN, $DB_PASSWORD) or die(mysql_error());
mysql_select_db($DB_NAME) or die(mysql_error());

mysql_query("set character_set_client  ='utf8'");
mysql_query("set character_set_results ='utf8'");
mysql_query("set collation_connection  ='utf8_general_ci'");
?>
