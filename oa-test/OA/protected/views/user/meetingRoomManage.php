<?php
echo "<script type='text/javascript'>";
echo "console.log('meetingRoomManage');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-timepicker-addon.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<p class="hidden" id="user_id"><?php echo $this->user->user_id;?></p><!-- 当前访问的user_id -->

<p class="hidden" id="user_type"><?php echo Yii::app()->session['permission'];?></p><!-- 当前访问的用户权限 -->

<!-- 主界面 -->
<div>
	<!-- 今日预约 -->
	<div class="bor-1-ddd pd10">
		<!-- 标题 -->
		<h4 class="mb15 pl10">
			<strong>今日预约</strong>
		</h4>
		<div class="mb10">
			<table class="table">
				<tr>
					<?php if(!empty($rooms)): ?>
					<?php $room_num = 0;?>
					<?php foreach($rooms as $room_row): ?>
					<td class="bor-1-ddd w200">
						<label><?php echo $room_row['name']; ?></label>
						<?php if(!empty($todays)):?>
						<?php $room_num = 0; ?>
						<?php foreach($todays as $row):?>
						<?php if($row['room_id'] == $room_row['id']):?>
						<p>●<?php echo $row['content'];?> [<?php echo date('H:i',strtotime($row['start_time'])).'-'.date('H:i',strtotime($row['end_time']));?>]</p>
						<?php $room_num ++;?>
						<?php endif; ?>
						<?php endforeach; ?>
						<?php if($room_num == 0):?>
						<p>暂无预约</p>
						<?php endif; ?>
						<?php else:?>
						<p>暂无预约</p>
						<?php endif; ?>
					</td>
					<?php endforeach; ?>
					<?php endif; ?>
				</tr>
			</table>
		</div>
	</div>

	<div class="bor-l-1-ddd bor-r-1-ddd pd10 bg-240">
		<!-- 上一个月 -->
		<button class="btn btn-default fl ml10" onclick="monthMinus()">
			<span class="glyphicon glyphicon-chevron-left"></span>
		</button>
		<!-- 今天 -->
		<button class="btn btn-default fl ml20 pd3 w50" onclick="toToday();">今天</button>
		<!-- 提示 -->
		<span class="fl ml20 mt5">
			<strong>提示：预约请点击相应的日期</strong>
		</span>
		<!-- 下一个月 -->
		<button class="btn btn-default fr mr10" onclick="monthPlus()">
			<span class="glyphicon glyphicon-chevron-right"></span>
		</button>
		<!-- 当前月历的日期 -->
		<div class="m0a center">
			<h3 class="center m0 ml250 fl" id="date-h3"><?php echo $date;?></h3>
		</div>
		<div class="clear"></div>
	</div>
	<!-- 日历表格 -->
	<table class="table table-bordered center m0" id="date-table">
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
	<!-- 页脚 -->
	<div class="bg-240 pd10">
		<div class="w300 m0a">
			<div class="clear"></div>
		</div>
	</div>
</div>

<!-- 会议室预约详情窗口 -->
<div id="meeting-detail-div" class="modal fade in hint bor-rad-5 w700" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title"></h4>
    </div>

    <div class="modal-body">
    	<!-- 已有的会议室预约 -->
    	<table id="meeting-info-table" class="table bor-1-ddd center">
    		<thead>
    			<tr>
    				<th class="center">会议时间</th>
    				<th class="center">会议室</th>
    				<th class="center">会议内容</th>
    				<th class="center">发起人</th>
    				<th class="center">操作</th>
    			</tr>
    		</thead>
    	</table>
    	<!-- 会议室预约表格 -->
        <table class="bor-1-ddd table hidden" id="new-meeting-table">
        	<tr>
        		<th class="center w130">会议时间</th>
        		<td>
        			<input class="form-control w130 inline pointer center" id="start-time" value="09:00">&nbsp;
	        		到&nbsp;
	        		<input class="form-control w130 inline pointer center" id="end-time" value="10:00">
        		</td>
        	</tr>
        	<tr>
        		<th class="center w130">会议内容</th>
        		<td><input type="text" class="form-control" id="meeting-content-input" placeholder="请输入会议的内容"></td>
        	</tr>
        	<tr>
        		<th class="center w130">选择会议室</th>
        		<td>
        			<select class="w130 form-control inline" id="meeting-place-select">
        				<?php 
        					foreach($rooms as $room){
        						echo "<option value='{$room['id']}'>{$room['name']}</option>";
        					}
        				?>
        			</select>
        		</td>
        	</tr>
        </table>
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success" id="new-meeting-apply-btn" onclick="newApply();"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;添加</button>
        <button class="btn btn-success hidden" id="send-meeting-apply-btn" onclick="sendApply();">提交预约</button>
    </div>
