<?php
return array(
	//'配置项'=>'配置值'


    //模版替换
    'TMPL_PARSE_STRING'  =>array(
        '__PUBLIC__' => __ROOT__.'/Public', // 更改默认的/Public 替换规则
        '__UPLOAD_IMG__' => __ROOT__.'/Public/uploadimg', //
        '__JS__'     => __ROOT__.'/Public/js', // 增加新的JS类库路径替换规则
        '__CSS__'    => __ROOT__.'/Public/css',
        '__IMG__'    => __ROOT__.'/Public/image',
        '__LIB__'    => __ROOT__.'/Public/lib'
    ),

    //不需要登录验证的模块
    'unCheckLogin' => array(
        'user.login','user.loginHandle','user.verify','user.clearBackDb'
    ),

    //不需要权限验证的模块
    'unPermissionLogin' => array(
        'index.index'
    ),


    //拓展配置
    'LOAD_EXT_CONFIG' => 'mysql,language,channel',

    'TMPL_ACTION_ERROR'     =>  'Public/dispatch_jump', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  'Public/dispatch_jump', // 默认成功跳转对应的模板文件

    'MESSAGE_ERR' => '操作繁忙,请刷新再试试',

	'DOMAIN' => 'http://sanya.continue.com',//'http://www.hysanya.com',
	'UPLOAD_IMG' => './Public/uploadimg/',//'/var/www/html/Public/uploadimg/',
	'UPLOAD_DOCUMENT' =>'./Public/document/',// '/var/www/html/Public/document/',
	//颜色配置
    'css' => array(  
        array('一级导航',       'cssNavi-color',         'cssNavi-bg'),
        array('二级级导航',      'cssNaviSon-color',      'cssNaviSon-bg'),
        array('首页中部',       'cssCenter-color',        'none'),
        array('首页新闻频道标签', 'cssChannelNews-color', 'cssChannelNews-bg'),
        array('首页频道标签',    'cssChannel-color',      'cssChannel-bg'),
        array('首页底部',       'cssFoot-color',          'cssFoot-bg'),
        array('友情链接',       'cssLink-color',          'cssLink-bg')
    ),
    //'TMPL_EXCEPTION_FILE'=>'./Public/error/index.html'
);