<?php
echo "<script type='text/javascript'>";
echo "console.log('activityRecord');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
    <div class="pd20">
	<h4 class="pd10 m0 b33 bor-b-1-ddd">参与统计</h4>
        <!-- 活动统计类型标签 -->
		<ul class="nav nav-tabs">
			<li role="presentation" class="active"><a class="pointer" onclick="showActivity(this);">活动举办统计</a></li>
			<li role="presentation"><a class="pointer" onclick="showPeople(this);">人员参与统计</a></li>
		</ul>
        <!-- 活动举办统计 -->
		<div id="activity-div">
            <!-- 搜索栏 -->
			<div class="pd20 bor-l-1-ddd bor-r-1-ddd">
				<label>活动：</label>
				<select class="form-control inline w150" id="activity-select">
					<option value="all">所有活动</option>
					<?php if(!empty($teams)): ?>
					<?php foreach($teams as $trow): ?>
					<option value="<?php echo $trow['id'];?>"><?php echo $trow['name']; ?></option>
					<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<label class="ml20">月份</label>
				<input class="form-control inline w150" id="month-input" value="<?php echo empty($month) ? '' : $month; ?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)" placeholder="请输入月份">
				<button class="btn btn-success w80 ml20 mt-5" onclick="search();">查询</button>
			</div>
            <!-- 活动举办统计表格 -->
			<?php if(!empty($activitys)): ?>
			<table class="table table-bordered m0 center">
				<tbody>
					<tr class="bg-fa">
						<th class="center">活动</th>
						<th class="center">举办时间</th>
						<th class="center">实际参与人数</th>
						<th class="center">实际参与人员</th>
						<th class="center">费用(元)</th>
					</tr>
					<?php foreach($activitys as $arow): ?>
					<tr>
						<td><?php echo $arow->team->name; ?></td>
						<td><?php echo $arow['activity_time']; ?></td>
                        <td><?php   
                            if(empty($arow->joins)){
                                echo '0';
                            }else{
                                $__count = 0; //计算实际参与人数
                                foreach($arow->joins as $__row){
                                    if($__row['status'] == "join") $__count ++;
                                }
                                echo $__count;
                            } ?>
                        </td>
						<td>
							<?php
								$join_index = 0;
								if(!empty($arow->joins)){
									foreach($arow->joins as $ajrow){if($ajrow['status'] == "join"){$join_index++;}}
									$index = 0;
									foreach($arow->joins as $ajrow){
										if($ajrow['status'] == "join"){
											$index++;
											if($index == $join_index){
												echo $ajrow->user->cn_name;
											}else{
												echo $ajrow->user->cn_name."、";
											}
										}
									}
								}
							?>
						</td>
						<td><?php echo $arow['outlay']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php else: ?>
			<h4 class="pd20 m0 center bor-1-ddd">没有活动记录</h4>
			<?php endif; ?>
		</div>
        <!-- 人员参与统计 -->
		<div id="people-div" class="hidden overflow-a" style="max-height:700px;">
            <!-- 人员参与统计表格 -->
			<?php if(!empty($users)): ?>
			<table class="table table-bordered m0 center">
				<tbody>
					<tr class="bg-fa">
						<th class="center">部门</th>
						<th class="center">姓名</th>
						<?php foreach($teams as $t_row): ?>
						<th class="center"><?php echo $t_row['name'];?></th>
						<?php endforeach; ?>
						<th class="center">合计</th>
					</tr>
					<?php foreach($users as $u_row): ?>
					<tr id="user-<?php echo $u_row->user_id; ?>" class="join-tr">
						<th class="center"><?php echo $u_row->department->name; ?></th>
						<td><?php echo $u_row->cn_name; ?></td>
						<?php 
							foreach($teams as $ttrow){
								echo "<td class='{$ttrow['id']}-td'></td>";
							}
						?>
						<td></td>
					</tr>
					<?php endforeach; ?>
					<tr id="total-tr">
						<th class="center" colspan="2">合计</th>
						<?php 
							foreach($teams as $ttrow){
								echo "<td class='{$ttrow['id']}-total-td'></td>";
							}
						?>
						<td id="total-summary-td"></td>
					</tr>
				</tbody>
			</table>

			<?php else: ?>
			<h4 class="m0 pd20 bor-1-ddd">没有活动数据</h4>
			<?php endif; ?>
		</div>
	</div>
