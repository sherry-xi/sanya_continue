<?php
/**
 * 文章
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/8/21
 * Time: 20:37
 */

namespace Admis\Controller;


class ArticleController extends BaseController{


    /**
     * 文章列表
     */
    public function index(){
        $this->indexCommon();
    }

    public function indexCommon($isDel=0){
        $where   = [];
        $cid     = I('get.cid');
        $status  = intval(I("get.status",-1));
		$audit   = intval(I("get.audit",-1));
        $top     = intval(I('get.top'));
        $slide   = intval(I('get.slide'));
        $scroll  = intval(I('get.scroll'));
        $keyword = trim(I('get.keyword'));

        if($cid){
            if(strpos($cid,'parent_')!==false){
                $cid = intval(str_replace('parent_','',$cid));
                $cids = M("channel")->where(array('parent_id'=>$cid))->getField('id',true);
                $where1['cid'] = array('in',$cids?$cids:[-1]);
                $where1['parent_cid'] = ['eq',$cid];
                $where1['_logic'] = 'or';
                $where['_complex'] = $where1;
            }else{
                $where['cid'] = array('eq',$cid);
            }
        }

        $where['is_del'] = ['eq',$isDel];
		if($audit !=-1){
            $where['audit'] = array('eq',$audit);
        }
        if($status !=-1){
            $where['status'] = array('eq',$status);
        }
        if($top){
            $where['top'] = array('eq',$top);
        }
        if($slide){
            $where['slide'] = array('eq',$slide);
        }
        if($scroll){
            $where['scroll'] = array('eq',$scroll);
        }
        if($keyword){
            $where['title'] = array("like","%{$keyword}%");
        }
        if(!isSuper()){
            //$where['cid'] = array('in',myExplode(session('user.article_per')));
        }
        $count   = M("article")->where($where)->count();// echo M('article')->getLastSql();die;
        $page    = getpage($count);
        $article = M('article')->where($where)->limit($page->firstRow.','.$page->listRows)->order("top desc,show_time desc")->select();

        $channel = $this->getChannel();
        $sonList = array();
        foreach($channel as $k=>$v){
            foreach($v['son'] as $key=>$son){
                $sonList[$son['id']]['name'] = $v['name'].'&gt'.$son['name'];
                $sonList[$son['id']]['pid'] = $v['id'];
            }
        }
        foreach($article as $k=>$v){
            if($v['cid'] == 0){
                $v['channel'] = $channel[$v['parent_cid']]['name'];//一级导航
            }else{
                $v['channel'] = $sonList[$v['cid']]['name'];
            }
            $v['pid']     =  $sonList[$v['cid']]['pid'];

            $article[$k] = $v;
        }
        //$this->assign('channel',filterChannel($channel));\
        $this->assign('channel',$channel);

		$this->assign('audit',$audit);
        $this->assign('cid',$cid);
        $this->assign('status',$status);
        $this->assign('top',$top);
        $this->assign('slide',$slide);
        $this->assign('scroll',$scroll);
        $this->assign('keyword',$keyword);
        $this->assign('isDel',$isDel);
        $this->assign('article',$article);
        $this->assign('pageShow',$page->show());
        $this->setPos();
        $this->display('index');
    }
    /*
     * 文章回收站
     */
    public function recycle(){
        $this->indexCommon(1);
    }

    /**
     * 添加文章页面
     */
    public function addArticle(){
        $id = intval(I('get.id'));

        $haveCheck = false; //是否有审核权限
        //$checkPer  = myExplode(session('user.article_check_per'));

        if($id){
		
            $article = M('article')->where(array('id'=>$id))->find();

			$pid = M("channel")->where(array('id'=>$article['cid']))->getField("parent_id");
			$pid = intval($pid);	

            //$haveCheck = isSuper() || in_array($pid,$checkPer);
            if($article['show_time']){
                $article['show_time'] = substr($article['show_time'],0,16);
            }
            $this->assign('article',$article);
        }
        //$this->assign('haveCheck',$haveCheck);

        $this->assign('author',session('user.truename'));
        $this->assign('copy',intval(I('get.copy')));
        //$this->assign('channel',filterChannel($this->getChannel()));
        $this->assign('channel',$this->getChannel());
        $this->assign('datetime',date('Y-m-d H:i'));
        $this->setPos(array('Article-index'));
        $this->display();
    }

    /**
     * 添加文章处理
     */
    public function addArticleHandle(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }
        $id   = intval(I('post.id'));
        $copy = intval(I('copy'));

        $data = array(
            'title'     => trim(I('post.title')),
            'title_color'=> trim(I('post.title_color')),
            'title_bold'=> trim(I('post.title_bold')),
            'keyword'   => trim(I('post.keyword')),
            'content'    => I('post.content'),
            'thumb'     => trim(I('post.thumb')),
            'status'    => intval(I('post.status')),
            'admin_id'  => session('user.id'),
            'author'    => trim(I('post.author')),
            'top'       => intval(I('post.top')),
            'slide'     => intval(I('post.slide')),
            'scroll'    => intval(I('post.scroll')),
            'show_time'    => I('post.show_time')
        );

