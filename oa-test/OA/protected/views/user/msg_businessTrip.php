<div class="center">
    <div class="bor-l-1-ddd bor-r-1-ddd">
        <ul class="nav nav-justified">
            <?php if(!empty($out) && !empty($procedure)): ?>
            <li class="bg-66 flow-li">
                <h4 class="white m0 mt5 center">提交出差申请</h4>
                <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
            </li>
            <?php
                $i = 0;
                $f_tag = 0;
                foreach($procedure as $procedure_detial){
                    if(!empty($logs[$i])){
                        switch($logs[$i]->status){
                            case "agree":{
                                $li_css = "flow-li bg-66";
                                $h4_css = "white";
                                $span_css = "glyphicon-ok-sign white";
                                break;
                            }
                            case "reject":{
                                $li_css = "flow-li-red bg-99";
                                $h4_css = "white";
                                $span_css = "glyphicon-remove-sign white";
                                $f_tag = 1;
                                break;
                            }
                            default:{
                                break;
                            }
                        }
                    }else if($f_tag==1){
                        $li_css = "flow-li-red bg-99";
                        $h4_css = "white";
                        $span_css = "glyphicon-remove-sign white";
                    }else{
                        $li_css = "";
                        $h4_css = "";
                        $span_css = "glyphicon-time";
                    }

                    echo "<li class='".$li_css."'><h4 class='m0 mt5 center ".$h4_css."'>".$procedure_detial['department']."</h4><div class='center'><span class='mt5 mb10 f18px glyphicon ".$span_css."'></span></div></li>";
                    $i++;
                }
                if($li_css == "flow-li bg-66"){
                    $result_css = "bg-66";
                }else if($li_css == "flow-li-red bg-99"){
                    $result_css = "bg-99";
                }else{
                    $result_css = "";
                }
                echo "<li class='".$result_css."'><h4 class='m0 mt5 center ".$h4_css."'>出差申请结果</h4><div class='center '><span class='mt5 mb10 f18px glyphicon ".$span_css."'></span></div></li>";
            ?>
            <?php else: ?>
            <li class="bg-66">
                <h4 class="white m0 mt5 center">申请成功</h4>
                <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
            </li>
            <?php endif; ?>
        </ul>
    </div><!-- 进度条 -->
    
    <div class="hidden" id="id"><?php echo "{$out->out_id}"; ?></div>
    <?php if(!empty($out)): ?>
    <table  class="table table-bordered table-hover left">
        <tr>
            <th class="w130 center bg-fa">姓名</th>
            <td><?php echo $out->user->cn_name; ?></td>
            <th class="w130 center bg-fa">部门</th>
            <td><?php echo $out->user->department->name; ?></td>
            <th class="w130 center bg-fa">职位</th>
            <td><?php echo $out->user->title; ?></td>
        </tr>
        <tr>
            <th class="w130 center bg-fa">填写日期</th>
            <td colspan="5"><?php echo $out->create_time; ?></td>
        </tr>
        <tr>
            <th class="w130 center bg-fa">出差类型</th>
            <td colspan="5"><?php if($out->type == "meeting"){echo "会议";}else if($out->type == "business"){echo "商务洽谈";}else{echo "市内外出";}; ?></td>
        </tr>
        <tr>
            <th class="w130 center bg-fa"><?php if($out->type == "meeting"){echo "会议名称";}else if($out->type == "recruit"){echo "大学名称";}else{echo "公司名称";} ?></th>
            <td colspan="5"><?php echo $out->company; ?></td>
        </tr>
        <tr>
            <th class="w130 center bg-fa">出差地点</th>
            <td colspan="5"><?php echo $out->place; ?></td>
        </tr>
        <?php if($out->type != "out"): ?>
        <tr>
            <th class="w130 center bg-fa">交通工具</th>
            <td colspan="5">
                <?php echo join(json_decode($out->transport, true), '、');?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="w130 center bg-fa">出差日期</th>
            <td colspan="5">
                <?php echo ($out->date_type != "normal") ? date('Y-m-d' ,strtotime($out->start_time)):date('Y-m-d H:i' ,strtotime($out->start_time)); ?>&nbsp;&nbsp;到&nbsp;&nbsp;<?php echo ($out->date_type != "normal") ? date('Y-m-d' ,strtotime($out->end_time)):date('Y-m-d H:i' ,strtotime($out->end_time));?>
                <?php if($out->date_type == "morning"){echo "仅上午(09:30-12:00)";}else if($out->date_type == "afternoon"){echo "仅下午(13:30-18:30)";} ?>
                <label class='ml20 inline'>共&nbsp;<?php echo $out->total_days; ?>&nbsp;天</label>
            </td>
        </tr>
        <tr>
            <th class="w130 center bg-fa">同行人员</th>
            <td colspan="5">
                <?php if(!empty($out->members) && count($out->members) > 1): ?>
                <?php foreach($out->members as $mrow): ?>
                <span class="mr10"><?php echo $mrow->user->cn_name; ?></span>
                <?php endforeach; ?>
                <?php else: ?>
                无
                <?php endif; ?>
            </td>  
        </tr>
        <?php if($out->type != "out"): ?>
        <tr>
            <th class="w130 center bg-fa">预计费用</th>
            <td colspan="5"><?php $cost = explode(".", $out->cost); echo $cost[0];?>&nbsp;元</td>
        </tr>
        <tr>
            <th class="w130 center bg-fa">行程说明</th>
            <td colspan="5"><?php echo $out->plan; ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th class="w130 center bg-fa">出差事由</th>
            <td colspan="5"><?php echo $out->content; ?></td>
        </tr>
        <?php if(!empty($out->delay)):?>
        <tr>
            <th class="w130 center bg-fa">延迟提交理由</th>
            <td colspan="5"><?php echo $out->delay; ?></td>
        </tr>
        <?php endif;?>
        <?php if($out['type'] != "out"): ?>
        <?php if(!empty($logs)): ?>
        <?php foreach($logs as $log): ?>
        <tr>
        <th class="w130 center bg-fa"><?php echo $log->user->department->name; ?>审批</th>
            <td colspan="5">
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->status == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php elseif($log->status == 'reject'): ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;"><?php echo $out->reason; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                    <?php if($log->status == 'agree'): ?>
                    <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name; ?></span></h5>
                    <?php endif; ?>
                    <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d' , strtotime($log->create_time)); ?></span></h5>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php if($out->status == 'wait' && $out->next == Yii::app()->session['user_id']): ?>
        <tr>
            <th class="w130 center">回复操作</th>
            <td>
                <button class="btn btn-success w100" id="agree">同意</button>
                <button class="btn btn-primary w100 ml20" id="reject">不同意</button>
            </td>
        </tr>
        <?php endif; ?>
        <?php endif; ?>
    </table><!-- 出差详情表格 -->
    <?php endif; ?>
    <button class="btn btn-lg btn-default w100" onclick="location.href='/user/businessTrip';">返回</button><!-- 返回按钮 -->
