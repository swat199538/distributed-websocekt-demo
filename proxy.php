<?php
/**
 * Created by PhpStorm.
 * User: wangl
 * Date: 2018/5/11
 * Time: 16:15
 */

$server = new swoole_server("127.0.0.1", 9500);


$server->on('connect', function (swoole_server $server, $fd){
    echo "connection open: {$fd}\n";

    $info = $server->getClientInfo();

    var_dump($info);

});


$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    $server->send($fd, "Swoole: {$data}");
});


$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});


$server->start();