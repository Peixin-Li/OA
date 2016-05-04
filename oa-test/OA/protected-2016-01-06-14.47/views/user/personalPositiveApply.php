<?php
echo "<script type='text/javascript'>";
echo "console.log('personalPositiveApply');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
	<div class="m0 p0 bor-1-ddd pd20">
		<!-- 标题 -->
		<div>
			<h4 class="pl5 mb15"><strong>我的转正申请</strong></h4>
		</div>
<?php if(!empty($applys)): ?>
		<table class="table table-bordered">
			<thead>
				<tr>
					<th>内容</th>
					<th class="w200">日期</th>
				</tr>
			</thead>
			<tbody>

<?php foreach($applys as $apply): ?>
<?php
switch($apply->status)
{
case 'wait':
    $status = '待审批';
    break;
case 'success':
    $status = '已通过';
    break;
case 'reject':
    $status = '未通过';
    break;
default:
    $status = '延迟转正';
    break;
}
?>
				<tr>
                <td><a href="/user/positiveApplyDetail/id/<?php echo $apply->id;?>"><?php echo "你的转正申请-{$status}";?></a></td>
                    <td><?php echo date('Y-m-d',strtotime($apply->create_time)); ?></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>	
<?php else:?>
	<h4 class="center m0">没有转正申请</h4>
<?php endif; ?>
	</div>
</div>
