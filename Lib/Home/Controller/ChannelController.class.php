<?php
/**
 * 频道页面
 */
namespace Home\Controller;
use Think\Controller;


class ChannelController extends BaseController{

    /**
     * 频道页面视图
     */
    public function index(){
        $parentChannel = $this->getChannelById(I('pid'));
        $this->assign('parentChannel',$parentChannel);

        if($this->pid == C("channelId.download")){ //资料下载页
            $this->fileDownload();
            exit;
        }

        $keyword = trim(I('keyword'));

        if($keyword){
            //用户通过顶部搜索框 搜索文章
            $article = $this->searchByKeyword($keyword,10);
        }else{
            //新闻频道 获取新闻列表
            if($this->mid){
                $article = $this->getArticleTwoByCid($this->mid,false);//用户点击二级导航
            }else{
                $article = $this->getArticleTwoByCid($this->pid,true);//用户点击一级导航
            }
            //如果只有一篇文章 直接显示文章内容
            if(count($article) == 1){
                $this->assign('isSingle',1);//标志是否单篇文章
                $this->article($article[0]);
                exit;
            }else{//不止一篇文章 显示文章列表
                $cid = $this->mid?$this->mid:$this->pid;
                $article = $this->getArticleByCids($cid);
            }

        }

        $newsList = $article['article'];
        $search = array(" ","　","\n","\r","\t","&nbsp;");
        $replace = array("","","","","","");
        $keywordReplace = "<span style='color: #116ee2 !important;'>{$keyword}</span>";

        foreach($newsList as $k=>$v){
            $v['author']      = $v['author']?$v['author']:$this->config['website'];
            $v['create_time'] = formatArticleTime($v['create_time']);
            $v['content']     = htmlspecialchars_decode($v['content']);
            $v['description'] = str_replace($search, $replace, strip_tags($v['content']));
            $v['thumb']       = $v['thumb']?$v['thumb']:$this->config['defaultThumb'];

            if($keyword){    //搜索词部分替换成高亮
                $v['title'] = str_replace($keyword,$keywordReplace,$v['title']);
            }
            if($v['show_time']){
                $v['create_time'] = substr($v['show_time'],0,16);
            }
            $newsList[$k]     = $v;

        }

        $this->assign('keyword',$article['keyword']);
        $this->assign('page',$article['page']->show());
        $this->assign('newsList',$newsList);

        $this->display();
    }

    /**
     * 文章浏览页面
     */
    public function article($article){

        $article['create_time'] = formatArticleTime($article['create_time']);
        $article['content']     = htmlspecialchars_decode($article['content']);

        if(!$article['author']){
            $article['author']  = $this->config['website'];
        }
        if($article['show_time']){
            $article['create_time'] = substr($article['show_time'],0,16);
        }

        $this->assign('article',$article);
        $parentChannel = $this->getChannelById(I('pid'));
        $this->assign('parentChannel',$parentChannel);
        $this->display("Index/article");
    }

    /**
     * 在线招生页面
     */
    public function apply(){
        $queryMsg = ''; //查看是否是报名时间
        $begin = date("Y").'-'.$this->config['applyBegin'];
        $end   = date("Y").'-'.$this->config['applyEnd']." 23:59:59";
        $now   = date("Y-m-d H:i:s");
        if($now < $begin){
            $queryMsg = "现在还不是报名时间，报名时间是：{$begin} 至 ".substr($end,0,10);
        }else if($now > $end){
            $queryMsg = "报名时间已过，报名时间是：{$begin} 至 ".substr($end,0,10);
        }
        $this->assign('queryMsg',$queryMsg);

        //专业
        $this->assign('class',formatClass($this->config['class']));
        $this->display();
    }


    /**
     * 在线报名
     */
    public function applyHandle(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }
        $data = array(
            'name'        => trim(I('name')),
            'card'        => trim(I('card')),
            'birthday'    => trim(I('birthday')),
            'school'      => trim(I('school')),
            'class'       => trim(I('class')),
            'sex'         => trim(I('sex')),
            'phone'       => trim(I('phone')),
            'parent_phone'=> trim(I('parent_phone')),
            'qq'          => trim(I('qq')),
            'weixin'      => trim(I('weixin')),
            'nation'      => trim(I('nation')),
            'politics'    => trim(I('politics')),
            'health'      => trim(I('health')),
            'native'      => trim(I('native')),
            'account_addr'=> trim(I('account_addr')),
            'addr'        => trim(I('addr')),
            'poverty'     => trim(I('poverty')),
            'low_security'=> trim(I('low_security')),
            'create_time' => date("Y-m-d H:i:s")
        );
        if(!$data['name'] ||!$data['card'] ){
            $this->message('请输入姓名和身份证',1);
        }
        //查看该学生是否已经提交过报名了
        $table = createSutdentTable("student_apply");
        $table2 = createSutdentTable("student");

