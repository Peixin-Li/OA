<?php
echo "<script type='text/javascript'>";
echo "console.log('recruitApply');";
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
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33">招聘申请</h4>
	<!-- 招聘申请表单 -->
	<table class="table m0">
		<tr>
			<th class="center w130">申请人</th>
			<td><?php echo $this->user->cn_name; ?></td>
		</tr>
		<tr>
			<th class="center w130">申请部门</th>
			<td>	
				<select class="inline form-control w150" id="department-select" onchange="getTitle();">
                <?php foreach($departments as $d_row){ ?>
                    <option value="<?php echo $d_row->department_id; ?>"><?php echo $d_row->name; ?></option>
                <?php } ?>
				</select>
				<input class="form-control w200 hidden inline" id="new-department-input" placeholder="请输入部门名称">
				<a class="pointer mt5" onclick="newDepartment();" id="new-department-a">新增部门</a>
				<a class="pointer mt5 hidden" onclick="cancelNewDepartment();" id="cancel-department-a">取消</a>
				<span id="department-formation" class="ml20"></span><span id="department-current" class="ml20"></span>
			</td>
		</tr>
		<tr>
			<th class="center w130">招聘职位</th>
			<td>
				<select class="inline form-control w150" id="title-select" onchange="getTitleFormation();"></select>
				<input class="form-control w200 hidden inline" id="new-title-input" placeholder="请输入职位名称">
				<a class="pointer mt5" onclick="newTitle();" id="new-title-a">新增职位</a>
				<a class="pointer mt5 hidden" onclick="cancelNewTitle();" id="cancel-title-a">取消</a>
				<span id="title-formation" class="ml20"></span><span id="title-current" class="ml20"></span>
			</td>
		</tr>
		<tr>
			<th class="center w130">招聘人数</th>
			<td><input class="form-control w50 inline" id="recruit-num">&nbsp;人</td>
		</tr>
		<tr>
			<th class="center w130">希望到职日期</th>
			<td>	
				<input class="form-control w200 pointer" id="expected-date" placeholder="请输入希望到职日期">
			</td>
		</tr>
		<tr>
			<th class="center w130">建议薪酬范围</th>
			<td>
				<input class="form-control w80 inline" id="expected-salary-low">
				&nbsp;至&nbsp;
				<input class="form-control w80 inline" id="expected-salary-high">
				&nbsp;（元/月）
			</td>
		</tr>
		<tr>
			<th class="center w130">工作经验</th>
			<td>
				<input class="form-control w50 inline" id="work-experience" placeholder="工作经验"> &nbsp;年
			</td>
		</tr>
		<tr>
			<th class="center w130">招聘类型</th>
			<td>
				<select class="inline form-control w150" id="type-select" onchange="recruitTypeChange();">
					<option value="internal">编制内增补</option>
					<option value="replace">编制内替代</option>
					<option value="add">编制外增补</option>
				</select>
			</td>
		</tr>
		<tr id="replace-tr-1" class="hidden">
			<th class="center w130">替代人姓名</th>
			<td>
				<input class="form-control w200" id="replace-name" placeholder="请输入替代人姓名">
			</td>
		</tr>
		<tr id="replace-tr-2" class="hidden">
			<th class="center w130">替代人离职日期</th>
			<td>
				<input class="form-control w200 pointer" id="replace-out-date" placeholder="请输入替代人离职日期">
			</td>
		</tr>
		<tr id="add-tr">
			<th class="center w130">招聘原因</th>
			<td>
				<input class="form-control" id="add-reason" placeholder="请输入招聘原因">
			</td>
		</tr>
		<tr>
			<th class="center w130">主要工作职责</th>
			<td>
				<textarea id="work-description" class="form-control"></textarea>
			</td>
		</tr>
		<tr>
			<th class="center w130">入职条件</th>
			<td>
				<table class="m0 table bor-1-ddd">
					<tr>
						<th class="w100 center">性别</th>
						<td>	
							<input type="radio" name="sex" value="m">&nbsp;男&nbsp;&nbsp;
							<input type="radio" name="sex" value="f" >&nbsp;女&nbsp;&nbsp;
							<input type="radio" name="sex" value="none" checked>&nbsp;不要求
						</td>
					</tr>
					<tr>
						<th class="w100 center">年龄</th>
						<td>
							<input class="form-control w50 inline" id="age" readonly>&nbsp;岁
							&nbsp;&nbsp;&nbsp;
							<input type="checkbox" id="age-checkbox" checked onclick="ageChange(this);">&nbsp;不要求
						</td>
					</tr>
					<tr>
						<th class="w100 center">学历</th>
						<td>
							<select id="education-background" class="inline form-control w100">
								<option value="undergraduate">本科</option>
								<option value="junior">初中</option>
								<option value="high">高中</option>
								<option value="college">大专</option>
								<option value="graduate">研究生</option>
								<option value="master">硕士</option>
								<option value="dr">博士</option>
							</select>
						</td>
					</tr>
					<tr>
						<th class="w100 center">专业</th>
						<td>
							<input class="form-control" id="major" placeholder="请输入专业要求">
						</td>
					</tr>
					<tr>
						<th class="w100 center">计算机水平</th>
						<td>
							<input type="radio" name="computer-level" value="great">&nbsp;<span class="pointer" onclick="$(this).prev().click();">优秀</span>&nbsp;&nbsp;
							<input type="radio" name="computer-level" value="good">&nbsp;<span class="pointer" onclick="$(this).prev().click();">良好</span>&nbsp;&nbsp;
							<input type="radio" name="computer-level" value="general">&nbsp;<span class="pointer" onclick="$(this).prev().click();">一般</span>&nbsp;&nbsp;
							<input type="radio" name="computer-level" value="none" checked>&nbsp;<span class="pointer" onclick="$(this).prev().click();">不要求</span>
						</td>
					</tr>
					<tr>
						<th class="w100 center">国语水平</th>
						<td>
							<input type="radio" name="mandarin-level" value="good">&nbsp;<span class="pointer" onclick="$(this).prev().click();">流利</span>&nbsp;&nbsp;
							<input type="radio" name="mandarin-level" value="general">&nbsp;<span class="pointer" onclick="$(this).prev().click();">一般</span>&nbsp;&nbsp;
							<input type="radio" name="mandarin-level" value="none" checked>&nbsp;<span class="pointer" onclick="$(this).prev().click();">不要求</span>&nbsp;&nbsp;
						</td>
					</tr>
					<tr>
						<th class="w100 center">粤语水平</th>
						<td>
							<input type="radio" name="cantonese-level" value="good">&nbsp;<span class="pointer" onclick="$(this).prev().click();">流利</span>&nbsp;&nbsp;
							<input type="radio" name="cantonese-level" value="general">&nbsp;<span class="pointer" onclick="$(this).prev().click();">一般</span>&nbsp;&nbsp;
							<input type="radio" name="cantonese-level" value="none" checked>&nbsp;<span class="pointer" onclick="$(this).prev().click();">不要求</span>&nbsp;&nbsp;
						</td>
					</tr>
					<tr>
						<th class="w100 center">外语水平</th>
						<td>
							<input type="radio" name="foreign-language-level" value="good">&nbsp;<span class="pointer" onclick="$(this).prev().click();">流利</span>&nbsp;&nbsp;
							<input type="radio" name="foreign-language-level" value="general">&nbsp;<span class="pointer" onclick="$(this).prev().click();">一般</span>&nbsp;&nbsp;
							<input type="radio" name="foreign-language-level" value="none" checked>&nbsp;<span class="pointer" onclick="$(this).prev().click();">不要求</span>&nbsp;&nbsp;
						</td>
					</tr>
					<tr>
						<th class="w100 center">户籍</th>
						<td>
							<input type="radio" name="residence" value="local">&nbsp;<span class="pointer" onclick="$(this).prev().click();">本地</span>&nbsp;&nbsp;
							<input type="radio" name="residence" value="nonlocal">&nbsp;<span class="pointer" onclick="$(this).prev().click();">外地</span>&nbsp;&nbsp;
							<input type="radio" name="residence" value="none" checked>&nbsp;<span class="pointer" onclick="$(this).prev().click();">不要求</span>&nbsp;&nbsp;
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th class="center w130">个性</th>
			<td>
				<input class="form-control" id="characteristics" placeholder="请输入个性">
			</td>
		</tr>
		<tr>
			<th class="center w130">备注</th>
			<td>
				<input class="form-control" id="comment" placeholder="请输入其他信息">
			</td>
		</tr>
		<tr>
			<th class="center w130">操作</th>
			<td><button class="btn btn-success w130" onclick="recruitApply();">提交招聘申请</button></td>
		</tr>
	</table>
