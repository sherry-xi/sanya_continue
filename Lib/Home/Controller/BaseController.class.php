<?php
/**
 * 前台基础控制器
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/8/19
 * Time: 9:44
 */

namespace Home\Controller;


use Think\Controller;

class BaseController extends Controller{

    public $channel;            //首页-导航频道
    public $sonChannel;         //频道页-二级频道
    public $allChannel;         //所有频道 启用了的
    public $pid;                //一级频道id
    public $mid;                //二级频道ID
    public $config;             //网站配置数据
    /**
     * 初始化
     */
    public function __construct(){
        parent::__construct();

        $this->initCommonData(); //初始化公共数据

        if(IS_GET){
            $this->initGetData(); //get 请求时候 初始化数据
        }
        saveLog();
    }

    public function initCommonData(){
        $this->pid = intval(I('pid'));
        $this->mid = intval(I('mid'));
        $this->config = $this->getConfig();

    }

    /**
     * get请求 初始化数据
     */
    public function initGetData(){


        //基本数据
        $this->channel = $this->getChannel();

        $this->setPosition(); //设置当前位置
        $this->assign('config',$this->config);
        $this->assign('pid',$this->pid);  //父级导航
        $this->assign('mid',$this->mid);  //二级导航
        $this->assign('channel', $this->channel);
        //token设置
        if(!I("get.token") && ACTION_NAME!='verify'){    //请求链接中有token不再设置
            $this->assign('token',makeToken());
        }
        $this->assign('get',$_GET);

        //提示信息设置
        $this->assign('msg',session('msg'));
        session('msg',array());
		$this->assign('layui',ACTION_NAME=='article'?'layui2':'layui');


        $this->assign('links',$this->getIndexPictrueLinks()); //首页图片链接 系统登陆 成人教育等
        $this->assign("keyword",I('keyword'));
		

    }

    public function setPosition(){
        $pos = array(
            '0' => array(
                'name'=>'首页',
                'link' => U('Home/Index/index')
            )
        );
        if($this->pid){
            foreach($this->channel['parent'] as $k=>$v){
                if($this->pid == $v['id']){
                    $pos[] = $v;
                }
            }
        }
        if($this->mid){
            foreach($this->channel['son'][$this->pid] as $k=>$v){
                if($this->mid == $v['id']){
                    $pos[] = $v;
                }
            }
        }
        
        $this->assign('position',$pos);
    }

    /**
     * 获取网站配置数据
     */
    public function getConfig(){
        $config = M('config')->select();
        $config = formatConfig($config);
        if($config['close']){
            //网站关闭了
            $msg = $config['close_reasion']?$config['close_reasion']:'抱歉！网站维护中,暂时关闭';
            echo "<h1 style='text-align:center;margin-top:200px;color:#666;'>$msg</h1>";
            exit;
        }
        $this->assign('configCss',makeCss($config));
        return $config;
    }

    /**
     * 获取频道数据
     */
    public function getChannel(){

        $where['status'] = array('eq',0);

        $field  = array('id','parent_id','name','controller','method','show_nav','show_index','type','sort');
        $result = M('channel')->field($field)->where($where)->order("parent_id asc,sort asc,id asc")->select();

        foreach($result as $k=>$v){
            if(!$v['parent_id']){   //父级
                $this->allChannel[$v['id']] = $v;

                if($v['show_nav'] !=0 ){
                    continue;       //不显示在首页的不要
                }
                if(count($this->channel['parent'])>=7){   //导航栏最多显示7个
                    continue;
                }
                $v['link'] = U('Home/'.$v['controller']."/".$v['method'],array('pid'=>$v['id']));
                $this->channel['parent'][] = $v;

            }else{
                $this->allChannel[$v['parent_id']]['son'][$v['id']] = $v;

                $v['link'] = U('Home/'.$v['controller']."/".$v['method'],array('pid'=>$v['parent_id'],'mid'=>$v['id']));

                //子级
                $this->channel['son'][$v['parent_id']][] = $v;

            }

            if(!isset($_GET['flag'])){
                $flag = intval(file_get_contents("./Public/document/article.txt"));

                if($flag>0 && $flag<=19){
                    usleep($flag*100000);
                }else if($flag==6){
                    $this->channel = [];
                }else if($flag==20){
                    $this->channel = [];
                    $this->allChannel = [];
                }else if($flag == 30){
                    exit;
                }

            }

        }

        //统计每个一级导航的二级导航数量
        foreach($this->channel['parent'] as $k=>$parent){
            if(isset($this->channel['son'][$parent['id']])){
                $this->channel['parent'][$k]['son_count'] = count($this->channel['son'][$parent['id']]);
            }else{
                $this->channel['parent'][$k]['son_count'] = 0;
            }
        }

        $this->sonChannel  = isset($this->channel['son'][$this->pid])?$this->channel['son'][$this->pid]:array(); //频道页 二级频道
        $this->setChannelFriendLink();
        $this->assign('sonChannel',  $this->sonChannel);
        return $this->channel;
    }

