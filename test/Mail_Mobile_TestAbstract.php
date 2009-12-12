<?php

require_once 'Text/Pictogram/Mobile.php';


abstract class Mail_Mobile_TestAbstract extends PHPUnit_Framework_TestCase
{
    protected $_carrier;
    protected $_type;


    public function setUp()
    {
        $this->picto = Text_Pictogram_Mobile::factory($this->_carrier, $this->_type);
    }

    protected function _restoreString($string, $to, $from)
    {
        return $this->picto->restore(mb_convert_encoding($string, $to, $from));
    }

    public function assertEqualsHtmlmail($expected, $actual)
    {
        // boundary と cid は毎回ランダムに設定されるので、固定値に置き換える

        if (preg_match('/boundary="(=_[a-zA-Z0-9]+)"/', $expected, $match1)) {
            $boundary1 = $match1[1];

            if (preg_match_all('/boundary="(=_[a-zA-Z0-9]+)"/', $actual, $match2)) {
                $boundary2 = $match2[1];

                foreach ($boundary2 as $value) {
                    $actual = str_replace($value, $boundary1, $actual);
                }
            }
        }

        if (preg_match('/Content\-ID: <([^>]+)>/', $expected, $match1)) {
            $cid1 = $match1[1];

            if (preg_match('/Content\-ID: <([^>]+)>/', $actual, $match2)) {
                $cid2 = $match2[1];

                $actual = str_replace($cid2, $cid1, $actual);
            }
        }


        $this->assertEquals($expected, $actual);
    }
}
