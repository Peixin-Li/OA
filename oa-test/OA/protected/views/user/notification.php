<?php
echo "<script type='text/javascript'>";
echo "console.log('notification');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="pd20 bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pl5 mb15">
		<strong>公告</strong>
	</h4>
	<!-- 公告表格 -->
	<?php if(!empty($notifys)): ?>
	<table class="table table-bordered m0 center">
		<thead>
			<tr class="bg-fa">
				<th class="center w130">类型</th>
				<th class="center">标题</th>
				<th class="center w130">发布日期</th>
			</tr>
		</thead>
		<tbody>
        <?php foreach($notifys as $row): ?>
			<tr> 
            <td>
            	<?php 
            		$type = "";
	            	switch($row['type']){
			  			case "other":{$type = "其他通知";break;}
			  			case "admin":{$type = "行政通知";break;}
			  			case "holiday":{$type = "放假通知";break;}
			  			case "internal":{$type = "内部悬赏";break;}
			  			case "activity":{$type = "活动通知";break;}
			  			case "appointments":{$type = "人事任命";break;}
			  		}
            		echo $type; 
            	?>
            </td>
            <td><a href="/user/notificationDetail/id/<?php echo $row->id; ?>"><?php echo $row->title; ?></a></td>
				<td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
			</tr>
        <?php endforeach; ?>
		</tbody>
	</table>
	
	<?php else: ?>
	<h4 class="pl5">目前没有公告</h4>
	<?php endif; ?>
</div>
