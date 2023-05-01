<?php

use Swoole\Http\{Server, Request, Response};
use Swoole\Coroutine\Http\Client;

Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$server = new Server('0.0.0.0', 8080);

$server->on('request', function (Request $request, Response $response) {
//    $response->header('Content-Type', 'text/html; charset=utf-8');
//    $response->end(print_r($request->header, true));
    $channel = new chan(2);

    go(function () use ($channel) {
//        $client = new Client('localhost', 8001);
//        $client->get('/server.php');
//        $content = $client->getBody();

        $curl = curl_init('http://localhost:8001/server.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($curl);

        $channel->push($content);
    });

    go(function () use ($channel) {
        $content = file_get_contents('arquivo.txt');
        $channel->push($content);
    });

    go(function () use ($channel, &$response) {
        $primeiraResposta = $channel->pop();
        $segundaResposta = $channel->pop();

        $response->end($primeiraResposta . $segundaResposta);
    });
});

$server->start();
