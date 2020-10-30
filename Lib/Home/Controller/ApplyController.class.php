<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 网上报名
 * Class ApplyController
 */
class ApplyController extends BaseController{

    public function index(){
        $this->assign("province",C("province"));

        $apartment = $this->getApartment();
        $this->assign('jsonApartment',json_encode($apartment));
        $this->assign("apply_type",$apartment);
        $this->assign("apply_major",array_shift($apartment)['major']);
        $this->display();
    }

    /**
     * 添加报名
     */
    public function add(){
        $data = I('');

        $data['created_at'] = date("Y-m-d H:i:s");
        $data['ip']    = get_client_ip();
        M("apply")->add($data);
        $this->success('报名成功');
    }

    /**
     * 自学考试报名系统
     */
    public function testApply(){
        $this->display();
    }
}