<?php
echo "<script type='text/javascript'>";
echo "console.log('notificationDetail');";
echo "</script>";
?>

<!-- 返回按钮 -->
<div class="left bor-l-1-ddd bor-r-1-ddd">
      <button class="btn btn-default ml10 mt10 f18px" onclick="location.href='/user/notification';"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回</button>
</div>
<div class="pd20 bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd">
	<!-- 标题 -->
	<h4 class="pl5 mb15">
		<strong>公告详情</strong>
	</h4>
	<?php if(!empty($notify)):?>
	<div class="pd20 bor-1-ddd">
		<!-- 公告标题 -->
		<h4 class="center">
			<strong><?php echo $notify['title'];?></strong>
		</h4>
		<!-- 公告内容 -->
		<pre class="show-pre m0 p00 f15px"><?php echo $notify['content'];?></pre>
		<!-- 发布时间 -->
		<h5 class="mt20 right">
			<strong>发布时间：<?php echo $notify['create_time'];?></strong>
		</h5>
	</div>
	<?php else: ?>
	<h4 class="pl5">查找不到该公告的详情</h4>
	<?php endif; ?>
</div>