</div>

<!-- js -->
<script type="text/javascript">
	// 年龄要求变化
	function ageChange(obj){
		if(obj.checked){
			$(obj).prev().attr("readonly","true");
		}else{
			$(obj).prev().removeAttr("readonly");
		}
	}

	// 发送招聘申请
	function recruitApply(){
		var department_name = "";
		if(!$("#new-department-input").hasClass("hidden")){
			department_name = $("#new-department-input").val();
		}else{
			$("#department-select").children().each(function(){
				if($(this).val() == $("#department-select").val()){
					department_name = $(this).text();
				}
			});
		}
		var title_name = "";
		if(!$("#new-title-input").hasClass("hidden")){
			title_name = $("#new-title-input").val();
		}else{
			$("#title-select").children().each(function(){
				if($(this).val() == $("#title-select").val()){
					title_name = $(this).text();
				}
			});
		}
		var recruit_num = $("#recruit-num").val();
		var expected_date = $("#expected-date").val();
		var expected_salary_low = $("#expected-salary-low").val();
		var expected_salary_high = $("#expected-salary-high").val();
		var expected_salary_str = expected_salary_low+"-"+expected_salary_high;
		var type = $("#type-select").val();
		var replace_name = "";
		var user_id = "";
		var find_tag = 0;
		var replace_out_date = "";
		var add_reason = "";
		if(type == "replace"){
			replace_name = $("#replace-name").val();
			user_id = "";
			$.each(users, function(){
				if(this['name'] == replace_name){
					user_id = this['id'];
					find_tag = 1;
					return false;
				}
			});
			replace_out_date = $("#replace-out-date").val();
		}else{
			add_reason = $("#add-reason").val();
		}
		var work_description = $("#work-description").val();
		var sex_arg = $('input[name="sex"]:checked').val();
		var age_arg = "0";
		if(document.getElementById("age-checkbox").checked==false){
			age_arg = $("#age").val();
		}
		var education_background = $("#education-background").val();
		var major = $("#major").val();
		var computer_level = $('input[name="computer-level"]:checked').val();
		var mandarin_level = $('input[name="mandarin-level"]:checked').val();
		var cantonese_level = $('input[name="cantonese-level"]:checked').val();
		var foreign_language_level = $('input[name="foreign-language-level"]:checked').val();
		var residence = $('input[name="residence"]:checked').val();
		var work_time = $("#work-experience").val();
		var characteristics = $("#characteristics").val();
		var comment = $("#comment").val();


		var d_pattern = /^\d+$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;

		if(department_name == ""){
			showHint("提示信息","请输入部门名称");
			if(!$("#new-department-input").hasClass("hidden")){
				$("#new-department-input").focus();
			}
		}else if(title_name == ""){
			showHint("提示信息","请输入职位名称");
			if(!$("#new-title-input").hasClass("hidden")){
				$("#new-title-input").focus();
			}
		}else if(!d_pattern.exec(recruit_num)){
			if(recruit_num == ""){
				showHint("提示信息","请输入招聘人数！");
			}else{
				showHint("提示信息","招聘人数输入格式错误！");
			}
			$("#recruit-num").focus();
		}else if(!date_pattern.exec(expected_date)){
			if(expected_date == ""){
				showHint("提示信息","请输入希望到职日期！");
			}else{
				showHint("提示信息","希望到职日期输入格式错误！");
			}
			$("#expected-date").focus();
		}else if(!d_pattern.exec(expected_salary_low) || !d_pattern.exec(expected_salary_high)){
			if(expected_salary_low == ""){
				showHint("提示信息","请输入薪资范围！");
				$("#expected-salary-low").focus();
			}else if(expected_salary_high == ""){
				showHint("提示信息","请输入薪资范围！");
				$("#expected-salary-high").focus();
			}else{
				showHint("提示信息","薪资范围输入格式错误！");
			}
		}else if(parseInt(expected_salary_low) >= parseInt(expected_salary_high)){
			showHint("提示信息","请输入正确的薪资范围");
		}else if(type == "replace" && find_tag == 0){
			showHint("提示信息","找不到该替代人！");
			$("#replace-name").focus();
		}else if(type == "replace" && !date_pattern.exec(replace_out_date)){
			showHint("提示信息","替代人离职日期输入格式错误！");
			$("#replace-out-date").focus();
		}else if(type == "add" && add_reason == ""){
			showHint("提示信息","请输入招聘原因！");
			$("#add-reason").focus();
		}else if(work_description == ""){
			showHint("提示信息","请输入职位描述！");
			$("#work-description").focus();
		}else if(document.getElementById("age-checkbox").checked==false && age_arg == ""){
			showHint("提示信息","请输入年龄要求！");
			$("#age").focus();
		}else if(!d_pattern.exec(work_time)){
			if(work_time == ""){
				showHint("提示信息","请输入工作经验！");
			}else{
				showHint("提示信息","工作经验输入格式不正确！");
			}
			$("#work-experience").focus();
		}else{
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/recruitApply',
		        data:{'department':department_name, 'title':title_name, 'number':recruit_num, 'entry_day':expected_date, 'pay':expected_salary_str, 'type':type, 'quit_user_id':user_id, 'quit_date':replace_out_date, 'add_reason':add_reason, 'work_content':work_description, 'work_life':work_time, 'individuality':characteristics, 'comment':comment, 'gender':sex_arg, 'age':age_arg, 'education':education_background, 'professional':major, 'computer':computer_level, 'mandarin':mandarin_level, 'cantonese':cantonese_level, 'foreign':foreign_language_level, 'residence':residence},
		        success:function(result){
		          	if(result.code == 0){
		          		showHint("提示信息","提交招聘申请成功！");
		          		var href_str = "/oa/recruitApplyDetail/id/"+result.id+"/type/recruitApply";
		          		setTimeout(function(){location.href = href_str;},1200);
		          	}else if(result.code == -1){
		          		showHint("提示信息","提交招聘申请失败！");
		          	}else if(result.code == -2){
		          		showHint("提示信息","参数错误！");
		          	}else if(result.code == -4){
		          		showHint("提示信息","招聘人数已超出编制！");
		          	}else if(result.code == -5){
		          		showHint("提示信息","不存在该编制！");
		          	}else if(result.code == -99){
		          		showHint("提示信息","你没有权限进行此操作！");
		          	}
		        }
		    });
		}
	}

	// 新增职位
	function newTitle(){
		$("#title-select").addClass("hidden");
		$("#new-title-input").removeClass("hidden");
		$("#new-title-input").val("");
		$("#new-title-input").focus();
		$("#cancel-title-a").removeClass("hidden");
		$("#new-title-a").addClass("hidden");
		$("#title-formation").addClass("hidden");
		$("#title-current").addClass("hidden");
	}

	// 新增职位-取消
	function cancelNewTitle(){
		$("#title-select").removeClass("hidden");
		$("#new-title-input").addClass("hidden");
		$("#cancel-title-a").addClass("hidden");
		$("#new-title-a").removeClass("hidden");
		$("#title-formation").removeClass("hidden");
		$("#title-current").removeClass("hidden");
	}

	// 新建部门
	function newDepartment(){
		$("#department-select").addClass("hidden");
		$("#new-department-input").removeClass("hidden");
		$("#new-department-input").val("");
		$("#new-department-input").focus();
		$("#cancel-department-a").removeClass("hidden");
		$("#new-department-a").addClass("hidden");
		$("#department-formation").addClass("hidden");
		$("#department-current").addClass("hidden");
		$("#title-formation").addClass("hidden");
		$("#title-current").addClass("hidden");

		$("#new-title-a").click();
		$("#new-department-input").focus();
	}

	// 新建部门-取消
	function cancelNewDepartment(){
		$("#department-select").removeClass("hidden");
		$("#new-department-input").addClass("hidden");
		$("#cancel-department-a").addClass("hidden");
		$("#new-department-a").removeClass("hidden");
		$("#department-formation").removeClass("hidden");
		$("#department-current").removeClass("hidden");
		$("#title-formation").removeClass("hidden");
		$("#title-current").removeClass("hidden");
	}

	// 招聘类型改变
	function recruitTypeChange(){
		if($("#type-select").val() == "replace"){
			$("#replace-tr-1").removeClass("hidden");
			$("#replace-tr-2").removeClass("hidden");
			$("#add-tr").addClass("hidden");
		}else{
			$("#replace-tr-1").addClass("hidden");
			$("#replace-tr-2").addClass("hidden");
			$("#add-tr").removeClass("hidden");
		}
	}

	// 初始化
	$(document).ready(function(){
		$('#expected-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
	    $('#replace-out-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
	    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	    getTitle();
	});
	
	// 根据部门获取编制
	function getTitle(){
		var department_id = $("#department-select").val();
		$.ajax({
	        type:'post',
	        dataType:'json',
	        url:'/ajax/getTitleByDepartment',
	        data:{'id':department_id},
	        success:function(result){
	          	if(result.code == 0){
	          		$("#title-select").children().remove();
	          		$.each(result.titles,function(){
	          			var str = "<option value='"+this['formation_id']+"'>"+this['title']+"</option>";
	          			$("#title-select").append(str);
	          		});
	          		var formation_str = "定编- "+result.count+" 人";
	          		$("#department-formation").text(formation_str);
	          		var current_str = "现有- "+result.user_count+" 人";
	          		$("#department-current").text(current_str);
	          		getTitleFormation();
	          	}else if(result.code == -1){
	          		showHint("提示信息","获取部门编制失败！");
	          	}else if(result.code == -2){
	          		showHint("提示信息","参数错误！");
	          	}else if(result.code == -99){
	          		showHint("提示信息","你没有权限进行此操作！");
	          	}
	        }
	    });
	}

	// 根据部门id和职位名称获取职位编制
	function getTitleFormation(){
		var department_id = $("#department-select").val();
		var title_id = $("#title-select").val();
  		var title = "";
  		$("#title-select").children().each(function(){
  			if($(this).val() == title_id){
  				title = $(this).text();
  			}
  		});
		$.ajax({
	        type:'post',
	        dataType:'json',
	        url:'/ajax/getTitleFormation',
	        data:{'department_id':department_id, 'title':title},
	        success:function(result){
	          	if(result.code == 0){
	          		var formation_str = "定编- "+result.total+" 人";
	          		$("#title-formation").text(formation_str);
	          		var current_str = "现有- "+result.count+" 人";
	          		$("#title-current").text(current_str);
	          	}else if(result.code == -1){
	          		showHint("提示信息","获取职位编制失败！");
	          	}else if(result.code == -2){
	          		showHint("提示信息","参数错误！");
	          	}else if(result.code == -99){
	          		showHint("提示信息","你没有权限进行此操作！");
	          	}
	        }
	    });
	}

	// 自动补全
	var users = new Array();
	var cn_name = new Array();
	<?php 
		foreach($users as $user){
			echo "users.push({'id':'{$user['user_id']}', 'name':'{$user['cn_name']}'});";
			echo "cn_name.push('{$user['cn_name']}');";
		}
	?>
	$("#replace-name").autocomplete({
		source: cn_name
	});
</script>
