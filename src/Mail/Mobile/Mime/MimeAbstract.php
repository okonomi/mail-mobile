<?php

require_once 'Mail/mime.php';


abstract class Mail_Mobile_Mime_MimeAbstract extends Mail_mime
{
    function __construct($crlf = "\r\n")
    {
        parent::Mail_mime($crlf);
    }

    public function addHTMLImage($file, $c_type='application/octet-stream',
                                 $name = '', $isfile = true)
    {
        $ret = parent::addHTMLImage($file, $c_type, $name, $isfile);
        if (PEAR::isError($ret)) {
            return $ret;
        }

        // CID‚ðã‘‚«
        $image =& $this->_html_images[count($this->_html_images) -1];
        $image['cid'] = $this->_generateContentsID();

        return true;
    }

    protected function _generateContentsID()
    {
        return md5(uniqid(time()));
    }

    public function &get($build_params = null)
    {
        if (isset($build_params)) {
            while (list($key, $value) = each($build_params)) {
                $this->_build_params[$key] = $value;
            }
        }

        if (isset($this->_headers['From'])){
            $domain = @strstr($this->_headers['From'],'@');
            //Bug #11381: Illegal characters in domain ID
            $domain = str_replace(array("<", ">", "&", "(", ")", " ", "\"", "'"), "", $domain);
            $domain = urlencode($domain);
            foreach($this->_html_images as $i => $img){
                $this->_html_images[$i]['cid'] = $this->_html_images[$i]['cid'] . $domain;
            }
        }

        if (count($this->_html_images) AND isset($this->_htmlbody)) {
            foreach ($this->_html_images as $key => $value) {
                $regex   = array();
                $regex[] = '#(\s)((?i)src|background|href(?-i))\s*=\s*(["\']?)' .
                            preg_quote($value['name'], '#') . '\3#';
                $regex[] = '#(?i)url(?-i)\(\s*(["\']?)' .
                            preg_quote($value['name'], '#') . '\1\s*\)#';

                $rep   = array();
                $rep[] = '\1\2=\3cid:' . $value['cid'] .'\3';
                $rep[] = 'url(\1cid:' . $value['cid'] . '\2)';

                $this->_htmlbody = preg_replace($regex, $rep, $this->_htmlbody);
                $this->_html_images[$key]['name'] = 
                    basename($this->_html_images[$key]['name']);
            }
        }

        $null        = null;
        $attachments = count($this->_parts)                 ? true : false;
        $html_images = count($this->_html_images)           ? true : false;
        $html        = strlen($this->_htmlbody)             ? true : false;
        $text        = (!$html AND strlen($this->_txtbody)) ? true : false;

        $message =& $this->_build($null, $attachments, $html_images, $html, $text);

        if (isset($message)) {
            $output = $message->encode();

            $this->_headers = array_merge($this->_headers,
                                          $output['headers']);
            $body = $output['body'];
            return $body;

        } else {
            $ret = false;
            return $ret;
        }
    }

    protected function &_build($null, $attachments, $html_images, $html, $text)
    {
        switch (true) {
        case $text AND !$attachments:
            $message =& $this->_addTextPart($null, $this->_txtbody);
            break;

        case !$text AND !$html AND $attachments:
            $message =& $this->_addMixedPart();
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $text AND $attachments:
            $message =& $this->_addMixedPart();
            $this->_addTextPart($message, $this->_txtbody);
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $html AND !$attachments AND !$html_images:
            if (isset($this->_txtbody)) {
                $message =& $this->_addAlternativePart($null);
                $this->_addTextPart($message, $this->_txtbody);
                $this->_addHtmlPart($message);
            } else {
                $message =& $this->_addHtmlPart($null);
            }
            break;

        case $html AND !$attachments AND $html_images:
            $message =& $this->_addRelatedPart($null);
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $this->_addHtmlPart($alt);
            } else {
                $this->_addHtmlPart($message);
            }
            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($message, $this->_html_images[$i]);
            }
            break;

        case $html AND $attachments AND !$html_images:
            $message =& $this->_addMixedPart();
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $this->_addHtmlPart($alt);
            } else {
                $this->_addHtmlPart($message);
            }
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $html AND $attachments AND $html_images:
            $message =& $this->_addMixedPart();
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $rel =& $this->_addRelatedPart($alt);
            } else {
                $rel =& $this->_addRelatedPart($message);
            }
            $this->_addHtmlPart($rel);
            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($rel, $this->_html_images[$i]);
            }
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        }

        return $message;
    }
}
