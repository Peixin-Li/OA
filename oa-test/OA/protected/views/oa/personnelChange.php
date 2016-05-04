<?php
echo "<script type='text/javascript'>";
echo "console.log('personnelChange');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/highcharts.js" charset="utf-8"></script>

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd">人事变动统计</h4>
	<!-- 查询条件 -->
	<div class="pd10 bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd">
		<label>年份：</label>
		<select class="form-control w100 inline" id="year-select">
			<?php for($i = 2012; $i <= 2050; $i++): ?>
			<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
			<?php endfor; ?>
		</select>
		<button class="btn btn-success w80 ml10 mt-5" onclick="search();">查询</button>
		<button class="btn btn-primary w100 ml10 mt-5" onclick="showChart();">查看图表</button>
	</div>
	<!-- 人事变动统计表 -->
	<?php if(!empty($users)): ?>
	<table class="table table-bordered" id="change-table">
		<tbody>
			<tr class="bg-fa">
				<th class="w80 center">月份</th>
				<th class="w200">部门</th>
				<th class="w300">入职</th>
				<th class="w300">离职</th>
			</tr>
			<?php $count = 0;?>
			<?php for($i = 1;$i <= 12; $i++): ?>
			<?php foreach($users as $row): ?>
			<?php if($row['status'] == "work" && date('Y',strtotime($row['entry_day'])) == $year && (int)date('m',strtotime($row['entry_day'])) == $i): ?>
			<?php $count++; ?>
			<tr name="tr-<?php echo $i; ?>">
				<td name="month" class="month-<?php echo $i; ?> center"><strong><?php echo $i.'月'; ?></strong></td>
				<td name="department" class="department-<?php echo $i;?>"><?php echo $row->department->name; ?></td>
				<td name="entry" class="entry-<?php echo $i;?>"><div><?php echo $row['title'].'-'.$row['cn_name']; ?></div></td>
				<?php if(!empty($row->quit) && date('Y',strtotime($row->quit['quit_date'])) == $year && (int)date('m',strtotime($row->quit['quit_date'])) == $i):?>
				<td name="quit"><div><?php echo $row['title'].'-'.$row['cn_name']; ?></div></td>
				<?php else: ?>
				<td name="quit"></td>
				<?php endif; ?>
			</tr>
			<?php elseif($row['status'] == 'quit' && date('Y',strtotime($row->quit['quit_date'])) == $year && (int)date('m',strtotime($row->quit['quit_date'])) == $i): ?>
			<?php $count++; ?>
			<tr name="tr-<?php echo $i; ?>">
				<td name="month" class="month-<?php echo $i; ?> center"><strong><?php echo $i.'月'; ?></strong></td>
				<td name="department" class="department-<?php echo $i;?>"><?php echo $row->department->name; ?></td>
				<td name="entry"></td>
				<td name="quit" class="quit-<?php echo $i;?>"><div><?php echo $row['title'].'-'.$row['cn_name']; ?></div></td>
			</tr>
			<?php endif; ?>
			<?php endforeach; ?>
			<?php endfor; ?>
			<td colspan="4" class="center <?php if($count != 0) echo "hidden"; ?>" id="empty-data"><h4 class="pd20">没有数据</h4></td>
		</tbody>
	</table>
	<?php endif; ?>
</div>

