<?php
/**
 * 语言文字相关
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/8/20
 * Time: 11:50
 */
    return array(

        //位置信息
        'pos' => array(
            'Index-index'           => '首页',
            'User-profile'          =>'我的账号',
            'User-profileEdit'      => '修改账号',
            'User-password'         =>'密码修改',
            'UserManage-index'      =>'用户列表',
            'UserManage-edit'       =>'添加/编辑用户',
            'UserManage-log'        =>'用户日志',
            'Article-index'         =>'文章列表',
            'Article-recycle'       =>'文章回收站',
            'Article-image'         =>'相片管理',
            'Article-addArticle'    => '添加/编辑/复制文章',
            'Article-channel'       =>'导航管理',
            'Article-sonChannel'    =>'二级导航',
            'Article-addChannel'    =>'添加/编辑导航',
            'Apply-index'           => '招生录取',
            'Apply-apply'          => '在线报名',
            'Apply-student'         => '导入招生数据',
            'Apply-edit'            => '编辑学生信息',
            'System-index'          =>'基本设置',
            'System-img'            =>'图片设置',
            'System-apply'          =>'招生设置',
            'System-css'            =>'颜色设置',
            'System-dataBack'       =>'数据备份',
            'IndexManage-index'     =>'幻灯片管理',
            'IndexManage-scroll'    =>'滚动栏管理',
            'IndexManage-link'      =>'友情链接',
            'IndexManage-add'       => '添加/编辑',
            'ApplyNew-index'        =>'网上报名',
            'File-index'            => '资料下载',
            'File-upload'           => '上传资料',
            'Apartment-index'       => '报名层次',
            'Apartment-edit'       => '报名层次编辑',
            'Apartment-major'       => '报名层次专业'
        ),
        
        'province' => [
            '海南省','北京市', '天津市',
            '河北省','山西省', '内蒙古自治区', '辽宁省', '吉林省', '黑龙江省',
            '上海市', '江苏省', '浙江省', '安徽省', '福建省', '江西省', '山东省',
            '河南省', '湖北省', '湖南省', '广东省', '广西壮族自治区',
            '重庆市', '四川省', '贵州省', '云南省', '西藏自治区', '陕西省',
            '甘肃省', '青海省', '宁夏回族自治区', '新疆维吾尔自治区', '香港特别行政区',
            '澳门特别行政区', '台湾省', '其他'
        ],
        //'apply_type' => ['201自考专升本', '201自考高起专', '211函授专升本'],
        //'apply_major' => ['专业1','专业二','专业三']

        //首页链接设置
        'web-links' => [
            'apply' => '网上报名',
            'self_test' => '自学考试',
            'aduit_test' => '成人教育',
            'system_login' => '系统登录',
            'self_test_intro' => '自学考试招生简章',
            'aduit_test_intro' => '成人教育招生简章'
        ],
        'TMPL_EXCEPTION_FILE'   =>  './Public/Tpl/think_exception.tpl'// 异常页面的模板文件

    );
?>