</div>

<!-- 修改会议室预约窗口 -->
<div id="edit-detail-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">修改会议预约详情</h4>
    </div>

    <div class="modal-body">
    	<!-- 会议室预约表格 -->
        <table class="bor-1-ddd table" id="edit-meeting-table">
        	<tr>
        		<th class="center w130">会议日期</th>
        		<td class="hidden" id="edit-id"></td>
        		<td>
        			<input class="form-control pointer" id="edit-date-input" placeholder="请选择会议日期">
        		</td>
        	</tr>
        	<tr>
        		<th class="center w130">会议时间</th>
        		<td>
        			<input class="form-control w130 inline pointer center" id="edit-start-time" value="09:00">&nbsp;
	        		到&nbsp;
	        		<input class="form-control w130 inline pointer center" id="edit-end-time" value="10:00">
        		</td>
        	</tr>
        	<tr>
        		<th class="center w130">会议内容</th>
        		<td>
        			<input type="text" class="form-control" id="edit-content-input" placeholder="请输入会议的内容">
        		</td>
        	</tr>
        	<tr>
        		<th class="center w130">选择会议室</th>
        		<td>
        			<select class="w130 form-control inline" id="edit-place-select">
        				<?php 
        					foreach($rooms as $room){
        						echo "<option value='{$room['id']}'>{$room['name']}</option>";
        					}
        				?>
        			</select>
        		</td>
        	</tr>
        </table>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success" onclick="sendEdit();">确认修改</button>
    </div>
</div>


