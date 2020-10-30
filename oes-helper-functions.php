<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<?php

global $F_CORE_DEFINED;

if ($F_CORE_DEFINED == '1') {
    return;
}

$F_CORE_DEFINED = 1;

global $UNIQ_APC_KEY;
if (array_key_exists('SERVER_NAME', $_SERVER)) {
    $UNIQ_APC_KEY = $_SERVER['SERVER_NAME'];
} else {
    $UNIQ_APC_KEY = '_cli_';
}
//error_log('uniq apc key: '.$UNIQ_APC_KEY);

/**
 * @return CgiQueryParams
 */
function post()
{
    global $POST;
    if (isset($POST)) {
        return $POST;
    }
    $POST = new CgiQueryParams();
    return $POST;
}


function headerText()
{
    contentType("plain");
}

function headerHtml()
{
    contentType("html");
}

function contentType($type)
{
    header("Content-Type: text/${type}; charset=UTF-8");
}

function debug($msg)
{

    upload()->log($msg);
    return;
    static $fh;

    $DEBUG = true;

    if (!$DEBUG) {
        return false;
    }

    if (!isset($fh)) {
        $log = $_SERVER["DEBUG_LOG"];
        if (empty($log)) {
            $log = "/tmp/city.log";
        }
        $fh = fopen($log, "a");
    }

    fwrite($fh, $msg);

    fwrite($fh, "\n");


}

function html($str = "", $default = "", $delimiter = ', ')
{
    $res = ashtml($str, $default);

    if (is_array($res)) {
        echo implode($delimiter, $res);
    } else {
        echo $res;
    }

}

function htmlfirstelem($array)
{
    if (is_array($array)) {
        html(reset($array));
    } else {
        html($array);
    }

}

function ashtml($str = "", $default = '')
{
    if (empty($str) && !$str === "0") {
        return $default;
    }

    if (is_array($str)) {
        return array_map(function ($s) {
            return htmlentities($s, ENT_QUOTES, "UTF-8");
        }, $str);
    } else {
        return htmlentities($str, ENT_QUOTES, "UTF-8");
    }
}

function escapeSlashes($str)
{
    return preg_replace("#/#", "\/", $str);
}

function __f($str)
{
    echo preg_replace("@#@", "%23", $str);
}

function _f($str)
{
    return preg_replace("@#@", "%23", $str);
}

function escapeHashSign($str)
{
    return preg_replace("@#@", "%23", $str);
}

function evalUniqFileName($file)
{
    $a = $file;
    $b = $a;
    $c = 0;
    while (file_exists($b)) {
        $b = $a . "[$c]";
        $c++;
    }
    return $b;
}

function parsemagtime($str)
{

    if (empty($str)) {
        return "";
    }

    $a = explode(".", $str);

    $a[2] = substr($a[2], 2);

    return implode(".", $a);

}

/* function croptext($str, $len, &$rest = "--1")
{
    if (empty($str)) {
        return "";
    }
    $ab = substr($str, 0, $len);
    $len2 = strripos($ab, ".") + 1;
    $len3 = $len2 + 1;
    if ($rest != "--1" && strlen($str) > $len3) {
        $rest = substr($str, $len3);
    } else {
        $rest = "";
    }
    return substr($str, 0, $len2);
}  */

function readFilesInDirMatchingSuffices($dir, $suffices = null)
{

    $pattern = null;

    if (isset($suffices)) {
        if (is_array($suffices)) {
            $pattern = "@\\.(" . implode("|", $suffices) . ")$@i";
        } else {
            $pattern = "@\\.$suffices@i";
        }
    }

    $list = array();

    if ($handle = opendir($dir)) {

        while (false !== ($file = readdir($handle))) {

            $filename = $file . "";

            if (stripos(".", $filename) === 0) {
                continue;
            }

            if ($pattern) {

                if (!preg_match($pattern, $filename)) {
                    continue;
                }

            }

            $list[] = $filename;

        }

    }

    closedir($handle);

    natsort($list);


    return $list;

}


function readFilesInDir($dir, $callback = "", $abspath = false)
{
    $list = array();

    $dir = endwithslash($dir);

    if ($handle = opendir($dir)) {

        while (false !== ($file = readdir($handle))) {

            $filename = $file . "";

            if (preg_match("/^\\./", $filename)) {
                continue;
            }

            if ($callback && is_callable($callback)) {
                if (!$callback($file, $dir)) {
                    continue;
                }
            }

            if ($abspath) {
                $file = $dir . $file;
            }

            $list[] = $file;

        }
    }

    closedir($handle);

    natsort($list);

    return $list;
}

function readFilesInDirRecursively($dir, $callback, $abspath = false, $level = 1)
{
    $list = array();

    $dir = endwithslash($dir);

    if ($handle = opendir($dir)) {

        while (false !== ($file = readdir($handle))) {

            $filename = $file . "";

            if (preg_match("/^\\./", $filename)) {
                continue;
            }

            if ($abspath) {
//                $file = $dir . $file;
            }

            if (is_dir($dir.$file)) {
                readFilesInDirRecursively($dir.$file,$callback,$abspath,$level+1);
            } else if (is_file($dir.$file)) {
                call_user_func($callback, $file, $dir, $level);
            }

        }
    }

    closedir($handle);

}

function getDirectories($dir, $abspath = false)
{
    $list = array();

    $dir = endwithslash($dir);

    if ($handle = opendir($dir)) {

        while (false !== ($file = readdir($handle))) {

            $filename = $file . "";

            if (preg_match("/^\\./", $filename)) {
                continue;
            }

            $path = $dir . $file;

            if (!is_dir($path)) {
                continue;
            }

            if ($abspath) {
                $list[] = $path;
            } else {
                $list[] = $file;
            }


        }
    }

    closedir($handle);

    return $list;
}

function delete_directory($dirname, $keepbasedir = false)
{
    if (!is_dir($dirname)) {
        return false;
    }

//    echo "REMOVING $dirname";

    $dir_handle = opendir($dirname);

    if (!$dir_handle) {
        return false;
    }

    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname . "/" . $file)) {
                unlink($dirname . "/" . $file);
            } else
                delete_directory($dirname . '/' . $file);
        }
    }

    closedir($dir_handle);

    if (!$keepbasedir) {
        @rmdir($dirname);
        @unlink($dirname);
    }

    return true;

}

function remove_accent($str)
{
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
    return str_replace($a, $b, $str);
}

function postslug($str)
{
    return strtolower(preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), array('-', '-', ''), remove_accent($str)));
}

function cleandirname($str)
{
    return preg_replace("/[^\.a-zA-Z0-9 -_:;*\"']/", "", $str);
}

function xml($file)
{

    static $a = array();

    if (isset($a[$file])) {
        return $a[$file];
    }

    $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

    $a[$file] = $xml;

    return $xml;

}

function ovar($o, $k)
{
    if (!is_array($k)) {
        return $o->$k;
    }
    $p = $o;
    foreach ($k as $l) {
        $p = $p->$l;
    }
    return $p;
}

function avar($o, $k)
{
    if (!is_array($o)) {
        return "";
    }
    return $o[$k];
}

function iftrue($cond, $text, $default = "")
{
    if ($cond) {
        echo $text;
        return true;
    }

    if (!empty($default)) {
        echo $default;
    }

    return false;

}

function ifnottrue($cond, $text, $default = "")
{
    iftrue(!$cond, $text, $default);
}

function iftrue2($cond, $text, $default = "")
{
    if ($cond) {
        return $text;
    }

    if (!empty($default)) {
        return $default;
    }

}

function opposite($val, $a, $b)
{

    if (empty($val)) {
        return $a;
    }

    if ($val == $a) {
        return $b;
    }

    return $a;

}

function ifselected($cond)
{
    if ($cond) {
        echo " selected ";
    }
}

function ifselected2($p, $q)
{
    if ($p == $q) {
        echo " selected ";
    }
}

function ifselected3($p, $q)
{
    if (array_value_exists($p, $q)) {
        echo " selected ";
    }

}

function checkedkey($key, &$array)
{
    if (is_array($array)) {
        if (array_key_exists($key, $array)) {
            echo " checked ";
        }
    } else if ($key == $array) {
        echo " checked ";
    }

}

function germandate($timestamp)
{

    if (!$timestamp) {
        return;
    }
    echo date("j.m.Y", $timestamp / 1000);

}


function prdate($timestamp)
{
    if (!$timestamp) {
        return;
    }
    echo date("M j, Y", $timestamp / 1000);
}

function prdatefull($timestamp)
{
    if (!$timestamp) {
        return;
    }
    echo date("F j, Y", $timestamp / 1000);
}

function prdateMonDay($timestamp)
{
    if (!$timestamp) {
        return;
    }
    echo date("M j", $timestamp / 1000);
}

function prdateYear($timestamp)
{
    if (!$timestamp) {
        return;
    }
    echo date("Y", $timestamp / 1000);
}

function croptext($str, $len, &$rest = null, $append = "")
{
    if (empty($str)) {
        return "";
    }
    $strlen = strlen($str);
    $ab = substr($str, 0, $len);
    $len2 = strripos($ab, " ") + 1;
    $len3 = $len2 + 1;
    if ($rest != null && $strlen > $len3) {
        $rest = substr($str, $len3);
    } else {
        $rest = "";
    }
    $res = substr($str, 0, $len2);

    if (strlen($res) < $strlen) {
        $res = "$res$append";
    }

    return $res;

}

function trimtext($str, $len, $append)
{

    if (empty($str)) {
        return "";
    }

    $strlen = strlen($str);

    if ($strlen > $len) {
        $str = substr($str, 0, $len);
        $str .= $append;
    }

    return $str;

}

function str_alt($txt, $alt)
{
    if (!empty($txt)) {
        return $txt;
    }
    return $alt;
}

function formattweet($str)
{
    return preg_replace("#(http://[a-zA-Z\\.0-9\\-%+:\\#\\/]+)#", "<a target='_blank' href='$1'>$1</a>", $str);
}

function formattweetdate($date)
{

    $now = time();

    $d = strtotime($date);

    $dif = $now - $d;

    $min = intval($dif / 60);

    $hours = intval($dif / 3600);

    $days = intval($dif / (3600 * 24));

    if ($min < 60) {
        return "$dif $now $d about $min minutes ago";
    }

    if ($hours < 24) {
        return "about $hours hours ago";
    }

    if ($days < 5) {
        return "about $days days ago";
    }

    return prdate($d * 1000);
}

function htmlchars($m)
{
    return htmlspecialchars(stripslashes($m));
}

function prhtmlchars($m)
{
    echo htmlchars($m);
}

function basehref($m = "")
{

    static $a;

    if (!isset($a)) {

        if (ne($m) && $m != "/") {
            $a = preg_replace("/" . escapeSlashes($m) . "/", "", $_SERVER['SCRIPT_URI']);
        } else {
            $a = $_SERVER['SCRIPT_URI'];
        }

        //echo "A $m $a ".$_SERVER["SCRIPT_URI"];

    }

    return $a;
}

function cgi_int_param($name, $defval = "")
{
    $val = $_REQUEST[$name];
    if (!isset($val)) {
        return intval($defval);
    }
    return intval($val);
}

function loadconfig($filename = "config.xml")
{
    global $config;
    $config = simplexml_load_file($filename, 'SimpleXMLElement', LIBXML_NOCDATA);
}

function config()
{
    global $config;
    return $config;
}


function selectedIfTrue($a, $b, $c = " selected ")
{
    if ($a == $b) {
        echo $c;
    }
}

$SLUGS = array();

function setslug($key, $val)
{
    global $SLUGS;
    $SLUGS[$key] = $val;
}

function delslug($key)
{
    global $SLUGS;
    unset($SLUGS[$key]);
}

function hasslug($key)
{
    global $SLUGS;
    return array_key_exists($key, $SLUGS);
}

function isslugnotempety($key)
{
    global $SLUGS;
    return array_key_exists($key, $SLUGS) && !empty($SLUGS[$key]);
}

function slug($s, $suffix = "/", $default = "")
{
    global $SLUGS;
    if (array_key_exists($s, $SLUGS)) {
        return $SLUGS[$s] . $suffix;
    }
    return $default;
}

function prslug($s, $suffix = "/", $default = "")
{
    echo slug($s, $suffix, $default);
}


function slugval($s, $default = "")
{
    global $SLUGS;
    if (array_key_exists($s, $SLUGS)) {
        return $SLUGS[$s];
    }
    return $default;
}


function slugwdefault($key, $default = "")
{
    global $SLUGS;
    if (!array_key_exists($key, $SLUGS)) {
        return $default;
    }
    return $SLUGS[$key];
}


if (false) {
    class Language
    {
        private static $language = null;

        public static function get()
        {
            new Language;
            return self::$language;
        }

        public static function getBestMatch($langs = array())
        {
            foreach ($langs as $n => $v)
                $langs[$n] = strtolower($v);
            $r = array();
            foreach (self::get() as $l => $v) {
                ($s = strtok($l, '-')) != $l && $r[$s] = 0;
                if (in_array($l, $langs))
                    return $l;
            }
            foreach ($r as $l => $v)
                if (in_array($l, $langs))
                    return $l;
            return null;
        }

        private function __construct()
        {
            if (self::$language !== null)
                return;
            if (($list = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
                if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
                    self::$language = array_combine($list[1], $list[2]);
                    foreach (self::$language as $n => $v)
                        self::$language[$n] = +$v ? +$v : 1;
                    arsort(self::$language);
                }
            } else
                self::$language = array();
        }
    }
}

function __filesort_asc($f, $g, $dir)
{

    $sf = stat($f);

    $sg = stat($g);

    if ($sf[8] < $sg[8]) {
        return -1;
    } else if ($sf[8] == $sg[8]) {
        return 0;
    }

    return 1;

}


function sortfiles_atime(&$files, $dir = "", $desc = true)
{

    uasort($files, function ($f, $g) use ($dir) {

        $sf = stat($dir . $f);

        $sg = stat($dir . $g);

        if ($sf[8] < $sg[8]) {
            return 1;
        } else if ($sf[8] == $sg[8]) {
            return 0;
        }

        return -1;

    });

}

function array_get($k, $array)
{
    return $array->$k;
}

function array_value_exists($k, $array)
{
    if (!isset($array)) {
        return false;
    }
    foreach ($array as $v) {
        if ($k == $v) {
            return true;
        }
    }
    return false;
}


function config_default($xpath)
{
    return config_text("default$xpath");
}

function config_text($xpath, $lang = "")
{
    $str = config()->xpath($xpath);
    return $str[0] . "";
}

function config_xpath_exists($xpath)
{
    $a = config()->xpath($xpath);
    return !empty($a);
}


function config_xpath($xpath)
{
    return config()->xpath($xpath);
}

function page_config($xpath, $page = "", $lang = "")
{

    if (empty($page)) {
        $page = slug("section");
    }

    $page = "handlers/$page";

    if (ne($lang)) {

        $val = config_text($page . "/" . $lang . "/" . $xpath);

        if (ne($val)) {
            return $val;
        }

        return "$lang@NOT";

    }

    $val = config_text($page . "/" . LANG . "/" . $xpath);

    if (ne($val)) {
        return $val;
    }

    $val = config_text($page . "/" . $xpath);

    if (ne($val)) {
        return $val;
    }

    $val = config_text("default/" . LANG . "/" . $xpath);

    if (ne($val)) {
        return $val;
    }

    $val = config_text("default/$xpath");

    if (ne($val)) {
        return $val;
    }

    return "@NOT";

}

function image($url)
{
    if (empty($url)) {
        return "";
    }
    return "img/items/$url";
}

function ne($p)
{
    return !empty($p);
}

function genRandomString($length = 10)
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $string = '';
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(1, strlen($characters)) - 1];
    }
    return $string;
}

function getCartID()
{
    return date("d-m-Y-") . genRandomString(6);
}

function startswith($str, $needle)
{
    if (!is_string($needle)) {
        throw new Exception();
    }
    return stripos($str, $needle) === 0;
}

/**
 * @param $str
 * @param $needle
 * @return bool
 */
function endswith($str, $needle)
{
    $pos = strripos($str, $needle);
    if ($pos === false) {
        return false;
    }
    $len1 = strlen($needle);
    $len2 = strlen($str);

    return $pos + $len1 === $len2;
}


//function selected($selected, $current = true, $echo = true)
//{
//    return __checked_selected_helper($selected, $current, $echo, 'selected');
//}
//
//function __checked_selected_helper($helper, $current, $echo, $type)
//{
//    if ((string)$helper === (string)$current)
//        $result = " $type='$type'";
//    else
//        $result = '';
//
//    if ($echo)
//        echo $result;
//
//    return $result;
//}

function hasparam($name)
{
    $v = rparam($name);
    return !empty($v);
}

function paramHasValue($param, $value)
{
    $v = rparam($param, array());
    return array_search($value, $v) !== false;
}

function rparam($name, $default = "")
{

    global $wp;

    if (isset($wp)) {
        $wp_query_vars = $wp->query_vars;
        if ($wp_query_vars && array_key_exists($name, $wp_query_vars)) {
            $value = $wp_query_vars[$name];
            if (empty($value)) {
                return $default;
            } else {
                return $value;
            }
        }
    }

    if (!isset($_REQUEST[$name])) {
        return $default;
    }

    return $_REQUEST[$name];

}

