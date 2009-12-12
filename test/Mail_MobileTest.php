<?php

require_once 'Mail_Mobile_TestAbstract.php';
require_once 'Mail/Mobile.php';


class Mail_MobileTest extends Mail_Mobile_TestAbstract
{
    public function setUp()
    {
    }

    public function testInstance()
    {
        $this->assertType('Mail_Mobile', new Mail_Mobile('docomo'));
    }

    public function testGetMessageText()
    {
        $data = array(
            'docomo'   => 'example@docomo.ne.jp',
            'softbank' => 'example@softbank.ne.jp',
            'ezweb'    => 'example@ezweb.ne.jp',
        );

        foreach ($data as $carrier => $address) {
            $header = array(
                'To'      => $address,
                'From'    => 'from@example.com',
                'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
            );
            $body = str_repeat('ﾃﾞｽ[({docomo 2})]', 20);

            $mail_mobile = new Mail_Mobile($carrier);
            $ret = $mail_mobile
                    ->setHeader($header)
                    ->setTXTBody($body);

            $excepted = file_get_contents(dirname(__FILE__).'/data/text-mail-'.$carrier.'.txt');
            $actual   = $mail_mobile->getMessage();
            $this->assertEquals($excepted, $actual);
        }
    }

    public function testGetMessageHtml()
    {
        $data = array(
            'docomo'   => 'example@docomo.ne.jp',
            'softbank' => 'example@softbank.ne.jp',
            'ezweb'    => 'example@ezweb.ne.jp',
        );

        foreach ($data as $carrier => $address) {
            $header = array(
                'To'      => $address,
                'From'    => 'from@example.com',
                'Subject' => 'テストﾃﾞｽ[({docomo 1})]',
            );
            $body = str_repeat('ﾃﾞｽ[({docomo 2})]', 20);

            $mail_mobile = new Mail_Mobile($carrier);
            $ret = $mail_mobile
                    ->setHeader($header)
                    ->setHTMLBody(dirname(__FILE__).'/data/mail.html', true)
                    ->addHTMLImage(dirname(__FILE__).'/data/image.gif', 'image/gif', 'image.gif');

            $excepted = file_get_contents(dirname(__FILE__).'/data/html-mail-'.$carrier.'.txt');
            $actual   = $mail_mobile->getMessage();
            $this->assertEqualsHtmlmail($excepted, $actual);
        }
    }

    public function testEncodingSetting()
    {
        $mail_mobile = new Mail_Mobile('docomo');

        $subject = 'あいうえお';
        $header = array(
            'To'      => 'example@docomo.ne.jp',
            'From'    => 'from@example.com',
            'Subject' => $subject,
        );

        $expected = '=?Shift-JIS?B?'.base64_encode(mb_convert_encoding($subject, 'sjis', 'utf-8')).'?=';
        $mail_mobile->setHeader($header);
        $ret = $mail_mobile->get();

        $this->assertEquals($expected, $ret[0]['Subject']);



        $encoding = 'UTF-8';
        $data = array(
            'docomo'   => 'Shift-JIS',
            'softbank' => 'UTF-8',
            'ezweb'    => 'ISO-2022-JP',
        );
        foreach ($data as $carrier => $code) {
            $mail_mobile = new Mail_Mobile($carrier, $encoding);

            $subject = mb_convert_encoding('あいうえお', $encoding, 'UTF-8');
            $header = array(
                'To'      => 'to@example.com',
                'From'    => 'from@example.com',
                'Subject' => $subject,
            );

            $subject = mb_convert_encoding('あいうえお', $code, $encoding);
            $expected = '=?'.$code.'?B?'.base64_encode($subject).'?=';
            $mail_mobile->setHeader($header);
            $ret = $mail_mobile->get();

            $this->assertEquals($expected, $ret[0]['Subject']);
        }
    }
}
