<?php
//登录
if(!$_POST){
    include "loginview.php";
}else {
    session_start();
        header("Access-Control-Allow-Origin: *");
        $user_name = $_POST['name'];
        $r = new Redis();
        $r->connect('127.0.0.1');
        $user_id = $r->get('username:'.$user_name);
        $is_login = $r->hGet('user_info:'.$user_id,'is_login');

        if(!$user_id){
            echo json_encode(['status'=>false,'msg'=>'无此用户！']);
        }else{
            if($is_login){
                echo json_encode(['status'=>false,'msg'=>'用户已经登录！']);
            }else{
                $r->hset('user_info:'.$user_id,'is_login',true);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $user_name;
                echo json_encode(['status'=>true,'msg'=>'登录成功','username'=>$user_name,'user_id'=>$user_id]);
            }
        }
        $r->close();
}
