<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Application\Config;
use Gideon\Application\Locale;
use Gideon\Renderer;
use Gideon\Http\Response;
use Gideon\Debug\Provider as Debug;

final class RendererTest extends TestCase
{
    private $config;
    private $locale;
    private $renderer;
    private $errorHandler;

    public function setUp()
    {
        $this->config = $config = new Config('test');
        $this->locale = new Locale($this->config);
        $this->renderer = new Renderer($this->config, $this->locale);
        $this->errorHandler = new ErrorHandler($config, $config->getLogger());
    }

    public function testRenderer()
    {
        $this->assertEquals($this->locale->getLocale(), $this->renderer->LANG);

        $this->renderer->ISSET = 'yes';
        $this->assertEquals(true, isset($this->renderer->ISSET));
    }

    public function testSimpleText()
    {
        $name = "a php object Tester";
        $country = "Testlandia";
        $status = $this->config->get('TEST_RENDERER_TEXT_CONFIG');
        $lang = $this->locale->getLocale();

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
        $this->renderer->attach($response);
        $response->bindParam('COUNTRY', $country);

        $this->assertEquals($name, $response->params->NAME);
        $this->assertEquals($country, $response->params->COUNTRY);
        $this->assertEquals($this->config->get('RESPONSE_CODE_DEFAULT'), $response->code);
        $this->assertEquals($this->config->get('RESPONSE_TYPE_DEFAULT'), $response->type);

        ob_start();
        $this->renderer->render($this->errorHandler, false);
        $output = ob_get_clean();
        $this->assertEquals(true, $this->errorHandler->isEmpty());
        $this->assertEquals($alredy_parsed, $output);

        // Test object with specified output
        $this->assertSame(true, $this->config->get('TEXT_HTML_RENDER_IN_PRE'));
        $type = 'text/html';
        $response->setType('text/html');
        ob_start();
        $this->renderer->render($this->errorHandler, false);
        $output = ob_get_clean();
        $this->assertEquals(true, $this->errorHandler->isEmpty());
        $this->assertEquals("<pre>$alredy_parsed</pre>", $output);
    }


    public function testJSON()
    {
        $obj['string'] = 'string';
        $obj['test'] = [0 => '1', 2 => '3'];
        $obj['nestedArray'] = [0,1,2,3,4,5,6,'object'=>['array'=>[1,2,3], 0]];

        // Build tested object
        $response = new Response\JSON($obj);
        $this->renderer->attach($response);

        $this->assertEquals($this->config->get('RESPONSE_CODE_DEFAULT'), $response->code);
        $this->assertEquals($this->config->get('JSON_TYPE_DEFAULT'), $response->type);

        // Render bare object
        ob_start();
        $this->renderer->render($this->errorHandler, false);
        $output = ob_get_clean();
        $this->assertEquals(true, $this->errorHandler->isEmpty());
        $this->assertNotEquals(true, empty($output));
        $this->assertEquals(json_encode($obj), $output);

        // override with params
        $string = 'notstring';
        $test = [0 => '1', 2 => '3'];
        $response->bindParam('string', $string);
        $response->bindParam('test', $test);

        // Render with overriden params
        ob_start();
        $this->renderer->render($this->errorHandler, false);
        $output = ob_get_clean();

        $this->assertEquals(true, $this->errorHandler->isEmpty());
        $this->assertEquals($string, $response->params['string']);
        $this->assertEquals($test, $response->params['test']);
        $this->assertNotEquals(true, empty($output));
        $this->assertNotEquals(json_encode($obj), $output);
        $obj['string'] = $string;
        $obj['test'] = $test;
        $this->assertEquals(json_encode($obj), $output);
        $this->assertEquals($obj, array_merge($response->handler, $response->params));
    }


}
