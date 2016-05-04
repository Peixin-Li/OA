<?php
echo "<script type='text/javascript'>";
echo "console.log('printSeal');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/user.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />

<style type="text/css">
	/*表格边框样式*/
	.bordered>thead>tr>th, .bordered>tbody>tr>th, .bordered>tfoot>tr>th, .bordered>thead>tr>td, .bordered>tbody>tr>td, .bordered>tfoot>tr>td{
		border: 1px solid black;
	}
	.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td{
		line-height: 2.0;
	}

	/*不打印的区域*/
	@media print { 
	.noprint { display: none;color:green } 
	} 
</style>

<!-- 主界面 -->
<div class="container center">
	<!-- 印鉴使用申请表 -->
	<?php if(!empty($seal)): ?>
	<table class="table bordered center m0a w800">
		<caption>
			<h3 class="black center">印鉴使用申请表</h3>
		</caption>
		<tbody>
			<tr>
				<th class="center w150">用鉴部门</th>
				<td colspan="2" class="w20 left"><?php echo $seal->user->department->name;?></td>
				<th class="center w150">用鉴人</th>
				<td colspan="2" class="w200 left"><?php echo $seal->user->cn_name;?></td>
				<th class="center w150">用鉴时间</th>
				<td class="w200"><?php echo $seal->use_time;?></td>
			</tr>
			<tr>
				<th class="center w150">何种印鉴</th>
				<td colspan="5" class="left">
					<?php 
						$type_str = "";
						//var_dump($seal['type']);
						foreach(explode(',', $seal['type']) as $st_key => $seal_type){
							$str = "";
							if($st_key != count(explode(',', $seal['type']))-1){
								$str = "、";
							}
							if($seal_type == "official"){
								$type_str .= "公章".$str;
							}else if($seal_type == "financial"){
								$type_str .= "财务章".$str;
							}else if($seal_type == "legal"){
								$type_str .= "法人章".$str;
							}
						}
						echo $type_str;
					?>
				</td>
				<th class="center w150">材料份数</th>
				<td><?php echo $seal->number;?></td>
			</tr>
			<tr>
				<th class="center w150">发往单位</th>
				<td colspan="4" class="left"><?php echo $seal->address;?></td>
				<th class="center w150" >文件编号</th>
				<td colspan="3"></td>
			</tr>
			<tr>
				<th class="center w150">用鉴事由</th>
				<td colspan="7" class="left">
					<div style="height:120px;">
						<?php echo $seal->reason;?>
					</div>
				</td>
			</tr>
			<tr>
				<th class="center w150">部门经理<br>审核</th>
				<td class="w150">
					<div style="height:100px;"></div>
				</td>
				<th class="center w150">财务部<br>审核</th>
				<td class="w150"></td>
				<th class="center w150">法务部<br>审核</th>
				<td class="w150"></td>
				<th class="center w150">公司领导<br>审核</th>
				<td class="w150"></td>
			</tr>
			<tr>
				<th class="center w150">备注</th>
				<td colspan="7"></td>
			</tr>
		</tbody>
	</table>
	<!-- 下载附件 -->
	<?php if(!empty($seal->path)): ?>
	<div class="noprint left w800 m0a mt10">
		<label>附件：</label><a href="<?php echo $seal->path; ?>" target="_blank">下载附件</a>
	</div>
	<!-- 打印表格 -->
	<?php endif; ?>
	<button class="btn btn-primary btn-lg noprint w100 mt20" onclick="window.print();">打印</button>
	<?php endif; ?>
</div>
