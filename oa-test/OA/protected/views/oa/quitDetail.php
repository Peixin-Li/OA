<?php
echo "<script type='text/javascript'>";
echo "console.log('quitDetail');";
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
	<h4 class="pd10 m0 b33 bor-1-ddd">离职申请详情</h4>
	<!-- 离职申请进度 -->
	<div class="bor-l-1-ddd bor-r-1-ddd">
		<ul class="nav nav-justified">
	        <li class="bg-66 flow-li">
	            <h4 class="white m0 mt5 center">1.提交离职申请</h4>
	            <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
	        </li>

	        <?php 
	        	if(!empty($procedure)){
	        		$no_num = 2;
	        		$count = count($procedure);
	        		$last_status = "";
	        		$first_tag = 0;
	        		foreach($procedure as $row){
	        			if($first_tag == 0){
	        				if($row[1] == "agree"){
	        					echo "<li class='bg-66 flow-li'><h4 class='white m0 mt5 center'>{$no_num}.填写离职原因</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
	        				}else if($row[1] == "wait"){
	        					echo "<li><h4 class='m0 mt5 center'>{$no_num}.填写离职原因</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
	        				}
	        				$first_tag = 1;
	        			}
	        			else{
	        				if($row[1] == "agree"){
								echo "<li class='bg-66 flow-li'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
		        			}else if($row[1] == "reject"){
		        				echo "<li class='flow-li-red bg-99'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
		        			}else{
		        				echo "<li><h4 class='m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
		        			}
	        			}
	        			$last_status = $row[1];
		        		$no_num++;
	        		}
	        		if($last_status == "agree"){
	        			echo "<li class='bg-66'><h4 class='white m0 mt5 center'>{$no_num}.离职申请结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
	        		}else if($last_status == "reject"){
	        			echo "<li class='bg-99'><h4 class='white m0 mt5 center'>{$no_num}.离职申请结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
	        		}else{
	        			echo "<li><h4 class='m0 mt5 center'>{$no_num}.离职申请结果</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
	        		}
	        	}
	        ?>
	    </ul>
	</div>
	<!-- 离职申请详情 -->
	<table class="table table-bordered">
		<tbody>
			<tr>
				<th class="w130 center">离职人姓名</th>
                <td class="hidden" id="apply-id"><?php echo $apply->id; ?></td>
                <td><?php echo $apply->user->cn_name;?></td>
			</tr>
			<tr>
				<th class="w130 center">所属部门</th>
                <td><?php echo $apply->user->department->name;?></td>
			</tr>
			<tr>
				<th class="w130 center">职位</th>
                <td><?php echo $apply->user->title;?></td>
			</tr>
			<tr>
				<th class="w130 center">入职日期</th>
                <td><?php echo $apply->user->entry_day;?></td>
			</tr>
            <?php if(!empty($apply->quit_reason)): ?>
			<tr>
				<th class="w130 center">离职原因</th>
                <td><?php echo $apply->quit_reason;?></td>
			</tr>
            <?php endif;?>
            <?php if($logs = $apply->allLogs): ?>
            <?php foreach($logs as $log): ?>
                  <?php if($log->action == 'create') { continue; } ?>
			<tr>
            <th class="w130 center"><?php echo $log->user->department->name; ?>审批</th>
				<td>
					<div class="fl">
	                    <div style="display:table-cell;" class="middle h80">
                            <?php if($log->action == 'agree'): ?> 
	                        <h5 class="w200 f15px">同意</h5>
                            <h5 class="w400 f15px">建议于<?php echo $log->quit_date; ?>离职</h5>
                            <?php $quit_date = $log->quit_date; ?>
                            <?php else: ?>
	                        <h5 class="w200 f15px">不同意</h5>
	                        <h5 class="w200 f15px">不同意原因：</h5>
                            <div class="xw600" style="word-break:break-all;"><?php echo $apply->reason; ?></div>
                            <?php endif; ?>
	                    </div>
	                </div>
	                <div class="fr">
	                    <div style="display:table-cell;" class="middle h80">
	                        <h5 class="w200 center">签名：<?php echo $log->user->cn_name;?></span></h5>
	                        <h5 class="w200 center">审批日期：<?php echo date('Y-m-d', strtotime($log->create_time));?><span></span></h5>
	                    </div>
	                </div>
				</td>
			</tr>
            <?php endforeach; ?>
            <?php endif; ?>
			


			<!-- 离职的人要填的东西 -->
            <?php if(!empty($user) && empty($apply->quit_reason) && $apply->next == $user->user_id): ?>
			<tr>
				<th class="w130 center">离职原因</th>
				<td><textarea class="form-control" id="quit-reason"></textarea></td>
			</tr>
			<tr>
				<th class="w130 center">操作</th>
				<td><button class="btn btn-success w100" onclick="sendReason();">提交</button></td>
			</tr>
            <?php endif; ?>
            <?php if(!empty($user) && $user->user_id == $apply->next && $apply->user_id != $user->user_id): ?>
			<!-- 审批的人要填的东西 -->
			<tr>
				<th class="w130 center"><?php echo empty($this->user) ? '部门负责人':$this->user->department->name;?>审批</th>
				<td>
					<div class="pd5">
						<input type="radio" name="decision" checked onchange="changeDecision();" value="agree">&nbsp;同意&nbsp;&nbsp;&nbsp;
						<input type="radio" name="decision" onchange="changeDecision();" value="reject">&nbsp;不同意
					</div>
					<div class="pd5" id="quit-date-div">
						建议于&nbsp;<input class="form-control w130 inline pointer center" id="quit-date" value="<?php if(!empty($quit_date)){echo $quit_date;}else{echo date('Y-m-d', strtotime("+1months"));}?>">&nbsp;离职
					</div>
					<div class="pd5 hidden" id="quit-reject-reason">
						<label>不同意原因:</label>
						<input class="form-control w800 inline" placeholder="请输入不同意原因" id="reject-reason">
					</div>
				</td>
			</tr>
			<tr>
				<th class="w130 center">操作</th>
				<td><button class="btn btn-success w100" onclick="sendProcess();">提交</button></td>
			</tr>
            <?php endif; ?>
            <?php if(!empty($apply->status) && $apply->status == "success" && $apply->handover_status=='create' && $apply->user_id == Yii::app()->session['user_id']):?>
            <tr>
            	<th class="w130 center">工作交接</th>
            	<td><button class="btn btn-success w130" onclick="$('#work-transform-div').modal({show:true});">发起工作交接</button></td>
            </tr>
        	<?php elseif(!empty($apply->status) && $apply->status == "success" && $apply->handover_status!='create'):?>
            <tr>
            	<th class="w130 center">工作交接</th>
            	<td><button class="btn btn-success w130" onclick="location.href='/oa/deliverWorkDetail/id/<?php echo $apply->id;?>';">查看工作交接表</button></td>
            </tr>
        	<?php endif; ?>
		</tbody>
	</table>
