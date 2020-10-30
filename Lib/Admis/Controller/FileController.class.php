<?php
namespace Admis\Controller;

class FileController extends BaseController{

    /**
     * 资料下载管理
     */
    public function index(){
        $where = [''];

        $channel = M("channel")->where(['parent_id'=>C("channelId.download")])->select();
        $channel = array_column($channel,null,'id');
        $count   = M("file")->count();
        $page    = getpage($count,10);

        $files = M('file')->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();

        foreach($files as $k=>$file){
            $files[$k]['channel'] = $channel[$file['channel_id']]?$channel[$file['channel_id']]['name']:'未分类';
            $files[$k]['size'] = file_size_format($file['size']);
        }
        $rootPath = C("DOMAIN").str_replace('.','',C("UPLOAD_DOCUMENT"));
        $this->assign('rootPath',$rootPath);
        $this->assign('files',$files);
        $this->assign('pageShow',$page->show());
        $this->setPos();
        $this->display();
    }

    /**
     * 删除资料
     */
    public function delete(){
        $file = M("file")->find(I("id"));
        M("file")->delete($file['id']);
        unlink(C("UPLOAD_DOCUMENT").$file['path']);
        $this->message('删除成功');
    }

    /**
     * 上传文件
     */
    public function upload(){

        $channel = M("channel")->where(['parent_id'=>C("channelId.download"),'status'=>0])->select();
        $this->setPos(['File-index']);

        $this->assign('fileChannelId',C("channelId.download"));
        $this->assign('channel',$channel);
        $this->display();
    }

    /**
     * 文件上传
     */
    public function uploadHandle(){
        $maxFileSize = 1024*1024*50; //最大50m
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     $maxFileSize ;// 设置附件上传大小
        //$upload->exts      =     array('jpg', 'jpeg', 'png', 'JPEG','gif','JPG');// 设置附件上传类型
        $upload->rootPath  =     C("UPLOAD_DOCUMENT"); // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录

        // 上传文件
        $info   =   $upload->upload();
        if($upload->getError()) {// 上传错误提示错误信息
            $this->message('上传失败'.$upload->getError());
            return ;
        }

        $info = $info['file'];
        $file = [
            'name' => $info['name'], //文件原名称
            'type' => $info['type'],
            'size' => $info['size'], //字节 大小
            'path' => $info['savepath'].$info['savename'],
            'user'  => session("user.username"),
            'channel_id' => I("channelId")
        ];
        M("file")->add($file);
        $this->message('上传成功');
    }
}