<?php
/**.-------------------------------------------------------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |--------------------------------------------------------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2018/6/11 15:21
 * |  Mail: Overcome.wan@Gmail.com
 * |  Desc: 业务逻辑层
 * '------------------------------------------------------------------------------------------------------------------*/

use \GatewayWorker\Lib\Gateway;
use \Library\Common\Db\MsgRedis;
use \Library\Common\Db\Pdo;

/**
 * 字典说明
 * type: 连接类型 {init 初始化、json 加入直播间、say 发表评论、like 点赞、play 点击播放}
 * client_id：客户端id
 * msg: 连接后触发的消息
 * joinTime：加入直播间时间
 * commentTime: 评论时间
 * content: 评论内容
 * roomId：直播间id
 * userId: 用户id
 * userName: 用户名称
 */
class Events
{
    const MSG_TYPE = ['join', 'say', 'like', 'pong'];

    /**
     * Pdo 实例
     * @var
     */
    private static $_pdo;

    /**
     * Worker进程开启时触发
     */
    public static function onWorkerStart()
    {
        echo "Start WorkerStart\n";
        // test MySQL
        //static::$_pdo = new Pdo();
    }

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

        // 链接MySQL数据库
        //$sql = "INSERT INTO tinywan_admin (username,password,status) VALUES ('onConnect','121111','1')";
        //static::$_pdo->exec($sql);

        // 解析数据
        $resData = json_decode($message, true);
        $type = $resData['type'];
        // 非消息类型为非法请求，关闭连接
        if(!in_array($type,self::MSG_TYPE)) {
            return Gateway::closeClient($client_id);
        }
        $roomId = $resData['roomId'];
        $userId = $resData['userId']; // 未登录，则传递一个随机
        $userName = $resData['userName'].rand(1111,9999); // 未登录，则传递一个随机
        $content = isset($resData['content']) ? $resData['content'] : 'default content';
        
        //将时间全部置为服务器时间
        $serverTime = date('Y-m-d H:i:s', time());

        switch ($type) {
            case 'join':  // 用户进入直播间
                //将客户端加入到某一直播间
                Gateway::joinGroup($client_id, $roomId);

                // 如果没有$_SESSION['userId']说明客户端没有加入
                if(!isset($_SESSION['userId'])) {
                    // 设置session，标记该客户端已经登录
                    $_SESSION['userId'] = $resData['userId'];
                }
                // 设置session，关闭时统计活动ID
                $_SESSION['roomId'] = $roomId;
                $_SESSION['userName'] = $userName;                      

                // PV 统计
                MsgRedis::Pv($roomId);

                //得到评论的数据
                $latestComments = MsgRedis::getLatestComments($roomId, 5);
                //向当前用户自己广播数据 广播给直播间内所有人，谁？什么时候？加入了那个房间？
                $resData = array(
                    'type' => 'join',
                    'userName' => $userName,
                    'message' => '用户加入直播间',
                    'totalViewNum' => 12,
                    'totalLikeNum' => 22,
                    'joinTime' => $serverTime,
                    'commentList' => array_reverse($latestComments), //倒序一下，把最新的放到最后（也就是页面的最下面）
                    'currentNum' => Gateway::getClientCountByGroup($roomId)
                );
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
                MsgRedis::saveAllComments($roomId,$resData);
                // 存储活动评论信息，最新的几条
                MsgRedis::saveLatestComments($roomId,$resData);
                // 广播给直播间内所有人
                Gateway::sendToGroup($roomId, json_encode($resData));
                break;
            case 'like':
                //点赞人数增长
                MsgRedis::increaseTotalLikeNum($roomId);
                $arr = array(
                  'type' => 'num',
                  'userName' => $userName,
                  'message' => '用户点赞成功',
                  'totalViewNum' => MsgRedis::getPv($roomId),
                  'totalLikeNum' => MsgRedis::getCommentsLikeNum($roomId) + MsgRedis::getTotalLikeNumByCheat($roomId),
                  'currentNum' => Gateway::getClientCountByGroup($roomId)
                );
                //给群组内的所有人广播
                Gateway::sendToGroup($roomId, json_encode($arr));
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
        $resData = [
            'type' => 'logout',
            'client_id' => $client_id,
            'userName' => $_SESSION['userName'],
            'outTime' => date('Y-m-d H:i:s', time()),
            'msg' => $_SESSION['userName'].' is logout' // 初始化房间信息
        ];
        if (isset($_SESSION['roomId'])) {
            Gateway::sendToGroup($_SESSION['roomId'], json_encode($resData));
        }else{
            GateWay::sendToAll("$client_id logout\r\n");
        }
    }
}
