<?php
echo "<script type='text/javascript'>";
echo "console.log('processLeave');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-1-ddd">请假审批</h4>
    <!-- 进度条 -->
    <div class="bor-l-1-ddd bor-r-1-ddd">
        <ul class="nav nav-justified" id="ulContenter">
            
        </ul>
    </div>
    <div class="hidden" id="id"><?php echo "{$leave->leave_id}"; ?></div>
    <!-- 请假申请详情表格 -->
    <table  class="table table-striped table-bordered table-hover f15px">
        <tr><th class="w130 center">填表日期</th><td><?php echo $leave->create_time ?></td></tr>
        <tr><th class="w130 center">姓名</th><td><?php echo $leave->user->cn_name; ?></td></tr>
        <tr><th class="w130 center">部门</th><td><?php echo $leave->user->department->name; ?></td></tr>
        <tr><th class="w130 center">岗位</th><td><?php echo $leave->user->title; ?></td></tr>
        <tr><th class="w130 center">天数</th><td><?php echo $leave->total_days; ?></td></tr>
        <tr><th class="w130 center">请假类型</th><td><?php echo $leave->cntype; ?></td></tr>
        <tr><th class="w130 center">请假时间</th><td><?php echo $leave->start_time; ?>&nbsp;到&nbsp;<?php echo $leave->end_time;?><label class='ml20 inline'>共&nbsp;<?php echo $leave->total_days; ?>&nbsp;天</label></td></tr>
        <tr><th class="w130 center">请假事由</th><td><?php echo $leave->content; ?></td></tr>
        <?php if(!empty($leave->delay)): ?>
        <tr><th class="w130 center">延迟提交原因</th><td><?php echo $leave->delay; ?></td></tr>
        <?php endif; ?>

        <?php if(!empty($logs)): ?>
        <?php foreach($logs as $log): ?>
        <tr>
            <th class="w130 center"><?php echo $log->user->department->name; ?>审批</th>
            <td>
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php elseif($log->action == 'reject'): ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;"><?php echo $leave->reason; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                    <?php if($log->action == 'agree'): ?>
                    <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name; ?></span></h5>
                    <?php endif; ?>
                    <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d' , strtotime($log->create_time)); ?></span></h5>
               </div>
            </div>
        </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
            <th class="w130 center">审批状态</th>
            <td>
                 待审批
            </td>
        </tr>
        <?php endif; ?>
        <?php if(!empty($leave->image)): ?>
        <tr>
            <th class="w130 center">附件</th>
            <td>
                <img src="<?php echo $leave->image;?>" class="w400 pointer" onclick="window.open('<?php echo $leave->image;?>');">
            </td>
        </tr>
        <?php endif; ?>
        <?php if($leave->status == 'wait' && $leave->next == Yii::app()->session['user_id'] ): ?>
        <tr>
            <th class="w130 center">回复操作</th>
            <td>
                <button class="btn btn-success w100" id="agree">同意</button>
                <button class="btn btn-primary w100 ml20" id="reject">不同意</button>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>
