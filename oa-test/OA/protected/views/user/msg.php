<?php
echo "<script type='text/javascript'>";
echo "console.log('msg');";
echo "</script>";
?>
<!-- 主界面 -->
<div class="center">
    <!-- 进度条 -->
    <div class="bor-l-1-ddd bor-r-1-ddd">
        <ul class="nav nav-justified" id="ulContenter">

            
        </ul>
    </div>
    <!-- 请假详情表格 -->
    <?php if(!empty($leave)): ?>
    <table  class="table table-bordered left">
        <tr>
            <th class="w130 center bg-fa">姓名</th>
            <td class="w200"><?php echo $leave->user->cn_name; ?></td>
            <th class="w130 center bg-fa">部门</th>
            <td class="w200"><?php echo $leave->user->department->name; ?></td>
            <th class="w130 center bg-fa">岗位</th>
            <td class="w300"><?php echo $leave->user->title; ?></td>
        </tr>
        <tr>
            <th class="w130 center bg-fa">填写时间</th>
            <td><?php echo $leave->create_time; ?></td>
            <th class="w130 center bg-fa">请假类型</th>
            <td><?php echo $leave->cntype; ?></td>
            <th class="w130 center bg-fa">请假时间</th>
            <td>
                <?php echo date('Y-m-d H:i', strtotime($leave->start_time)); ?>&nbsp;&nbsp;到&nbsp;&nbsp;<?php echo date('Y-m-d H:i', strtotime($leave->end_time));?><label class='ml10 inline'>共&nbsp;<?php echo $leave->total_days; ?>&nbsp;天</label>
            </td>
        </tr>
        
        <tr><th class="w130 center bg-fa">请假事由</th><td colspan="5"><?php echo $leave->content; ?></td></tr>
        <?php if(!empty($leave->delay)): ?>
        <tr><th class="w130 center bg-fa">延迟提交原因</th><td colspan="5"><?php echo $leave->delay; ?></td></tr>
        <?php endif; ?>
        <!-- 审批的框 -->
        <?php if(!empty($logs)): ?>
        <?php foreach($logs as $log): ?>
        <tr>
            <th class="w130 center bg-fa"><?php echo $log->user->department->name; ?>审批</th>
            <td colspan="5">
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php elseif($log->action == 'reject'): ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;">
                            <?php echo $leave->reason; ?>
                        </div>
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
        <!-- 需要上传附件 并且 附件为空 并且 审批状态不为拒绝的时候显示提示 -->
        <?php if(($leave->type == "sick" || $leave->type == "marriage" || $leave->type == "maternity") && empty($leave->image) && $leave->status != "reject"): ?>
        <?php if(!empty($this->user) && $this->user->user_id == $leave->user->user_id): ?>
        <tr>
            <th class="w130 center bg-fa">提示</th>
            <td class="b2" colspan="5">
                <?php if($leave->type == "sick"): ?>
                (休假后3个工作日内补交病假证明扫描件上传)
                <?php elseif($leave->type == "marriage"): ?>
                (休假后7个工作日内补交结婚证扫描件上传)
                <?php elseif($leave->type == "maternity"): ?>
                (休假后15个工作日内补交婴儿出生证明扫描件上传)
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>
        <!-- 已有上传附件 -->
        <?php if(!empty($leave->image) && file_exists('.'.$leave->image)): ?>
        <tr id="img-tr">
            <th class="w130 center bg-fa">附件</th>
            <td colspan="5">
                <img src="<?php echo '.'.$leave->image;?>" class="w400 pointer" onclick="window.open('<?php echo $leave->image;?>');">
                <?php if(!empty($this->user) && $this->user->user_id == $leave->user->user_id): ?>
                <a class="pointer ml10" onclick="showUpload();">重新上传</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
        <!-- 本人 并且 审批状态不为拒绝的时候显示上传 -->
        <?php if(!empty($this->user) && $this->user->user_id == $leave->user->user_id && $leave->status != "reject" && ($leave->type == "sick" || $leave->type == "marriage" || $leave->type == "maternity")):?>
        <tr id="preImg-tr" <?php if(!empty($leave->image) && file_exists('.'.$leave->image)){echo "class='hidden'";}?>>
           <th class="w130 center bg-fa">附件上传预览</th>
           <td colspan="5" class="left">
            <img class="w400 hidden" id="prevImg-img"></img>
           </td>
        </tr>
        <tr id="attachment-tr" <?php if(!empty($leave->image) && file_exists('.'.$leave->image)){echo "class='hidden'";}?>>
          <th class="w130 center bg-fa"><?php if(!empty($leave->image) && file_exists('.'.$leave->image)){echo "重新上传";}else{echo "附件";}?></th>
          <td colspan="5" id="attanchment-td">
            <input type="file" id="attachment" onchange="preImg();">
          </td>
        </tr>
        <tr id="upload-fun-tr" <?php if(!empty($leave->image) && file_exists('.'.$leave->image)){echo "class='hidden'";}?>>
            <th class="w130 center bg-fa">操作</th>
            <td colspan="5">
                <button class="btn btn-success w100 disabled" onclick="uploadAttachment();" id="upload-btn">提交附件</button>
                <?php if(!empty($leave->image) && file_exists('.'.$leave->image)): ?>
                <a class="pointer ml10" onclick="cancelUpload();">取消</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>
    <?php endif; ?>
    <button class="btn btn-lg btn-default w100" onclick="location.href='/user/leave';">返回</button><!-- 返回按钮 -->
