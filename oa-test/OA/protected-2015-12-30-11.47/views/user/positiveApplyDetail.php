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
<div class="center">
	<!-- 进度条 -->
	<div class="bor-l-1-ddd bor-r-1-ddd">
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

	<?php if(!empty($apply) && !empty($this)): ?>
	<?php if($this->user->user_id == $apply->next): ?>
	<table class="table table-bordered center m0" id="positive-table">
		<caption class="p00">
          <h3 class="center black bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd m0 pd20">转正申请</h3>
        </caption>
		<tr>
			<td id="positive-apply-id" class="hidden"><?php echo empty($apply->id) ? '':$apply->id;?></td>
			<th class="w50 center bg-fa">姓名</th>
			<td><?php if(!empty($this->user)) echo $this->user->cn_name;?></td>
			<th class="w50 center bg-fa">部门</th>
			<td colspan="2"><?php if(!empty($this->user)) echo $this->user->department->name;?></td>
			<th class="w50 center bg-fa">职位</th>
			<td colspan="2"><?php if(!empty($this->user)) echo $this->user->title;?></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">入职时间</th>
			<td><?php if(!empty($this->user)) echo $this->user->entry_day;?></td>
			<th class="w50 center bg-fa">转正前薪资</th>
			<td><?php echo empty($apply->trial_salary) ? '':$apply->trial_salary ;?>元/月</td>
			<th class="w50 center bg-fa">约定薪资</th>
			<td><?php echo empty($apply->promise_salary) ? '':$apply->promise_salary ;?>元/月</td>
			<th class="w50 center bg-fa">工作年限</th>
			<td><?php echo empty($apply->work_life) ? '':$apply->work_life;?>年</td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">序号</th>
			<th class="w200 center bg-fa">工作内容</th>
			<th class="w50 center bg-fa">占比</th>
			<th class="w100 center bg-fa"></th>
			<th class="w80 center bg-fa"></th>
<!--             <th class="w100 center bg-fa">参考标准</th>
            <th class="w80 center bg-fa">工作量</th> -->
			<th class="w50 center bg-fa">完成率</th>
			<th class="w50 center bg-fa"></th>
            <!-- <th class="w50 center bg-fa">延误率</th> -->
			<th class="w50 center bg-fa">返工率</th>
		</tr>
		<tr class="work-content-tr">
			<td>1</td>
			<td><input class="form-control content-input" placeholder="请输入工作内容"></td>
			<td><input class="form-control inline w50 percent-input">&nbsp;%</td>
			<td><input class="form-control standard-input hidden" placeholder="请输入参考标准"></td>
			<td><input class="form-control workload-input hidden" placeholder="请输入工作量"></td>
			<td><input class="form-control inline w50 complete-input" >&nbsp;%</td>
			<td><input class="form-control inline w50 delay-input hidden" ></td>
			<td><input class="form-control inline w50 redo-input" >&nbsp;%</td>
		</tr>
		<tr class="work-content-tr">
			<td>2</td>
			<td><input class="form-control content-input" placeholder="请输入工作内容"></td>
			<td><input class="form-control inline w50 percent-input">&nbsp;%</td>
			<td><input class="form-control standard-input hidden" placeholder="请输入参考标准"></td>
			<td><input class="form-control workload-input hidden" placeholder="请输入工作量"></td>
			<td><input class="form-control inline w50 complete-input" >&nbsp;%</td>
			<td><input class="form-control inline w50 delay-input hidden" ></td>
			<td><input class="form-control inline w50 redo-input" >&nbsp;%</td>
		</tr>
		<tr class=" bg-fa">
			<td colspan="8"><a class="pointer" onclick="newLine(this);">增加一行</a></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">员工自评</th>
			<td colspan="7"><textarea class="form-control" id="self-assessment"></textarea></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">个人规划</th>
			<td colspan="7"><textarea class="form-control" id="personal-plan"></textarea></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">意见</br>与建议</th>
			<td colspan="7"><textarea class="form-control" id="opinion"></textarea></td>
		</tr>
	</table>
    <button class="btn btn-success btn-lg w100 mb15 mt20" onclick="sendpositiveApply();">提交</button>	
    
	<?php else: ?>
	<table class="table table-bordered center" id="work-content-table">
		<tr>
			<th class="w80 center bg-fa">姓名</th>
            <td class="hidden" id="apply-id"><?php echo $apply->id; ?></td>
			<td><?php echo $apply->user->cn_name; ?></td>
			<th class="w50 center bg-fa">部门</th>
			<td colspan="2"><?php echo $apply->user->department->name; ?></td>
			<th class="w50 center bg-fa">职位</th>
			<td colspan="2"><?php echo $apply->user->title; ?></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">入职时间</th>
			<td class="w150"><?php echo $apply->user->entry_day; ?></td>
			<th class="w80 center bg-fa">转正前薪资</th>
            <td><?php echo $apply->trial_salary; ?>元/月</td>
            <th class="w80 center bg-fa">约定薪资</th>
            <td><?php echo $apply->promise_salary; ?>元/月</td>
			<th class="w80 center bg-fa">工作年限</th>
			<td><?php echo $apply->work_life; ?>年</td>
		</tr>
		<tr>
			<th class="w80 center bg-fa">序号</th>
			<th class="w200 center bg-fa">工作内容</th>
			<th class="w50 center bg-fa">占比</th>
			<th class="w100 center bg-fa"></th>
			<th class="w80 center bg-fa"></th>