<!-- 退回请假单模态框 -->
<div id="reject-reason-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">输入不同意原因</h4>
    </div>

    <div class="modal-body">
        <label>不同意原因：</label>
        <textarea type="text" class="form-control inline" id="reject-input"></textarea>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="rejectSubmit()">提交</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
    // 页面初始化
    window.onload = function(){
        var pattern = /^\d+$/;

        // 同意操作
        $("#agree").click(function(){
            var id = $("#id").text();
            if(!pattern.exec(id)){
                showHint("提示信息","请刷新页面");
            }else{
                $.ajax({
                    type:'post',
                    dataType:'json',
                    url:'/ajax/agreeLeave',
                    data:{'id':id},
                    success:function(result){
                        if(result.code == 0){
                            showHint("提示信息","同意成功");
                            setTimeout(function(){location.reload();},1200);
                        }
                        else if(result.code == -2)
                            showHint("提示信息","请刷新页面");
                        else if(result.code == -100) {
                            $("#reject-input").val("申请的补休 > 当前剩余的补休，请重新提交申请");
                            showHint("提示信息","申请的补休 > 当前剩余的补休，请退回此申请单");
                        }
                        else if(result.code == -101) {
                            $("#reject-input").val("申请的年假 > 剩余的年假，请重新提交申请");
                            showHint("提示信息","申请的年假 > 剩余的年假，请退回此申请单");
                        }
                        else{
                            showHint("提示信息","系统错误，请联系管理员");
                        }
                    }
                });
            }
        });

        // 不同意操作
        $("#reject").click(function(){
            $("#reject-reason-div").css("top","30%");
            $("#reject-reason-div").modal({show:true});
            $('#agree').addClass('disabled');
            $('#reject').addClass('disabled');
        });

        ulContent();
    }
    //显示进度条
    function ulContent(){
        var content = "";
        content += "<li class='bg-66 flow-li'>"+
                        "<h4 class='white m0 mt5 center'>1.提交请假申请</h4>"+
                        "<div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div>"+
                    "</li>";
        var procedure = <?php echo CJSON::encode($procedure) ?>;
        var logs =  <?php echo CJSON::encode($logs) ?>;
        console.log(logs);
        for(var x in procedure){
            if(logs[x] == undefined){
                content +=  "<li>"+
                            "<h4 class='m0  mt5 center'>"+(parseInt(x)+2)+"."+procedure[x]['department']+"("+procedure[x]['name']+")"+"</h4>"+
                            "<div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div>"+
                            "</li>";
                if(x == procedure.length-1){
                    content +=  "<li>"+
                                    "<h4 class='m0 mt5 center'>"+(parseInt(x)+3)+"."+"请假申请结果</h4>"+
                                    "<div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div>"+
                                "</li>";
                                break;
                }            
            }else if(logs !== ""){
            if(logs[x]['action'] == "agree"){
                content +=  "<li class='flow-li bg-66'>"+
                                "<h4 class='m0 white mt5 center'>"+(parseInt(x)+2)+"."+procedure[x]['department']+"("+procedure[x]['name']+")"+"</h4>"+
                                "<div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div>"+
                            "</li>";

                if(x == procedure.length-1){
                    content +=  "<li class='bg-66'>"+
                                    "<h4 class='white m0 mt5 center'>"+(parseInt(x)+3)+"."+"请假申请结果</h4>"+
                                    "<div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div>"+
                                "</li>";
                }
            }else if(logs[x]['action'] == "reject"){
                content +=   "<li class='flow-li-red bg-99'>"+
                                "<h4 class='m0 white mt5 center'>"+procedure[x]['department']+"("+procedure[x]['name']+")"+"</h4>"+
                                "<div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div>"+
                            "</li>"+
                            "<li class='bg-99'>"+
                                "<h4 class='white m0 mt5 center'>"+"请假申请结果</h4>"+
                                "<div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div>"+
                            "</li>";
                            break;
            }
            }
        }

        $('#ulContenter').append(content);
    }
    // 发送退回申请单
    function rejectSubmit(){
        // 获取数据
        var id = $("#id").text();
        var reject_reason = $("#reject-input").val();

        // 验证数据
        var pattern = /^\d+$/;
        if(!pattern.exec(id)){
            showHint("提示信息","请刷新页面");
        }else if(reject_reason == ""){
            showHint("提示信息","请输入不同意原因");
        }else{
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/rejectLeave',
                data:{'id':id,'reason':reject_reason},
                success:function(data){
                    if(data.code == 0){   
                        showHint("提示信息","驳回请假单成功");
                        setTimeout(function(){location.reload();},1200);
                    }else{
                        showHint("提示信息","系统出错，请联系管理员");
                    }
                }
            });
        }
    }
</script>
