<?php
/**
 * 招生录取
 * Created by PhpStorm.
 * User: Aous
 * Date: 2017/9/7
 * Time: 22:24
 */

namespace Admis\Controller;


class ApplyController extends BaseController{

    /**
     * 招生录取
     */
    public function index(){
        $this->common('student');
        $param = $_REQUEST;
        $param['export'] = 1;
        $this->assign('exportUrl',U('index',$param));
        $this->setPos();
        $this->display();
    }

    /**
     * 在线报名
     */
    public function apply(){
        $this->common('student_apply');
        $param = $_REQUEST;
        $param['export'] = 1;
        $this->assign('exportUrl',U('apply',$param));
        $this->setPos();
        $this->display('index');
    }

    /**
     * 删除在线报名信息
     */
    public function clearAppyStudent(){
        $table = "student_apply_".getYear();
        M($table)->where("1=1")->delete();
        $this->message("清空成功");

    }
    /**
     * 招生录取和在线报名公共方法
     * @param $t 表明
     */
    public function common($t){
        $where   = array();
        $where2  = ' 1=1 ';
        $class   = I('className');
        $sex     = I("sex");
        $keyword = trim(I("keyword"));
        $year    = getYear();
        $table   = $t;
        $apply_id = I('apply_id',-1);
        ;
        if($class){
            $where['class'] = arrya('eq',$class);
        }
        if($sex){
            $where['sex'] = arrya('eq',$sex);
        }
        if($apply_id!=-1){
            $con = $apply_id==0?'eq':'gt';
            $where['apply_id'] = array($con,0);
        }
        if($keyword){
            $where2 .= " and (name like '%{$keyword}%' ";
            $where2 .= " or card like '%{$keyword}%' ";
            $where2 .= " or school like '%{$keyword}%' ";
            $where2 .= " or phone like '%{$keyword}%' ";
            $where2 .= " or account_addr like '%{$keyword}%')";

        }
        $table   = createSutdentTable($table,$year);
        if(I('export')){
            //导出数据操作
            $student = M($table)->where($where)->where($where2)->order("id desc")->select();
            foreach($student as $k=>$v){
                $student[$k]['create_time'] = substr($v['create_time'],0,10);
            }
            $name = ($t=='student'?"招生录取":'在线报名').$year;
            exportExcel($student,$name);
        }else{
            $count   = M($table)->where($where)->where($where2)->count();
            $page    = getpage($count);
            $student = M($table)->where($where)->where($where2)->limit($page->firstRow.','.$page->listRows)->order("id desc")->select();
        }
        foreach($student as $k=>$v){
            $student[$k]['create_time'] = substr($v['create_time'],0,10);
        }
        $param = $_REQUEST;


        $this->assign('param',$param);
        $this->assign('year',$year);
        $this->assign('student',$student);
        $this->assign('pageShow',$page->show());
        $this->assign('page',$page);
        $this->assign('class',formatClass($this->config['class']));
        $this->assign('years',getYears());
        $this->assign('t',$t);
    }


    public function importStudent(){
        $this->setPos();
        $this->display();
    }

    public function importExcel(){
        if(!checkToken() || !IS_POST){
            if(!isset($_POST['file'])){
                $this->returnJs(array('errcode'=>1,'msg'=>'操作失败，请重试'));
            }else{
                $this->message();
            }
        }
        if(isset($_POST['file'])){
            $filename = I("file");
        }else{

            //文件上传
            $uploadRes = parent::uploadFile(array('xlsx'));
            if($uploadRes['errcode'] !=0){
                $this->returnJs($uploadRes);
            }
            $filename = $uploadRes['filename'];
        }
        $result = readExcel($filename);
        if($result['errcode']!=0){
            if(!isset($_POST['file'])){
                $this->returnJs(array('errcode'=>1,'msg'=>$result['msg']));
            }else{
                $this->message($result['msg'],1);
            }
        }
        $createTime = date("Y-m-d H:i:s");
        $data = array();
        $res = 0;
        for($i=1;$i<count($result['data']);$i++){
            $v = $result['data'][$i];
            if(!val($v[0]) || !val($v[1]) || !val($v[2])){ //姓名/身份证/报名专业必须有 不然报错 导入失败
                $res = $i;  //记录失败行数
                break;
            }

            $data[] = array(
              'name'        => val($v[0]),
              'card'        => val($v[1]),
                'class'     => val($v[2]),
                'phone'     => val($v[3]),
                'birthday'  => val($v[4]),
              'school'      => val($v[5]),
              'sex'         => val($v[6])?val($v[6]):'未知',
              'parent_phone'=> val($v[7]),
              'qq'          => val($v[8]),
              'weixin'      => val($v[9]),
               'nation'     => val($v[10]),
              'politics'    => val($v[11])?val($v[11]):'团员',
              'health'      => val($v[12]),
              'native'      => val($v[13]),
              'account_addr'=> val($v[14]),
              'addr'        => val($v[15]),
              'poverty'     => val($v[16])?val($v[16]):'否',
              'low_security'=> val($v[17])?val($v[17]):'否',
               'user_id' => session('user.id'),
               'create_time' => $createTime
            );
        }
        if(isset($_POST['file'])){
            if($res !==0){
                $this->message('导入失败，第'.$res."行数据有误(姓名/身份证/报名专业必须有)",1);
            }
            if(!count($data)){
                $this->message("excel没有数据");
            }
            $table = createSutdentTable('student');
            $res = M($table)->addAll($data);

            if($res){
                $this->message('成功导入'.count($data)."个学生");
            }else{
                $this->message('导入失败，请联系管理员',1);
            }
        }else{
            if($res !==0){
                $uploadRes['errcode'] = 1;
                $uploadRes['msg'] = '导入失败，第'.$res."行数据有误(姓名/身份证/报名专业必须有)";
            }
            //文件上传 记录总数
            $uploadRes['count'] = count($data);
            $this->returnJs($uploadRes);
        }

    }

