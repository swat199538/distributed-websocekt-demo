<?php
/**
 * Created by PhpStorm.
 * User: wangl
 * Date: 2018/5/11
 * Time: 16:15
 */

$server = new swoole_server("0.0.0.0", 9500);


$server->on('connect', function (swoole_server $server, $fd){
    echo "connection open: {$fd}\n";

//    $info = $server->getClientInfo($fd);

//    var_dump($info);

    $server->send($fd, '连接成功');

});


$server->on('receive', function ($server, $fd, $reactor_id, $data) {

    var_dump($data);

    $server->send($fd, "Swoole: {$data}");
});


$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});

$server->on('WorkerStart', function (swoole_server $server){
    if ($server->taskworker){
        return;
    }

    swoole_timer_tick(10000, function () use ($server){
        foreach ($server->connections as $fd){
            $server->send($fd, '我是proxy');
        }
    });

});


$server->start();