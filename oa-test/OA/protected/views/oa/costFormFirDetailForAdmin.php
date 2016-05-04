<?php
echo "<script type='text/javascript'>";
echo "console.log('costFormFirDetailForAdmin');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/user.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/highcharts.js" charset="utf-8"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />

<?php 
	// 检测是否公共部门的人
	$public_arr = array("总经理办公室", "商务部", "人事行政部", "项目管理部", "IT运维部");
	$public_id_arr = array();
	$p_index = 0;
	foreach($departments as $ddddrow){
		if(in_array($ddddrow['name'], $public_arr)){
			$public_id_arr[$p_index++] = $ddddrow['department_id'];
		}
	}
	$public_tag = false;
	$public_dname = empty($this->user) ? '' : $this->user->department->name;
	if(!empty($this->user) &&  in_array($public_dname, $public_arr)){
		$public_tag = true;
	}
?>

<style type="text/css">
	/*定义表格样式*/
	table{
		line-height: 35px;
		font-size: 10px;
	}
	.nw130{
		min-width: 130px;
	}
	.nw50{
		min-width: 50px;
	}
	.nw250{
		min-width: 250px;
	}
</style>


<div>
	<div style="min-width:1400px;">
        <!-- 曲线图-年度实际费用对比图 -->
		<div style="width:50%;min-width:650px;" class="fl pd20" id="spline-chart"></div>
        <!-- 饼图-实际费用占比图 -->
		<div style="width:50%;min-width:650px;" class="fl pd20" >
            <!-- 饼图 -->
			<div id="pie-chart"></div>
			<div class="right" style="position:relative;margin-top:-400px;margin-left:70px;">
				<div>
					<label>月份：</label>
					<select id="month-select" class="inline w130" onchange="showPie();"></select>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
    <!-- 一级费用表格 -->
	<div>
		<table class="table table-bordered m0 center">
			<caption class="p00 m0">
				<h4 class="m0 pd20 black bor-r-1-ddd bor-l-1-ddd bor-t-1-ddd center"><?php echo date('Y年m月',strtotime($month));?>公司费用汇总对比表</h4>
			</caption>
			<thead>
				<tr class="bg-fa">
					<th class="center w150">部门</th>
					<th class="center w100">月份</th>
					<th class="center w130">预算费用</th>
					<?php foreach($category as $ckey => $crow): ?>
					<th class="center w130"><?php echo $crow; ?></th>
					<?php endforeach; ?>
					<th class="center w130">实际费用</th>
					<th class="center w130">剩余费用</th>
					<th class="center w130">环比增长</th>
					<th class="center w130">环比增长(%)</th>
					<th class="center w300" style="max-width:300px;min-width:300px;">费用超支说明</th>
				</tr>
			</thead>
			<?php if(!$public_tag): ?>
			<tbody>
				<?php $department_th_tag = false; // 部门表头输出标记 ?>

				<?php for($i = 1; $i <= date('m', strtotime($month));$i++): // 输出小于当前月的 ?>

				<?php $month_budget_total = 0;  // 每月总费用; ?>

				<?php foreach($departments as $drow):  ?>

				<?php if(!empty($this->user) && $drow['department_id'] == $this->user->department->department_id): // 当前登录用的标记 ?>

				<tr class="data-tr">
					<td class="hidden <?php echo $i; ?>-id"></td>

					<?php if(!$department_th_tag): ?>
					<?php $department_th_tag = true; ?>
					<td class="bg-fa" name="department-name-td" rowspan="<?php echo (int)date('m', strtotime($month));?>">
						<?php echo $drow['name']; ?>
					</td><!-- 部门名称 -->
					<?php endif; ?>

					<th class="center">
						<?php echo $i.'月';$month_th = true;?>
					</th>

					<td class="<?php echo $i; ?>-budget" name="budget-td"><?php echo $budgets[$drow['department_id']]; $month_budget_total += $budgets[$drow['department_id']];?></td><!-- 预算费用 -->
					
					<?php foreach($category as $ckey => $crow): ?>
					<td class="<?php echo $i; ?>-<?php echo $ckey; ?>" name="type-cost-td"></td>
					<?php endforeach; ?>
					
					<td class="<?php echo $i; ?>-cost" name="total-cost-td"></td><!-- 实际费用 -->
					<td class="<?php echo $i; ?>-rest" name="total-rest-td"></td><!-- 剩余费用 -->
					<td class="<?php echo $i; ?>-grow" name="total-grow-td"></td><!-- 环比增长 -->
					<td class="<?php echo $i; ?>-grow-percent" name="total-grow-percent-td"></td><!-- 环比增长% -->
					<td class="<?php echo $i; ?>-description" name="description-td">
						<!-- <div>
							<a class="pointer" onclick="showDescription(this);">填写说明</a>
						</div> -->
					</td><!-- 费用超支说明 -->
				</tr>
				<?php endif;?>

				<?php endforeach; ?>

				<?php endfor; ?>
				<tr>
					<td colspan="2">合计</td>
					<td name="summary-td" class="budget-summary-td"></td>
					<?php foreach($category as $ckey => $crow): ?>
					<td class="<?php echo $ckey; ?>-summary-td" name="summary-td"></td>
					<?php endforeach; ?>
					<td name="summary-td" class="cost-summary-td"></td>
					<td name="summary-td" class="rest-summary-td"></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
			<?php else: ?>
			<tbody>
				<?php $department_th_tag = false; // 部门表头输出标记 ?>

				<?php for($i = 1; $i <= date('m', strtotime($month));$i++): // 输出小于当前月的 ?>
				<tr class="data-tr">
					<td class="hidden <?php echo $i; ?>-id">
						<?php 
							foreach($reports as $report_row){
								if(in_array($report_row['department_id'], $public_id_arr) && $report_row['month'] == $i){
									echo $report_row['id'];
									break;
								}
							}
						?>
					</td>

					<?php if(!$department_th_tag): ?>
					<?php $department_th_tag = true; ?>
					<td class="bg-fa" name="department-name-td" rowspan="<?php echo (int)date('m', strtotime($month));?>">公共部门</td><!-- 部门名称 -->
					<?php endif; ?>

					<th class="center">
						<?php echo $i.'月';$month_th = true;?>
					</th>

					<td class="<?php echo $i; ?>-budget" name="budget-td">
						<?php 
							$month_total = 0;
							foreach($departments as $de_row){
								if(in_array($de_row['name'], $public_arr)){
									$month_total += $budgets[$de_row['department_id']];
								}
							}
							echo $month_total;
						?>
					</td><!-- 预算费用 -->

					<?php $public_cost = 0; $public_description = ""; ?>
					<?php foreach($category as $ckey => $crow): ?>
					<td class="<?php echo $i; ?>-<?php echo $ckey; ?>" name="type-cost-td">
						<?php 
							if(!empty($reports)){
								$type_total = 0;
								foreach($reports as $report_row){
									if(in_array($report_row['department_id'], $public_id_arr) && $report_row['month'] == $i){
										$type_total += $report_row[$ckey];
										if($public_description == ""){
											$public_description = $report_row['description'];
										}
									}
								}
								$public_cost += $type_total;
								echo $type_total;
							}
						?>
					</td>
					<?php endforeach; ?>

					<td class="<?php echo $i; ?>-cost" name="total-cost-td"><?php echo $public_cost; ?></td><!-- 实际费用 -->
					<td class="<?php echo $i; ?>-rest" name="total-rest-td"><?php echo $month_total - $public_cost; ?></td><!-- 剩余费用 -->
					<td class="<?php echo $i; ?>-grow" name="total-grow-td"></td><!-- 环比增长 -->
					<td class="<?php echo $i; ?>-grow-percent" name="total-grow-percent-td"></td><!-- 环比增长% -->
					<td class="<?php echo $i; ?>-description" name="description-td">
						<?php if(!empty($public_description)): ?>
						<div><?php echo $public_description;?></div>
						<div><a class='pointer' onclick='showDescription(this);'>修改</a></div>
						<?php else: ?>
						<div><a class='pointer' onclick='showDescription(this);'>填写说明</a></div>
						<?php endif; ?>
					</td>
				</tr>
				<?php endfor; ?>
				<tr>
					<td colspan="2">合计</td>
					<td name="summary-td" class="budget-summary-td"></td>
					<?php foreach($category as $ckey => $crow): ?>
					<td class="<?php echo $ckey; ?>-summary-td" name="summary-td"></td>
					<?php endforeach; ?>
					<td name="summary-td" class="cost-summary-td"></td>
					<td name="summary-td" class="rest-summary-td"></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
			<?php endif; ?>
		</table>
	</div>
