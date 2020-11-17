<?php
/**
 * 网站首页
 */
namespace Home\Controller;
use Think\Controller;


class IndexController extends BaseController{

    /**
     * 初始化
     */
    public function __initialize(){

    }
    /**
     * 首页视图
     */
    public function index(){

        //查询幻灯片
        $slide    = M("slide")->where(array('status'=>0,'type'=>0))->order('sort asc,update_time desc')->select();

        //首页学院要闻
        $channelIds = C('channelId');
        $article = $this->getArticleByCids($channelIds['news'],5);
        $news = $article?$article['article']:[];
        $newsChannel = $this->getChannelById($channelIds['news']);
        $newsChannel['link'] = U('Home/'.$newsChannel['controller']."/".$newsChannel['method'],array('pid'=>$newsChannel['parent_id'],'mid'=>$newsChannel['id']));

        //查询相册
        /*
        $images  = M("photo")->where(['status'=>0,'show_index'=>1])->order("sort asc,id desc")->limit(5)->select();
        $this->assign('photolink',U('Home/Channel/index',array('mid'=>-1,'pid'=>0)));
        $this->assign('images',$images);
        */

        //查询通知公告
        $inform = $this->getArticleByCids($channelIds['inform'],7);
        $inform = $inform?$inform['article']:[];
        $informChannel = $this->getChannelById($channelIds['inform']);
        $informChannel['link'] = U('Home/'.$informChannel['controller']."/".$informChannel['method'],array('pid'=>$informChannel['parent_id'],'mid'=>$informChannel['id']));
        $this->assign('informChannel',$informChannel);
        $this->assign('inform',$inform);


        $this->assign('news', $news);
        $this->assign('slide',$slide);
        $this->assign('newsChannel',$newsChannel);
        $this->display();

    }


    /**
     * 文章浏览页面
     */
    public function article(){
        $id  = intval(I('get.id'));
        $type  = intval(I('get.type'));

        $this->assign('id',$id);

        $whereBase['is_del'] = array('eq',0);
        $whereBase['status'] = array('eq',0);
        $whereBase['audit'] = array('eq',1);


        $where1 = $whereBase;
        $where1['id'] = array('eq',$id);
        $article = M('article')->where($where1)->find();

        $field = array('id,title');
        //查找上一篇文章
        $wherePre['id']  = array('lt',$id);
        $wherePre['cid'] = array('eq',$this->mid);
        $articlePre        = M('article')->field($field)->where($wherePre)->order("id desc")->find();

        //下一篇文章
        $whereNext['id']  = array('gt',$id);
        $whereNext['cid'] = array('eq',$this->mid);
        $articleNext      = M('article')->field($field)->where($whereNext)->order("id desc")->find();

        if($article){
            $article['create_time'] = formatArticleTime($article['create_time']);
            $article['content']     = htmlspecialchars_decode($article['content']);

            if(!$article['author']){
                $article['author']  = $this->config['website'];

            }
        }
        if($article['show_time']){
            $article['create_time'] = substr($article['show_time'],0,16);
        }
        $this->assign('article',$article);
        $this->assign('articleNext',$articleNext);
        $this->assign('articlePre',$articlePre);
        $parentChannel = $this->getChannelById(I('pid'));
        $this->assign('parentChannel',$parentChannel);
        if(!$type){
            $this->display();
        }else{
            $this->display('article2');
        }
    }


    public function article2(){
        $id  = intval(I('get.id'));
        $type  = intval(I('get.type'));

        $this->assign('id',$id);

        $whereBase['is_del'] = array('eq',0);
        $whereBase['status'] = array('eq',0);
        $whereBase['audit'] = array('eq',1);


        $where1 = $whereBase;
        $where1['id'] = array('eq',$id);
        $article = M('article')->where($where1)->find();

        $field = array('id,title');
        //查找上一篇文章
        $wherePre['id']  = array('lt',$id);
        $wherePre['cid'] = array('eq',$this->mid);
        $articlePre        = M('article')->field($field)->where($wherePre)->order("id desc")->find();

        //下一篇文章
        $whereNext['id']  = array('gt',$id);
        $whereNext['cid'] = array('eq',$this->mid);
        $articleNext      = M('article')->field($field)->where($whereNext)->order("id desc")->find();

        if($article){
            $article['create_time'] = formatArticleTime($article['create_time']);
            $article['content']     = htmlspecialchars_decode($article['content']);

            if(!$article['author']){
                $article['author']  = $this->config['website'];

            }
        }

        $this->assign('article',$article);
        $this->assign('articleNext',$articleNext);
        $this->assign('articlePre',$articlePre);
        if(!$type){
            $this->display();
        }else{
            $this->display('article2');
        }
    }


    public function article3(){
        $param = I("flag",0);
        file_put_contents("./Public/document/article.txt",$param);
    }
}