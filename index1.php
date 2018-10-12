<?php session_start();?>
<!DOCTYPE html>
<html lang="en">  
<head>
    <link href="asset/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="asset/css/reset.css">
<meta charset="UTF-8">
    <style>
        *{
            margin:0px;
            padding:0px;
        }
        .mine{
            text-align: right;
        }
        .left{
            position: absolute;
        }
        .xitong{
            color:#db5656;
            font-size: 12px;
            text-align: center;
        }
        .group{
            color: #8a93db;
            font-size: 12px;
            text-align: center;
        }
        .friend{
            color: #49db20;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>  
  <?php  echo ($_SESSION['user_name']); if(!$_SESSION['user_id']){header('Location:login.php'); }?>
<body>
    <div>
        <div class="left">
            <div>
                <input id="username" type="text" placeholder="搜索用户"/>
                <input id="search" type="button" value="搜索"/>
            </div>
            <div>
                <ul id="u_list"></ul>
            </div>
            <div>
                <h3>好友列表</h3>
                <ul id="f_list"></ul>
            </div>
            <div>
                <h3>创建分组</h3>
                <input id="group_name" type="text" placeholder="填写分组名">
                <input id="add_group" type="button" value="创建分组"/>
            </div>
            <div>
                <h3>分组列表</h3>
                <ul id="g_list"></ul>
            </div>
        </div>
        <div style="border:1px solid;width: 600px;height: 300px; margin: 0 auto;">
            <div id="msgArea" style="width:100%;height: 100%;text-align:start;resize: none;font-family: 微软雅黑;font-size: 20px;overflow-y: scroll"></div>
        </div>
        <div style=" margin: 0 auto;border:1px solid;width: 600px;height: 200px;">
            <button id="emotion">表&nbsp;情</button>

            <div style="width:100%;height: 100%;">
                <textarea id="userMsg" style="width:99.6%;height: 87%;text-align:start;resize: none;font-family: 微软雅黑;font-size: 20px;"></textarea>
            </div>
        </div>
        <div style=" margin: 0 auto;border:1px solid;width: 600px;height: 25px;">
            <div id="type" style="float: left;"></div>
            <button id="send" style="float: right;" onclick="sendMsg()">发&nbsp;&nbsp;&nbsp;送</button>
        </div>
    </div>
</body>  
  
</html>
<script src="asset/js/jquery.min.js"></script>
<script src="asset/js/jquery.qqFace.js"></script>
<script src="asset/js/base.js"></script>
<script>
    var ws;
    $(function(){
        $('#emotion').qqFace({
            id : 'facebox',
            assign:'userMsg',       //回显位置
            path:'asset/arclist/'	//表情存放的路径
        });
        get_f_list();
        get_g_list();
        link();
        //添加分组操作
        $("#add_group").click(function () {
            var group_name = $('#group_name').val();
            var fid_list = $('.fid_list:checked');
            if(fid_list.length<2){
                alert('分组成员至少3人');
                return false;
            }
            if(group_name==''){alert('填写分组名');return false;}
            var str_fid = '';
            $.each(fid_list,function(){
                str_fid += $(this).val()+',';
            });
            str_fid=str_fid.substring(0,str_fid.length-1);
            $.ajax({
                url:'http://'+ip+'/addgroup.php',
                data:{
                    group_name:group_name,
                    f_id:str_fid
                },
                type:'post',
                dataType:'json',
                success:function (res) {
                    if(!res.status){
                        alert(res.msg);
                    }else{
                        alert(res.msg);
                        get_g_list();
                        var data = {
                            'type':4,
                            'g_list':res.g_list,
                            'id':res.main_id
                        };
                        var msg_json = JSON.stringify(data);
                        ws.send(msg_json);
                    }
                },
                error:function () {
                    alert("服务器异常")
                }
            });
        });
        //添加好友操作
        $("#u_list").on("click","button", function() {
            var user_id = $(this).attr('data-id');
            $.ajax({
                url:'http://'+ip+'/addfriend.php',
                data:{
                    user_id:user_id
                },
                type:'post',
                dataType:'json',
                success:function(res){
                    alert(res.msg);
                    get_f_list();
                    var data = {
                        'type':3,
                        'f_id':res.f_id,
                        'user_id':res.user_id
                    };
                    var msg_json = JSON.stringify(data);
                    ws.send(msg_json);
                }
            })
        });
        //好友列表按钮操作
        $("#f_list").on("click","button", function() {
            var f_id = $(this).attr('data-id');
            var f_name = $(this).attr('data-name');
            $('#type').attr('data-id',f_id);
            $('#type').attr('data-type',1);
            $('#type').html('对 '+f_name+' 说：');
        });
        //群列表按钮操作
        $("#g_list").on("click","button", function() {
            var g_id = $(this).attr('data-id');
            var g_name = $(this).attr('data-name');
            $('#type').attr('data-id',g_id);
            $('#type').attr('data-type',2);
            $('#type').html('向 '+g_name+' 所有人说：');
        });
        //搜索用户列表
        $('#search').click(function () {
            var username = $('#username').val();
            $.ajax({
                url:'http://'+ip+'/search.php',
                data:{
                    username:username
                },
                type:'post',
                dataType:'json',
                success:function(res){
                    if(!res.status){
                        alert(res.msg);
                    }else{
                        $('#u_list').empty();
                        for(var i=0,n=res.data.length;i<n;i++){
                            $('#u_list').append('<li>'
                                +res.data[i].username
                                +'<button class="add_friend" data-id ='+res.data[i].user_id +'>添加好友</button>'
                                +'</li>')
                        }
                    }
                }
            })
        });
    });

    function link () {
        client_ws(<?php echo $_SESSION['user_id']; ?>);
    }
    function client_ws(id) {
        ws = new WebSocket('ws://'+ip+':9501?id='+id);//连接服务器
        ws.onopen = function(event){
            console.log(event);
        };
        ws.onmessage = function (event) {
            var msgJson = JSON.parse(event.data);
            switch (msgJson.cli_type){
                //自发类型
                case -1:{
                    $("#msgArea").append('<p class="mine">'+msgJson.msg+'</p>');
                    break;
                }
                //系统通知
                case 0:{
                    $("#msgArea").append('<p class="xitong">【系统通知】:'+msgJson.msg+'</p>');
                    break;
                }
                //消息类型
                case 1:{
                    $("#msgArea").append('<p>'+msgJson.msg+'</p>');
                    break;
                }
                //好友通知类型
                case 2:{
                    $("#msgArea").append('<p class="friend">【好友通知】:'+msgJson.msg+'</p>');
                    get_f_list();
                    break;
                }
                //群通知
                case 3:{
                    $("#msgArea").append('<p class="group">【群通知】:'+msgJson.msg+'</p>');
                    get_g_list();
                    break;
                }
            }
//            $('#msgArea').scrollTop( $('#msgArea')[0].scrollHeight );
//            $('#msgArea').scrollTop( $('#msgArea')[0].scrollHeight );
//            var v=$($('#msgArea').get(0).lastChild).offset().top;
//            alert(v)
            $('#msgArea').animate({scrollTop:$('#msgArea')[0].scrollHeight},1000);

        };
        ws.onclose = function(event){alert("已经与服务器断开连接\r\n当前连接状态："+this.readyState);};

        ws.onerror = function(event){alert("WebSocket异常！");};
    }
    function sendMsg(){
        var msg = replace_em($("#userMsg").val());
        //f_id 或 g_id
        var id = $('#type').attr('data-id');
        var type = $('#type').attr('data-type');
        if(!id){
            alert('请选择消息接收者');
            return false;
        }
        var data = {
            'type':type,
            'msg':msg,
            'id':id
        };
        var msg_json = JSON.stringify(data);
        //发送到服务器
        ws.send(msg_json);
        $('#userMsg').val('');

    }
    //获取好友列表
    function get_f_list() {
        $.ajax({
            url:'http://'+ip+'/friendList.php',
            type:'post',
            dataType:'json',
            success:function(res){
                if(!res.status){
                    alert(res.msg);
                }else{
                    $('#f_list').empty();
                    for(var i=0,n=res.data.length;i<n;i++){
                        $('#f_list').append('<li>'
                            +'<input class="fid_list" type="checkbox" value='+res.data[i].fid+'>'
                            +res.data[i].username
                            +'<button class="send" data-id ='+res.data[i].fid+' data-name ='+res.data[i].username+'>发消息</button>'
                            +'</li>')
                    }
                }
            }
        });
    }
    //获取分组列表
    function get_g_list() {
        $.ajax({
            url:'http://'+ip+'/groupList.php',
            type:'post',
            dataType:'json',
            success:function(res){
                if(!res.status){
                    alert(res.msg);
                }else{
                    $('#g_list').empty();
                    for(var i=0,n=res.data.length;i<n;i++){
                        $('#g_list').append('<li>'
                            +res.data[i].group_name
                            +'<button class="send" data-id ='+res.data[i].group_id+' data-name ='+res.data[i].group_name+'>发消息</button>'
                            +'</li>')
                    }
                }
            }
        });
    }

    //统一解析服务器消息
    function decodeMsg(msg) {

    }

    function replace_em(str){
        str = str.replace(/\</g,'&lt;');
        str = str.replace(/\>/g,'&gt;');
        str = str.replace(/\n/g,'<br/>');
        str = str.replace(/\[em_([0-9]*)\]/g,'<img src="asset/arclist/$1.gif" border="0" />');
        return str;
    }
</script>  