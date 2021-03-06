<?php
namespace Gideon\Database\Connection;

use Gideon\Config;

/**
 * Config keys used:
 * - 'MYSQL_SETTINGS_DEFAULT'
 */

class MySQL extends Base 
{
    protected function parseSettings(Config $config, array $settings = null): array
    {
        $defaults = $config->get('MYSQL_SETTINGS_DEFAULT');

        if(!isset($settings['username']) && isset($defaults['username']))
        {
            $settings['username'] = $defaults['username'];

            if(isset($defaults['password']))
                $settings['password'] = $defaults['password'];
        }

        if(!isset($settings['prefix']))
            $settings['prefix'] = $defaults['prefix'] ?? 'mysql';

        if(!isset($settings['host']))
            $settings['host'] = $defaults['host'] ?? 'localhost';

        if($settings['host'] !== 'localhost' && !isset($settings['port']))
            $settings['port'] = $defaults['port'] ?? '3306';
        
        if(!isset($settings['dbname']))
            $settings['dbname'] = $defaults['dbname'];
            
        if(!isset($settings['charset']) && isset($defaults['charset']))
            $settings['charset'] = $defaults['charset'];

        return $settings;
    } 
}