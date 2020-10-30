<?php
/**基类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14
 * Time: 14:50
 */

namespace Admis\Controller;


use Think\Controller;


class BaseController extends Controller{

    public $config;
    public $menuId;
    /**
     * 初始化
     */
    public function __construct(){

        parent::__construct();

        saveLog();

        //登录检查
        $this->isLogin();
        $this->initCommonData(); //初始化公共数据

        if(IS_GET){
            $this->initGetData(); //get 请求时候 初始化数据
        }
    }

    public function  initCommonData(){
        $this->pid = intval(I('pid'));
        $this->mid = intval(I('mid'));
        $this->config = M('config')->select();
        $this->config = formatConfig($this->config);
        $this->menuId = array('mid'=>$this->mid,'pid'=>$this->pid);
    }

    /**
     * 初始化一些数据
     */
    public function initGetData(){

        //菜单数据初始化
        $menuList = session('menu');
        $showMenu = session('showMenu')?session('showMenu'):array();

        foreach($menuList as $k=>$v){
            if(!in_array($v['id'],$showMenu)){
                $menuList[$k]['hide'] = 'menu-hide'; //这些是隐藏的菜单
            }
        }
        $this->assign('menuList',$menuList);

        //token设置
        if(!I("get.token") && ACTION_NAME!='verify'){    //请求链接中有token不再设置
            $this->assign('token',makeToken());
        }
        //提示信息设置
        $this->assign('msg',session('msg'));
        session('msg',array());

        //分配请求参数
        $this->assign('get',$_GET);
        $this->assign('mid',$this->mid);
        $this->assign('pid',$this->pid);
        $this->assign('menuId',$this->menuId);
        $this->assign('isSuper',isSuper());
        //分配配置参数

        $this->assign('config',$this->config);
    }



    /**
     * 获取频道
     */
    public function getChannel(){
        $where['status'] = 0;

        $result = M('channel')->field(array('id,parent_id,name'))->where($where)->order("parent_id asc,sort asc,id asc")->select();
        $channel = array();
        foreach($result as $k=>$v){
            if(!$v['parent_id']){
                $channel[$v['id']] = $v;
            }else{
                $channel[$v['parent_id']]['son'][] = $v;

            }
        }

        return $channel;
    }

    /**
     * 隐藏菜单
     */
    public function hideMenu(){
        $hidden   = I('get.hidden');
        $showMenu = session('showMenu')?session('showMenu'):array();
        if($hidden == 1){
            //隐藏
            foreach($showMenu as $k=>$v){
                if($this->pid == $v){
                    unset($showMenu[$k]);
                    break;
                }
            }
        }else{
            //显示
            if(!in_array($this->pid,$showMenu)){
                array_push($showMenu,$this->pid);
                $showMenu = array_unique($showMenu);
                session('showMenu',$showMenu);
            }
        }

        session('showMenu',$showMenu);
    }



    /**
     * 登录检查
     */
    public function isLogin(){
        if(!isset($_SESSION)){
            session_start(); //开启session
        }

        //排除不需要登录判断的模块
        $action             = strtolower(lcfirst(CONTROLLER_NAME).'.'.lcfirst(ACTION_NAME));
        $uncheckLogin       = C('unCheckLogin');
        $unPermissionLogin  = C('unPermissionLogin');
        $userMode           = session('user.mode');

        foreach($uncheckLogin as $k=>$v){
            $uncheckLogin[$k] = strtolower($v);
        }
        foreach($unPermissionLogin as $k=>$v){
            $unPermissionLogin[$k] = strtolower($v);
        }
        foreach($userMode as $k=>$v){
            $userMode[$k] = strtolower($v);
        }
        //这些模块不需要登录检查
        if(in_array($action,$uncheckLogin)){
            return true;
        }

        if(!isset($_SESSION['user']) && !$_SESSION['user']['id']){
            $this->error('您还未登录',U('User/login'));
        }


        //这些模块不需要登录检查
        if(strtolower(CONTROLLER_NAME) == 'user' || in_array($action,C('unPermissionLogin'))){
            return true;
        }

        //登录校验通过 权限检查
        if(!isSuper() && !in_array(strtolower(CONTROLLER_NAME),$userMode)){
            //$this->error('您无权限访问',U('Index/index'));
        }

    }


    /**
     * 获取菜单
     */
    public function getMenu(){
        $result = M("menu")->field("id,parent_id,name,mode,method")->order("parent_id asc,sort asc,id asc")->where(array('status'=>0))->select();
        $menu = array();
        foreach($result as $k=>$v){
            if($v['parent_id'] == 0){
                $menu[$v['id']] = $v;
            }else{
                $v['link'] = U('Admis/'.$v['mode'].'/'.$v['method'],array('pid'=>$v['parent_id'],'mid'=>$v['id']));
                $menu[$v['parent_id']]['son'][] = $v;
            }
        }
        return $menu;
    }


