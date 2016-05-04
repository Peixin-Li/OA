<?php
echo "<script type='text/javascript'>";
echo "console.log('budgetDetail');";
echo "</script>";
?>

<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-l-1-ddd bor-r-1-ddd bor-t-1-ddd">费用预算详情</h4>
	<!-- 标题 -->
	<?php if(!empty($data) && !empty($departments)): ?>
	<div class="pd10 bor-l-1-ddd bor-r-1-ddd bor-t-1-ddd center bg-fa">
		<h4 class="m0 ">
			<strong><?php echo empty($year) ? '2016' : $year;?>年费用预算详情</strong>
		</h4>
	</div>
	<!-- 费用预算表格 -->
	<table class="table table-bordered m0 center table-striped" id="detail-budget-table">
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
			<tr>
				<th class="center">办公费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="office-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="office-total"></td>
			</tr>
			<tr>
				<th class="center">福利费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="welfare-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="welfare-total"></td>
			</tr>
			<tr>
				<th class="center">差旅费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="travel-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="travel-total"></td>
			</tr>
			<tr>
				<th class="center">业务招待费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="entertain-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="entertain-total"></td>
			</tr>
			<tr>
				<th class="center">水电费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="hydropower-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="hydropower-total"></td>
			</tr>
			<tr>
				<th class="center">中介费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="intermediary-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="intermediary-total"></td>
			</tr>
			<tr>
				<th class="center">租赁费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="rental-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="rental-total"></td>
			</tr>
			<tr>
				<th class="center">测试费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="test-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="test-total"></td>
			</tr>
			<tr>
				<th class="center">外包费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="outsourcing-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="outsourcing-total"></td>
			</tr>
			<tr>
				<th class="center">物管费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="property-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="property-total"></td>
			</tr>
			<tr>
				<th class="center">修缮费</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="repair-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="repair-total"></td>
			</tr>
			<tr>
				<th class="center">其他</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="other-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="other-total"></td>
			</tr>
			<tr id="month-tr">
				<th class="center">月度合计</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="month-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="month-total"></td>
			</tr>
			<tr id="year-tr">
				<th class="center">年度合计</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="year-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="year-total"></td>
			</tr>
			<tr id="percent-tr">
				<th class="center">占比</th>
				<?php $i = 1;foreach(array_reverse($departments) as $drow): ?>
				<td id="percent-<?php echo $drow['department_id'];?>" name="col-<?php echo $i++;?>"></td>
				<?php endforeach; ?>
				<td id="percent-total"></td>
			</tr>
			<tr id="check-tr">
				<th class="center">预算变化</th>
				<?php foreach(array_reverse($departments) as $drow): ?>
				<td>
					<a class="pointer" onclick="checkChange('<?php echo $drow['department_id']; ?>','<?php echo $drow['name']; ?>');">查看</a>
				</td>
				<?php endforeach; ?>
				<td></td>
			</tr>
		</tbody>
	</table>
	<!-- 返回按钮 -->
	<div class="pd20 center">
		<button class="btn btn-default f18px w100" onclick="location.href='/oa/budget';">返回</button>
	</div>

	<?php else: ?>
	<h4 class="m0 pd20 center bor-1-ddd">加载年度预算信息失败，请刷新</h4>
	<?php endif; ?>
</div>