<!-- js -->
<script type="text/javascript">
/*------------------------------------------页面初始化-----------------------------------------------------*/

	// 页面初始化
	$(document).ready(function(){
		// 日历控件初始化
		$('#start-time').datetimepicker({timeOnly:true});
		$('#end-time').datetimepicker({timeOnly:true});
		$('#edit-start-time').datetimepicker({timeOnly:true});
		$('#edit-end-time').datetimepicker({timeOnly:true});
		$('#edit-date-input').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});

		// 初始化日历
		initDateTable();
	});	

	// 初始化表格
	function initDateTable(){
		var first_weekday = getFirstWeekday();  // 该月第一天周几
		var days_count = getDaysCount();        // 该月的天数

		// 清空表格数据
		$("#date-table").find("tbody").children().remove();

		var set_flag = 0; // 填充每月前面的空格标记
		var td_count = 0; // 单元格计数

		// 循环输出
		for(var n = 0;n <= days_count;n++){
			// 初始化单元格计数
			td_count = 0; 

			// 判断是否已填充每月前面的空格
			if(set_flag == 0){
				// 根据第一天是周几来填充空白的单元格
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

				// 判断是否已经够了7个单元格，够7个就换行
				if(td_count == 7){
					$("#date-table").find("tbody").append("<tr></tr>");
					var td_str = "<td id='td-"+n+"' style='vertical-align:top;'><p class='right m0'>"+n+"</p></td>";
					$("#date-table").find("tr").last().append(td_str);
				}else{
					var td_str = "<td id='td-"+n+"' style='vertical-align:top;'><p class='right m0'>"+n+"</p></td>";
					$("#date-table").find("tr").last().append(td_str);
				}
			}
		}

		// 填充最后一行的空白单元格
		if(td_count==7){
			var str = "<td></td><td></td><td></td><td></td><td></td><td></td>";
			$("#date-table").find("tr").last().append(str);
		}else{
			for(var k = 0;k < 6-td_count;k++){
				var str = "<td></td>";
				$("#date-table").find("tr").last().append(str);
			}
		}

		// 给单元格注册click事件
		$("#date-table").find("td").each(function(){
			// 判断是否为空白单元格
			if(typeof($(this).attr("id"))!="undefined"){
				$(this).bind("click",function(){
					showDetails(this);
				});
			}
		});

		// 填充数据
		setData();

		// 获取当天的日期字符串
		var today = new Date();
		if(navigator.userAgent.indexOf("Chrome")>-1){
			var year = today.getYear();
            year += 1900;
		}else{
        	var year = today.getFullYear();
        }
        var month = today.getMonth() + 1;

        // 当天的日期高亮显示
		if(year == $("#date-h3").text().split("-")[0] && month == $("#date-h3").text().split("-")[1]){
			$("#td-"+today.getDate()).css("background","#FFFFCC");  // 设置背景色
			if($("#td-"+today.getDate()).find("p.white").text()!=""){
				$("#td-"+today.getDate()).find("p.white").addClass("hidden");
				$("#td-"+today.getDate()).find("p.white").parent().attr('onmouseover','$(this).find(\'p.white\').removeClass(\'hidden\')');
				$("#td-"+today.getDate()).find("p.white").parent().attr('onmouseout','$(this).find(\'p.white\').addClass(\'hidden\')');
			}
		}
	}

	// 填充数据
	var data = new Array(); // 预约数据
	var room = new Array(); // 会议室数据
	function setData(){
		<?php 
			// 填充会议室数据
			foreach($rooms as $room){
				echo "room.push({'id':'{$room['id']}', 'name':'{$room['name']}', 'location':'{$room['location']}', 'status':'{$room['status']}'});";
			}
		?>
		<?php 
			// 填充预约数据
			$index = 0; 
			foreach($data as $details){
				foreach($details as $detail){
					echo "data.push({'id':'{$detail['id']}','row_id':'{$index}','content':'{$detail['content']}', 'room_id':'{$detail['room_id']}', 'room_name':'{$detail->room->name}','meeting_date':'{$detail['meeting_date']}', 'start_time':'{$detail['start_time']}', 'end_time':'{$detail['end_time']}', 'user_id':'{$detail['user_id']}', 'user_name':'{$detail->user->cn_name}'});";
					$index ++ ;
				}
			}
		?>

		var days_count = getDaysCount();  // 当前月最大天数
		var content_flag = 0;  // 是否预约的标记

		for(var n = 0;n <= days_count;n++){
			content_flag = 0; // 预约标记初始化

			// 遍历预约数据
			$.each(data, function(){
				// 预约的日期
				var date = this['meeting_date']; 
				var day = date.split("-")[2]; 

				// 判断正在输出的是否为预约的日期
				if(n == day){ 
					var row_id_str = "<p class='m0 hidden' name='row_id'>"+this['row_id']+"</p>";

					// 会议内容处理
					var content = "";
					if(this['content'].length > 8){
						content = this['content'].substr(0,8)+"...";
					}else{
						content = this['content'];
					}

					// 填充数据到表格中
					var content_str = "<p class='m0 left' name='place' title='"+this['start_time'].substr(0,5)+"-"+this['end_time'].substr(0,5)+" ["+this['room_name']+"] "+content+"'>●"+content+"</p>";
					$("#td-"+n).append("<div class='meeting-div mb5'></div>");
					$("#td-"+n).find("div").last().append(row_id_str);
					$("#td-"+n).find("div").last().append(content_str);
					
					content_flag = 1;
				}
			});

			// 判断是否有预约
			if(content_flag == 0){
				// 获取日期字符串
				var date_today = new Date();
				var today_str = parseInt(date_today.getDate());
				var month_str = parseInt(date_today.getMonth()+1);
				var year_str = "";
				if(navigator.userAgent.indexOf("Chrome")>-1){
					year_str = date_today.getYear();
		            year_str += 1900;
				}else{
		        	year_str = date_today.getFullYear();
		        }

		        // 判断是否输出预约会议室按钮
		        if(parseInt(year_str) < parseInt(year)){ // 大于当前年份，显示预约会议室按钮
		        	var blank_str = "<p class='m0 white' name='blank'>点击预约会议室</p>";
					$("#td-"+n).append("<div class='meeting-div pt20 pb20'></div>");
					$("#td-"+n).find("div").last().append(blank_str);
		        }else if(parseInt(year_str) == parseInt(year)){ // 在当前年份
		        	if(month_str < parseInt(month)){ // 大于当前月份，显示预约会议室按钮
		        		var blank_str = "<p class='m0 white' name='blank'>点击预约会议室</p>";
						$("#td-"+n).append("<div class='meeting-div pt20 pb20'></div>");
						$("#td-"+n).find("div").last().append(blank_str);
		        	}else if(month_str == parseInt(month)){ // 在当前月份
		        		if(n >= today_str){ // 大于等于当前日期，显示预约会议室按钮
		        			var blank_str = "<p class='m0 white' name='blank'>点击预约会议室</p>";
							$("#td-"+n).append("<div class='meeting-div pt20 pb20'></div>");
							$("#td-"+n).find("div").last().append(blank_str);
		        		}else{ // 小于当前日期，隐藏会议室按钮
		        			var blank_str = "<p class='m0' name='blank'>&nbsp;</p>";
							$("#td-"+n).append("<div class='pt20 pb20'></div>");
							$("#td-"+n).find("div").last().append(blank_str);
		        		}
		        	}else{ // 小于当前月份，隐藏会议室按钮
		        		var blank_str = "<p class='m0' name='blank'>&nbsp;</p>";
						$("#td-"+n).append("<div class='pt20 pb20'></div>");
						$("#td-"+n).find("div").last().append(blank_str);
		        	}
		        }else{ // 小于当前年份，隐藏会议室按钮
		        	var blank_str = "<p class='m0' name='blank'>&nbsp;</p>";
					$("#td-"+n).append("<div class='pt20 pb20'></div>");
					$("#td-"+n).find("div").last().append(blank_str);
		        }
			}
		}
	}
    
	// 当前查看的日期
	var date = new Date();
	var year = $("#date-h3").text().split("-")[0];
	var month = $("#date-h3").text().split("-")[1];

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

