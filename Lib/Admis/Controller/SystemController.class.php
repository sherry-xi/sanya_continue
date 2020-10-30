<?php
/**
 * 系统设置
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/8/22
 * Time: 22:35
 */

namespace Admis\Controller;

class SystemController extends BaseController{

    /**
     * 系统设置页面
     */
    public function index(){

        $this->setPos();
        $this->display();
    }

    /**
     * 首页图片链接设置
     */
    public function img(){

        $config = formatByKey(M('config')->where(['type'=>6])->select(),'keyword');
        $keys = C('web-links');
        $data = [];

        foreach($keys as $key=>$value){
            $data[$key] = [
                'name' => $key,
                'text' => $value,
                'img' => $config[$key."_img"]?$config[$key."_img"]['value']:'',
                'link' => $config[$key."_link"]?$config[$key."_link"]['value']:'',
            ];
        }
        $keyword = I('get.keyword');
        $this->assign('data',$data);
        $this->setPos();
        $this->display();
    }

    /*
 * 首页图片链接设置
 */
    public function imgHandle(){
        $data = I('');
        unset($data['pid']);
        unset($data['mid']);
        unset($data['token']);

        foreach($data as $k=>$value){
            if(M("config")->where(['keyword'=>$k])->find()){
                M("config")->where(['keyword'=>$k])->save(['value'=>trim($value)]);
            }else{
                M("config")->add(['value'=>trim($value),'keyword'=>$k,'type'=>6]);
            }

        }
        $this->message('设置成功',0);

    }


    /**
     * 首页链接图片上传
     * @return array|void
     */
    public function uploadLinkImg(){
        $result = parent::uploadImg();
        if(!$result['url']){
            $result = array('errcode'=>1,'上传失败');
        }
        $result = json_encode($result);
        echo "<script type='text/javascript'>";
        echo "parent.uploadCallBack('{$result}')";
        echo "</script>";
        exit;
    }


    /**
     * 图片上传
     * @return array|void
     * @deprecated  丢弃
     */
    public function uploadImg(){
        $result = parent::uploadImg();
        if($result['url']){
            //写入数据库
            M("config")->where(array('keyword'=>$result['keyword']))->delete();
            $data = array(
                'keyword' => $result['keyword'],
                'value' => $result['url'],
                'type' =>1
            );
            M('config')->add($data);
        }else{
            $result = array('errcode'=>1,'上传失败');
        }
        $result = json_encode($result);
        echo "<script type='text/javascript'>";
        echo "parent.uploadCallBack('{$result}')";
        echo "</script>";
        exit;
    }

    /**
     * 系统设置
     */
    public function baseSet(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }
        $data   = $_REQUEST;
        if($data['type'] == 2){
            if($data['applyBegin'] > $data['applyEnd']){
                $this->message('报名开始时间不能大于结束时间',1);
            }
            if($data['queryBegin'] > $data['queryEnd']){
                $this->message('录取查询开始时间不能大于结束时间',1);
            }
        }

        $result = array();
        $type   = intval(I('post.type'));
        $except = array('type','pid','mid','token');
        foreach($data as $k=>$v){
            if(in_array($k,$except)){
                continue;
            }
            $result[] = array(
                'keyword' => trim($k),
                'value'   => trim($v),
                'type'    => $type
            );
        }
        M('config')->where(array('type'=>$type))->delete();
        M('config')->addAll($result);
        $this->message('设置成功',0);
    }
}