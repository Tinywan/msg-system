<?php

/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 字典说明
 * type: 连接类型 {init 初始化、json 加入直播间、say 发表评论、like 点赞、play 点击播放}
 * client_id：客户端id
 * msg: 连接后触发的消息
 * joinTime：加入直播间时间
 * commentTime: 评论时间
 * content: 评论内容
 * roomId：直播间id
 * userId: 直播间id
 * userName: 用户名称
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        $resData = [
            'type' => 'init',
            'client_id' => $client_id,
            'msg' => 'connect is success' // 初始化房间信息
        ];
        Gateway::sendToClient($client_id, json_encode($resData));
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $message)
    {
        // 服务端console输出 
        //echo "msg : $message \r\n";

        // 解析数据
        $resData = json_decode($message, true);
        $type = $resData['type'];
        $roomId = $resData['roomId'];
        $userId = $resData['userId']; // 未登录，则传递一个随机
        $userName = $resData['userName']; // 未登录，则传递一个随机
        $content = isset($resData['content']) ? $resData['content'] : 'default content';
        
        //将时间全部置为服务器时间
        $serverTime = date('Y-m-d H:i:s', time());

        switch ($type) {
            case 'join':  // 用户进入直播间
                //将客户端加入到某一直播间
                Gateway::joinGroup($client_id, $roomId);
                $resData = [
                    'type' => 'join',
                    'roomId' => $roomId,
                    'userName' => $userName,
                    'msg' => "enters the Room", // 发送给客户端的消息，而不是聊天发送的内容
                    'joinTime' => $serverTime // 加入时间                    
                ];
                $redis = new \Redis;
                $redis->connect('127.0.0.1',6379);
                $key = "PV:ROOM:".$roomId;
                $field = "ROOM_TOTAL_PV";
                // 进入房间的人数增长，自增 ，增加PV统计
                $redis->hIncrBy($key,$field,1);

                // 广播给直播间内所有人，谁？什么时候？加入了那个房间？
                Gateway::sendToGroup($roomId, json_encode($resData));
                break;
            case 'say':  // 用户发表评论
                $resData = [
                    'type' => 'say',
                    'roomId' => $roomId,
                    'userName' => $userName,
                    'content' => $content,
                    'commentTime' => $serverTime // 发表评论时间
                ];

                // 将所有评论存储到redis中
                $redis = new \Redis;
                $redis->connect('127.0.0.1',6379);
                $commentsKey = "COMMENTS:TOATAL:".$roomId;
                $redis->rPush($commentsKey, json_encode($resData));//往右插，往数据库写入是从左往右顺序执行的

                // 存储活动评论信息，最新的几条
                $latestCommentsKey = "COMMENTS:LATEST:".$roomId;
                $redis->lPush($latestCommentsKey, json_encode($resData));  //往左插，取出时从左面开始取
                $redis->lTrim($latestCommentsKey, 0, 10);

                // 广播给直播间内所有人
                Gateway::sendToGroup($roomId, json_encode($resData));
                break;
            case 'pong':
                break; // 接收心跳
            default:
                //Gateway::sendToAll($client_id,$json_encode($resData));
                break;
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
       // 向所有人发送 
        GateWay::sendToAll("$client_id logout\r\n");
    }
}