/*------------------------------------------修改会议室预约-----------------------------------------------------*/

	// 显示修改会议室预约
	function editMeeting(obj){
		// 获取数据
		var id = $(obj).parent().parent().children().first();
		var time = id.next();
		var room = time.next();

		// 填充数据
		$("#edit-id").text(id.text());
		$.each(data, function(){
			if(id.text() == this['id']){
				$("#edit-content-input").val(this['content']);
				$("#edit-place-select").val(this['room_id']);
				$("#edit-date-input").val(this['meeting_date']);
				$("#edit-start-time").val(this['start_time'].split(":")[0]+":"+this['start_time'].split(":")[1]);
				$("#edit-end-time").val(this['end_time'].split(":")[0]+":"+this['end_time'].split(":")[1]);
			}
		});

		// 隐藏会议室预约详情模态框
		$("#meeting-detail-div").modal('hide');

		// 显示修改会议室预约模态框
		var ySet = (window.innerHeight - $("#edit-detail-div").height())/2;
		var xSet = (window.innerWidth - $("#edit-detail-div").width())/2;
		$("#edit-detail-div").css("top",ySet);
		$("#edit-detail-div").css("left",xSet);
		$("#edit-detail-div").modal({show:true});
	}

	// 发送修改会议室预约
	function sendEdit(){
		// 获取数据
		var id = $("#edit-id").text();
		var meeting_date = $("#edit-date-input").val();
		var start_time = $("#edit-start-time").val();
		var end_time = $("#edit-end-time").val();
		var room_id = $("#edit-place-select").val();
		var content = $("#edit-content-input").val();

		// 验证数据
		var time_pattern = /^\d{2}:\d{2}$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		if(start_time >= end_time){
			showHint("提示信息","起始时间不能在结束时间之后！");
		}else if(!time_pattern.exec(start_time)){
			showHint("提示信息","起始时间格式不正确！");
		}else if(!time_pattern.exec(end_time)){
			showHint("提示信息","起始时间格式不正确！");
		}else if(!date_pattern.exec(meeting_date)){
			showHint("提示信息","日期格式不正确！");
		}else if(content == ""){
			showHint("提示信息","请输入会议内容！");
		}else{
			$.ajax({
		        type:'post',
		        url: '/ajax/editBookingMeeting',
		        dataType:'json',
		        data:{'id':id,'meeting_date':meeting_date, 'start_time':start_time, 'end_time':end_time, 'content':content, 'room_id':room_id},
		        success:function(result){
		          if(result.code == '0'){
		            showHint("提示信息","修改会议室预约成功！");
		            setTimeout(function(){location.reload();},1200);
		          }else if(result.code == '-1'){
		            showHint("提示信息","修改会议室预约失败！");
		          }else if(result.code == '-2'){
		            showHint("提示信息","参数错误");
		          }else if(result.code == '-3'){
		            showHint("提示信息","找不到该会议室");
		          }else if(result.code == '-4'){
		            showHint("提示信息","该会议室此时间段已被预约");
		          }else{
		            showHint("提示信息","你没有权限执行此操作");
		          }
		        }
		    });
		}
	}

