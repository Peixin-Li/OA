<?php
echo "<script type='text/javascript'>";
echo "console.log('positiveApplyDetail');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">转正申请详情</h4>
	<div class="bor-l-1-ddd bor-r-1-ddd">
		<!-- 进度 -->
		<ul class="nav nav-justified">
	        <li class="bg-66 flow-li">
	            <h4 class="white m0 mt5 center">1.提交转正申请</h4>
	            <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
	        </li>

	        <?php 
	        	if(!empty($procedure)){
	        		$no_num = 2;
	        		$count = count($procedure);
	        		$last_status = "";
	        		foreach($procedure as $row){
	        			if($row[1] == "agree"){
							echo "<li class='bg-66 flow-li'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
	        			}else if($row[1] == "reject" || $row[1] == "delay"){
	        				echo "<li class='flow-li-red bg-99'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
	        			}else{
	        				echo "<li><h4 class='m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
	        			}
	        			$last_status = $row[1];
	        			$no_num++;
	        		}
	        		if($last_status == "agree"){
	        			echo "<li class='bg-66'><h4 class='white m0 mt5 center'>{$no_num}.转正申请结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
	        		}else if($last_status == "reject"  || $row[1] == "delay"){
	        			echo "<li class='bg-99'><h4 class='white m0 mt5 center'>{$no_num}.转正申请结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
	        		}else{
	        			echo "<li><h4 class='m0 mt5 center'>{$no_num}.转正申请结果</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
	        		}
	        	}
	        ?>
	    </ul>
	</div>
	<!-- 转正申请详情表格 -->
	<table class="table table-bordered center" id="work-content-table">
		<tr>
			<th class="w80 center">姓名</th>
            <td class="hidden" id="apply-id"><?php echo $apply->id; ?></td>
			<td><?php echo $apply->user->cn_name; ?></td>
			<th class="w50 center">部门</th>
			<td colspan="2"><?php echo $apply->user->department->name; ?></td>
			<th class="w50 center">职位</th>
			<td colspan="2"><?php echo $apply->user->title; ?></td>
		</tr>
		<tr>
			<th class="w50 center">入职时间</th>
			<td class="w150"><?php echo $apply->user->entry_day; ?></td>
			<th class="w80 center">转正前薪资</th>
            <td><?php echo $apply->trial_salary; ?>元/月</td>
            <th class="w80 center">约定薪资</th>
            <td><?php echo $apply->promise_salary; ?>元/月</td>
			<th class="w80 center">工作年限</th>
			<td><?php echo $apply->work_life; ?>年</td>
		</tr>
		<tr>
			<th class="w80 center">序号</th>
			<th class="w200 center">工作内容</th>
			<th class="w50 center">占比</th>
			<th class="w100 center"></th>
			<th class="w80 center"></th>
