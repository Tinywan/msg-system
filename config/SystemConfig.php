<?php
/**.-------------------------------------------------------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |--------------------------------------------------------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2018/6/11 15:20
 * |  Mail: Overcome.wan@Gmail.com
 * |  Desc: 系统配置信息
 * '------------------------------------------------------------------------------------------------------------------*/

namespace Config;


class SystemConfig
{
    /**
     * 得到当前服务器的内网IP地址
     * @return string
     */
    public static function getInnerIP()
    {
        return str_replace(PHP_EOL, '', shell_exec("ifconfig eth0 |awk -F '[ :]+' 'NR==2 {print $4}'"));
    }

    /**
     * 得到当前服务器的CPU数量（默认逻辑CPU数量）
     * @return int
     */
    public static function getCpuNum()
    {
        return intval(shell_exec('cat /proc/cpuinfo | grep "processor" | wc -l'));
    }
}