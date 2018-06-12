<?php
/**.-------------------------------------------------------------------------------------------------------------------
 * |  Github: https://github.com/Tinywan
 * |  Blog: http://www.cnblogs.com/Tinywan
 * |--------------------------------------------------------------------------------------------------------------------
 * |  Author: Tinywan(ShaoBo Wan)
 * |  DateTime: 2018/6/12 9:35
 * |  Mail: Overcome.wan@Gmail.com
 * |  Desc: 描述信息
 * '------------------------------------------------------------------------------------------------------------------*/

namespace Library\Common\Db;


class MySQLi
{
    protected $_conn;

    public function connect($host, $user, $passwd, $dbname)
    {
        $conn = mysqli_connect($host, $user, $passwd, $dbname);
        $this->_conn = $conn;
    }

    public function query($sql)
    {
        $res = mysqli_query($this->_conn, $sql);
        return $res;
    }

    public function close()
    {
        mysqli_close($this->_conn);
    }
}