<!-- 确认活动结果模态框 -->
<div id="chart-div" class="modal fade in hint bor-rad-5" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title"><?php echo empty($year) ? date('Y') : $year;?>年人事变动情况</h4>
    </div>
    <div class="modal-body">
       	<div id="container" style="width:1300px;height:auto;"></div>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
	$(document).ready(function(){
		if($("#empty-data").hasClass("hidden")){
			totalCal();
			rowSpan();
			prepareData();
		}

		<?php if(!empty($year)): ?>
		$("#year-select").val("<?php echo $year; ?>");
		<?php endif; ?>
	});

	// 计算合计
	function totalCal(){
		var total_entry_num = 0;
		var total_quit_num = 0;
		for(var i = 1; i <= 12;i++){
			var entry_num = 0;
			var quit_num = 0;
			$("tr[name='tr-"+i+"']").each(function(){
				if($(this).find("td[name='entry']").html() != "") entry_num++;
				if($(this).find("td[name='quit']").html() != "") quit_num++;
			});
			var tr_content = "<tr style='background:#FFFF99;'><td>合计</td><td name='entry-total-"+i+"'>"+((entry_num == 0) ? '' : ("共 "+entry_num+" 人")) +"</td><td name='quit-total-"+i+"'>"+((quit_num == 0) ? '' : ("共 "+quit_num+" 人")) +"</td></tr>";
			$("tr[name='tr-"+i+"']").last().after(tr_content);
			total_entry_num += entry_num;
			total_quit_num += quit_num;
		}
		var total_content = "<tr class='bg-66 white'><td colspan='2' class='center'>总计</td><td>"+((total_entry_num == 0) ? '' : ("共 "+total_entry_num+" 人")) +"</td><td>"+((total_quit_num == 0) ? '' : ("共 "+total_quit_num+" 人")) +"</td></tr>";
		$("#change-table").find("tbody").append(total_content);
	}

	// 合并单元格
	function rowSpan(){
		for(var i = 1; i <= 12;i++){
			var set_tag_month = false;
			var th_obj_month = null;
			var th_num_month = 0;
			$("td[name='month']").each(function(){
				var month = parseInt($(this).attr("class").split("-")[1]);
				if(month == i){
					if(set_tag_month){
						$(this).remove();
					}else{
						th_obj_month = this;
						set_tag_month = true;
					}
					th_num_month ++;
				}
			});
			$(th_obj_month).attr("rowspan", th_num_month+1);

			var th_obj_department = null;
			var th_num_department = 0;
			var last_department = "";
			$("td[name='department']").each(function(){
				var month = parseInt($(this).attr("class").split("-")[1]);
				if(month == i){
					var department_name = $(this).text();
					if(last_department == ""){
						th_num_department ++;
						th_obj_department = this;
						last_department = department_name
					}else if(last_department != department_name){
						$(th_obj_department).attr("rowspan", th_num_department);
						$(th_obj_department).next().attr("rowspan", th_num_department);
						$(th_obj_department).next().next().attr("rowspan", th_num_department);
						th_num_department = 1;
						th_obj_department = this;
						last_department = department_name;
					}else{
						var content_entry = $(this).next().html();
						var content_quit = $(this).next().next().html();
						if(content_entry) $(th_obj_department).next().append(content_entry);
						if(content_quit) $(th_obj_department).next().next().append(content_quit);
						$(this).next().next().remove();
						$(this).next().remove();
						$(this).remove();
						th_num_department++;
					}
				}
			});
			$(th_obj_department).attr("rowspan", th_num_department);
			$(th_obj_department).next().attr("rowspan", th_num_department);
			$(th_obj_department).next().next().attr("rowspan", th_num_department);
		}
	}

	// 查询
	function search(){
		var year = $("#year-select").val();
		location.href = "/oa/personnelChange/year/"+year;
	}

	// 显示图表
	function showChart(){
		var ySet = (window.innerHeight - $("#chart-div").height())/2;
	    var xSet = (window.innerWidth - $("#chart-div").width())/2;
	    $("#chart-div").css("top",ySet);
	    $("#chart-div").css("left",xSet);
	    $("#chart-div").modal({show:true});
	}

	// 准备图表数据
	function prepareData(){
		var dtime_arr = ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'];
		var series = new Array();
		var entry_data = new Array();
		var quit_data = new Array();
		var d_pattern = /\d+/;
		for(var i = 1; i <= 12;i++){
			var entry_text = $("td[name='entry-total-"+i+"']").text();
			if(entry_text == ""){
				entry_data.push(0);
			}else{
				var num = parseInt(d_pattern.exec(entry_text));
				entry_data.push(num);
			}
			var quit_text = $("td[name='quit-total-"+i+"']").text();
			if(quit_text == ""){
				quit_data.push(0);
			}else{
				var num = parseInt(d_pattern.exec(quit_text));
				quit_data.push(num);
			}
		}
		var marker = {'enabled':false};
		series.push({'name':'入职', 'data':entry_data, 'marker':marker});
		series.push({'name':'离职', 'data':quit_data, 'marker':marker});
		initCharts(dtime_arr,series);
	}

	// 初始化图表
    function initCharts(dtime_arr, series){
        $('#container').highcharts({
            // 表格类型
            chart: {
                type: 'spline',
                backgroundColor:'transparent',
            },
            // 表格标题
            title: {
                text: "<?php echo empty($year) ? date('Y') : $year;?>年人事变动图"
            },
            // x轴
            xAxis: {
                categories: dtime_arr
            },
            // y轴
            yAxis: {
                title: {
                    text: '人'
                },
                labels: {
                    formatter: function() {
                        return this.value +'(人)';
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
</script>