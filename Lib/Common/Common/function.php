<?php
/**
 * 自定义公共函数
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/14
 * Time: 14:45
 */


 function read_all($dir){
		 if(!is_dir($dir)){
			 exit('文件夹不存在');
		 }
		$files = array();
		 $handle = opendir($dir);
	 
		 if($handle){
			 while(($file = readdir($handle)) !== false){
					
					if($file!='.' && $file != '..' && $file != 'dbback.sh'){
						$filename = $dir.'/'.$file;
						$filesize = file_size_format(filesize($filename));
						

						$files[] =  array(
							'file' => $file,
							'version' => substr($file,0,14),
							'datetime' => date('Y-m-d H:i:s',fileatime($filename)),
							'size' => $filesize,
							'filename' => $filename,
							'filepath' => '/dbbase/'.$file
						);
					}
			   }
		  }
		return $files;
    }

function downfile($filename){
	$filepath = './dbbase/'.$filename;
	 $date=date("Ymd-H:i:m");
	 Header( "Content-type:  application/octet-stream "); 
	 Header( "Accept-Ranges:  bytes "); 
	 Header( "Accept-Length: " .filesize($filename));
	 header( "Content-Disposition:  attachment;  filename= ".$filename); 
	 echo file_get_contents($filepath);
	 readfile($filepath); 
}
/**
 * 文件大小格式化
 * @param integer $size 初始文件大小，单位为byte
 * @return array 格式化后的文件大小和单位数组，单位为byte、KB、MB、GB、TB
 */
function file_size_format($size = 0, $dec = 2) {
    $unit = array("B", "KB", "MB", "GB", "TB", "PB");
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1024;
        $pos++;
    }
    $result['size'] = round($size, $dec);
    $result['unit'] = $unit[$pos];
    return $result['size'].$result['unit'];
}

/**
 * 检查是否是用户名 [/^\w{4,12}$/, "请填写4-12位数字、字母、下划线
 * @param $username 用户名
 */
 function checkUsername($username){
    if(!preg_match('/^\w{4,12}$/',$username)){
        return false;
    }
     return true;
}

/**
 * 检查是否是合法的密码  /^[\S]{6,16}$/, "6-16位字符，不能包含空格"
 * @param $password
 */
function checkPassword($password){
    if(!preg_match('/^[\S]{6,16}$/',$password)){
        return false;
    }
    return true;
}

/**
 * token 检查
 */
function checkToken(){
    return true; //20170910 由于 token有一个bug 先关闭验证
    if(session('token') != '' && (session('token') ==I('get.token') || session('token') ==I('post.token'))){
       return true;
    }
    return false;
}

function getpage($count, $pagesize = 10){
    $p = new Think\Page($count, $pagesize);
    $p->setConfig('header', '共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页&nbsp;&nbsp;');
    $p->setConfig('first', '首页');
    $p->setConfig('prev', '上一页');
    $p->setConfig('next', '下一页');
    $p->setConfig('last', '尾页面');
    $p->setConfig('theme', '%HEADER%%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%');
    return $p;
}

/**
 * @param $data
 * @return string
 */
function myImpole($data){
    return $data?implode(',',$data):"";
}

function myExplode($data){
    return $data?explode(',',$data):array();
}

/**
 * 获取域名
 */
function getDomain(){
    return "http://".$_SERVER['SERVER_NAME'];
}

/**
 * 保存操作日志
 * @param int $result 操作结果 0默认 1成功 2失败
 */
function saveLog(){
    if(ACTION_NAME == 'verify'){
        return false; //验证码不记录日志
    }
    $param = array_merge($_GET,$_POST);
    foreach($param as $k=>$v){
        if($k >100){
            //参数数量太大也不要了
            break;
        }
        if(in_array($k,array('password'))){
            unset($param[$k]) ; //不记录一些敏感数据
            continue;
        }
        if(strlen($v) > 200){ //太长的参数不要了
            unset($param[$k]);
        }

    }
    $log = array(
        'admin_id'  => intval(session('user.id')),
        'ip'        => get_client_ip(),
        'controller'=> CONTROLLER_NAME,
        'action'    => ACTION_NAME,
        'param'     => json_encode($param),
    );

    M('admin_log')->add($log);
}

