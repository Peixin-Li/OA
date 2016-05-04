<?php
echo "<script type='text/javascript'>";
echo "console.log('overtimeList');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>

<!-- 主界面 -->
<div>
  <!-- 标题 -->
  <h4 class="pd10 m0 b33 bor-1-ddd">加班报表统计<span class="ml20 b2 f14px">（提示：每年结束前需更换好新的节假日）</span></h4>
  <!-- 查询条件以及操作 -->
  <div class="bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd pd10">
    <label>月份：</label>
    <input class="form-control inline w150" id="search-month" value="<?php echo (empty($month)) ? date('Y-m', strtotime('-1months')) : $month;?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)">
    <button class="btn btn-success w80 mt-2 ml10" onclick="search();">查询</button>
    <button class="btn btn-success mt-2 ml10 fr" onclick="location.href='/oa/downloadHolidayOvertime/month/<?php echo (empty($month)) ? date('Y-m') : $month;?>'">导出天数报表</button>
    <button class="btn btn-success mt-2 ml10 fr" onclick="location.href='/oa/downloadOvertime/month/<?php echo (empty($month)) ? date('Y-m') : $month;?>'">导出次数报表</button>
  </div>
  <!-- 加班统计表格 -->
  <?php if(!empty($data)): ?>
  <table class="table m0 table-bordered table-striped bor-t-none">
  	<thead>
  		<tr>
  			<th class="w150 center">部门</th>
	  		<th class="w200 center">员工</th>
        <th class="w200 center">工作日(加班次数)</th>
        <th class="w200 center">周末及法定节假日(加班天数)</th>
  		</tr>
  	</thead>
  	<tbody>
      <?php $cur_department = ""; ?>
      <?php foreach($data as $row): ?>
        <?php foreach($row['list'] as $detail):?>
        <tr>
          <?php if($cur_department != $row['department_name']): ?>
          <td class="center" rowspan="<?php echo count($row['list']);?>"><?php echo $row['department_name']; ?></td>
          <?php $cur_department = $row['department_name'];?>
          <?php endif; ?>
  			<td class="center"><?php echo $detail['name'];?></td>
        <td class="center"><?php echo $detail['count'];?></td>
        <td class="center"><?php echo $detail['days'];?></td>
        </tr>
        <?php endforeach; ?>
  		</tr>
      <?php endforeach; ?>
  	</tbody>
  </table>

  <?php else: ?>
  <h4 class="center bor-1-ddd m0 pd20">没有加班记录</h4>
  <?php endif; ?>
</div>

<!-- js -->
<script type="text/javascript">
  // 查询
  function search(){
    var month = $("#search-month").val();
    var month_pattern = /^\d{4}-\d{2}$/;
    if(!month_pattern.exec(month)){
      showHint("提示信息", "月份格式输入错误");
    }else{
      location.href = "/oa/overtimeList/month/"+month;
    }
  }
</script>
