<?php
namespace Gideon\Renderer\Response;

use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Renderer\Response;

/**
 * Used Config keys:
 * - TEXT_REPLACE_PATTERN
 * - TEXT_REPLACE_PATTERN_DOCUMENT
 * - TEXT_REPLACE_UNDEFINED
 */

class Text extends Base
{

    public function setHandler($text): Response 
    {
        if(!is_string($text))
            $text = var_export($this->handler, true);
            
        $this->handler = $text;
        return $this;
    }

    public function setup(Config $config)
    {
        // Replace only $this->params, $config
        $param = $this->params;
        $this->handler = preg_replace_callback($config->get('TEXT_REPLACE_PATTERN'), 
            function($matches) use($config, $param)
            {
                $obj_name = strtolower($matches[1]);

                if(isset(${$obj_name}->{$matches[2]}))
                    return ${$obj_name}->{$matches[2]};

                return $matches[0];
            },
            $this->handler
        );
    }

    public function render(Config $config, Locale $locale, \stdClass $document)
    {
        $text_output = $this->type == 'text/html' ? "<pre>{$this->handler}</pre>" : $this->handler;
        
        // Replace all things
        $param = $this->params;
        $text_output = preg_replace_callback($config->get('TEXT_REPLACE_PATTERN'), 
            function($matches) use($config, $locale, $document, $param)
            {
                $obj_name = strtolower($matches[1]);

                if(isset(${$obj_name}->{$matches[2]}))
                    return ${$obj_name}->{$matches[2]};

                return $config->get('TEXT_REPLACE_UNDEFINED');
            }, 
            $text_output
        );

        echo $text_output;
    }

}