        $where = array(
            'card' => $data['card'],
            'phone' => $data['phone']
        );
        $id = M($table)->where($where)->field(array('id'))->find();
        if($id){
            $this->message('您已经报过名了,不可以再次报名');
        }else{
            $id = M($table2)->where($where)->field(array('id'))->find();
            if($id){
                $this->message('您已经报过名了,不可以再次报名');
            }
        }
        $id = M($table)->add($data);
        if($id){
            session('applyOk',str_replace('xxx',$data['name'],$this->config['applyOk']));
            $this->message('报名成功');
        }else{
            $this->message('报名失败,请点击上角qq联系管理员');
        }

    }


    public function checkCode(){
        if(!IS_AJAX){
            $this->ajaxReturn(array('errcode'=>1,'msg'=>'服务器繁忙，请刷新重试'));
        }
        $verify = new \Think\Verify();
        if(!$verify->check(I('get.code'))){
            $this->ajaxReturn(array('errcode'=>1,'msg'=>'验证码错误'));
        }
        $this->ajaxReturn(array('errcode'=>0,'msg'=>'验证码正确'));
    }

    /**
     * 录取查询
     */
    public function applyQuery(){

        if(I('get.name')){
            //提交查询
            $where['name']      = array('eq',trim(I('name')));
            $where['card']      = array('eq',trim(I('card')));

            $table    = createSutdentTable('student');
            $student  = M($table)->where($where)->find();
            if($student){   //被录取了
                $result = str_replace('xxx',$student['name'],$this->config['applyMsg']);
                M($table)->where(array('id'=>$student['id']))->save(array('query_time'=>date('Y-m-d H:i:s')));
                $student['name'] .= "<b style='color:red;'>   (恭喜,您已被录取)</b>";
                session('applyOk',null);
            }else{
                //没被录取
                unset($where['apply_id']);
                $table    = createSutdentTable('student_apply');
                $student  = M($table)->where($where)->find();

                if($student){
                    $result=str_replace('xxx',$student['name'],$this->config['applyNo']);
                    session('applyOk',null);
                }else{
                    //没有这个学生信息
                    $result = "没有该学生信息";
                }
            }
            $this->assign('student',$student);
            $this->assign('result',$result);

        }


        $queryMsg = ''; //查看是否是报名时间
        $begin = date("Y").'-'.$this->config['queryBegin'];
        $end   = date("Y").'-'.$this->config['queryEnd']." 23:59:59";
        $now   = date("Y-m-d H:i:s");
        if($now < $begin){
            $queryMsg = "现在还不是录取查询，录取查询时间是：{$begin} 至 ".substr($end,0,10);
        }else if($now > $end){
            $queryMsg = "录取查询时间已过，录取查询时间是：{$begin} 至 ".substr($end,0,10);
        }
        $this->assign('queryMsg',$queryMsg);
        $this->display();
    }


    public function fileDownload(){

        $channel = M("channel")->where(['parent_id'=>C("channelId.download")])->select();
        $channel = array_column($channel,null,'id');

        $where = " 1 = 1 ";
        if($this->mid){
            $where .= " and channel_id = ".$this->mid;
        }
        $count   = M("file")->where($where)->count();
        $page    = getpage($count,20);
        $channel = M("channel")->where(['parent_id'=>C("channelId.download")])->select();
        $channel = array_column($channel,null,'id');
        $files = M('file')->where($where)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();

        foreach($files as $k=>$file){
            $files[$k]['size'] = file_size_format($file['size']);
            $files[$k]['channel'] = $channel[$file['channel_id']]?$channel[$file['channel_id']]['name']:'';

        }
        $rootPath = C("DOMAIN").str_replace('.','',C("UPLOAD_DOCUMENT"));
        $this->assign('rootPath',$rootPath);
        $this->assign('files',$files);
        $this->assign('pageShow',$page->show());
        $this->display('file');
    }

}