<?php
/**
 * 首页管理
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/8/25
 * Time: 21:02
 */

namespace Admis\Controller;


class IndexManageController extends BaseController{

    private $typeList;
    public function __construct(){
        parent::__construct();
        $this->typeList  = array('index','scroll','link');
    }

    /**
     * 幻灯片管理
     */
    public function index(){
        $this->setPos();
        $this->showList(0);
    }

    /**
     * 滚动栏管理
     */
    public function scroll(){
        $this->setPos();
        $this->showList(1);

    }

    /**
     * 友情链接管理
     */
    public function link(){

        //获取所有父级频道

        $this->setPos();
        $this->showList(2);
    }

    /**
     * 根据parent_id获取频道
     * @param string $status
     * @param int $parent_id
     * @return mixed
     */
    private function getChannelByParentId($status='',$parent_id = 0){
        $where = array();
        if($status!==''){
            $where['status']    = $status;
        }
        $where['parent_id'] = $parent_id;
        $result = M("channel")->field("id,name")->order("sort asc,id asc")->where($where)->select();
        $channel = array_merge(array(array('id'=>'0','name'=>'首页')),$result);
      
        return $channel;
    }

    /**
     * 公共页面列表显示
     * @param $type
     */
    public function showList($type){
        $where  = array('type'=>$type);
        $order  = "status asc,sort asc,update_time desc";
        $count  = M("slide")->where($where)->count();
        $page   = getpage($count);

        $data = M('slide')->where($where)->order($order)->limit($page->firstRow.','.$page->listRows)->select();
        if($type == 2){
            $res     = $this->getChannelByParentId();
            $channel = array();
            foreach($res as $key=>$value){
                $channel[$value['id']] = $value['name'];
            }
            foreach($data as $k=>$v){
                $cids = explode(',',$v['cid']);
                $data[$k]['channel']  = '';
                $i=1;
                foreach($cids as $key=>$cid){


                    $data[$k]['channel'] .= $channel[$cid]."&nbsp;&nbsp;";
                    if($i%2==0){
                        $data[$k]['channel']  .= "<br/>";
                    }
                    $i++;
                }

            }
        }
        $this->assign('pageShow',$page->show());
        $this->assign('type',$type);
        $this->assign('data',$data);
        $this->display('index');

    }

    /**
     * 添加编辑
     */
    public function add(){
        $id     = intval(I('get.id'));
        $type   = intval(I('get.type'));
        $slide  = array();

        if($id){
            $slide = M('slide')->where(array('id'=>$id))->find();
            $this->assign('slide',$slide);
        }
        if($type == 2){
            $cid = explode(',',$slide['cid']);
            $channel = $this->getChannelByParentId();
            foreach($channel as $k=>$v){
                $channel[$k]['checked'] = in_array($v['id'],$cid)?'checked="checked"':'';
            }
            $this->assign('channel',$channel);
        }

        $this->assign('type',$type);
        $this->setPos(array('IndexManage-'.$this->typeList[$type]));
        $this->display();
    }

    /**
     * 执行添加或编辑
     */
    public function addHandle(){

        if(!checkToken() || !IS_POST){
            $this->message();
        }

        $id   = intval(I('post.id'));
        $type = intval(I('post.type'));

        $data = array(
            'title'     => trim(I('post.title')),
            'link'      => trim(I('post.link')),
            'status'    => trim(I('post.status')),
            'type'      => $type,
            'img'       => trim(I('post.img')),
            'sort'      => intval(I('sort'))
        );
        if($type == 2){
            $cid  = I('post.cid');
            $data['cid'] = implode(',',$cid);
        }
        $msg = '添加成功';
        if($id){
            //更新
            $data['id'] = $id;
            M('slide')->save($data);
            $msg = '编辑成功';
        }else{
            $data['cid'] =  $data['cid']? $data['cid']:'';
            //添加
            $data['create_time'] = date("Y-m-d H:i:s");
             M('slide')->add($data);
        }
        $this->message($msg,0,U($this->typeList[$type],array('mid'=>$this->mid,'pid'=>$this->pid)));
    }

    public function del(){
        if(!checkToken() || !IS_GET){
            $this->message();
        }

        $id = intval(I('get.id'));
        $res = M('slide')->where(array('id'=>$id))->delete();
        if($res){
            $this->message('删除成功',0);
        }else{
            $this->message('删除失败',1);
        }
    }

    /**
     * 图片上传
     */
    public function upload(){
        $result = parent::uploadImg();
        $result = json_encode($result);
        echo "<script type='text/javascript'>";
        echo "parent.uploadCallBack('{$result}')";
        echo "</script>";
        exit;
    }



}