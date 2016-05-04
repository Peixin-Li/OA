<?php
echo "<script type='text/javascript'>";
echo "console.log('activityBudget');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">
		费用预算
		<button class="btn btn-success pd3 w80 ml10 " onclick="showNewBudget();">添加预算</button>
	</h4>
    <!-- 内容 -->
	<div class="pd20">
        <!-- 搜索栏 -->
		<div>
			<label>年份：</label>
			<select class="form-control inline w130" id="year-select">
				<?php for($i = 2013; $i <= 2050; $i++): ?>
				<option value="<?php echo $i;?>"><?php echo $i;?></option>
				<?php endfor; ?>
			</select>
			<button class="btn btn-success w80 mt-5 ml10" onclick="search();">查询</button>
		</div>
        <!-- 费用预算表格 -->
		<?php if(!empty($budgets)): ?>
		<table class="table table-bordered mt20 center w500">
			<tbody>
				<tr class="bg-fa">
					<th class="center">小组名</th>
					<th class="center">预算</th>
				</tr>
				<?php foreach($budgets as $row): ?>
				<tr>
					<td><?php echo $row->team->name;?></td>
					<td><?php echo $row->total; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<button class="btn btn-primary" onclick="showBudgetChange();">查看预算变化</button>
		<button class="btn btn-success w100 ml10" onclick="showEditBudget();">修改预算</button>
		<?php else: ?>
		<h4 class="mt20 pd20 bor-1-ddd center">没有预算，请添加</h4>
		<?php endif; ?>
	</div>
</div>

<!-- 添加预算模态框 -->
<div id="new-description-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">新增费用预算</h4>
    </div>

    <div class="modal-body">
    	<div class="pd10">
    		<label>年份：</label>
    		<select id="new-year-select" class="form-control inline w130" onchange="newYearChange();">
    			<?php for($i = 2013; $i <= 2050; $i++): ?>
				<option value="<?php echo $i;?>"><?php echo $i;?></option>
				<?php endfor; ?>
    		</select>
    		<span class="hidden b2 ml5" id="new-remind">(已有预算，请重新选择)</span>
    	</div>
        <table class="table table-bordered m0 center" id="new-table">
        	<tbody>
        		<tr class="bg-fa">
        			<th class="center w200">小组名</th>
					<th class="center w200">预算</th>
        		</tr>
        		<?php if(!empty($teams)): ?>
        		<?php foreach($teams as $row): ?>
        		<tr>
        			<td><?php echo $row->name;?></td>
        			<td>
        				<input class="form-control center" id="new-<?php echo $row->id;?>" value="0">
        			</td>
        		</tr>
        		<?php endforeach; ?>
        		<?php endif; ?>
        	</tbody>
        </table>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="sendNewBudget()" id="new-btn">提交</button>
    </div>
</div>

<!-- 修改预算模态框 -->
<div id="edit-description-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">修改费用预算</h4>
    </div>

    <div class="modal-body">
        <table class="table table-bordered m0 center" id="edit-table">
        	<tbody>
        		<tr class="bg-fa">
        			<th class="center w200">小组名</th>
					<th class="center w200">预算</th>
        		</tr>
        		<?php foreach($budgets as $row): ?>
        		<tr>
        			<td><?php echo $row->team->name;?></td>
        			<td>
        				<input class="form-control center" id="edit-<?php echo $row->team->id;?>" value="<?php echo floor($row['total']); ?>">
        			</td>
        		</tr>
        		<?php endforeach; ?>
        	</tbody>
        </table>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="sendEditBudget()">提交</button>
    </div>
</div>

<!-- 查看预算变化模态框 -->
<div id="budget-change-div" class="modal fade in hint bor-rad-5 w800" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">查看预算变化</h4>
    </div>

    <div class="modal-body">
        <table class="table table-bordered m0 center" id="change-table">
        	<thead>
        		<tr>
	          		<th class="center w300">组名</th>
	          		<th class="center w300">原始预算(元)</th>
	          		<th class="center w300">原始预算增加(元)</th>
	          		<th class="center w300">原始预算减少(元)</th>
	          		<th class="center w300">已报销(元)</th>
	          		<th class="center w300">当前剩余(元)</th>
	          	</tr>
        	</thead>
          	<tbody></tbody>
        </table>
    </div>
</div>

<!--js-->

