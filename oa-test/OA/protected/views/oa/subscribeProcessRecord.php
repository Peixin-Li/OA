<?php
echo "<script type='text/javascript'>";
echo "console.log('subscribeProcessRecord');";
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
	<h4 class="pd10 m0 b33 bor-b-1-ddd">审批记录</h4>
	<div class="pd20">
		<!-- 搜索 -->
		<label class="mr10">选择月份:</label>
		<input class="form-control w130 inline pointer" placeholder="请输入月份" id="month-input" value="<?php echo empty($month) ? '':$month;?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)">
		<label class="mr10 ml20">姓名:</label>
		<input class="form-control w130 inline" placeholder="请输入姓名" id="username-input">
		<button class="btn btn-success w80 ml10" onclick="search();">查询</button>
		<!-- 可选标签 -->
		<ul class="nav nav-tabs mt20">
			<li role="presentation" class="<?php if(!empty($status)){if($status == 'wait') echo 'active';}?>"><a href="/oa/subscribeProcessRecord/status/wait">待审批</a></li>
			<li role="presentation" class="<?php if(!empty($status)){if($status == 'success') echo 'active';}?>"><a href="/oa/subscribeProcessRecord/status/success">已通过</a></li>
			<li role="presentation" class="<?php if(!empty($status)){if($status == 'reject') echo 'active';}?>"><a href="/oa/subscribeProcessRecord/status/reject">未通过</a></li>
			<li role="presentation" class="<?php if(!empty($status) && $status == "all"){echo 'active';}else{echo 'hidden';}?>"><a>搜索结果</a></li>
		</ul>
		<!-- 审批记录 -->
		<?php if(!empty($data)): ?>
		<table class="table table-bordered m0 bor-t-none">
			<thead>	
				<tr>
					<th>内容</th>
					<th class="w200">日期</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($data as $row): ?>
				<tr>
                	<td><a href="/oa/subscribeDetail/id/<?php echo $row->id; ?>/type/subscribeProcessRecord">
	                    <?php echo "{$row->user->cn_name}申请购买"; 
		                    if($details = $row->details)
		                    {
		                        foreach($details as $detail)
		                        {
		                            echo "{$detail->quantity}{$detail->name}[".((float)$detail->price)."元]";
		                        }
		                    }
		                ?></a>
	            	</td>
                    <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php else:?>
		<h4 class="center pd20 m0 bor-t-none bor-l-1-ddd bor-b-1-ddd bor-r-1-ddd">没有记录</h4>
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
	});	

	function search(){
		var date_pattern = /^\d{4}-\d{2}$/;
		var user_name = $("#username-input").val();
		var user_id = "";
		if(user_name != ""){
			$.each(users_arr, function(){
				if(this['name'] == user_name) user_id = this['id'];
			});
		}
		var user_str = "";
		if(user_id != ""){
			user_str = "/user_id/"+user_id;
		}

		var month = $('#month-input').val()
		if(month != ""){
			if(!date_pattern.exec(month)){
				showHint("提示信息","月份输入格式错误");
			}else{
				location.href='/oa/subscribeProcessRecord/month/'+month+user_str+"/status/all";
			}
		}else{
			if(user_id == ""){
				showHint("提示信息","请输入搜索条件");
			}else{
				location.href="/oa/subscribeProcessRecord"+user_str+"/status/all";
			}
		}
	}
</script>
