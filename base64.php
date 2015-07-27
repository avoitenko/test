<?php

mb_internal_encoding('UTF-8');

class Base64 {

    private $_alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    private $_PADCHAR = '=';
    private $debug = false;

    public function _key($key = '') {
        $this->_alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        //--- перемешиваем
        if ($key && strlen($key) > 0) {
            $_alpha = str_split($this->_alpha);
            $ckey = str_split($key);
            $len = count($ckey);

            for ($i = 0; $i < $len; $i++) {
                $pos1 = (ord($ckey[$i]) - 65) & 0x3f;
                $pos2 = (0x3f - $pos1);
                $shift = (ord($ckey[$i]) & 0x03);
                //--- поменять местами
                $ch1 = $_alpha[$pos1];
                $ch2 = $_alpha[$pos2];
                $_alpha[$pos1] = $ch2;
                $_alpha[$pos2] = $ch1;

                //--- сдвинуть
                $count = count($_alpha);
                for ($k = 0; $k < $shift; $k++) {
                    $ch1 = $_alpha[0];
                    for ($m = 1; $m < $count; $m++) {
                        $_alpha[$m - 1] = $_alpha[$m];
                    }
                    $_alpha[$count - 1] = $ch1;
                }
            }
            $this->_alpha = implode($_alpha);
        }
    }

