<?php

use PHPUnit\Framework\TestCase;

use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Debug\Provider as Debug;

final class LocaleTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = new Config('test');
    }

    public function testLoadLocale()
    {
        $locale = new Locale($this->config);
        $this->assertEquals($this->config->get('LOCALE_DEFAULT'), 'en_EN');
        $this->assertEquals($this->config->get('LOCALE_DEFAULT'), $locale->getLanguage());
        $locale->setLanguage('te_ST');
        $this->assertEquals('mistake', $locale->get('TEXT_ERROR'));
        return $locale;
    }

    /**
     * @depends testLoadLocale
     */
    public function testImportDefaults($locale)
    {
        $default = new Locale($this->config);
        $this->assertEquals($locale->getLanguage(), $default->getLanguage());
        $default->setLanguage($this->config->get('LOCALE_DEFAULT'));
        $this->assertNotEquals($locale->getLanguage(), $default->getLanguage());
        $this->assertEquals($locale->get('TEXT_LANGUAGE'), $default->get('TEXT_LANGUAGE'));
    } 

}