</div>

<!--js-->
<script type="text/javascript">
	// 初始化参与人员
	var join_arr = new Array();
	<?php 
		if(!empty($joins)){
			foreach($joins as $j_key => $j_row){
				foreach($j_row as $t_key => $join_detail){
					echo "join_arr.push({'user_id':'{$j_key}', 'team_id':'{$t_key}', 'times':'{$join_detail}'});";
				}
			}
		}
	?>

	// 显示活动举办统计
	function showActivity(obj){
		$(obj).parent().addClass("active");
		$(obj).parent().next().removeClass("active");
		$("#activity-div").removeClass("hidden");
		$("#people-div").addClass("hidden");
	}

	// 显示人员参与统计
	function showPeople(obj){
		$(obj).parent().addClass("active");
		$(obj).parent().prev().removeClass("active");
		$("#activity-div").addClass("hidden");
		$("#people-div").removeClass("hidden");
	}

	// 查询
	function search(){
		var activity = $("#activity-select").val();
		var month = $("#month-input").val();
		var month_pattern = /^\d{4}-\d{2}$/;
		if(month != "" && !month_pattern.exec(month)){
			showHint("提示信息","月份输入格式不正确");
			$("#month-input").focus();
		}else{
			if(month != ""){
				var href_str = "/oa/activityRecord/team_id/"+activity+"/month/"+month;
			}else{
				var href_str = "/oa/activityRecord/team_id/"+activity;
			}
			location.href = href_str;
		}
	}

	// 页面初始化
	$(document).ready(function(){
		$("#activity-select").val("<?php echo empty($team_id) ? 'all' : $team_id; ?>");

		// 加载人员参加统计
		loadJoin();

		// 表头合并
		thSpan();
	});

	// 合并表头
	function thSpan(){
		var join_th_arr = new Array();
		var last_th = ""; // 当前表头
		var row_num = 0; // 行数
		$("tr.join-tr").each(function(){
			row_num++;
			var th_str = $(this).children().first().text(); // 表头名称
			if(last_th == ""){
				last_th = th_str;
				$(this).children().first().attr("name", th_str);
			}else if(th_str != last_th){
				$("tr.join-tr").find("th[name='"+last_th+"']").attr("rowspan",row_num-1);
				row_num = 1;
				last_th = th_str;
				$(this).children().first().attr("name", th_str);
			}else{
				$(this).children().first().remove();
			}
			$("tr.join-tr").find("th[name='"+last_th+"']").attr("rowspan",row_num);
		});
	}

	// 加载人员参加统计
	function loadJoin(){
		$.each(join_arr, function(){
			var user_id = this['user_id'];
			var team_id = this['team_id'];
			var times = this['times'];
			$("#user-"+user_id).find("td."+team_id+"-td").text(times);
		});

		totalCal(); // 计算总数

		summaryCal();  // 汇总合计
	}

	// 计算每个人的合计
	function totalCal(){
		$("tr.join-tr").each(function(){
			var total = 0;
			$(this).find("td").each(function(){
				if($(this).attr("class") && $(this).attr("class").indexOf("-td") >= 0 && $(this).text() != ""){
					total = accAdd(total, parseInt($(this).text()));
				}
			});
			$(this).children().last().text(total);
		});
	}

	// 汇总合计
	function summaryCal(){
		var total_summary = 0;
		$("#total-tr").find("td").each(function(){
			if(!$(this).attr("id")){
				var team_id = $(this).attr("class").split("-total-td")[0];
				var total = 0;
				$("td."+team_id+"-td").each(function(){
					if($(this).text()){
						total = accAdd(total, parseInt($(this).text()));
					}
				});
				$(this).text(total);
				total_summary = accAdd(total_summary, total);
			}else{
				$("#total-summary-td").text(total_summary);
			}
		});
	}

	// 精确加法
	function accAdd(arg1,arg2){  
		var r1,r2,m;  
		try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}  
		try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}  
		m=Math.pow(10,Math.max(r1,r2))  
		return (arg1*m+arg2*m)/m  
	}
</script>
