<?php

require __DIR__ . '/../vendor/autoload.php';
include "db.php";

use Utopia\App;
use Utopia\Database\Document;
use Utopia\Database\Query;
use Utopia\Validator\Text;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;
use Utopia\CLI\Console;
use Swoole\Http\Server;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

$host = '0.0.0.0';
$port = '3000';
$server = new Server($host, $port);

App::init(function($response) {
  $response
      ->addHeader('Cache-control', 'no-cache, no-store, must-revalidate')
      ->addHeader('Expires', '-1')
      ->addHeader('Pragma', 'no-cache')
      ->addHeader('X-XSS-Protection', '1;mode=block');
}, ['response'], '*');

App::shutdown(function($request) {
  $date = new DateTime();
  Console::success($date->format('c').' '.$request->getURI());
}, ['request'], 'api');

App::get('/')
  ->inject('request')
  ->inject('response')
  ->inject('db')
  ->action(
    function($request, $response, $db) {
      $result = all($db);

      $response->json($result);
  }
);

App::get('/:name')
->param('name', '', new Text(128), 'Hero name.')
->inject('request')
->inject('response')
->inject('db')
->action(
  function($name, $request, $response, $db) {
    $response->json(
      [
        "result" => find($db, $name)
      ]
    );
  }
);


App::post('/')
->param('name', '', new Text(128), 'Hero name')
->param('origin', '', new Text(128), 'Hero origin')
->inject('request')
->inject('response')
->inject('db')
->action(
    function($name, $origin, $request, $response, $db) {
      try {
        $result = $db->createDocument('heroes', new Document([
          'name' => $name,
          'origin' => $origin,
          '$read' => ['role:all'],
          '$write' => ['role:all']
        ]));

        $response->json(["result" => $result]);
      } catch (\Exception $e) {
        Console::error($e->getMessage());

        $response->json(['error' => $e->getMessage()]);
      }
  }
);

$server->on('start', function($server) {
  Console::success('Server started at http://'.$server->host.':'.$server->port.'\n');
});

$server->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {
  $data = Data();
  $request = new Request($swooleRequest);
  $response = new Response($swooleResponse);
  $app = new App('america/new_york');

  App::setResource(
    'db', 
    function() use($data)  { 
      return $data;
    }
  );

  try {
      $app->run($request, $response);
  } catch (\Throwable $th) {
      Console::error('There\'s a problem with '.$request->getURI());
      $swooleResponse->end('500: Server Error');
  }
});

printf('Server running on %s:%d (^c to quit)...', $host, $port);
$server->start();