<!--             <th class="w100 center bg-fa">参考标准</th>
            <th class="w80 center bg-fa">工作量</th> -->
			<th class="w50 center bg-fa">完成率</th>
			<th class="w50 center bg-fa"></th>
            <!-- <th class="w50 center bg-fa">延误率</th> -->
			<th class="w50 center bg-fa">返工率</th>
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
			<th class="w80 center bg-fa">员工自评</th>
			<td colspan="7" class="left"><?php echo $apply->evaluation; ?></td>
		</tr>
		<tr>
			<th class="w80 center bg-fa">个人规划</th>
			<td colspan="7" class="left"><?php echo $apply->plan; ?></td>
		</tr>
		<tr>
			<th class="w80 center bg-fa">意见与建议</th>
			<td colspan="7" class="left"><?php echo $apply->suggest; ?></td>
		</tr>
		<!-- 审批的框 -->
<?php $logs = $apply->allLogs(); foreach($logs as $log): ?>
		<tr>
        <th class="w80 center bg-fa"><?php echo $log->user->department->name; ?><br />审批</th>
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
        	// 转正薪资
        	if(!empty($log->qualify_salary)){
        		$qualify_salary = $log->qualify_salary;
        	}
        	// 转正日期
        	if(!empty($log->qualify_date)){
        		$qualify_date = $log->qualify_date;
        	}
    	?>
		<?php endforeach; ?>
		<?php if(!empty($user) && $user->user_id == $apply->next): ?>
		<tr>
			<th class="w50 center">操作</th>
			<td colspan="7" class="left"><button class="btn btn-success w100" onclick="showEvaluate();">填写评价</button></td>
		</tr>
		<?php endif; ?>
	</table>
	<!-- 返回按钮 -->
	<?php if($apply->status != "success"): ?>
	<button class="btn btn-lg btn-default w100" onclick="location.href='/user/personalPositiveApply';">返回</button>
	<?php endif; ?>
	<?php endif; ?>
	<?php endif; ?>
</div>

