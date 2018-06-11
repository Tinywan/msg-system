<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/8/26 22:01
 * |  Mail: Overcome.wan@Gmail.com
 * |  Function: 统计消息服务器信息
 * '-------------------------------------------------------------------*/

namespace library;

class MsgRedis
{
    /**
     * 返回一个Redis 实例
     * @return null
     */
    public static function instance()
    {
        //return BaseRedis::message();
        return BaseRedis::location();
    }

    /**
     * 直播间PV统计
     * @param $roomId
     */
    public static function Pv($roomId)
    {
        $key = "PV:ROOM:".$roomId;
        $field = "ROOM_TOTAL_PV";
        self::instance()->hIncrBy($key,$field,1);
    }

    /**
     * 直播间UV统计
     * @param $roomId
     */
    public static function Uv($roomId)
    {
        $key = "UV:ROOM:".$roomId;
        $field = "ROOM_TOTAL_UV";
        self::instance()->hIncrBy($key,$field,1);
    }

    /**
     * 存储活动的评论信息，全部
     * @param $roomId
     * @param $comment
     */
    public static function saveAllComments($roomId, $comment)
    {
        $commentsKey = "COMMENTS:TOATAL:".$roomId;
        //往右插，往数据库写入是从左往右顺序执行的
        self::instance()->rPush($commentsKey, json_encode($comment));
    }

    /**
     * 增加评论的数量
     * @param $roomId
     * @return bool|int|string
     */
    public static function increaseTotalCommentsNum($roomId)
    {
        $key = $roomId . "Num";
        $field = "TotalCommentsNum";
        self::instance()->hIncrBy($key, $field, 1);
    }

    /**
     * 存储活动评论信息，最新的几条
     * @param $roomId
     * @param $comment
     */
    public static function saveLatestComments($roomId, $comment)
    {
        $latestCommentsKey = "COMMENTS:LATEST:".$roomId;
        //往左插，取出时从左面开始取
        self::instance()->lPush($latestCommentsKey, json_encode($comment));
        self::instance()->lTrim($latestCommentsKey, 0, 9);
    }

    /**
     * 取出最新的活动评论信息
     * @param $liveId
     * @param $num
     * @return array
     */
    public static function getLatestComments($roomId, $num)
    {
        $latestCommentsKey = "COMMENTS:LATEST:".$roomId;
        return self::instance()->lRange($latestCommentsKey, 0, $num - 1);
    }

}