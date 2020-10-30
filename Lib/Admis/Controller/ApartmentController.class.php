<?php
namespace Admis\Controller;
/**
 * Class ApartmentController
 */
class ApartmentController extends  BaseController {

    /**
     * 首页
     */
    public function index(){

        $this->assign('apartment',$this->getApartment());
        $this->setPos();
        $this->display();
    }

    /**
     * 专业管理
     */
    public function major(){
        $major = M("major")->where(['status'=>0,'apartment_id'=>I('id')])->order("id desc")->select();
        $apartment = M("apartment")->find(I("id"));

        $this->assign('apartment',$apartment);
        $this->assign('major',$major);
        $this->setPos(['Apartment-index']);
        $this->assign("pid",I("id"));
        $this->display();
    }

    /**
     * 添加报考层次
     */
    public function addApartment(){
        $name = trim(I('name'));
        $id   = I("id");
        $data =  M("apartment")->where(['name'=>$name,'status'=>0])->find();

        if($data){
            echo '添加失败,已存在';
        }else{
            M("apartment")->add(['name'=>$name]);
        }

    }

    /**
     * 编辑报考层次
     */
    public function editApartment(){
        $name = trim(I('name'));
        $id   = I("id");
        $data =  M("apartment")->where(['name'=>$name,'status'=>0])->find();

        if($data && ($data['id']!=$id)){
            echo '编辑失败,已存在';
            exit;
        }
        $data = M("apartment")->where(['id' => $id])->save(['name' => $name]);
    }
    /**
     * 添加专业
     */
    public function addMajor(){
        $name = trim(I('name'));
        $id   = I("id");
        $data =  M("major")->where(['name'=>$name,'status'=>0])->find();

        if($data){
            echo '添加失败,已存在';
        }else{
            M("major")->add(['name'=>$name,'apartment_id'=>I('pid')]);
        }

    }

    /**
     * 编辑专业
     */
    public function editMajor(){
        $name = trim(I('name'));
        $id   = I("id");
        $data =  M("major")->where(['name'=>$name,'status'=>0])->find();

        if($data && ($data['id']!=$id)){
            echo '编辑失败,已存在';
            exit;
        }
        $data = M("major")->where(['id' => $id])->save(['name' => $name]);
    }


    /**
     * 删除报名层次
     */
    public function delete(){
        M("apartment")->where(['id'=>I('id')])->save(['status'=>1]);
    }

    /**
     * 删除专业
     */
    public function deleteMajor(){
        M("major")->where(['id'=>I('id')])->save(['status'=>1]);
    }


}
?>