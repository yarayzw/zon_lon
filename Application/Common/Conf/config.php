<?php
return array(
	//'配置项'=>'配置值'

    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '192.168.0.103', // 服务器地址 192.168.31.132  192.168.1.128 47.97.96.199   120.79.183.103
    'DB_NAME'   => 'ymkj_dn', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码  YhDB-mysql-5.7!
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'ymkj_', // 数据库表前缀


    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__'=>__ROOT__.'/Public',
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__IMG__'    => __ROOT__ . '/Public/img',
        '__CSS__'    => __ROOT__ . '/Public/css',
        '__JS__'     => __ROOT__ . '/Public/js',
        '__H3__'     => __ROOT__ . '/Public/h3',
    )
);