function rparam_as_array($name, $default = array())
{
    $a = $_REQUEST[$name];
    if (empty($a)) {
        return x_as_array($default);
    }
    return x_as_array($a);
}

function __html($str)
{
    if (empty($str) && !$str === "0") {
        return;
    }

    echo htmlentities($str, ENT_COMPAT, "UTF-8");
}

function get_($str)
{
    return htmlentities($str, ENT_COMPAT, "UTF-8");
}

//function _($str) {
//    return htmlentities($str, ENT_COMPAT, "UTF-8");
//}

function escapespace($str)
{
    return preg_replace("@ @", "\\ ", $str);
}

function __v($str)
{
    __escquote($str);
}

function __escquote($str)
{
    echo preg_replace("@([\"'])@", "\\\\$1", $str);
}

function __escdoublequote($str)
{
    echo preg_replace("@([\"])@", "\\\\$1", $str);
}

function __escsinglequote($str)
{
    echo preg_replace("@(['])@", "\\\\$1", $str);
}

function _v($str)
{
    return htmlspecialchars($str);
}

function _u($str)
{
    return rawurlencode($str);
}

function __u($str)
{
    echo rawurlencode($str);
}


function array_first(&$array)
{
    $keys = array_keys($array);
    return $array[$keys[0]];
}

/**
 * @param $array
 * @param string $delim
 * @param callable $func
 * @return string
 */
function concat(&$array, $delim = ", ", $func = null)
{
    $notfirst = false;
    $str = "";
    foreach ($array as &$a) {
        if ($func != null) {
            $s = $func($a);
        } else {
            $s = $a . "";
        }
        if ($notfirst) {
            $str .= $delim . $s;
        } else {
            $str .= $s;
            $notfirst = true;
        }
    }
    return $str;
}

function concatproperties(&$array, $property, $delim)
{

    $first = true;
    $names = "";
    foreach ($array as $a) {
        if (!$first) {
            $names .= $delim;
        }
        $first = false;
        $names .= $a->$property;
    }
    return $names;
}

/**
 * @param $array
 * @param $delim
 * @return string
 */
function concatdoublearray(&$array, $delim)
{

    $first = true;

    $names = "";

    if (!$array) {
        return "";
    }

    foreach ($array as $a) {

        foreach ($a as $b) {

            if (!$first) {
                $names .= $delim;
            }

            $first = false;

            $names .= $b;

        }

    }

    return $names;
}

function concatattributes(&$array, $property, $delim)
{
    $first = true;
    $names = "";
    foreach ($array as $a) {
        if (!$first) {
            $names .= $delim;
        }
        $first = false;
        $names .= $a[$property];
    }
    return $names;
}

global $newscategories;

$newscategories = array(

    "general.news" => "General News",
    "general.interview" => "Interview",
    "general.audio" => "Audio",
    "general.free-mp3" => "Free MP3",
    "general.feature" => "Feature",
    "general.release" => "Release",
    "general.video" => "Video",
    "general.tour" => "Tour",
    "general.press" => "Press",
    "aritst.press" => "Artist Press",
    "artist.quote" => "Artist Quote",
    "artist.quote.featured" => "Artist Quote (Featured)",
    "release.quote" => "Release Quote",
    "label.video.release" => "Label Video Release",
    "label.press.release" => "Label Press Release",
    "newsletter.message" => "Newsletter Item",

);

function array_value_with_default(array $arr, $key, $default)
{
    if (array_key_exists($key, $arr)) {
        return $arr[$key];
    } else {
        return $default;
    }
}

function array_has_value(array $arr, $key)
{
    return array_search($key, $arr) !== false;
}

/**
 * @param $str
 * @param string $alt
 * @return string
 */
function copyval($str, $alt = "")
{
    if (!isset($str) || empty($str)) {
        return $alt;
    } else {
        return $str;
    }
}

function prependstr($prefix, $str)
{
    if (!isset($str) || empty($str)) {
        return "";
    } else {
        return $prefix . $str;
    }
}

function x_prepend_array($array, $prefix)
{

    if (empty($array)) {
        return [];
    }

    if (!is_array($array)) {
        $array = [$array];
    }

    $res = [];

    foreach ($array as $str) {
        $res[] = $prefix . $str;
    }

    return $res;

}

/**
 * @param $t
 * @return string
 */
function format_runtime($t)
{

    $min = intval($t / 60);

    $sec = $t % 60;

    if ($sec < 10) {
        $sec = "0" . $sec;
    }

    return $min . ":" . $sec;

}

function equals($a, $b)
{

    $isaa = is_array($a);
    $isab = is_array($b);
    $isoa = is_object($a);
    $isob = is_object($b);

    if (!$isaa && !$isab) {
        return $a == $b;
    }

    if ($isaa && $isab) {
        foreach ($a as $aa) {
            foreach ($b as $bb) {
                if ($aa == $bb) {
                    return true;
                }
            }
        }
        return false;
    }

    if ($isaa) {
        foreach ($a as $aa) {
            if ($aa == $b) {
                return true;
            }
        }
        return false;
    }

    foreach ($b as $bb) {
        if ($bb == $a) {
            return true;
        }
    }

    return false;
}

function __checked($bool)
{
    if ($bool) {
        echo " checked='1' ";
    }
}

function _checked($bool)
{

    if ($bool) {
        return "checked";
    }

    return "";

}

/**
 * @param $list
 * @param string $curvalue
 * @param string $defaultlabel
 * @param string $defaultvalue
 */
function __options($list, $curvalue = "", $defaultlabel = "", $defaultvalue = "", $labelCallback = null)
{

    if (ne($defaultlabel)) {
        $defaultvalue = _v($defaultvalue);
        $selected = (equals($curvalue, $defaultvalue) ? "selected" : "");
        $hasBeenSelected = true;
        echo <<<EOD
           <option $selected value="$defaultvalue">$defaultlabel</option>
EOD;

    }

    foreach ($list as $val => $label) {

        $selected = false;

        if (is_array($curvalue)) {
            /**
             * @var array $curvalue
             */
            foreach ($curvalue as $v) {
                if ($val == $v) {
                    $selected = true;
                    break;
                }
            }
        } else {
            $selected = $val == $curvalue;
        }

        if (!$hasBeenSelected) {
            $selected = ($selected ? "selected" : "");
        }


        if ($labelCallback) {
            $label = call_user_func($labelCallback,$label);
        }

        ?>
        <option <?php echo $selected; ?> value="<?php __v((string)$val); ?>"><?php html((string)$label); ?></option>
        <?php

    }

}

/**
 * @param $list
 * @param string $curvalue
 * @param string $defaultlabel
 * @param string $defaultvalue
 */
function _options($list, $curvalue = "", $defaultlabel = "", $defaultvalue = "")
{
    $str = "";

    if (ne($defaultlabel)) {
        $defaultvalue = get_($defaultvalue);
        $defaultlabel = get_($defaultlabel);
        $selected = (equals($curvalue, $defaultvalue) ? "selected" : "");
        $str .= <<<EOD
           <option $selected value="$defaultvalue">$defaultlabel</option>
EOD;

    }

    foreach ($list as $val => $label) {

        $selected = false;

        if (is_array($curvalue)) {
            /**
             * @var array $curvalue
             */
            foreach ($curvalue as $v) {
                if ($val == $v) {
                    $selected = true;
                    break;
                }
            }
        } else {
            $selected = $val == $curvalue;
        }

        $selected = ($selected ? "selected" : "");

        $str .= "<option $selected value=\"" . get_($val) . "\">" . get_($label) . "</option>";


    }

    return $str;

}

function toarray($in)
{

    if (!isset($in)) {
        return array();
    }


    if (!is_array($in)) {

        if (empty($in)) {
            return array();
        }

        return array($in);

    }

    return $in;

}

function __jsonencode($r)
{
    echo json_encode($r);
}

function xml_findbyattribute($nodes, $key, $val)
{

    foreach ($nodes as $n) {
        $attribs = $n->attributes();
        if (empty($attribs)) {
            continue;
        }
        if (equals($attribs->$key, $val)) {
            return $n;
        }
    }

    throw new Exception("not found [$key] => [$val]");

}

function dircopy($source, $dest, $diffDir = null)
{

    $sourceHandle = opendir($source);

    if ($diffDir == null) {
        $diffDir = "";
    }

    @mkdir($dest . '/' . $diffDir);

    while ($res = readdir($sourceHandle)) {

        if ($res == '.' || $res == '..')
            continue;

        if (is_dir($source . '/' . $res)) {
            dircopy($source . '/' . $res, $dest, $diffDir . '/' . $res);
        } else {
            copy($source . '/' . $res, $dest . '/' . $diffDir . '/' . $res);
        }
    }

    closedir($sourceHandle);

}

function endwithslash($str)
{
    if (!preg_match("@/$@", $str)) {
        return $str . "/";
    }
    return $str;

}

function endNotWithSlash($str)
{
    return preg_replace('@/+$@', '', $str);
}

class FtpExpress
{

    public $pwd;

    public $conn;
    public $dircallback;
    public $filecallback;
    public $dest;
    public $isdirdest;
    public $urlparts;
    public $destpath;
    public $token;
    public $connected;

    function __construct($dest, $dircallback = null, $filecallback = null)
    {
        $this->dest = $dest;

        $this->token = genRandomString(4);

        $this->dircallback = $dircallback;

        $this->filecallback = $filecallback;

        $this->urlparts = parse_url($this->dest);

        $this->destpath = $this->urlparts['path'];

        $this->isdirdest = false;

        if (endswithslash($this->destpath)) {
            $this->isdirdest = true;
        }


    }

    public function connect()
    {

        if ($this->connected) {
            return true;
        }

        $hostname = $this->urlparts["host"];

        $this->conn = ftp_connect($hostname);

        try {

            $ftp_user_name = "ftp";
            $ftp_user_pass = "john@doe.com";

            if ($this->urlparts["user"] != "") {
                $ftp_user_name = copyval($this->urlparts["user"]);
                $ftp_user_pass = copyval($this->urlparts["pass"]);
            }

            $login = ftp_login($this->conn, $ftp_user_name, $ftp_user_pass);


            if (!$this->conn || !$login) {
                throw new Exception('Connection attempt failed! ' . $this->dest);
            }

            $this->chdir($this->destpath);

            $this->connected = true;

            $this->debug("connected $hostname");

            return false;

        } catch (Exception $e) {
            echo $e;
            throw $e;
        }

    }

    function chdir($pwd, $docreate = true)
    {

        $this->debug("chdir.CHDIR $pwd");

        if (!@ftp_chdir($this->conn, $pwd)) {

            $this->debug("chdir.CHDIR failed $pwd");

            foreach (preg_split("@/+@", startwithoutslash($pwd)) as $p) {

                if (empty($p)) {
                    continue;
                }

                if (@ftp_chdir($this->conn, $p)) {
                    $this->debug("chdir.success $pwd [$p]");
                    continue;
                }

                if (!$docreate) {
                    throw new Exception("couldn't chdir to $pwd ($p)");
                }


                $this->debug("chdir.MKDIR $pwd [$p]");

                if (!@ftp_mkdir($this->conn, $p)) {
                    $this->debug("ftp_mkdir $pwd $p failed");
                    throw new Exception("ftp_mkdir $pwd ($p) failed");
                }


                try {
                    @ftp_chmod($this->conn, 0770, $p);
                } catch (Exception $e) {

                }


                $this->debug("chdir.CHDIR.after.MKDIR $p");

                if (!@ftp_chdir($this->conn, $p)) {
                    $this->debug("couldn't chdir to $pwd $p");
                    throw new Exception("couldn't chdir to $pwd ($p)");
                }

            }

        }

    }

    public function debug($msg)
    {
        debug($this->token . ":: " . $msg);
    }

    /**
     *
     */
    public function close()
    {

        if (!$this->connected) {
            return;
        }

        if (!$this->conn) {
            return;
        }

        ftp_close($this->conn);

        $this->connected = false;
    }

    /**
     * @param $source
     * @param string $destfilename
     * @throws Exception
     */
    public function copy($source, $destfilename = "", $cwd = null)
    {

        $isdirsource = false;

        if (is_dir($source)) {
            $isdirsource = true;
        }

        if ($isdirsource && !$this->isdirdest) {
            throw new Exception("copy of directory to file failed - $source / $this->dest");
        }

//        $closeonfinish = !$this->connect();


        try {

            if (!$isdirsource) {

                if (empty($destfilename)) {
                    $destfilename = basename($source);
                }

                $this->debug("copy $source $destfilename");

                if (!@ftp_put($this->conn, $destfilename, $source, FTP_BINARY)) {
                    $this->debug("copy failed $source $destfilename");
                    throw new Exception("ftp_put $source $destfilename failed");
                }

//                @ftp_chmod($this->conn, 0770, $destfilename);

                try {
                    @ftp_chmod($this->conn, 0770, $destfilename);
                } catch (Exception $e) {

                }

            } else {

                $this->traverse($source, "/", $cwd);

            }

        } catch (Exception $e) {

            throw $e;

        }


    }

    /**
     * @param $source
     * @param $path
     * @param null $cwd
     * @return mixed
     * @throws Exception
     */
    function traverse($source, $path, $cwd = null)
    {


        if ($this->dircallback) {
            $a = $this->dircallback;
            /**
             * @var Closure $a
             */
            if (!$a($cwd, $path)) {
                $this->debug("DIR blocked $cwd");
                return;
            }
        }

        $this->debug("traverse $source$path");

        if ($cwd != null) {
            $this->debug("CHDIR $cwd");
            if (!@ftp_chdir($this->conn, $cwd)) {
                $this->debug("MKDIR $cwd");

                if (!@ftp_mkdir($this->conn, $cwd)) {
                    throw new Exception("ftp_mkdir failed $cwd");
                }


                try {
                    @ftp_chmod($this->conn, 0770, $cwd);
                } catch (Exception $e) {
                }

                $this->debug("CHDIR $cwd");

                if (!@ftp_chdir($this->conn, $cwd)) {
                    throw new Exception("ftp_chdir failed after ftp_mkdir $cwd");
                }
            }
        }

        $sourceHandle = opendir($source . $path);

        try {

            while (true) {

                $res = @readdir($sourceHandle);

//                debug("RES $res");

                if (!$res) {
//                    debug("BREAK");
                    break;
                }

                if ($res == '.' || $res == '..')
                    continue;

//                debug("RES $source | $path | $res | \n");

                if (is_dir($source . $path . $res)) {

                    $this->traverse($source, $path . $res . "/", $res);

                } else if (is_file($source . $path . $res)) {

//                    debug("PUT $source/$res to $cwd/$res");

                    $mdtm = ftp_mdtm($this->conn, $res);

                    $stat = stat("$source$path$res");

                    $size = $stat[7];

//                    debug("MDTM $res $mdtm " . $stat[9]);

                    $haschanged = true;

                    if ($mdtm > 0 && $stat[9] < $mdtm) {
                        $remotesize = ftp_size($this->conn, $res);
//                        debug("SIZE $remotesize / $size");
                        if ($size != $remotesize) {
                            $haschanged = true;
                        } else {
                            $haschanged = false;
                        }
                    }

                    if ($haschanged) {

                        if (!@ftp_pasv($this->conn, true)) {
                            $this->debug("ftp_pasv failed $cwd");
                            throw new Exception("ftp_pasv failed $cwd");
                        }

                        $ts = microtime(true);

                        if (!@ftp_put($this->conn, $res, $source . $path . $res, FTP_BINARY)) {
                            $this->debug("ftp_put $source/$res -> $cwd/$res failed");
                            throw new Exception("ftp_put $source/$res -> $cwd/$res failed");
                        }


                        try {
                            @ftp_chmod($this->conn, 0770, $res);
                        } catch (Exception $e) {

                        }


                        $ts = microtime(true) - $ts;

                        $this->debug("transfer took $ts ($size) " . $size / $ts);

                    }

                }

            }

            $this->debug("CDUP $path");

            //            echo "CDUP\n";

            if (!@ftp_cdup($this->conn)) {
                throw new Exception("ftp_cdup failed ($cwd)");
            }

        } catch (Exception $e) {
            closedir($sourceHandle);
            throw $e;
        }

    }

    public function cdup()
    {
        if (!@ftp_cdup($this->conn)) {
            throw new Exception("ftp_cdup failed.");
        }
    }

}

function endwithoutslash($str)
{
    return preg_replace("@/+$@", "", $str);
}

function startwithoutslash($str)
{
    return preg_replace("@^/+@", "", $str);
}

function startwithslash($str)
{
    if (!preg_match("@^/@", $str)) {
        return "/$str";
    }
    return $str;
}

function _absolutepath($str)
{
    return startwithslash(endwithslash($str));
}

