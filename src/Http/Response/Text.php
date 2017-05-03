<?php
namespace Gideon\Http\Response;

use stdClass;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Response;

/**
 * Used Config keys:
 * - TEXT_REPLACE_PATTERN
 * - TEXT_REPLACE_PATTERN_DOCUMENT
 * - TEXT_REPLACE_UNDEFINED
 * - TEXT_HTML_RENDER_IN_PRE
 */

class Text extends Base
{

    public function setHandler($text): Response
    {
        if (!is_string($text)) {
            $text = var_export($this->handler, true);
        }

        $this->handler = $text;
        return $this;
    }

    public function setup(Config $config)
    {
        // Replace only $this->params, $config
        $param = $this->params;
        $this->handler = preg_replace_callback($config->get('TEXT_REPLACE_PATTERN'),
            function ($matches) use ($config, $param) {
                $obj_name = strtolower($matches[1]);

                if (isset(${$obj_name}->{$matches[2]})) {
                    return ${$obj_name}->{$matches[2]};
                }

                return $matches[0];
            },
            $this->handler
        );
    }

    public function render(Config $config, Locale $locale = null, stdClass $document = null)
    {
        $text_output = $this->handler;
        if ($this->type === 'text/html' && $config->get('TEXT_HTML_RENDER_IN_PRE') === true) {
            $text_output = "<pre>$text_output</pre>";
        }

        // Replace all things
        $param = $this->params;
        $text_output = preg_replace_callback($config->get('TEXT_REPLACE_PATTERN'),
            function ($matches) use ($config, $locale, $document, $param) {
                $obj_name = strtolower($matches[1]);

                if (isset(${$obj_name}->{$matches[2]})) {
                    return ${$obj_name}->{$matches[2]};
                }

                return $config->get('TEXT_REPLACE_UNDEFINED');
            },
            $text_output
        );

        echo trim($text_output);
    }

    public function mergeWith(Text $response): Text
    {
        $this->handler = rtrim($this->handler)  . "\n" . ltrim($response->handler);
        $this->code = $response->code;
        $this->type = $response->type;
        foreach ($response->params as $name => $value) {
            $this->params->{$name} = $value;
        }
        return $this;
    }
}
