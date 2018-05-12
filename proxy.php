<?php
/**
 * Created by PhpStorm.
 * User: wangl
 * Date: 2018/5/11
 * Time: 16:15
 */

//定义redis地址
define('REDIS_SERVER', '192.168.3.46');
//定义连接机器保存KEY
define('ON_LINE_SERVER', 'online_server');


$server = new swoole_server("0.0.0.0", 9500);

$redis = new Redis();
$redis->connect(REDIS_SERVER);


$server->on('connect', function (swoole_server $server, $fd) use ($redis){
    echo "connection open: {$fd}\n";

    $info = $server->getClientInfo($fd);

    //根据IP保存上线websocket服务器fd
    $redis->hSet(ON_LINE_SERVER, $info['remote_ip'], $fd);

    $server->send($fd, '连接成功');

});


$server->on('receive', function ($server, $fd, $reactor_id, $data) use ($redis) {

    $info = json_decode($data, true);

    //查找服务器并将消息发送
    $to = $redis->hGet(ON_LINE_SERVER, $info['forward']);

    //转发消息
    $server->send($to, $data);

    $server->send($fd, "Swoole: {转发成功}");
});


$server->on('close', function (swoole_server $server, $fd) use ($redis) {

    //移除下线websocket服务器
    $redis->hDel(ON_LINE_SERVER, $server->getClientInfo($fd)['remote_ip']);
    echo "connection close: {$fd}\n";

});

$server->on('WorkerStart', function (swoole_server $server){
});


$server->start();