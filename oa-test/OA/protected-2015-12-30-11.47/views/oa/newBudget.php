<?php
echo "<script type='text/javascript'>";
echo "console.log('newBudget');";
echo "</script>";
?>

<!-- css -->
<style type="text/css">
	/*定义表格样式*/
	.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td{
		padding:8px 5px 8px 5px;
	}
</style>

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-l-1-ddd bor-r-1-ddd bor-t-1-ddd">新增年度费用预算</h4>
	<!-- 选择年份 -->
	<div class="bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd pd10">
		<label>年份：</label>
		<select class="form-control inline w130" onchange="yearCheck();" id="year-select">
			<?php for($i = 2013;$i <= 2050;$i++): ?>
			<option value="<?php echo $i;?>"><?php echo $i;?></option>
			<?php endfor; ?>
		</select>
		<span class="b2 ml10" id="remind-span">（当前选择年份已有年度预算，请重新选择）</span>
	</div>
	<!-- 表格 -->
	<?php if(!empty($departments)): ?>
	<?php $department_count = count($departments);?>
	<table class="table table-bordered m0 center table-striped" id="new-budget-table">
		<thead>	
			<tr>
				<th class="w100 center">项目</th>
				<?php foreach(array_reverse($departments) as $drow): ?>
				<th class="w100 center"><?php echo ($drow['name'] == "总经理办公室") ? "总经办" : $drow['name'];?></th>
				<?php endforeach; ?>
				<th class="w100 center">合计</th>
			</tr>
		</thead>
		<tbody>
			<tr id="office-tr">
				<th class="center">办公费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('office');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="office-total"></td>
			</tr>
			<tr id="welfare-tr">
				<th class="center">福利费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('welfare');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="welfare-total"></td>
			</tr>
			<tr id="travel-tr">
				<th class="center">差旅费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('travel');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="travel-total"></td>
			</tr>
			<tr id="entertain-tr">
				<th class="center">业务招待费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('entertain');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="entertain-total"></td>
			</tr>
			<tr id="hydropower-tr">
				<th class="center">水电费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('hydropower');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="hydropower-total"></td>
			</tr>
			<tr id="intermediary-tr">
				<th class="center">中介费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('intermediary');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="intermediary-total"></td>
			</tr>
			<tr id="rental-tr">
				<th class="center">租赁费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('rental');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="rental-total"></td>
			</tr>
			<tr id="test-tr">
				<th class="center">测试费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('test');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="test-total"></td>
			</tr>
			<tr id="outsourcing-tr">
				<th class="center">外包费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>"  onchange="typeTotalCal('outsourcing');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="outsourcing-total"></td>
			</tr>
			<tr id="property-tr">
				<th class="center">物管费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('property');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="property-total"></td>
			</tr>
			<tr id="repair-tr">
				<th class="center">修缮费</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('repair');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="repair-total"></td>
			</tr>
			<tr id="other-tr">
				<th class="center">其他</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td><input class="form-control center" name="col-<?php echo $i+1;?>" onchange="typeTotalCal('other');departmentTotalCal(this);"></td>
				<?php endfor; ?>
				<td id="other-total"></td>
			</tr>
			<tr id="total-tr">
				<th class="center">合计</th>
				<?php for($i = 0; $i < $department_count; $i++): ?>
				<td name="col-<?php echo $i+1;?>"></td>
				<?php endfor; ?>
				<th class="center" id="year-total"></th>
			</tr>
		</tbody>
	</table>
	<!-- 操作 -->
	<div class="pd20 center">
		<button class="btn btn-success f18px w100" onclick="showNewBudget();" id="send-btn">提交</button>
		<button class="btn btn-default f18px w100 ml20" onclick="location.href='/oa/budget';">返回</button>
	</div>

	<?php else: ?>
	<h4 class="m0 pd20 center bor-1-ddd">加载部门信息失败，请刷新</h4>
	<?php endif; ?>
</div>

