<?php
/**
 * Created by PhpStorm.
 * User: 郭立夫
 * Date: 2018/2/5
 * Time: 18:14
 */
header("Access-Control-Allow-Origin: *");
$user_name = '*'.$_POST['username'].'*';
$r = redis();
$pre_key = 'username:';
$k_n = strlen($pre_key);
$username = $r->keys($pre_key.$user_name);
foreach ($username as $k=>$v){
    $user_info[$k]['user_id'] = $r->get($v);
    $user_info[$k]['username'] = substr($v,$k_n);
}
if($user_info){
    echo json_encode(['status'=>true,'data'=>$user_info]);
}else{
    echo json_encode(['status'=>false,'msg'=>'无']);
}
function redis(){
    $r = new Redis();
    $r->connect('127.0.0.1');
    return $r;
}
