<?php
/**
 * Created by PhpStorm.
 * User: Dragon
 * Date: 2017-1-15
 * Time: 下午 3:46
 */

namespace Applications\Development;


class RedisHandler extends \Redis
{
    private static $REDIS_MSG = null;
    private static $REDIS_CACHE = null;

    /**
     * 返回MsgRedis的单例
     * @return null|RedisHandler
     */
    public static function getMsgInstance()
    {
        if (null == self::$REDIS_MSG) {
            self::$REDIS_MSG = new self();
            self::$REDIS_MSG->connect(Config::$IP_INNER_REDIS_MSG, Config::$PORT_REDIS_MSG);
        }
        return self::$REDIS_MSG;
    }


    /**
     * 返回CacheRedis的单例
     * @return null|RedisHandler
     */
    private static function getCacheInstance()
    {
        if (null == self::$REDIS_CACHE) {
            self::$REDIS_CACHE = new self();
            self::$REDIS_CACHE->connect(Config::$IP_INNER_REDIS_CACHE, Config::$PORT_REDIS_CACHE);
        }
        return self::$REDIS_CACHE;
    }

    //=========================以下是客户端信息的统计=========================

    /**
     * 统计客户端的信息，通过Hash的方式存储
     * @param $liveId
     * @param $userId
     * @param $clientId
     * @param $data
     * @return int
     */
    public static function saveStatisticsInfoByHash($liveId, $userId, $clientId, $data)
    {
        $key = $liveId . "StatisticsByHash";
        self::getMsgInstance()->hSet($key, $userId . "_" . $clientId, $data);
    }

    /**
     * 统计客户端的信息，通过List的方式存储
     * @param $liveId
     * @param $data
     * @return int
     */
    public static function saveStatisticsInfoByList($liveId, $data)
    {
        $key = $liveId . "StatisticsByList";
        self::getMsgInstance()->rPush($key, $data); //往右插，统计时从左往右顺序执行
    }

    //=========================以下是某活动评论的存储与统计=========================

    /**
     * 存储活动的评论信息，全部
     * @param $liveId
     * @param $comment
     */
    public static function saveComments($liveId, $comment)
    {
        $key = $liveId . "Comments";
        self::getMsgInstance()->rPush($key, $comment);//往右插，往数据库写入是从左往右顺序执行的
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
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 存储活动评论信息，最新的几条
     * @param $liveId
     * @param $comment
     */
    public static function saveLateComments($liveId, $comment)
    {
        $key = $liveId . "CommentsLate";
        self::getMsgInstance()->lPush($key, $comment);  //往左插，取出时从左面开始取
        self::getMsgInstance()->lTrim($key, 0, 100);

        self::saveLateCommentsToCacheRedis($liveId, $comment);
    }

    /**
     * 取出最新的活动评论信息
     * @param $liveId
     * @param $num
     * @return array
     */
    public static function getLateComments($liveId, $num)
    {
        $key = $liveId . "CommentsLate";
        return self::getMsgInstance()->lRange($key, 0, $num - 1);
    }

    /**
     * 存储活动评论信息，存储1000条，放在cacheRedis中
     * @param $liveId
     * @param $comment
     */
    public static function saveLateCommentsToCacheRedis($liveId, $comment)
    {
        $key = $liveId;
        self::getCacheInstance()->lPush($key, $comment);  //往左插，控制那面写的是lPush，按照这个为规范吧
        self::getCacheInstance()->lTrim($key, 0, 1000);
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
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
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
        return intval(self::getMsgInstance()->hGet($key, $field));
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
        return intval(self::getMsgInstance()->hGet($key, $field));
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
        return self::getMsgInstance()->hSet($key, $field, $num);
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
        return intval(self::getMsgInstance()->hGet($key, $field));
    }

    //=========================以下是某活动播放直播人数的统计=========================

    /**
     * 添加所有点击直播按钮的人数（观看直播）
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseTotalPlayLiveNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalPlayLiveNum";
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 递增当前点击直播按钮的人数（观看直播）
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseCurrentPlayLiveNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayLiveNum";
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 递减当前点击直播按钮的人数（观看直播）
     * @param $liveId
     * @return bool|int|string
     */
    public static function decreaseCurrentPlayLiveNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayLiveNum";
        return self::getMsgInstance()->hIncrBy($key, $field, -1);
    }