</div>

<!-- 发起工作交接 -->
<div id="work-transform-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">发起工作交接</h4>
    </div>

    <div class="modal-body">
    	<div class="m0a w300">
			<label>请选择工作接收人：</label>
	        <input class="form-control w150 inline center" id="transform-name">
    	</div>
        
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="addHandOverWork();">确认</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
	// 用户数组初始化
	var users = new Array();
	var cn_name = new Array();
	<?php 
		if(!empty($users)){
			foreach($users as $row){
				echo "users.push({'cn_name':'{$row['cn_name']}','user_id':'{$row['user_id']}'});";
				echo "cn_name.push('{$row['cn_name']}');";
			}
		}
	?>

	// 页面初始化
	$(document).ready(function(){
		// 自动补全
		$("#transform-name").autocomplete({
			source:cn_name
		});

		// 日期选择控件初始化
		$('#quit-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
   		$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

	// 指定接收人
	function addHandOverWork(){
		var id = $("#apply-id").text();
		var name = $("#transform-name").val();
		var handover_user_id = "";
		var f_tag = 0;
		$.each(users, function(){
			if(this['cn_name'] == name){
				handover_user_id = this['user_id'];
				f_tag = 1;
			}
		});
		if(f_tag == 0){
			showHint("提示信息","不存在的接收人");
			$("#transform-name").focus();
		}else{
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/submitHandover',
		        data:{'id':id, 'handover_user_id':handover_user_id},
		        success:function(result){
		          if(result.code == 0){
		          	showHint("提示信息","发起工作交接成功");
		          	var href_str = "/oa/deliverWorkDetail/id/"+id;
		            setTimeout(function(){location.href=href_str;},1200);
		          }else if(result.code == -1){
		            showHint("提示信息","发起工作交接失败！");
		          }else if(result.code == -2){
		            showHint("提示信息","参数错误！");
		          }else if(result.code == -3){
		            showHint("提示信息","找不到该离职人！");
		          }else{
		          	showHint("提示信息","你没有权限执行此操作");
		          }
		        }
		     });
		}
	}	

	// 提交意见
	function sendReason(){
		var id = $("#apply-id").text();
		var reason = $("#quit-reason").val();
		if(reason == ""){
			showHint("提示信息","请输入离职原因");
		}else{
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/writeQuitReason',
		        data:{'id':id, 'quit_reason':reason},
		        success:function(result){
		          if(result.code == 0){
		          	showHint("提示信息","提交离职原因成功");
		            setTimeout(function(){location.reload();},1200);
		          }else if(result.code == -1){
		            showHint("提示信息","提交离职原因失败！");
		          }else if(result.code == -2){
		            showHint("提示信息","参数错误！");
		          }else if(result.code == -3){
		            showHint("提示信息","找不到该离职人！");
		          }else{
		          	showHint("提示信息","你没有权限执行此操作");
		          }
		        }
		     });
		}
	}

	// 改变决定
	function changeDecision(){
		if($("input[name='decision']:checked").val() == "agree"){
			$("#quit-date-div").removeClass("hidden");
			$("#quit-reject-reason").addClass("hidden");
		}else{
			$("#quit-date-div").addClass("hidden");
			$("#quit-reject-reason").removeClass("hidden");
		}
	}

	// 发送审批结果
	function sendProcess(){
		var id = $("#apply-id").text();
		if($("input[name='decision']:checked").val() == "agree"){
			var quit_date = $("#quit-date").val();
			var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
			if(quit_date == ""){
				showHint("提示信息","请输入建议离职日期");
				$("#quit-date").focus();
			}else if(!date_pattern.exec(quit_date)){
				showHint("提示信息","建议离职日期输入错误");
				$("#quit-date").focus();
			}else{
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/agreeQuitApply',
			        data:{'id':id, 'quit_date':quit_date},
			        success:function(result){
			          if(result.code == 0){
			          	showHint("提示信息","同意离职成功");
			            setTimeout(function(){location.reload();},1200);
			          }else if(result.code == -1){
			            showHint("提示信息","同意离职失败！");
			          }else if(result.code == -2){
			            showHint("提示信息","参数错误！");
			          }else if(result.code == -3){
			            showHint("提示信息","找不到该离职人！");
			          }else{
			          	showHint("提示信息","你没有权限执行此操作");
			          }
			        }
			    });
			}
		}else{
			var reason = $("#reject-reason").val();
			if(reason == ""){
				showHint("提示信息","请输入不同意原因");
				$("#reject-reason").focus();
			}else{
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/rejectQuitApply',
			        data:{'id':id, 'reason':reason},
			        success:function(result){
			          if(result.code == 0){
			          	showHint("提示信息","退回离职申请成功");
			            setTimeout(function(){location.reload();},1200);
			          }else if(result.code == -1){
			            showHint("提示信息","退回离职申请失败！");
			          }else if(result.code == -2){
			            showHint("提示信息","参数错误！");
			          }else if(result.code == -3){
			            showHint("提示信息","找不到该离职人！");
			          }else{
			          	showHint("提示信息","你没有权限执行此操作");
			          }
			        }
			    });
			}
			
		}
	}
</script>
	

