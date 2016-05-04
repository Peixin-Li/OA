<?php
echo "<script type='text/javascript'>";
echo "console.log('editorRolesSet');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">文档库权限设置</h4>
    <!-- 人员设置 -->
	<div class="pd20 bor-b-1-ddd">
		<table class="table table-unbordered m0">
			<tbody>
				<tr>
					<th class="right w130">管理员：</th>
					<td class="w80">
						<span id="editor-admin"></span>
						<input class="hidden" id="input_admin">
					</td>
					<td>
						<a class="pointer" onclick="edit('admin',this)">修改</a>
						<a class="pointer hidden" onclick="save('admin')">保存</a>
						<a class="pointer hidden" onclick="cancel('admin',this);">取消</a>
					</td>
				</tr>

				<tr>
					<th class="right w130">文档审核者：</th>
					<td class="w80">
						<span id="editor-approver"></span>
						<input class="hidden" id="input_approver">
					</td>
					<td>
						<a class="pointer" onclick="edit('approver',this)">修改</a>
						<a class="pointer hidden" onclick="save('approver')">保存</a>
						<a class="pointer hidden" onclick="cancel('approver',this);">取消</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
    <!-- 文档编辑者设置 -->
	<div class="pd20 bor-b-1-ddd">
		<table class="table table-unbordered m0">
			<tbody>
				<tr>
					<th class="w130 right">文档编辑者：</th>
					<td id="editor-td">
						<a class="pointer ml20" onclick="addNewEditorRole();">增加</a>
					</td>
				</tr>
				<tr class="hidden" id="new_editor_role_tr">
					<th class="w130 right">新增接收人：</th>
					<td>
						<input class="" id="new_editor_role_input">
						<a class="pointer ml5" onclick="sendEditorRole();">提交</a>
						<a class="pointer ml5" onclick="cancelEditorBack();">取消</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<h4 class="bor-b-1-ddd pd20 hidden">查找不到操作人员的信息</h4>
</div>

