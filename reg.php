<?php
//注册
session_start();
header("Access-Control-Allow-Origin: *");
$r = new Redis();
$r->connect('127.0.0.1');

$user_name = $_POST['name'];

$user_id = $r->get('username:'.$user_name);
if($user_id){
    echo json_encode(['status'=>false,'msg'=>'用户已存在！']);
}else{
    $global_id = $r->incr('global_id');
    $r->hMset('user_info:'.$global_id,['user_name'=>$user_name,'fd'=>'','is_login'=>true]);
    $r->set('username:'.$user_name,$global_id);
    $_SESSION['user_id'] = $global_id;
    $_SESSION['user_name'] = $user_name;
    echo json_encode(['status'=>true,'msg'=>'注册成功','username'=>$user_name]);
}
$r->close();

