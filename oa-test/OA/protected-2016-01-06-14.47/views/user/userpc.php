<?php
echo "<script type='text/javascript'>";
echo "console.log('userpc');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <div class="m0 p0 bor-1-ddd pd20 ">
        <!-- 标题 -->
        <h4 class="mb15 pl5 f20px">
            <strong>内网电脑信息</strong>
        </h4>
        <!-- 消息列表表格 -->
        <table id="table-data" class="table table-striped table-hover bor-1-ddd" style="width:80%">
            <tr>
                <th class="center">IP地址</th>
                <th class="center">MAC地址</th>
                <th class="center">操作</th>
            </tr>
        </table>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
var user_pc = <?php echo $user_pc?>;
// 页面初始化
$(document).ready(function(){
    $.each(user_pc, function(){
        var str_content = '<tr>' + 
            '<td class="center">'+ this['ip'] +'</th>' + 
            '<td class="center">'+ this['mac']+'</th>' +
            '<td class="center"><a class="pointer" onclick="sendWol(this)">远程开机</a></th>' + 
            '</tr>';
        $("#table-data").append(str_content);
    });
});

function sendWol(obj) {
    var ip_addr = $(obj).parent().prev().prev().text();
    var mac_addr = $(obj).parent().prev().text();
    var ip_addr_list = ip_addr.split(".");
    var ip_net = ip_addr_list[0]+"."+ip_addr_list[1]+"."+ip_addr_list[2]+".255";
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: '/ajax/sendWol',
        data: {'ip_net':ip_net, 'mac_addr':mac_addr},
        success:function(result){
            if(result.code==0)
                showHint("提示信息","已经发送开机指令");
            else
                showHint("提示信息", "操作失败"+result.msg);
        },
        error:function(arg1, arg2, arg3){
            showHint("提示信息", arg3);
        }
    });
}
</script>