<script type="text/javascript">
	// 小组预算变化数组初始化
	var change_arr = new Array();
	var total_change_arr = new Array();
	<?php 
		if(!empty($budgets)){
			$total_add = 0;  // 总的添加
			$total_minus = 0; // 总的减少 
			$total_original = 0; // 总的原始
			$total_cost = 0; // 总的消耗
			foreach($budgets as $b_row){
				$add = 0; // 添加
				$minus = 0; // 减少
				$original = 0; // 原始
				$cost = 0; // 消耗
				$original = $b_row['original'];
				$cost = $b_row['cost'];
				foreach($b_row->changes as $c_row){
					if($c_row['amount'] >= 0){
						$add += $c_row['amount'];
					}else{
						$minus += $c_row['amount'];
					}
				}
				// 剩余
				$rest = $add + $minus + $original - $cost;

				// 计算汇总
				$total_add += $add;
				$total_minus += $minus;
				$total_original += $original;
				$total_cost += $cost;
				echo "change_arr.push({'team':'{$b_row->team['name']}', 'add':'+{$add}', 'minus':'{$minus}', 'original':'{$original}','cost':'-{$cost}','rest':'{$rest}'});";
			}
			// 总的剩余
			$total_rest = $total_add + $total_minus + $total_original - $total_cost;
			echo "total_change_arr.push({'team':'合计', 'add':'+{$total_add}', 'minus':'{$total_minus}', 'original':'{$total_original}', 'cost':'-{$total_cost}', 'rest':'{$total_rest}'});";
		}
	?>

	// 显示预算变化
	function showBudgetChange(){
		// 清空变化表格
		$("#change-table").find("tbody").children().remove();

		// 遍历变化数组
		$.each(change_arr, function(){
			var str = "<tr><td>"+this['team']+"</td><td>"+this['original']+"</td><td>"+this['add']+"</td><td>"+this['minus']+"</td><td>"+this['cost']+"</td><td>"+this['rest']+"</td></tr>";
			$("#change-table").find("tbody").append(str);
		});	

		// 遍历总的变化数组
		$.each(total_change_arr, function(){
			var str = "<tr><td>"+this['team']+"</td><td>"+this['original']+"</td><td>"+this['add']+"</td><td>"+this['minus']+"</td><td>"+this['cost']+"</td><td>"+this['rest']+"</td></tr>";
			$("#change-table").find("tbody").append(str);
		});

		// 显示预算变化模态框
		var ySet = (window.innerHeight - $("#budget-change-div").height())/2;
	    var xSet = (window.innerWidth - $("#budget-change-div").width())/2;
	    $("#budget-change-div").css("top",ySet);
	    $("#budget-change-div").css("left",xSet);
	    $("#budget-change-div").modal({show:true});
	}

	// 已有年份数组初始化
	var list_arr = new Array();
	<?php if(!empty($list)): ?>
		<?php foreach ($list as $lrow): ?>
			list_arr.push("<?php echo $lrow; ?>");
		<?php endforeach; ?>
	<?php endif; ?>

	// 添加年份选择绑定
	function newYearChange(){
		var year = $("#new-year-select").val();
		var find_tag = false; // 查找标记
		$.each(list_arr, function(key, value){
			if(value == year){
				find_tag = true;
				return false;
			}
		});

		// 判断是否已存在
		if(!find_tag){
			$("#new-btn").removeClass("disabled");
			$("#new-remind").addClass("hidden");
		}else{
			$("#new-btn").addClass("disabled");
			$("#new-remind").removeClass("hidden");
		}
	}

	// 修改年度预算
	function sendEditBudget(){
		var d_pattern = /^\d+$/;
		var f_tag = false; // 错误标记

		// 获取数据
		var data = new Array();
		$("#edit-table").find("input").each(function(){
			var num = $(this).val();
			if(!d_pattern.exec(num)){
				showHint("提示信息","预算输入格式不正确");
				$(this).focus();
				f_tag = true;
			}else{
				var id = $(this).attr("id").split("edit-")[1];
				data.push({'team_id':id, 'total':num});
			}
		});

		// 判断是否有错误
		if(!f_tag){
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/editTeamBudget',
		        data:{'year':"<?php echo empty($year) ? date('Y') : $year; ?>", 'data':data},
		        success:function(result){
		          if(result.code == 0){
		          	showHint("提示信息","修改预算成功");
		            setTimeout(function(){location.reload();},1200);
		          }else if(result.code == -1){
		            showHint("提示信息","修改预算失败！");
		          }else if(result.code == -2){
		            showHint("提示信息","参数错误！");
		          }else if(result.code == -3){
		            showHint("提示信息","当前选择年份已有预算！");
		          }else{
		          	showHint("提示信息","你没有权限执行此操作！");
		          }
		        }
		    });
		}
	}

	// 显示修改年度预算
	function showEditBudget(){
		var ySet = (window.innerHeight - $("#edit-description-div").height())/3;
        var xSet = (window.innerWidth - $("#edit-description-div").width())/2;
        $("#edit-description-div").css("top",ySet);
        $("#edit-description-div").css("left",xSet);
        $('#edit-description-div').modal({show:true});
	}

	// 添加年度预算
	function sendNewBudget(){
		var d_pattern = /^\d+$/;
		var f_tag = false; // 错误标记

		// 获取数据
		var data = new Array();
		var year = $("#new-year-select").val();
		$("#new-table").find("input").each(function(){
			var num = $(this).val();
			if(!d_pattern.exec(num)){
				showHint("提示信息","预算输入格式不正确");
				$(this).focus();
				f_tag = true;
			}else{
				var id = $(this).attr("id").split("new-")[1];
				data.push({'team_id':id, 'total':num});
			}
		});

		// 判断是否有错误
		if(!f_tag){
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/addTeamBudget',
		        data:{'year':year, 'data':data},
		        success:function(result){
		          if(result.code == 0){
		          	showHint("提示信息","添加预算成功");
		            setTimeout(function(){location.reload();},1200);
		          }else if(result.code == -1){
		            showHint("提示信息","添加预算失败！");
		          }else if(result.code == -2){
		            showHint("提示信息","参数错误！");
		          }else if(result.code == -3){
		            showHint("提示信息","当前选择年份已有预算！");
		          }else{
		          	showHint("提示信息","你没有权限执行此操作！");
		          }
		        }
		    });
		}
	}

    // 显示添加费用预算的模态框
    function showNewBudget(){
    	newYearChange();

    	// 显示添加费用预算的模态框
    	var ySet = (window.innerHeight - $("#new-description-div").height())/3;
        var xSet = (window.innerWidth - $("#new-description-div").width())/2;
        $("#new-description-div").css("top",ySet);
        $("#new-description-div").css("left",xSet);
        $('#new-description-div').modal({show:true});
    }

	// 查询
	function search(){
		var year = $("#year-select").val();
		location.href = "/oa/activityBudget/year/"+year;
	}

	// 页面初始化
	$(document).ready(function(){
		<?php if(!empty($year)): ?>
			$("#year-select").val("<?php echo $year; ?>");
		<?php endif; ?>
	});
</script>