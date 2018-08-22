#### 安装

```php
git clone https://github.com/Tinywan/msg-system.git
```

#### 解决依赖 

```php
composer install
```

#### 编辑配置文件

###### Redis（默认）  

```php
config/RedisConfig.php
```

###### MySQL 

```php
config/DbConfig.php
```

#### 开始服务  

###### Linux

```php
php start.php start
```
> 守护进程 `php start.php start -d`

###### Windows 

```php
start_for_win.bat
```
>直接运行批处理文件即可，如何做成一个服务请自行谷歌 

#### 数据字典  

###### 消息事件   

|  字段   |  描述   |
| :--- | :--- |
| init  |  初始化  |
| json  | 加入直播间 |
|  say  | 发表评论  |
| like  |  点赞   |
| close |  退出   |

###### 消息内容描述   

|  字段   |  描述   |
| :--- | :--- |
| client_id  |  客户端连接唯一id  |
| msg  | 消息 |
| joinTime  | 加入直播间时间  |
| commentTime  |  评论时间|
| content |  评论内容   |
| roomId |  直播间id   |
| userId |  用户id   |
| userName |  用户昵称   |

#### 客户端页面

[代码](https://github.com/Tinywan/msg-system/issues/1)

#### Demo  

![demo-01](/library/Images/show.gif) 

#### 问题  

* 提示错误`start_businessworker.php terminated and try to restart`
  * 请查看Redis是否配置合适
  * 如果`composer.json`修改过，使用`composer dump-autoload`命令则重新自动生成autoload的文件
