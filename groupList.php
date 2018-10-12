<?php
/**
 * Created by PhpStorm.
 * User: 郭立夫
 * Date: 2018/2/11
 * Time: 16:23
 */
session_start();
header("Access-Control-Allow-Origin: *");
$user_id = $_SESSION['user_id'];
$pre_group_key = 'group:my_group:user_id:';
$r = new Redis();
$r->connect('127.0.0.1');

$arr_id = $r->sMembers($pre_group_key.$user_id);
$g_list=[];
foreach ($arr_id as $k=>$g_id){
    $g_list[$k]['group_id'] = $g_id;
    $g_list[$k]['group_name'] = $r->hGet('group:group_info:group_id:'.$g_id,'group_name');
}
echo json_encode(['status'=>true,'msg'=>'获取成功','data'=>$g_list]);