<!--js-->
<script type="text/javascript">
	var editor_roles = <?php echo $editor_roles_js; ?>;
	var users = <?php echo $users_js; ?>;
	var cn_name = new Array();

	$.each(users, function(key, value) {
		cn_name.push(value['cn_name']);
	});

		// 页面初始化
	$(document).ready(function(){
		// 自动补全
		$("#input_admin").autocomplete({
			source:cn_name
		});
		$("#input_approver").autocomplete({
			source:cn_name
		});
		$("#new_editor_role_input").autocomplete({
			source:cn_name
		});
		$.each(editor_roles, function() {            //初始化操作人员
			var username = findNameById(this['user_id']);
			if (this['type'] == 'admin') {
				$("#editor-admin").text(username);
			} else if(this['type'] == 'approver') {
				$("#editor-approver").text(username);
			}else if(this['type'] == 'editor') {
				var html_content = '<button class="btn btn-success pd3 w100 mr5" name="' + this['id'] + '">' + username +
						'&nbsp;<span class="glyphicon glyphicon-remove middle mt-2" onclick="deleteFeedBack(this);"></span></button>';
				$("#editor-td").prepend(html_content);
			}
		});
	});

	function findNameById(id) {
		var name = "";
		$.each(users, function(key, value) {
			if (value['user_id'] == id) {
				name = value['cn_name'];
				return false;
			}
		});
		return name;
	}

	// 修改按钮
	function edit(title, obj){
		$("#input_"+title).removeClass("hidden");
		$("#input_"+title).prev().addClass("hidden");
		$(obj).addClass("hidden");
		$(obj).next().removeClass("hidden");
		$(obj).next().next().removeClass("hidden");
	}

	// 保存按钮
	function save(title){
		var f_tag = 0; // 错误标记
		var user_id;
		
		if($("#input_"+title).val() != ""){
			// 判断是否存在该员工
			var find_tag = 0;
			var name = $("#input_"+title).val();
			$.each(users, function(){
				if(this['cn_name'] == name){
					user_id = this['user_id'];
					find_tag = 1;
					return false;
				}
			});
			if(find_tag == 0){
				showHint("提示信息","不存在此用户");
				$("#input_"+title).focus();
				f_tag = 1;
			}
		}
		else{
			showHint("提示信息","请输入名称");
			$("#input_"+title).focus();
			f_tag = 1;
		}
		// 判断是否有错
		if(f_tag == 0){
			$.ajax({
		      type:'post',
		      dataType:'json',
		      url:'/ajax/changeEditorRoles',
		      data:{'user_id':user_id,'role':title},
		      success:function(result){
		      	if (result.code==0) {
		      		showHint('提示消息','修改成功');
		      		setTimeout(function () {
		      			location.reload();
		      		}, 2000);
		      	} else if(result.code==-1) {
		      		showHint('提示信息', '修改失败');
		      	} else if(result.code==-2) {
		      		showHint('提示信息', '参数错误');
		      	}
		      	else if(result.code==-3)
		      		showHint('提示信息', '无权限进行此操作');
		      },
		      error:function(arg1, arg2, arg3){
		      	showHint("提示信息", arg3);
		      }
		    });
		}
	}

	// 取消按钮
	function cancel(title, obj){
		$("#input_"+title).addClass("hidden");
		$("#input_"+title).prev().removeClass("hidden");
		$(obj).addClass("hidden");
		$(obj).prev().addClass("hidden");
		$(obj).prev().prev().removeClass("hidden");
	}

	// 添加新的意见反馈接收人员
	function addNewEditorRole(){
		$("#new_editor_role_tr").removeClass("hidden");
		$("#new_editor_role_input").val("");
		$("#new_editor_role_input").focus();
	}

	// 删除意见反馈接收人员
	function deleteFeedBack(obj){
		var id = $(obj).parent().attr("name");
		$.ajax({
	      type:'post',
	      dataType:'json',
	      url:'/ajax/deleteRolesEditor',
	      data:{'id':id},
	      success:function(result){
	        if(result.code == 0){
	        	showHint("提示信息","删除成功");
	        	setTimeout(function(){location.reload();}, 1200);
	        }else if(result.code == -1){
	          	showHint("提示信息","删除失败");
	        }else if(result.code == -2){
	          	showHint("提示信息","参数错误");
	        }else if(result.code == -3){
	          	showHint("提示信息","你没有权限执行此操作");
	        }else{
	          	showHint("提示信息","操作失败");
	        }
	      }
	    });
	}

	// 取消增加反馈接收人员
	function cancelEditorBack(){
		$("#new_editor_role_tr").addClass("hidden");
	}

	// 发送新增的反馈接收人员
	function sendEditorRole(){
		// 判断是否存在该员工
		var name = $("#new_editor_role_input").val();
		var id = 0;
		$.each(users, function() {
			if (this['cn_name']==name) {
				id = this['user_id'];
				return false;
			}
		});
		if(id == 0){
			showHint("提示信息","不存在此用户");
			$("#new_editor_role_input").focus();
		}else{
			$.ajax({
		      type:'post',
		      dataType:'json',
		      url:'/ajax/addRolesEditor',
		      data:{'user_id': id},
		      success:function(result){
		        if(result.code == 0){
		        	showHint("提示信息","添加接收人员成功");
		        	setTimeout(function(){location.reload();}, 1200);
		        }else if(result.code == -1){
		          	showHint("提示信息","添加接收人员失败");
		        }else if(result.code == -5){
		          	showHint("提示信息","重复添加");
		        }
		        else if(result.code == -3){
		          	showHint("提示信息","无权限进行此操作");
		        }

		      },
		      error:function(arg1, arg2, arg3) {
		      		showHint("提示信息",arg3);
		      }
		    });
		}
	}

</script>