<!-- 查看预算变化模态框 -->
<div id="check-budget-div" class="modal fade in hint bor-rad-5 w1000" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title"></h4>
    </div>
    <div class="modal-body">
        <table class="table table-bordered m0 center" id="budget-change-table">
        	<thead>
        		<tr>
	          		<th class="center w300">项目</th>
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
	// 预算变化数组初始化
	var change_arr = new Array();
	var total_change_arr = new Array();
	<?php 
		if(!empty($changes)){
			foreach($changes as $c_key => $c_row){
				// 汇总数据初始化
				$total_add = 0;
				$total_minus = 0;
				$total_original = 0;
				$total_cost = 0;
				foreach($c_row as $t_key => $t_row){
					// 数据初始化
					$add = 0;
					$minus = 0;
					$original = 0;
					$cost = 0;

					// 数据赋值
					$original = $t_row['original'];
					$cost = $t_row['cost'];
					foreach($t_row->changes as $m_row){
						if($m_row['amount'] >= 0){
							$add += $m_row['amount'];
						}else{
							$minus += $m_row['amount'];
						}
					};

					// 计算剩余
					$rest = $add + $minus + $original - $cost;

					// 计算汇总数据
					$total_add += $add;
					$total_minus += $minus;
					$total_original += $original;
					$total_cost += $cost;
					echo "change_arr.push({'department_id':'{$c_key}', 'type':'{$t_key}', 'add':'".(($add > 0) ? '+'.$add : '0')."', 'minus':'{$minus}', 'original':'{$original}','cost':'".(($cost > 0) ? '-'.$cost : '0')."','rest':'{$rest}'});";
				}

				// 计算汇总剩余
				$total_rest = $total_add + $total_minus + $total_original - $total_cost;
				echo "total_change_arr.push({'department_id':'{$c_key}', 'add':'".(($total_add > 0) ? '+'.$total_add : '0')."', 'minus':'{$total_minus}', 'original':'{$total_original}', 'cost':'".(($total_cost > 0) ? '-'.$total_cost : '0')."', 'rest':'{$total_rest}'});";
			}
		}
	?>

	// 类别翻译
	function categoryToCN(category){
		switch(category){
			case "office":{return "办公费";break;}
			case "welfare":{return "福利费";break;}
			case "travel":{return "差旅费";break;}
			case "entertain":{return "业务招待费";break;}
			case "hydropower":{return "水电费";break;}
			case "intermediary":{return "中介费";break;}
			case "rental":{return "租赁费";break;}
			case "test":{return "测试费";break;}
			case "outsourcing":{return "外包费";break;}
			case "property":{return "物管费";break;}
			case "repair":{return "修缮费";break;}
			case "other":{return "其他";break;}
		}
	}

	// 初始化类别数组
	var category_arr = new Array();
	category_arr.push('office');
	category_arr.push('welfare');
	category_arr.push('travel');
	category_arr.push('entertain');
	category_arr.push('hydropower');
	category_arr.push('intermediary');
	category_arr.push('rental');
	category_arr.push('test');
	category_arr.push('outsourcing');
	category_arr.push('property');
	category_arr.push('repair');
	category_arr.push('other');

	// 查看预算变化
	function checkChange(department_id,name){
		$("#check-budget-div").find("h4.hint-title").text("查看"+name+"年预算变化");
		$("#budget-change-table").find("tbody").children().remove();

        var category_arr_change = new Array('office','welfare','travel','entertain','hydropower','intermediary','rental','test','outsourcing','property','repair','other');

        // 遍历预算类别数组
		$.each(category_arr_change, function(key,value){
			var c_type = value; // 预算类别

			var find_tag = false; // 查找标记

			// 遍历预算变化数组
			$.each(change_arr, function(){
				if(this['type'] == c_type && department_id == this['department_id']){
					// 填充数据
					var str = "<tr><td>"+categoryToCN(this['type'])+"</td><td>"+this['original']+"</td><td>"+this['add']+"</td><td>"+this['minus']+"</td><td>"+this['cost']+"</td><td>"+this['rest']+"</td></tr>";
					$("#budget-change-table").find("tbody").append(str);
					
					var type = this['type'];
					$.each(category_arr_change, function(key,value){
						if(type.indexOf(value) >= 0){
							category_arr_change[key] = "";  // 置为空，证明已经输出过了
						}
					});
					find_tag = true;
				}
			});

			// 判断是否存在
			if(!find_tag){
				var str = "<tr><td>"+categoryToCN(c_type)+"</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>";
				$("#budget-change-table").find("tbody").append(str);
			}
		});

		// 年度合计
		$.each(total_change_arr, function(){
			if(department_id == this['department_id']){
				var str = "<tr><td>年度合计</td><td>"+this['original']+"</td><td>"+this['add']+"</td><td>"+this['minus']+"</td><td>"+this['cost']+"</td><td>"+this['rest']+"</td></tr>";
				$("#budget-change-table").find("tbody").append(str);
			}
		});

		// 显示年度预算变化模态框
		var ySet = (window.innerHeight - $("#check-budget-div").height())/2;
	    var xSet = (window.innerWidth - $("#check-budget-div").width())/2;
	    $("#check-budget-div").css("top",ySet);
	    $("#check-budget-div").css("left",xSet);
	    $("#check-budget-div").modal({show:true});
	}

	// 页面初始化
	$(document).ready(function(){
		// 初始化数据
		<?php 
			if(!empty($data)){
				foreach($data as $row){
					echo "$('#{$row['type']}-{$row['department_id']}').text('".round($row['total'],2)."');";
				}
			}
		?>

		// 计算合计
		totalCal();

		// 月度和年度合计
		monthAndYearCal();

		// 占比计算
		percentCal();
	});	

	// 计算合计
	function totalCal(){
		$("#detail-budget-table").find("tbody").find("tr").each(function(){
			if(!$(this).attr("id")){
				var total = 0;
				$(this).find("td").each(function(){
					if($(this).attr("id").indexOf("total") < 0){
						if($(this).text()){
							total += parseFloat($(this).text());
						}
					}
				});
				$(this).find("td").last().text(total);
			}
		});
	}

	// 月度和年度合计
	function monthAndYearCal(){
		var i = 1;
		var month_total = 0;
		$("#month-tr").find("td").each(function(){
			if($(this).attr("id").indexOf("total") < 0){
				var col = $(this).attr("name");
				var num = 0;
				// 汇总部门每个费用
				$("td[name='"+col+"']").each(function(){
					if(!$(this).parent().attr("id")){
						if($(this).text()){
							num += parseFloat($(this).text());
						}
					}
				});
				// 年度合计
				$("#year-tr").find("td[name='"+col+"']").each(function(){
					$(this).text(num);
				});
				// 汇总
				month_total += num;
				// 保留小数点后两位
				num = (num/12).toFixed(2);
				// 如果都为0就不显示
				if(num.split(".")[1] == "00"){ 
					num = num.split(".")[0]; 
				} 
				// 月度合计
				$(this).text(num);
			}
		});
		// 年度合计汇总
		$("#year-total").text(month_total);
		// 保留小数点后两位
		month_total = (month_total/12).toFixed(2);
		// 如果都为0就不显示
		if(month_total.split(".")[1] == "00"){ 
			month_total = month_total.split(".")[0]; 
		}
		// 月度合计汇总
		$("#month-total").text(month_total);
	}

	// 占比
	function percentCal(){
		$("#percent-tr").find("td").each(function(){
			if($(this).attr("id").indexOf("total") < 0){
				var col = $(this).attr("name");
				var num = $("#year-tr").find("td[name='"+col+"']").text();
				var total = $("#year-total").text();

				if(parseFloat(total) == 0){
					var percent = "0%";
				}else{
					var percent = (parseFloat(num)/parseFloat(total))*100;
					percent = percent.toFixed(2);
					if(percent.split(".")[1] == "00"){ 
						percent = percent.split(".")[0]; 
					}
					percent += "%";
				}
				
				$(this).text(percent);
			}
		});
	}
</script>
