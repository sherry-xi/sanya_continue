<?php
/**
 * 用户管理
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/8/20
 * Time: 16:36
 */

namespace Admis\Controller;


class UserManageController extends  BaseController{

    /**
     * 用户列表
     */
    public function index(){
        $where  = array('status'=>0,'permission'=>array('neq','all'));
        $count  = M("admin")->where($where)->count();
        $page   = getpage($count);
        $users  = M("admin")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->select();

        foreach($users as $k=>$v){
            if($v['permission'] == 'all'){
                unset($users[$k]); //过滤超级管理员
                continue;
            }
            if(!$v['permission']){
                $users[$k]['right'] = "<span style='color:#666;'>(无)</span>";
                continue;
            }
            $permission      = explode(',',$v['permission']);
            $where['id']     =array('in',$permission);
            $where['status'] = array('eq',0);

            //查找权限
            $result = M("menu")->where($where)->getField('name',100);
            $users[$k]['right'] = implode('|',$result);
        }
        $this->assign('pageShow',$page->show());
        $this->assign('users',$users);
        $this->setPos();
        $this->display();
    }


    /**
     * 用户日志 登录和退出日志
     */
    public function log(){
        $where['action'] = array('in',array('loginHandle','loginOut'));
        $where['admin_id'] = array('gt',0);

        $count  = M("admin_log")->where($where)->count();
        $page   = getpage($count);

        $logs = M("admin_log")->where($where)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        foreach($logs as $k=>$v){
            $logs[$k]['action'] =   $v['action']=='loginHandle'?'登录':'退出';
            $user = M("admin")->field(array('username','truename'))->where(array('id'=>$v['admin_id']))->find();
            if($user){
                $logs[$k]['username'] = $user['username'];
                $logs[$k]['truename'] = $user['truename'];
            }else{
                $logs[$k]['username'] = '账号已被删除';
            }

        }
        $this->setPos();
        $this->assign("logs",$logs);
        $this->assign('pageShow',$page->show());
        $this->display();
    }


    /**
     * 用户删除
     */
    public function del(){

        if(!checkToken() ||!IS_GET){
            $this->message();
        }
        $id = intval(I('get.id'));
        if(!$id){
            $this->message('参数错误',1);
        }
        $where = array('id'=>$id,'status'=>0);
        $user = M('admin')->where($where)->find();
        if(!$user){
            $this->message('账号不存在',1);
        }
        $res  = M("admin")->where($where)->delete();
        if($res){
            $this->message('删除成功',0);
        }else{
            $this->message('删除失败,请稍后再试试',1);

        }
    }

    /**
     * 编辑/添加用户
     */
    public function edit(){
        $id = intval(I('get.id'));
        $user = array();
        if($id){
            $user = M("admin")->field('password',true)->where(array('id'=>$id))->find();
        }

        //查询所有权限
        $permission = M('menu')->field("id,name")->where(array('status'=>0,'parent_id'=>0))->order('sort asc')->select();
        //查询所有频道
        $channel1 = $this->getChannel();
        $channel2 = $channel1;

        /*
        //设置选择已有的权限
        if($user&&$user['permission']){
            $per = explode(',',$user['permission']);
            foreach($permission as $k=>$v){
                if(in_array($v['id'],$per)){
                    $permission[$k]['check'] = 1;
                }
            }
        }*/
        $userPer = $user?myExplode($user['permission']):array();
        $userCheckPer = $user?myExplode($user['article_check_per']):array();
        $userArticlePer = $user?myExplode($user['article_per']):array();

        $this->assign('userPer',$userPer);
        $this->assign('userCheckPer',$userCheckPer);
        $this->assign('userArticlePer',$userArticlePer);
        $this->assign('channel1',$channel1);
        $this->assign('channel2',$channel2);
        $this->setPos(array('UserManage-index'));
        $this->assign('permission',$permission);
        $this->assign('user',$user);
        $this->display();
    }



    /**
     * 编辑/添加用户
     */
    public function editHandle(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }
        $id         = intval(I('post.id'));
        $username   = trim(I('post.username'));
        $user = array(
            'truename'          => I('post.truename'),

        );
        if(I('post.password')){
            $user['password'] = md5(I('post.password'));
        }

        $msg = '';
        if($id){
            $user['id'] = $id;
            M('admin')->save($user);
            $msg = '编辑成功';
        }else{
            //检查账号是否已经存在了
            $exitUser = M('admin')->where(array('username'=>$username))->find();
            if($exitUser){
                $this->message('添加失败，账号已存在',1);
            }
            $user['username'] = $username;
            $user['create_time'] = date("Y-m-d H:i:s");
            $user['permission']  = 'manage'; //写死 管理员身份
            $res = M('admin')->add($user);
            if(!$res){
                $this->message('添加失败,请重试',1);
            }
            $msg = '添加成功';
        }
        $this->message($msg,0,U('UserManage/index',$this->menuId));

    }

}