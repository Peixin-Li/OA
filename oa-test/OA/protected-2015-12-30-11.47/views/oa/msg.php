<?php
echo "<script type='text/javascript'>";
echo "console.log('msg');";
echo "</script>";
// echo var_dump($logs[2]->action);
?>

<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-1-ddd">请假申请详情</h4>
    <!-- 进度条 -->
    <div class="bor-l-1-ddd bor-r-1-ddd">
        <ul class="nav nav-justified" id="ulContenter">
            
        </ul>
    </div>
    <!-- 请假申请表 -->
    <?php if(!empty($leave)): ?>
    <table  class="table table-striped table-bordered table-hover">
        <tr><th class="w130 center">姓名</th><td><?php echo $leave->user->cn_name; ?></td></tr>
        <tr><th class="w130 center">部门</th><td><?php echo $leave->user->department->name; ?></td></tr>
        <tr><th class="w130 center">填写时间</th><td><?php echo $leave->create_time; ?></td></tr>
        <tr><th class="w130 center">岗位</th><td><?php echo $leave->user->title; ?></td></tr>
        <tr><th class="w130 center">请假类型</th><td><?php echo $leave->cntype; ?></td></tr>
        <tr><th class="w130 center">请假时间</th><td><?php echo date('Y-m-d H:i', strtotime($leave->start_time)); ?>&nbsp;&nbsp;到&nbsp;&nbsp;<?php echo date('Y-m-d H:i', strtotime($leave->end_time));?><label class='ml20 inline'>共&nbsp;<?php echo $leave->total_days; ?>&nbsp;天</label></td></tr>
        <tr><th class="w130 center">请假事由</th><td><?php echo $leave->content; ?></td></tr>
        <?php if(!empty($leave->delay)): ?>
        <tr><th class="w130 center">延迟提交原因</th><td><?php echo $leave->delay; ?></td></tr>
        <?php endif; ?>
        <?php if(!empty($logs)): ?>
        <!-- 审批的框 -->
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
        <?php endif; ?>
        <!-- 附件 -->
        <?php if(!empty($leave->image)): ?>
        <tr>
            <th class="w130 center">附件</th>
            <td>
                <img src="<?php echo $leave->image; ?>" class="pointer w400" onclick="window.open('<?php echo $leave->image; ?>');">
            </td>
        </tr>
        <?php endif; ?>
    </table>
    <?php endif; ?>
</div>

<script type="text/javascript">
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
    
    if (document.all){
        window.attachEvent('onload',ulContent);
    }
    else{
        window.addEventListener('load',ulContent,false);
    }

    
</script>