<?php
//$ws = new swoole_websocket_server("192.168.199.199", 9501);
$ws = new swoole_websocket_server("192.168.4.56", 9501);
$ws->user_c = [];   //给ws对象添加属性user_c，值为空数组；
//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    $r = redis();
    $user_id = $request->get['id'];
    $user_name =  $r->hGet('user_info:'.$user_id,'user_name');
    $fd = $request->fd;
    $ws->user_c[] = $fd;
    $r->hset('user_info:'.$user_id,'fd',$fd);
    $r->hset('user_info:'.$user_id,'is_login',true);
    $r->set('fd:'.$fd,$user_id);
    //$ws->push($request->fd, "hello, welcome\n");
    echo 'username:'.$user_name.',id:'.$user_id.',fd:'.$fd;
    $r->close();
    foreach($ws->user_c as $v){
        $ws->push($v,returnMsg(0,$user_name.'上线了'));
    }
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {

    $data = json_decode($frame->data,true);
    var_dump($data);
    $fd = $frame->fd;
    send($ws,$data,$fd);
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    //删除已断开的客户端
    $r = redis();
    $user_id = $r->get('fd:'.$fd);
    $user_name = $r->hGet('user_info:'.$user_id,'user_name');
    $r->hset('user_info:'.$user_id,'is_login',0);
    //删除fd
    $r->del('fd:'.$fd);
    foreach($ws->user_c as $v){
        if($v != $fd){
            $ws->push($v,returnMsg(0,$user_name.'下线了'));
        }
    }
    unset($ws->user_c[$fd-1]);
});
$ws->start();


function redis(){
    $r = new Redis();
    $r->connect('127.0.0.1');
    return $r;
}

function send($ws,$data,$fd){
    $my_id = redis()->get('fd:'.$fd);
    $send_people_info = getUserInfoByFd($fd);
    $receive_people_info = getUserInfoById($data['id']);

    $user_name = $send_people_info['user_name'];

    $msg =  '<b>'.$user_name.'</b>'.":{$data['msg']}\n";
    switch ($data['type']){
        //单人发送
        case 1 :{
            $ws->push($fd,returnMsg(-1,$msg));
            $ws->push($receive_people_info['fd'],returnMsg(1,$msg));
            break;
        }
        //群组发送
        case 2:{
            //通过group_id获取集合数组，遍历发送。
            $group_id = $data['id'];
            $arr_u_id = getGroupById($group_id);
            foreach ($arr_u_id as $id){
                $send_info = getUserInfoById($id);
                if($id == $my_id){
                    $ws->push($fd,returnMsg(-1,$msg));
                }else{
                    $ws->push($send_info['fd'],returnMsg(1,$msg));
                }
            }
            break;
        }
        //加好友通知
        case 3:{
            $ws->push(getUserInfoById($data['f_id'])['fd'],returnMsg(2,$user_name.'将你添加为好友'));
            break;
        }
        //拉人进群通知
        case 4:{
            foreach ($data['g_list'] as $g_id){
                $ws->push(getUserInfoById($g_id)['fd'],returnMsg(3,$user_name.'把你拉群里了'));
            }
            break;
        }
    }
}
//通过fd获取信息
function getUserInfoByFd($fd){
    $r = redis();
    $it = null;
    $user_id = $r->get('fd:'.$fd);
    $user_info = $r->hScan('user_info:'.$user_id,$it);
    return $user_info;
}
//通过id获取信息
function getUserInfoById($id){
    $r = redis();
    $it = null;
    $user_info = $r->hScan('user_info:'.$id,$it);
    return $user_info;
}
//获取组用户id
function getGroupById($id){
    $r = redis();
    return $r->sMembers('group:group_aggregate:group_id:'.$id);
}
//处理返回消息
/*
 * type 3 群组通知类消息
 * type 2 好友通知类消息
 * type 1 通信类型消息
 * type 0 系统通知类消息
 * type -1 自发消息类型
 * */
function returnMsg($cli_type, $msg){
    return json_encode(['cli_type'=>$cli_type,'msg'=>$msg]);
}