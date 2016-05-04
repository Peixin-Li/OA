<?php
echo "<script type='text/javascript'>";
echo "console.log('positiveApply');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">转正申请</h4>
	<!-- 发起转正申请 -->
	<table class="table bor-1-ddd" id="positive-table">
		<tr>
			<th class="w130 center">转正员工姓名</th>
			<td>
				<input class="form-control w130" placeholder="员工名称" id="user-name" autofocus onfocus="var check_name_interval = setInterval('findUser()',100);">
			</td>
		</tr>
		<tr>
			<th class="w130 center">部门</th>
			<td>
				<div class="pd5" id="department">&nbsp;</div>	
			</td>
		</tr>
		<tr>
			<th class="w130 center">职位</th>
			<td>
				<div class="pd5" id="title">&nbsp;</div>	
			</td>
		</tr>
		<tr>
			<th class="w130 center">入职时间</th>
			<td>
				<div class="pd5" id="entry-day">&nbsp;</div>	
			</td>
		</tr>
		<tr>
			<th class="w130 center">转正前薪资</th>
			<td>
				<input class="form-control w130 inline" placeholder="转正前薪资" id="trial-salary"> 元/月
			</td>
		</tr>
		<tr>
			<th class="w130 center">约定薪资</th>
			<td>
				<input class="form-control w130 inline" placeholder="约定薪资" id="promise-salary"> 元/月
			</td>
		</tr>
		<tr>
			<th class="w130 center">工作年限</th>
			<td>
				<input class="form-control w130 inline" placeholder="工作年限" id="work-life"> 年
			</td>
		</tr>
		<tr>
			<th class="w130 center">操作</th>
			<td>
				<button class="btn btn-success w100" onclick="sendPositiveApply();">提交</button>
			</td>
		</tr>
	</table>
</div>

<!-- js -->
<script type="text/javascript">
	// 发送转正申请
	function sendPositiveApply(){
		var user_id = "";
		var user_name = $("#user-name").val();
		$.each(user_arr, function(){
			if(this['name'] == user_name) user_id = this['id'];
		});
		var trial_salary = $("#trial-salary").val();
		var promise_salary = $("#promise-salary").val();
		var work_life = $("#work-life").val();
		var d_pattern = /^\d+$/;
		if(user_name == ""){
			showHint("提示信息","请输入员工姓名");
			$("#user-name").focus();
		}else if(user_id == ""){
			showHint("提示信息","查找不到此员工");
			$("#user-name").focus();
		}else if(trial_salary == ""){
			showHint("提示信息","请输入转正前薪资");
			$("#trial-salary").focus();
		}else if(!d_pattern.exec(trial_salary)){
			showHint("提示信息","转正前薪资输入格式不正确");
			$("#trial-salary").focus();
		}else if(promise_salary == ""){
			showHint("提示信息","请输入约定薪资");
			$("#promise-salary").focus();
		}else if(!d_pattern.exec(promise_salary)){
			showHint("提示信息","约定薪资输入格式不正确");
			$("#promise-salary").focus();
		}else if(work_life == ""){
			showHint("提示信息","请输入工作年限");
			$("#work-life").focus();
		}else if(!d_pattern.exec(work_life)){
			showHint("提示信息","工作年限输入格式错误");
			$("#work-life").focus();
		}else{
			$.ajax({
                type:'post',
                url:'/ajax/createQualifyApply',
                dataType:'json',
                data:{'user_id':user_id, 'trial_salary':trial_salary, 'promise_salary':promise_salary, 'work_life':work_life},
                success:function(result){
                    if(result.code == 0){
                        showHint("提示信息","发起转正申请成功");
                        setTimeout(function(){location.reload();},1000);    
                    }else if(result.code == '-1'){
                        showHint("提示信息","发起转正申请失败");
                    }else if(result.code == '-2'){
                        showHint("提示信息","参数错误");
                    }else if(result.code == '-3'){
                        showHint("提示信息","查找不到此员工");
                    }else if(result.code == '-4'){
                        showHint("提示信息","该员工已转正");
                    }else if(result.code == '-5'){
                        showHint("提示信息","添加转正申请记录失败");
                    }else{
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
		}
	}

	// 寻找员工信息
	function findUser(){
		var user_name = $("#user-name").val();
		$.each(user_arr, function(){
			if(this['name'] == user_name){
				$("#department").text(this['department']);
				$("#title").text(this['title']);
				$("#entry-day").text(this['entry_day']);
			}
		});
	}

	// 页面初始化
	$(document).ready(function(){
		$("#user-name").autocomplete({
			source: cn_name
		});
	});

	// 初始化员工数组
	var user_arr = new Array();
	var cn_name = new Array();
	<?php if(!empty($users)): ?>
	<?php foreach($users as $urow): ?>
	user_arr.push({"id":"<?php echo $urow['user_id']; ?>", "name":"<?php echo $urow['cn_name']; ?>", "title":"<?php echo $urow['title']; ?>", "department":"<?php echo $urow->department->name; ?>", "entry_day":"<?php echo $urow['entry_day']; ?>"});
	cn_name.push("<?php echo $urow['cn_name']; ?>");
	<?php endforeach; ?>
	<?php endif; ?>
</script>