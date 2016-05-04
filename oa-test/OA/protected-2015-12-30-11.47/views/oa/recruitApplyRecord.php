<?php
echo "<script type='text/javascript'>";
echo "console.log('recruitApplyRecord');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">我的招聘申请</h4>
	<!-- 我的招聘申请 -->
	<table class="table m0 bor-b-1-ddd table-striped">
		<thead>
			<tr>
				<th class="hidden">ID</th>
				<th class="w20 center"></th>
				<th>内容</th>
				<th>状态</th>
				<th class="w200">日期</th>
			</tr>
		</thead>

        <tbody>
        	<?php if(!empty($data)): ?>
			<?php foreach($data as $row): ?>
						<tr>
							<td class="hidden">1</td>
			<?php if($row->status == 'success')
			{
				echo '<td class="w20 center"><span class="glyphicon glyphicon-ok b5c"></span></td>';
			}
			else if($row->status == 'wait')
			{
			    echo '<td class="w20 center"><span class="glyphicon glyphicon-time"></span></td>';
			}
			else
			{
			    echo '<td class="w20 center"><span class="glyphicon glyphicon-remove b2"></span></td>';
			}
			?>
			<td><a href="/oa/recruitApplyDetail/id/<?php echo $row->id;?>/type/recruitApplyRecord"><?php 
			echo "{$row->department}-";
            if($row->type == "replace"){
                echo "编制内替代";
            }else if($row->type == "internal"){
                echo "编制内增补";
            }else if($row->type == "add"){
                echo "编制外增补";
            }
			echo "-【{$row->title}】-{$row->number}人"; 
			?></a></td>
			    <td><?php if($row->status == 'success')
			{
			    echo '已通过';
			}
			else if($row->status == 'wait')
			{
			    echo '待审批';
			}
			else
			{
			    echo '未通过';
			}
			?></td>
							<td><?php echo date('Y-m-d',strtotime($row->create_date)); ?></td>
			            </tr>
			<?php endforeach; ?>
			<?php else: ?>
			<?php echo "<tr><td colspan='4' class='center f18px'>没有记录</td></tr>"; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<!-- 分页 -->
	<div class="pd20">
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