/*--------------------------------------------删除会议室预约------------------------------------------------*/

	// 显示删除会议
	var delete_id = 0;
	function deleteMeeting(obj){
		// 获取数据
		var id = $(obj).parent().parent().children().first();
		var time = id.next();
		var room = time.next();

		// 二次提醒
		var str = "确认删除"+time.text()+"在"+room.text()+"的会议预约?";
		delete_id = id.text();
		showConfirm("删除会议预约",str,"确认",'sendDelete()',"取消");
	}

	// 发送删除会议
	function sendDelete(){
		$.ajax({
	        type:'post',
	        url: '/ajax/deleteBookingMeeting',
	        dataType:'json',
	        data:{'id':delete_id},
	        success:function(result){
	          if(result.code == '0'){
	            showHint("提示信息","删除会议室预约成功！");
	            setTimeout(function(){location.reload();},1200);
	          }else if(result.code == '-1'){
	            showHint("提示信息","删除会议室预约失败！");
	          }else if(result.code == '-2'){
	            showHint("提示信息","参数错误");
	          }else if(result.code == '-3'){
	            showHint("提示信息","找不到该会议室");
	          }else{
	            showHint("提示信息","你没有权限执行此操作");
	          }
	        }
	    });
	}

/*--------------------------------------------新增会议室预约------------------------------------------------*/

	// 发送新增会议室预约
	function sendApply(){
		// 获取数据
		var start_time = $("#start-time").val();
		var end_time = $("#end-time").val();
		var room_id = $("#meeting-place-select").val();
		var content = $("#meeting-content-input").val();
		var month = $("#date-h3").text();
		var day = $("#meeting-detail-div").find(".hint-title").find("span").text();
		if(parseInt(day)<10) day = "0" + day;
		var meeting_date = month+"-"+day;

		// 判断此时间段内有没有其他人预约
		var book_flag = 0;
		if($("#meeting-info-table").find("#blank_tr").text() == ""){  // 已经有人预约的话
			$.each(data, function(){
				if(room_id == this['room_id'] && meeting_date == this['meeting_date'] && book_flag == 0){  // 会议室和日期都相同的话
					// 判断时间上是否有重叠
					if(start_time > this['start_time'].substr(0,5)){
						if(start_time < this['end_time'].substr(0,5)){
							showHint("提示信息","该会议室此时间段已被预约！");
							book_flag = 1;
							return false;
						}
					}else if(start_time < this['start_time'].substr(0,5)){
						if(end_time > this['start_time'].substr(0,5)){
							showHint("提示信息","该会议室此时间段已被预约！");
							book_flag = 1;
							return false;
						}
					}else{
						showHint("提示信息","该会议室此时间段已被预约！");
						book_flag = 1;
						return false;
					}
				}
			});
		}

		// 验证数据
		var time_pattern = /^\d{2}:\d{2}$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		if(book_flag == 0){
			var current_time = "<?php echo date('H:i');?>";
			var current_day = "<?php echo date('d');?>";
			if(start_time < current_time && parseInt(day) == parseInt(current_day)){
				showHint("提示信息","起始时间不能在当前时间之前！");
			}else if(start_time >= end_time){
				showHint("提示信息","起始时间不能在结束时间之后！");
			}else if(!time_pattern.exec(start_time)){
				showHint("提示信息","起始时间格式不正确！");
			}else if(!time_pattern.exec(end_time)){
				showHint("提示信息","起始时间格式不正确！");
			}else if(!date_pattern.exec(meeting_date)){
				showHint("提示信息","日期格式不正确！");
			}else if(content == ""){
				showHint("提示信息","请输入会议内容！");
			}else{
				$.ajax({
			        type:'post',
			        url: '/ajax/bookingMeeting',
			        dataType:'json',
			        data:{'meeting_date':meeting_date, 'start_time':start_time, 'end_time':end_time, 'content':content, 'room_id':room_id},
			        success:function(result){
			          if(result.code == '0'){
			            showHint("提示信息","预约会议室成功！");
			            setTimeout(function(){location.reload();},1200);
			          }else if(result.code == '-1'){
			            showHint("提示信息","预约会议室失败！");
			          }else if(result.code == '-2'){
			            showHint("提示信息","参数错误");
			          }else if(result.code == '-3'){
			            showHint("提示信息","找不到该会议室");
			          }else if(result.code == '-4'){
			            showHint("提示信息","该会议室此时间段已被预约");
			          }else{
			            showHint("提示信息","你没有权限执行此操作");
			          }
			        }
			    });
			}
		}
	}
	
	// 显示新增预约会议室
	function newApply(){
		$("#meeting-content-input").val("");
		$("#new-meeting-apply-btn").addClass("hidden");
		$("#send-meeting-apply-btn").removeClass("hidden");
		$("#new-meeting-table").removeClass("hidden");
	}

