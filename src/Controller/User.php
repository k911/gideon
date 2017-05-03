<?php
namespace Gideon\Controller;

use Gideon\Http\Response;

class User extends Base
{
    public function index(int $id)
    {
        $response = new Response\Text("WTFSA! User: $id");
        $response->setType('text/html');
        return $response;
    }
}