<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Gideon\Application\Config;
use Gideon\Application\Locale;
use Gideon\Exception\IOException;

final class LocaleTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = new Config('test', null, ['LOCALE_PATH' => realpath(__DIR__ . DIRECTORY_SEPARATOR . 'TestContainers' ) . DIRECTORY_SEPARATOR]);
    }

    public function testLoadLocale()
    {
        $locale = new Locale($this->config);
        $this->assertEquals(true, $this->config->has('LOCALE_DEFAULT'));
        $this->assertEquals($this->config->get('LOCALE_DEFAULT'), $locale->getLocale());

        $notNull = null;
        try {
            // should fail
            $locale->setLocale('te_ST');
        } catch (IOException $exception) {
            $notNull = $exception;
        }
        $this->assertNotEquals(null, $notNull);

        return $locale;
    }

    /**
     * @depends testLoadLocale
     */
    public function testImportDefaults($locale)
    {
        $config = $this->config;
        $default = new Locale($config);

        // should be same right know
        $this->assertEquals($locale->getLocale(), $default->getLocale());

        // load not default locale
        $this->assertEquals(true, $config->has('TEST_NOT_DEFAULT_LOCALE'));
        $locale->setLocale($config->get('TEST_NOT_DEFAULT_LOCALE'));
        $this->assertNotEquals($config->get('LOCALE_DEFAULT'), $locale->getLocale());

        $this->assertNotEquals($locale->getLocale(), $default->getLocale());
        $this->assertNotEquals($locale->get('TEXT_LANGUAGE'), $default->get('TEXT_LANGUAGE'));
        $this->assertEquals($locale->get('APPLICATION_NAME'), $default->get('APPLICATION_NAME'));
    }

}