</div>

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
        <button class="w100 btn btn-success" onclick="rejectSubmit()" data-dismiss="modal">提交</button>
    </div>
</div><!-- 退回出差申请模态框 -->

<script type="text/javascript">
    // 页面初始化
    $(document).ready(function(){
        var pattern = /^\d+$/;

        // 同意申请
        $("#agree").click(function(){
            var id = $("#id").text();
            if(!pattern.exec(id)){
                showHint("提示信息","请刷新页面");
            }else{
                $.ajax({
                    type:'post',
                    dataType:'json',
                    url:'/ajax/agreeOut',
                    data:{'id':id},
                    success:function(result){
                        if(result.code == 0){
                            showHint("提示信息","同意成功");
                            setTimeout(function(){location.reload();},1200);
                        }else if(result.code == -1){
                            showHint("提示信息","同意出差失败！");
                        }else if(result.code == -2){
                            showHint("提示信息","参数错误！");
                        }else if(result.code == -3){
                            showHint("提示信息","找不到该出差单！");
                        }else{
                            showHint("提示信息","你没有权限进行此操作！");
                        }
                    }
                });
            }
        });

        // 退回申请
        $("#reject").click(function(){
            // 显示退回申请模态框
            var ySet = (window.innerHeight - $("#reject-reason-div").height())/2;
            var xSet = (window.innerWidth - $("#reject-reason-div").width())/2;
            $("#reject-reason-div").css("top",ySet);
            $("#reject-reason-div").css("left",xSet);
            $("#reject-reason-div").modal({show:true});

            // 同意和不同意按钮不可点
            $('#agree').addClass('disabled');
            $('#reject').addClass('disabled');
        });
    });

    // 提交退回申请
    function rejectSubmit(){
        var pattern = /^\d+$/;
        var id = $("#id").text();
            var reject_reason = $("#reject-input").val();
            if(!pattern.exec(id)){
                showHint("提示信息","请刷新页面");
            }else{
                $.ajax({
                    type:'post',
                    dataType:'json',
                    url:'/ajax/rejectOut',
                    data:{'id':id,'reason':reject_reason},
                    success:function(result){
                        if(result.code == 0){
                            showHint("提示信息","退回出差单成功");
                            setTimeout(function(){location.reload();},1200);
                        }else if(result.code == -1){
                            showHint("提示信息","不同意申请单失败！");
                        }else if(result.code == -2){
                            showHint("提示信息","参数错误！");
                        }else if(result.code == -3){
                            showHint("提示信息","没有找到此出差单！");
                        }else if(result.code == -99){
                            showHint("提示信息","你没有权限进行此操作！");
                        }
                    }
                });
            }
    }
    </script>
