<?php
namespace Gideon;

use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Request;

interface Controller 
{
    public function __construct(Config $config, Locale $locale, Request $request);
}