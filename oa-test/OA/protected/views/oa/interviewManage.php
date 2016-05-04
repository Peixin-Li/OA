<?php
echo "<script type='text/javascript'>";
echo "console.log('interviewManage');";
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
<?#php echo "<pre>";var_dump($data); ?>
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">面试安排</h4>
	<!-- 跳转日期 -->
	<div class="bor-l-1-ddd bor-r-1-ddd pd10">
		<button class="btn btn-default fl ml10" onclick="monthMinus()"><span class="glyphicon glyphicon-chevron-left"></span></button>
		<button class="btn btn-default fl ml20 pd3 w50" onclick="toToday();">今天</button>
		<button class="btn btn-default fr mr10" onclick="monthPlus()"><span class="glyphicon glyphicon-chevron-right"></span></button>
		<div class="m0a center">
			<h3 class="m0 center" id="date-h3"><?php if(!empty($date)) echo $date;else echo date("Y-m");?></h3>
		</div>
		<div class="clear"></div>
	</div>
	<!-- 选择面试日期 -->
	<table class="table table-bordered center" id="date-table">
		<thead>
			<tr>
				<th class="center w150">周一</th>
				<th class="center w150">周二</th>
				<th class="center w150">周三</th>
				<th class="center w150">周四</th>
				<th class="center w150">周五</th>
				<th class="center w150">周六</th>
				<th class="center w150">周日</th>
			</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>

<!-- 详情窗口 -->
<div id="interview-detail-div" class="modal fade in hint bor-rad-5 w600" style="display: block; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title"></h4>
    </div>

    <div class="modal-body">
    	<!-- 已有的面试安排 -->
    	<table id="interview-info-table" class="table bor-1-ddd center m0">
    		<thead>
    			<tr>
    				<th class="center">面试时间</th>
    				<th class="center">面试人</th>
    				<th class="center">面试岗位</th>
    				<th class="center">操作</th>
    			</tr>
    		</thead>
    		<tbody></tbody>
    	</table>
    </div>

    <div class="modal-footer">
    </div>
</div>

