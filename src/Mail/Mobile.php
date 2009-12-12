<?php

require_once 'Mail.php';
require_once 'Mail/mime.php';
require_once 'Text/Pictogram/Mobile.php';


class Mail_Mobile
{
    private static $_type = array(
        'docomo' => array(
            'emoji_type'    => 'sjis',
            'charset'       => 'Shift-JIS',
            'mime_charset'  => 'Shift-JIS',
        ),
        'softbank' => array(
            'emoji_type'    => 'utf-8',
            'charset'       => 'UTF-8',
            'mime_charset'  => 'UTF-8',
         ),
        'ezweb' => array(
            'emoji_type'    => 'jis-email',
            'charset'       => 'jis',
            'mime_charset'  => 'ISO-2022-JP',
         ),
    );

    private $_carrier;

    private $_picto;

    private $_mime;

    private $_encoding;


    function __construct($carrier, $encoding = null)
    {
        $this->_carrier = $carrier;


        $this->_mime = self::loadMime($carrier);

        $type = self::$_type[$this->_carrier];
        $this->_picto = Text_Pictogram_Mobile::factory($this->_carrier, $type['emoji_type']);

        if (is_null($encoding)) {
            $this->_encoding = mb_internal_encoding();
        } else {
            $this->_encoding = $encoding;
        }
    }

    public static function loadMime($carrier)
    {
        $class = 'Mail_Mobile_Mime_'.ucfirst(strtolower($carrier));
        if (!class_exists($class)) {
            $file = str_replace('_', '/', $class).'.php';
            require_once $file;
        }

        return new $class("\r\n");
    }

    public function setHeader($header)
    {
        $charset      = self::$_type[$this->_carrier]['charset'];
        $mime_charset = self::$_type[$this->_carrier]['mime_charset'];


        if (isset($header['Subject'])) {
            $subject = $header['Subject'];
            $subject = $this->_restoreString($subject);
            $subject = "=?".$mime_charset."?B?" . base64_encode($subject) . "?=";
            $header['Subject'] = $subject;
        }
        $this->_mime->headers($header);

        return $this;
    }

    public function setTXTBody($data, $isfile = false, $append = false)
    {
        if ($isfile) {
            $content = file_get_contents($data);
        } else {
            $content = $data;
        }
        $content = $this->_restoreString($content);

        $this->_mime->setTXTBody($content, false, $append);

        return $this;
    }

    public function setHTMLBody($data, $isfile = false, $append = false)
    {
        if ($isfile) {
            $content = file_get_contents($data);
        } else {
            $content = $data;
        }
        $content = $this->_restoreString($content);

        $this->_mime->setHTMLBody($content, false, $append);

        return $this;
    }

    public function addHTMLImage($file, $c_type='application/octet-stream',
                                 $name = '', $isfile = true)
    {
        $this->_mime->addHTMLImage($file, $c_type, $name, $isfile);

        return $this;
    }

    public function get()
    {
        $body   = $this->_mime->get();
        $header = $this->_mime->headers();

        return array($header, $body);
    }

    public function getMessage()
    {
        return $this->_mime->getMessage();
    }

    private function _restoreString($content)
    {
        $content = mb_convert_encoding($content, self::$_type[$this->_carrier]['charset'], $this->_encoding);
        $content = $this->_picto->restore($content);

        return $content;
    }
}
