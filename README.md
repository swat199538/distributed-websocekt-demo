# distributed-websocekt-demo

## 基于swoole和redis的分布式websocket demo，效果如图:

![效果图](https://www.codingfish.xyz/wp-content/uploads/2018/05/demo.png)


## 使用demo:

1. 确保有三台服务器和可公用的redis服务器

2. 确保三台服务器都装了swoole扩展和redis php驱动

3. 修改proxy.php中的redis连接地址然后在服务器1 cli模式下 &nbsp;&nbsp; php proxy.php &nbsp;&nbsp; 启动

4. 修改message.php 中的prxoy服务器地址和redis连接地址然后在服务器2 cli模式下 &nbsp;&nbsp; php message.php &nbsp;&nbsp; 启动

5. 修改message2.php 中的prxoy服务器地址和redis连接地址然后在服务器3 cli模式下 &nbsp;&nbsp; php message2.php &nbsp;&nbsp; 启动

6. 把client1.html中的websocekt连接地址修改成message.php监听的地址，然后浏览器打开。在input中输入 &nbsp;&nbsp; login://user1 &nbsp;&nbsp; 点击发送

7. 把client2.html中的websocket的连接地址修改成message2.php监听的地址，然后浏览器打开。在input中输入 &nbsp;&nbsp; login://user2 &nbsp;&nbsp; 点击发送

8. 把client2.html中iput中的内容改成 &nbsp;&nbsp; user1::你好呀 &nbsp;&nbsp;  点击发送

9. 在client1.html中的text就可以看见client2.htm发送过来的内容

## 实现此demo有什么用:

websocket服务器为了避免服务器过载，单台服务器的socket的连接数是有限的，当一台服务器的连接数到达上限，扩充websocket服务器是必然的。

## 实现思路：

实现分布式其实就是要实现websocket服务器之间的消息互通。增加一个转发服务器，所有websocket服务器与其建立连接。当websocket服务器要推送的客户端不是与本机连接的，就将消息推送给转发服务器，转发服务器再把消息转发给客户端所在的websocekt服务器，客户端所在的websocekt服务器收到转发服务器消息后把消息推送给客户端。

## 流程图：

![流程图](https://www.codingfish.xyz/wp-content/uploads/2018/05/tccd.png)

### 流程图说明:

client1可向client2或者其他client发送消息，并接收其他client发送的消息.

Redis中保存client连接的信息，给每个用户分配唯一的key,包括链接的哪台服务器,转发服务器定时检测消息服务器，如消息服务器挂掉，由转发服务器清理掉Redis已经挂掉的所有链接(为实现)。


## 完整的流程：

1. Client1给Client2发送一条消息

2. WebSocket1接收到消息，根据key从Redis取出Client2的连接信息，连接在本机，直接推送给Client2，流程结束。

3. 如果连接不在本机，把消息推送到转发服务器,由转发服务器把该消息推送给连接所在消息服务器，消息服务器接收消息，推送给Client2。

4. 消息发送结束。