function ftp_put_contents($fpc_path_and_name, $fpc_content)
{


    //Temporary folder in the server
    $cfg_temp_folder = "/tmp/";

    //Link to FTP
    $cfg_ftp_server = "ftp://ftp.com";

    //FTP username
    $cfg_user = "user";

    //FTP password
    $cfg_pass = "password";

    //Document Root of FTP
    $cfg_document_root = "DOCUMENT ROOT OF FTP";

    //Link to the website
    $cfg_site_link = "Link to the website";

    //Check if conteins slash on the path of the file
    $cotains_slash = strstr($fpc_path_and_name, "/");

    //Get filename and paths
    if ($cotains_slash) {
        $fpc_path_and_name_array = explode("/", $fpc_path_and_name);
        $fpc_file_name = end($fpc_path_and_name_array);
    } else {
        $fpc_file_name = $fpc_path_and_name;
    }

    //Create local temp dir
    if (!file_exists($cfg_temp_folder)) {
        if (!mkdir($cfg_temp_folder, 0777)) {
            echo "Unable to generate a temporary folder on the local server - $cfg_temp_folder.<br />";
            die();
        }
    }

    //Create local file in temp dir
    if (!file_put_contents(str_replace("//", "/", $cfg_temp_folder . $fpc_file_name), $fpc_content)) {
        echo "Unable to generate the file in the temporary location - " . str_replace("//", "/", $cfg_temp_folder . $fpc_file_name) . ".<br />";
        die();
    }

    //Connection to the FTP Server
    $fpc_ftp_conn = ftp_connect("$cfg_ftp_server");

    //Check connection
    if (!$fpc_ftp_conn) {
        echo "Could not connect to server <b>$cfg_ftp_server</b>.<br />";
        die();
    } else {

        // login
        // check username and password
        if (!ftp_login($fpc_ftp_conn, "$cfg_user", "$cfg_pass")) {
            echo "User or password.<br />";
            die();
        } else {

            //Document Root
            if (!ftp_chdir($fpc_ftp_conn, $cfg_document_root)) {
                echo "Error to set Document Root.<br />";
                die();
            }


            //Check if there are folders to create
            if ($cotains_slash) {

                //Check if have folders and is not just the file name
                if (count($fpc_path_and_name_array) > 1) {

                    //Remove last array
                    $fpc_remove_last_array = array_pop($fpc_path_and_name_array);

                    //Checks if there slashs on the path
                    if (substr($fpc_path_and_name, 0, 1) == "/") {
                        $fpc_remove_first_array = array_shift($fpc_path_and_name_array);
                    }

                    //Create each folder on ftp
                    foreach ($fpc_path_and_name_array as $fpc_ftp_path) {
                        if (!@ftp_chdir($fpc_ftp_conn, $fpc_ftp_path)) {
                            if (!ftp_mkdir($fpc_ftp_conn, $fpc_ftp_path)) {
                                echo "Error creating directory $fpc_ftp_path.<br />";
                            } else {
                                if (!ftp_chdir($fpc_ftp_conn, $fpc_ftp_path)) {
                                    echo "Error go to the directory $fpc_ftp_path.<br />";
                                }
                            }
                        }
                    }
                } else {

                }
            }

            //Check upload file
            if (!ftp_put($fpc_ftp_conn, $fpc_file_name, str_replace("//", "/", $cfg_temp_folder . $fpc_file_name), FTP_ASCII)) {
                echo "File upload <b>$fpc_path_and_name</b> failed!<br />";
                die();
            } else {
                if (!unlink(str_replace("//", "/", $cfg_temp_folder . $fpc_file_name))) {
                    echo "Error deleting temporary file.<br />";
                    die();
                } else {
                    echo "File upload <a href='$cfg_site_link" . str_replace("//", "/", "/$fpc_path_and_name") . "'><b>$cfg_site_link" . str_replace("//", "/", "/$fpc_path_and_name") . "</a></b> successfully performed.<br />";
                }
            }

            //Close connection to FTP server
            ftp_close($fpc_ftp_conn);
        }
    }
}

function endswithslash($str)
{
    return preg_match("@/$@", $str);
}

function startswithslash($str)
{
    return preg_match("@^/@", $str);
}

function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

function safe_serialize($o)
{
    return base64_encode(serialize($o));
}

function safe_serialize_and_save($o, $file)
{
    return file_put_contents($file, base64_encode(serialize($o)));
}

function load_and_safe_unserialize($file)
{
    $str = @file_get_contents($file);
    if (!$str) {
        return false;
    }
    return unserialize(base64_decode($str));
}

function safe_unserialize($str)
{
    return unserialize(base64_decode($str));
}

function setcururi($val)
{
    global $_cururi;
    $_cururi = $val;
}

function get_cururi()
{
    global $_cururi;
    return $_cururi;
}

function setcurlang($val)
{
    global $_curlang;
    $_curlang = $val;
}

function getcurlang()
{
    global $_curlang;
    return $_curlang;
}

function setlink($key, $val)
{
    global $_text;
    $_link[$key] = $val;
}

function _uri()
{
    global $_cururi;
    echo $_cururi;
}

function _o($key)
{
    global $_text;
    $a = $_text[$key];
    if (empty($a)) {
        echo "";
        return;
    }
    echo htmlentities($a);
}

function setbaseuri($uri)
{
    global $_baseuri;
    $_baseuri = $uri;
}


function _l($key = null, $lang = null)
{
    echo get_l($key, $lang);
}

function get_l($key = null, $lang = null)
{

    global $_links;
    global $_rlinks;
    global $_baseuri;
    global $_cururi;
    global $_curlang;

    if ($lang == null) {
        $lang = $_curlang;
    }

    if ($key == null) {
        $key = $_rlinks[$_curlang][$_cururi];
    }

    $a = $_links[$lang][$key];

    return "$_baseuri/$lang/$a.html";

}

function get_lwolang($key = null, $lang = null)
{

    global $_links;
    global $_rlinks;
    global $_baseuri;
    global $_cururi;
    global $_curlang;

    if ($lang == null) {
        $lang = $_curlang;
    }

    if ($key == null) {
        $key = $_rlinks[$_curlang][$_cururi];
    }

    $a = $_links[$lang][$key];

    return "$_baseuri/$a.html";

}

function __o($key)
{
    global $_text;
    $a = $_text[$key];
    if (empty($a)) {
        return "";
    }
    return htmlentities($a);
}

function _ifurimatchesecho($uri, $str)
{
    global $_links;
    global $_cururi;
    if ($_cururi == $_links["de"][$uri]) {
        echo $str;
        return;
    }
    if ($_cururi == $_links["en"][$uri]) {
        echo $str;
        return;
    }
    if ($_cururi == $_links["ru"][$uri]) {
        echo $str;
        return;
    }


}

function _ifuristartswithecho($uri, $str)
{

    global $_links;

    //    global $_cururi;

    $ruri = get_ruri();

    //echo $_links["de"][$_cururi], " / ", $_cururi, " /  ", $uri, " - ", stripos($_links["de"][$_cururi], $uri);

    if (stripos($_links["de"][$ruri], $uri) === 0) {
        echo $str;
        return;
    }
    if (stripos($_links["en"][$ruri], $uri) === 0) {
        echo $str;
        return;
    }
    if (stripos($_links["ru"][$ruri], $uri) === 0) {
        echo $str;
        return;
    }
}

function _h($src)
{
    global $_hashes;
    global $_curlang;
    echo $_hashes[$_curlang][$src];
}

function _ruri($src = null)
{

    global $_rlinks;
    global $_curlang;
    global $_cururi;

    if ($src == null) {
        $src = $_cururi;
    }

    echo $_rlinks[$_curlang][$src];

}

function get_ruri($src = null)
{

    global $_rlinks;
    global $_curlang;
    global $_cururi;

    if ($src == null) {
        $src = $_cururi;
    }

    return $_rlinks[$_curlang][$src];

}

function includecontent($page)
{
    global $_curlang;
    include($_curlang . "/" . $_curlang . "-" . $page);
}

function _title($uri = null)
{
    global $_title;
    global $_curlang;
    global $_cururi;
    if ($uri == null) {
        $uri = get_ruri();
    }
    echo $_title[$_curlang][$uri];
}


function ifurimatches($uri)
{
    global $_links;
    global $_cururi;
    if ($_cururi == $_links["de"][$uri]) {
        return true;
    }
    if ($_cururi == $_links["en"][$uri]) {
        return true;
    }
    if ($_cururi == $_links["ru"][$uri]) {
        return true;
    }

    return false;

}

function ifuristartswith($uri)
{

    global $_links;

    $ruri = get_ruri();

    if (stripos($_links["de"][$ruri], $uri) === 0) {
        return true;
    }
    if (stripos($_links["en"][$ruri], $uri) === 0) {
        return true;
    }
    if (stripos($_links["ru"][$ruri], $uri) === 0) {
        return true;
    }

    return false;
}

function _m($num)
{

    return money_format('%!.2i', $num);

    $netto = round($num, 2);

    $decnetto = floor($num);

    $fraction = (string)($num - $decnetto) * 100;

    if (strlen($fraction) == 1) {
        $fraction = "${fraction}0";
    }

    $str = $decnetto . "," . $fraction;

    return $str;

}

function __m($num)
{
    echo _m($num);
}

function __euro($num)
{
    echo _m($num);
    echo "&nbsp;&euro;";
}

define("MINUTES_1", 60);
define("MINUTES_5", 5 * 60);
define("MINUTES_30", 30 * 60);
define("MINUTES_60", 60 * 60);

function redirect($url, $replace = false)
{
    header("Location: $url");
    exit;
}

function _mkdir($dir, $mode = null, $recursive = null)
{

    if (is_dir($dir)) {
        return;
    }

    if (!mkdir($dir, $mode, $recursive)) {
        throw new Exception("mkdir failed [$dir]");
    }
}

function _symlink($target, $link, $removeprevious = false)
{

    if (file_exists($link)) {
        if ($removeprevious) {
            @unlink($link);
        } else {
            return false;
        }
    }

    if (!symlink($target, $link)) {
        throw new Exception("symlink $target $link failed");
    }

    return true;

}

function _filecopy($src, $dst, $nooverwrite = true)
{

    if (file_exists($dst) && $nooverwrite) {

        $pathinfo = pathinfo($dst);

        $dirname = $pathinfo["dirname"];
        $basename = $pathinfo["basename"];
        $extension = $pathinfo["extension"];
        $filename = $pathinfo["filename"];

        $pos = 1;

        while (true) {
            $compfilename = "$filename($pos).$extension";
            if (!file_exists($dirname . "/" . $compfilename)) {
                break;
            }
            $pos++;
        }

        $dst = $dirname . "/" . $compfilename;

    }

    error_log("copying files ($src) to ($dst)");

    copy($src, $dst);

}

function ctime($file)
{

    $s = stat($file);

    return $s[10];

}

function atime($file)
{

    $s = stat($file);

    return $s[8];

}

function mtime($file)
{

    $s = stat($file);

    return $s[9];

}


function load_json($file, $assoc = false)
{
    $file = @file_get_contents($file);
    if (!$file) {
        throw new Exception("file not found [$file]");
    }
    return json_decode($file, $assoc);
}

function save_json($file, $data)
{
    $json = json_encode($data);
    return @file_put_contents($file, $json);
}

function assert_is_dir($dir)
{
    if (!is_dir($dir)) {
        throw new Exception("is not dir [$dir]");
    }
}

function assert_is_file($file)
{

    if (!file_exists($file)) {
        throw new Exception("not exists [$file]");
    }

    if (is_dir($file)) {
        throw new Exception("is dir [$file]");
    }

}

/**
 * @param $dir
 * @return ClassAutoloader
 */
function initclassloader($dir)
{

    static $autoloader;

    if (isset($autoloader)) {
        return $autoloader;
    }

    $autoloader = new ClassAutoloader($dir);

    return $autoloader;

}

/**
 *
 */
class ClassAutoloader
{

    var $dir;

    /**
     * @param $dir
     */
    public function __construct($dir)
    {
        spl_autoload_register(array($this, 'loader'));
        $this->dir = endwithslash($dir);
    }

    /**
     * @param $className
     */
    private function loader($className)
    {
//        error_log ('Trying to load '.$className.' via '. __METHOD__."()\n");
        $className = preg_replace("@\\\\@", "/", $className);
        /** @define "$this->dir$className" "VALUE" */
        @include($this->dir . $className . ".php");
    }
}

/**
 * @param $expr
 * @param string $exception
 * @return mixed
 * @throws Exception
 */
function throwonfalse($expr, $exception = "not found")
{
    if ($expr === false) {
        throw new Exception($exception);
    }
    return $expr;
}

/**
 * @param $expr
 * @param string $exception
 * @return mixed
 * @throws Exception
 */
function throwonempty($expr, $exception = "not found")
{
    if (empty($expr)) {
        throw new Exception($exception);
    }
    return $expr;
}

global $COUNTRIES;

$COUNTRIES = array("af" => "Afghanistan", "al" => "Albania", "dz" => "Algeria", "ad" => "Andorra", "ao" => "Angola", "ag" => "Antigua and Barbuda", "ar" => "Argentina", "am" => "Armenia", "au" => "Australia", "at" => "Austria", "az" => "Azerbaijan", "bs" => "Bahamas, The", "bh" => "Bahrain", "bd" => "Bangladesh", "bb" => "Barbados", "by" => "Belarus", "be" => "Belgium", "bz" => "Belize", "bj" => "Benin", "bt" => "Bhutan", "bo" => "Bolivia", "ba" => "Bosnia and Herzegovina", "bw" => "Botswana", "br" => "Brazil", "bn" => "Brunei", "bg" => "Bulgaria", "bf" => "Burkina Faso", "bi" => "Burundi", "kh" => "Cambodia", "cm" => "Cameroon", "ca" => "Canada", "cv" => "Cape Verde", "cf" => "Central African Republic", "td" => "Chad", "cl" => "Chile", "cn" => "China, People's Republic of", "co" => "Colombia", "km" => "Comoros", "cd" => "Congo, Democratic Republic of the (Congo – Kinshasa)", "cg" => "Congo, Republic of the (Congo – Brazzaville)", "cr" => "Costa Rica", "ci" => "Cote d'Ivoire (Ivory Coast)", "hr" => "Croatia", "cu" => "Cuba", "cy" => "Cyprus", "cz" => "Czech Republic", "dk" => "Denmark", "dj" => "Djibouti", "dm" => "Dominica", "do" => "Dominican Republic", "ec" => "Ecuador", "eg" => "Egypt", "sv" => "El Salvador", "gq" => "Equatorial Guinea", "er" => "Eritrea", "ee" => "Estonia", "et" => "Ethiopia", "fj" => "Fiji", "fi" => "Finland", "fr" => "France", "ga" => "Gabon", "gm" => "Gambia, The", "ge" => "Georgia", "de" => "Germany", "gh" => "Ghana", "gr" => "Greece", "gd" => "Grenada", "gt" => "Guatemala", "gn" => "Guinea", "gw" => "Guinea-Bissau", "gy" => "Guyana", "ht" => "Haiti", "hn" => "Honduras", "hu" => "Hungary", "is" => "Iceland", "in" => "India", "id" => "Indonesia", "ir" => "Iran", "iq" => "Iraq", "ie" => "Ireland", "il" => "Israel", "it" => "Italy", "jm" => "Jamaica", "jp" => "Japan", "jo" => "Jordan", "kz" => "Kazakhstan", "ke" => "Kenya", "ki" => "Kiribati", "kp" => "Korea, Democratic People's Republic of (North Korea)", "kr" => "Korea, Republic of  (South Korea)", "kw" => "Kuwait", "kg" => "Kyrgyzstan", "la" => "Laos", "lv" => "Latvia", "lb" => "Lebanon", "ls" => "Lesotho", "lr" => "Liberia", "ly" => "Libya", "li" => "Liechtenstein", "lt" => "Lithuania", "lu" => "Luxembourg", "mk" => "Macedonia", "mg" => "Madagascar", "mw" => "Malawi", "my" => "Malaysia", "mv" => "Maldives", "ml" => "Mali", "mt" => "Malta", "mh" => "Marshall Islands", "mr" => "Mauritania", "mu" => "Mauritius", "mx" => "Mexico", "fm" => "Micronesia", "md" => "Moldova", "mc" => "Monaco", "mn" => "Mongolia", "me" => "Montenegro", "ma" => "Morocco", "mz" => "Mozambique", "mm" => "Myanmar (Burma)", "na" => "Namibia", "nr" => "Nauru", "np" => "Nepal", "nl" => "Netherlands", "nz" => "New Zealand", "ni" => "Nicaragua", "ne" => "Niger", "ng" => "Nigeria", "no" => "Norway", "om" => "Oman", "pk" => "Pakistan", "pw" => "Palau", "pa" => "Panama", "pg" => "Papua New Guinea", "py" => "Paraguay", "pe" => "Peru", "ph" => "Philippines", "pl" => "Poland", "pt" => "Portugal", "qa" => "Qatar", "ro" => "Romania", "ru" => "Russia", "rw" => "Rwanda", "kn" => "Saint Kitts and Nevis", "lc" => "Saint Lucia", "vc" => "Saint Vincent and the Grenadines", "ws" => "Samoa", "sm" => "San Marino", "st" => "Sao Tome and Principe", "sa" => "Saudi Arabia", "sn" => "Senegal", "rs" => "Serbia", "sc" => "Seychelles", "sl" => "Sierra Leone", "sg" => "Singapore", "sk" => "Slovakia", "si" => "Slovenia", "sb" => "Solomon Islands", "so" => "Somalia", "za" => "South Africa", "es" => "Spain", "lk" => "Sri Lanka", "sd" => "Sudan", "sr" => "Suriname", "sz" => "Swaziland", "se" => "Sweden", "ch" => "Switzerland", "sy" => "Syria", "tj" => "Tajikistan", "tz" => "Tanzania", "th" => "Thailand", "tl" => "Timor-Leste (East Timor)", "tg" => "Togo", "to" => "Tonga", "tt" => "Trinidad and Tobago", "tn" => "Tunisia", "tr" => "Turkey", "tm" => "Turkmenistan", "tv" => "Tuvalu", "ug" => "Uganda", "ua" => "Ukraine", "ae" => "United Arab Emirates", "gb" => "United Kingdom", "us" => "United States", "uy" => "Uruguay", "uz" => "Uzbekistan", "vu" => "Vanuatu", "va" => "Vatican City", "ve" => "Venezuela", "vn" => "Vietnam", "ye" => "Yemen", "zm" => "Zambia", "zw" => "Zimbabwe", "ge" => "Abkhazia", "tw" => "China, Republic of (Taiwan)", "az" => "Nagorno-Karabakh", "cy" => "Northern Cyprus", "md" => "Pridnestrovie (Transnistria)", "so" => "Somaliland", "ge" => "South Ossetia", "au" => "Ashmore and Cartier Islands", "cx" => "Christmas Island", "cc" => "Cocos (Keeling) Islands", "au" => "Coral Sea Islands", "hm" => "Heard Island and McDonald Islands", "nf" => "Norfolk Island", "nc" => "New Caledonia", "pf" => "French Polynesia", "yt" => "Mayotte", "gp" => "Saint Barthelemy", "gp" => "Saint Martin", "pm" => "Saint Pierre and Miquelon", "wf" => "Wallis and Futuna", "tf" => "French Southern and Antarctic Lands", "pf" => "Clipperton Island", "bv" => "Bouvet Island", "ck" => "Cook Islands", "nu" => "Niue", "tk" => "Tokelau", "gg" => "Guernsey", "im" => "Isle of Man", "je" => "Jersey", "ai" => "Anguilla", "bm" => "Bermuda", "io" => "British Indian Ocean Territory", "" => "British Sovereign Base Areas", "vg" => "British Virgin Islands", "ky" => "Cayman Islands", "fk" => "Falkland Islands (Islas Malvinas)", "gi" => "Gibraltar", "ms" => "Montserrat", "pn" => "Pitcairn Islands", "sh" => "Saint Helena", "gs" => "South Georgia and the South Sandwich Islands", "tc" => "Turks and Caicos Islands", "mp" => "Northern Mariana Islands", "pr" => "Puerto Rico", "as" => "American Samoa", "um" => "Baker Island", "gu" => "Guam", "um" => "Howland Island", "um" => "Jarvis Island", "um" => "Johnston Atoll", "um" => "Kingman Reef", "um" => "Midway Islands", "um" => "Navassa Island", "um" => "Palmyra Atoll", "vi" => "U.S. Virgin Islands", "um" => "Wake Island", "hk" => "Hong Kong", "mo" => "Macau", "fo" => "Faroe Islands", "gl" => "Greenland", "gf" => "French Guiana", "gp" => "Guadeloupe", "mq" => "Martinique", "re" => "Reunion", "ax" => "Aland", "aw" => "Aruba", "an" => "Netherlands Antilles", "sj" => "Svalbard", "ac" => "Ascension", "ta" => "Tristan da Cunha", "aq" => "Antarctica", "cs" => "Kosovo", "ps" => "Palestinian Territories (Gaza Strip and West Bank)", "eh" => "Western Sahara", "aq" => "Australian Antarctic Territory", "aq" => "Ross Dependency", "aq" => "Peter I Island", "aq" => "Queen Maud Land", "aq" => "British Antarctic Territory");