        $cid  = I("cid");

        //文章挂在一级导航下
        if(strpos($cid,'parent_') !== false){
            $data['parent_cid'] = str_replace('parent_','',$cid);
            $data['cid'] = 0;
        }else{
            $data['cid'] = $cid; //文章挂在2级导航下
            $data['parent_cid'] = 0;
        }

        if(isset($_POST['audit'])){
            //$data['audit']  = intval(I('post.audit'));
            $data['audit']  = 1;//默认审核通过
        }
        $extra = array(
            'cid'  => $data['cid'],
            'title'=> $data['title'],
            'img'  => $data['thumb'],
        );

        $msg = '添加';
        if($id && !$copy){

            $msg = '编辑';
            $res  = M('article')->where(array('id'=>$id))->save($data);
            if($res === false){
                $this->message($msg.'失败',1);
            }
            $extra['article_id'] = $id;
        }else{
            if($copy){
                $msg = '复制';
            }
            $data['create_time'] = date('Y-m-d H:i:s');

            $extra['article_id'] = M('article')->add($data);
            if($extra['article_id'] == false){
                $this->message($msg.'失败',1);
            }
        }
        //获取一级频道id
        $pid = M("channel")->where(array('id'=>$data['cid']))->getField('parent_id');
        $extra['link'] = getDomain().U('Home/Index/article',array('id'=>$extra['article_id'],'mid'=>$data['cid'],'pid'=>$pid));

        $where2['type']       = array('in',array(0,1));
        $where2['article_id'] = array('eq',$extra['article_id']);
        $result = M("slide")->field(array('type','id'))->where($where2)->select();

        $extra['type'] = 0;
        $slides['slide']     = $extra;
        $extra['type'] = 1;
        $slides['scroll']     = $extra;

        foreach($result as $k=>$v){
            foreach($slides as $i=>$val){
                if($val['type'] == $v['type']){
                    $slides[$i]['id'] = $v['id'];
                }
            }
        }
        foreach($slides as $k=>$v){
            $v['status'] = $data['status'];
            if($data[$k]){
                if(isset($v['id']) && $v['id']){
                    //编辑
                    M("slide")->save($v);
                }else{
                    $v['create_time'] = date("Y-m-d H:i:s");
                    //添加
                    M("slide")->add($v);
                }
            }else{
                //删除
                if(isset($v['id'])){
                    M("slide")->where(array('id'=>$v['id']))->delete();
                }
            }
        }

