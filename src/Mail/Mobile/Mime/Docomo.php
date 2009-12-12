<?php

require_once 'Mail/Mobile/Mime/MimeAbstract.php';


class Mail_Mobile_Mime_Docomo extends Mail_Mobile_Mime_MimeAbstract
{
    function __construct($crlf = "\r\n")
    {
        parent::__construct($crlf);

        $this->_build_params = array_merge(
            $this->_build_params,
            array(
                'head_encoding' => 'base64',
                'text_encoding' => 'base64',
                'html_encoding' => 'quoted-printable',
                '7bit_wrap'     => 998,
                'html_charset'  => 'Shift-JIS',
                'text_charset'  => 'Shift-JIS',
                'head_charset'  => 'Shift-JIS',
                'ignore-iconv'  => true,
            )
        );
    }

    protected function &_build($null, $attachments, $html_images, $html, $text)
    {
        switch (true) {
            // デコメール本文とインライン画像
        case $html AND !$attachments AND $html_images:
            /**
             * multipart/mixed
             * \- multipart/related
             *    |- multipart/alternative
             *    |  |- text/plain
             *    |  \- text/html
             *    \--image/xxx (*n)
             */
            $message =& $this->_addMixedPart();

            $rel =& $this->_addRelatedPart($message);

            $alt =& $this->_addAlternativePart($rel);
            $this->_addTextPart($alt, isset($this->_txtbody) ? $this->_txtbody : '');
            $this->_addHtmlPart($alt);

            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($rel, $this->_html_images[$i]);
            }
            break;

        default:
            $message = parent::_build($null, $attachments, $html_images, $html, $text);
        }

        return $message;
    }

    function &_addHtmlImagePart(&$obj, $value)
    {
        $params = array(
            'content_type' => $value['c_type'],
            'encoding'     => 'base64',
            'dfilename'    => $value['name'],
            'cid'          => $value['cid'],
        );

        $ret = $obj->addSubpart($value['body'], $params);
        return $ret;
    }
}
