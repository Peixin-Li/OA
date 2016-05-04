<?php
echo "<script type='text/javascript'>";
echo "console.log('personalQuitRecord');";
echo "</script>";
?>

<div>
	<div class="pd20 bor-1-ddd">
		<!-- 标题 -->
		<h4 class="mb15 pl5">
			<strong>我的离职申请</strong>
		</h4>
		<!-- 我的离职申请表格 -->
		<?php if(!empty($data)): ?>
		<table class="table bor-1-ddd m0">
			<thead>
				<tr>
					<th>内容</th>
					<th class="w200">日期</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($data as $key => $row): ?>
				<tr>
                    <td>
                    	<a href="/user/quitDetail/id/<?php echo $row->id; ?>">你的离职申请-<?php if($row->status == 'success'){ echo '已完成'; } else if($row->status == 'reject') { echo '未通过'; } else { echo '待审批'; };  ?>
                    	</a>
                    	<?php if( $quit_handover_info[$key] ): ?>
                    		<a href="/user/deliverWorkDetail/id/<?php echo $row->id; ?>" class="ml20">查看工作交接表</a>
                    	<?php endif; ?>
                    </td>
                    <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else:?>
	<h4 class="center bor-1-ddd m0 pd10">没有离职申请记录</h4>
	<?php endif; ?>
	</div>
</div>
