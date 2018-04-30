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
    
    /* URL配置 */
    'URL_CASE_INSENSITIVE' => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 1, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

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