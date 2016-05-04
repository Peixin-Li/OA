<?php
echo "<script type='text/javascript'>";
echo "console.log('activityHeadSet');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">组长设置</h4>
    <!-- 兴趣小组组长设置表格 -->
	<div class="pd20">
		<?php if(!empty($teams)): ?>
		<table class="table table-bordered center w600" id="team-table">
			<tbody>
				<tr class="bg-fa"> 
					<th class="w300 center">组名</th>
					<th class="w300 center">组长</th>
				</tr>
				<?php foreach($teams as $row): ?>
				<tr>
					<td class="hidden"><?php echo $row['id']; ?></td>
					<td><?php echo $row['name']; ?></td>
					<td>	
						<span><?php echo $row->user->cn_name; ?></span>
						<a class="pointer ml10" onclick="editName(this);">修改</a>
						<input class="form-control w150 inline hidden">
						<a class="pointer ml10 hidden" onclick="saveName(this);">保存</a>
						<a class="pointer ml10 hidden" onclick="cancelName(this);">取消</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php else: ?>
		<h4 class="m0 pd20 bor-1-ddd center">没有数据</h4>
		<?php endif; ?>
	</div>
</div>


<!--js-->
<script type="text/javascript">
	// 保存
	function saveName(obj){
		var id = $(obj).parent().parent().children().first().text();
		var name = $(obj).prev().val();
		if(name != ""){
			// 判断是否存在此员工
			var user_id = "";
			$.each(users_arr, function(){
				if(this['name'] == name){
					user_id = this['id'];
					return false;
				}
			});
			if(user_id == ""){
				showHint("提示信息","查找不到此员工，请重新输入");
				$(obj).prev().focus();
			}else{
				$.ajax({
			        type:'post',
			        dataType:'json',
			        url:'/ajax/editTeam',
			        data:{'team_id':id, 'user_id':user_id},
			        success:function(result){
			          if(result.code == 0){
			          	showHint("提示信息","设置组长成功");
			            setTimeout(function(){location.reload();},1200);
			          }else if(result.code == -1){
			            showHint("提示信息","设置组长失败！");
			          }else if(result.code == -2){
			            showHint("提示信息","参数错误！");
			          }else if(result.code == -3){
			            showHint("提示信息","查找不到该兴趣小组！");
			          }else if(result.code == -4){
			            showHint("提示信息","查找不到该员工！");
			          }else{
			          	showHint("提示信息","你没有权限执行此操作！");
			          }
			        }
			    });
			}
		}else{
			showHint("提示信息","查找不到此员工，请重新输入");
			$(obj).prev().focus();
		}
	}

	// 取消
	function cancelName(obj){
		$(obj).prev().addClass("hidden");
		$(obj).prev().prev().addClass("hidden");
		$(obj).prev().prev().prev().removeClass("hidden");
		$(obj).prev().prev().prev().prev().removeClass("hidden");
		$(obj).addClass("hidden");
	}

	// 修改
	function editName(obj){
		var name = $(obj).prev().text();
		$(obj).next().val(name);
		$(obj).prev().addClass("hidden");
		$(obj).next().removeClass("hidden");
		$(obj).next().next().removeClass("hidden");
		$(obj).next().next().next().removeClass("hidden");
		$(obj).addClass("hidden");
	}

	// 用户数组初始化
	var users_arr = new Array();
	var cn_name_arr = new Array();
	<?php if(!empty($users)): ?>
	<?php foreach($users as $urow): ?>
		users_arr.push({'id':"<?php echo $urow['user_id'];?>", 'name':"<?php echo $urow['cn_name'];?>"});
		cn_name_arr.push("<?php echo $urow['cn_name']; ?>");
	<?php endforeach; ?>
	<?php endif; ?>

	// 页面初始化
	$(document).ready(function(){
		// 自动补全
		$("#team-table").find("input").each(function(){
			$(this).autocomplete({
				source:cn_name_arr
			});
		});
	});
</script>