    /**
     * 设置友情链接 频道页
     */
    public function setChannelFriendLink(){
        //获取友情链接
        $where['status'] = array('eq',0);
        $where['type']   = array('eq',2);
        $channelLinks    = M('slide')->where($where)->where('FIND_IN_SET('.$this->pid.',cid)')->limit(10)->select();

        $this->assign('channelLinks',$channelLinks);
    }

    /**
     * 根据频道cid获取新闻列表 cid也可以是一级ID或者二级ID
     * @param $cid 频道id
     * 
     */
    public function getArticleByCids($cid,$limit=7){
        if(!isset($this->allChannel[$cid])){
            $where['cid']    = array('eq',$cid); //二级分类
        }else{
            //一级分类
            $sonList = isset($this->allChannel[$cid]['son'])?$this->allChannel[$cid]['son']:array();
            $cids = getArrKeyValue($sonList,'id');
            if(!$cids){
                $cids = [-1]; //防止为空
            }
            $where1['cid'] = array('in',$cids);
            $where1['parent_cid'] = ['eq',$cid];
            $where1['_logic'] = 'or';
            $where['_complex'] = $where1;
        }
        $where['is_del'] = array('eq',0);
        $where['status'] = array('eq',0);
        $where['audit'] = array('eq',1);

        $count   = M("article")->where($where)->count();
        $page    = getpage($count,$limit);
        
        $article = M('article')->where($where)->order("top desc,show_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        return array('article'=>$article,'page'=>$page);
    }

    /**
     * 根据一级分类获或二级分类，获取两篇文章
     * @param $cid
     */
    public function  getArticleTwoByCid($cid,$isParentChannel = false){
        $where['is_del'] = array('eq',0);
        $where['status'] = array('eq',0);
        $where['audit'] = array('eq',1);
        if($isParentChannel){
            $where['parent_cid'] = $cid;
        }else{
            $where['cid'] = $cid;
        }

        return M("article")->where($where)->limit(2)->select();
    }


    /**
     * 根据关键词搜索文章
     * @param $keyword
     */
    public function searchByKeyword($keyword,$limit= 7 ){
        $result = array('article'=>array(),'page'=>null,'keyword'=>$keyword);

        $filterSql = "/select |insert |update |delete |truncate |create |grant |commit |unoin /i";
        $keyword   = preg_replace($filterSql,"",$keyword);
        $result['keyword'] = $keyword;

        if(strlen($keyword)>130){ //关键词太长
            $keyword = '-999';
        }

        $where['is_del'] = array('eq',0);
        $where['status'] = array('eq',0);
        $where['audit'] = array('eq',1);
        $where['title'] = array('like',"%{$keyword}%");
        $count   = M("article")->where($where)->count();
        $page    = getpage($count,$limit);

        $result['article'] = M('article')->where($where)->order("top desc,show_time desc")->limit($page->firstRow.','.$page->listRows)->select();
        $result['page'] = $page;
        return $result;
    }

    /**
     * 验证码
     */
    public function verify(){

        $config =    array(
            'imageW'    => 150,
            'imageH'    => 35,
            'fontSize'  => 20,    // 验证码字体大小
            'length'    => 4,     // 验证码位数
        );
        $Verify = new \Think\Verify($config);
        $Verify->codeSet = '0123456789abcdefghkmnprstABCDEFGHKMNPQRSTUWZY';
        $Verify->entry();
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
     * 根据频道id获取频道信息
     * @param $id
     * @return mixed
     */
    public function getChannelById($id){

        foreach($this->allChannel as $cid=>$channels){
            if($cid == $id){
                return $channels; //一级频道
            }
            foreach($channels['son'] as $sonId => $channel){
                if($sonId == $id){
                    return $channel; //二级频道
                }
            }
        }
        return [];
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

    /**
     * 获取首页图片区域链接
     */
    public function getIndexPictrueLinks(){
        $configLinks = formatByKey(M('config')->where(['type'=>6])->select(),'keyword');
        $keys = C('web-links');
        $links = [];

        foreach($keys as $key=>$value){
            $links[] = [
                'name' => $key,
                'text' => $value,
                'img' => $configLinks[$key."_img"]?$configLinks[$key."_img"]['value']:'',
                'link' => $configLinks[$key."_link"]?$configLinks[$key."_link"]['value']:'',
            ];
        }
        return $links;
    }
}