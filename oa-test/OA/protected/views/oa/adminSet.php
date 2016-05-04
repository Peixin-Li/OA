<?php
echo "<script type='text/javascript'>";
echo "console.log('adminSet');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">操作人员设置</h4>
    <!-- 人员设置 -->
	<?php if(!empty($operators)): ?>
	<div class="pd20 bor-b-1-ddd">
		<table class="table table-unbordered m0">
			<tbody>
				<?php foreach($operators as $row):?>
				<?php if($row['type'] != "feedback" && $row['type'] != "admin_department" && $row['type'] != "operation_department"):?>
				<tr>
					<th class="right w130"><?php echo $row['comment']; ?>：</th>
					<td class="w80">
						<span><?php echo $row->user->cn_name; ?></span>
						<input class="hidden" id="input_<?php echo $row['type'];?>">
					</td>
					<td>
						<a class="pointer" onclick="edit('<?php echo $row['type'];?>', this);">修改</a>
						<a class="pointer hidden" onclick="save('<?php echo $row['type'];?>', '<?php echo $row['id']; ?>', this);">保存</a>
						<a class="pointer hidden" onclick="cancel('<?php echo $row['type'];?>', this);">取消</a>
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
    <!-- 意见反馈人员的设置 -->
	<div class="pd20 bor-b-1-ddd">
		<table class="table table-unbordered m0">
			<tbody>
				<tr>
					<th class="w130 right">意见反馈接收：</th>
					<td id="feedback-td">
						<?php foreach($operators as $row): ?>
						<?php if($row['type'] == "feedback"): ?>
						<button class="btn btn-success pd3 w100" name="<?php echo $row['id']; ?>"><?php echo $row->user->cn_name; ?>&nbsp;<span class="glyphicon glyphicon-remove middle mt-2" onclick="deleteFeedBack(this);"></span></button>
						<?php endif; ?>
						<?php endforeach; ?>
						<a class="pointer ml20" onclick="addFeedBack();">增加</a>
					</td>
				</tr>
				<tr class="hidden" id="new_feedback_tr">
					<th class="w130 right">新增接收人：</th>
					<td>
						<input class="" id="new_feedback_input">
						<a class="pointer ml5" onclick="sendFeedBack();">提交</a>
						<a class="pointer ml5" onclick="cancelFeedBack();">取消</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<!-- 部门的设置 -->	
	<div class="pd20">
		<table class="table table-unbordered m0">
			<tbody>
				<?php foreach($operators as $row): ?>
				<?php if($row['type'] == "admin_department" || $row['type'] == "operation_department"): ?>
				<tr>
					<th class="w130 right"><?php echo $row['comment'];?>：</th>
					<td class="w150">
						<span><?php echo $row->department->name; ?></span>
						<select class="w200 hidden form-control inline" id="select_<?php echo $row['type'];?>">
							<?php if(!empty($departments)): ?>
							<?php foreach($departments as $rown): ?>
							<option value="<?php echo $rown['department_id']; ?>"><?php echo $rown['name'];?></option>
							<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</td>
					<td>
						<a class="pointer" onclick="edit('<?php echo $row['type'];?>', this);">修改</a>
						<a class="pointer hidden" onclick="save('<?php echo $row['type'];?>', '<?php echo $row['id']; ?>', this);">保存</a>
						<a class="pointer hidden" onclick="cancel('<?php echo $row['type'];?>', this);">取消</a>
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php else: ?>
	<h4 class="bor-b-1-ddd pd20">查找不到操作人员的信息</h4>
	<?php endif; ?>
</div>


