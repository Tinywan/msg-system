<?php

namespace Applications\Development;

/**
 * 开发服务器信息及端口等的配置类
 * Class AppConfig
 */
class Config
{

    //=========================开发环境消息系统的配置信息=========================
    public static $IP_INNER_REGISTER = "10.168.32.191";        //Register服务器内网IP
    public static $IP_INNER_DETECTION = "10.168.32.191";       //Detection服务器内网IP（检测服务器，和Register服务器一台）
    public static $IP_INNER_REDIS_MSG = "10.161.229.111";       //Redis消息服务器内网IP
    public static $IP_INNER_REDIS_CACHE = "10.161.229.111";     //Redis缓存服务器内网IP（主要缓存1000条评论）

    //Register服务器上的端口
    public static $PORT_REGISTER_REGISTER = 1101;               //Register服务器的Register服务的开发端口
    public static $PORT_REGISTER_GLOBALDATA = 1102;             //Register服务器的GlobalData服务的开发端口
    public static $PORT_DETECTION_PROVIDER = 1103;              //Detection服务器Provider的端口
    public static $PORT_DETECTION_FINDER = 1103;                //Detection服务器Finder的端口，跟PROVIDER端口一致
    public static $PORT_DETECTION_REPORT = 1104;                //Detection服务器Report（Worker）的端口，Watcher服务向Detection服务器汇报用
    public static $PORT_DETECTION_WEB = 1105;                   //Detection服务器Web的端口，用来给浏览器访问

    //Gateway服务器上的开发端口，因为开发环境都部署在一台服务器上所以这里端口占用不能冲突！
    public static $PORT_GATEWAY_WEBSOCKET = 1106;               //Gateway服务器的WebSocket服务的开发端口
    public static $PORT_GATEWAY_START = 1107;                   //Gateway服务器的内部通讯起始端口，从该端口开始加上Gateway进程数个端口都是内部通讯端口

    //Redis服务器的端口
    public static $PORT_REDIS_MSG = 6379;                       //Redis消息服务器的连接端口
    public static $PORT_REDIS_CACHE = 6379;                     //Redis缓存服务器的连接端口
    public static $REDIS_AUTH = 123456;                     //Redis缓存服务器的连接端口

    //消息系统各服务的名字
    public static $NAME_REGISTER = "D_REGISTER";                //Register服务的名字
    public static $NAME_GLOBALDATA = "D_GLOBALDATA";            //GlobalData服务的名字
    public static $NAME_GATEWAY = "D_GATEWAY";                  //Gateway服务的名字
    public static $NAME_WORKER = "D_WORKER";                    //Worker服务的名字


    /**
     * 得到当前服务器的内网IP地址
     * @return string
     */
    static function getInnerIP()
    {
        return str_replace(PHP_EOL, '', shell_exec("ifconfig eth0 |awk -F '[ :]+' 'NR==2 {print $4}'"));
    }

    /**
     * 得到当前服务器的CPU数量（默认逻辑CPU数量）
     * @return int
     */
    static function getCpuNum()
    {
        return intval(shell_exec('cat /proc/cpuinfo | grep "processor" | wc -l'));
    }

    /**
     * 设置当前Gateway进程的数量为CPU数量的2倍，开发环境、测试环境默认是1倍，消耗CPU和内存
     * @return int
     */
    static function getGatewayNum()
    {
        return 1;
    }

    /**
     * 设置当前Worker进程的数量为CPU数量的5倍，开发环境、测试环境默认是1倍，消耗CPU
     * @return int
     */
    static function getWorkerNum()
    {
        return 1;
    }

    /**
     * 得到开放给外部的WebSocket地址及端口
     * @return string
     */
    static function getWebSocketAddress()
    {
        return "websocket://0.0.0.0:" . self::$PORT_GATEWAY_WEBSOCKET;
    }

    /**
     * 启动Worker和Gateway服务器时得到注册服务器的地址及端口
     * @return string
     */
    static function getRegisterAddress()
    {
        return self::$IP_INNER_REGISTER . ":" . self::$PORT_REGISTER_REGISTER;
    }

    /**
     * Worker服务中得到注册服务器GlobalData服务的地址及端口
     * @return string
     */
    static function getGlobalDataAddress()
    {
        return self::$IP_INNER_REGISTER . ":" . self::$PORT_REGISTER_GLOBALDATA;
    }
}