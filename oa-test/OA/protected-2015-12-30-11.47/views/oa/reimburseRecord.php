<?php
echo "<script type='text/javascript'>";
echo "console.log('reimburseRecord');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">报销记录</h4>
	<div class="pd20">
		<!-- 条件查询 -->
		<label class="mr10">选择月份:</label>
		<input class="form-control w130 inline pointer" placeholder="请输入月份" id="month-input" value="<?php echo empty($month) ? '':$month;?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)">
		<label class="mr10 ml20">部门:</label>
		<select class="form-control w130 inline" id="department-select">
			<option value="all">所有部门</option>
			<?php if(!empty($departments)): ?>
			<?php foreach($departments as $drow): ?>
			<option value="<?php echo $drow['department_id'];?>"><?php echo $drow['name'];?></option>
			<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<label class="mr10 ml20">姓名:</label>
		<input class="form-control w130 inline" placeholder="请输入姓名" id="username-input">
		<label class="mr10 ml20">编号:</label>
		<input class="form-control w130 inline" placeholder="请输入编号" id="no-input" value="<?php echo empty($no) ? '' : $no;?>">
		<button class="btn btn-success w80 ml10" onclick="search();">查询</button>
		<!-- 报销记录 -->
		<?php if(!empty($data)): ?>
		<table class="table table-bordered mt20">
			<thead>	
				<tr class="bg-fa">
					<th class="w50">编号</th>
					<th class="w100">部门</th>
					<th class="w80">姓名</th>
					<th class="w150">报销单</th>
					<th class="w200">报销清单</th>
					<th class="w100">日期</th>
					<th class="w200">操作</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($data as $key=>$row): ?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><?php echo $add_info[$key]['department_name']; ?></td>
                	<td><?php echo $add_info[$key]['cn_name']; ?></td>
            		<td>
            			<a href="/user/printReimburse/id/<?php echo $row['id']; ?>" target="_blank">
            				<?php echo date('Ymd',strtotime($row->create_time)); ?>报销单
            			</a>
            		</td>
            		<td>
            			<a href="/user/printReimburseList/id/<?php echo $row['id']; ?>" target="_blank">
            				<?php foreach($row->details as $key => $drow){echo $drow['content'].(($key == count($row->details)-1) ? '' : "、");};?>
            			</a>
            		</td>
                    <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
                    <td>
                    	<a href="/user/printReimburse/id/<?php echo $row['id']; ?>" target="_blank">【打印报销单】</a>
                    	<a href="/user/printReimburseList/id/<?php echo $row['id']; ?>" class="ml10" target="_blank">【打印清单】</a>
                    </td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php else:?>
			<h4 class="center pd20 m0 bor-1-ddd mt20">没有记录</h4>
		<?php endif; ?>
	<!-- 分页 -->
	<div class="w500 m0a pd20">
        <?php 
            $this->widget('CLinkPager',array(
                'firstPageLabel'=>'首页',
                'lastPageLabel'=>'末页',
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'pages'=>$page,
                'maxButtonCount'=>5,   
            )
        );
        ?>
        </div>
	</div>
</div>

<!-- js -->
<script type="text/javascript">
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
		$("#username-input").autocomplete({
			source: cn_name_arr
		});

		<?php if(!empty($department_id)): ?>
		$("#department-select").val("<?php echo $department_id;?>");
		<?php endif; ?>
	});	

	// 查询
	function search(){
		var date_pattern = /^\d{4}-\d{2}$/;
		var d_pattern = /^\d+$/;
		var month = $("#month-input").val();
		var user_id = "";
		var user_name = $("#username-input").val();
		var no = $("#no-input").val();
		var department_id = $("#department-select").val();

		var month_str = "";
		var user_str = "";
		var no_str = "";

		var f_tag = true;
		if(month && f_tag){
			if(!date_pattern.exec(month)){
				showHint("提示信息","月份输入格式错误");
				f_tag = false;
			}else{
				month_str = "/month/"+month;
			}
		}
		if(user_name != "" && f_tag){
			$.each(users_arr, function(){
				if(this['name'] == user_name) user_id = this['id'];
			});
			if(user_id){
				user_str = "/user_id/"+user_id;
			}else{
				showHint("提示信息","查找不到此用户");
				f_tag = false;
			}
		}
		if(no != "" && f_tag){
			if(!d_pattern.exec(no)){
				showHint("提示信息","编号输入格式错误");
				f_tag = false;
			}else{
				no_str = "/no/"+no;
			}
		}
		if(f_tag){
			location.href = "/oa/reimburseRecord/department_id/"+department_id+user_str+month_str+no_str;
		}
	}
</script>
