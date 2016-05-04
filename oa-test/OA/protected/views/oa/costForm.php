<?php
echo "<script type='text/javascript'>";
echo "console.log('costForm');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">费用报表</h4>
	<div class="pd20">
		<!-- 搜索栏 -->
		<label>年份：</label>
		<select class="form-control inline w130" id="year-select">
			<?php for($i = 2013; $i <= 2050; $i++): ?>
			<option value="<?php echo $i;?>"><?php echo $i;?></option>
			<?php endfor; ?>
		</select>
		<button class="btn btn-success mt-5 w80 ml10" onclick="search();">查询</button>
		<!-- 费用预算报表表格 -->
		<?php if(!empty($report)): ?>
		<table class="table table-bordered mt20 center">
			<thead>
				<tr class="bg-fa">
					<th class="center w200">年份</th>
					<th class="center w200">月份</th>
					<th class="center">报表</th>
				</tr>
			</thead>
			<tbody>
                <?php
                    if($year < date("Y")) {
                        $this_moth = 12;
                    }
                    else {
                        $this_moth = date("m");
                    }
                ?>
				<?php for($i = 1;$i <= $this_moth; $i++): ?>
				<tr>
					<td><?php echo $year; ?></td>
					<td><?php echo $i."月"; ?></td>
					<td>
						<?php if(!empty($this->user) && ($this->user->user_id == $ceo_id || $this->user->user_id == $admin_id || $this->user->user_id == $hr_id || $this->user->user_id == $commissioner_id)): ?>
						<a href="/oa/costFormFirDetail/month/<?php echo $year.'-'.(($i < 10) ? '0'.$i : $i);?>" target="_blank"><?php echo $year.(($i < 10) ? '0'.$i : $i)."一级报表"; ?></a>
						<a href="/oa/costFormSecDetail/month/<?php echo $year.'-'.(($i < 10) ? '0'.$i : $i);?>" class="ml20" target="_blank"><?php echo $year.(($i < 10) ? '0'.$i : $i)."二级报表"; ?></a>
						<?php else: ?>
						<a href="/oa/costFormFirDetailForAdmin/month/<?php echo $year.'-'.(($i < 10) ? '0'.$i : $i);?>" target="_blank"><?php echo $year.(($i < 10) ? '0'.$i : $i)."一级报表"; ?></a>
						<?php endif; ?>
					</td>
				</tr>
				<?php endfor; ?>
			</tbody>
		</table>
		<!-- 若返回信息为空 -->
		<?php else: ?>
		<h4 class="bor-1-ddd center pd20 mt20">没有预算报表</h4>
		<?php endif; ?>
	</div>
</div>

<!--js-->
<script type="text/javascript">
	// 页面初始化
	$(document).ready(function(){
		<?php if(!empty($year)): ?>
		$("#year-select").val("<?php echo $year;?>");
		<?php endif; ?>
	});

	// 查询
	function search(){
		var year = $("#year-select").val();
		location.href = "/oa/costForm/year/"+year;
	}
</script>