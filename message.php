<?php
/**
 * Created by PhpStorm.
 * User: wangl
 * Date: 2018/5/11
 * Time: 14:14
 */

//本机ip地址
define('LOCALHOST', '192.168.3.195');
//fd映射登录名zSetKey
define('MEMBER_FD_KEY', LOCALHOST.'_member');
//定义proxy服务器IP
define('PROXY_SERVER', '192.168.3.158');
//定义redis
define('REDIS_SERVER', '192.168.3.158');


$server = new swoole_websocket_server("0.0.0.0", 9501);

$redis = new Redis();
$redis->connect(REDIS_SERVER);

$client = new swoole_client(PROXY_SERVER, 9500);
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

//socket连接
$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "握手成功{$request->fd}\n";
});

//socket收到消息
$server->on('message', function (swoole_websocket_server $server, $frame) use ($redis) {

    $data = json_decode($frame->data, true);

    //登录 todo:检查指定用户是否登录
    if ($data['cmd'] == 'login'){
        $loginInfo = [
            'ip'=>LOCALHOST,
            'fd'=>$frame->fd
        ];

        $redis->set($data['user'], serialize($loginInfo));

        $redis->zAdd(MEMBER_FD_KEY, $frame->fd, $data['user']);

        $server->push($frame->fd, "登录成功");
    }

    if ($data['cmd'] == 'chat'){

        //查找用户登录信息
        if (!$userInfo = $redis->get($data['to'])){
            $server->push($frame->fd, '该用户不存在');
        }

        $userInfo = unserialize($userInfo);

        //判断是否连接同一条服务器
        if ($userInfo['ip'] == LOCALHOST){
            $server->push($userInfo['fd'], $data['msg']);
        }

    }
});

//socket关闭
$server->on('close', function ($ser, $fd) use ($redis) {

    $loginInfoKey = $redis->zRangeByScore(MEMBER_FD_KEY, $fd, $fd);

    if (!empty($loginInfoKey)){
        //移除登录信息
        $redis->del($loginInfoKey[0]);
    }

    //移除fd列表
    $redis->zRemRangeByScore(MEMBER_FD_KEY, $fd, $fd);

});

$server->start();