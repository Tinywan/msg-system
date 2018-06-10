<?php
/** .-------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |-------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2017/8/26 22:01
 * |  Mail: Overcome.wan@Gmail.com
 * |  Function: 统计消息服务器信息
 * |  Created by PhpStorm
 * '-------------------------------------------------------------------*/


namespace Applications\Development;

class MsgRedis
{
    /**
     * 返回一个Redis 实例
     * @return null
     */
    public static function instance()
    {
        return RedisHandler::location();
    }

    /**
     * ------------------------------------以下是某活动评论的存储与统计---------------------------------------------------
     * 存储活动的评论信息，全部
     * @param $liveId
     * @param $comment
     */
    public static function saveAllComments($liveId, $comment)
    {
        $key = $liveId . "Comments";
        //往右插，往数据库写入是从左往右顺序执行的
        self::instance()->rPush($key, $comment);
        //评论数量自增
        self::increaseTotalCommentsNum($liveId);
    }

    /**
     * 增加评论的数量
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseTotalCommentsNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalCommentsNum";
        return self::redisInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 存储活动评论信息，最新的几条
     * @param $liveId
     * @param $comment
     */
    public static function saveLateComments($liveId, $comment)
    {
        $key = $liveId . "CommentsLate";
        self::redisInstance()->lPush($key, $comment);  //往左插，取出时从左面开始取
        self::redisInstance()->lTrim($key, 0, 100);
    }

    //=========================以下是某活动打开网页人数的统计=========================

    /**
     * 添加打开直播页面的客户端数量（累计打开页面人数）
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseTotalViewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalViewNum";
        return self::redisInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 得到打开直播页面的客户端的数量（累计打开页面人数）
     * @param $liveId
     * @return bool|int|string
     */
    public static function getTotalViewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalViewNum";
        return intval(self::redisInstance()->hGet($key, $field));
    }

    /**
     * 得到打开直播页面的客户端的作弊数量（累计打开页面人数的作弊数量）
     * @param $liveId
     * @return bool|int|string
     */
    public static function getTotalViewNumByCheat($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalViewNumByCheat";
        return intval(self::redisInstance()->hGet($key, $field));
    }

    /**
     * 设置某活动最高同时打开直播页面的人数
     * @param $liveId
     * @param $num
     * @return bool|int|string
     */
    public static function setMaxOnlineViewNum($liveId, $num)
    {
        $key = $liveId . "Num";
        $field = "MaxOnlineViewNum";
        return self::redisInstance()->hSet($key, $field, $num);
    }

    /**
     * 得到某活动最高同时打开直播页面的人数
     * @param $liveId
     * @return bool|int|string
     */
    public static function getMaxOnlineViewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "MaxOnlineViewNum";
        return intval(self::redisInstance()->hGet($key, $field));
    }

}