<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Renderer;
use Gideon\Renderer\Response;
use Gideon\Debug\Provider as Debug;

final class RendererTest extends TestCase 
{
    private $config;
    private $locale;
    private $renderer;

    public function setUp()
    {
        $this->config = new Config('test');
        $this->locale = new Locale($this->config);
        $this->renderer = new Renderer($this->config, $this->locale);
    }

    public function testRenderer()
    {
        $this->assertEquals($this->locale->getLanguage(), $this->renderer->LANG);

        $this->renderer->ISSET = 'yes';
        $this->assertEquals(true, isset($this->renderer->ISSET));
    }

    public function testSimpleText()
    {
        $name = "a php object Tester";
        $country = "Testlandia";
        $status = $this->config->get('TEST_RENDERER_TEXT_CONFIG');
        $lang = $this->locale->getLanguage();

        // Input for Response\Text
        $text = <<<EOT
Hi, I'm {{PARAM_NAME}} and I live in {{PARAM_COUNTRY}}.
I use PHP7 and I'm proud of it. It can produce such a beatiful output,
especially when code is written properly.

Status: {{CONFIG_TEST_RENDERER_TEXT_CONFIG}}
Language: {{DOCUMENT_LANG}}
EOT;

        // Expected output
        $alredy_parsed = <<<EOT
Hi, I'm {$name} and I live in {$country}.
I use PHP7 and I'm proud of it. It can produce such a beatiful output,
especially when code is written properly.

Status: {$status}
Language: {$lang}
EOT;

        // Build tested object
        $response = new Response\Text($text);
        $response->bindParam('NAME', $name);
        $this->renderer->init($response);
        $response->bindParam('COUNTRY', $country);

        $this->assertEquals($name, $response->params->NAME);
        $this->assertEquals($country, $response->params->COUNTRY);
        $this->assertEquals($this->config->get('RESPONSE_CODE_DEFAULT'), $response->code);
        $this->assertEquals($this->config->get('RESPONSE_TYPE_DEFAULT'), $response->type);

        ob_start();
        $this->renderer->render(false);
        $output = ob_get_clean();
        $this->assertEquals($alredy_parsed, $output);

        // Test object with specified output
        $type = 'text/html';
        $response->setType('text/html');
        ob_start();
        $this->renderer->render(false);
        $output = ob_get_clean();
        $this->assertEquals("<pre>$alredy_parsed</pre>", $output);
    }



}