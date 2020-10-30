<?php
/**
 * 用户模块
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14
 * Time: 14:59
 */

namespace Admis\Controller;


class UserController extends BaseController{



    /**
     * 登录页面视图
     */
    public function login(){

        $this->display();
    }
    public function verify(){

        $config =    array(
            'imageW'    => 150,
            'imageH'    => 35,
            'fontSize'  => 20,    // 验证码字体大小
            'length'    => 4,     // 验证码位数
        );
        $Verify = new \Think\Verify($config);
        $Verify->codeSet = '0123456789';
        $Verify->entry();
    }
    /**
     * 用户登录
     */
    public function loginHandle(){

        if(!checkToken() || !IS_POST){
            $this->message();
        }

        $verify = new \Think\Verify();
        if(!$verify->check(I('post.code'))){
            $this->message('验证码错误',1);
        }

        $username = I("username");
        $password = I("password");
        if(!checkUsername($username) || !checkPassword($password)){
            $this->message('登陆失败,账号或密码错误',1);
        }
        $where = array("username"=>$username);
        $user  = M("admin")->where($where)->find();

        if(!$user ||$user['password'] != md5($password)){
            $this->message('登陆失败,账号或密码错误',1);
        }
        if($user['status']==1){
            $this->message('登陆失败,您的账号已被禁用',1);
        }

        //登陆成功
        $menu = array();    //菜单权限
        //权限查询
        if($user['permission'] != ''){
            $menu = $this->getMenu();
        }
        $user['mode'] = array();
        if(!in_array($user['permission'],['all','manage'])){ //超级管理员不需要筛选权限
            $permission = explode(',',$user['permission']);
            foreach($menu as $k=>$v){
                if(!in_array($v['id'],$permission)){
                    unset($menu[$k]);
                }else{
                    $user['mode'][] = $v['mode'];
                }
            }
        }

        unset($user['password']);
        session('menu',$menu);
        session('user',$user);
        saveLog();
        $this->success('登录成功',U('User/profile'));
    }

	
	public function clearBackDb(){
		$data = read_all('./dbbase');
		$minDate = date("YmdHis",strtotime('-15 days')); 
		foreach($data as $k=>$v){
			if($v['version'] < $minDate ){
				if(file_exists($v['filename'])){
					unlink($v['filename']);
				}
			}
		}
	}


    /**
     * 个人资料查看
     */
    public function profile(){
        $where = array(
            'admin_id'  => session('user.id'),
            'result'    => 1,
            'action'    => 'loginHandle'
        );
        $log  = M("admin_log")->field('ip,create_time')->where($where)->order('id desc ')->find();
        $user = M("admin")->field('password',true)->where(array('id'=>session("user.id")))->find();
        $this->assign('user',$user);
        $this->assign('log',$log);
        $this->setPos();
        $this->display();
    }

    /**
     * 修改账号信息视图
     */
    public function profileEdit(){
        $user = M("admin")->field('password',true)->where(array('id'=>session("user.id")))->find();

        $this->assign('user',$user);
        $this->setPos(array('User-profile'));
        $this->display();
    }

    /**
     * 修改账号信息
     */
    public function profileEditHandle(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }

        $data = array(
            'id'        => session("user.id"),
            'truename'  => trim(I("post.truename")),
            'mobile'    => trim(I("post.mobile")),
            'email'     => trim(I("post.email")),
        );
        $res = M("admin")->save($data);
        if($res!==false){
            $this->message('修改成功',0,U('User/profile'));
        }else{
            $this->message('修改失败',1);
        }

    }

    /**
     * 密码修改
     */
    public function password(){
        $this->setPos(array('User-profile'));

        $this->display();
    }

    /**
     * 密码修改 处理
     */
    public function passwordHandle(){

        if(!checkToken() || !IS_POST){
            $this->message();
        }
        $oldPassword = I("post.oldPassword");
        $newPassword = I("post.newPassword");
        $where = array('id'=>session('user.id'),'password'=>md5($oldPassword));
        $id = M("admin")->field("id")->where($where)->find();

        if(!$id){
            $this->message('旧密码错误',1);
        }

        $res = M("admin")->where($where)->save(array('password'=>md5($newPassword)));
        if($res !== false){
            $this->message('密码修改成功',0,U("User/profile"));
        }else{
            $this->message('密码修改失败,请刷新再试试');
        }
    }
    /**
     * 用户退出登录
     */
    public function loginOut(){
        saveLog();
        session_destroy();      //清空以创建的所有SESSION
        session_unset("user");  //清空指定的session

        if(isset($_SESSION) && isset($_SESSION['user'])){
            unset($_SESSION["user"]);//清空指定的session
        }

        $this->success('成功退出',U('Admis/User/login'));
    }

}