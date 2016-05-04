<?php
echo "<script type='text/javascript'>";
echo "console.log('budget');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-l-1-ddd bor-r-1-ddd bor-t-1-ddd">
    	费用预算
    	<button class="btn btn-success pd3 w100 ml20" onclick="newBudget();">添加年度预算</button>
    </h4>
    <!-- 费用预算表格 -->
    <?php if(!empty($data)): ?>
    <table class="table table-bordered m0 center" id="annual-budget-table">
    	<thead>
    		<tr class="bg-fa">
    			<th class="center">年份</th>
    			<th class="center">操作</th>
    		</tr>
    	</thead>
    	<tbody>
    		<?php foreach($data as $row): ?>
    		<tr>
    			<td class="hidden"><?php echo $row['id']; ?></td>
    			<td><?php echo $row['year']; ?></td>
    			<td>
    				<a class="pointer" onclick="check(this);">查看</a>
    				<a class="pointer ml10" onclick="edit(this);">修改</a>
    			</td>
    		</tr>
    		<?php endforeach; ?>
    	</tbody>
    </table>
	<?php endif; ?>
</div>

<!--js-->
<script type="text/javascript">
	// 新增年度预算
	function newBudget(){
		location.href = "/oa/newBudget";
	}

	// 查看
	function check(obj){
		year = $(obj).parent().prev().text();
		location.href = "/oa/budgetDetail/year/"+year;
	}

	// 修改
	function edit(obj){
		year = $(obj).parent().prev().text();
		location.href = "/oa/budgetEdit/year/"+year;
	}
	
	// 删除
	var delete_year = "";
	function showDelete(obj){
		delete_year = $(obj).parent().prev().text();
		var remind_str = "确定要删除"+delete_year+"年的年度预算?";
		showConfirm("提示信息", remind_str, "确定", "sendDelete();", "取消");
	}

	// 发送删除
	function sendDelete(){
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/ajax/removeYearBudget',
			data:{'year':delete_year},
			success:function(result){
				if(result.code == 0){
					showHint("提示信息","删除年度预算成功");
					setTimeout(function(){location.reload();},1200);
				}else if(result.code == -1){
				  	showHint("提示信息","删除年度预算失败");
				}else if(result.code == -2){
				  	showHint("提示信息","参数错误");
				}else{
					showHint("提示信息","你没有权限执行此操作！");
				}
			}
		});
	}
</script>