    /**
     * 编辑录取学生
     */
    public function edit(){
        $t       = I('t');
        $year    = getYear();
        $student = M($t."_".$year)->where(array('id'=>intval(I('id'))))->find();
        $adminUser = M('admin')->where(array('id'=>$student['user_id']))->getField('truename');
        $message = "来源：在线报名<br/>";

        if($t == 'student'){
            //来源,创建者、录取审批人,创建时间、录取时间,查询录取情况
            if(!$student['apply_id']){
                $message = "来源：统招<br/>创建人：{$adminUser}<br/>创建时间：{$student['create_time']}";
            }else{
                $message .= "录取审批人：{$adminUser}<br/>录取时间：{$student['create_time']}";
                if($student['query_time'] > 0){
                    $message .= "    (该学生于".$student['query_time']." 在本网站查询到了已被录取)";
                }
            }
            $this->assign('backUrl',U('index',array('mid'=>$this->mid,'pid'=>$this->pid)));
        }else{
            //来源，申请时间
            $message .= "在线报名时间：".$student['create_time'];
            $this->assign('backUrl',U('apply',array('mid'=>$this->mid,'pid'=>$this->pid)));
        }

        $this->assign('message',$message);
        $this->assign('class',formatClass($this->config['class']));
        $this->setPos();
        $this->assign('student',$student);
        $this->display();
    }

    /**
     * 编辑
     */
    public function editHandle(){
        if(!checkToken() || !IS_POST){
            $this->message();
        }
        $id      = intval(I('id'));      //学生id
        $t       = I('t');       // 表明
        $status  = intval(I('status')); //status = 1是 审核通过 0是不通过
        $year    = getYear(); //数据所在年份

        $table = createSutdentTable($t,$year);

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
        );

        if($status){
            //审核通过在线报名学生
            $data['apply_id'] = $id;
            $data['user_id'] = session("user.id");
            $data['create_time'] = date("Y-m-d H:i:s");
            $res = M($table)->where(array('id'=>$id))->delete();
            if($res){
                $res = M(createSutdentTable('student',$year))->add($data);
                if($res){
                    $this->message('录取成功',0,U('apply',array('mid'=>$this->mid,'pid'=>$this->pid)));
                }
            }
        }else{
            //编辑学生
            M($table)->where(array('id'=>$id))->save($data);
            $url = U('index',array('mid'=>$this->mid,'pid'=>$this->pid));
            if($t !='student'){
                $url = U('apply',array('mid'=>$this->mid,'pid'=>$this->pid));
            }
            $this->message('编辑成功',0,$url);
        }
        $this->message('操作失败，请联系管理员',1);

    }

    /**
     * 删除录取学生
     */
    public function del(){
        if(!checkToken() || !IS_GET){
            $this->message();
        }
        $year    = getYear();
        $t       = I('t');
        $res = M($t.'_'.$year)->where(array('id'=>intval(I('id'))))->delete();
        if($res){
            $this->message('删除成功');
        }else{
            $this->message('删除失败',1);
        }
    }

    private function returnJs($result){
        $result = json_encode($result);
        echo "<script type='text/javascript'>";
        echo "parent.uploadCallBack('{$result}')";
        echo "</script>";
        exit;
    }
}