        $this->message($msg.'成功',0,U('index',$this->menuId));
    }

    public function delArticle(){
        if(!checkToken() || !IS_GET){
            $this->message();
        }
        $is_del =intval(I('is_del'));
        $msg    = '删除成功';
        $url    = "recycle";
        $where  = array('id'=>intval(I('get.id')));
        $where2 = array('article_id'=>I('get.id'));
       if($is_del == 2){
            $msg = '彻底删除成功';
           M('article')->where($where)->delete();
           M('slide')->where($where2)->delete();
        }else{
            $status = $is_del;
           if($is_del == 0){
               $msg = '还原成功';
           }else{
               $url = "index";
           }
           M('article')->where($where)->save(array('is_del'=>$is_del));
           M('slide')->where($where2)->save(array('status'=>$status));
       }
        $this->message($msg,0,U($url,$this->menuId));
    }

    public function uploadImg2(){
        $result = parent::uploadImg();
        $res    = array(
            'code' => $result['errcode'],
            'msg'  => $result['msg'],
            'data' => array('src'=> $result['path'].$result['url'])
        );
        $this->ajaxReturn($res);
    }

    public function uploadImg(){
        $result = parent::uploadImg();
        $result = json_encode($result);

        echo "<script type='text/javascript'>";
        echo "parent.uploadCallBack('{$result}')";
        echo "</script>";
        exit;
    }


    /*
     * 频道列表 二级频道
     */
    public function sonChannel(){
        $parentId = intval(I('get.parent_id'));
        $parent   = M('channel')->where(array('status'=>0,'id'=>$parentId))->find();

        $channel = M("channel")->where(array('status'=>0,'parent_id'=>$parentId))->order("sort asc,id asc")->select();
        foreach($channel as $k=>$v){
            $channel[$k]['articleCnt'] = M('article')->where(array('cid'=>$v['id']))->count();

        }
        $this->assign('parent',$parent);
        $this->assign('channel',$channel);
        $this->setPos(array('Article-channel'));

        $this->display();
    }

    /**
     * 频道列表 一级频道
     */
    public function channel(){

        $result     = M('channel')->where(array('status'=>0,'parent_id'=>0))->order('sort asc,id asc')->select();
        //$articlePer = myExplode(session('user.article_per'));
        $parentIds  = M('channel')->where(array('status'=>0))->getField('parent_id',true);
        foreach($result as $k=>$v){
            if(!isSuper() && !in_array($v['id'],$parentIds) ){
               // unset($result[$k]);
            }else{
                $result[$k]['sonCnt'] = M('channel')->where(array('status'=>0,'parent_id'=>$v['id']))->count();
            }
        }
        $this->assign('channel',$result);
        $this->setPos();
        $this->display();
    }

    /**
     * 删除频道
     */
    public function delChannel(){
        $id = intval(I('get.id'));
        if(!checkToken() || !IS_GET || !$id){
            $this->message();
        }
        if(isset($_GET['sonCnt']) && I('sonCnt',0) > 0){
            $this->message('该频道还有子频道,不能删除',1);
        }
        if(isset($_GET['articleCnt']) && I('articleCnt',0) > 0){
            $this->message('该频道还有文章,不能删除',1);
        }



        M('channel')->where(array('id'=>$id))->delete();
        $this->message('删除成功',0);
    }

    /**
     * 添加频道
     */
    public function addChannel(){
        $id        = intval(I('get.id'));
        $parent_id = intval(I('get.parent_id'));    //有parent_id就是二级频道

        $channel = M('channel')->where(array('id'=>$id))->find();
        if($channel && $channel['parent_id']){
            $parent_id = $channel['parent_id'];
        }
        $this->assign('parent_id',$parent_id);
        $this->assign('channel',$channel);
        $this->setPos(array('Article-channel'));
        $this->display();
    }

    /**
     * 编辑频道
     */
    public function addChannelHandle(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }

        $id        = intval(I('post.id'));

        $data = array(
            'name'       => trim(I('post.name')),
            'sort'       => intval(I('post.sort')),
            'show_nav'   => intval(I('post.show_nav')),
            'show_index' => intval(I('post.show_index')),
            'parent_id' => intval(I('post.parent_id'))
        );
        $msg = '添加成功';
        if($id){
            //编辑
            $data['id'] = $id;
            M("channel")->save($data);
            $msg = '编辑成功';
        }else{
            //添加
            $data['create_time'] = date("Y-m-d H:i:s");
            M('channel')->add($data);
        }
        $parentId = intval(I('post.parent_id'));
        if($parentId){
            $this->menuId['parent_id'] = $parentId;
            $this->message($msg,0,U('Article/sonChannel',$this->menuId));
        }else{
            $this->message($msg,0,U('Article/channel',$this->menuId));
        }
    }

    /**
     * 相册列表
     */
    public function image(){

        $count  = M("photo")->count();
        $page   = getpage($count,18);
        $images  = M("photo")->order("sort asc,id desc")->limit($page->firstRow.','.$page->listRows)->select();
        foreach($images as $k=>$v){
            if($v['show_index']){
                $images[$k]['remark'] = '(首页显示)'.$v['remark'];
            }
        }
        $this->assign('dataJsong',json_encode($images));
        $this->assign('pageShow',$page->show());
        $this->assign('images',$images);
        $this->setPos();
        $this->display();
    }

    /**
     * 上传相册
     */
    public function uploadImg3(){
        $result = parent::uploadImg();
        $flag = false;
        if($result['errcode'] == 0 && $result['url']){
            $data = array(
                'user_id' => session("user.id"),
                'url' => $result['url']
            );
            $flag = M("photo")->add($data);
            if($flag){
                session("msg",array('msg'=>"上传成功",'errcode'=>0));
            }else{
                session("msg",array('msg'=>"上传成功,但插入数据库失败",'errcode'=>1));

            }
        }

        $result = json_encode($result);

        echo "<script type='text/javascript'>";
        echo "parent.uploadCallBack('{$result}')";
        echo "</script>";
        exit;

    }


    /**
     * 删除相片
     */
    public function delImg(){

        $id = intval(I('id'));
        $img = M("photo")->where(array('id'=>$id))->find();
        if(!$img){
            $this->message('相片不存在',1);
        }
        $res = M("photo")->where(array('id'=>$id))->delete();
        if($res){
            unlink("./Public/uploadimg/".$img['url']);
            $this->message('删除成功');
        }else{
            $this->message('删除失败',1);
        }
    }

    /**
     * 修改相片
     */
    public function editImg(){
        $data = array(
            'id'     => intval(I('id')),
            'remark' => trim(I('remark')),
            'status'=> intval(I('status')),
            'show_index'=> intval(I('show_index')),
        );
        $res = M('photo')->save($data);
        if($res !== false){
            session("msg",array('msg'=>'修改成功','errcode'=>0));
            $this->ajaxReturn(array('status'=>0));
        }else{
            session("msg",array('msg'=>'服务器繁忙,修改失败,','errcode'=>1));
            $this->ajaxReturn(array('status'=>1));
        }
    }
}