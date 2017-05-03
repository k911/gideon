<?php
require("../vendor/autoload.php");
$config = new Gideon\Handler\Config('debug', ['LOGGER_FILE' => __DIR__ . '/../application/log/app2.log']);
$cache = new Gideon\Cache\SimpleCache($config);
$cache->clear();

function callback(int $id, $config)
{
    $connection = (new Gideon\Database\Connection\MySQL($config, [
        'username' => 'root',
        'dbname' => 'knit_backup',
        'charset' => 'latin1'
    ]))->connect();

    // That ones who have existing author
    $t = $connection->prepare('
        SELECT
            m.tytul,m.tresc,m.data,m.kto_dodal,m.widocznosc,
            u.imie,u.nazwisko,u.email,u.login
        FROM wiadomosci as m
        INNER JOIN uzytkownicy as u
            ON u.id = m.kto_dodal
        ORDER BY m.data ASC
        LIMIT 10
    ');
    $t->execute();
    $rows = [];
    while($row = $t->fetch())
    {
        $rows[] = $row;
    }

    // Rest ones
    $t = $connection->prepare('
        SELECT
            m.tytul,m.tresc,m.data,m.kto_dodal,m.widocznosc
        FROM wiadomosci as m
        WHERE m.kto_dodal NOT IN
        	(SELECT u.id FROM uzytkownicy as u)
        ORDER BY m.data ASC
        LIMIT 10
    ');
    $t->execute();
    while($row = $t->fetch())
    {
        $rows[] = $row;
    }

    $data = [];
    foreach($rows as $row)
    {
        $art = [];
        $art['title'] = $row['tytul'];
        // TODO: Split
        $art['content'] = $row['tresc'];
        // Member model
        $art['author'] = [
            'nickname' => $row['login'] ?? 'unknown',
            'firstname' => $row['imie'] ?? '',
            'lastname' => $row['nazwisko'] ?? '',
            'email' => $row['email'] ?? ''
        ];
        $art['visibility'] = $row['widocznosc'];
        $art['date'] = new DateTime($row['data']);
        $data[] = $art;
    }

    // Sort by date
    usort($data, function($a, $b) {
        return ($a['date'] < $b['date']) ? -1 : 1;
    });

    // Convert DateTime obj to string format
    foreach($data as $i => $art)
    {
        $data[$i]['date'] = $art['date']->format(DateTime::ATOM);
    }

    //return (new Gideon\Http\Response\JSON($data));
    $data = json_encode($data, JSON_PRETTY_PRINT); //var_export($data, true);
    return (new Gideon\Http\Response\Text($data));
}

if (!$cache->has('ROUTER')) {
    $router = new Gideon\Router\FastRouter($config);
    $router->addRoute('app2.php/test/:id', 'callback', 'GET');
    $router->addRoute('app2.php/user/:id', ['User', 'index'], 'GET'); // app2.php/user/[:id]/[:action]/
    $router->prepareAll();
    $cache->set('ROUTER', $router, 60*3600);
} else {
    $router = $cache->get('ROUTER');
}

$app = new Gideon\Application($config, $router);
$app->run();
// $app->showDebugDetails();
$app->render();
