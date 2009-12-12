<?php

require_once 'Mail_Mobile_TestAbstract.php';
require_once 'Mail/Mobile/Mime/Ezweb.php';


class Mail_Mobile_Mime_EzwebTest extends Mail_Mobile_TestAbstract
{
    protected $_carrier = 'ezweb';
    protected $_type = 'jis-email';


    public function setUp()
    {
        parent::setUp();
    }

    public function testInstance()
    {
        $this->assertType('Mail_Mobile_Mime_Ezweb', new Mail_Mobile_Mime_Ezweb);
    }

    public function testGetMessageText()
    {
        $header = array(
            'To'      => 'example@ezweb.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
        );
        $body = str_repeat('ﾃﾞｽ[({docomo 2})]', 20);

        $header['Subject'] = $this->_restoreString($header['Subject'], 'jis', 'utf-8');
        $header['Subject'] = '=?ISO-2022-JP?B?'.base64_encode($header['Subject']).'?=';

        $body              = $this->_restoreString($body, 'jis', 'utf-8');

        $mime = new Mail_Mobile_Mime_Ezweb;
        $mime->headers($header);
        $mime->setTXTBody($body);
        $message = $mime->getMessage();

        $actual = file_get_contents(dirname(__FILE__).'/data/text-mail-ezweb.txt');

        $this->assertEquals($actual, $message);
    }

    public function testGetMessageHtml()
    {
        $header = array(
            'To'      => 'example@ezweb.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
        );
        $html = file_get_contents(dirname(__FILE__).'/data/mail.html');

        $header['Subject'] = $this->_restoreString($header['Subject'], 'jis', 'utf-8');
        $header['Subject'] = '=?ISO-2022-JP?B?'.base64_encode($header['Subject']).'?=';

        $html              = $this->_restoreString($html, 'jis', 'utf-8');

        $mime = new Mail_Mobile_Mime_Ezweb;
        $mime->headers($header);
        $mime->setHTMLBody($html);
        $mime->addHTMLImage(dirname(__FILE__).'/data/image.gif', 'image/gif', 'image.gif');
        $message = $mime->getMessage();

        $expected = file_get_contents(dirname(__FILE__).'/data/html-mail-ezweb.txt');

        $this->assertEqualsHtmlmail($expected, $message);
    }
}
