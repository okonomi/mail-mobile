<?php

require_once 'Mail_Mobile_TestAbstract.php';
require_once 'Mail/Mobile/Mime/Softbank.php';


class Mail_Mobile_Mime_SoftbankTest extends Mail_Mobile_TestAbstract
{
    protected $_carrier = 'softbank';
    protected $_type = 'utf-8';


    public function setUp()
    {
        parent::setUp();
    }

    public function testInstance()
    {
        $this->assertType('Mail_Mobile_Mime_Softbank', new Mail_Mobile_Mime_Softbank);
    }

    public function testGetMessageText()
    {
        $header = array(
            'To'      => 'example@softbank.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
        );
        $body = str_repeat('ﾃﾞｽ[({docomo 2})]', 20);

        $header['Subject'] = $this->_restoreString($header['Subject'], 'utf-8', 'utf-8');
        $header['Subject'] = '=?UTF-8?B?'.base64_encode($header['Subject']).'?=';

        $body              = $this->_restoreString($body, 'utf-8', 'utf-8');

        $mime = new Mail_Mobile_Mime_Softbank;
        $mime->headers($header);
        $mime->setTXTBody($body);
        $message = $mime->getMessage();

        $actual = file_get_contents(dirname(__FILE__).'/data/text-mail-softbank.txt');

        $this->assertEquals($actual, $message);
    }

    public function testGetMessageHtml()
    {
        $header = array(
            'To'      => 'example@softbank.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
        );
        $html = file_get_contents(dirname(__FILE__).'/data/mail.html');

        $header['Subject'] = $this->_restoreString($header['Subject'], 'utf-8', 'utf-8');
        $html              = $this->_restoreString($html, 'utf-8', 'utf-8');

        $mime = new Mail_Mobile_Mime_Softbank;
        $mime->headers($header);
        $mime->setHTMLBody($html);
        $mime->addHTMLImage(dirname(__FILE__).'/data/image.gif', 'image/gif', 'image.gif');
        $message = $mime->getMessage();

        $expected = file_get_contents(dirname(__FILE__).'/data/html-mail-softbank.txt');

        $this->assertEqualsHtmlmail($expected, $message);
    }
}
