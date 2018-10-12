<?php
/**
 * Created by PhpStorm.
 * User: 郭立夫
 * Date: 2018/2/7
 * Time: 16:02
 * friendList
 */
session_start();
header("Access-Control-Allow-Origin: *");
$user_id = $_SESSION['user_id'];
$pre_friends_aggregate_key = 'friends_aggregate:';
$r = new Redis();
$r->connect('127.0.0.1');

$arr_id = $r->sMembers($pre_friends_aggregate_key.$user_id);
$f_list=[];
foreach ($arr_id as $k=>$id){
    $f_list[$k]['fid'] = $id;
    $f_list[$k]['username'] = $r->hGet('user_info:'.$id,'user_name');
}
echo json_encode(['status'=>true,'msg'=>'获取成功','data'=>$f_list]);
$r->close();