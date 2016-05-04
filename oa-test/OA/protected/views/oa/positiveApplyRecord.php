<?php
echo "<script type='text/javascript'>";
echo "console.log('positiveApplyRecord');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">转正申请记录</h4>
	<div class="m0 p0 pd20">
		<!-- 可选标签 -->
		<ul class="nav nav-tabs" role="tablist">
	        <li role="presentation" class="<?php if(!empty($status)){if($status == 'wait') echo 'active';}?>"><a class="pointer" onclick="switchTabs(this);">待审批</a></li>
	        <li role="presentation" class="<?php if(!empty($status)){if($status == 'success') echo 'active';}?>"><a class="pointer" onclick="switchTabs(this);">已通过</a></li>
	        <li role="presentation" class="<?php if(!empty($status)){if($status == 'other') echo 'active';}?>"><a class="pointer" onclick="switchTabs(this);">未通过</a></li>
	    </ul>
	    <!-- 转正申请记录 -->
		<?php if(!empty($result)): ?>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>内容</th>
					<th class="w200">日期</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($result as $row): ?>
				<tr>
                	<td><a href="<?php echo "/oa/positiveApplyDetail/id/{$row->id}";?>/type/positiveApplyRecord"><?php echo "{$row->user->cn_name}-{$row->user->department->name}-{$row->user->title}"; ?></a></td>
                	<td><?php echo date('Y-m-d', strtotime($row->create_time)); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php else: ?>
			<h4 class="center bor-1-ddd m0 pd10">没有记录</h4>
		<?php endif; ?>
		<!-- 分页 -->
		<div class="w500 m0a">
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
	// 切换标签
	function switchTabs(obj){
		switch($(obj).text()){
			case "待审批":{
				location.href = "/oa/positiveApplyRecord/status/wait";
				break;
			}
			case "已通过":{
				location.href = "/oa/positiveApplyRecord/status/success";
				break;
			}
			case "未通过":{
				location.href = "/oa/positiveApplyRecord/status/other";
				break;
			}	
		}
	}
</script>
