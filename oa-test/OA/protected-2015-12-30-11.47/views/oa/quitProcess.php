<?php
echo "<script type='text/javascript'>";
echo "console.log('quitProcess');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33">发起离职申请</h4>
	<!-- 发起离职申请 -->
	<table class="table m0">
		<tbody>
			<tr>
				<th class="w130 center">离职人姓名</th>
				<td><input class="form-control w150" placeholder="请输入离职人姓名" id="quit-name" onfocus="var check_name_interval = setInterval('showDetail()',100);" onblur="check_name_interval = null;"></td>
			</tr>
			<tr>
				<th class="w130 center">所属部门</th>
				<td id="quit-department">空</td>
			</tr>
			<tr>
				<th class="w130 center">职位</th>
				<td id="quit-title">空</td>
			</tr>
			<tr>
				<th class="w130 center">入职日期</th>
				<td id="quit-entry-date">空</td>
			</tr>
			<tr>
				<th class="w130 center">操作</th>
				<td><button class="btn btn-success w100" onclick="confirmQuit();">提交</button></td>
			</tr>
		</tbody>
	</table>
</div>

<!-- js -->
<script type="text/javascript">
	// 用户数组初始化
	var user_arr = new Array();
	var cn_name = new Array();
	<?php 
		foreach($users as $row){
			echo "user_arr.push({'user_id':'{$row['user_id']}', 'cn_name':'{$row['cn_name']}', 'department':'{$row->department->name}', 'title':'{$row['title']}', 'entry_date':'{$row['entry_day']}'});";
			echo "cn_name.push('{$row['cn_name']}');";
		}
	?>
	$("#quit-name").autocomplete({
	    source: cn_name
	});

	// 确认办理离职手续
	var quit_user_id = "";
	function confirmQuit(){
		var name = $("#quit-name").val();
		var find_tag = 0;
		$.each(user_arr, function(){
			if(name == this['cn_name']){
				quit_user_id = this['user_id'];
				find_tag = 1;
			}
		});
		if(find_tag == 0){
			showHint("提示信息","该离职申请人不存在");
			$("#quit-name").focus();
		}else{
			var remind_str = "确认提交 "+name+" 的离职申请?";
			showConfirm("提示信息",remind_str,"确认","sendQuit();","取消");
		}
	}

	// 发送离职
	function sendQuit(){
		$.ajax({
	        type:'post',
	        dataType:'json',
	        url:'/ajax/submitQuitApply',
	        data:{'user_id':quit_user_id},
	        success:function(result){
	          if(result.code == 0){
	          	showHint("提示信息","离职申请提交成功");
	            var href_str = "/oa/quitDetail/id/"+result.id;
	            setTimeout(function(){location.href = href_str},1200);
	          }else if(result.code == -1){
	            showHint("提示信息","离职申请提交失败！");
	          }else if(result.code == -2){
	            showHint("提示信息","参数错误！");
	          }else if(result.code == -3){
	            showHint("提示信息","找不到该员工！");
	          }else if(result.code == -4){
	          	showHint("提示信息","该员工已经离职了！");
	          }else if(result.code == -5){
	          	showHint("提示信息","请先更改此员工的权限！");
	          }else{
	          	showHint("提示信息","你没有权限执行此操作！");
	          }
	        }
	      });
	}

	// 显示详情
	function showDetail(){
		var name = $("#quit-name").val();
		$.each(user_arr, function(){
			if(name == this['cn_name']){
				$("#quit-department").text(this['department']);
				$("#quit-title").text(this['title']);
				$("#quit-entry-date").text(this['entry_date']);
			}
		});
	}
</script>