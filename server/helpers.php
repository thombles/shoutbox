<?php

include_once 'config.php';

function autolink($str, $attributes=array()) {
    $attrs = '';
    foreach ($attributes as $attribute => $value) {
        $attrs .= " {$attribute}=\"{$value}\"";
    }

    $str = ' ' . $str;
    $str = preg_replace(
        '`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
        '$1<a href="$2"'.$attrs.'>$2</a>',
        $str
    );
    $str = substr($str, 1);
    $str = preg_replace('`href=\"www`','href="http://www',$str);
    return $str;
}

function getConfig($key) {
    global $conf;
    return $conf ? $conf[$key] : getenv($key);
}

function emote($str) {
	 if (startsWith($str, "/me ")) {
	    return "<i>" . substr($str, 3) . "</i>";
	 }
	 return $str;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}


?>