function findcountrybycode($code)
{
    global $COUNTRIES;
    return $COUNTRIES[strtolower($code)];
}

function &countries()
{
    global $COUNTRIES;
    return $COUNTRIES;
}

/**
 * @param $str
 * @param string $replace
 * @return mixed
 */
function nonewline($str, $replace = " ")
{
    return preg_replace("@[\r\n]@", $replace, $str);
}

/**
 * @param $attr
 * @return bool
 */
function has_session_attr($attr)
{
    return !empty($_SESSION[$attr]);
}

/**
 * @param $attr
 * @param $val
 */
function set_session_attr($attr, $val)
{
    $_SESSION[$attr] = $val;
}

/**
 * @param $attr
 * @param $val
 * @param $default
 * @return string
 */
function get_session_attr($attr, $default = "")
{
    return copyval($_SESSION[$attr], $default);
}

/**
 * @param null $f
 * @return null
 */
function form(&$f = null)
{
    static $s = false;
    if (isset($f)) {
        $s = $f;
    }
    return $s;
}

/**
 * @param $dir
 * @return bool
 */
function canreaddir($dir)
{
    return file_exists($dir) && is_dir($dir);
}

/**
 * @param object $object
 */
function isempty($object)
{
    if (!isset($object)) {
        return true;
    }
    foreach ($object as $k) {
        return false;
    }
    return true;

}

/**
 * @param $k
 * @param $a
 */
function ake($k, $a)
{

    if (!isset($a)) {
        return false;
    }

    if (is_array($a)) {
        return $a[$k];
    }

    if (is_object($a)) {
        return $a->$k;
    }

    return false;

}

function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function copyarray($a)
{
    if (!is_array($a)) {
        return array();
    }
    return $a;
}

function array_clear_from_empty($a)
{

    $b = array();

    foreach ($a as $k_ => $l_) {
        if (!empty($l_)) {
            $b[$k_] = $l_;
        }
    }

    return $b;

}

function __truefalse($tf)
{
    if ($tf) {
        echo "true";
    } else {
        echo "false";
    }
}

global $___translations;

$___translations = array();

global $___curlang;

$___curlang = "de";

function ___($str)
{


    global $___translations;
    global $___curlang;


    $res = $___translations[$___curlang][$str];

    if (!empty($res)) {
        return html($res);
    }

    return html($str);

}

function gettranslation($str)
{

    global $___translations;
    global $___curlang;


    $res = $___translations[$___curlang][$str];

    return copyval($res, $str);

}

function get___($str)
{

    global $___translations;
    global $___curlang;

    $res = $___translations[$___curlang][$str];

    if (!empty($res)) {
        return get_($res);
    }

    return get_($str);

}

function myisint($a)
{
    $isint = is_int($a);
    if ($isint) {
        return true;
    }
    return (intval($a) . "" === $a);
}


function includeViewPart($file, $raiseException = false)
{

    $path = $file;

    if (is_dir($path)) {
        if ($raiseException)
            throw new Exception("view part not found $file");
        return;
    }

//    if ($raiseException) {
    if (!file_exists($path)) {
        if ($raiseException)
            throw new Exception("view part not found $file");
        else return;
    }
//    }

    include($path);

}

function sendemail($subject, $textbody, $htmlbody, $recipients, $to, $from)
{

    $mimeparams['text_encoding'] = "7bit";
    $mimeparams['text_charset'] = "UTF-8";
    $mimeparams['head_charset'] = "UTF-8";
    $mimeparams['html_charset'] = "UTF-8";
    $mimeparams['eol'] = $crlf;

    $mime = new Mail_mime($mimeparams);

//    $mime = new Mail_mime(array('eol' => $crlf));

    if (!empty($textbody)) {
        $mime->setTXTBody($textbody);
    }

    if (!empty($htmlbody)) {
        $mime->setHTMLBody($htmlbody);
    } else {
        $mime->setHTMLBody("<html><body><pre>$textbody</pre></body></html>");
    }

    // Perform the actual send.
    $smtp_params = array();
    $smtp_params['host'] = 'mail.example.com';
    $smtp_params['port'] = '25';
    $smtp_params['username'] = 'mail@oes.digital';
    $smtp_params['password'] = 'xxxxxxx';
    $smtp_params['auth'] = TRUE;
    $smtp_params['persist'] = TRUE;
    $smtp_params['verbose'] = TRUE;

    $m = new Mail();

    $mail = $m->factory('smtp', $smtp_params);

    $hdrs["From"] = $from;
    $hdrs["To"] = "mail@oes.digital";
    $hdrs["Subject"] = $subject;
    $hdrs["Date"] = date("r");

    $body = $mime->get();
    $hdrs = $mime->headers($hdrs);

    if (!is_array($recipients)) {
        $recipients = array($recipients);
    }

    $recipients = implode(",", $recipients);

    $mail->send($recipients, $hdrs, $body);


}

global $_BODYPARTS;
global $_BODYPARTS_BASEDIR;

function setBodyPartsBaseDir($dir)
{
    global $_BODYPARTS_BASEDIR;
    $_BODYPARTS_BASEDIR = $dir;
}

function getViewFilePath($key)
{
    global $_BODYPARTS;
    global $_BODYPARTS_BASEDIR;
    $path = $_BODYPARTS[$key];
    if (empty($path)) {
        return $_BODYPARTS_BASEDIR . "/view/empty.php";
    }
    return $_BODYPARTS_BASEDIR . "/" . $path;
}

function setViewBodyParts($bp, $dir = "./")
{
    global $_BODYPARTS;
    global $_BODYPARTS_BASEDIR;
    $_BODYPARTS_BASEDIR = $dir;
    $_BODYPARTS = $bp;
}

function setViewFilePath($key, $path)
{
    global $_BODYPARTS;
    $_BODYPARTS[$key] = $path;
}

function formatPreis($preis)
{
    return str_replace(".", ",", sprintf("%3.2f", $preis));
}

function eFormatPreis($preis)
{
    echo formatPreis($preis);
}

function cgi($in, $key, $default = "")
{
    return copyval($in[$key], $default);
}

function eCgi($in, $key, $default = "")
{
    html(cgi($in, $key, $default));
}

function prhtmline($w)
{

    if (empty($w)) {
        return;
    }

    echo $w, "\n";

}

class HtmlOutput
{


    public function pr($m, $pre = null, $post = null)
    {

        if (empty($m)) {
            return;
        }

        if (isset($pre)) {
            echo $pre;
        }

        echo $m;

        if (isset($post)) {
            echo $post;
        }

    }

    public function table($class = "")
    {
        echo "<table";
        $this->prclass($class);
        echo ">";
    }

    public function prclass($class)
    {

        if (empty($class)) {
            return;
        }

        echo " class=\"$class\" ";

    }

    public function cltable()
    {
        echo "</table>";
    }

    public function tr($class = "")
    {
        echo "<tr";
        $this->prclass($class);
        echo ">";
    }

    public function cltr()
    {
        echo "</tr>";
    }

    public function date($time)
    {
        $this->trim(date("d.m.Y H:i", $time));
    }

    public function trim($m, $pre = null, $post = null, $trailingNewline = true)
    {

        $m = trim($m);

        if (empty($m)) {
            return;
        }
        if (isset($pre)) {
            echo $pre;
        }

        echo $m;

        if (isset($post)) {
            echo $post;
        }

        if ($trailingNewline) {
            echo "\n";
        }

    }

    public function thead($columns = array(), $classes = array())
    {

        echo "<thead>";
        foreach ($columns as $k => $col) {
            $this->th($col, $classes[$k]);
        }
        echo "</thead>";

    }

    public function th($text = null, $class = "")
    {

        echo "<th";

        $this->prclass($class);

        echo ">";

        if (isset($text)) {
            echo $text, "</th>";
        }

    }

    public function tag($tag, $attribs, $closedTag = false)
    {
        if ($closedTag) {
            echo "</$tag";
        } else {
            echo "<$tag";
        }

        foreach ($attribs as $k => $v) {
            $this->prattrib($k, $v);
        }
        echo ">";
    }

    public function prattrib($name, $value)
    {

        if (empty($value)) {
            return;
        }

        echo " $name=\"" . get_($value) . "\" ";

    }

    public function cltag($name)
    {
        echo "</$name>";
    }

    public function prstyle($style)
    {

        if (empty($style)) {
            return;
        }

        echo " style=\"$style\" ";

    }

    public function td($text = null, $class = "", $ext = "")
    {

        echo "<td";

        $this->prclass($class);

        if (!empty($ext)) {
            echo $ext;
        }

        echo ">";

        if (isset($text)) {
            echo $text, "</td>";
        }

    }

    public function cltd()
    {
        echo "</td>";
    }

    public function ahref($text, $link, $class = "")
    {

        if (empty($text)) {
            return "";
        }

        $str = "<a href=\"$link\" class=\"$class\" title=\"\">" . $text . "</a>";

        return $str;

    }

    public function button($text, $name, $value, $type = "submit", $class = "", $style = "")
    {

        echo "<button";

        $this->prattrib("name", $name);
        $this->prattrib("value", $value);
        $this->prattrib("style", $style);
        $this->prattrib("class", $class);
        $this->prattrib("type", $class);

        echo ">" . get_($text) . "</button>";


    }

    public function prhtml($t)
    {
        echo $this->html($t);
    }

    public function html($t)
    {
        return htmlentities($t, ENT_COMPAT, "UTF-8");
    }
}


function composeURL($url, $params)
{

    if (empty($params)) {
        return $url;
    }

    $str = "";

    $isfirst = true;
    foreach ($params as $k => $v) {
        if ($isfirst) {
            $str .= "?$k=" . urlencode($v);
            $isfirst = false;
        } else {
            $str .= "&$k=" . urlencode($v);
        }
    }

    return $url . $str;

}

function toMysqlTimestamp($time = null)
{
    if (!isset($time)) {
        return date('Y-m-d H:i:s');
    }
    return date('Y-m-d H:i:s', $time);
}

function cmp_int($a, $b)
{
    if ($a < $b) {
        return -1;
    }
    if ($a == $b) {
        return 0;
    }
    return 1;
}

function rcmp_int($a, $b)
{
    if ($a < $b) {
        return 1;
    }
    if ($a == $b) {
        return 0;
    }
    return -1;
}

function show_json_last_error()
{
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo ' - No errors';
            break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
            break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
            break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
        default:
            echo ' - Unknown error';
            break;
    }

}

