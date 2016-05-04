<?php
echo "<script type='text/javascript'>";
echo "console.log(costFormFirDetail);";
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

<!-- 主界面 -->
<div>
	<div style="min-width:1400px;">
		<!-- 曲线图-年度实际费用对比图 -->
		<div style="width:50%;min-width:650px;" class="fl pd20" id="spline-chart"></div>
		<!-- 饼图-实际费用占比图 -->
		<div style="width:50%;min-width:650px;" class="fl pd20" >
			<div id="pie-chart"></div><!-- 饼图 -->
			<div class="right" style="position:relative;margin-top:-400px;margin-left:70px;">
				<div>
					<label>月份：</label>
					<select id="month-select" class="inline w130" onchange="showPie();"></select>
				</div>
				<div class="mt10">
					<label>部门：</label>
					<select id="department-select" class="inline w130" onchange="showPie();"></select>
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
			<tbody>
				<?php for($i = 1; $i <= date('m', strtotime($month));$i++): ?>
				<tr class="bg-fa">
					<th class="center w50">月份</th>
					<th class="center w150">部门</th>
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
				<?php $month_budget_total = 0;  // 每月总费用; ?>

				<?php $month_th = false; foreach($departments as $drow): ?>
				<tr class="data-tr">
					<td class="hidden <?php echo $i; ?>-id-<?php echo $drow['department_id']?>"></td>
					<?php if(!$month_th): ?>
					<th class="center bg-fa" rowspan="<?php echo count($departments)-3; ?>">
						<?php echo $i.'<br>月';$month_th = true;?>
					</th>
					<?php endif; ?>
					<!-- 部门名称 -->
					<td name="department-name-td">
						<?php echo $drow['name']; ?>
					</td>
					<!-- 预算费用 -->
					<td class="<?php echo $i; ?>-budget-<?php echo $drow['department_id'];?>">
						<?php 
							if(array_key_exists($drow['department_id'], $budgets)) {
								echo $budgets[$drow['department_id']]; 
								$month_budget_total += $budgets[$drow['department_id']];
							}
							else {
								echo 0;
							}
						?>
					</td>
					<?php foreach($category as $ckey => $crow): ?>
					<td class="<?php echo $i; ?>-<?php echo $ckey; ?>-<?php echo $drow['department_id']?>" name="type-cost-td"></td>
					<?php endforeach; ?>
					<!-- 实际费用 -->
					<td class="<?php echo $i; ?>-cost-<?php echo $drow['department_id']?>" name="total-cost-td"></td>
					<!-- 剩余费用 -->
					<td class="<?php echo $i; ?>-rest-<?php echo $drow['department_id']?>" name="total-rest-td"></td>
					<!-- 环比增长 -->
					<td class="<?php echo $i; ?>-grow-<?php echo $drow['department_id']?>" name="total-grow-td"></td>
					<!-- 环比增长% -->
					<td class="<?php echo $i; ?>-grow-percent-<?php echo $drow['department_id']?>" name="total-grow-percent-td"></td>
					<!-- 费用超支说明 -->
					<td class="<?php echo $i; ?>-description-<?php echo $drow['department_id']?>" name="description-td">
						<!-- <div>
							<a class="pointer" onclick="showDescription(this);">填写说明</a>
						</div> -->
					</td>
				</tr>
				<?php endforeach; ?>
				<tr class="<?php echo $i; ?>-summary-tr" name="summary-tr">
					<!-- 部门名称 -->
					<td>合计</td>
					<!-- 预算费用 -->
					<td><?php echo $month_budget_total; ?></td>
					<?php foreach($category as $ckey => $crow): ?>
					<td class="<?php echo $i; ?>-<?php echo $ckey; ?>-summary" name="summary-td"></td>
					<?php endforeach; ?>
					<!-- 实际费用 -->
					<td class="<?php echo $i; ?>-cost-summary" name="summary-td"></td>
					<!-- 剩余费用 -->
					<td class="<?php echo $i; ?>-rest-summary" name="summary-td"></td>
					<!-- 环比增长 -->
					<td class="<?php echo $i; ?>-grow-summary" name="summary-td"></td>
					<!-- 环比增长% -->
					<td class="<?php echo $i; ?>-growpercent-summary" name="summary-td"></td>
					<!-- 费用超支说明 -->
					<td></td>
				</tr>
				<tr>
					<td colspan="20"></td>
				</tr>
				<?php endfor; ?>
			</tbody>
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
				echo "report_arr.push({'id':'{$report_row['id']}', 'month':'{$report_row['month']}', 'department_id':'{$report_row['department_id']}', 'description':'{$report_row['description']}', 'create_time':'{$report_row['create_time']}', 'office':'{$report_row['office']}', 'welfare':'{$report_row['welfare']}', 'travel':'{$report_row['travel']}', 'entertain':'{$report_row['entertain']}', 'hydropower':'{$report_row['hydropower']}', 'intermediary':'{$report_row['intermediary']}', 'rental':'{$report_row['rental']}', 'test':'{$report_row['test']}', 'outsourcing':'{$report_row['outsourcing']}', 'property':'{$report_row['property']}', 'repair':'{$report_row['repair']}', 'other':'{$report_row['other']}',});";
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
				$("td."+month+"-"+type+"-"+department_id).text(data[type]);
			});

			// 填充费用超支说明
			var str = "<div><a class='pointer' onclick='showDescription(this);''>填写说明</a></div>";
			$("td."+month+"-description-"+department_id).append(str);
			$("td."+month+"-description-"+department_id).find('div').before("<div>"+description+"</div>");
			if(description != ""){
				$("td."+month+"-description-"+department_id).find('div').find("a").text("修改");
			}

			// 填充id
			$("td."+month+"-id-"+department_id).text(id);
		});

		// 填充每个月的实际费用
		$("td[name='total-cost-td']").each(function(){
			var total_cost = 0; // 实际费用初始化

			// 每个月的实际费用
			var month = $(this).attr("class").split("-cost-")[0];
			var department_id = $(this).attr("class").split("-cost-")[1];
			$("td[name='type-cost-td']").each(function(){
				var td_month = $(this).attr("class").split("-")[0];
				var td_department_id = $(this).attr("class").split("-")[2];
				if(td_month == month && td_department_id == department_id){
					total_cost = accAdd(total_cost, parseFloat($(this).text())); // 实际费用累加
				}
			});
			$(this).text(total_cost);
		});

		// 填充每个月的剩余费用
		$("td[name='total-rest-td']").each(function(){
			var total_rest = 0;
			var month = $(this).attr("class").split("-rest-")[0];
			var department_id = $(this).attr("class").split("-rest-")[1];
			var budget = parseFloat($("td."+month+"-budget-"+department_id).text()); // 预算
			var cost = parseFloat($("td."+month+"-cost-"+department_id).text()); // 费用
			total_rest = accMinus(budget, cost); // 剩余费用
			$(this).text(total_rest);
		});

		// 环比增长
		$("td[name='total-grow-td']").each(function(){
			var total_grow = 0;
			var current_month = "<?php echo empty($month) ? date('m') :date('m',strtotime($month.'-01'));?>";
			var month = $(this).attr("class").split("-grow-")[0];
			var department_id = $(this).attr("class").split("-grow-")[1];
			// 判断是否为1月
			if(month != 1){
				var current = parseFloat($("td."+month+"-cost-"+department_id).text()); // 当月实际费用
				var prev = parseFloat($("td."+(month-1)+"-cost-"+department_id).text()); // 上月实际费用
				var grow = accMinus(current, prev); // 环比增长
				$(this).text(grow);
			}else{
				$(this).text("");
			}
		});

		// 环比增长%
		$("td[name='total-grow-percent-td']").each(function(){
			var total_grow = 0;
			var current_month = "<?php echo empty($month) ? date('m') :date('m',strtotime($month.'-01'));?>";
			var month = $(this).attr("class").split("-grow-percent-")[0];
			var department_id = $(this).attr("class").split("-grow-percent-")[1];
			// 判断是否为1月
			if(month != 1){
				var current = parseFloat($("td."+month+"-cost-"+department_id).text());  // 当月实际费用
				var prev = parseFloat($("td."+(month-1)+"-cost-"+department_id).text());  // 上月实际费用
				var grow = accMinus(current, prev);  // 环比增长
				var grow_percent = "0%";
				if(prev != 0){
					var percent = (grow/prev)*100;
					grow_percent = percent.toFixed(2)+"%";  // 环比增长%
				}
				$(this).text(grow_percent);
			}else{
				$(this).text("");
			}
		});

		// 计算合计
		$("td[name='summary-td']").each(function(){
			var month = $(this).attr("class").split("-summary")[0].split("-")[0];
			var type = $(this).attr("class").split("-summary")[0].split("-")[1];
			switch(type){
				// 计算实际费用
				case "cost":{
					var cost_total = 0;
					$("td[name='total-cost-td']").each(function(){
						var td_month = $(this).attr("class").split("-")[0];
						if(td_month == month){
							cost_total = accAdd(cost_total, parseFloat($(this).text()));
						}
					});
					$(this).text(cost_total);
					break;
				}
				// 计算剩余费用
				case "rest":{
					var rest_total = 0;
					$("td[name='total-rest-td']").each(function(){
						var td_month = $(this).attr("class").split("-")[0];
						if(td_month == month){
							rest_total = accAdd(rest_total, parseFloat($(this).text()));
						}
					});
					$(this).text(rest_total);
					break;
				}
				// 计算环比增长
				case "grow":{
					var grow_total = 0;
					$("td[name='total-grow-td']").each(function(){
						var td_month = $(this).attr("class").split("-")[0];
						if(parseInt(td_month) >= 2){
							if(td_month == month){
								grow_total = accAdd(grow_total, parseFloat($(this).text()));
							}
						}else{
							grow_total = "";
						}
					});
					$(this).text(grow_total);
					break;
				}
				// 计算环比增长%
				case "growpercent":{
					if(parseInt(month) >= 2){
						var grow_percent = "0%";
						var prev = parseFloat($("td."+(parseInt(month)-1)+"-cost-summary").text());
						var current = parseFloat($("td."+month+"-grow-summary").text());
						if(prev != 0){
							var percent = (current/prev)*100;
							grow_percent = percent.toFixed(2)+"%";
						}
						$(this).text(grow_percent);
					}
					break;
				}
				// 计算各类费用
				default :{
					var type_total = 0;
					$("td[name='type-cost-td']").each(function(){
						var td_month = $(this).attr("class").split("-")[0];
						var td_type = $(this).attr("class").split("-")[1];
						if(td_month == month && td_type == type){
							type_total = accAdd(type_total, parseFloat($(this).text()));
						}
					});
					$(this).text(type_total);
					break;
				}
			}
		});
	
		// 合并公共部门
		var department_data = new Array();
		var department_description_arr = new Array();
		var department_description = "";
		var department_description_id = "";
		$("td[name='department-name-td']").each(function(){
			var department_name = $(this).text();
			department_name = department_name.replace(/\s*/g, "");

			// 判断是否为公共部门
			if(department_name == "总经理办公室" || department_name == "商务部" || department_name == "人事行政部" || department_name == "项目管理部" || department_name == "IT运维部"){
				$(this).text("公共部门");
				var obj = $(this).next();
				var month = $(obj).attr("class").split("-budget-")[0];
				var department_detail = new Array();
				// 公共部门预算费用后面15个记录都添加进去
				for(var i =0; i < 16;i++){
					department_detail.push($(obj).text());
					obj = $(obj).next();
				}
				// 取有id的公共部门的超支说明。给department_description赋值
				if(department_description == "" && $(obj).next().find("div").first().text()!=""){
					department_description = $(obj).next().find("div").first().text();
				}
				// 同时把这个id记录起来
				if(department_description_id == "" && $(obj).parent().children().first().text() != ""){
					department_description_id = $(obj).parent().children().first().text();
				}
				if(department_description_arr.length > 0){
					// 判断是否已经添加过该月的超支说明
					var de_find_tag = false;
					$.each(department_description_arr, function(){
						if(this['month'] == month){
							de_find_tag = true;
						}
					});

					// 如果没找到，就添加到部门说明数组中
					if(!de_find_tag && department_description_id != ""){
						department_description_arr.push({'month':month, 'department_description':department_description, 'department_description_id':department_description_id});
					}
				}else if(department_description_id != ""){
					department_description_arr.push({'month':month, 'department_description':department_description, 'department_description_id':department_description_id});
				}
				department_detail.push(month); // 把月份加到部门数据的末尾
				department_data.push(department_detail); // 加载到详细数据中
			}
		});
		var first_tag = false;
		var last_month = "";
		$("td[name='department-name-td']").each(function(){
			var department_name = $(this).text();
			department_name = department_name.replace(/\s*/g, "");
			var month = $(this).next().attr("class").split("-budget-")[0];
			if(department_name == "公共部门"){
				if(last_month == ""){
					last_month = month;
				}else if(last_month != month){
					last_month = month;
					first_tag = false;
				}
				
				if(!first_tag){  // 如果不是第一条
					first_tag = true;
					var obj = $(this).next();
					for(var i = 0; i < 16;i++){
						var data = 0;
						$.each(department_data, function(){
							if(this[16] == month){
								data = accAdd(data, this[i]);
							}
						});
						if(month == 1 && i == 15){
							$(obj).text("");
						}else{
							$(obj).text(data);
						}
						
						obj = $(obj).next();
					}

					$.each(department_data, function(){
						if(this['month'] == month){
							if(this['department_description_id'] != ""){
								$(obj).parent().children().first().text(this['department_description_id']);
								if(this['department_description'] != ""){
									$(obj).next().html("<div>"+this['department_description']+"</div><div><a class='pointer' onclick='showDescription(this);''>修改</a></div>");
								}else{
									$(obj).next().html("<div></div><div><a class='pointer' onclick='showDescription(this);''>填写说明</a></div>");
								}
							}
						}
					});
					
				}else{
					$(this).parent().remove();
				}
			}
		});

		// 合并以后算环比
		$("td[name='department-name-td']").each(function(){
			var department_name = $(this).text();
			department_name = department_name.replace(/\s*/g, "");
			if(department_name == "公共部门"){
				var obj = $(this).next();
				var month = $(obj).attr("class").split("-budget-")[0];
				if(month > 1){
					var prev = "";
					$("td[name='total-cost-td']").each(function(){
						var td_month = $(this).attr("class").split("-cost-")[0];
						if(parseInt(td_month) == parseInt(month)-1 && prev == ""){
							prev = parseFloat($(this).text());
						}
					});
					var current = parseFloat($(this).parent().find("td[name='total-grow-td']").text());

					var percent = "";
					if(prev == 0){
						percent = "0%";
					}else{
						percent = (current/prev)*100;
						percent = percent.toFixed(2);
						percent = percent+"%";
					}
					$(this).parent().find("td[name='total-grow-percent-td']").text(percent);
				}
			}
		});
	}

	// 分类数组初始化
	var category_arr = new Array();
	<?php 
		if(!empty($category)){
			foreach($category as $key => $row){
				echo "category_arr.push({'en_name':'{$key}', 'cn_name':'{$row}'});";
			}
		}
	?>

	// 页面初始化
	$(document).ready(function(){
		loadData();

		initSelect();

		showSpline();

		initPieSeries();

		showPie();
	});

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
			$("td[name='summary-td']").each(function(){
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
				department_id = $(this).attr("class").split("-")[2];
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
			pie_series.push({'month':month, 'department_id':department_id,'data':data});
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
		var department_str = "";
		var last_department_id = "";
		$("td[name='department-name-td']").each(function(){
			var department_id = $(this).next().attr("class").split("-budget-")[1];
			var department_name = $(this).text();
			department_name = department_name.replace(/\s*/g, "");
			department_str = "<option value='"+department_id+"'>"+department_name+"</option>";
			var find_tag = false;
			$("#department-select").children().each(function(){
				if($(this).val() == department_id){
					find_tag = true; 
					return false;
				}
			});
			if(!find_tag){
				$("#department-select").append(department_str);
			}
		});

		$("#month-select").val(current_month);
	}

	function showPie(){
		var month = $("#month-select").val();
		var department_id = $("#department-select").val();
		$.each(pie_series, function(){
			if(this['month'] == month && this['department_id'] == department_id){
				var data = new Array();
				data.push({'name':'实际费用占比图','type':'pie','data':this['data']});
				initPie(data);
			}
		});
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

	// 显示说明模态框
	var action_id = "";
	function showDescription(obj){
		action_id = $(obj).parent().parent().parent().children().first().text();
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
</script>