<?php
/**
 * 数据库配置
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14
 * Time: 18:02
 */
    return array(
        /* 数据库设置 */
        'DB_TYPE'               =>'mysql',
        'DB_HOST'               =>  '127.0.0.1', // 服务器地址
        'DB_NAME'               =>  'sanya_continue',          // 数据库名
        'DB_USER'               =>  'root',      // 用户名
        'DB_PWD'                =>  'root',          // 密码
        'DB_PORT'               =>  '3306',        // 端口
        'DB_PREFIX'             =>  '',    // 数据库表前缀
        'DB_DEBUG'  			=>  true, // 数据库调试模式 开启后可以记录SQL日志
        'DB_CHARSET'            =>  'utf8',      // 数据库编码默认采用utf8
    );
?>