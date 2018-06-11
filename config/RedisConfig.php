<?php
/**.-------------------------------------------------------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |--------------------------------------------------------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2018/6/11 15:14
 * |  Mail: Overcome.wan@Gmail.com
 * |  Desc: 描述信息
 * '------------------------------------------------------------------------------------------------------------------*/

namespace config;

class RedisConfig
{
    // 本地测试
    public static $location = [
        // 服务器地址
        'host' => '127.0.0.1',
        // 端口号
        'port' => '6379',
        // 密码
        'password' => '',
        // 缓存前缀
        'prefix' => 'REDIS_LOCATION:',
        // 缓存有效期 0表示永久缓存
        'expire' => 604800
    ];

    // 消息
    public static $message = [
        // 服务器地址
        'host' => '172.19.230.35',
        // 端口号
        'port' => '6379',
        // 密码
        'password' => '',
        // 缓存前缀
        'prefix' => 'REDIS_MESSAGE:',
        // 缓存有效期 0表示永久缓存
        'expire' => 604800
    ];

    // 缓存
    public static $cache = [
        // 服务器地址
        'host' => '127.0.0.1',
        // 端口号
        'port' => '6379',
        // 密码
        'password' => '',
        // 缓存前缀
        'prefix' => 'REDIS_CACHE:',
        // 缓存有效期 0表示永久缓存
        'expire' => 604800
    ];
}