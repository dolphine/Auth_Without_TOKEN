Auth_Without_TOKEN
====================

UniFi Controller 和 微信公众平台开发服务器不在同一台机器上时使用，适用LNMP 环境

PS.需要配合 https://github.com/Ubiquiti-cn/auth#代码配置 一起使用

安装使用方式
======

    git clone https://github.com/Ubiquiti-cn/Auth_Without_TOKEN.git auth_unifi
    
    cd auth_unifi
    
    git clone https://github.com/Ubiquiti-cn/auth.git guest
    
#### 按照auth readme，配置好 `config.php`

    /* 微信 开发者中心->服务器配置 Token值 */
    define('WECHAT_TOKEN', '');
    
`WECHAT_TOKEN` 为空，不要填写


#### 此时文件目录
    --- www_document

    --- www_document\auth_unifi
    --- www_document\auth_unifi\.htaccess
    --- www_document\auth_unifi\index.php

    --- www_document\auth_unifi\guest
    
#### 修改Nginx.conf 开启htaccess 

    server {
        listen 80;
        ...
        ...
        ...

        root /var/www/auth_without_token;
        index index.html index.htm index.php;
        ...
        ...
        include /var/www/auth_without_token/.htaccess;
    }

#### 重启nginx server
    sudo service nginx restart

修改微信公众平台开发URL
======

token 不变，在原URL 前加上本台UniFi Controller服务器访问地址

如原开发 URL 为 
    
    http://www.demo.com/weixin/index.php
    
本台服务器配置完后的外网访问地址为 `115.115.115.115`或者 `weixin.ubnt.com.cn`, 则修改成
    
    http://115.115.115.115/www.demo.com/weixin/index.php

或
    
    http://weixin.ubnt.com.cn/www.demo.com/weixin/index.php
    
在原URL 前，加上UniFi Controller 的服务器地址