<!--             <th class="w100 center">参考标准</th>
            <th class="w80 center">工作量</th> -->
			<th class="w50 center">完成率</th>
			<!-- <th class="w50 center">延误率</th> -->
            <th class="w50 center"></th>
			<th class="w50 center">返工率</th>
		</tr>
		<?php $reports = $apply->report; foreach($reports as $report): ?>
		<tr>
            <td><?php echo $report->serial ; ?></td>
			<td><?php echo $report->content ; ?></td>
			<td><?php echo $report->proportion ; ?>%</td>
			<td><?php //echo $report->reference ; ?></td>
			<td><?php //echo $report->quantity ; ?></td>
			<td><?php echo $report->completion_rate ; ?>%</td>
			<td><?php //echo $report->delay_rate ; ?></td>
			<td><?php echo $report->rework_rate ; ?>%</td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<th class="w80 center">员工自评</th>
			<td colspan="7" class="left"><?php echo $apply->evaluation; ?></td>
		</tr>
		<tr>
			<th class="w80 center">个人规划</th>
			<td colspan="7" class="left"><?php echo $apply->plan; ?></td>
		</tr>
		<tr>
			<th class="w80 center">意见与建议</th>
			<td colspan="7" class="left"><?php echo $apply->suggest; ?></td>
		</tr>
		<!-- 审批的框 -->
		<?php $logs = $apply->allLogs(); foreach($logs as $log): ?>
		<tr>
        <th class="w80 center"><?php echo $log->user->department->name; ?><br />审批</th>
            <td colspan="7">
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80 left">
						<?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <h5 class="w400 f15px">转正日期：<?php if(!empty($log->qualify_date)) echo $log->qualify_date; ?>&nbsp;&nbsp;&nbsp;调整薪资：<?php if(!empty($log->qualify_salary)) echo $log->qualify_salary; ?>元/月</h5>
						<?php elseif($log->action == 'reject'): ?>
                        <h5 class="w200 f15px">不同意</h5>
						<?php elseif($log->action == 'delay'): ?>
                        <h5 class="w200 f15px">延迟转正</h5>
						<?php endif; ?>

                        <h5 class="xw600 f15px" style="word-break:break-all;">评语：<?php echo $log->comment; ?></h5>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                        <h5 class="w200 center">签名：<?php echo $log->user->cn_name; ?><span></span></h5>
                        <h5 class="w200 center">审批日期：<?php echo date('Y-m-d',strtotime($log->create_time)); ?><span></span></h5>
                    </div>
                </div>
            </td>
        </tr>

        <?php 
        	if(!empty($log->qualify_salary)){
        		$qualify_salary = $log->qualify_salary;
        	}
        	if(!empty($log->qualify_date)){
        		$qualify_date = $log->qualify_date;
        	}
    	?>
		<?php endforeach; ?>
		<?php if(!empty($user)): ?>
		<?php if($user->user_id == $apply->next): ?>
		<tr>
			<th class="w50 center">操作</th>
			<td colspan="7" class="left"><button class="btn btn-success w100" onclick="showEvaluate();">填写评价</button></td>
		</tr>
		<?php endif; ?>
		<?php endif; ?>
	</table>