<!--js-->
<script type="text/javascript">
	// 修改按钮
	function edit(title, obj){
		if(title == "admin_department" || title == "operation_department"){
			$("#select_"+title).removeClass("hidden");
			$("#select_"+title).prev().addClass("hidden");
		}else{
			$("#input_"+title).removeClass("hidden");
			$("#input_"+title).prev().addClass("hidden");
		}
		
		$(obj).addClass("hidden");
		$(obj).next().removeClass("hidden");
		$(obj).next().next().removeClass("hidden");
	}

	// 保存按钮
	function save(title, id,  obj){
		var f_tag = 0; // 错误标记
		if(title == "admin_department" || title == "operation_department"){
			var object_id = $("#select_"+title).val();
		}else{
			if($("#input_"+title).val() != ""){
				var object_id = "";

				// 判断是否存在该员工
				var find_tag = 0;
				var name = $("#input_"+title).val();
				$.each(users, function(){
					if(this['name'] == name){
						object_id = this['id'];
						find_tag = 1;
						return false;
					}
				});
				if(find_tag == 0){
					showHint("提示信息","不存在此用户");
					$("#input_"+title).focus();
					f_tag = 1;
				}
			}else{
				showHint("提示信息","请输入名称");
				$("#input_"+title).focus();
				f_tag = 1;
			}
		}

		// 判断是否有错
		if(f_tag == 0){
			$.ajax({
		      type:'post',
		      dataType:'json',
		      url:'/ajax/editOperate',
		      data:{'id':id,'object_id':object_id},
		      success:function(result){
		        if(result.code == 0){
		        	showHint("提示信息","修改操作人员成功");

		        	// 判断修改的是否为IT运维部和人事行政部
		        	if(title == "admin_department" || title == "operation_department"){
		        		var name = "";
		        		$("#select_"+title).children().each(function(){
		        			if($(this).val() == object_id) name = $(this).text();
		        		});
			        	$("#select_"+title).prev().text(name);
			        	$("#select_"+title).addClass("hidden");
			        	$("#select_"+title).prev().removeClass("hidden");
		        	}else{
		        		var name = $("#input_"+title).val();
		        		$("#input_"+title).val("");
			        	$("#input_"+title).prev().text(name);
			        	$("#input_"+title).addClass("hidden");
			        	$("#input_"+title).prev().removeClass("hidden");
		        	}
		        	$(obj).addClass("hidden");
					$(obj).prev().removeClass("hidden");
					$(obj).next().addClass("hidden");
		        }else if(result.code == -1){
		          	showHint("提示信息","修改操作人员失败");
		        }else if(result.code == -2){
		          	showHint("提示信息","参数错误");
		        }else if(result.code == -3){
		          	showHint("提示信息","找不到该用户");
		        }else{
		          	showHint("提示信息","你没有权限执行此操作");
		        }
		      }
		    });
		}
		
	}

	// 取消按钮
	function cancel(title, obj){
		if(title == "admin_department" || title == "operation_department"){
			$("#select_"+title).addClass("hidden");
			$("#select_"+title).prev().removeClass("hidden");
		}else{
			$("#input_"+title).addClass("hidden");
			$("#input_"+title).prev().removeClass("hidden");
		}
		
		$(obj).addClass("hidden");
		$(obj).prev().addClass("hidden");
		$(obj).prev().prev().removeClass("hidden");
	}

	// 用户数组初始化
	var users = new Array();
	var cn_name = new Array();
	<?php 
		if(!empty($users)){
			foreach($users as $row){
				echo "users.push({'id':'{$row->user_id}', 'name':'{$row->cn_name}'});";
				echo "cn_name.push('{$row->cn_name}');";
			}
		}
	?>

	// 页面初始化
	$(document).ready(function(){
		// 自动补全
		$("#input_ceo").autocomplete({
			source:cn_name
		});
		$("#input_admin").autocomplete({
			source:cn_name
		});
		$("#input_hr").autocomplete({
			source:cn_name
		});
		$("#input_commissioner").autocomplete({
			source:cn_name
		});
		$("#input_webadmin").autocomplete({
			source:cn_name
		});
		$("#new_feedback_input").autocomplete({
			source:cn_name
		});

		// 初始化下拉框
		$("#select_admin_department").children().each(function(){
			if($(this).text() == $("#select_admin_department").prev().text()) $("#select_admin_department").val($(this).val());
		});
		$("#select_operation_department").children().each(function(){
			if($(this).text() == $("#select_operation_department").prev().text()) $("#select_operation_department").val($(this).val());
		});
	});

	// 添加新的意见反馈接收人员
	function addFeedBack(){
		$("#new_feedback_tr").removeClass("hidden");
		$("#new_feedback_input").val("");
		$("#new_feedback_input").focus();
	}

	// 删除意见反馈接收人员
	function deleteFeedBack(obj){
		var id = $(obj).parent().attr("name");
		$.ajax({
	      type:'post',
	      dataType:'json',
	      url:'/ajax/deleteOperate',
	      data:{'id':id},
	      success:function(result){
	        if(result.code == 0){
	        	showHint("提示信息","删除接收人员成功");
	        	setTimeout(function(){location.reload();}, 1200);
	        }else if(result.code == -1){
	          	showHint("提示信息","删除接收人员失败");
	        }else if(result.code == -2){
	          	showHint("提示信息","参数错误");
	        }else if(result.code == -3){
	          	showHint("提示信息","找不到该用户");
	        }else{
	          	showHint("提示信息","你没有权限执行此操作");
	        }
	      }
	    });
	}

	// 取消增加反馈接收人员
	function cancelFeedBack(){
		$("#new_feedback_tr").addClass("hidden");
	}

	// 发送新增的反馈接收人员
	function sendFeedBack(){
		var id = "";

		// 判断是否存在该员工
		var name = $("#new_feedback_input").val();
		var find_tag = 0;
		$.each(users, function(){
			if(this['name'] == name){
				id = this['id'];
				find_tag = 1;
			}
		});

		var a_tag = 0; // 是否已存在的标记
		$("#feedback-td").find(".btn-success").each(function(){
			if($(this).text().indexOf(name) > -1){
				a_tag = 1;
			}
		});
		if(find_tag == 0){
			showHint("提示信息","不存在此用户");
			$("#new_feedback_input").focus();
		}else if(find_tag == 1 && a_tag == 1){
			showHint("提示信息","已存在的接收人");
			$("#new_feedback_input").focus();
		}else{
			$.ajax({
		      type:'post',
		      dataType:'json',
		      url:'/ajax/createOperate',
		      data:{'comment':'意见反馈接收人员', 'type':'feedback', 'object_id':id},
		      success:function(result){
		        if(result.code == 0){
		        	showHint("提示信息","添加接收人员成功");
		        	setTimeout(function(){location.reload();}, 1200);
		        }else if(result.code == -1){
		          	showHint("提示信息","添加接收人员失败");
		        }else if(result.code == -2){
		          	showHint("提示信息","参数错误");
		        }else if(result.code == -3){
		          	showHint("提示信息","找不到该用户");
		        }else{
		          	showHint("提示信息","你没有权限执行此操作");
		        }
		      }
		    });
		}
	}
</script>