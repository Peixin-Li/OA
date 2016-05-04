<?php
echo "<script type='text/javascript'>";
echo "console.log('deliverWorkRecord');";
echo "</script>";
?>

<!--主界面-->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">工作交接记录</h4>
    <!-- 工作交接记录表格 -->
    <?php if(!empty($data)): ?>
	    <table class="table m0 table-striped table-hover">
		    <thead>
			    <tr>
				    <th>内容</th>
				    <th class="w200">日期</th>
			    </tr>
		    </thead>
		    <tbody>
            <?php foreach($data as $row): ?>
			    <tr>
                    <td><a href="/oa/deliverWorkDetail/id/<?php echo $row->id; ?>"><?php echo "{$row->user->cn_name}-{$row->user->department->name}-{$row->user->title}-";if($row->status == 'success') { echo "已完成交接";} else{echo '进行中';}; ?></a></td>
                    <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
			    </tr>
    <?php endforeach; ?>
		    </tbody>
	    </table>
    <?php else: ?>
	    <h4 class="center m0 pd10">没有离职申请记录</h4>
    <?php endif; ?>
</div>