/**
 * 格式化配置数据
 * @param $config 返回key=>value键值对类型数组
 */
function formatConfig($config){
    $result = array();
    foreach($config as $k=>$v){
        if($v['keyword'] == 'right'){
            $patten = array("\r\n", "\n", "\r");
            $result[$v['keyword']."_msg"] = str_replace($patten,"<br/>",$v['value']);
        }
        $result[$v['keyword']] = $v['value'];
    }
    return $result;
}

/**
 * 将数组格式化成 key=>value
 * @param $data
 * @param $key
 * @return array
 */
function formatByKey($data,$key){
    $result = array();
    foreach($data as $v){
        $result[$v[$key]] = $v;
    }
    return $result;
}

/**
 * token生成
 */
function makeToken(){
    $token = md5(time().rand(1,10000));
    session('token',$token);
    return $token;
}

/**
 * 获取二位数组制定的键值
 * @param $arr
 * @param $key
 */
function getArrKeyValue($arr,$key){
    $data = array();
    foreach($arr as $k=>$v){
        $data[] = $v[$key];
    }
    return $data;
}

/**
 * 格式化文章时间
 * @param $time
 * @return bool|string
 */
function formatArticleTime($time){
    return date("Y/m/d H:i",strtotime($time));
}

function p($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

/**
 * 判断是否是超级管理员
 * @return bool
 */
function isSuper(){
    return session('user.permission') == 'all';
}

/**
 * @param $channel
 * @return mixed
 */
function filterChannel($channel){
    if(isSuper()){
        return $channel;
    }
    $articlePer = myExplode(session('user.article_per'));
    foreach($channel as $k=>$parent){
        foreach($parent['son'] as $key=>$son){
            if(!in_array($son['id'],$articlePer)){
                //没有权限
                unset($channel[$k]['son'][$key]);
            }
        }
        if(!$channel[$k]['son']){
            unset($channel[$k]);
        }
    }
    return $channel;

}

/**
 * 二维数组根据某字段排序
 * @param $arrData
 * @param $sort
 * @return mixed
 */
function array2Sort($arrData,$field,$sort='SORT_ASC'){
    /*
       $sort = 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
       $field= 'age',       //排序字段
   */
    $arrSort = array();
    foreach($arrData AS $uniqid => $row){
        foreach($row AS $key=>$value){
            $arrSort[$key][$uniqid] = $value;
        }
    }
    array_multisort($arrSort[$field], constant($sort), $arrData);
    return $arrData;
}

/**
 * 格式化专业班级数据
 * @param $class
 */
function formatClass($class){

    $class = explode("\n",$class);

    foreach($class as $k=>$v){
        if(!trim($v)){
            continue;
        }

        $class[$k] = trim($v);
    }
    return $class;
}

/**
 * 根据配置文件生成css样式
 * @param $config
 */
function makeCss($config){
    $style = '';
    foreach($config as $k=>$v){
        if(!preg_match("/-bg|-color$/",$k)){
            continue;
        }
        $key = explode('-',$k);
        $name = 'color:';
        if($key[1] == 'bg'){
            $name = "background:";
        }
        $style .= " .".$k."{";
        if($v){
            $style .= $name.$v." !important;";
        }
        $style .= "}";
    }
    return  $style;

}

/**
 * 获取当前或者 根据传参 得到年份
 */
function getYear(){
    $year = date("Y");
    if(I('year')){
        $year = intval(I('year'));
    }
    return $year;
}

/**
 * 获取年份数据 从2017年开始
 */
function getYears(){
    $year = date("Y");
    $years = array();
    for($i=2017;$i<=$year;$i++){
        $years[] = $i;
    }
    rsort($years);
    return $years;
}

/**
 * 返回变量值 如果没有就返回 default
 * @param $value
 * @param string $default
 */
function val($value,$default=''){
    return isset($value)?trim($value):$default;
}

function readExcel($fileName){
    if(!file_exists($fileName)){
        return array('errcode'=>1,'msg'=>'excel文件不存在');
    }
    if(!preg_match("/.xlsx$/",$fileName)){
        return array('errcode'=>2,'msg'=>'excel文件格式不对,必须是2007以上版本的excel,*xlxs');
    }
    include 'PHPExcel/Classes/PHPExcel/IOFactory.php';

    $inputFileType = 'Excel2007';//这个是计xlsx的  //$inputFileType = 'Excel5';    //这个是读 xls的
    $inputFileName =  $fileName; //$inputFileName = './sampleData/example2.xls';
    $objReader     = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel   = $objReader->load($inputFileName);

    $objWorksheet   = $objPHPExcel->getActiveSheet();
    $highestColumn  = $objWorksheet->getHighestColumn();
    $totalRows      = $objWorksheet->getHighestRow();//取得总列数
    $totalCols      = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
    $data = array();
    for ($row = 1;$row <= $totalRows;$row++) {
        for ($col = 0;$col < $totalCols;$col++) {
            $strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
        $data[] = $strs;
    }
    return array('errcode'=>0,'data'=>$data);
}

function exportExcelData($data,$field,$name='Excel'){

    include 'PHPExcel/Classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $title = array_values($field);
    $keys = array_keys($field);

    $col = 65;
    $width = array(
        'A' => 8,
        'C' => 23,
        'D' => 15,
        'L' => 8,
        'M' => 8,
        'N' => 8,
        'H' => 8,
    );
    foreach($title as $k=>$v){
        $num=1;
        $column = chr($col++);
        $w = isset($width[$column])?$width[$column]:20;

        $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($w);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column.$num, $v);
    }

    /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
    foreach($data as $k => $v){
        $num=$k+2;
        $col = 65;

        //$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($col++).$num, $k+1);
        foreach($keys as $key){

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($col++).$num, $v[$key]);
        }

        /*
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue(chr($col++).$num, $k+1)
            ->setCellValue(chr($col++).$num, $v['name'])
            ->setCellValue(chr($col++).$num, $v['card']." ")
            ->setCellValue(chr($col++).$num, $v['class'])
            ->setCellValue(chr($col++).$num, $v['phone']." ")
            ->setCellValue(chr($col++).$num, $v['birthday'])
            ->setCellValue(chr($col++).$num, $v['school'])
            ->setCellValue(chr($col++).$num, $v['sex'])
            ->setCellValue(chr($col++).$num, $v['parent_phone']." ")
            ->setCellValue(chr($col++).$num, $v['qq']." ")
            ->setCellValue(chr($col++).$num, $v['weixin'])
            ->setCellValue(chr($col++).$num, $v['nation'])
            ->setCellValue(chr($col++).$num, $v['politics'])
            ->setCellValue(chr($col++).$num, $v['health'])
            ->setCellValue(chr($col++).$num, $v['native'])
            ->setCellValue(chr($col++).$num, $v['account_addr'])
            ->setCellValue(chr($col++).$num, $v['addr'])
            ->setCellValue(chr($col++).$num, $v['poverty'])
            ->setCellValue(chr($col++).$num, $v['low_security']);
        */
    }

    ob_end_clean();
    $objPHPExcel->getActiveSheet()->setTitle('User');
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$name.'.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}


function exportExcel($data,$name='Excel'){
    include 'PHPExcel/Classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();

    $title = array(
        '序号',
        '姓名',
        '身份证号',
        '报考专业',
        '联系电话',
        '出生年月',
        '初中毕业学校',
        '性别',
        '家长联系电话',
        'QQ号',
        '微信号',
        '民族',
        '政治面貌',
        '健康情况',
        '籍贯',
        '户口所在地',
        '家庭住址',
        "是否精准扶贫家庭",
        "是否低保家庭"
    );
    $col = 65;
    $width = array(
        'A' => 8,
        'C' => 23,
        'D' => 15,
        'L' => 8,
        'M' => 8,
        'N' => 8,
        'H' => 8,
    );
    foreach($title as $k=>$v){
        $num=1;
        $column = chr($col++);
        $w = isset($width[$column])?$width[$column]:20;

        $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($w);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column.$num, $v);
    }
    /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
    foreach($data as $k => $v){
        $num=$k+2;
        $col = 65;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue(chr($col++).$num, $k+1)
            ->setCellValue(chr($col++).$num, $v['name'])
            ->setCellValue(chr($col++).$num, $v['card']." ")
            ->setCellValue(chr($col++).$num, $v['class'])
            ->setCellValue(chr($col++).$num, $v['phone']." ")
            ->setCellValue(chr($col++).$num, $v['birthday'])
            ->setCellValue(chr($col++).$num, $v['school'])
            ->setCellValue(chr($col++).$num, $v['sex'])
            ->setCellValue(chr($col++).$num, $v['parent_phone']." ")
            ->setCellValue(chr($col++).$num, $v['qq']." ")
            ->setCellValue(chr($col++).$num, $v['weixin'])
            ->setCellValue(chr($col++).$num, $v['nation'])
            ->setCellValue(chr($col++).$num, $v['politics'])
            ->setCellValue(chr($col++).$num, $v['health'])
            ->setCellValue(chr($col++).$num, $v['native'])
            ->setCellValue(chr($col++).$num, $v['account_addr'])
            ->setCellValue(chr($col++).$num, $v['addr'])
            ->setCellValue(chr($col++).$num, $v['poverty'])
            ->setCellValue(chr($col++).$num, $v['low_security']);
    }
    ob_end_clean();
    $objPHPExcel->getActiveSheet()->setTitle('User');
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$name.'.xlsx"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

/**
 * 创建学生表 如果没有的话
 * @param $table 表名
 * @param $year 年份 没有的话默认当年
 */
function createSutdentTable($table,$year=''){
    if(!$year){
        $year = getYear();
    }
    $table = $table.'_'.$year;
    $fullName = C('DB_PREFIX').$table;
    $res = M()->query("SHOW TABLES like '{$fullName}'");
    if($res){
        return $table;
    }else{
        $sql = "CREATE TABLE `{$fullName}` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
              `name` varchar(64) NOT NULL DEFAULT '' COMMENT '姓名',
              `card` varchar(64) NOT NULL DEFAULT '' COMMENT '身份证',
              `birthday` varchar(64) NOT NULL DEFAULT '' COMMENT '出生年月',
              `school` varchar(128) NOT NULL DEFAULT '' COMMENT '毕业学校',
              `class` varchar(128) NOT NULL DEFAULT '' COMMENT '报考专业',
              `sex` varchar(20) NOT NULL DEFAULT '' COMMENT '性别',
              `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '联系电话',
              `parent_phone` varchar(32) NOT NULL DEFAULT '' COMMENT '家长联系电话',
              `qq` varchar(32) NOT NULL DEFAULT '' COMMENT 'qq号码',
              `weixin` varchar(32) NOT NULL DEFAULT '' COMMENT '微信号',
              `nation` varchar(64) NOT NULL DEFAULT '' COMMENT '民族',
              `politics` varchar(20) NOT NULL DEFAULT '' COMMENT '政治面貌',
              `health` varchar(32) NOT NULL DEFAULT '' COMMENT '健康情况',
              `native` varchar(32) NOT NULL DEFAULT '' COMMENT '籍贯',
              `account_addr` varchar(128) NOT NULL DEFAULT '' COMMENT '户口所在地',
              `addr` varchar(128) NOT NULL DEFAULT '' COMMENT '家庭住址',
              `poverty` varchar(20) NOT NULL DEFAULT '' COMMENT '扶贫家庭',
              `low_security` varchar(20) NOT NULL DEFAULT '' COMMENT '低保家庭',
              `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建用户id',
              `apply_id` int(11) NOT NULL DEFAULT '0' COMMENT '申请id 0是教师导入的 大于0是学生申请的',
              `query_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '学校查询日期 查询过了的就不能更改了',
              `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建日期',
              `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后一次更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COMMENT='学生表已经 录取了的学生';
            
         ";
        $res = M()->query($sql);
        return $table;
    }
}
?>