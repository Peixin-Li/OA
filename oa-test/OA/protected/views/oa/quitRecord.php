<?php
echo "<script type='text/javascript'>";
echo "console.log('quitRecord');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">离职申请记录</h4>
	<div class="pd20">
		<!-- 可选标签 -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="<?php if(!empty($status)){if($status == 'wait') echo 'active';}else echo 'active';?>"><a href="/oa/quitRecord/status/wait">待审批</a></li>
			<li role="presentation" class="<?php if(!empty($status)){if($status == 'success') echo 'active';}?>"><a href="/oa/quitRecord/status/success">已通过</a></li>
			<li role="presentation" class="<?php if(!empty($status)){if($status == 'reject') echo 'active';}?>"><a href="/oa/quitRecord/status/reject">未通过</a></li>  
		</ul>
		<!-- 离职申请记录 -->
		<?php if(!empty($data)): ?>
		<table class="table bor-1-ddd m0">
			<thead>
				<tr>
					<th>内容</th>
					<th class="w200">日期</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($data as $row):?>
				<tr>
					<td><a href="/oa/quitDetail/id/<?php echo $row->id;?>/type/quitRecord"><?php echo "{$row->user->cn_name}-{$row->user->department->name}-{$row->user->title}"; ?></a></td>
					<td><?php echo date('Y-m-d', strtotime($row->create_time)); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<?php else:?>
		<h4 class="center bor-1-ddd m0 pd10">没有离职申请记录</h4>
		<?php endif; ?>
		<!-- 分页 -->
		<div class="pd10">
			<div class="w600 m0a">
		    <?php 
		        $this->widget('CLinkPager',array(
		            'firstPageLabel'=>'首页',
		            'lastPageLabel'=>'末页',
		            'prevPageLabel'=>'上一页',
		            'nextPageLabel'=>'下一页',
		            'pages'=>$page,
		            'maxButtonCount'=>9,
		        )
		    );
		    ?>
	    	</div>
		</div>
	</div>
</div>