if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL)
    {

        if ($code !== NULL) {

            switch ($code) {
                case 100:
                    $text = 'Continue';
                    break;
                case 101:
                    $text = 'Switching Protocols';
                    break;
                case 200:
                    $text = 'OK';
                    break;
                case 201:
                    $text = 'Created';
                    break;
                case 202:
                    $text = 'Accepted';
                    break;
                case 203:
                    $text = 'Non-Authoritative Information';
                    break;
                case 204:
                    $text = 'No Content';
                    break;
                case 205:
                    $text = 'Reset Content';
                    break;
                case 206:
                    $text = 'Partial Content';
                    break;
                case 300:
                    $text = 'Multiple Choices';
                    break;
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
                case 304:
                    $text = 'Not Modified';
                    break;
                case 305:
                    $text = 'Use Proxy';
                    break;
                case 400:
                    $text = 'Bad Request';
                    break;
                case 401:
                    $text = 'Unauthorized';
                    break;
                case 402:
                    $text = 'Payment Required';
                    break;
                case 403:
                    $text = 'Forbidden';
                    break;
                case 404:
                    $text = 'Not Found';
                    break;
                case 405:
                    $text = 'Method Not Allowed';
                    break;
                case 406:
                    $text = 'Not Acceptable';
                    break;
                case 407:
                    $text = 'Proxy Authentication Required';
                    break;
                case 408:
                    $text = 'Request Time-out';
                    break;
                case 409:
                    $text = 'Conflict';
                    break;
                case 410:
                    $text = 'Gone';
                    break;
                case 411:
                    $text = 'Length Required';
                    break;
                case 412:
                    $text = 'Precondition Failed';
                    break;
                case 413:
                    $text = 'Request Entity Too Large';
                    break;
                case 414:
                    $text = 'Request-URI Too Large';
                    break;
                case 415:
                    $text = 'Unsupported Media Type';
                    break;
                case 500:
                    $text = 'Internal Server Error';
                    break;
                case 501:
                    $text = 'Not Implemented';
                    break;
                case 502:
                    $text = 'Bad Gateway';
                    break;
                case 503:
                    $text = 'Service Unavailable';
                    break;
                case 504:
                    $text = 'Gateway Time-out';
                    break;
                case 505:
                    $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;

    }
}

function imapheaderdecode($str)
{
    $parts = imap_mime_header_decode($str);
    return concatproperties($parts, "text", "");
}

function extractemailfromstr($str)
{
    $str = imapheaderdecode($str);
    if (preg_match_all("/^\s*\"*(.*?)\"*\s*<?([\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+)\s*>?$/i", $str, $matches)) {
        unset ($matches[0]);
        return $matches;
    }
    return false;
}

function isnullorempty(&$p)
{
    return is_null($p) || empty($p);
}

function normalizeUtf8String($s)
{
    // Normalizer-class missing!
    if (!class_exists("Normalizer", $autoload = false))
        return $original_string;


    // maps German (umlauts) and other European characters onto two characters before just removing diacritics
    $s = preg_replace('@\x{00c4}@u', "AE", $s); // umlaut Ä => AE
    $s = preg_replace('@\x{00d6}@u', "OE", $s); // umlaut Ö => OE
    $s = preg_replace('@\x{00dc}@u', "UE", $s); // umlaut Ü => UE
    $s = preg_replace('@\x{00e4}@u', "ae", $s); // umlaut ä => ae
    $s = preg_replace('@\x{00f6}@u', "oe", $s); // umlaut ö => oe
    $s = preg_replace('@\x{00fc}@u', "ue", $s); // umlaut ü => ue
    $s = preg_replace('@\x{00f1}@u', "ny", $s); // ñ => ny
    $s = preg_replace('@\x{00ff}@u', "yu", $s); // ÿ => yu


    // maps special characters (characters with diacritics) on their base-character followed by the diacritical mark
    // exmaple:  Ú => U´,  á => a`
    $s = Normalizer::normalize($s, Normalizer::FORM_D);


    $s = preg_replace('@\pM@u', "", $s); // removes diacritics


    $s = preg_replace('@\x{00df}@u', "ss", $s); // maps German ß onto ss
    $s = preg_replace('@\x{00c6}@u', "AE", $s); // Æ => AE
    $s = preg_replace('@\x{00e6}@u', "ae", $s); // æ => ae
    $s = preg_replace('@\x{0132}@u', "IJ", $s); // ? => IJ
    $s = preg_replace('@\x{0133}@u', "ij", $s); // ? => ij
    $s = preg_replace('@\x{0152}@u', "OE", $s); // Œ => OE
    $s = preg_replace('@\x{0153}@u', "oe", $s); // œ => oe

    $s = preg_replace('@\x{00d0}@u', "D", $s); // Ð => D
    $s = preg_replace('@\x{0110}@u', "D", $s); // Ð => D
    $s = preg_replace('@\x{00f0}@u', "d", $s); // ð => d
    $s = preg_replace('@\x{0111}@u', "d", $s); // d => d
    $s = preg_replace('@\x{0126}@u', "H", $s); // H => H
    $s = preg_replace('@\x{0127}@u', "h", $s); // h => h
    $s = preg_replace('@\x{0131}@u', "i", $s); // i => i
    $s = preg_replace('@\x{0138}@u', "k", $s); // ? => k
    $s = preg_replace('@\x{013f}@u', "L", $s); // ? => L
    $s = preg_replace('@\x{0141}@u', "L", $s); // L => L
    $s = preg_replace('@\x{0140}@u', "l", $s); // ? => l
    $s = preg_replace('@\x{0142}@u', "l", $s); // l => l
    $s = preg_replace('@\x{014a}@u', "N", $s); // ? => N
    $s = preg_replace('@\x{0149}@u', "n", $s); // ? => n
    $s = preg_replace('@\x{014b}@u', "n", $s); // ? => n
    $s = preg_replace('@\x{00d8}@u', "O", $s); // Ø => O
    $s = preg_replace('@\x{00f8}@u', "o", $s); // ø => o
    $s = preg_replace('@\x{017f}@u', "s", $s); // ? => s
    $s = preg_replace('@\x{00de}@u', "T", $s); // Þ => T
    $s = preg_replace('@\x{0166}@u', "T", $s); // T => T
    $s = preg_replace('@\x{00fe}@u', "t", $s); // þ => t
    $s = preg_replace('@\x{0167}@u', "t", $s); // t => t

    // remove all non-ASCii characters
    $s = preg_replace('@[^\0-\x80]@u', "", $s);


    // possible errors in UTF8-regular-expressions
    if (empty($s))
        return $original_string;
    else
        return $s;
}

function normalizeFormC($in)
{
    if (is_array($in)) {
        $out = array();
        foreach ($in as $k => $l) {
            $out[$k] = Normalizer::normalize($l, Normalizer::FORM_C);
        }
        return $out;
    } else {
        return Normalizer::normalize($in, Normalizer::FORM_C);
    }
}

function normalizeFormD($in)
{
    if (is_array($in)) {
        $out = array();
        foreach ($in as $k => $l) {
            $out[$k] = Normalizer::normalize($l, Normalizer::FORM_D);
        }
        return $out;
    } else {
        return Normalizer::normalize($in, Normalizer::FORM_D);
    }
}

function cleanNonUtf8Characters($str)
{
    return preg_replace('/[^\\p{L}\\p{N}]/ui', '', $str);
}

function normalizeToAsciiWithoutSlash($str)
{
    return preg_replace('@[^(\x20-\x7F)/]*@', '', normalizeFormD($str));
}

function normalizeToAscii($str)
{
    return preg_replace('@[^(\x20-\x7F)]*@', '', normalizeFormD($str));
}

function normalizeToSimpleSortAscii($str)
{
    $str = normalizeFormD($str);
    return strtolower(preg_replace('/[^a-zA-Z0-9]/i', '', $str));
}

function normalizeToSimpleSortAsciiWithSpace($str)
{
    $str = normalizeFormD($str);
    return strtolower(preg_replace('/[^a-zA-Z0-9 \-_]/i', '', $str));
}

function normalizeToSimpleSortAsciiWithGreek($str)
{
    $str = normalizeFormD($str);
    return mb_substr(mb_strtolower(preg_replace('/[^a-zA-Z0-9\p{Greek}]/ui', '', $str)), 0, 32);
}

function transliterateRussian($str)
{
    return transliterator_transliterate('Russian-Latin/BGN', $str);
}

function transliterateGreek($str)
{
    return transliterator_transliterate('Greek-Latin/BGN', $str);
}

function normalizeToSimpleSortAsciiWithTransliterationBeforehand($str)
{
    return normalizeToSimpleSortAscii(transliterateRussian(transliterateGreek($str)));
}

function getUrlPageName($str)
{
    return str_replace(" ", "_", $str);
}

function simpleXmlToArray($xml)
{

    $data = array();

    $namespaces = $xml->getNamespaces(true);

    foreach ($namespaces as $ns => $nsURN) {
        $o = $xml->children($ns, true);
        foreach ($o as $key => $value) {
            $data[$ns . ':' . $key] = simpleXmlToArray($value);
        }
    }

    $data[''] = (string)$xml;

    return $data;

}


class SimpleDataHolder
{

//    private $_data = array();

    function __construct($_data = null)
    {
        if (isset($_data) && is_array($_data)) {
            $this->setData($_data);
        }
    }

//    public function __isset($name)
//    {
//        return isset($this->_data[$name]);
//    }

//    public function __get($name)
//    {
//        return $this->_data[$name];
//    }
//?
//    public function __set($name, $value)
//    {
//        $this->_data[$name] = $value;
//    }

//    function __unset($name)
//    {
//        unset($this->_data[$name]);
//    }
//    function __call($name, $arguments)
//    {
//        $this->${name} = $arguments[0];
//    }
//    function __set($name, $value)
//    {
//        $this->${name} = $value;
//    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        if (empty($data)) {
            return false;
        }
        if (is_object($data) || is_array($data)) {
            foreach ($data as $k => $v) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * @return mixed
     */
    public function &getData()
    {
        return get_object_vars($this);
    }


}

class SimpleDataHolder2
{

    private $_data = array();

    function __construct($_data = null)
    {
        if (isset($_data) && is_array($_data)) {
            $this->_data = $_data;
        }
    }

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    function __unset($name)
    {
        unset($this->_data[$name]);
    }

    /**
     * @return mixed
     */
    public function &getData()
    {
        return $this->_data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }


}

function x_array_merge_ext(array $arr1, $arr2 = null)
{

    if (empty($arr2)) {
        return $arr1;
    }

    if (!is_array($arr2)) {
        return $arr2 = array($arr1);
    }

    return array_merge($arr1, $arr2);

}


function x_deep_replace($search, $subject)
{
    $subject = (string)$subject;

    $count = 1;
    while ($count) {
        $subject = str_replace($search, '', $subject, $count);
    }

    return $subject;
}

function x_empty($var)
{
    return empty($var);
}

function x_esc_url($url)
{

    $original_url = $url;

    if ('' == $url)
        return $url;

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = x_deep_replace($strip, $url);
    $url = str_replace(';//', '://', $url);
    /* If the URL doesn't appear to contain a scheme, we
     * presume it needs http:// appended (unless a relative
     * link starting with /, # or ? or a php file).
     */
    if (strpos($url, ':') === false && !in_array($url[0], array('/', '#', '?')) &&
        !preg_match('/^[a-z0-9-]+?\.php/i', $url)
    )
        $url = 'http://' . $url;

    if ('/' === $url[0]) {
        $good_protocol_url = $url;
    } else {
        if (!is_array($protocols))
            $protocols = x_allowed_protocols();
        $good_protocol_url = x_wp_kses_bad_protocol($url, $protocols);
        if (strtolower($good_protocol_url) != strtolower($url))
            return '';
    }

    /**
     * Filter a string cleaned and escaped for output as a URL.
     *
     * @param string $good_protocol_url The cleaned URL to be returned.
     * @param string $original_url The URL prior to cleaning.
     * @param string $_context If 'display', replace ampersands and single quotes only.
     * @since 2.3.0
     *
     */
    return $good_protocol_url;
}

function x_allowed_protocols()
{
    static $protocols;

    if (empty($protocols)) {
        $protocols = array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp');
        $protocols = apply_filters('kses_allowed_protocols', $protocols);
    }

    return $protocols;
}

function x_wp_kses_bad_protocol($string, $allowed_protocols)
{
    $string = x_wp_kses_no_null($string);
    $iterations = 0;

    do {
        $original_string = $string;
        $string = x_wp_kses_bad_protocol_once($string, $allowed_protocols);
    } while ($original_string != $string && ++$iterations < 6);

    if ($original_string != $string)
        return '';

    return $string;
}

function x_wp_kses_bad_protocol_once($string, $allowed_protocols, $count = 1)
{
    $string2 = preg_split('/:|&#0*58;|&#x0*3a;/i', $string, 2);
    if (isset($string2[1]) && !preg_match('%/\?%', $string2[0])) {
        $string = trim($string2[1]);
        $protocol = x_wp_kses_bad_protocol_once2($string2[0], $allowed_protocols);
        if ('feed:' == $protocol) {
            if ($count > 2)
                return '';
            $string = x_wp_kses_bad_protocol_once($string, $allowed_protocols, ++$count);
            if (empty($string))
                return $string;
        }
        $string = $protocol . $string;
    }

    return $string;
}

function x_wp_kses_bad_protocol_once2($string, $allowed_protocols)
{
    $string2 = x_wp_kses_decode_entities($string);
    $string2 = preg_replace('/\s/', '', $string2);
    $string2 = x_wp_kses_no_null($string2);
    $string2 = strtolower($string2);

    $allowed = false;
    foreach ((array)$allowed_protocols as $one_protocol)
        if (strtolower($one_protocol) == $string2) {
            $allowed = true;
            break;
        }

    if ($allowed)
        return "$string2:";
    else
        return '';
}

function x_wp_kses_decode_entities($string)
{
    $string = preg_replace_callback('/&#([0-9]+);/', '_wp_kses_decode_entities_chr', $string);
    $string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', '_wp_kses_decode_entities_chr_hexdec', $string);

    return $string;
}

function x_wp_kses_no_null($string)
{
    $string = preg_replace('/\0+/', '', $string);
    $string = preg_replace('/(\\\\0)+/', '', $string);

    return $string;
}

function x_apcFetch($key, $callback = null, $ttl = 0, $force = false, $domain = '')
{

    $res = x_apc_fetch($key, $success, $domain);

    if ($success && !$force) {
        {
            Oes::cache_debug('apc.fetch.success', ['id' => $key]);
        }
        return $res;
    }

    if (empty($callback)) {
        throw new \Exception("not found");
    }

    Oes::cache_debug('apc.store', ['id' => $key, 'forced' => $force]);

    /**
     * @var Closure $callback
     */
    $res = $callback();

//    error_log("apc.miss:: $key $UNIQ_APC_KEY $domain ($force) (ttl=$ttl)");

    $ret = x_apc_store($key, $res, $ttl, $domain);

    if ($ret !== true) {
        Oes::error('apc.store.error', ['id' => $key, 'ret' => $ret]);
    }

    {
        Oes::cache_debug('apc.store.success', ['id' => $key]);
    }

    return $res;

}


function x_apcStore($key, $value, $ttl = 0)
{
    return x_apc_store($key, $value, $ttl);
}

function x_str_replace_first($needle, $replace, $haystack)
{
    if (($pos = strpos($haystack, $needle)) !== false) {
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }
    return $haystack;
}

function x_eval_char_class($str)
{
    $normalizedval = normalizeToSimpleSortAsciiWithGreek($str);
    $firstchar = mb_substr($normalizedval, 0, 1);
    if (!preg_match('@[a-zA-Z\p{Greek}]@u', $firstchar)) {
        $firstchar = '#';
    }
    return $firstchar;
}

function x_as_array($p = null, $notifempty = false)
{

    if (is_array($p)) {
        return $p;
    }

    if (!isset($p)) {
        return array();
    }

    if (is_null($p)) {
        return array();
    }

    if ($notifempty && empty($p)) {
        return [];
    }

    return array($p);

}

function x_tolower($array)
{
    if (is_array($array)) {
        return array_map(function ($str) {
            return strtolower($str);
        }, $array);
    } else {
        return strtolower($array);
    }
}

function x_mb_tolower($array, $encoding = null)
{
    if (is_array($array)) {
        return array_map(function ($str) use ($encoding) {
            return mb_strtolower($str, $encoding);
        }, $array);
    } else {
        return mb_strtolower($array, $encoding);
    }
}

/*function x_doublequote($p) {
    if (is_array($p)) {
        return array_map(function($str) { return '"'.$str.'"'; },$p);
    }
    return '"'.$p.'"';
}*/

function x_clearFromApostrophS($x)
{
    $x = preg_replace("@[’'’]s@u", "", $x);
    $x = preg_replace("@[’'’]$@u", "", $x);
    return $x;
}

function x_transformFamilyGivenName($x)
{

    $x = $y = normalizeFormC(trim($x));

    $x = x_clearFromApostrophS($x);

    if (preg_match('@^(.*?)<[ib]>(.*?)</[ib]>(.*?)$@u', $x, $matches)) {

        $firstname = $matches[1];
        $lastname = $matches[2];
        $appendix = $matches[3];

        if (!empty($firstname)) {
            $xx = $lastname . ', ' . $firstname;
        } else {
            $xx = $lastname;
        }

        $appendix = trim($appendix);

        if (!empty($appendix)) {
            if (startswith($appendix, ',')) {
                $xx = $xx . $appendix;
            } else {
                $xx = $xx . ', ' . $appendix;
            }
        }

        return $xx;

    } else {

        $xN = str_replace(', Jr.', '', $x);
        $xN = str_replace(',Jr.', '', $xN);
        $xN = str_replace('Jr.', '', $xN);

        $xN = trim($xN);

        $hasJr = false;

        if ($xN != $x) {
            $hasJr = true;
        }

        if (stripos($xN, ',') !== false) {
            return $x;
        }


        $parts = explode(' ', $xN);

        $last = array_pop($parts);

        if (empty($parts)) {
            $x = $last;
        } else {
            $x = $last . ', ' . implode(' ', $parts);
        }

        if ($hasJr) {
            $x .= ', Jr.';
        }


        return $x;

    }

}

function x_apc_fetch($key, &$success, $domain = '')
{
    global $UNIQ_APC_KEY;
    $key = $domain . ':' . $UNIQ_APC_KEY . ':' . $key;
    {
        Oes::cache_debug('x_apc_fetch', ['key' => $key]);
    }
    return apcu_fetch($key, $success);
}

function x_apc_store($key, $data, $ttl = 0, $domain = '')
{
    global $UNIQ_APC_KEY;
    $key = $domain . ':' . $UNIQ_APC_KEY . ':' . $key;
    {
        Oes::cache_debug('x_apc_store', ['key' => $key, 'size' => strlen(serialize($data))]);
    }
    return apcu_store($key, $data, $ttl);
}

function x_apc_delete($key, $domain = '')
{
    global $UNIQ_APC_KEY;
    $key = $domain . ':' . $UNIQ_APC_KEY . ':' . $key;
    {
        Oes::cache_debug('x_apc_delete', ['key' => $key]);
    }
    return apcu_delete($key);
}

//function x_implode($glue, $array)
//{
//    if (empty($array) || !is_array($array)) {
//        return $array;
//    }
//    return implode($glue, $array);
//}

function x_truncateExcerpt($excerpt, $length = 32)
{

    if (!$excerpt) {
        return '';
    }

    $excerpt = substr($excerpt, 0, $length);
    $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
    $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));

    return $excerpt;

}

function x_createAsciiURL($str)
{
    $url = normalizeToAscii($str);
    $url = preg_replace('/[^a-zA-Z0-9_]+/', ' ', $url);
    $url = preg_replace('/\s+/', ' ', $url);
    $url = str_replace(' ', '_', $url);
    return $url;
}