/*--------------------------------------------显示会议室预约详情------------------------------------------------*/

	// 显示详情
	function showDetails(obj){
		// title设定
		var day = $(obj).find("p").first().text();
		var hint_title = month+"月"+day+"日会议室安排"
		$("#meeting-detail-div").find(".hint-title").text(hint_title);
		var day_str = "<span class='hidden'>"+day+"</span>";
		$("#meeting-detail-div").find(".hint-title").append(day_str);

		// 清空表格数据
		$("#meeting-info-table").find("tbody").children().remove();

		// 数据填充
		if($(obj).find("p.white").text() == ""){ // 当天有会议室预约
			$(obj).find("div").each(function(){
				var row_id = $(this).find("p.hidden").text();

				// 判断当前用户是否为申请人或者管理员, 如果是则显示修改和删除按钮
				if(data[row_id]['user_id'] == $("#user_id").text() || $("#user_type").text() == "admin"){
					var str = "<tr><td class='hidden'>"+
					data[row_id]['id']+"</td><td>"+
					data[row_id]['start_time'].substr(0,5)+"-"+data[row_id]['end_time'].substr(0,5)+
					"</td><td>"+data[row_id]['room_name']+"</td><td class='w300' style='word-break:break-all;'>"+
					data[row_id]['content']+"</td><td>"+data[row_id]['user_name']+
					"</td><td><button class='btn bor-none bg-trans btn-default' onclick='editMeeting(this);'><span class='glyphicon glyphicon-pencil'></span></button>"+
					"<button class='btn btn-default bor-none bg-trans b2' onclick='deleteMeeting(this);'><span class='glyphicon glyphicon-remove'></span></button>"+
					"</td></tr>";
				}else{
					var str = "<tr><td class='hidden'>"+
					data[row_id]['id']+"</td><td>"+
					data[row_id]['start_time'].substr(0,5)+"-"+data[row_id]['end_time'].substr(0,5)+
					"</td><td>"+data[row_id]['room_name']+"</td><td class='w300' style='word-break:break-all;'>"+
					data[row_id]['content']+"</td><td>"+data[row_id]['user_name']+
					"</td><td></td></tr>";
				}
				
				// 填充数据到表格中
				$("#meeting-info-table").append(str);
			});

			// 获取日期字符串
			var date_today = new Date();
			var today_str = parseInt(date_today.getDate());
			var month_str = parseInt(date_today.getMonth()+1);
			var year_str = "";

			// 获取年份
			if(navigator.userAgent.indexOf("Chrome")>-1){
				year_str = date_today.getYear();
	            year_str += 1900;
			}else{
	        	year_str = date_today.getFullYear();
	        }

	        // 判断是否可以继续添加会议室预约
	        if(parseInt(year_str) < parseInt(year)){  // 小于当前年份，不显示添加按钮
	        	$("#new-meeting-apply-btn").removeClass("hidden");
	        }else if(parseInt(year_str) == parseInt(year)){ // 在当前年份
	        	if(parseInt(month) < month_str){  // 小于当前月份，不显示添加按钮
					$("#new-meeting-apply-btn").addClass("hidden");
				}else if(parseInt(month) == month_str){  // 在当前月份
					if(parseInt(day) < today_str){  // 日期在今天以前，不显示添加按钮
						$("#new-meeting-apply-btn").addClass("hidden");
					}else{  // 日期在今天以后，显示添加按钮
						$("#new-meeting-apply-btn").removeClass("hidden");
					}
				}else{ // 大于当前月份，显示添加按钮
					$("#new-meeting-apply-btn").removeClass("hidden");
				}
	        }else{  // 大于当前年份，显示添加按钮
	        	$("#new-meeting-apply-btn").addClass("hidden");
	        }

	        // 隐藏提交按钮
	        $("#send-meeting-apply-btn").addClass("hidden");

	        // 隐藏新增会议室预约表格
			$("#new-meeting-table").addClass("hidden");

			// 显示会议室详情表格
			$("#meeting-info-table").removeClass("hidden");
		}else{  // 当天没有会议室预约
			// 当日会议室预约详情表格显示：目前没有预约
			var str = "<tr><td colspan='5' id='blank_tr'>目前没有预约</td></tr>";
			$("#meeting-info-table").append(str);
			$("#meeting-info-table").addClass("hidden");

			// 如果没有就直接显示：新增会议室预约
			newApply();
		}
		
		// 显示会议室预约详情模态框
		var ySet = (window.innerHeight - $("#meeting-detail-div").height())/2;
		var xSet = (window.innerWidth - $("#meeting-detail-div").width())/2;
		$("#meeting-detail-div").css("top",ySet);
		$("#meeting-detail-div").css("left",xSet);
		$("#meeting-detail-div").modal({show:true});
	}

