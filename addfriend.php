<?php
/**
 * Created by PhpStorm.
 * User: 郭立夫
 * Date: 2018/2/6
 * Time: 16:06
 */
session_start();
header("Access-Control-Allow-Origin: *");
$user_id = $_SESSION['user_id'];
$friend_id = $_POST['user_id'];

$pre_friends_aggregate_key = 'friends_aggregate:';
$pre_friend_info_key = 'friend_info:';

if($user_id==$friend_id){
    echo json_encode(['status'=>false,'msg'=>'别加你自己']);exit();
}


$r = new Redis();
$r->connect('127.0.0.1');
if($r->sIsMember($pre_friends_aggregate_key.$user_id,$friend_id)){
    echo json_encode(['status'=>false,'msg'=>'对方已经是你的好友了']);exit();
}
$r->multi();
//好友id加入集合
$r->sAdd($pre_friends_aggregate_key.$user_id,$friend_id);
$r->sAdd($pre_friends_aggregate_key.$friend_id,$user_id);
//详细信息
$r->hset($pre_friend_info_key.$user_id.':'.$friend_id,'add_time',time());
$r->hset($pre_friend_info_key.$friend_id.':'.$user_id,'add_time',time());
$r->exec();
echo json_encode(['status'=>true,'msg'=>'添加成功','user_id'=>$user_id,'f_id'=>$friend_id]);exit();