</div>


<!-- js -->
<script type="text/javascript">
    // 显示上传按钮
    function showUpload(){
        $("#img-tr").addClass("hidden");
        $("#attachment-tr").removeClass("hidden");
        $("#upload-fun-tr").removeClass("hidden");
        $("#preImg-tr").removeClass("hidden");
    }

    // 取消上传
    function cancelUpload(){
        $("#img-tr").removeClass("hidden");
        $("#attachment-tr").addClass("hidden");
        $("#upload-fun-tr").addClass("hidden");
        $("#preImg-tr").addClass("hidden");
    }

    // 清空并重置上传组件
    function resetAttachment(){
        $("#attanchment-td").children().remove();
        var str = "<input type='file' id='attachment' onchange='preImg();'>";
        $("#attanchment-td").append(str);
        $("#prevImg-img").attr("src", "");
    }

    // 附件预览
    function preImg(){
        var file_type = document.getElementById("attachment").files[0].type;  // 文件类型
        var file_size = document.getElementById("attachment").files[0].size;  // 文件大小
        if(file_type.indexOf("image") < 0){  // 图片验证
            showHint("提示信息","请选择jpg或png或gif格式的图片");
            $("#prevImg-img").attr("src", "");
            resetAttachment();
            $("#upload-btn").addClass("disabled");
        }else if(file_size > 5242880){ // 大小验证
            showHint("提示信息","请选择小于5M的照片");
            $("#prevImg-img").attr("src", "");
            resetAttachment();
            $("#upload-btn").addClass("disabled");
        }else{
            var reader = new FileReader();
            reader.onload = function(e){
                //给预览图src赋值
                var src = this.result;
                var img = document.getElementById("prevImg-img");
                img.src = src; 
            }
            reader.readAsDataURL(document.getElementById("attachment").files[0]);
            $("#prevImg-img").removeClass("hidden");
            $("#upload-btn").removeClass("disabled");
        }
        $("#preImg-tr").removeClass("hidden");
    }

    // 上传附件
    function uploadAttachment(){
        var fileObj = document.getElementById("attachment").files[0];  // 文件对象
        if(fileObj != "undefined"){
            // 表单地址
            var url = "/ajax/leaveUploadPicture";

            // 填充数据到表单中
            var form = new FormData();
            form.append("file", fileObj);
            form.append("id", "<?php echo empty($leave->leave_id) ? '0' :$leave->leave_id; ?>");

            // XMLHttp请求对象
            var xhr = new XMLHttpRequest();
            xhr.open("post", url, true);
            xhr.send(form);

            // 回调函数
            xhr.onreadystatechange = function(){
                if(xhr.readyState==4 && xhr.status==200){
                    var response = xhr.responseText;
                    // 从xml字符串转换成xml对象
                    try{
                        domParser = new  DOMParser();
                        xmlDoc = domParser.parseFromString(response, 'text/xml');
                        var code = xmlDoc.getElementsByTagName("code")[0].childNodes[0].nodeValue;
                        var url = xmlDoc.getElementsByTagName("url")[0].childNodes[0].nodeValue;
                    }catch(e){
                        showHint("提示信息","解析返回信息失败，请重试");
                    }
                    if(code == 0){
                        showHint("提示信息","上传成功");
                        setTimeout(function(){location.reload();},1200);
                    }else if(code == -1){
                        showHint("提示信息","上传失败");
                    }else if(code == -2){
                        showHint("提示信息","参数错误");
                    }else if(code == -3){
                        showHint("提示信息","查找不到此请假申请");
                    }else if(code == -4){
                        showHint("提示信息","请上传小于5M的照片");
                    }else if(code == -5){
                        showHint("提示信息","请上传jpg、png或gif格式的照片");
                    }else if(code == -99){
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            }
        }
    }

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
    window.onload = function(){
        ulContent();
    }
    
</script>
