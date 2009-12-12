<?php

require_once 'Mail_Mobile_TestAbstract.php';
require_once 'Mail/Mobile/Mime/Docomo.php';


class Mail_Mobile_Mime_DocomoTest extends Mail_Mobile_TestAbstract
{
    protected $_carrier = 'docomo';
    protected $_type = 'sjis';


    public function setUp()
    {
        parent::setUp();
    }

    public function testInstance()
    {
        $this->assertType('Mail_Mobile_Mime_Docomo', new Mail_Mobile_Mime_Docomo);
    }

    public function testGetMessageText()
    {
        $header = array(
            'To'      => 'example@docomo.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
        );
        $body = str_repeat('ﾃﾞｽ[({docomo 2})]', 20);

        $header['Subject'] = $this->_restoreString($header['Subject'], 'sjis', 'utf-8');
        $header['Subject'] = '=?Shift-JIS?B?'.base64_encode($header['Subject']).'?=';

        $body              = $this->_restoreString($body, 'sjis', 'utf-8');

        $mime = new Mail_Mobile_Mime_Docomo;
        $mime->headers($header);
        $mime->setTXTBody($body);
        $message = $mime->getMessage();

        $actual = file_get_contents(dirname(__FILE__).'/data/text-mail-docomo.txt');

        $this->assertEquals($actual, $message);
    }

    public function testGetMessageHtml()
    {
        $header = array(
            'To'      => 'example@docomo.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
        );
        $html = file_get_contents(dirname(__FILE__).'/data/mail.html');

        $header['Subject'] = $this->_restoreString($header['Subject'], 'sjis', 'utf-8');
        $html              = $this->_restoreString($html, 'sjis', 'utf-8');

        $mime = new Mail_Mobile_Mime_Docomo;
        $mime->headers($header);
        $mime->setHTMLBody($html);
        $mime->addHTMLImage(dirname(__FILE__).'/data/image.gif', 'image/gif', 'image.gif');
        $message = $mime->getMessage();

        $expected = file_get_contents(dirname(__FILE__).'/data/html-mail-docomo.txt');

        $this->assertEqualsHtmlmail($expected, $message);
    }
}