    public function unichr($dec) {
        if ($dec < 128) {
            $utf = chr($dec);
        } else if ($dec < 2048) {
            $utf = chr(192 + (($dec - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        } else {
            $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
            $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        }
        return $utf;
    }

    private function _alpha_gender($key = '') {
        if (strlen($key) == 64) {
            $this->_alpha = $key;
        }
    }

    private function _getbyte64($str, $i) {
        $idx = strpos($this->_alpha, $str[$i]);
        if ($idx === -1) {
            trigger_error("Cannot decode base64", E_USER_ERROR);
        }
        return $idx;
    }

    private function _getbyte($s, $i) {
        $x = ord($s[$i]);
        if ($x > 255) {
            trigger_error("INVALID_CHARACTER_ERR: DOM Exception 5", E_USER_ERROR);
        }
        return $x;
    }

    public function encode($s = '', $key = false, $safe = false) {


        if (mb_detect_encoding($s, 'UTF-8', true) === false) {
            $s = utf8_encode($s);
        }

        $slen = mb_strlen($s) * 2;
        //--- проверка пустой строки
        if ($slen == 0)
            return '';

        //--- применеие нового ключа
        $this->_key($key);


//--- преобразование к однобайтному массиву
        $getbyte = array();
        $this->utf8_mb_array($s, $getbyte);
        $arlen = count($getbyte);
        //--- 
        $x = array();
        $imax = $slen - $slen % 3;
        $b10 = 0;

        if ($this->debug)
            echo $imax . "  " . $slen . "  " . $arlen . "<br>";

        for ($i = 0; $i < $imax; $i += 3) {
            $b10 = ($getbyte[$i] << 16 ) | ( $getbyte[$i + 1] << 8 ) | ($getbyte[$i + 2]);
            if ($this->debug)
                echo $i . " " . dechex($b10) . "<br>";

            $x[] = ( $this->_alpha[( $b10 >> 18 )] );
            $x[] = ( $this->_alpha[( ( $b10 >> 12 ) & 0x3F )] );
            $x[] = ( $this->_alpha[( ( $b10 >> 6 ) & 0x3f )] );
            $x[] = ( $this->_alpha[( $b10 & 0x3f )] );
        }

        switch ($slen - $imax) {
            case 1:
                $b10 = $getbyte[$i] << 16;
                $x[] = $this->_alpha[( $b10 >> 18)];
                $x[] = $this->_alpha[(($b10 >> 12) & 0x3F)];
                $x[] = $this->_PADCHAR;
                $x[] = $this->_PADCHAR;
                break;
            case 2:
                $b10 = ($getbyte[$i] << 16) | ($getbyte[$i + 1] << 8);
                $x[] = $this->_alpha[( $b10 >> 18)];
                $x[] = $this->_alpha[(( $b10 >> 12) & 0x3F)];
                $x[] = $this->_alpha[(( $b10 >> 6) & 0x3F)];
                $x[] = $this->_PADCHAR;
                break;
        }
        $result = implode('', $x);
        if ($safe == true) {
            $result = str_replace(array('+', '/', '='), array('-', '_', ''), $result);
        }
        return $result;
    }

    public function decode($s = '', $key = false, $safe = false) {

        //--- новый ключ
        if ($key && strlen($key) == 64) {
            $this->_alpha_gender($key);
        }

        //$s = (string) $s;
        $imax = strlen($s);

        //---
        if ($imax === 0)
            return '';

        //--- применеие нового ключа
        $this->_key($key);

        if ($safe) {
            $s = str_replace(array('-', '_'), array('+', '/'), $s);
            $mod4 = strlen($s) % 4;
            if ($mod4 > 0) {
                $s .= substr('====', $mod4);
            }
            $imax = strlen($s);
        }

        $pads = 0;
        $x = array();
        $b10 = 0;
        if ($imax % 4 !== 0) {
            trigger_error("Cannot decode base64", E_USER_ERROR);
        }
        if ($s[$imax - 1] === $this->_PADCHAR) {
            $pads = 1;
            if ($s[$imax - 2] === $this->_PADCHAR) {
                $pads = 2;
            }
            // either way, we want to ignore this last block
            $imax -= 4;
        }
        for ($i = 0; $i < $imax; $i += 4) {
            $b10 = ($this->_getbyte64($s, $i) << 18 ) | ( $this->_getbyte64($s, $i + 1) << 12 ) | ($this->_getbyte64($s, $i + 2) << 6 ) | $this->_getbyte64($s, $i + 3);
            $x[] = chr($b10 >> 16);
            $x[] = chr(( $b10 >> 8 ) & 0xff);
            $x[] = chr($b10 & 0xff);
            if ($this->debug)
                echo $i . " " . dechex($b10) . "<br>";
        }
        switch ($pads) {
            case 1:
                $b10 = ( $this->_getbyte64($s, $i) << 18 ) | ( $this->_getbyte64($s, $i + 1) << 12 ) | ( $this->_getbyte64($s, $i + 2) << 6 );
                $x[] = chr($b10 >> 16);
                $x[] = chr(($b10 >> 8) & 0xff);
                break;
            case 2:
                $b10 = ( $this->_getbyte64($s, $i) << 18) | ( $this->_getbyte64($s, $i + 1) << 12 );
                $x[] = chr($b10 >> 16);
                break;
        }
        return $this->mb_array_utf8($x);
    }

    public function ordinal($str, $o) {
        $charString = mb_substr($str, $o, 1, 'utf-8');
        $size = strlen($charString);
        $ordinal = ord($charString[0]) & (0xFF >> $size);
        //Merge other characters into the value
        for ($i = 1; $i < $size; $i++) {
            $ordinal = $ordinal << 6 | (ord($charString[$i]) & 127);
        }
        return $ordinal;
    }

    public function utf8_mb_array($text, array &$char_array) {
        $len = mb_strlen($text);
        for ($offset = 0; $offset < $len; $offset++) {
            $chr = $this->ordinal($text, $offset);
            //echo $offset . " " . dechex($chr) . "<br>";
            $char_array[] = ($chr & 0xFF);
            $char_array[] = ($chr >> 8) & 0xFF;
        }
    }

    function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function mb_array_utf8(array $char_array) {
        $out = "";
        $len = count($char_array);
        for ($offset = 0; $offset < $len; $offset+=2) {

            $chr = ord($char_array[$offset]);
            if (($offset + 1) < $len)
                $chr += ord($char_array[$offset + 1]) << 8;
            $out .= $this->unichr($chr);
        }
        return $out;
    }

}
