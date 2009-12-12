<?php

mb_language('ja');
mb_internal_encoding('UTF-8');

if (defined('E_DEPRECATED')) {
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL);
}

set_include_path(realpath(dirname(__FILE__) . '/../src') . PATH_SEPARATOR .
                 realpath(dirname(__FILE__) . '/../vendor/Mail-1.1.14') . PATH_SEPARATOR .
                 realpath(dirname(__FILE__) . '/../vendor/Mail_Mime-1.5.2') . PATH_SEPARATOR .
                 realpath(dirname(__FILE__) . '/../vendor/Mail_mimeDecode-1.5.1') . PATH_SEPARATOR .
                 realpath(dirname(__FILE__) . '/../vendor/Text_Pictogram_Mobile-0.0.2') . PATH_SEPARATOR .
                 realpath(dirname(__FILE__) . '/../vendor/Mail_Address_MobileJp') . PATH_SEPARATOR .
                 realpath(dirname(__FILE__) . '/../vendor/qdmail.1.2.6b') . PATH_SEPARATOR .
                 get_include_path());

require_once 'PHPUnit/Framework.php';