    /**
     * 得到当前观看直播的人数
     * @param $liveId
     * @return bool|int|string
     */
    public static function getCurrentPlayLiveNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayLiveNum";
        return self::getMsgInstance()->hGet($key, $field);
    }

    /**
     * 当前观看直播的人数重置或归零
     * @param $liveId
     * @param int $num
     * @return bool|int|string
     */
    public static function resetCurrentPlayLiveNum($liveId, $num = 0)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayLiveNum";
        return self::getMsgInstance()->hSet($key, $field, $num);
    }

    /**
     * 设置某活动最高同时观看的人数
     * @param $liveId
     * @param $num
     * @return bool|int|string
     */
    public static function setMaxOnlinePlayLiveNum($liveId, $num)
    {
        $key = $liveId . "Num";
        $field = "MaxOnlinePlayLiveNum";
        return self::getMsgInstance()->hSet($key, $field, $num);
    }

    /**
     * 得到某活动最高同时观看直播的人数
     * @param $liveId
     * @return bool|int|string
     */
    public static function getMaxOnlinePlayLiveNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "MaxOnlinePlayLiveNum";
        return intval(self::getMsgInstance()->hGet($key, $field));
    }

    //=========================以下是某活动播放回顾人数的统计=========================

    /**
     * 添加点击回顾按钮的人数（观看回顾）
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseTotalPlayReviewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalPlayReviewNum";
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 递增当前观看回顾的人数（观看回顾）
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseCurrentPlayReviewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayReviewNum";
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 递减当前观看回顾的人数（观看回顾）
     * @param $liveId
     * @return bool|int|string
     */
    public static function decreaseCurrentPlayReviewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayReviewNum";
        return self::getMsgInstance()->hIncrBy($key, $field, -1);
    }

    /**
     * 递增当前所有用户观看回顾的流量（单位Mb）
     * @param $liveId
     * @param $flow
     * @return bool|int|string
     */
    public static function increaseAddOnPlayReviewFlow($liveId, $flow)
    {
        $key = $liveId . "Num";
        $field = "AddOnPlayReviewFlow";
        return self::getMsgInstance()->HincrByFloat($key, $field, $flow);
    }

    /**
     * 得到当前观看回顾的人数
     * @param $liveId
     * @return bool|int|string
     */
    public static function getCurrentPlayReviewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayReviewNum";
        return self::getMsgInstance()->hGet($key, $field);
    }

    /**
     * 当前观看回顾的人数重置或归零
     * @param $liveId
     * @param int $num
     * @return bool|int|string
     */
    public static function resetCurrentPlayReviewNum($liveId, $num = 0)
    {
        $key = $liveId . "Num";
        $field = "CurrentPlayReviewNum";
        return self::getMsgInstance()->hSet($key, $field, $num);
    }

    /**
     * 设置某活动最高同时观看回顾的人数
     * @param $liveId
     * @param $num
     * @return bool|int|string
     */
    public static function setMaxOnlinePlayReviewNum($liveId, $num)
    {
        $key = $liveId . "Num";
        $field = "MaxOnlinePlayReviewNum";
        return self::getMsgInstance()->hSet($key, $field, $num);
    }

    /**
     * 得到某活动最高同时观看回顾的人数
     * @param $liveId
     * @return bool|int|string
     */
    public static function getMaxOnlinePlayReviewNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "MaxOnlinePlayReviewNum";
        return intval(self::getMsgInstance()->hGet($key, $field));
    }

    //=========================以下是某活动点赞人数统计=========================

    /**
     * 添加活动点赞的数量
     * @param $liveId
     * @return bool|int|string
     */
    public static function increaseTotalLikeNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalLikeNum";
        return self::getMsgInstance()->hIncrBy($key, $field, 1);
    }

    /**
     * 得到活动点赞的数量
     * @param $liveId
     * @return bool|int|string
     */
    public static function getTotalLikeNum($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalLikeNum";
        return intval(self::getMsgInstance()->hGet($key, $field));
    }

    /**
     * 得到活动点赞的作弊数量
     * @param $liveId
     * @return int
     */
    public static function getTotalLikeNumByCheat($liveId)
    {
        $key = $liveId . "Num";
        $field = "TotalLikeNumByCheat";
        self::setUpdateStatus($key);
        return intval(self::getMsgInstance()->hGet($key, $field));
    }

    /**
     * 设置数据的更新情况
     * @param $key
     * @return int
     */
    public static function setUpdateStatus($key)
    {
        $field = "IsUpdate";
        self::getMsgInstance()->hSet($key, $field, 1);
    }

    //=========================以下是活动黑白名单控制=========================

    /**
     * 设置用户过滤器，0无名单，1白名单，-1黑名单
     * @param $liveId
     * @return int
     */
    public static function getUserFilter($liveId)
    {
        $key = $liveId . "Num";
        $field = "UserFilter";
        return intval(self::getMsgInstance()->hGet($key, $field));
    }

    /**
     * 返回黑名单列表
     * @param $liveId
     * @return array|mixed
     */
    public static function getBlackList($liveId)
    {
        $key = $liveId . "Num";
        $field = "BlackList";
        if (self::getMsgInstance()->hExists($key, $field)) {
            return json_decode(self::getMsgInstance()->hGet($key, $field), true);
        } else {
            return array();
        }
    }

    /**
     * 返回白名单列表
     * @param $liveId
     * @return array|mixed
     */
    public static function getWhiteList($liveId)
    {
        $key = $liveId . "Num";
        $field = "WhiteList";
        if (self::getMsgInstance()->hExists($key, $field)) {
            return json_decode(self::getMsgInstance()->hGet($key, $field), true);
        } else {
            return array();
        }
    }

    //=========================以下是判断WebSocket连接来源是否合法=========================

    /**
     * 判断域名是否合法
     * @param $domain
     * @return bool
     */
    public static function inLegalDomains($domain)
    {
        $key = "Domains";
        return self::getMsgInstance()->hExists($key, $domain);
    }
}