</div>
<!-- 模态框 -->
<div id="evaluate-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">转正评价</h4>
    </div>

    <div class="modal-body">
        <table class="table bor-1-ddd m0" id="evaluate-table">
          <tr>
            <th class="w130 center">操作</th>
            <td id="positive-id" class="hidden"></td>
            <td>
            	<input type="radio" name="decision" value="agree" checked onchange="decisionChange();">&nbsp;建议转正&nbsp;&nbsp;
            	
            	<!-- 部门负责人 -> sara -> verky（非人事总监非CEO则显示） -->
            	<?php if($qulify_type == 1 && $admin_id != Yii::app()->session['user_id'] && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<!-- 部门负责人 -> verky（非CEO则显示） -->
            	<?php if($qulify_type == 2 && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<!-- verky -> sara（是CEO则显示） -->
            	<?php if($qulify_type == 3 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<!-- verky （是CEO则显示） -->
            	<?php if($qulify_type == 4 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<input type="radio" name="decision" value="reject" onchange="decisionChange();">&nbsp;不予转正
            </td>
          </tr>
          <tr class="agree-tr">
            <th class="w130 center">转正日期</th>
            <td><input class="form-control w150 pointer" placeholder="请选择转正日期" id="entry-date" value="<?php if(!empty($qualify_date)){echo $qualify_date;}else{echo date('Y-m-d',strtotime("+1months", strtotime($apply->user->entry_day)));} ?>"></td>
          </tr>
          <tr class="agree-tr">
            <th class="w130 center">薪资调整</th>
            <td>	
            	<!-- 部门负责人 -> sara -> verky（非人事总监非CEO则显示） -->
            	<?php if($qulify_type == 1 && $admin_id != Yii::app()->session['user_id'] && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();"  checked>&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" value="<?php echo empty($apply->promise_salary) ? '':$apply->promise_salary; ?>">&nbsp;元/月</br>
            	<?php endif; ?>

            	<!-- 部门负责人 -> verky（非CEO则显示） -->
            	<?php if($qulify_type == 2 && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();"  checked>&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" value="<?php echo empty($apply->promise_salary) ? '':$apply->promise_salary; ?>">&nbsp;元/月</br>
            	<?php endif; ?>

            	<!-- verky -> sara（是CEO则显示） -->
            	<?php if($qulify_type == 3 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();"  checked>&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" value="<?php echo empty($apply->promise_salary) ? '':$apply->promise_salary; ?>">&nbsp;元/月</br>
            	<?php endif; ?>

            	<!-- verky （是CEO则显示） -->
            	<?php if($qulify_type == 4 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();"  checked>&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" value="<?php echo empty($apply->promise_salary) ? '':$apply->promise_salary; ?>">&nbsp;元/月</br>
            	<?php endif; ?>
            	<div class="inline-block mr20 mt10 <?php if(!empty($qualify_salary)) echo 'hidden';?>">
            		<input type="radio" name="salary"  value="modify" onchange="changeSalary();">&nbsp;薪资调整&nbsp;&nbsp;&nbsp;&nbsp;
            	</div>
            	<input class="form-control inline w100" id="change-salary" readonly="readonly"  value="<?php if(!empty($qualify_salary)) echo $qualify_salary; ?>">&nbsp;元/月&nbsp;&nbsp;
            </td>
          </tr>
          <tr class="hidden delay-tr">
            <th class="w130 center">试用期结束日期</th>
            <td><input class="form-control w150 pointer" placeholder="请选择转正日期" id="probation-end-date"></td>
          </tr>
          <tr>
          	<th class="w130 center">评语</th>
          	<td><textarea class="form-control" id="opinion"></textarea></td>
          </tr>
        </table>
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="sendEvaluate();">提交</button>
    </div>
</div>


<!-- js -->
<script type="text/javascript">
	// 评价-显示
	function showEvaluate(){
		var ySet = (window.innerHeight - $("#evaluate-div").height())/2;
    	var xSet = (window.innerWidth - $("#evaluate-div").width())/2;
     	$("#evaluate-div").css("top",ySet);
      	$("#evaluate-div").css("left",xSet);
     	$("#evaluate-div").modal({show:true});
	}

	// 决定-显示
	function decisionChange(){
		var decision = $("input[name='decision']:checked").val();
		if(decision == "agree"){
			$(".agree-tr").removeClass("hidden");
			$(".delay-tr").addClass("hidden");
		}else if(decision == "delay"){
			$(".agree-tr").addClass("hidden");
			$(".delay-tr").removeClass("hidden");
		}else{
			$(".agree-tr").addClass("hidden");
			$(".delay-tr").addClass("hidden");
		}
	}

	// 选择薪资调整的类型
	function changeSalary(){
		var type = $("input[name='salary']:checked").val();
		if(type == "contract"){
			$("#unchange-salary").removeAttr("readonly");
			$("#change-salary").attr("readonly", "readonly");
		}else{
			$("#change-salary").removeAttr("readonly");
			$("#unchange-salary").attr("readonly", "readonly");
		}
	}

	// 页面初始化
	$(document).ready(function(){
		$('#entry-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
		$('#probation-end-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    	$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

	// 发送评价
	function sendEvaluate(){
		var id = $("#apply-id").text();
		var comment = $("#opinion").val();
		var decision = $("input[name='decision']:checked").val();
		if(decision == "agree"){
			var entry_date = $("#entry-date").val();
			var type = $("input[name='salary']:checked").val();
			var qualify_salary = "";
			if(type == "contract"){
				qualify_salary = $("#unchange-salary").val();
			}else{
				qualify_salary = $("#change-salary").val();
			}

			var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
			var d_pattern = /^\d+$/;
			if(entry_date == ""){
				showHint("提示信息","请输入转正日期");
			}else if(!date_pattern.exec(entry_date)){
				showHint("提示信息","转正日期输入格式错误");
			}else if(type == "contract" && qualify_salary == ""){
				showHint("提示信息","请输入调整的薪资");
				$("#unchange-salary").focus();
			}else if(type == "contract" && !d_pattern.exec(qualify_salary)){
				showHint("提示信息","调整的薪资输入格式错误");
				$("#unchange-salary").focus();
			}else if(type == "modify" && qualify_salary == ""){
				showHint("提示信息","请输入调整的薪资");
				$("#change-salary").focus();
			}else if(type == "modify" && !d_pattern.exec(qualify_salary)){
				showHint("提示信息","调整的薪资输入格式错误");
				$("#change-salary").focus();
			}else if(comment == ""){
				showHint("提示信息","请输入评语");
				$("#opinion").focus();
			}else{
				$.ajax({
	                type:'post',
	                url:'/ajax/agreeApplyQualify',
	                dataType:'json',
	                data:{'id':id, 'qualify_date':entry_date, 'qualify_salary':qualify_salary, 'comment':comment, 'type':type},
	                success:function(result){
	                    if(result.code == 0){
	                        showHint("提示信息","同意转正申请提交成功");
	                        setTimeout(function(){location.reload();},1200);  
	                    }else if(result.code == '-1'){
	                        showHint("提示信息","同意转正申请提交失败");
	                    }else if(result.code == '-2'){
	                        showHint("提示信息","参数错误");
	                    }else if(result.code == '-3'){
	                        showHint("提示信息","找不到该申请");
	                    }else if(result.code == '-4'){
                            showHint("提示信息","输入的字符串太长，最大的字符长度为5000");
                        }else{
	                        showHint("提示信息","你没有权限执行此操作");
	                    }
	                }
	            });
			}
		}else if(decision == "delay"){
			var qualify_date = $("#probation-end-date").val();
			var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
			if(qualify_date == ""){
				showHint("提示信息","请输入试用期结束日期");
			}else if(!date_pattern.exec(qualify_date)){
				showHint("提示信息","试用期结束日期输入格式错误");
			}else if(comment == ""){
				showHint("提示信息","请输入评语");
				$("#opinion").focus();
			}else{
				$.ajax({
	                type:'post',
	                url:'/ajax/delayApplyQualify',
	                dataType:'json',
	                data:{'id':id, 'comment':comment, 'qualify_date':qualify_date},
	                success:function(result){
	                    if(result.code == 0){
	                        showHint("提示信息","延长试用期提交成功");
	                        setTimeout(function(){location.reload();},1200);  
	                    }else if(result.code == '-1'){
	                        showHint("提示信息","延长试用期提交失败");
	                    }else if(result.code == '-2'){
	                        showHint("提示信息","参数错误");
	                    }else if(result.code == '-3'){
	                        showHint("提示信息","找不到该申请");
	                    }else{
	                        showHint("提示信息","你没有权限执行此操作");
	                    }
	                }
	            });
			}
		}else{
			if(comment == ""){
				showHint("提示信息","请输入评语");
				$("#opinion").focus();
			}else{
				$.ajax({
	                type:'post',
	                url:'/ajax/rejectApplyQualify',
	                dataType:'json',
	                data:{'id':id, 'comment':comment},
	                success:function(result){
	                    if(result.code == 0){
	                        showHint("提示信息","退回转正申请提交成功");
	                        setTimeout(function(){location.reload();},1200);  
	                    }else if(result.code == '-1'){
	                        showHint("提示信息","退回转正申请提交失败");
	                    }else if(result.code == '-2'){
	                        showHint("提示信息","参数错误");
	                    }else if(result.code == '-3'){
	                        showHint("提示信息","找不到该申请");
	                    }else{
	                        showHint("提示信息","你没有权限执行此操作");
	                    }
	                }
	            });
			}
		}
	}
</script>
