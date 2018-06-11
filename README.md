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

# 数据字典  

#### 连接类型   

|  字段   |  描述   |
| :---: | :---: |
| init  |  初始化  |
| json  | 加入直播间 |
|  say  | 发表评论  |
| like  |  点赞   |
| close |  退出   |

#### 消息字段   

|  字段   |  描述   |
| :---: | :---: |
| client_id  |  客户端连接唯一id  |
| msg  | 消息 |
|  joinTime  | 加入直播间时间  |
| commentTime  |  评论时间|
| content |  评论内容   |
| roomId |  直播间id   |
| userId |  用户id   |
| userName |  用户昵称   |
