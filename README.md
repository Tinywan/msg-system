# Install

```php
git clone https://github.com/Tinywan/msg-system.git
```

# update 

```php
composer install
```

# config

#### Redis  

```php
config/RedisConfig.php
```

#### MySQL 

```php
config/DbConfig.php
```

# run  

#### Linux

```php
php start.php start
```

#### Windows 

```php
start_for_win.bat
```

# Data Dictionary  

#### connect type   

|  field   |  desc   |
| :---: | :---: |
| init  |  初始化  |
| json  | 加入直播间 |
|  say  | 发表评论  |
| like  |  点赞   |
| close |  退出   |

#### msg field   

|  fields   |  desc   |
| :---: | :---: |
| client_id  |  客户端连接唯一id  |
| msg  | 消息 |
|  joinTime  | 加入直播间时间  |
| commentTime  |  评论时间|
| content |  评论内容   |
| roomId |  直播间id   |
| userId |  用户id   |
| userName |  用户昵称   |

## 客户端页面

[代码](https://github.com/Tinywan/msg-system/issues/1)

## Demo Show 

![demo-01](/library/Images/show.gif) 

## Problem 

* 提示错误`start_businessworker.php terminated and try to restart`
  * 请查看Redis是否配置合适
  * 如果`composer.json`修改过，使用`composer dump-autoload`命令则重新自动生成autoload的文件
