<?php
/**
 * Created by PhpStorm.
 * User: 郭立夫
 * Date: 2018/2/11
 * Time: 14:50
 */
session_start();
header("Access-Control-Allow-Origin: *");
$user_id = $_SESSION['user_id'];

$r = new Redis();
$r->connect('127.0.0.1');

$group_name = $_POST['group_name'];
$group_id = $r->incr('global_group_id');

$arr_f_id = explode(',',$_POST['f_id']);
$num = count($arr_f_id)+1;
$r->multi();
//好友id加入集合
$r->sAdd('group:group_aggregate:group_id:'.$group_id,$user_id);
foreach ($arr_f_id as $f_id){
    $r->sAdd('group:group_aggregate:group_id:'.$group_id,$f_id);
}

//加入我的群集合
$r->sAdd('group:my_group:user_id:'.$user_id,$group_id);
foreach ($arr_f_id as $f_id){
    $r->sAdd('group:my_group:user_id:'.$f_id,$group_id);
}


//分组详细信息
$r->hMset('group:group_info:group_id:'.$group_id,['group_name'=>$group_name,'main_id'=>$user_id,'num'=>$num,'add_time'=>time()]);
$r->exec();

echo json_encode(['status'=>true,'msg'=>'创建成功','main_id'=>$user_id,'g_list'=>$arr_f_id]);
$r->close();exit();