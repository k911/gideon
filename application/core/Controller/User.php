<?php
namespace Gideon\Controller;

use Gideon\Renderer\Response;

class User extends Base 
{
    public function index()
    {
        $response = new Response\Text("WTFSA!");
        $response->setType('text/html');
        return $response;
    }
}