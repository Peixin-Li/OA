<?php
echo "<script type='text/javascript'>";
echo "console.log('overtimeDetail');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-1-ddd">加班申请详情</h4>
    <div class="bor-l-1-ddd bor-r-1-ddd">
        <!-- 进度 -->
        <ul class="nav nav-justified">
            <li class="bg-66 flow-li">
                <h4 class="white m0 mt5 center"><?php if(!empty($data->type) && $data->type != 'normal') { echo '提交加班申请'; } else { echo '加班申请结果'; }; ?></h4>
                <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
            </li>
            <?php foreach($procedure as $row) : ?>
                <?php if($row['status'] == "agree"): ?>
                    <li class="bg-66 flow-li">
                        <h4 class="m0 mt5 center white"> <?php echo $row['department'] ?> </h4>
                        <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
                    </li>
                <?php elseif($row['status'] == "reject"): ?>
                    <li class="bg-99">
                        <h4 class="m0 mt5 center white"> <?php echo $row['department'] ?> </h4>
                        <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-remove-sign"></span></div>
                    </li>
                <?php else: ?>
                    <li>
                        <h4 class="m0 mt5 center"> <?php echo $row['department'] ?> </h4>
                        <div class="center"><span class="mt5 mb10 f18px  glyphicon glyphicon-time"></span></div>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if($row['status'] == "agree"): ?>
                <li class="bg-66">
                    <h4 class="m0 mt5 center white">加班申请结果</h4>
                    <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
                </li>
            <?php elseif($row['status'] == "reject"): ?>
                <li class="bg-99">
                    <h4 class="m0 mt5 center white">加班申请结果</h4>
                    <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-remove-sign"></span></div>
                </li>
            <?php else: ?>
                <li>
                    <h4 class="m0 mt5 center">加班申请结果</h4>
                    <div class="center"><span class="mt5 mb10 f18px  glyphicon glyphicon-time"></span></div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <!-- 加班申请详情 -->
    <?php if(!empty($data)): ?>
    <table  class="table table-bordered table-hover">
        <tr>
            <th class="w130 center">填写时间</th>
            <td class="w200"><?php echo $data->create_time;?></td>
            <th class="w130 center">姓名</th>
            <td class="w200"><?php echo $data->user->cn_name;?></td>
            <th class="w130 center">部门</th>
            <td class="w200"><?php echo $data->user->department->name;?></td>
        </tr>
        <tr>
            <th class="w130 center">加班时间</th>
            <td colspan="5" class="left"><?php echo ($data['type'] == "holiday") ? substr($data['start_time'], 0, 16)." 至 ".substr($data['end_time'], 0, 16) : substr($data['end_time'], 0, 16);?></td>
        </tr>
        <tr>
            <th class="w130 center">工作内容</th>
            <td colspan="5"><?php echo $data['content']; ?></td>
        </tr>
        <?php if(!empty($data->logs)): ?>
        <?php foreach($data->logs as $log): ?>
        <tr>
            <th class="w130 center"><?php echo $log->user->department->name?></th>
            <td colspan="5" class="left">
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php else: ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;"><?php echo $log->reason; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name;?></span></h5>
                        <?php endif; ?>
                        <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d',strtotime($log->create_time));?></span></h5>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php if(!empty($this->user) && $this->user->user_id == $data->next ):?>
        <tr>
            <th class="w130 center">回复操作</th>
            <td colspan="7" class="left">
                <button class="btn btn-success w100" onclick="agree();">同意</button>
                <button class="btn btn-primary w100 ml20" onclick="reject();">不同意</button>
            </td>
        </tr>
         <?php endif; ?>
    </table>
    <?php endif; ?>
</div>

<!-- 不同意按钮模态框 -->
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
    var overtime_id = "<?php echo empty($data) ? '' : $data['id']; ?>";
    // 同意
    function agree(){
        var reason = "";
        $.ajax({
            type:'post',
            url:'/ajax/approveOvertime',
            dataType:'json',
            data:{'id':overtime_id, 'action':"agree", 'reason':reason},
            success:function(result){
                if(result.code == 0){
                  showHint("提示信息","提交成功");
                  setTimeout(function(){location.reload();},1200);
                }else if(result.code == '-1'){
                  showHint("提示信息","提交失败");
                }else if(result.code == '-2'){
                  showHint("提示信息","参数错误");
                }else if(result.code == '-3'){
                  showHint("提示信息","查找不到此加班信息");
                }else{
                  showHint("提示信息","你没有权限执行此操作");
                }
              }
        });
    }

    // 拒绝
    function reject(){
        $("#reject-reason-div").css("top","30%");
        $("#reject-reason-div").modal({show:true});
    }

    // 发送拒绝
    function rejectSubmit(){
        var reason = $("#reject-input").val();
        if(reason == ""){
            showHint("提示信息", "请输入原因");
            $("#reject-input").focus();
        }else{
            $.ajax({
                type:'post',
                url:'/ajax/approveOvertime',
                dataType:'json',
                data:{'id':overtime_id, 'action':"reject", 'reason':reason},
                success:function(result){
                    if(result.code == 0){
                      showHint("提示信息","提交成功");
                      setTimeout(function(){location.reload();},1200);
                    }else if(result.code == '-1'){
                      showHint("提示信息","提交失败");
                    }else if(result.code == '-2'){
                      showHint("提示信息","参数错误");
                    }else if(result.code == '-3'){
                      showHint("提示信息","查找不到此加班信息");
                    }else{
                      showHint("提示信息","你没有权限执行此操作");
                    }
                  }
            });
        }
    }  
</script>