<!-- 转正评价模态框 -->
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
            	<?php if($qualify_type == 1 && $admin_id != Yii::app()->session['user_id'] && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<!-- 部门负责人 -> verky（非CEO则显示） -->
            	<?php if($qualify_type == 2 && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<!-- verky -> sara（是CEO则显示） -->
            	<?php if($qualify_type == 3 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<!-- verky （是CEO则显示） -->
            	<?php if($qualify_type == 4 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="decision" value="delay" onchange="decisionChange();">&nbsp;试用期延长&nbsp;&nbsp;
            	<?php endif; ?>

            	<input type="radio" name="decision" value="reject" onchange="decisionChange();">&nbsp;不予转正
            </td>
          </tr>
          <tr class="agree-tr">
            <th class="w130 center">转正日期</th>
            <td><input class="form-control w150 pointer" placeholder="请选择转正日期" id="entry-date" value="<?php if(!empty($qualify_date)) echo $qualify_date; ?>"></td>
          </tr>
          <tr class="agree-tr">
            <th class="w130 center">薪资调整</th>
            <td>	
            	<!-- 部门负责人 -> sara -> verky（非人事总监非CEO则显示） -->
            	<?php if($qualify_type == 1 && $admin_id != Yii::app()->session['user_id'] && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();">&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" readonly="readonly">&nbsp;元/月</br>
            	<?php endif; ?>

            	<!-- 部门负责人 -> verky（非CEO则显示） -->
            	<?php if($qualify_type == 2 && $ceo_id != Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();">&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" readonly="readonly">&nbsp;元/月</br>
            	<?php endif; ?>

            	<!-- verky -> sara（是CEO则显示） -->
            	<?php if($qualify_type == 3 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();">&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" readonly="readonly">&nbsp;元/月</br>
            	<?php endif; ?>

            	<!-- verky （是CEO则显示） -->
            	<?php if($qualify_type == 4 && $ceo_id == Yii::app()->session['user_id']): ?>
            	<input type="radio" name="salary" value="contract" onchange="changeSalary();">&nbsp;入职约定薪资&nbsp;&nbsp;
            	<input class="form-control inline w100" id="unchange-salary" readonly="readonly">&nbsp;元/月</br>
            	<?php endif; ?>
            	<div class="inline-block mr20 mt10 <?php if(!empty($qualify_salary)) echo 'hidden';?>">
            		<input type="radio" name="salary"  value="modify" onchange="changeSalary();" checked>&nbsp;薪资调整&nbsp;&nbsp;&nbsp;&nbsp;
            	</div>
            	<input class="form-control inline w100" id="change-salary" value="<?php if(!empty($qualify_salary)) echo $qualify_salary; ?>">&nbsp;元/月&nbsp;&nbsp;
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
	// 页面初始化
	$(document).ready(function(){
		// 日期控件初始化
		$('#entry-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
		$('#probation-end-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    	$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

/*------------------------------------------------转正评价------------------------------------------------*/

	// 转正评价-显示
	function showEvaluate(){
		var ySet = (window.innerHeight - $("#evaluate-div").height())/2;
	    var xSet = (window.innerWidth - $("#evaluate-div").width())/2;
	    $("#evaluate-div").css("top",ySet);
	    $("#evaluate-div").css("left",xSet);
		$("#evaluate-div").modal({show:true});
	}

	

	// 同意转正选项-显示
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
			$("#change-salary").val("");
			$("#change-salary").attr("readonly", "readonly");
		}else{
			$("#change-salary").removeAttr("readonly");
			$("#unchange-salary").val("");
			$("#unchange-salary").attr("readonly", "readonly");
		}
	}

	// 发送评价
	function sendEvaluate(){
		// 获取数据
		var id = $("#apply-id").text();
		var comment = $("#opinion").val();
		var decision = $("input[name='decision']:checked").val();

		// 判断是否同意
		if(decision == "agree"){
			// 获取数据
			var entry_date = $("#entry-date").val();
			var type = $("input[name='salary']:checked").val();
			var qualify_salary = "";
			if(type == "contract"){
				qualify_salary = $("#unchange-salary").val();
			}else{
				qualify_salary = $("#change-salary").val();
			}

			// 验证数据
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
	                    }else{
	                        showHint("提示信息","你没有权限执行此操作");
	                    }
	                }
	            });
			}
		}else if(decision == "delay"){
			// 获取数据
			var qualify_date = $("#probation-end-date").val();

			// 验证数据
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

/*------------------------------------------------转正员工发送的操作------------------------------------------------*/

	// 发送转正
	function sendpositiveApply(){
		// 获取数据
		var id = $("#positive-apply-id").text();
		var opinion = $("#opinion").val();
		var personal_plan = $("#personal-plan").val();
		var self_assessment = $("#self-assessment").val();

		// 验证数据
		var d_pattern = /^\d+$/;
		var work_content = new Array();  // 工作内容数组
		var content_flag = 0; // 非空标记
		var f_flag = 0;  // 错误标记
		var serial = 1;
		$("#positive-table").find("tr.work-content-tr").each(function(){
			// 获取数据
			var content = $(this).find(".content-input").val();
			var percent = $(this).find(".percent-input").val();
			var standard = $(this).find(".standard-input").val();
			var workload = $(this).find(".workload-input").val();
			var complete = $(this).find(".complete-input").val();
			var delay = $(this).find(".delay-input").val();
			var redo = $(this).find(".redo-input").val();

			var row_content_flag = 0;  // 行数据非空标记

			// 判断是否全部为空
			if(content != ""||percent !=""||standard != ""||workload != ""||complete != ""||delay != ""||redo != ""){
				content_flag = 1;
				row_content_flag = 1;

				// 工作内容的验证
				if(content == ""){
					showHint("提示信息","请输入工作内容");
					$(this).find(".content-input").focus();
					f_flag = 1;
					return false;
				}

				// 占比的验证
				if(percent == ""){
					showHint("提示信息","请输入工作占比");
					$(this).find(".percent-input").focus();
					f_flag = 1;
					return false;
				}else{
					// 验证占比格式
					if(!d_pattern.exec(percent)){
						alert(percent);
						showHint("提示信息","占比格式输入错误");
						$(this).find(".percent-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(percent) >100 || parseInt(percent) <= 0){
						showHint("提示信息","占比为1到100之间的数值");
						$(this).find(".percent-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 工作量的验证
				// if(workload == ""){
				// 	showHint("提示信息","请输入工作量");
				// 	$(this).find(".workload-input").focus();
				// 	f_flag = 1;
				// 	return false;
				// }

				// 完成率的验证
				if(complete == ""){
					showHint("提示信息","请输入工作完成率");
					$(this).find(".complete-input").focus();
					f_flag = 1;
					return false;
				}else{
					// 验证完成率格式
					if(!d_pattern.exec(complete)){
						showHint("提示信息","完成率格式输入错误");
						$(this).find(".complete-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(complete) >100 || parseInt(complete) < 0){
						showHint("提示信息","完成率为1到100之间的数值");
						$(this).find(".complete-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 延误率的验证
				// if(delay == ""){
				// 	showHint("提示信息","请输入工作延误率");
				// 	$(this).find(".delay-input").focus();
				// 	f_flag = 1;
				// 	return false;
				// }else{
				// 	// 验证延误率格式
				// 	if(!d_pattern.exec(delay)){
				// 		showHint("提示信息","延误率格式输入错误");
				// 		$(this).find(".delay-input").focus();
				// 		f_flag = 1;
				// 		return false;
				// 	}else if(parseInt(delay) >100 || parseInt(delay) < 0){
				// 		showHint("提示信息","延误率为1到100之间的数值");
				// 		$(this).find(".delay-input").focus();
				// 		f_flag = 1;
				// 		return false;
				// 	}
				// }

				// 返工率的验证
				if(redo == ""){
					showHint("提示信息","请输入工作返工率");
					$(this).find(".redo-input").focus();
					f_flag = 1;
					return false;
				}else{
					// 验证返工率格式
					if(!d_pattern.exec(redo)){
						showHint("提示信息","返工率格式输入错误");
						$(this).find(".redo-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(redo) >100 || parseInt(redo) < 0){
						showHint("提示信息","返工率为1到100之间的数值");
						$(this).find(".redo-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 完成率+延误率+返工率是否等于100%的验证
                // if(parseInt(complete)+parseInt(delay)+parseInt(redo) != 100){
				if(parseInt(complete)+parseInt(redo) != 100){
					showHint("提示信息","完成率+返工率必须等于100%");
					f_flag = 1;
					return false;
				}
			}

			// 如果这一行有内容 并且 验证通过，则加入到数组中
			if(row_content_flag == 1 && f_flag == 0){
				work_content.push({"serial":serial,"content":content, "proportion":percent, "reference":standard, "quantity":workload, "completion_rate":complete, "delay_rate":delay, "rework_rate":redo});
				serial++;
			}
		});

		// 如果没有输入任何工作内容
		if(content_flag == 0){
			showHint("提示信息","请输入工作内容");
		}else if(f_flag == 0){
			// 计算占比总和
			var total_percent = 0;
			$.each(work_content, function(){
				total_percent += parseInt(this.proportion);
			});

			// 验证数据
			if(total_percent != 100){
				showHint("提示信息","占比总和必须等于100%");
				return false;
			}else if(self_assessment == ""){
				showHint("提示信息","请输入员工自评");
				$("#self-assessment").focus();
				return false;
			}else if(personal_plan == ""){
				showHint("提示信息","请输入个人规划");
				$("#personal-plan").focus();
				return false;
			}else if(opinion == ""){
				showHint("提示信息","请输入意见与建议");
				$("#opinion").focus();
				return false;
			}else{
				$.ajax({
	                type:'post',
	                url:'/ajax/userQualify',
	                dataType:'json',
	                data:{'id':id, 'contents':work_content, 'evaluation':self_assessment, 'plan':personal_plan, 'suggest':opinion},
	                success:function(result){
	                    if(result.code == 0){
	                        showHint("提示信息","转正申请提交成功");
	                        setTimeout(function(){location.reload();},1200);  
	                    }else if(result.code == '-1'){
	                        showHint("提示信息","转正申请提交失败");
	                    }else if(result.code == '-2'){
	                        showHint("提示信息","参数错误");
	                    }else if(result.code == '-3'){
	                        showHint("提示信息","查找不到此申请");
	                    }else if(result.code == '-4'){
	                        showHint("提示信息","你已经转正了，不需要重复提交");
	                    }else{
	                        showHint("提示信息","你没有权限执行此操作");
	                    }
	                }
	            });
			}
		}
	}

	// 新增一行
    var row_num = 3;
	function newLine(obj){
		var str = "<tr class='work-content-tr'><td>"+row_num+"</td>"+
			"<td><input class='form-control content-input' placeholder='请输入工作内容'></td>"+
			"<td><input class='form-control inline w50 percent-input'>&nbsp;%</td>"+
			"<td><input class='form-control standard-input hidden' placeholder='请输入参考标准'></td>"+
			"<td><input class='form-control workload-input hidden' placeholder='请输入工作量'></td>"+
			"<td><input class='form-control inline w50 complete-input' >&nbsp;%</td>"+
			"<td><input class='form-control inline w50 delay-input hidden' ></td>"+
			"<td><input class='form-control inline w50 redo-input' >&nbsp;%</td></tr>";
		$(obj).parent().parent().before(str);
		row_num++;
	}
</script>