function x_trim($a)
{
    if (empty($a)) {
        return $a;
    }

    if (is_array($a)) {
        return array_map(function ($str) {
            return trim($str);
        }, $a);
    } else {
        return trim($a);
    }
}

function x_array_keys($a)
{
    if (empty($a)) {
        return array();
    }
    return array_keys($a);
}

function mb_strcasecmp($str1, $str2, $encoding = null)
{
    if (null === $encoding) {
        $encoding = mb_internal_encoding();
    }
    return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
}

function x_values_as_keys($list)
{
    $r = array();
    if (empty($list)) {
        return $r;
    }
    foreach ($list as $x) {
        $r[$x] = $x;
    }
    return $r;
}

function x_array_merge($x, $p)
{
    if (empty($p)) {
        return $x;
    }
    return array_merge($x, $p);
}


function x_concatName($last, $first = '', $middle = '')
{

    if ($first) {
        $name = $first;
    }
    if ($middle) {
        $name .= " " . $middle;
    }

    if ($last) {
        $name .= " " . $last;
    }

    return trim($name);

}

function x_concatListingName($last, $first = '')
{

    if ($last) {
        $name = $last;
        if ($first) {
            $name .= ', ' . $first;
        }
    } else if ($first) {
        $name = $first;
    }

    return $name;

}

function x_doublequote($str)
{
    if (is_array($str)) {
        return array_map(function ($str) {
            return x_doublequote($str);
        }, $str);
    }
    return '"' . $str . '"';
}

function x_removeLastBrackets($p)
{
    return preg_replace(" @\([^\)]+\)$@", "", $p);
}

/**
 * @param DOMDocument $dom
 * @param DOMNode $elem
 * @param $name
 * @param $value
 * @return DOMNode
 */
function &x_dom_addAttributeToElem(&$dom, &$elem, $name, $value)
{
    $attrib = $dom->createAttribute($name);
    $attrib->value = $value;
    $elem->appendChild($attrib);
    return $elem;
}

/**
 * @param DOMNode $parent
 * @param DOMNode $child
 * @return DOMNode
 */
function &x_dom_addChild($parent, &$child)
{
    $parent->appendChild($child);
    return $parent;
}

function x_updateWikiTemplateNamedParameters($text, $templateName, $addOrModify, $delete, &$found = array())
{

    $found = array();

    $placeholder = "Dleo_ndsanfksdfubxDNSKJS_2";

    $templateCode = preg_replace('@^(\{\{.*?\n\}\}).+@s', "$1", $text);

    $textModified = preg_replace('@^(\{\{.*?\n\}\})(.+)@s', "$placeholder$2", $text);

    $a0 = implode('|', array_keys($addOrModify));

    if (preg_match_all("@\|($a0)\s*=\s*(.+)\n@", $templateCode, $matches)) {
        foreach ($matches[1] as $pos => $name) {
            $value = $matches[2][$pos];
            $found[$name] = $value;
        }
    }

// adding

    foreach ($addOrModify as $field => $value) {

        $fieldExists = array_key_exists($field, $found);

        if ($fieldExists) {
            $templateCode = preg_replace("@\|$field\s*=(.*?)\n@", "|$field=$value\n", $templateCode);
        } else {
            $templateCode = preg_replace("@(\{\{$templateName\s*\n)@", "$1" . "|$field=$value\n", $templateCode);
        }

    }

    foreach ($delete as $field) {
        $templateCode = preg_replace("@\|$field\s*=(.*?)\n@", "", $templateCode);
    }

    return preg_replace("@$placeholder@", $templateCode, $textModified);

}

function fromhtml($str = "")
{
    $str = html_entity_decode($str, ENT_QUOTES, "UTF-8");
    $str = preg_replace("@&.*?;@", " ", $str);
    return $str;
}

function x_getServerProperty($name, $default = null)
{
    $val = $_SERVER[$name];
    if (empty($val)) {
        if (is_null($default)) {
            throw new \Exception("$name not set");
        }
        return $default;
    }
    return $val;
}

function x_httpSendFile($filepath, $mimetype = 'application/octet-stream', $filename = '')
{

    if (!file_exists($filepath)) {
        return false;
    }

    header('Content-Type: ' . $mimetype);

    header('Content-Length: ' . filesize($filepath));

    if (!empty($filename)) {
        header("Content-disposition: attachment; filename=" . rawurlencode($filename));
    }

    $fd = fopen($filepath, 'r');
    fpassthru($fd);
    fclose($fd);

    return true;

}


/**
 * Remove disallowed characters from string to get a nearly safe filename
 *
 * @param string $fileName
 * @return mixed|string
 */
function x_sanitizeFilename($fileName)
{
    static $forbiddenCharacters = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");
    $fileName1 = str_replace($forbiddenCharacters, '', $fileName);
    $fileName2 = preg_replace('/[\s-]+/', '-', $fileName1);
    return trim($fileName2, '.-_');
}

/**
 * Wrap embedded URL in text
 * @param $text
 * @return mixed
 */
function x_wrapUrlInTextWithAHref($text)
{

    $textNew = preg_replace_callback("@(https?|ftps?)://([a-z0-9\-]+\.)+[a-z0-9\-]+(/([a-z0-9;+\(\):!*\-_~\@,\[\]=%#?&\.]+/?)+)?@i", function ($matches) {
        $href = $matches[0];
        $lastCharIsDot = preg_match('@\.$@', $href);
        $label = $href;
        $href = preg_replace('@\.$@', '', $href);
        $ret = <<<EOD
<a href='$href' rel='nofollow' target='_blank'>$href</a>
EOD;

        if ($lastCharIsDot) {
            $ret .= '.';
        }

        return $ret;

    }, $text);

    return $textNew;

}

function x_isAuthenticatedRequest()
{
    $user = x_getAuthenticatedUser();
    return !empty($user);
}

function x_getAuthenticatedUser()
{
    return $_SERVER['REMOTE_USER'];
}

function x_isAuthenticatedUser($users)
{
    $user = x_getAuthenticatedUser();
    if (empty($user)) {
        return false;
    }
    foreach (x_as_array($users) as $u) {
        if ($user == $u) {
            return true;
        }
    }
    return false;
}

function x_removeHtmlTags($html)
{
    return preg_replace('@</?[^>]+>@', '', $html);
}

function x_arrayCopyValuesAsKeys(array $array, array $new = array())
{
    if (empty($array)) {
        return array();
    }
    foreach ($array as $value) {
        $new[$value] = $value;
    }
    return $new;
}

function x_decodeByteEscapedUtf8($str)
{
    return preg_replace("#(\\\\x[0-9A-Fa-f]{2})#ei", "chr(hexdec('\\1'))", $str);
}

function x_xmlToJson($xml)
{

    if (is_string($xml)) {
        return x_xmlToJson(new SimpleXMLElement($xml));
    }

    $out = [];

    $node = dom_import_simplexml($xml);

    $childNodes = $node->childNodes;

    $text = "";

    foreach ($childNodes as $child) {

        $name = $child->nodeName;

        $nodeType = $child->nodeType;

        if ($nodeType == XML_ELEMENT_NODE) {
            $out[$name][] = x_xmlToJson($child);
        } else {
            $text .= $child->nodeValue;
        }
    }

    $out[':text'] = trim($text);

    return $out;

}

function x_getGndRdfXml($id, $cacheDir = false, $convertToJson = true)
{

    if (empty($id)) {
        throw new Exception("x_getGndRdfXml: no ID provided");
    }

    error_log("x_getGndRdfXml $id");

    $cacheFileName = false;

    if ($cacheDir) {

        if (!$convertToJson) {
            $cacheFileName = "$id.xml";
        } else {
            $cacheFileName = "$id.json";
        }

        $cacheFileName = $cacheDir . "/" . $cacheFileName;

        if (file_exists($cacheFileName)) {
            if ($convertToJson) {
                return json_decode(file_get_contents($cacheFileName), true);
            } else {
                return file_get_contents($cacheFileName);
            }
        }

    }

    $xml = x_getRawGndRdfXML($id);

    $BASE = <<<EOD
xmlns:gndo="http://d-nb.info/standards/elementset/gnd#" xmlns:lib="http://purl.org/library/" xmlns:marcRole="http://id.loc.gov/vocabulary/relators/" xmlns:owl="http://www.w3.org/2002/07/owl#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns:geo="http://www.opengis.net/ont/geosparql#" xmlns:umbel="http://umbel.org/umbel#" xmlns:dbp="http://dbpedia.org/property/" xmlns:dnbt="http://d-nb.info/standards/elementset/dnb/" xmlns:rdau="http://rdaregistry.info/Elements/u/" xmlns:sf="http://www.opengis.net/ont/sf#" xmlns:dnb_intern="http://dnb.de/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:v="http://www.w3.org/2006/vcard/ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:bibo="http://purl.org/ontology/bibo/" xmlns:gbv="http://purl.org/ontology/gbv/" xmlns:isbd="http://iflastandards.info/ns/isbd/elements/" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:dc="http://purl.org/dc/elements/1.1/"
EOD;

    $xml = preg_replace("@^(<rdf:Description )@m", "$1$BASE ", $xml);

    $xml = preg_replace_callback('@<(gndo:.*?) (rdf:resource=\"(http://d-nb.info/gnd/(.*?))\")/>@m', function ($matches) {

        $rdfstr = x_getRawGndRdfXML($matches[4]);

        $ret = "<" . $matches[1] . " " . $matches[2] . ">" . $rdfstr . "</" . $matches[1] . ">";

        return $ret;

    }, $xml);

    if ($convertToJson) {
        $json = x_xmlToJson($xml);
    }

    if (!empty($cacheFileName)) {
        if ($convertToJson) {
            file_put_contents($cacheFileName, json_encode($json));
        } else {
            file_put_contents($cacheFileName, $xml);
        }
    }

    if ($convertToJson) {
        return $json;
    } else {
        return $xml;
    }

}

