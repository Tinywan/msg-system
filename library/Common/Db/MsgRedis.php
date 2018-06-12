<?php
/**.-------------------------------------------------------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |--------------------------------------------------------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2018/6/10 6:52
 * |  Mail: Overcome.wan@Gmail.com
 * |  Desc: 统计消息服务器信息
 * '------------------------------------------------------------------------------------------------------------------*/

namespace Library\Common\Db;

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

    /**.----------------------------------------------------------------------------------------------------------------
     * |--------------------消息统计
     * '----------------------------------------------------------------------------------------------------------------
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
     * 直播间PV
     * @param $roomId
     * @return int
     */
    public static function getPv($roomId)
    {
        $key = "PV:ROOM:".$roomId;
        $field = "ROOM_TOTAL_PV";
        return intval(self::instance()->hGet($key, $field));
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
        $key = "COMMENTS:NUMS:".$roomId;
        $field = "COMMENTS_TOTAL_NUM";
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
     * @param $roomId
     * @param $num
     * @return array
     */
    public static function getLatestComments($roomId, $num)
    {
        $latestCommentsKey = "COMMENTS:LATEST:".$roomId;
        return self::instance()->lRange($latestCommentsKey, 0, $num - 1);
    }

    /**
     * 添加活动点赞的数量
     * @param $roomId
     * @return bool|int|string
     */
    public static function increaseTotalLikeNum($roomId)
    {
        $key = "COMMENTS:LIKE:".$roomId;
        $field = "COMMENTS_LIKE_TOTAL";
        return self::instance()->hIncrBy($key, $field, 1);
    }

    /**
     * 得到活动点赞的数量
     * @param $roomId
     * @return bool|int|string
     */
    public static function getCommentsLikeNum($roomId)
    {
        $key = "COMMENTS:LIKE:".$roomId;
        $field = "COMMENTS_LIKE_TOTAL";
        return intval(self::instance()->hGet($key, $field));
    }

    /**
     * 得到活动点赞的作弊数量
     * @param $roomId
     * @return int
     */
    public static function getTotalLikeNumByCheat($roomId)
    {
        $key = $roomId . "Num";
        $field = "TotalLikeNumByCheat";
        self::setUpdateStatus($key);
        return intval(self::instance()->hGet($key, $field));
    }

    /**
     * 设置数据的更新情况
     * @param $key
     * @return int
     */
    public static function setUpdateStatus($key)
    {
        $field = "IsUpdate";
        self::instance()->hSet($key, $field, 1);
    }


    /**
     * 设置用户过滤器，0无名单，1白名单，-1黑名单
     * @param $roomId
     * @return int
     */
    public static function getUserFilter($roomId)
    {
        $key = $roomId . "Num";
        $field = "UserFilter";
        return intval(self::instance()->hGet($key, $field));
    }

    /**
     * 返回黑名单列表
     * @param $roomId
     * @return array|mixed
     */
    public static function getBlackList($roomId)
    {
        $key = "LIST:BLACK:".$roomId;
        $field = "BLACK_LIST";
        if (self::instance()->hExists($key, $field)) {
            return json_decode(self::instance()->hGet($key, $field), true);
        } else {
            return array();
        }
    }

    /**
     * 返回白名单列表
     * @param $roomId
     * @return array|mixed
     */
    public static function getWhiteList($roomId)
    {
        $key = "LIST:WHITE:".$roomId;
        $field = "WHITE_LIST";
        if (self::instance()->hExists($key, $field)) {
            return json_decode(self::instance()->hGet($key, $field), true);
        } else {
            return array();
        }
    }

    /**
     * 判断域名是否合法
     * @param $domain
     * @return bool
     */
    public static function inLegalDomains($domain)
    {
        $key = "DOMAINS";
        return self::instance()->hExists($key, $domain);
    }

}