</div>

<!-- 填写说明模态框 -->
<div id="description-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">填写说明</h4>
    </div>

    <div class="modal-body">
        <textarea type="text" class="form-control inline" id="description-input" rows="3"></textarea>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="sendDescription()">提交</button>
    </div>
</div>

<!--js-->
<script type="text/javascript">
	// 报表数据数组初始化
	var report_arr = new Array();
	<?php 
		if(!empty($reports)){
			foreach($reports as $report_department => $report_row){
				if($report_row['department_id'] == $this->user->department->department_id){
					echo "report_arr.push({'id':'{$report_row['id']}', 'month':'{$report_row['month']}', 'department_id':'{$report_row['department_id']}', 'description':'{$report_row['description']}', 'create_time':'{$report_row['create_time']}', 'office':'{$report_row['office']}', 'welfare':'{$report_row['welfare']}', 'travel':'{$report_row['travel']}', 'entertain':'{$report_row['entertain']}', 'hydropower':'{$report_row['hydropower']}', 'intermediary':'{$report_row['intermediary']}', 'rental':'{$report_row['rental']}', 'test':'{$report_row['test']}', 'outsourcing':'{$report_row['outsourcing']}', 'property':'{$report_row['property']}', 'repair':'{$report_row['repair']}', 'other':'{$report_row['other']}',});";
				}
			}
		}
	?>

	// 分类数组初始化
	var category_arr = new Array();
	<?php 
		if(!empty($category)){
			foreach($category as $key => $row){
				echo "category_arr.push({'en_name':'{$key}', 'cn_name':'{$row}'});";
			}
		}
	?>

	// 填充数据
	function loadData(){
		// 将所有类别的费用都先填充0
		$("td[name='type-cost-td']").text("0");

		// 填充每个月每个类型的数据
		$.each(report_arr, function(key, value){
			var department_id = value['department_id'];
			var month = parseInt(value['month']);
			var description = value['description'];
			var id = value['id'];
			var data = value;

			// 遍历类别数组，填充类别名称
			$.each(category_arr, function(){
				var type = this['en_name'];
				$("td."+month+"-"+type).text(data[type]);
			});

			// 填充费用超支说明
			var str = "<div><a class='pointer' onclick='showDescription(this);'>填写说明</a></div>";
			$("td."+month+"-description").append(str);
			$("td."+month+"-description").find('div').before("<div>"+description+"</div>");
			if(description != ""){
				$("td."+month+"-description").find('div').find("a").text("修改");
			}

			// 填充id
			$("td."+month+"-id").text(id);
		});

		// 填充每个月的实际费用
		$("td[name='total-cost-td']").each(function(){
			var total_cost = 0; // 实际费用初始化

			// 将每个类型的费用加起来
			$(this).parent().find("td[name='type-cost-td']").each(function(){
				var num = parseFloat($(this).text());
				total_cost = accAdd(total_cost, num);
			});

			$(this).text(total_cost);
		});

		// 填充每个月的剩余费用
		$("td[name='total-rest-td']").each(function(){
			var cost = parseFloat($(this).prev().text());
			var budget = parseFloat($(this).parent().find("td[name='budget-td']").text());
			var rest = accMinus(budget, cost);
			$(this).text(rest);
		});

		// 环比增长
		$("td[name='total-grow-td']").each(function(){
			var month = $(this).attr("class").split("-grow")[0];
			if(month != 1){
				var current = null;
				var prev = null;
				$("td[name='total-cost-td']").each(function(){
					var td_month = $(this).attr("class").split("-cost")[0];
					if(parseInt(td_month) == month-1 && prev == null){
						prev = parseFloat($(this).text()); // 上月实际费用
					}
					if(parseInt(td_month) == month && current == null){
						current = parseFloat($(this).text());  // 当月实际费用
					}
				});
				var grow = accMinus(current, prev); // 环比增长
				$(this).text(grow);
			}
		});	

		// 环比增长%
		$("td[name='total-grow-percent-td']").each(function(){
			var month = $(this).attr("class").split("-grow-percent")[0];
			if(month != 1){
				var current = parseFloat($(this).prev().text());
				var prev = null;
				$("td[name='total-cost-td']").each(function(){
					var td_month = $(this).attr("class").split("-cost")[0];
					if(parseInt(td_month) == month-1 && prev == null){
						prev = parseFloat($(this).text()); // 上月实际费用
					}
				});
				var percent = "0%";
				if(prev != 0 || current != 0){
					percent = ((current/prev)*100);
					percent = percent.toFixed(2);
					percent += "%";
				}
				$(this).text(percent);
			}
		});

		// 计算合计
		$("td[name='summary-td']").each(function(){
			var type = $(this).attr("class").split("-summary-td")[0];
			if(type == "cost"){
				var total = 0;
				$("td[name='total-cost-td']").each(function(){
					var num = parseFloat($(this).text());
					total = accAdd(total,num);
				});
				$(this).text(total);
			}else if(type == "rest"){
				var total = 0;
				$("td[name='total-rest-td']").each(function(){
					var num = parseFloat($(this).text());
					total = accAdd(total,num);
				});
				$(this).text(total);
			}else if(type == "budget"){
				var total = 0;
				$("td[name='budget-td']").each(function(){
					var num = parseFloat($(this).text());
					total = accAdd(total,num);
				});
				$(this).text(total);
			}else{
				var total = 0;
				$("td[name='type-cost-td']").each(function(){
					var td_class = $(this).attr("class");
					if(td_class.indexOf(type) > -1){
						var num = parseFloat($(this).text());
						total = accAdd(total,num);
					}
				});
				$(this).text(total);
			}
			
		});
	}

	function loadPublicData(){
		// 环比增长
		$("td[name='total-grow-td']").each(function(){
			var month = $(this).attr("class").split("-grow")[0];
			if(month != 1){
				var current = null;
				var prev = null;
				$("td[name='total-cost-td']").each(function(){
					var td_month = $(this).attr("class").split("-cost")[0];
					if(parseInt(td_month) == month-1 && prev == null){
						prev = parseFloat($(this).text()); // 上月实际费用
					}
					if(parseInt(td_month) == month && current == null){
						current = parseFloat($(this).text());  // 当月实际费用
					}
				});
				var grow = accMinus(current, prev); // 环比增长
				$(this).text(grow);
			}
		});	

		// 环比增长%
		$("td[name='total-grow-percent-td']").each(function(){
			var month = $(this).attr("class").split("-grow-percent")[0];
			if(month != 1){
				var current = parseFloat($(this).prev().text());
				var prev = null;
				$("td[name='total-cost-td']").each(function(){
					var td_month = $(this).attr("class").split("-cost")[0];
					if(parseInt(td_month) == month-1 && prev == null){
						prev = parseFloat($(this).text()); // 上月实际费用
					}
				});
				var percent = "0%";
				if(prev != 0 || current == 0){
					percent = ((current/prev)*100);
					percent = percent.toFixed(2);
					percent += "%";
				}
				$(this).text(percent);
			}
		});

		// 计算合计
		$("td[name='summary-td']").each(function(){
			var type = $(this).attr("class").split("-summary-td")[0];
			if(type == "cost"){
				var total = 0;
				$("td[name='total-cost-td']").each(function(){
					var num = parseFloat($(this).text());
					total = accAdd(total,num);
				});
				$(this).text(total);
			}else if(type == "rest"){
				var total = 0;
				$("td[name='total-rest-td']").each(function(){
					var num = parseFloat($(this).text());
					total = accAdd(total,num);
				});
				$(this).text(total);
			}else if(type == "budget"){
				var total = 0;
				$("td[name='budget-td']").each(function(){
					var num = parseFloat($(this).text());
					total = accAdd(total,num);
				});
				$(this).text(total);
			}else{
				var total = 0;
				$("td[name='type-cost-td']").each(function(){
					var td_class = $(this).attr("class");
					if(td_class.indexOf(type) > -1){
						var num = parseFloat($(this).text());
						total = accAdd(total,num);
					}
				});
				$(this).text(total);
			}
			
		});
	}

	// 初始化月份和部门的下拉选择
	function initSelect(){
		var month_str = "";
		var current_month = "<?php echo empty($month) ? '1' : date('m', strtotime($month)); ?>";
		current_month = parseInt(current_month);
		for(var i = 1;i <= current_month; i++){
			month_str = "<option value='"+i+"'>"+i+"月</option>";
			$("#month-select").append(month_str);
		}
		$("#month-select").val(current_month);
	}

	function showPie(){
		var month = $("#month-select").val();
		$.each(pie_series, function(){
			if(this['month'] == month){
				var data = new Array();
				data.push({'name':'实际费用占比图','type':'pie','data':this['data']});
				initPie(data);
			}
		});
	}

	// 页面初始化
	$(document).ready(function(){
		<?php if(!$public_tag): ?>
		loadData();
		<?php else:?>
		loadPublicData();
		<?php endif; ?>

		initSelect();

		showSpline();

		initPieSeries();

		showPie();
	});	

	// 显示饼图
	var pie_series = new Array();
	function initPieSeries(){
		$("tr.data-tr").each(function(){
			var month = "";
			var department_id = "";
			var data = new Array();
			$(this).find("td[name='type-cost-td']").each(function(){
				var type = $(this).attr("class").split("-")[1];
				month = $(this).attr("class").split("-")[0];
				var num = parseFloat($(this).text());
				var total = parseFloat($(this).parent().find("td[name='total-cost-td']").text());
				var percent = 0;
				if(total != 0){
					percent = (num/total).toFixed(2);
				}
				percent = parseFloat(percent);
				var type_name = "";
				$.each(category_arr, function(){
					if(this['en_name'] == type){
						type_name = this['cn_name'];
						return false;
					}
				});
				data.push([type_name, percent]);
			});
			pie_series.push({'month':month, 'data':data});
		});
	}

	// 显示曲线图
	function showSpline(){
		// 曲线图横坐标
		var dtime_arr = new Array();
		var current_month = new Date().getMonth()+1;
		for(var i = 1; i<= current_month;i++){
			var month_str = i+"月";
			dtime_arr.push(month_str);
		}

		var series = new Array();
		$.each(category_arr, function(){
			var type = this['en_name'];
			var type_name = this['cn_name'];
			var data = new Array();
			$("td[name='type-cost-td']").each(function(){
				var td_type = $(this).attr("class").split("-")[1];
				if(td_type == type){
					var num = parseFloat($(this).text());
					data.push(num);
				}
			});
			series.push({'name':type_name, 'data':data});
		});


		initCharts(dtime_arr, series);
	}

	// 初始化图表
    function initCharts(dtime_arr, series){
        $('#spline-chart').highcharts({
            // 表格类型
            chart: {
                type: 'spline',
                backgroundColor:'transparent',
            },
            // 表格标题
            title: {
                text: "年度实际费用对比图"
            },
            // x轴
            xAxis: {
                categories: dtime_arr
            },
            // y轴
            yAxis: {
                title: {
                    text: '元'
                },
                labels: {
                    formatter: function() {
                        return this.value +'(元)';
                    }
                }
            },
            // 工具栏
            tooltip: {
                crosshairs: true,
                shared: true
            },
            // 点的修饰
            plotOptions: {
                spline: {
                    marker: {
                        radius: 4,
                        lineColor: '#666666',
                        lineWidth: 1
                    }
                }
            },
            // 数据
            series: series
        });
    }

    // 初始化图表
    function initPie(series){
        $('#pie-chart').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: "实际费用占比图"
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    },
                    showInLegend: true
                }
            },
            series: series
        });
    }

	// 显示说明模态框
	var action_id = "";
	function showDescription(obj){
		action_id = $(obj).parent().parent().parent().children().first().text();
		action_id = action_id.replace(/\s*/g, "");
		var content = $(obj).parent().prev().text();
		if(content){
			$("#description-input").val(content);
		}else{
			$("#description-input").val("");
		}

		var ySet = (window.innerHeight - $("#description-div").height())/3;
        var xSet = (window.innerWidth - $("#description-div").width())/2;
        $("#description-div").css("top",ySet);
        $("#description-div").css("left",xSet);
        $('#description-div').modal({show:true});
	}

	// 精确加法
	function accAdd(arg1,arg2){  
		var r1,r2,m;  
		try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}  
		try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}  
		m=Math.pow(10,Math.max(r1,r2))  
		return (arg1*m+arg2*m)/m  
	}

	// 精确减法
	function accMinus(arg1,arg2){  
		var r1,r2,m;  
		try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}  
		try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}  
		m=Math.pow(10,Math.max(r1,r2))  
		return (arg1*m-arg2*m)/m  
	}

	// 发送说明
	function sendDescription(){
		if(action_id == ""){
			showHint("提示信息","没有数据，不能填写说明");
		}else{
			var description = $("#description-input").val();
			$.ajax({
		        type:'post',
		        dataType:'json',
		        url:'/ajax/editExpenseReport',
		        data:{'id':action_id, 'description':description},
		        success:function(result){
		          if(result.code == 0){
		          	showHint("提示信息","超支说明提交成功");
		            setTimeout(function(){location.reload();},1200);
		          }else if(result.code == -1){
		            showHint("提示信息","超支说明提交失败！");
		          }else if(result.code == -2){
		            showHint("提示信息","参数错误！");
		          }else if(result.code == -3){
		            showHint("提示信息","找不到该记录！");
		          }else{
		          	showHint("提示信息","你没有权限执行此操作！");
		          }
		        }
		    });
		}
	}
</script>