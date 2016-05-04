<?php
echo "<script type='text/javascript'>";
echo "console.log('mailDetail');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33">邮件详情</h4>
    <!-- 邮件详情表格 -->
	<table class="table m0">
		<tr>
			<th class="w130">发件人</th>
            <td><?php echo $mail->sender_email; ?></td>
		</tr>
		<tr>
			<th class="w130">收件人</th>
            <td><?php echo $mail->receive_email; ?></td>
		</tr>
		<tr>
			<th class="w130">发送状态</th>
            <?php 
                if(empty($mail->status))
                {
                        echo "<td>等待发送</td>";
                }
                else if($mail->status == 'success')
                {
                        echo "<td>发送成功</td>";
                }
                else if($mail->status == 'wait')
                {
                        echo "<td>等待发送</td>";
                }
                else if($mail->status == 'fail ')
                {
                        echo "<td>发送失败</td>";
                }
            ?>
		</tr>
		<tr>
			<th class="w130">创建时间</th>
            <td><?php echo $mail->create_time; ?></td>
		</tr>
		<tr>
			<th class="w130">邮件主题</th>
            <td><?php echo $mail->subject; ?></td>
		</tr>
		<tr>
			<th class="w130">正文</th>
            <td><?php echo $mail->message; ?></td>
		</tr>
	</table>
</div>