function x_CountryCodesInGerman($lang = 'en', $cc = false)
{

    static $laender;

    if (!isset($laender)) {


        $laender['en']['AF'] = "Afghanistan";
        $laender['de']['AF'] = "Afghanistan";
        $laender['en']['AL'] = "Albania";
        $laender['de']['AL'] = "Albanien";
        $laender['en']['AS'] = "American Samoa";
        $laender['de']['AS'] = "Amerikanisch Samoa";
        $laender['en']['AD'] = "Andorra";
        $laender['de']['AD'] = "Andorra";
        $laender['en']['AO'] = "Angola";
        $laender['de']['AO'] = "Angola";
        $laender['en']['AI'] = "Anguilla";
        $laender['de']['AI'] = "Anguilla";
        $laender['en']['AQ'] = "Antarctica";
        $laender['de']['AQ'] = "Antarktis";
        $laender['en']['AG'] = "Antigua and Barbuda";
        $laender['de']['AG'] = "Antigua und Barbuda";
        $laender['en']['AR'] = "Argentina";
        $laender['de']['AR'] = "Argentinien";
        $laender['en']['AM'] = "Armenia";
        $laender['de']['AM'] = "Armenien";
        $laender['en']['AW'] = "Aruba";
        $laender['de']['AW'] = "Aruba";
        $laender['en']['AT'] = "Austria";
        $laender['de']['AT'] = "Österreich";
        $laender['en']['AU'] = "Australia";
        $laender['de']['AU'] = "Australien";
        $laender['en']['AZ'] = "Azerbaijan";
        $laender['de']['AZ'] = "Aserbaidschan";
        $laender['en']['BS'] = "Bahamas";
        $laender['de']['BS'] = "Bahamas";
        $laender['en']['BH'] = "Bahrain";
        $laender['de']['BH'] = "Bahrain";
        $laender['en']['BD'] = "Bangladesh";
        $laender['de']['BD'] = "Bangladesh";
        $laender['en']['BB'] = "Barbados";
        $laender['de']['BB'] = "Barbados";
        $laender['en']['BY'] = "Belarus";
        $laender['de']['BY'] = "Weißrussland";
        $laender['en']['BE'] = "Belgium";
        $laender['de']['BE'] = "Belgien";
        $laender['en']['BZ'] = "Belize";
        $laender['de']['BZ'] = "Belize";
        $laender['en']['BJ'] = "Benin";
        $laender['de']['BJ'] = "Benin";
        $laender['en']['BM'] = "Bermuda";
        $laender['de']['BM'] = "Bermuda";
        $laender['en']['BT'] = "Bhutan";
        $laender['de']['BT'] = "Bhutan";
        $laender['en']['BO'] = "Bolivia";
        $laender['de']['BO'] = "Bolivien";
        $laender['en']['BA'] = "Bosnia and Herzegovina";
        $laender['de']['BA'] = "Bosnien Herzegowina";
        $laender['en']['BW'] = "Botswana";
        $laender['de']['BW'] = "Botswana";
        $laender['en']['BV'] = "Bouvet Island";
        $laender['de']['BV'] = "Bouvet Island";
        $laender['en']['BR'] = "Brazil";
        $laender['de']['BR'] = "Brasilien";
        $laender['en']['BN'] = "Brunei Darussalam";
        $laender['de']['BN'] = "Brunei Darussalam";
        $laender['en']['BG'] = "Bulgaria";
        $laender['de']['BG'] = "Bulgarien";
        $laender['en']['BF'] = "Burkina Faso";
        $laender['de']['BF'] = "Burkina Faso";
        $laender['en']['BI'] = "Burundi";
        $laender['de']['BI'] = "Burundi";
        $laender['en']['KH'] = "Cambodia";
        $laender['de']['KH'] = "Kambodscha";
        $laender['en']['CM'] = "Cameroon";
        $laender['de']['CM'] = "Kamerun";
        $laender['en']['CA'] = "Canada";
        $laender['de']['CA'] = "Kanada";
        $laender['en']['CV'] = "Cape Verde";
        $laender['de']['CV'] = "Kap Verde";
        $laender['en']['KY'] = "Cayman Islands";
        $laender['de']['KY'] = "Cayman Inseln";
        $laender['en']['CF'] = "Central African Republic";
        $laender['de']['CF'] = "Zentralafrikanische Republik";
        $laender['en']['TD'] = "Chad";
        $laender['de']['TD'] = "Tschad";
        $laender['en']['CL'] = "Chile";
        $laender['de']['CL'] = "Chile";
        $laender['en']['CN'] = "China";
        $laender['de']['CN'] = "China";
        $laender['en']['CO'] = "Colombia";
        $laender['de']['CO'] = "Kolumbien";
        $laender['en']['KM'] = "Comoros";
        $laender['de']['KM'] = "Comoros";
        $laender['en']['CG'] = "Congo";
        $laender['de']['CG'] = "Kongo";
        $laender['en']['CK'] = "Cook Islands";
        $laender['de']['CK'] = "Cook Inseln";
        $laender['en']['CR'] = "Costa Rica";
        $laender['de']['CR'] = "Costa Rica";
        $laender['en']['CI'] = "Côte d'Ivoire";
        $laender['de']['CI'] = "Elfenbeinküste";
        $laender['en']['HR'] = "Croatia";
        $laender['de']['HR'] = "Kroatien";
        $laender['en']['CU'] = "Cuba";
        $laender['de']['CU'] = "Kuba";
        $laender['en']['CZ'] = "Czech Republic";
        $laender['de']['CZ'] = "Tschechien";
        $laender['en']['DK'] = "Denmark";
        $laender['de']['DK'] = "Dänemark";
        $laender['en']['DJ'] = "Djibouti";
        $laender['de']['DJ'] = "Djibouti";
        $laender['en']['DO'] = "Dominican Republic";
        $laender['de']['DO'] = "Dominikanische Republik";
        $laender['en']['TP'] = "East Timor";
        $laender['de']['TP'] = "Osttimor";
        $laender['en']['EC'] = "Ecuador";
        $laender['de']['EC'] = "Ecuador";
        $laender['en']['EG'] = "Egypt";
        $laender['de']['EG'] = "Ägypten";
        $laender['en']['SV'] = "El salvador";
        $laender['de']['SV'] = "El Salvador";
        $laender['en']['GQ'] = "Equatorial Guinea";
        $laender['de']['GQ'] = "Äquatorial Guinea";
        $laender['en']['ER'] = "Eritrea";
        $laender['de']['ER'] = "Eritrea";
        $laender['en']['EE'] = "Estonia";
        $laender['de']['EE'] = "Estland";
        $laender['en']['ET'] = "Ethiopia";
        $laender['de']['ET'] = "Äthiopien";
        $laender['en']['FK'] = "Falkland Islands";
        $laender['de']['FK'] = "Falkland Inseln";
        $laender['en']['FO'] = "Faroe Islands";
        $laender['de']['FO'] = "Faroe Inseln";
        $laender['en']['FJ'] = "Fiji";
        $laender['de']['FJ'] = "Fiji";
        $laender['en']['FI'] = "Finland";
        $laender['de']['FI'] = "Finland";
        $laender['en']['FR'] = "France";
        $laender['de']['FR'] = "Frankreich";
        $laender['en']['GF'] = "French Guiana";
        $laender['de']['GF'] = "Französisch Guiana";
        $laender['en']['PF'] = "French Polynesia";
        $laender['de']['PF'] = "Französisch Polynesien";
        $laender['en']['GA'] = "Gabon";
        $laender['de']['GA'] = "Gabon";
        $laender['en']['GM'] = "Gambia";
        $laender['de']['GM'] = "Gambia";
        $laender['en']['GE'] = "Georgia";
        $laender['de']['GE'] = "Georgien";
        $laender['en']['DE'] = "Germany";
        $laender['de']['DE'] = "Deutschland";
        $laender['en']['GH'] = "Ghana";
        $laender['de']['GH'] = "Ghana";
        $laender['en']['GI'] = "Gibraltar";
        $laender['de']['GI'] = "Gibraltar";
        $laender['en']['GR'] = "Greece";
        $laender['de']['GR'] = "Griechenland";
        $laender['en']['GL'] = "Greenland";
        $laender['de']['GL'] = "Grönland";
        $laender['en']['GD'] = "Grenada";
        $laender['de']['GD'] = "Grenada";
        $laender['en']['GP'] = "Guadeloupe";
        $laender['de']['GP'] = "Guadeloupe";
        $laender['en']['GU'] = "Guam";
        $laender['de']['GU'] = "Guam";
        $laender['en']['GT'] = "Guatemala";
        $laender['de']['GT'] = "Guatemala";
        $laender['en']['GN'] = "Guinea";
        $laender['de']['GN'] = "Guinea";
        $laender['en']['GY'] = "Guyana";
        $laender['de']['GY'] = "Guyana";
        $laender['en']['HT'] = "Haiti";
        $laender['de']['HT'] = "Haiti";
        $laender['en']['VA'] = "Vatican";
        $laender['de']['VA'] = "Vatikan";
        $laender['en']['HN'] = "Honduras";
        $laender['de']['HN'] = "Honduras";
        $laender['en']['HU'] = "Hungary";
        $laender['de']['HU'] = "Ungarn";
        $laender['en']['IS'] = "Iceland";
        $laender['de']['IS'] = "Island";
        $laender['en']['IN'] = "India";
        $laender['de']['IN'] = "Indien";
        $laender['en']['ID'] = "Indonesia";
        $laender['de']['ID'] = "Indonesien";
        $laender['en']['IR'] = "Iran";
        $laender['de']['IR'] = "Iran";
        $laender['en']['IQ'] = "Iraq";
        $laender['de']['IQ'] = "Irak";
        $laender['en']['IE'] = "Ireland";
        $laender['de']['IE'] = "Irland";
        $laender['en']['IL'] = "Israel";
        $laender['de']['IL'] = "Israel";
        $laender['en']['IT'] = "Italy";
        $laender['de']['IT'] = "Italien";
        $laender['en']['JM'] = "Jamaica";
        $laender['de']['JM'] = "Jamaika";
        $laender['en']['JP'] = "Japan";
        $laender['de']['JP'] = "Japan";
        $laender['en']['JO'] = "Jordan";
        $laender['de']['JO'] = "Jordanien";
        $laender['en']['KZ'] = "Kazakstan";
        $laender['de']['KZ'] = "Kasachstan";
        $laender['en']['KE'] = "Kenya";
        $laender['de']['KE'] = "Kenia";
        $laender['en']['KI'] = "Kiribati";
        $laender['de']['KI'] = "Kiribati";
        $laender['en']['KW'] = "Kuwait";
        $laender['de']['KW'] = "Kuwait";
        $laender['en']['KG'] = "Kyrgystan";
        $laender['de']['KG'] = "Kirgistan";
        $laender['en']['LA'] = "Lao";
        $laender['de']['LA'] = "Laos";
        $laender['en']['LV'] = "Latvia";
        $laender['de']['LV'] = "Lettland";
        $laender['en']['LB'] = "Lebanon";
        $laender['de']['LB'] = "Libanon";
        $laender['en']['LS'] = "Lesotho";
        $laender['de']['LS'] = "Lesotho";
        $laender['en']['LI'] = "Liechtenstein";
        $laender['de']['LI'] = "Liechtenstein";
        $laender['en']['LT'] = "Lithuania";
        $laender['de']['LT'] = "Litauen";
        $laender['en']['LU'] = "Luxembourg";
        $laender['de']['LU'] = "Luxemburg";
        $laender['en']['MO'] = "Macau";
        $laender['de']['MO'] = "Macau";
        $laender['en']['MK'] = "Macedonia ";
        $laender['de']['MK'] = "Mazedonien";
        $laender['en']['MG'] = "Madagascar";
        $laender['de']['MG'] = "Madagaskar";
        $laender['en']['MW'] = "Malawi";
        $laender['de']['MW'] = "Malawi";
        $laender['en']['MY'] = "Malaysia";
        $laender['de']['MY'] = "Malaysia";
        $laender['en']['MV'] = "Maldives";
        $laender['de']['MV'] = "Malediven";
        $laender['en']['ML'] = "Mali";
        $laender['de']['ML'] = "Mali";
        $laender['en']['MT'] = "Malta";
        $laender['de']['MT'] = "Malta";
        $laender['en']['MR'] = "Mauritania";
        $laender['de']['MR'] = "Mauretanien";
        $laender['en']['MU'] = "Mauritius";
        $laender['de']['MU'] = "Mauritius";
        $laender['en']['YT'] = "Mayotte";
        $laender['de']['YT'] = "Mayotte";
        $laender['en']['MX'] = "Mexico";
        $laender['de']['MX'] = "Mexiko";
        $laender['en']['FM'] = "Micronesia";
        $laender['de']['FM'] = "Mikronesien";
        $laender['en']['MD'] = "Moldova";
        $laender['de']['MD'] = "Moldavien";
        $laender['en']['MC'] = "Monaco";
        $laender['de']['MC'] = "Monaco";
        $laender['en']['MN'] = "Mongolia";
        $laender['de']['MN'] = "Mongolei";
        $laender['en']['MS'] = "Montserrat";
        $laender['de']['MS'] = "Montserrat";
        $laender['en']['MA'] = "Morocco";
        $laender['de']['MA'] = "Marokko";
        $laender['en']['MZ'] = "Mozambique";
        $laender['de']['MZ'] = "Mosambik";
        $laender['en']['MM'] = "Myanmar";
        $laender['de']['MM'] = "Myanmar";
        $laender['en']['NA'] = "Namibia";
        $laender['de']['NA'] = "Namibia";
        $laender['en']['NR'] = "Nauru";
        $laender['de']['NR'] = "Nauru";
        $laender['en']['NP'] = "Nepal";
        $laender['de']['NP'] = "Nepal";
        $laender['en']['NL'] = "Netherlands";
        $laender['de']['NL'] = "Niederlande";
        $laender['en']['NZ'] = "New Zealand";
        $laender['de']['NZ'] = "Neuseeland";
        $laender['en']['NI'] = "Nicaragua";
        $laender['de']['NI'] = "Nicaragua";
        $laender['en']['NE'] = "Niger";
        $laender['de']['NE'] = "Niger";
        $laender['en']['NG'] = "Nigeria";
        $laender['de']['NG'] = "Nigeria";
        $laender['en']['NU'] = "Niue";
        $laender['de']['NU'] = "Niue";
        $laender['en']['NF'] = "Norfolk Island";
        $laender['de']['NF'] = "Norfolk Inseln";
        $laender['en']['KP'] = "North Korea";
        $laender['de']['KP'] = "Nord Korea";
        $laender['en']['NO'] = "Norway";
        $laender['de']['NO'] = "Norwegen";
        $laender['en']['OM'] = "Oman";
        $laender['de']['OM'] = "Oman";
        $laender['en']['PK'] = "Pakistan";
        $laender['de']['PK'] = "Pakistan";
        $laender['en']['PW'] = "Palau";
        $laender['de']['PW'] = "Palau";
        $laender['en']['PA'] = "Panama";
        $laender['de']['PA'] = "Panama";
        $laender['en']['PG'] = "Papua New Guinea";
        $laender['de']['PG'] = "Papua Neu Guinea";
        $laender['en']['PY'] = "Paraguay";
        $laender['de']['PY'] = "Paraguay";
        $laender['en']['PE'] = "Peru";
        $laender['de']['PE'] = "Peru";
        $laender['en']['PH'] = "Philippines";
        $laender['de']['PH'] = "Philippinen";
        $laender['en']['PL'] = "Poland";
        $laender['de']['PL'] = "Polen";
        $laender['en']['PT'] = "Portugal";
        $laender['de']['PT'] = "Portugal";
        $laender['en']['PR'] = "Puerto Rico";
        $laender['de']['PR'] = "Puerto Rico";
        $laender['en']['RO'] = "Romania";
        $laender['de']['RO'] = "Rumänien";
        $laender['en']['RU'] = "Russia";
        $laender['de']['RU'] = "Russland";
        $laender['en']['RW'] = "Rwanda";
        $laender['de']['RW'] = "Ruanda";
        $laender['en']['WS'] = "Samoa";
        $laender['de']['WS'] = "Samoa";
        $laender['en']['SM'] = "San Marino";
        $laender['de']['SM'] = "San Marino";
        $laender['en']['SA'] = "Saudi Arabia";
        $laender['de']['SA'] = "Saudi-Arabien";
        $laender['en']['SN'] = "Senegal";
        $laender['de']['SN'] = "Senegal";
        $laender['en']['SC'] = "Seychelles";
        $laender['de']['SC'] = "Seychellen";
        $laender['en']['SL'] = "Sierra Leone";
        $laender['de']['SL'] = "Sierra Leone";
        $laender['en']['SG'] = "Singapore";
        $laender['de']['SG'] = "Singapur";
        $laender['en']['SK'] = "Slovakia";
        $laender['de']['SK'] = "Slovakei";
        $laender['en']['SB'] = "Solomon Islands";
        $laender['de']['SB'] = "Solomon Inseln";
        $laender['en']['SO'] = "Somalia";
        $laender['de']['SO'] = "Somalia";
        $laender['en']['ZA'] = "South Africa";
        $laender['de']['ZA'] = "Südafrika";
        $laender['en']['KR'] = "South Korea";
        $laender['de']['KR'] = "Südkorea";
        $laender['en']['ES'] = "Spain";
        $laender['de']['ES'] = "Spanien";
        $laender['en']['LK'] = "Sri Lanka";
        $laender['de']['LK'] = "Sri Lanka";
        $laender['en']['SD'] = "Sudan";
        $laender['de']['SD'] = "Sudan";
        $laender['en']['SR'] = "Suriname";
        $laender['de']['SR'] = "Suriname";
        $laender['en']['SZ'] = "Swaziland";
        $laender['de']['SZ'] = "Swasiland";
        $laender['en']['SE'] = "Sweden";
        $laender['de']['SE'] = "Schweden";
        $laender['en']['CH'] = "Switzerland";
        $laender['de']['CH'] = "Schweiz";
        $laender['en']['SY'] = "Syria";
        $laender['de']['SY'] = "Syrien";
        $laender['en']['TW'] = "Taiwan";
        $laender['de']['TW'] = "Taiwan";
        $laender['en']['TJ'] = "Tajikistan";
        $laender['de']['TJ'] = "Tadschikistan";
        $laender['en']['TZ'] = "Tanzania";
        $laender['de']['TZ'] = "Tansania";
        $laender['en']['TH'] = "Thailand";
        $laender['de']['TH'] = "Thailand";
        $laender['en']['TG'] = "Togo";
        $laender['de']['TG'] = "Togo";
        $laender['en']['TO'] = "Tonga";
        $laender['de']['TO'] = "Tonga";
        $laender['en']['TT'] = "Trinidad and Tobago";
        $laender['de']['TT'] = "Trinidad und Tobago";
        $laender['en']['TN'] = "Tunisia";
        $laender['de']['TN'] = "Tunesien";
        $laender['en']['TR'] = "Turkey";
        $laender['de']['TR'] = "Türkei";
        $laender['en']['TM'] = "Turkmenistan";
        $laender['de']['TM'] = "Turkmenistan";
        $laender['en']['TV'] = "Tuvalu";
        $laender['de']['TV'] = "Tuvalu";
        $laender['en']['UG'] = "Uganda";
        $laender['de']['UG'] = "Uganda";
        $laender['en']['UA'] = "Ukraine";
        $laender['de']['UA'] = "Ukraine";
        $laender['en']['AE'] = "United Arab Emirates";
        $laender['de']['AE'] = "Vereinigte Arabische Emirate";
        $laender['en']['GB'] = "United Kingdom";
        $laender['de']['GB'] = "Vereinigtes Königreich";
        $laender['en']['US'] = "United States of America";
        $laender['de']['US'] = "Vereinigte Staaten von Amerika";
        $laender['en']['UY'] = "Uruguay";
        $laender['de']['UY'] = "Uruguay";
        $laender['en']['UZ'] = "Uzbekistan";
        $laender['de']['UZ'] = "Usbekistan";
        $laender['en']['VU'] = "Vanuatu";
        $laender['de']['VU'] = "Vanuatu";
        $laender['en']['VE'] = "Venezuela";
        $laender['de']['VE'] = "Venezuela";
        $laender['en']['VN'] = "Vietnam";
        $laender['de']['VN'] = "Vietnam";
        $laender['en']['VG'] = "Virgin Islands";
        $laender['de']['VG'] = "Virgin Islands";
        $laender['en']['EH'] = "Western Sahara";
        $laender['de']['EH'] = "Westsahara";
        $laender['en']['YE'] = "Yemen";
        $laender['de']['YE'] = "Jemen";
        $laender['en']['YU'] = "Yugoslavia";
        $laender['de']['YU'] = "Jugoslavien";
        $laender['en']['ZR'] = "Zaire";
        $laender['de']['ZR'] = "Zaire";
        $laender['en']['ZM'] = "Zambia";
        $laender['de']['ZM'] = "Sambia";
        $laender['en']['ZW'] = "Zimbabwe";
        $laender['de']['ZW'] = "Simbabwe";

    }

    if ($lang && $cc) {
        return $laender[$lang][$cc];
    }

    if ($lang) {
        return $laender[$lang];
    }

    return $laender;

}

function x_CountryCodesInGerman2()
{

    static $d;

    if (isset($d)) {
        return $d;
    }

    $a = <<<EOD
AA Arabien
AD Andorra
AE United Arab Emirates
AF Afghanistan
AG Antigua and Barbuda
AI Anguilla
AL Albanien
AM Armenien
AN Netherlands Antilles
AO Angola
AQ Antarktis
AR Argentinien
AS Amerikanisch-Samoa
AT Österreich
AU Australien
AW Aruba
AZ Azerbaijan
BA Bosnien/Herzegowina
BB Barbados
BD Bangladesch
BE Belgien
BF Burkina Faso
BG Bulgarien
BH Bahrain
BI Burundi
BJ Benin
BM Bermuda
BN Brunei Darussalam
BO Bolivien
BR Brasilien
BS Bahamas
BT Bhutan
BU Burma
BV Bouvetinsel
BW Botswana
BY Belarus
BZ Belize
CA Kanada
CC Cocos (Keeling) Islands
CF Central African Republic
CG Kongo
CH Schweiz
CI Elfenbeinküste
CK Cook Islands
CL Chile
CM Cameroon, United Republic of
CN China
CO Kolumbien
CR Costa Rica
CU Kuba
CV Cape Verde
CX Christmas Island
CY Zypern
CZ Tschechische Republik
DE Deutschland
DJ Dschibuti
DK Dänemark
DM Dominica
DO Dominikanische Republik
DZ Algerien
EC Ecuador
EE Estland
EG Ägypten
EH Western Sahara
ES Spanien
ET Äthiopien
FI Finnland
FJ Fiji
FK Falkland Islands (Malvinas)
FM Micronesia
FO Faroe Islands
FR Frankreich
GA Gabun
GB Vereinigtes Königreich
GD Grenada
GE Georgien
GF Französisch-Guyana
GH Ghana
GI Gibraltar
GL Grönland
GM Gambia
GN Guinea
GP Guadeloupe
GQ Equatorial Guinea
GR Griechenland
GT Guatemala
GU Guam
GW Guinea-Bissau
GY Guyana
HK Hong Kong
HM Heard and MacDonald Islands
HN Honduras
HR Kroatien
HT Haiti
HU Ungarn
ID Indonesien
IE Irland
IL Israel
IN Indien
IO British Indian Ocean Territor
IQ Irak
IR Iran (Islamic Republic of)
IS Island
IT Italien
JM Jamaica
JO Jordanien
JP Japan
KE Kenia
KG Kyrgystan
KH Cambodia
KI Kiribati
KK Kazakhstan
KM Comoros
KN Saint Kitts and Nevis
KP Korea, Democratic People s Re
KR Korea, Republic of
KW Kuwait
KY Cayman Islands
LA Lao People s Democratic Repub
LB Lebanon
LC Saint Lucia
LI Liechtenstein
LK Sri Lanka
LR Liberia
LS Lesotho
LT Litauen
LU Luxemburg
LV Lettland
LY Libyan Arab Jamahiriya
MA Marokko
MC Monaco
MD Moldawien, Republik
ME Montenegro
MG Madagascar
MH Marshall Islands
MK Makedonien
ML Mali
MM Myanmar
MN Mongolei
MO Macau
MP Northern Mariana Islands
MQ Martinique
MR Mauritania
MS Montserrat
MT Malta
MU Mauritius
MV Maldives
MW Malawi
MX Mexico
MY Malaysia
MZ Mozambique
NA Namibia
NC New Caledonia
NE Niger
NF Norfolk Island
NG Nigeria
NI Nicaragua
NL Niederlande
NO Norwegen
NP Nepal
NR Nauru
NT Neutral Zone
NU Niue
NZ Neuseeland
OM Oman
PA Panama
PE Peru
PF Französisch-Polynesien
PG Papua New Guinea
PH Philippines
PK Pakistan
PL Polen
PM Saint Pierre and Miquelon
PN Pitcairn
PR Puerto Rico
PT Portugal
PW Palau
PY Paraguay
QA Qatar
RE Reunion
RO Rumänien
RU Rußland
RW Rwanda
SA Saudi Arabia
SB Solomon Islands
SC Seychelles
SD Sudan
SE Schweden
SG Singapore
SH Saint Helena
SI Slowenien
SJ Svalbard and Jan Mayen Island
SK Slovakei
SL Sierra Leone
SM San Marino
SN Senegal
SO Somalia
SQ Serbien
SR Surinam
ST Sao Tome and Principe
SV El Salvador
SY Syrian Arab Republic
SZ Swaziland
TC Turks and Caicos Islands
TD Chad
TF French Southern Territories
TG Togo
TH Thailand
TJ Tajikistan
TK Tokelau
TM Turkemistan
TN Tunesien
TO Tonga
TP East Timor
TR Türkei
TT Trinidad and Tobago
TV Tuvalu
TW Taiwan, Province of China
TZ Tanzania, United Republic of
UA Ukraine
UG Uganda
UM United States Minor Outlying
US Vereinigte Staaten von Amerika
UY Uruguay
UZ Uzbekistan
VA Vatikanstadt
VC Saint Vincent and the Grenadines
VE Venezuela
VG Virgin Islands (British)
VI Virgin Islands (U.S.)
VN Vietnam
VU Vanuatu
WF Wallis and Futuna Islands
WS Western Samoa
YE Yemen, Republic of
YU Jugoslawien
ZA Südafrika
ZM Zambia
ZR Zaire
ZW Zimbabwe
EOD;

    $b = explode("\n", $a);

    foreach ($b as $c) {
        list ($cc, $label) = explode(" ", $c, 2);
        $d[$cc] = $label;
    }

    return $d;

}

