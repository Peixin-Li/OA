<?php
echo "<script type='text/javascript'>";
echo "console.log('budgetEdit');";
echo "</script>";
?>

<!--报表样式，先不删，不转移，等修改报表时再处理-->
<style type="text/css">
	/*定义表格边框*/
	.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td{
		padding:8px 5px 8px 5px;
	}
</style>
<!-- 主界面 -->
<div>
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-l-1-ddd bor-r-1-ddd bor-t-1-ddd">修改费用预算</h4>
    <!-- 标题 -->
	<div class="pd10 bor-l-1-ddd bor-r-1-ddd bor-t-1-ddd center bg-fa">
		<h4 class="m0">
			<strong><?php echo empty($year) ? '2016' : $year;?>年费用预算</strong>
		</h4>
	</div>
    <!-- 修改年度预算表格 -->
	<?php if(!empty($departments)): ?>
	<?php $department_count = count($departments);?>
	<table class="table table-bordered m0 center table-striped" id="edit-budget-table">
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
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="office-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="office-total"></td>
			</tr>
			<tr id="welfare-tr">
				<th class="center">福利费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="welfare-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="welfare-total"></td>
			</tr>
			<tr id="travel-tr">
				<th class="center">差旅费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="travel-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="travel-total"></td>
			</tr>
			<tr id="entertain-tr">
				<th class="center">业务招待费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="entertain-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="entertain-total"></td>
			</tr>
			<tr id="hydropower-tr">
				<th class="center">水电费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="hydropower-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="hydropower-total"></td>
			</tr>
			<tr id="intermediary-tr">
				<th class="center">中介费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="intermediary-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="intermediary-total"></td>
			</tr>
			<tr id="rental-tr">
				<th class="center">租赁费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="rental-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="rental-total"></td>
			</tr>
			<tr id="test-tr">
				<th class="center">测试费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="test-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="test-total"></td>
			</tr>
			<tr id="outsourcing-tr">
				<th class="center">外包费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="outsourcing-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="outsourcing-total"></td>
			</tr>
			<tr id="property-tr">
				<th class="center">物管费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="property-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="property-total"></td>
			</tr>
			<tr id="repair-tr">
				<th class="center">修缮费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="repair-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="repair-total"></td>
			</tr>
			<tr id="other-tr">
				<th class="center">其他</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td>
					<input class="form-control center" id="other-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>" onchange="totalCal();">
				</td>
				<?php endforeach; ?>
				<td id="other-total"></td>
			</tr>
			<tr id="total-tr">
				<th class="center">合计</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="year-total"></td>
			</tr>
		</tbody>
	</table>
	<div class="pd20 center">
		<button class="btn btn-success f18px w100" onclick="showEditBudget();" id="send-btn">提交</button>
		<button class="btn btn-default f18px w100 ml20" onclick="location.href='/oa/budget';">返回</button>
	</div>

	<?php else: ?>
	<h4 class="m0 pd20 center bor-1-ddd">加载部门信息失败，请刷新</h4>
	<?php endif; ?>
</div>

<!--js-->
<script type="text/javascript">
	// 计算汇总
	function totalCal(){
		var d_pattern = /^\d+$/;
		// 计算每类费用的合计
		$("#edit-budget-table").find("tbody").find("tr").each(function(){
			if($(this).attr("id").indexOf("total") < 0){
				var total = 0;
				$(this).find("input").each(function(){
					if($(this).val() && d_pattern.exec($(this).val())){
						total += parseFloat($(this).val());
					}
				});	
				$(this).find("td").last().text(total);
			}
		});

		// 计算每个部门的合计
		var year_total = 0;
		$("#total-tr").find("td").each(function(){
			if(!$(this).attr("id")){
				var total = 0;
				var col = $(this).attr("name");
				$("#edit-budget-table").find("tbody").find("tr").each(function(){
					if($(this).attr("id").indexOf("total") < 0){
						var num = $(this).find("input[name='"+col+"']").val();
						if(num && d_pattern.exec(num)){
							total += parseFloat(num);
						}
					}
				});
				year_total += total;
				$(this).text(total);
			}
		});

		// 计算年度合计
		// $("#year-total").text(year_total);
	}

	// 页面初始化
	$(document).ready(function(){
		// 初始化数据
		<?php 
			if(!empty($data)){
				foreach($data as $row){
					echo "$('#{$row['type']}-{$row['department_id']}').val('".round($row['total'],2)."');";
				}
			}
		?>

		// 计算合计
		totalCal();
	});	

	// 初始化部门数组
	var department_arr = new Array();
	<?php if(!empty($departments)): ?>
	<?php foreach(array_reverse($departments) as $drow): ?>
	<?php echo "department_arr.push('{$drow['department_id']}');";?>
	<?php endforeach; ?>
	<?php endif; ?>

	// 提示是否添加预算
	var data = new Array();
	function showEditBudget(){
		var year = $("#year-select").val();
		data = new Array();
		var f_tag = false;  // 错误标记
		var d_pattern = /^\d+$/;
		$("#edit-budget-table").find("tbody").find("tr").each(function(){
			if($(this).attr("id").indexOf("total") < 0){
				var type = $(this).attr("id").split("-")[0]; // 类型
				var detail_arr = new Array();
				if(!f_tag){
					var index = 0; // 插入数据的下标
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

		// 判断是否有错误
		if(!f_tag){
			// 发送修改预算
			sendEditBudget();
		}
	}

	// 发送修改预算
	function sendEditBudget(){
		var year = "<?php echo empty($year) ? '' : $year;?>";
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/edityearbudget',
			data:{'year':year,'data':data},
			success:function(result){
				if(result.code == 0){
					showHint("提示信息","修改年度预算成功");
					setTimeout(function(){location.href = '/oa/budgetDetail/year/'+year;},1200);
				}else if(result.code == -1){
				  	showHint("提示信息","修改年度预算失败");
				}else if(result.code == -2){
				  	showHint("提示信息","参数错误");
				}else{
					showHint("提示信息","你没有权限执行此操作！");
				}
			}
		});
	}
</script>