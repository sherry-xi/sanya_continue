<?php
namespace Admis\Controller;
class ApplyNewController extends BaseController{

    /**
     * 网上报名
     */
    public function index(){

        $where = ['status'=>0];

        if(I("apid")){
            $where['apply_type'] = I('apid');
        }
        $count   = M("apply")->where($where)->count();
        $page    = getpage($count,10);
        $student = M("apply")->where($where)->order("id desc")->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        $apartment = $this->getApartment();

        foreach($student as $k=>$v){
            $aprt = $apartment[$v['apply_type']];
            $student[$k]['apply_type'] = $aprt['name'];
            $student[$k]['apply_major'] = $aprt['major'][$v['apply_major']]['name'];
        }
        $this->assign("apartment",$apartment)
        ;

        $this->assign('student',$student);
        $this->assign('pageShow',$page->show());
        $this->assign('page',$page);
        $this->assign("apply_major",C("apply_major"));
        $this->setPos();
        $this->display();

    }

    /**
     * 删除
     */
    public function delete(){
        M("apply")->where(['id'=>I('id')])->update(['status'=>1]);
        $this->success('删除成功');
    }

    /**
     * 导出数据
     */
    public function export(){
        $field = [
            'id'            => 'ID',
            'name'          => '姓名',
            'sex'           => '性别',
            'mobile'        =>'系联手机',
            'apply_type'    =>	'报名层次',
            'apply_major'   =>	'名报专业',
            'province' 	    =>'在所省份',
            'birthday'      => '生日',
            'nation' 	    =>'族民',
            'idcard' 	    =>'份证身',
            'is_job'        =>'是否就业',
            'residence'     =>'户口所在地',
            'gradute_school' =>'业毕学校',
            'education'     => '学历',
            'graduate_date' => '毕业日期',
            'graduate_major' => '业毕专业',
            'graduate_id'   => '毕业号',
             'email'        => '邮件',
             'address'      => '信通地址',
            'phone'         => '号码',
              'company'     => '作工单位',
              'position'    => '位职',
              'company_phone '=>'位单电话',
              'company_address' =>'位单地址',
              'remark'       =>'注备',
              'created_at'   => '报名时间'
        ];

        $where = ['status'=>0];

        $data   = M("apply")->field(array_keys($field))->where($where)->select();
        $apartments = $this->getApartment();
        foreach($data as $k=>$v){
            $data[$k]['sex'] = $v['sex']?'女':'男';
            $apartment = $apartments[$v['apply_type']];
            $data[$k]['apply_type'] = $apartment['name'];
            $data[$k]['apply_major'] = $apartment['major'][$v['apply_major']]['name'];
        }
        exportExcelData($data,$field,'网上报名表'.date("Ymd"));
    }

    /*
     * 查看
     */
    public function show(){
        $data = M("apply")->find(I('id'));
        $apartment = M("apartment")->where(['id'=>$data['apply_type']])->getField('name');
        $major = M("_major")->where(['id'=>$data['apply_major']])->getField('name');

        $this->assign("apartment",$apartment);
        $this->assign("major",$major);
        $this->assign('data',$data);
        $this->setPos(['ApplyNew-index']);
        $this->display();
    }

    /*
    * 编辑
    */
    public function edit(){
        $data = M("apply")->find(I('id'));
        $this->assign('data',$data);
        $this->setPos(['ApplyNew-index']);

        $apartment = $this->getApartment();
        $major = $apartment[$data['apply_type']]['major'];
        $this->assign("jsonApartment",json_encode($apartment));
        $this->assign("major",$major);
        $this->assign("apartment",$apartment);
        $this->assign("province",C('province'));
        $this->display();
    }

    /*
    * 编辑
    */
    public function editHandle(){
        M("apply")->save(I(''));
        $this->message('编辑成功');
    }
}
?>