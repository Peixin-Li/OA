<?php
echo "<script type='text/javascript'>";
echo "console.log('processOverTime');";
echo "</script>";
?>

<!-- 主界面 -->
<!-- 返回按钮 -->
<div class="left bor-l-1-ddd bor-r-1-ddd">
      <button class="btn btn-default ml10 mt10 f18px" onclick="location.href='/user/departmentOverTime';"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回</button>
</div>
<div class="pd20 bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd center">
	<!-- 标题 -->
	<h4 class="pl5 mb15 left"><strong>部门加班详情</strong></h4>
	<!-- 部门加班详情 -->
  	<?php if(!empty($data)): ?>
	<table class="table bor-1-ddd left m0" id="overtime-table">
		<thead>
			<tr>
				<th class="center w50"></th>
				<th class="w130">姓名</th>
				<th class="w150">加班时间</th>
				<th class="">工作内容</th>
			</tr>
		</thead>
		<tbody>
        <?php foreach($data as $row): ?>
			<tr>
        <td><input type="checkbox" name="checkbox" id="<?php echo $row->id; ?>" checked></td>
        <td><?php echo $row->user->cn_name; ?></td>
        <td><?php echo $row->overtime_date." ".date('H:i',strtotime($row->overtime_time)); ?></td>
        <td><?php echo $row->content; ?></td>
			</tr>
        <?php endforeach; ?>
		</tbody>
	</table>
	<p class="gray m0 pt10 left">（确认加班信息无误则在前面打勾，点击提交。）</p>
  	<button class="btn btn-success w100 btn-lg" onclick="sendOverTime();">提交</button>
  	<?php else: ?>
	<!-- no found -->
	<h4 class="center bor-1-ddd m0 pd20">没有需要审批的加班记录</h4>
	<?php endif; ?>
</div>

<!-- js -->
<script type="text/javascript">
	// 提交加班统计
	var id_arr = new Array();
	var id_arr_reject = new Array();
	function sendOverTime(){
		id_arr = new Array();
		id_arr_reject = new Array();
		$("input[name='checkbox']").each(function(){
			if(this.checked){
				id_arr.push($(this).attr("id"));
			}else{
				id_arr_reject.push($(this).attr("id"));
			}
		});
		if(id_arr.length != 0){
			$.ajax({
		      type:'post',
		      dataType:'json',
		      url:'/ajax/overtimeProcess',
		      data:{'id_arr':id_arr, 'tag':'success'},
		      success:function(result){
		        if(result.code == 0){
		        	if(id_arr_reject.length != 0){
		        		sendReject();
		        	}else{
		        		showHint("提示信息","确认加班信息成功！");
		        		setTimeout(function(){location.reload();},1200);
		        	}
		        }else if(result.code == -1){
		          	showHint("提示信息","确认加班信息失败！");
		        }else if(result.code == -2){
		          	showHint("提示信息","参数错误！");
		        }else{
		        	showHint("提示信息","你没有权限执行此操作！");
		        }
		      }
		    });
		}else{
			if(id_arr_reject.length != 0){
				sendReject();
			}
		}
		
	}

	function sendReject(){
		$.ajax({
	      type:'post',
	      dataType:'json',
	      url:'/ajax/overtimeProcess',
	      data:{'id_arr':id_arr_reject,'tag':'reject'},
	      success:function(result){
	        if(result.code == 0){
	        	showHint("提示信息","确认加班信息成功！");
	        	setTimeout(function(){location.reload();},1200);
	        }else if(result.code == -1){
	          	showHint("提示信息","确认加班信息失败！");
	        }else if(result.code == -2){
	          	showHint("提示信息","参数错误！");
	        }else{
	        	showHint("提示信息","你没有权限执行此操作！");
	        }
	      }
	    });
	}
</script>