<!-- 详情窗口 -->
<div id="interview-edit-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">修改面试时间</h4>
    </div>

    <div class="modal-body">
    	<table class="table bor-1-ddd m0">
    		<tr>
    			<th class="center w130">面试日期</th>
    			<td class="hidden" id="edit-resume-id"></td>
    			<td>
    				<input class="form-control w150 pointer" id="edit-input-date">
    			</td>
    		</tr>
    		<tr>
    			<th class="center w130">面试时间</th>
    			<td>
    				<div class="fl">
        				<div>
	        				<button class="btn btn-default pd3 w36 bor-none f10px mr20 ml8" id="edit-start-hour-minus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-up"></span></button>
	        				<button class="btn btn-default pd3 w36 bor-none f10px" id="edit-start-minute-minus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-up"></span></button>
	        			</div>
	        			<div>
	        				<input type="text" class="form-control center w50 m0a h30 inline" id="edit-start-hour-input" value="09" onchange="hourInputCheck(this);">
	        				:
	        				<input type="text" class="form-control center w50 m0a h30 inline" id="edit-start-minute-input" value="30" onchange="minuteInputCheck(this);">
	        			</div>
	        			<div>
	        				<button class="btn btn-default pd3 w36 bor-none f10px mr20 ml8" id="edit-start-hour-plus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-down"></span></button>
	        				<button class="btn btn-default pd3 w36 bor-none f10px" id="edit-start-minute-plus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-down"></span></button>
	        			</div>
        			</div>
    			</td>
    		</tr>
    	</table>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success" id="send-interview-apply-btn" onclick="sendEditInterview();">确认修改</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
	// 修改面试时间-发送
	function sendEditInterview(){
		var id = $("#edit-resume-id").text();
		var date = $("#edit-input-date").val();
		var hour = $("#edit-start-hour-input").val();
		var minute = $("#edit-start-minute-input").val();
		var time_str = date+" "+hour+":"+minute+":00";
		var time_str_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
		if(!time_str_pattern.exec(time_str)){
			showHint("提示信息","日期格式输入不正确");
		}else{
			$.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/editInterviewTime',
                data:{'id':id, 'time':time_str},
                success:function(data){
                    if(data.code == 0)
                    {
                        showHint("提示信息","修改面试时间成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(data.code == -1)
                    {
                        showHint("提示信息","修改面试时间失败");
                    }
                    else if(data.code == -2)
                    {
                        showHint("提示信息","参数错误");
                    }
                    else if(data.code == -3)
                    {
                        showHint("提示信息","找不到该简历");
                    }
                    else
                    {
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
		}
	}

	// 修改面试时间-显示
	function editInterview(obj){
		var resume_id = $(obj).parent().parent().children().first().text();
		$("#edit-resume-id").text(resume_id);
		var interview_time = $(obj).parent().parent().children().first().next().text();
		var date = interview_time.split(" ")[0];
		var hour = interview_time.split(" ")[1].substr(0,2);
		var minute = interview_time.split(" ")[1].split(":")[1];
		$("#edit-input-date").val(date);
		$("#edit-start-hour-input").val(hour);
		$("#edit-start-minute-input").val(minute);
		var ySet = (window.innerHeight - $("#interview-edit-div").height())/3;
        var xSet = (window.innerWidth - $("#interview-edit-div").width())/2;
        $("#interview-edit-div").css("top",ySet);
        $("#interview-edit-div").css("left",xSet);
		$("#interview-edit-div").modal({show:true});
	}

	// 显示详情
	function showDetails(obj){

		var title_str = month+"月"+$(obj).find("p").first().text()+"日面试安排";
		$("#interview-detail-div").find("h4.hint-title").text(title_str);

		var day = $(obj).find("p").first().text();
		if(parseInt(day) < 10) day = "0"+day;
		var date_str = year+"-"+month+"-"+day;
		$("#interview-info-table").find("tbody").children().remove();
		var empty_tag = 0;
		
		$.each(data,function(){
			if(this['interview_time'].split(" ")[0] == date_str){
				var status_str = "";
				if(this['status'] == "arrange"){
					status_str = "<td><button class='btn bor-none bg-trans btn-default' onclick='editInterview(this);'><span class='glyphicon glyphicon-pencil'></td>";
				}else if(this['status'] == 'nonarrival'){
					status_str = "<td>缺席</td>";
				}else if(this['status'] == 'giveup'){
					status_str = "<td>放弃入职</td>";
				}else if(this['status'] == 'assessment'){
					status_str = "<td>已面试</td>";
				}else if(this['status'] == "entry"){
					status_str = "<td>即将入职</td>";
				}
				var str = "<tr><td class='hidden'>"+this['resume_id']+"</td>"+
				"<td class='hidden'>"+this['interview_time']+"</td>"+
				"<td>"+this['interview_time'].split(" ")[1].substr(0,5)+"</td>"+
				"<td>"+this['name']+"</td>"+
				"<td>"+this['title']+"</td>"+status_str+"</tr>";
				$("#interview-info-table").find("tbody").append(str);
				empty_tag = 1; 
			}
		});
		if(empty_tag == 0){
			var str = "<tr><td colspan='4'>当天没有安排面试</td></tr>";
			$("#interview-info-table").find("tbody").append(str);
		}
		var ySet = (window.innerHeight - $("#interview-detail-div").height())/3;
        var xSet = (window.innerWidth - $("#interview-detail-div").width())/2;
        $("#interview-detail-div").css("top",ySet);
        $("#interview-detail-div").css("left",xSet);
		$("#interview-detail-div").modal({show:true});
	}

	// 当前日期
	var date = new Date();
	var year = $("#date-h3").text().split("-")[0];
	var month = $("#date-h3").text().split("-")[1];
	
	// 初始化表格
	$(document).ready(function(){
		initDateTable();

		$('#edit-input-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    	$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

	// 填充数据
	var data = new Array();
	function setData(){
		<?php 
			foreach($resumes as $row){
				echo "data.push({'interview_time':'{$row->interview_time}', 'name':'{$row->name}', 'title':'{$row->apply->title}', 'resume_id':'{$row->id}', 'status':'{$row->status}'});";
			}
		?>
		var content_flag = 0;
		var days_count = getDaysCount();
		for(var n = 0;n<= days_count;n++){
			content_flag = 0;
			$.each(data, function(){
				var date = this['interview_time'];
				var day = date.split("-")[2].substr(0,2);
				var time = date.split(" ")[1].substr(0,5);
				var title = this['title'];
				var name = this['name'];
				var status = this['status'];
				var status_str = "";
				if(status == "arrange"){
					status_str = "等待面试";
				}else if(status == "nonarrival"){
					status_str = "缺席";
				}else if(status == "assessment"){
					status_str = "已面试";
				}else if(status == "giveup"){
					status_str = "放弃入职";
				}else if(status == "entry"){
					status_str = "即将入职";
				}
				if(n == day){
					var row_id_str = "<p class='m0 hidden' name='resume_id'>"+this['resume_id']+"</p>";
					var content_str = "<p class='m0 left' title='"+name+"-"+time+"-"+status_str+"'>●"+title+"</p>";
					$("#td-"+n).append("<div class='meeting-div mb5'></div>");
					$("#td-"+n).find("div").last().append(row_id_str);
					$("#td-"+n).find("div").last().append(content_str);
					content_flag = 1;
				}
			});
			if(content_flag == 0){
				var blank_str = "<p class='m0 white'></p>";
				$("#td-"+n).append("<div class='meeting-div pt20 pb20'></div>");
				$("#td-"+n).find("div").last().append(blank_str);
			}
		}
	}

	// 求当前月最大天数
	function getDaysCount(){
		var maxdate = new Date(year, month , 0);
		var days_count = maxdate.getDate();
		return days_count;
	}

	// 求月第一天
	function getFirstWeekday(){
		var first_str = year+"/"+month+"/01";
		var firstDate = new Date(first_str);
		firstDate.setDate(1); 
		var first_weekday = firstDate.getDay();
		if(first_weekday == 0) first_weekday = 7;
		return first_weekday;
	}

	// 向前一个月-btn
	function monthMinus(){
		if(parseInt(month)-1 < 10){
			if(parseInt(month)-1 < 1){
				var href_str = "/oa/interviewManage/date/"+(parseInt(year)-1)+"-12";
			}else{
				var href_str = "/oa/interviewManage/date/"+year+"-0"+(month-1);
			}
			
		}else{
			var href_str = "/oa/interviewManage/date/"+year+"-"+(month-1);
		}
		location.href = href_str;
	}

	// 向后一个月-btn
	function monthPlus(){
		if(parseInt(month)+1 < 10){
			var href_str = "/oa/interviewManage/date/"+year+"-0"+(parseInt(month)+1);
		}else if(parseInt(month)+1 > 12){
			var href_str = "/oa/interviewManage/date/"+(parseInt(year)+1)+"-01";
		}else{
			var href_str = "/oa/interviewManage/date/"+year+"-"+(parseInt(month)+1);
		}
		location.href = href_str;
	}

	// 初始化表格
	function initDateTable(){
		// 获得第一天和天数
		var first_weekday = getFirstWeekday();
		var days_count = getDaysCount();

		// 清空表格数据
		$("#date-table").find("tbody").children().remove();

		var set_flag = 0;
		var td_count = 0;
		for(var n = 0;n <= days_count;n++){
			td_count = 0;
			if(set_flag == 0){
				$("#date-table").find("tbody").append("<tr></tr>");
				for(var i=0;i < first_weekday-1;i++){
					$("#date-table").find("tr").last().append("<td></td>");
				}
				set_flag = 1;
			}else{
				// 计算有几个td了
				$("#date-table").find("tr").last().children().each(function(){
					td_count += 1;
				});
				// 够7个就换行
				if(td_count == 7){
					$("#date-table").find("tbody").append("<tr></tr>");
					var td_str = "<td id='td-"+n+"' class='pointer' style='vertical-align:top;'><p class='right m0'>"+n+"</p></td>";
					$("#date-table").find("tr").last().append(td_str);
				}else{
					var td_str = "<td id='td-"+n+"' class='pointer' style='vertical-align:top;'><p class='right m0'>"+n+"</p></td>";
					$("#date-table").find("tr").last().append(td_str);
				}
			}
		}
		// 填充空的td
		if(td_count==7){
			var str = "<td></td><td></td><td></td><td></td><td></td><td></td>";
			$("#date-table").find("tr").last().append(str);
		}else{
			for(var k = 0;k < 6-td_count;k++){
				var str = "<td></td>";
				$("#date-table").find("tr").last().append(str);
			}
		}


		// 给td注册click事件-显示详情
		$("#date-table").find("td").each(function(){
			if(typeof($(this).attr("id"))!="undefined"){
				$(this).bind("click",function(){
					showDetails(this);
				});
			}
		});

		// 填充数据
		setData();

		// 当天
		var today = new Date();
		if(navigator.userAgent.indexOf("Chrome")>-1){
			var year = today.getYear();
            year += 1900;
		}else{
        	var year = today.getFullYear();
        }
        var month = today.getMonth() + 1;
		if(year == $("#date-h3").text().split("-")[0] && month == $("#date-h3").text().split("-")[1]){
			$("#td-"+today.getDate()).css("background","#FFFFCC");
			if($("#td-"+today.getDate()).find("p.white").text()!=""){
				$("#td-"+today.getDate()).find("p.white").addClass("hidden");
				$("#td-"+today.getDate()).find("p.white").parent().attr('onmouseover','$(this).find(\'p.white\').removeClass(\'hidden\')');
				$("#td-"+today.getDate()).find("p.white").parent().attr('onmouseout','$(this).find(\'p.white\').addClass(\'hidden\')');
			}
		}
	}

	// 回到今天
	function toToday(){
		var today = new Date();
		if(navigator.userAgent.indexOf("Chrome")>-1){
			var year = today.getYear();
            year += 1900;
		}else{
        	var year = today.getFullYear();
        }
        var month = today.getMonth() + 1;
        var href_str = "/oa/interviewManage/date/"+year+"-"+month;
        location.href = href_str;
	}

	// 时间设置
	function timeSet(id){
		switch(id){
			case "start-hour-minus":{
				var start_hour = parseInt($("#interview-start-hour-input").val());
				if(start_hour == 0){
					start_hour = 23;
				}else{
					start_hour -= 1;
				}
				var start_hour_str = "";
				if(start_hour < 10){
					start_hour_str = "0"+start_hour;
				}else{
					start_hour_str = start_hour;
				}
				$("#interview-start-hour-input").val(start_hour_str);
				break;
			}
			case "start-hour-plus":{
				var start_hour = parseInt($("#interview-start-hour-input").val());
				if(start_hour == 23){
					start_hour = 0;
				}else{
					start_hour += 1;
				}
				
				var start_hour_str = "";
				if(start_hour < 10){
					start_hour_str = "0"+start_hour;
				}else{
					start_hour_str = start_hour;
				}
				$("#interview-start-hour-input").val(start_hour_str);
				break;
			}
			case "start-minute-minus":{
				var start_minute = $("#interview-start-minute-input").val();
				if(start_minute == "00"){
					start_minute = "30";
				}else{
					start_minute = "00";
				}
				$("#interview-start-minute-input").val(start_minute);
				break;
			}
			case "start-minute-plus":{
				var start_minute = $("#interview-start-minute-input").val();
				if(start_minute == "00"){
					start_minute = "30";
				}else{
					start_minute = "00";
				}
				$("#interview-start-minute-input").val(start_minute);
				break;
			}


			case "edit-start-hour-minus":{
				var start_hour = parseInt($("#edit-start-hour-input").val());
				if(start_hour == 0){
					start_hour = 23;
				}else{
					start_hour -= 1;
				}
				var start_hour_str = "";
				if(start_hour < 10){
					start_hour_str = "0"+start_hour;
				}else{
					start_hour_str = start_hour;
				}
				$("#edit-start-hour-input").val(start_hour_str);
				break;
			}
			case "edit-start-hour-plus":{
				var start_hour = parseInt($("#edit-start-hour-input").val());
				if(start_hour == 23){
					start_hour = 0;
				}else{
					start_hour += 1;
				}
				
				var start_hour_str = "";
				if(start_hour < 10){
					start_hour_str = "0"+start_hour;
				}else{
					start_hour_str = start_hour;
				}
				$("#edit-start-hour-input").val(start_hour_str);
				break;
			}
			case "edit-start-minute-minus":{
				var start_minute = $("#edit-start-minute-input").val();
				if(start_minute == "00"){
					start_minute = "30";
				}else{
					start_minute = "00";
				}
				$("#edit-start-minute-input").val(start_minute);
				break;
			}
			case "edit-start-minute-plus":{
				var start_minute = $("#edit-start-minute-input").val();
				if(start_minute == "00"){
					start_minute = "30";
				}else{
					start_minute = "00";
				}
				$("#edit-start-minute-input").val(start_minute);
				break;
			}
		}
	}

	// 小时输入检测
	function hourInputCheck(obj){
		var hour = $(obj).val();
		if(parseInt(hour) < 10){
			hour = "0" + parseInt(hour);
		}
		var d_pattern = /^\d{2}$/;
		if(!d_pattern.exec(hour) || parseInt(hour) >= 24 || parseInt(hour) < 0){
			showHint("提示信息","小时格式输入错误");
			$(obj).val("09");
		}else{
			$(obj).val(hour);
		}
	}

	// 分钟输入检测
	function minuteInputCheck(obj){
		var minute = $(obj).val();
		if(parseInt(minute) < 10){
			minute = "0" + parseInt(minute);
		}
		if(minute != "30" && minute != "00"){
			showHint("提示信息","分钟格式输入错误！");
			$(obj).val("00");
		}else{
			$(obj).val(minute);
		}
	}
</script>