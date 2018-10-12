<script src="asset/js/jquery.min.js"></script>
<script src="asset/js/base.js"></script>
<script>
    if(confirm('是否直接登录')){
        var login_name = prompt("请输入用户名");
        $.ajax({
            url:'http://'+ip+'/login.php',
            data:{
                name:login_name
            },
            type:'post',
            dataType:'json',
            success:function(res){
                if(!res.status){
                    alert(res.msg);
                }else{
                    location.href='./index.php';
                }
            }
        });
    }else{
        var reg_name = prompt("请输入注册用户名");
        if(reg_name==null || reg_name==''){
            alert('注册名不能为空！');
        }else{
            $.ajax({
                url:'http://'+ip+'/reg.php',
                data:{
                    name:reg_name
                },
                dataType:'json',
                type:'post',
                success:function(res){
                    if(!res.status){
                        alert(res.msg);
                    }else{
                        alert(res.msg);
                        location.href='./index.php';
                    }
                }
            });
        }
    }
</script>