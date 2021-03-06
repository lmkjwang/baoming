<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Loader;

class Admin extends controller
{

    public function register(){
        Session::clear();
        return $this->fetch();
    }
    public function checkres(){
        $username=$_POST['user'];
        $password=$_POST['pass'];
        $user=Db::name('admin')->where("username = '$username'")->find();
        if(!empty($user)){
           if($user['password'] == $password){
               Session::set('status','success');
               $this->success('登录成功','/index.php/admin/admin/index');
           }else{
               $this->error('密码错误，登录失败','admin/register');
           }
        }else{
            $this->error('账号错误，登录失败','admin/register');
        }
    }
    //修改密码，包含updatepaw和repwd两个方法
    public function updatepaw(){
        $ses=Session::has('status');
        if($ses){
            return $this->fetch();
        }else{
            $this->error('请通过登录方式进入后台','admin/register');
        }
    }
    public function repwd(){
        $user=$_POST['user'];
        $oldpwd=$_POST['oldpwd'];
        $newpwd=$_POST['newpwd'];
        $result=Db::name('admin')->where("username = '$user' and password = '$oldpwd'")->find();
        $id=$result['id'];
        if(!empty($result)){
            $ques=Db::name('admin')->where('id='.$id)->update(['password' => "$newpwd"]);
            if($ques){
                $this->success('修改管理员密码成功','admin/index');
            }else{
                $this->error('出现未知错误请联系开发人员','admin/updatepaw');
            }
        }else{
            $this->error('原始账号或密码不正确','admin/updatepaw');
        }
    }
   //设置分数线，包含setting和changeset两个方法
    public function setting(){
        $ses=Session::has('status');
        if($ses){
            $result=Db::name('through-line')->find();
            $this->assign('fs',$result['through']);
            return $this->fetch();
        }else{
            $this->error('请通过登录方式进入后台','admin/register');
        }

    }
    public function changeset(){
        $fs=$_POST['fs'];
        $res=Db::name('through-line')->where('id=1')->update(['through'=>"$fs"]);
        if($res){
            $this->success('设置成功','admin/index');
        }else{
            $this->error('设置失败','admin/setting');
        }
    }
    function dizhi()
    {
        $p=$_POST['area'];
        Session::set('area',$p);
        Header("Location: index");
    }
    public function index(){
        $ses=Session::has('status');
        if($ses){
            $p=Session::get('area');
            if($p) {
                $res = Db::name('user')
                    ->alias('u')
                    ->join('userinfo i', 'u.id = i.user_id')
                    ->join('subject s','i.major = s.id')
                    ->field('i.id,i.image,i.school,i.interviewstatus,i.username,i.examnum,i.sex,i.idcar,s.subject,i.province,i.city,i.district,i.address,i.brtel,i.telone,i.teltwo,i.examscore,i.examtime,i.examaddress,i.examaddnum')
                    ->where("district='$p' AND paystatus = 1")
                    ->paginate(6);
                $fs=Db::name('through-line')->find();
                $this->assign('user', $res);
                $this->assign('fs',$fs);
            }else{
                $res = Db::name('user')
                    ->alias('u')
                    ->join('userinfo i', 'u.id = i.user_id')
                    ->join('subject s','i.major = s.id')
                    ->field('i.id,i.image,i.school,i.interviewstatus,i.username,i.examnum,i.sex,i.idcar,s.subject,i.province,i.city,i.district,i.address,i.brtel,i.telone,i.teltwo,i.examscore,i.examtime,i.examaddress,i.examaddnum')
                    ->where("paystatus = 1")
                    ->paginate(6);
                $fs=Db::name('through-line')->find();
                $this->assign('user', $res);
                $this->assign('fs',$fs);
            }
            return $this->fetch();
        }else{
           $this->error('请通过登录方式进入后台','admin/register');
        }
    }
    //index页面成绩框失去焦点事件
    public function updascore(){
        $id=input('post.id');
        $value=input('post.value');
        $up=Db::name('userinfo')->where("id=$id")->update(['examscore'=>$value]);
        if($up){
            echo 1;
        }
    }
//,'examtime'=>$time,'examaddress'=>$add,'examaddnum'=>$addnum
    public function updatime(){
        $id=input('post.id');
        $value=input('post.value');
        $up=Db::name('userinfo')->where("id=$id")->update(['examtime'=>$value]);
        if($up){
            echo 1;
        }
    }
    public function updaadd(){
        $id=input('post.id');
        $value=input('post.value');
        $up=Db::name('userinfo')->where("id=$id")->update(['examaddress'=>$value]);
        if($up){
            echo 1;
        }
    }
    public function updanum(){
        $id=input('post.id');
        $value=input('post.value');
        $up=Db::name('userinfo')->where("id=$id")->update(['examaddnum'=>$value]);
        if($up){
            echo 1;
        }
    }
    public function updainter(){
        $val=input('post.id');
        $a=explode(',',$val);
        $id=$a[0];
        $inter=$a[1];
        Db::name('userinfo')->where("id=$id")->update(['interviewstatus'=>$inter]);
        if($inter == 1){
            echo 1;
        }elseif($inter == 2){
            echo 2;
        }else{
            echo 3;
        }
    }
    //清除地址搜索数据
    public function clear(){
        Session::delete('area');
        Header("Location: index");
    }
    //打印excel
    public function dayin(){
        $p=Session::get('area');
        if($p){
            $list=Db::query("SELECT username,CASE sex
				 WHEN '0' THEN '女'
                 WHEN '1' THEN '男' END newsex,idcar,school,address,brtel,telone,teltwo,CASE major
                 WHEN '1' THEN '高中-美术'
                 WHEN '2' THEN '高中-音乐舞蹈'
				 WHEN '3' THEN '高中-播音主持'
                 WHEN '4' THEN '教育-美术'
				 WHEN '5' THEN '教育-音乐舞蹈'
                 WHEN '6' THEN '教育-播音主持'
				 WHEN '7' THEN '教育-休闲体育' END newmajor,examnum,examtime,examaddress,examaddnum from userinfo

                 where district='$p' AND  paystatus= 1");

        }else{
            $list=Db::query("SELECT username,CASE sex
				 WHEN '0' THEN '女'
                 WHEN '1' THEN '男' END newsex,idcar,image,school,address,brtel,telone,teltwo,CASE major
                 WHEN '1' THEN '高中-美术'
                 WHEN '2' THEN '高中-音乐舞蹈'
				 WHEN '3' THEN '高中-播音主持'
                 WHEN '4' THEN '教育-美术'
				 WHEN '5' THEN '教育-音乐舞蹈'
                 WHEN '6' THEN '教育-播音主持'

				 WHEN '7' THEN '教育-休闲体育' END newmajor,examnum,examtime,examaddress,examaddnum  from userinfo where paystatus= '1'");

        }
        $excel2007=false;
        $indexKey=array('username','newsex','idcar','school','address','brtel','telone','teltwo','newmajor','examnum','examtime','examaddress','examaddnum');
        if(empty($filename)) $filename = $p.date("Y-m-d",time());//time();
        if( !is_array($indexKey)) return false;

        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        //初始化PHPExcel()
        Loader::import('classes\PHPExcel',EXTEND_PATH);
        $objPHPExcel = new \PHPExcel();
        //设置保存版本格式
        if($excel2007){
            Loader::import('classes\PHPExcel\Writer\PHPExcel_Writer_Excel2007',EXTEND_PATH);
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $filename = $filename.'.xlsx';
        }else{
            Loader::import('classes\PHPExcel\Writer\PHPExcel_Writer_Excel5',EXTEND_PATH);
            $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
            $filename = $filename.'.xls';
        }    
        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
//        array('姓名','性别','身份证号','就读学校','家庭地址','本人电话','家长电话（1）','家长电话（2）','报考专业','准考证号','考试时间','考点名称','考场编号');
        $objActSheet->setCellValue('A1',"考生姓名");
        $objActSheet->setCellValue('B1',"性别");
        $objActSheet->setCellValue('C1',"身份证号");
        $objActSheet->setCellValue('D1',"就读学校");
        $objActSheet->setCellValue('E1',"家庭地址");
        $objActSheet->setCellValue('F1',"本人电话");
        $objActSheet->setCellValue('G1',"家长电话（1）");
        $objActSheet->setCellValue('H1',"家长电话（2）");
        $objActSheet->setCellValue('I1',"报考专业");
        $objActSheet->setCellValue('J1',"准考证号");
        $objActSheet->setCellValue('K1',"考试时间");
        $objActSheet->setCellValue('L1',"考点名称");
        $objActSheet->setCellValue('M1',"考场编号");
        $startRow = 2;
        foreach ($list as $row) {
            foreach ($indexKey as $key => $value){
                //这里是设置单元格的内容
                $objActSheet->setCellValue($header_arr[$key].$startRow,$row[$value]);
            }
            $startRow++;
        }

        // 下载这个表格，在浏览器输出
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename='.$filename.'');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

    //修改科目系列操作
    public function userdata(){
        $ses=Session::has('status');
        if($ses){
            $list=Db::name('subject')->order('id')->select();
            $this->assign('subject',$list);
            return $this->fetch();
        }else{
            $this->error('请通过登录方式进入后台','admin/register');
        }
    }
    public function updatesub(){
        $id= input('post.id');
        $subject=input('post.subject');
        $submoney=input('post.submoney');
        $up=Db::name('subject')->where("id=$id")->update(['subject'=>$subject,'submoney'=>$submoney]);
       if($up){
           echo 1;
       }
    }
    public function delsub(){
        $id=input('post.id');
        $del=Db::name('subject')->where("id=$id")->delete();
        echo $del;
    }
    public function insub(){
        $subject=input('post.subject');
        $submoney=input('post.submoney');
        $up=Db::name('subject')->insert(['subject'=>$subject,'submoney'=>$submoney]);
        echo $up;
    }
    //获取 考生信息(用来生成准考证)；
    public function getDetailInfo(){
        $id=input('post.id',0 , 'intval');
        if(!$id) {
            return json_encode(array('status'=>0,'msg'=>'服务器异常，请联系管理员'));
        }
        $res = Db::name('userinfo')
        ->alias('u')
        ->join('subject s','s.id = u.major') 
        ->where(['u.id'=>$id])
        ->field('u.username,u.image,u.school,u.idcar,u.examnum,u.examaddnum,u.examaddress,s.subject,u.examimg')
        ->find();
        if(!empty($res)){
            $res['image'] = request() -> domain().$res['image'];
            $res['examnum'] = substr($res['idcar'], 0, 6) . substr(str_pad($res['examnum'], 5, '0',STR_PAD_LEFT), -5);
            //如果已有准考证；
            if($res['examimg']){
                return json_encode(array('status'=>2,'msg'=>'准考证已生成','data'=>$res));
            }
            
            return json_encode(array('status'=>1,'msg'=>'OK','data'=>$res));
        }
        return json_encode(array('status'=>0,'msg'=>'网络繁忙，请稍后重试'));
        
    }
    //生成并保存准考证；
    public function base64imgsave(){
        
        $id = input('post.id',0 , 'intval');
        $img = input('imgbase64');
        
        //文件夹日期
        $ymd = date("Ymd");
        
        //图片路径地址	
        // $basedir = 'uploads/base64/'.$ymd.'';
        $basedir = 'uploads'.DS.'base64'.DS.$ymd.'';
        $fullpath = $basedir;
        if(!is_dir($fullpath)){
            mkdir($fullpath,0777,true);
        }
        $types = empty($types)? array('jpg', 'gif', 'png', 'jpeg'):$types;
        
        $img = str_replace(array('_','-'), array('/','+'), $img);
        
        $b64img = substr($img, 0,100);
        
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $b64img, $matches)){
            
            $type = $matches[2];
            if(!in_array($type, $types)){
                return array('status'=>1,'info'=>'图片格式不正确，只支持 jpg、gif、png、jpeg哦！','url'=>'');
            }
            $img = str_replace($matches[1], '', $img);
            $img = base64_decode($img);
            $photo = DS.md5(date('YmdHis').rand(1000, 9999)).'.'.$type;
            file_put_contents($fullpath.$photo, $img);
            

            //存入数据库
            $res = Db::name('userinfo') -> where(['id'=> $id]) -> update(['examimg'=>DS.$basedir.$photo]);
            if($res !== false){
                $ary['status'] = 1;
                $ary['msg'] = '保存图片成功';
                $ary['url'] = DS.$basedir.$photo;
                return $ary;
            }else{
                $ary['status'] = 0;
                $ary['msg'] = '图片保存失败';
                $ary['url'] = DS.$basedir.$photo;
                return $ary;
            }
            
            
            
        
        }
        
            $ary['status'] = 0;
            $ary['msg'] = '请选择要上传的图片';
            
            return $ary;
        
    }

    //图片预览；
    public function previewImage(){
        $id=input('id', 0, 'intval');
        if(!$id){
            return array('status'=>0,'msg'=>'服务器异常，请联系管理员');
        }
        $fil=Db::name('userinfo')->where(['id'=>$id])->field('examimg,id,username')->find();
        if(empty($fil)){
            return array('status'=>0,'msg'=>'信息不存在');
        }
        if(!$fil['examimg']){
            return array('status'=>0,'msg'=>'未生成准考证');
        }
        $url =  request() ->domain() . $fil['examimg'];
        return array('status'=>1,'msg'=>'OK','data'=> $url);
       
    }

}