<!-- js -->
<script type="text/javascript">
	// 计算每个类型的合计
	function typeTotalCal(type){
		var total = 0;
		var d_pattern = /^\d+$/;
		$("#"+type+"-tr").find("input").each(function(){
			if($(this).val() && d_pattern.exec($(this).val())){
				total += parseFloat($(this).val());
			}
		});
		$("#"+type+"-total").text(total);
	}

	// 计算每个部门的合计
	function departmentTotalCal(obj){
		var department_total = 0;
		var d_pattern = /^\d+$/;
		var col = $(obj).attr("name");
		$("#new-budget-table").find("tbody").find("tr").each(function(){
			if($(this).attr("id").indexOf("total") < 0){
				if($(this).find("input[name='"+col+"']").val() && d_pattern.exec($(this).find("input[name='"+col+"']").val())){
					department_total += parseFloat($(this).find("input[name='"+col+"']").val());
				}
			}
		});
		$("#total-tr").find("td[name='"+col+"']").text(department_total);

		yearTotalCal();
	}

	// 年度总预算的合计
	function yearTotalCal(){
		var year_total = 0;
		$("#total-tr").find("td").each(function(){
			if(!$(this).attr("id")){
				if($(this).text()){
					year_total += parseFloat($(this).text());
				}
			}
		});
		// $("#year-total").text(year_total);
	}

	// 初始化部门数组
	var department_arr = new Array();
	<?php if(!empty($departments)): ?>
	<?php foreach(array_reverse($departments) as $drow): ?>
	<?php echo "department_arr.push('{$drow['department_id']}');";?>
	<?php endforeach; ?>
	<?php endif; ?>

	// 提示是否添加预算
	var data = new Array();
	function showNewBudget(){
		var year = $("#year-select").val();
		data = new Array();
		var f_tag = false;
		var d_pattern = /^\d+$/;
		$("#new-budget-table").find("tbody").find("tr").each(function(){
			if($(this).attr("id").indexOf("total") < 0){
				var type = $(this).attr("id").split("-")[0];
				var detail_arr = new Array();
				if(!f_tag){
					var index = 0;
					$(this).find("input").each(function(){
						var num = $(this).val();
						if(num == ""){
							showHint("提示信息", "请输入预算");
							$(this).focus();
							f_tag = true;
							return false;
						}else if(!d_pattern.exec(num)){
							showHint("提示信息", "预算输入格式不正确");
							$(this).focus();
							f_tag = true;
							return false;
						}else{
							// 将每个部门的预算加入到类型data数组中
							var department_id = department_arr[index++];
							detail_arr.push({'department_id':department_id, 'num':num});
						}
					});
					// 将一个类型的数据加入到data数组中
					data.push({'data_list':detail_arr,'type':type});
				}else{
					return false;
				}
			}
		});

		if(!f_tag){
			var remind_str = "确定添加"+year+"年的年度预算?";
			showConfirm("提示信息",remind_str, "确定", "sendNewBudget();", "取消");
		}
	}

	// 发送添加预算
	function sendNewBudget(){
		var year = $("#year-select").val();
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/addYearBudget',
			data:{'year':year,'data':data},
			success:function(result){
				if(result.code == 0){
					showHint("提示信息","添加年度预算成功");
					setTimeout(function(){location.href = "/oa/budgetDetail/year/"+year;},1200);
				}else if(result.code == -1){
				  	showHint("提示信息","添加年度预算失败");
				}else if(result.code == -2){
				  	showHint("提示信息","参数错误");
				}else if(result.code == -3){
				  	showHint("提示信息","当前选择年度已有年度预算");
				}else{
					showHint("提示信息","你没有权限执行此操作！");
				}
			}
		});
	}

	// 检测是否已经有了年度预算
	function yearCheck(){
		var year = $("#year-select").val();
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/SearchYearBudget',
			data:{'year':year},
			success:function(result){
				$("#send-btn").removeClass("disabled");
				$("#remind-span").addClass("hidden");
				if(result.code == 0){
					$("#send-btn").addClass("disabled");
					$("#remind-span").removeClass("hidden");
				}else if(result.code == -1){
				  	showHint("提示信息","获取年度预算信息失败，请刷新");
				}else if(result.code == -2){
				  	showHint("提示信息","获取年度预算信息参数错误");
				}
			}
		});
	}

	// 页面初始化
	$(document).ready(function(){
		// 默认选择明年
		var year = new Date().getFullYear()+1;
		$("#year-select").val(year);

		// 检测是否有了年度预算
		yearCheck();

		$("input").val("0");
	});
</script>