    /**
     * 设置 我的位置
     * @param $key  关键字 为空自动获取当前控制器和方法
     * @param $prePosi 前面的位置信息
     */
    public function setPos($prePosi = array()){
        $position = array();

        //前面位置
        foreach($prePosi as $k=>$v){
            $key   = $v;        //关键字
            $param = array();   //参数

            if(is_array($v)){   //数组类型
                $key   = $k;
                $param = $v;
            }
            $link = '';

            $controller = str_replace('-','/',$key);
            $link = U($controller,$param);
            $position[] = array('name'=>C('pos.'.$key),'link'=>$link);
        }

        //当前位置
        $action = CONTROLLER_NAME.'-'.ACTION_NAME;
        $position[] = array(
            'name'=>C('pos.'.$action),'link'=>U(CONTROLLER_NAME.'/'.ACTION_NAME,$_GET)
        );
        //网站标题
        $webTitle = $position;
        array_unshift($webTitle,array('name'=>'三亚海洋技术学院'));
        $webTitle = array_reverse($webTitle);

        $this->assign("webTitle",$webTitle);
        $this->assign("pos",$position);
    }

    /**
     * 页面提示 用户操作后跳转并设置提示信息
     * @param $msg  提示信息
     * @param $errcode 状态码 0成功 1失败
     * @param $url  $url
     */
    public function message($msg='',$errcode=0,$url=''){
        if($msg == ''){
            $errcode = 1; //没有提示信息 怎是失败
        }
        if(!$msg){
            $msg = $errcode==0?'操作成功':C('MESSAGE_ERR');
        }
        if(!$url){
            $url =  $_SERVER['HTTP_REFERER']; //上一页
        }
        if(!$url){
            $this->error("页面不存在",U("Home/Index/index"));
        }

        session("msg",array('msg'=>$msg,'errcode'=>$errcode));
        header("Location:{$url}");
        exit;
    }

    /**
     * 图片上传
     * @param int $maxFileSize
     * @param json data
     *      array(
    errcode => 0 成功，1失败
     *          msg => 成功/失败 信息
     *          keyword=> 图片关键字名称
     *          url=> 图片相对路径        失败为空
     *
     *  );
     */
    public function uploadImg(){
        if(!IS_POST){
            $this->message();
        }
        $maxFileSize= I('post.maxsize')?I('post.maxsize'):500*1024;
        $result = array(
            'errcode' => 1,
            'msg' => '',
        );

        foreach($_FILES as $k=>$v){
            $result['keyword'] = $k;

        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     $maxFileSize ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'jpeg', 'png', 'JPEG','gif','JPG');// 设置附件上传类型
        $upload->rootPath  =     C('UPLOAD_IMG'); // 设置附件上传根目录

        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $result['msg'] = $upload->getError();
        }else{// 上传成功
            $result['errcode'] = 0;
            $result['msg'] = '上传成功';
            $result['url'] = $info[$result['keyword']]['savepath'].$info[$result['keyword']]['savename'];
            $result['path'] =  C('DOMAIN').'/Public/uploadimg/';
        }
        return $result;

    }

   

    /**
     * 文件上传
     * @param $type 文件类型
     * @param string $maxSize 文件最大值
     * @return array
     */
    public function uploadFile($type,$maxSize=''){
        if(!IS_POST){
            return  array('errcode' => 1, 'msg' => '操作失败，请重试');
        }
        $maxFileSize= $maxSize?$maxSize:1024*1024*2;//2M
        $result = array(
            'errcode' => 1,
            'msg' => '',
        );

        foreach($_FILES as $k=>$v){
            $result['keyword'] = $k;

        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     $maxFileSize ;// 设置附件上传大小
        $upload->exts      =     $type;// 设置附件上传类型
        $upload->rootPath  =     C('UPLOAD_DOCUMENT'); // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $result['msg'] = $upload->getError();
        }else{// 上传成功
            $result['errcode'] = 0;
            $result['msg'] = '上传成功';
            $result['url'] = C('DOMAIN')."/Public/document/".$info[$result['keyword']]['savepath'].$info[$result['keyword']]['savename'];
            $result['filename'] = C('UPLOAD_DOCUMENT').$info[$result['keyword']]['savepath'].$info[$result['keyword']]['savename'];
        }
        return $result;

    }

    /**
     * 获取报名层次
     */
    public function getApartment(){
        $apartment = M("apartment")->where(['status'=>0])->order("id desc")->select();
        $apartment = array_column($apartment,null,'id');
        foreach($apartment as $id=>$v){
            $major = M("major")->where(['status'=>0,'apartment_id'=>$v['id']])->order("id desc")->select();
            $major = array_column($major,null,'id');
            $apartment[$id]['major'] = $major;
        }
        return $apartment;
    }
}





