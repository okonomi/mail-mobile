<?php

require_once 'Mail/Mobile/Mime/MimeAbstract.php';


class Mail_Mobile_Mime_Ezweb extends Mail_Mobile_Mime_MimeAbstract
{
    function __construct($crlf = "\r\n")
    {
        parent::__construct($crlf);

        $this->_build_params = array_merge(
            $this->_build_params,
            array(
                'head_encoding' => 'base64',
                'text_encoding' => '7bit',
                'html_encoding' => 'quoted-printable',
                '7bit_wrap'     => 998,
                'html_charset'  => 'ISO-2022-JP',
                'text_charset'  => 'ISO-2022-JP',
                'head_charset'  => 'ISO-2022-JP',
                'ignore-iconv'  => true,
            )
        );
    }

    protected function _generateContentsID()
    {
        return md5(uniqid(time())).'@';
    }

    protected function _build($null, $attachments, $html_images, $html, $text)
    {
        switch (true) {
            // デコメール本文とインライン画像
        case $html AND !$attachments AND $html_images:
            /**
             * multipart/mixed
             * |- multipart/alternative
             * |  |- text/plain
             * |  \- text/html
             * \- image/**
             */
            $message =& $this->_addMixedPart($null);

            $alt =& $this->_addAlternativePart($message);
            $this->_addTextPart($alt, isset($this->_txtbody) ? $this->_txtbody : '');
            $this->_addHtmlPart($alt);

            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($message, $this->_html_images[$i]);
            }
            break;

        default:
            $message = parent::_build($null, $attachments, $html_images, $html, $text);
            break;
        }

        return $message;
    }
}