/*--------------------------------------------上一个月、下一个月、今天操作---------------------------------------------------*/

	// 向前一个月-btn
	function monthMinus(){
		if(parseInt(month)-1 < 10){
			if(parseInt(month)-1 < 1){
				var href_str = "/user/meetingRoomManage/date/"+(parseInt(year)-1)+"-12";
			}else{
				var href_str = "/user/meetingRoomManage/date/"+year+"-0"+(month-1);
			}
		}else{
			var href_str = "/user/meetingRoomManage/date/"+year+"-"+(month-1);
		}
		location.href = href_str;
	}

	// 向后一个月-btn
	function monthPlus(){
		if(parseInt(month)+1 < 10){
			var href_str = "/user/meetingRoomManage/date/"+year+"-0"+(parseInt(month)+1);
		}else if(parseInt(month)+1 > 12){
			var href_str = "/user/meetingRoomManage/date/"+(parseInt(year)+1)+"-01";
		}else{
			var href_str = "/user/meetingRoomManage/date/"+year+"-"+(parseInt(month)+1);
		}
		location.href = href_str;
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
        if(parseInt(month) < 10){
        	month = "0" + month;
        }
        var href_str = "/user/meetingRoomManage/date/"+year+"-"+month;
        location.href = href_str;
	}
</script>