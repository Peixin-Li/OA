<?php
echo "<script type='text/javascript'>";
echo "console.log('rolesSet');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<div class="bor-1-ddd">
	<h4 class="pd10 m0 b33 bor-b-1-ddd">
		<strong>系统权限设置</strong>
        <button class="mt-5 btn btn-success fr pd3 mr10" onclick="newRoleuser();"><span class="glyphicon glyphicon-plus"></span>
        	&nbsp;添加用户角色</button>
	</h4><!-- 标题 -->

	<?php if(!empty($roles)): ?>
	<div class="pd20 bor-b-1-ddd">
		<table class="table table-unbordered m0">
			<tbody>
				<?php foreach($roles_user as $row_user):?>
					<tr>
					<th class="w130 right"><?php echo $row_user['cn_name']; ?>：</th>
					<td id="roles-td">
					<?php foreach($roles as $row_role):?>
						<?php if($row_role['user_id'] == $row_user['user_id']):?>
							<button class="btn btn-success pd3 w100" name="<?php echo $row_role['id']; ?>">
							<?php echo $roles_comment[$row_role->role_name]; ?>&nbsp;
								<span class="glyphicon glyphicon-remove middle mt-2" onclick="DeleteRoles(this);"></span>
							</button>
						<?php endif; ?>
					<?php endforeach; ?>
					</td>
                </tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div><!-- 人员设置 -->
	<?php else: ?>
	<h4 class="bor-b-1-ddd pd20">查找不到操作人员的信息</h4>
	<?php endif; ?>

	<div id="new-roles-user" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
	    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
	      <a class="close" data-dismiss="modal">×</a>
	      <h4 class="hint-title">添加用户权限</h4>
	    </div>
	    <div class="modal-body">
	      <table class="table table-unbordered center m0">
	        <tbody>
	          <tr>
	            <th class="w130">用户名称</th>
	            <td><input class="form-control" id="new_roles_name_input"></td>
	          </tr>
	          <tr>
	            <th class="w130">角色名称</th>
	            <td>
	              <select class="form-control" id="rolename">
	                <?php if(!empty($roles_comment)): ?>
	                <?php foreach($roles_comment as $key=>$value): ?>
	                	<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
	                <?php endforeach; ?>
	                <?php endif; ?>
	              </select>
	            </td>
	          </tr>
	        </tbody>
	      </table>
	    </div>

	    <div class="modal-footer" id="modal-footer">
	      <button class="btn btn-success w100" onclick="addRoles();">提交</button>
	    </div>
	</div>
</div>
<br>

<div class="bor-1-ddd">
	<h4 class="pd10 m0 b33 bor-b-1-ddd"><strong>系统权限说明</strong></h4>
	<div class="pd20 bor-b-1-ddd">
		<table class="table table-bordered" id="change-table">
			<tbody>
				<tr class="bg-fa">
					<th class="w80 center"></th>
					<th class="w80 center">超级管理员</th>
					<th class="w80 center">普通管理员</th>
					<th class="w80 center">人事</th>
					<th class="w80 center">财务</th>
				</tr>
				<tr class="bg-fa">
					<th class="w80 center">进入管理界面</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">Y</th>
				</tr>
				<tr class="bg-fa">
					<th class="w80 center">查看招聘/面试/转正信息</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">---</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">---</th>
				</tr>
				<tr class="bg-fa">
					<th class="w80 center">查看加班/费用/预算信息</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">---</th>
					<th class="w80 center">---</th>
					<th class="w80 center">Y</th>
				</tr>
				<tr class="bg-fa">
					<th class="w80 center">设置系统权限</th>
					<th class="w80 center">Y</th>
					<th class="w80 center">---</th>
					<th class="w80 center">---</th>
					<th class="w80 center">---</th>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
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
		$("#new_roles_name_input").autocomplete({
			source:cn_name
		});
	});

	// 删除意见反馈接收人员
	function DeleteRoles(obj){
		var id = $(obj).parent().attr("name");
		$.ajax({
	      type:'post',
	      dataType:'json',
	      url:'/ajax/deleteRoles',
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
	          	showHint("提示信息","找不到该用户");
	        }
	        else if(result.code == -4){
	          	showHint("提示信息","不能删除唯一的超级管理员");
	        }else{
	          	showHint("提示信息","你没有权限执行此操作");
	        }
	      }
	    });
	}

	// 新增用户权限弹出窗口
  function newRoleuser(){
    var ySet = (window.innerHeight - $("#new-roles-div").height())/3;
    var xSet = (window.innerWidth - $("#new-roles-user").width())/2;
    $("#new-roles-user").css("top",ySet);
    $("#new-roles-user").css("left",xSet);
    $("#new-roles-user").modal({show:true});
  }

	// 发送新增的反馈接收人员
	function addRoles(){
		var user_id = "";
		// 判断是否存在该员工
		var roles_name = $("#rolename").val();
		var name = $("#new_roles_name_input").val();
		var find_tag = 0;
		$.each(users, function(){
			if(this['name'] == name){
				user_id = this['id'];
				find_tag = 1;
			}
		});

		if(find_tag == 0){
			showHint("提示信息","不存在此用户");
			$("#new_roles_name_input").focus();
		}else{
			$.ajax({
		      type:'post',
		      dataType:'json',
		      url:'/ajax/createRole',
		      data:{'roles_name':roles_name, 'user_id':user_id},
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