function x_replaceLtGtParentheses($str)
{
    $str = str_replace('<', '[', $str);
    $str = str_replace('>', ']', $str);
    return $str;
}

function x_httpGet($url, $auth = '', $headers = array())
{

    $s = curl_init();

    curl_setopt($s, CURLOPT_HTTPGET, true);
//    curl_setopt($s, CURLOPT_URL, "http://encyclopedia.1914-1918-online.net/");
    curl_setopt($s, CURLOPT_URL, $url);

//    curl_setopt($s, CURLOPT_HEADER, true);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($s, CURLINFO_HEADER_OUT, false);
    curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);

    if (!empty($headers)) {
        curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
    } else {
        curl_setopt($s, CURLOPT_HTTPHEADER, array('Expect:'));
    }

    curl_setopt($s, CURLOPT_VERBOSE, 1);
    curl_setopt($s, CURLOPT_TIMEOUT, 120);
    curl_setopt($s, CURLOPT_MAXREDIRS, 3);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

//    $verbose = fopen('/tmp/curl-stderr.log', 'w+');
//    curl_setopt($s, CURLOPT_STDERR, $verbose);

    if (!empty($auth)) {
        curl_setopt($s, CURLOPT_USERPWD, $auth);
        curl_setopt($s, CURLOPT_FORBID_REUSE, true);
    }

//    $response = curl_exec($s);
    $response = curl_exec($s);

//    error_log($response);

//    echo $response;

//    exit(1);

    curl_close($s);

    return $response;

}

function x_getRawGndRdfXML($resource, $cacheDir = false)
{

    $cacheFileName = "raw.$resource.xml";

    if ($cacheDir) {
        $cacheFilePath = $cacheDir . "/" . $cacheFileName;
        if (file_exists($cacheFilePath)) {
            return file_get_contents($cacheFilePath);
        }
    }

    $url = $resource;

    if (stripos($url, "http:") === false && stripos($url, "https:") === false) {
        $url = "http://d-nb.info/gnd/$resource/about/rdf";
    }

    if (stripos($url, "/about/rdf") === false) {
        $url = "$url/about/rdf";
    }

    $xmldata = file_get_contents($url);

    if (empty($xmldata)) {
        throw new Exception("response of ($url) is empty");
    }

    if ($cacheDir) {
        $cacheFilePath = $cacheDir . "/" . $cacheFileName;
        file_put_contents($cacheFilePath, $xmldata);
    }


    return $xmldata;

}

function x_delete_from_array($value, $array)
{
    foreach ($array as $pos => $val) {
        if ($value == $val) {
            unset($array[$pos]);
        }
    }
    return $array;
}

function x_get_remote_addr()
{
    return $_SERVER['REMOTE_ADDR'];
}

function x_copy_value_to_key($list)
{
    $new = [];
    foreach ($list as $x) {
        $new[$x] = $x;
    }
    return $new;
}

function x_count_array($list)
{

    if (empty($list)) {
        return 0;
    }

    if (!is_array($list)) {
        return 0;
    }

    return count($list);

}

function x_array_remove($list, $value)
{

    if (empty($list)) {
        return $list;
    }

    if (!is_array($list)) {
        return $list;
    }

    foreach ($list as $k => $v) {
        if ($v == $value) {
            unset($list[$k]);
        }
    }

    return $list;

}

function x_hasKey($key, $array)
{
    if (!is_array($array)) {
        return false;
    }
    return array_key_exists($key, $array);
}

function x_isValTrue($key, $array, $strong = false)
{
    if (!is_array($array)) {
        return false;
    }

    if (!array_key_exists($key, $array)) {
        return false;
    }

    $val = $array[$key];

    if ($strong) {
        return $val === true;
    } else {
        return !empty($value);
    }

}

function x_file_get_contents_over_http($url, $opts = [])
{
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
        'http' => array(
            'timeout' => 1200
        )
    );

    $arrContextOptions = array_merge($arrContextOptions, $opts);

    return file_get_contents($url, false, stream_context_create($arrContextOptions));

}

function x_in_array($value, &$array, $property = false)
{
    return x_search_in_array($value, $array, $property) !== false;
}

function x_search_in_array($value, &$array, $property = false)
{
    if (!$property) {
        return array_search($value, $array);
    }

    if (!is_array($array)) {
        return false;
    }

    foreach ($array as $key => $values) {
        if (array_key_exists($property, $values)) {
            if ($values[$property] == $value) {
                return $key;
            }
        }
    }

    return false;

}

function x_search_for_value_in_array($value, &$array, $property = false)
{
    $key = x_search_in_array($value,$array,$property);
    if ($key !== false) {
        return $array[$key];
    }
    return $key;
}

function x_search_for_object_in_array_by_property($value, &$array, $property)
{
    if (!$property) {
        return false;
    }

    foreach ($array as $key => $object) {
        if (!is_object($object)) {
            continue;
        }

        if (isset($object->{$property})) {
            if ($object->{$property} == $value) {
                return $object;
            }
        }
    }

    return false;

}

function x_multi_in_array($values, $array, $property = false)
{
    foreach ($values as $value) {
        if (in_array($value, $array)) {
            return true;
        }
    }
    return false;
}


function x_lookup_entry_in_array(&$array, $key, $error = true, $default = false)
{

    if (array_key_exists($key, $array)) {
        return $array[$key];
    }

    if ($error) {
        throw new Exception("$error $key");
    } else {
        return $default;
    }

}

function x_foreach_array($array, $callable, $payload = null)
{
    if (!is_array($array)) {
        return false;
    }

    $pos = 0;
    foreach ($array as $item) {
        call_user_func($callable, $item, $pos, $payload);
        $pos++;
    }

    return true;

}

function x_safe_json_encode($data, $options = 0, $depth = 512)
{
    return base64_encode(json_encode($data, $options));
}

function x_safe_json_decode($str, $assoc = false)
{
    return json_decode(base64_decode($str), $assoc);
}

function x_array_empty($array)
{
    if (!is_array($array)) {
        throw new Exception("is not an array");
    }
    return count($array) == 0;

}

function x_array_not_empty($array)
{
    if (!is_array($array)) {
        return false;
//        throw new Exception("is not an array");
    }
    return count($array) > 0;

}

function x_combine_strings($a1 = null, $a2 = null, $a3 = null, $a4 = null)
{
    $a = [];
    if (!empty($a1)) {
        $a[] = $a1;
    }
    if (!empty($a2)) {
        $a[] = $a2;
    }
    if (!empty($a3)) {
        $a[] = $a3;
    }
    if (!empty($a4)) {
        $a[] = $a4;
    }
    return implode(" ", $a);
}

/**
 * @param $list
 * @param $array
 * @param bool $concat
 * @param string $separator
 * @param callable $callback
 * @return array|string
 */
function x_match_by_key($list, $array, $concat = false, $separator = ", ", $callback = null)
{
    if (empty($list)) {
        return [];
    }
    if (empty($array)) {
        return [];
    }

    if (!is_array($list)) {
        if (is_object($list)) {
            throw new Exception("x_match_by_key: object given");
        }
        $list = [$list];
    }

    $res = [];
    foreach ($list as $value) {
        if ($callback) {
            $res[] = call_user_func($callback, ($array[$value]));
        } else {
            $res[] = $array[$value];
        }
    }

    if ($concat) {
        return implode($separator, $res);
    }

    return $res;

}

function x_return_first_non_empty($array)
{
    if (!is_array($array)) {
        return false;
    }
    foreach ($array as $k => $v) {
        if (!empty($v)) {
            return $v;
        }
    }
    return false;
}

function x_str_duplicate_match($str)
{
    return strtolower(preg_replace('@[^a-zA-Z]@', '', $str));
}

function x_wp_time($time = 0, $gmt = 0, $type = 'mysql')
{

    if (!$time) {
        $time = time();
    }

    switch ($type) {
        case 'mysql':
            return ($gmt) ? gmdate('Y-m-d H:i:s') : gmdate('Y-m-d H:i:s', ($time + (get_option('gmt_offset') * HOUR_IN_SECONDS)));
        case 'timestamp':
            return ($gmt) ? $time : $time + (get_option('gmt_offset') * HOUR_IN_SECONDS);
        default:
            return ($gmt) ? date($type) : date($type, $time + (get_option('gmt_offset') * HOUR_IN_SECONDS));
    }
}


function x_error($str)
{
    fwrite(STDERR, $str);
    fwrite(STDERR, PHP_EOL);
}

function x_list_of_property_values($array, $property, $function = null)
{

    if (empty($array)) {
        return $array;
    }

    $list = [];

    if (is_array($array)) {

        foreach ($array as $x) {

            if (is_array($x)) {
                $val = $x[$property];
            } else if (is_object($x)) {
                $val = $x->{$property};
            }

            if ($function) {
                $val = call_user_func($function, $val);
            }

            $list[] = $val;

        }

    }

    return $list;

}

function x_concat_object_property($list, $property, $glue = ", ", $last_glue = '')
{

    if (empty($list)) {
        return '';
    }

    $res = [];

    foreach ($list as $li) {
        if (is_string($property)) {
            if (is_object($li)) {
                $res[] = $li->{$property};
            } else if (is_array($li)) {
                $res[] = $li[$property];
            }
        } else if (is_callable($property)) {
            $ret = $property($li);
            if ($ret !== false) {
                $res[] = $ret;
            }
        }
    }

    $len = count($res);

    if ($len == 0) {
        return "";
    }

    if ($len == 1) {
        return reset($res);
    }

    if (!empty($last_glue)) {
        $last = array_pop($res);
        return implode($glue, $res) . $last_glue . $last;
    }

    return implode($glue, $res);

}

function x_implode($glue, $array, $last_glue = null)
{

    if (!is_array($array)) {
        return "";
    }

    $len = count($array);

    if ($len == 1) {
        return reset($array);
    }

    if (!empty($last_glue)) {
        $last = array_pop($array);
        return implode($glue, $array) . $last_glue . $last;
    }

    return implode($glue, $array);

}

function x_not_empty($x)
{
    return !empty($x);
}

function x_error_log($str)
{
    error_log($str);
    echo $str, "\n";
    ob_flush();
    flush();
}

function x_echo_plural($array, $singular, $plural)
{
    if (is_array($array)) {
        if (count($array) > 1) {
            echo $plural;
        } else {
            echo $singular;
        }
    } else if (is_numeric($array)) {
        if ($array > 1) {
            echo $plural;
        } else {
            echo $singular;
        }
    } else {
        echo $singular;
    }
}

function x_get_singular_plural($array, $singular, $plural)
{
    if (is_array($array) && count($array) > 1) {
        return $plural;
    } else if ($array > 1) {
        return $plural;
    } else {
        return $singular;
    }
}

function x_property_in_array($value, $property, $list)
{

    if (empty($list)) {
        return false;
    }

    foreach ($list as $pos => $l) {

        if (!array_key_exists($property, $l)) {
            continue;
        }

        if ($l[$property] == $value) {
            return $pos;
        }

    }

    return false;

}


function x_get_abs_filepath($path, $dir, $mandatory = true)
{

    if (empty($path)) {

        if ($mandatory) {
            throw new Exception("x_get_abs_filepath: mandatory path missing");
        }

        return $path;

    }

    if (startswith($path, DIRECTORY_SEPARATOR)) {
        return $path;
    }

    if (endswith($dir, DIRECTORY_SEPARATOR)) {
        return $dir . $path;
    } else {
        return $dir . DIRECTORY_SEPARATOR . $path;
    }

}

function x_ifnot_empty($value, $callback)
{

    if (empty($value)) {
        return;
    }

    return call_user_func($callback, $value);

}

function x_filter_array_by_property($array, $property, $value, $reverse = false, $withoutKeys=false)
{

    $res = [];

    if (is_array($array)) {
        foreach ($array as $pos => $a) {
            if (is_object($a)) {
                $val = $a->{$property};
            } else if (is_array($a) && array_key_exists($property, $a)) {
                $val = $a[$property];
            } else {
                continue;
            }

            if (!$reverse) {
                if ($val == $value) {
                    if ($withoutKeys) {
                        $res[] = $a;
                    } else {
                        $res[$pos] = $a;
                    }
                }
            } else {
                if ($val != $value) {
                    if ($withoutKeys) {
                        $res[] = $a;
                    } else {
                        $res[$pos] = $a;
                    }
                }
            }


        }
    }

    return $res;

}

function x_file_get_csv($filename,$delimiter=",")
{
    $fd = fopen($filename, 'r');
    $rows = [];

    while (($row = fgetcsv($fd,0,$delimiter)) != false) {
        $rows[] = $row;
    }
    return $rows;
}

function x_simple_markdown_to_html($str)
{
    $str = preg_replace("@'''(.*?)'''@", "<b>$1</b>", $str);
    return preg_replace("@''(.*?)''@", "<i>$1</i>", $str);
}

function x_render_html($html, $payload = null)
{

    if (empty($html)) {
        return $html;
    }

    if (!is_callable($html)) {
        return $html;
    }

    ob_start();
    call_user_func($html, $payload);
    $html = ob_get_clean();

    return $html;

}

function x_getRemoteIpAddress()
{

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
//check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;

}

function x_newline_split($str)
{
    if (empty($str)) {
        return [];
    }
    return preg_split('@[\r\n]+@', $str);
}

function x_array_to_csv($values, $delimiter = ', ', $treat0 = false)
{
    $res = [];
    foreach ($values as $val) {
        if ($treat0) {
            if ($val !== 0 && empty($val)) {
                continue;
            }
        } else {
            if (empty($val)) {
                continue;
            }
        }
        $res[] = $val;
    }
    return implode($delimiter, $res);
}

function x_buildUrlWithQuery($url, $params, $key, $value, $deleteParamIfValueIsEmpty = true)
{
    if (is_array($params)) {
        if ($value) {
            $params[$key] = $value;
        } else if ($deleteParamIfValueIsEmpty) {
            unset($params[$key]);
        }
        return $url . '?' . http_build_query($params);
    } else {
        return $url;
    }
}

function x_copy_properties($names,$src,&$dst)
{
    $isArraySrc = is_array($src);
    $isArrayDst = is_array($dst);
    foreach ($names as $name)
    {
        $val = $isArraySrc ? $src[$name] : $src->{$name};
        if (!isset($val) || (empty($val) && $val !== 0)) {
            continue;
        }
//        echo $name, "\n";
//        echo $val, "\n";
//        echo gettype($val), "\n";
        if ($isArrayDst) {
            $dst[$name] = $val;
        } else {
            $dst->{$name} = $val;
        }
    }
}