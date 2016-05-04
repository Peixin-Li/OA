<?php
echo "<script type='text/javascript'>";
echo "console.log('costFormSecDetail');";
echo "</script>";
?>

<!--js-->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/user.js"></script>
<!--css-->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />

<style type="text/css">
	table{
		font-size: 10px;
	}

	.table-bordered{
		border-bottom: none;
	}
</style>

<!-- 主界面 -->
<div>
	<div>
        <!-- 报表上方 -->
		<?php if(!empty($category) && !empty($reports)): ?>
		<div class="w1300 m0a">
			<h3 class="m0 pd20 center black"><?php echo date('Y年m月', strtotime($month));?>公司费用申请清单</h3>
			<div class="pd10" style="margin-top:-60px;">
				<label>一级分类：</label>
				<select class="form-control inline w200" onchange="typeChange();" id="type-select">
					<?php foreach ($reports as $select_key => $select_row): ?>
					<option value="<?php echo $select_key; ?>"><?php echo $category[$select_key]; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
        <!-- 报表 -->
		<div class="w1300 m0a">
			<table class="table table-bordered center w1300">
				<tbody>
					<?php foreach ($reports as $key => $rrow): // 一级大类的循环 ?>
					<tr class="bg-fa" name="<?php echo $key;?>-tr">
						<th class="center">项目</th>
						<th class="center">部门</th>
						<th class="center">项目</th>
						<th class="center">名称</th>
						<th class="center">单价</th>
						<th class="center">数量</th>
						<th class="center">金额(元)</th>
						<th class="center">经手人</th>
						<th class="center">日期</th>
						<th class="center">发票</th>
						<th class="center">报销金额</th>
						<th class="center">报销日期</th>
					</tr>
					<?php $first_total = 0; $first_r_total = 0;// 一级大类总计 ?>

					<?php $th_tag = false; ?>

					<?php $th_index = 0;foreach($rrow as $dkey => $detail_row){foreach($detail_row as $departments_row){foreach($departments_row as $depart_row_num){$th_index++;}}}?>
					
					<?php 
						$department_public_count = 0;
						foreach($rrow as $department_key => $department_row){
							if($department_key == "总经理办公室" || $department_key == "人事行政部" || $department_key == "商务部" || $department_key == "IT运维部" || $department_key == "项目管理部"){
								$department_public_count ++ ;
							}
						}
					?>

					<?php foreach($rrow as $department_key => $department_row): // 部门的循环  ?>

					<?php $department_total = 0; // 部门总计 ?>


					<?php $department_th_index = 0;foreach($department_row as $dkey => $detail_row){foreach($detail_row as $depart_th_row){$department_th_index++;}} $department_th_tag = false;  // 部门的行数 ?>
					
					<?php foreach($department_row as $dkey => $detail_row): // 小类的循环 ?>

					<?php $type_th_index = 0;$type_th_index = count($detail_row); $type_th_tag = false;?>
					
					<?php foreach($detail_row as $dddrow): // 每一条记录的循环 ?>
					<tr class="<?php echo $department_key; ?>-tr" name="<?php echo $key;?>-tr">
						<?php if(!$th_tag): ?>
						<td rowspan="<?php echo ($department_public_count > 1) ? $th_index+1+count($rrow)-$department_public_count+1 : $th_index+1+count($rrow); ?>" name="<?php echo $key;?>-category-td"><?php echo $category[$key];?></td><!-- 一级大类 -->
						<?php $th_tag = true;?>
						<?php endif; ?>
						<?php if(!$department_th_tag): ?>
						<td class="department-name" name="<?php echo $key;?>-department-td" rowspan="<?php echo $department_th_index; ?>">
							<?php 
								$department_name =  $department_key;
								if($department_name == "总经理办公室" || $department_name == "人事行政部" || $department_name == "商务部" || $department_name == "IT运维部" || $department_name == "项目管理部"){
									$department_name = "公共部门";
								}
								echo $department_name;
								$department_th_tag = true;
							?>
						</td><!-- 部门名称 -->
						<?php endif; ?>
						<?php if(!$type_th_tag): ?>
						<td class="<?php echo $department_name;?>-type" name="<?php echo $key;?>-type-td" rowspan="<?php echo $type_th_index;$type_th_tag=true;?>">
							<?php echo ($dkey != "key") ? $dkey : ''; ?>
						</td><!-- 二级类名称 -->
						<?php endif; ?>
						<td><?php echo $dddrow->applyDetail['name']; ?></td>
						<td><?php echo $dddrow->applyDetail['price']; ?></td>
						<td><?php echo $dddrow->applyDetail['quantity']; ?></td>
						<td name="money-td"><?php 
								echo $dddrow->applyDetail['price'] * $dddrow->applyDetail['quantity']; 
								$department_total += $dddrow->applyDetail['price'] * $dddrow->applyDetail['quantity'];  
								$first_total += $dddrow->applyDetail['price'] * $dddrow->applyDetail['quantity'];  
							?>
						</td>
						<td><?php echo $dddrow->reimburse->user->cn_name; ?></td>
						<td><?php echo date('Y年m月d日', strtotime($dddrow->applyDetail['create_time'])); ?></td>
						<td><?php echo ($dddrow['have_receipt'] == "yes") ? '有' : '无' ;?></td>
						<td name="amount-td"><?php echo $dddrow['amount']; $first_r_total+=$dddrow['amount']; ?></td>
						<td><?php echo date('Y年m月d日', strtotime($dddrow['create_time'])); ?></td>
					</tr>
					<?php endforeach; ?>
					<?php endforeach; ?>
					<!-- <tr class="<?php //echo $department_name?>-summary-tr" name="<?php //echo $key;?>-tr" style="background:#FFFF99;">
						<th class="center" colspan="2">合计</th>
						<td colspan="3"></td>
						<td class="department-summary-td"><?php //echo $department_total; ?></td>
						<td colspan="5"></td>
					</tr> -->
					<?php endforeach; ?>
					<tr name="<?php echo $key;?>-tr" class="bg-66 white">
						<th class="center" colspan="5">总计</th>
						<td><?php echo $first_total; ?></td>
						<td colspan="3"></td>
						<td><?php echo $first_r_total; ?></td>
						<td></td>
					</tr><!-- 一级大类总计 -->
					<!-- <tr name="<?php //echo $key;?>-tr">
						<td colspan="12"></td>
					</tr> --><!-- 分隔行 -->
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else: ?>
			<h4 class="m0 pd20 bor-1-ddd center">没有数据</h4>
			<?php endif; ?>
		</div>
	</div>
</div>

<!--js-->
<script type="text/javascript">
	$(document).ready(function(){
		publicToTheFirst();
		publicSpan();
		typeSpan();
		secondSpan();
		totalCal();
		typeChange();
	});

	var category_arr = new Array();
	category_arr.push('office');
	category_arr.push('welfare');
 	category_arr.push('travel');
	category_arr.push('entertain');
	category_arr.push('hydropower');
 	category_arr.push('intermediary');
 	category_arr.push('rental');
	category_arr.push('test');
 	category_arr.push('outsourcing');
 	category_arr.push('property');
 	category_arr.push('repair');
 	category_arr.push('other');

 	// 计算各部门总和
 	function totalCal(){
 		$("td.department-name").each(function(){
 			var type = $(this).attr("name").split("-department-td")[0];
 			var total = 0;
 			var amount_total = 0;
 			var row_num = parseInt($(this).attr("rowspan"));
 			var tr_obj = $(this).parent();
 			for(var i =0; i < row_num;i++){
 				var num = parseFloat($(tr_obj).find("td[name='money-td']").text());
 				var amount_num = parseFloat($(tr_obj).find("td[name='amount-td']").text());
 				total = accAdd(total, num);
 				amount_total = accAdd(amount_total, amount_num);
 				tr_obj = $(tr_obj).next();
 			}
 			var str = "<tr name='"+type+"-tr' style='background:#FFFF99;'>"+
					"<th class='center' colspan='2'>合计</th>"+
					"<td colspan='3'></td>"+
					"<td class='department-summary-td'>"+total+"</td>"+
					"<td colspan='3'></td>"+
					"<td>"+amount_total+"</td>"+
					"<td></td>"+
				"</tr>";
 			$(tr_obj).before(str);
 		});
 	}


	// 分类切换
	function typeChange(){
		var type = $("#type-select").val();
		$("tr").each(function(){
			var name = $(this).attr("name").split("-tr")[0];
			if(name == type){
				$(this).removeClass("hidden");
			}else{
				$(this).addClass("hidden");
			}
		});
	}

	// 公共部门提到最上层
	function publicToTheFirst(){
		var last_department = "";
		var last_type = "";
		$("tr").each(function(){
			var class_str = $(this).attr("class");
			if(class_str){
				if(class_str.indexOf("商务部") >=0 || class_str.indexOf("总经理办公室") >=0 ||class_str.indexOf("人事行政部") >=0 ||class_str.indexOf("IT运维部") >=0 ||class_str.indexOf("项目管理部") >=0){
					var department_name = class_str.split("-tr")[0];
					var type = $(this).attr("name").split("-tr")[0];
					if(last_type == ""){
						last_type = type;
					}else if(last_type != type){  // 遇到了不一样的类型
						last_type = type;
						last_department = "";
					}

					if(last_department == ""){   
						last_department = department_name;
					}else if(last_department != department_name){  // 遇到了不一样的部门
						// 在同类型中找第一个公共部门的最后一行
						var last_obj = "";
						$("tr[name='"+type+"-tr']").each(function(){
							if($(this).attr("class") && $(this).attr("class").split("-tr")[0] == last_department){
								last_obj = this;
							}
						});
						var de_array = $("tr."+department_name+"-tr");
						$("tr."+department_name+"-tr").each(function(){
							var class_str = $(this).attr("class");
							var name_str = $(this).attr("name");
							var d_type = name_str.split("-tr")[0];
							if(d_type == type){
								$(last_obj).after("<tr class='"+class_str+"' name='"+name_str+"'>"+$(this).html()+"</tr>");
								last_obj = $(last_obj).next();
								$(this).remove();
							}
						});

						last_department = department_name;
					}
				}
			}
		});
	}

	// 合并公共部门表头
	function publicSpan(){
		$.each(category_arr,function(){
			var type = this;
			var row_num = 0;
			var first_tag = false;
			$("tr[name='"+type+"-tr']").each(function(){
				if($(this).find("td.department-name")){
					if($(this).find("td.department-name").text().indexOf("公共部门") >=0 ){
						row_num += parseInt($(this).find("td.department-name").attr("rowspan"));
					}
				}
			});
			$("tr[name='"+type+"-tr']").each(function(){
				if($(this).find("td.department-name")){
					if($(this).find("td.department-name").text().indexOf("公共部门") >=0 ){
						if(!first_tag){
							$(this).find("td.department-name").attr("rowspan",row_num);
							first_tag = true;
						}else{
							$(this).find("td.department-name").remove();
						}
					}
				}
			});
		});
	}

	// 二级类型数组
	var office_arr = new Array("快递费","招聘费","通讯费", "交通费", "网络费","办公设备","办公软件","办公用品","其他");
	var welfare_arr = new Array("加班费", "图书", "工作餐", "下午茶", "生日礼物", "生日会", "婚育礼物", "兴趣小组", "部门经费", "旅游经费", "体检费", "培训费", "游戏经费", "年会费用", "其他");

	// 合并二级类
	function typeSpan(){
		// 合并福利费
		$.each(welfare_arr, function(key, value){
			var type = value;
			var first_obj = "";
			var first_tag = false;
			$("tr[name='welfare-tr']").each(function(){ // 遍历福利费的行
				if($(this).find("td[name='welfare-type-td']") && typeof($(this).find("td[name='welfare-type-td']").attr("class")) != "undefined"){  // 如果找到了头的话
					if($(this).find("td[name='welfare-type-td']").attr("class").indexOf("公共部门") > -1){
						var type_name = $(this).find("td[name='welfare-type-td']").text();
						type_name = type_name.replace(/\s*/g, "");
						if(type_name.indexOf(type) >= 0){ // 找到了该二级类
							if(!first_tag){  // 如果已经是第一次找到的话
								var obj = $(this); 
								var num_row = parseInt($(this).find("td[name='welfare-type-td']").attr("rowspan"));
								for(var i = 1;i < num_row;i++){
									obj = $(obj).next();
								}
								first_obj = obj; 
							}else{
								var tr_obj = this;
								var row_span = parseInt($(this).find("td[name='welfare-type-td']").attr("rowspan"));
								for(var i = 1;i <= row_span; i++){
									var content = $(tr_obj).html();
									var class_str = $(tr_obj).attr("class");
									var name_str = $(tr_obj).attr("name");
									tr_obj = $(tr_obj).next();
									$(tr_obj).prev().remove();
									$(first_obj).after("<tr class='"+class_str+"' name='"+name_str+"'>"+content+"</tr>");
									first_obj = $(first_obj).next();

								}
							}
						}else{ 
							if(first_obj != ""){
								first_tag = true;
							}
						}
					}
				}
			});
		});

		// 合并办公费
		$.each(office_arr, function(key,value){
			var type = value;
			var first_obj = "";
			var first_tag = false;
			$("tr[name='office-tr']").each(function(){ // 遍历福利费的行
				if($(this).find("td[name='office-type-td']") && typeof($(this).find("td[name='office-type-td']").attr("class")) != "undefined"){  // 如果找到了头的话
					if($(this).find("td[name='office-type-td']").attr("class").indexOf("公共部门") > -1){
						var type_name = $(this).find("td[name='office-type-td']").text();
						type_name = type_name.replace(/\s*/g, "");
						if(type_name.indexOf(type) >= 0){ // 找到了该二级类
							if(!first_tag){  // 如果已经是第一次找到的话
								var obj = $(this); 
								var num_row = parseInt($(this).find("td[name='office-type-td']").attr("rowspan"));
								for(var i = 1;i < num_row;i++){
									obj = $(obj).next();
								}
								first_obj = obj; 
							}else{
								var tr_obj = this;
								var row_span = parseInt($(this).find("td[name='office-type-td']").attr("rowspan"));
								for(var i = 1;i <= row_span; i++){
									var content = $(tr_obj).html();
									var class_str = $(tr_obj).attr("class");
									var name_str = $(tr_obj).attr("name");
									tr_obj = $(tr_obj).next();
									$(tr_obj).prev().remove();
									$(first_obj).after("<tr class='"+class_str+"' name='"+name_str+"'>"+content+"</tr>");
									first_obj = $(first_obj).next();
								}
							}
						}else{ 
							if(first_obj != ""){
								first_tag = true;
							}
						}
					}
				}
			});
		});
	}

	// 合并二级类别表头
	function secondSpan(){
		$.each(welfare_arr,function(key, value){
			var type = value;
			var row_num = 0;
			$("tr[name='welfare-tr']").each(function(){
				$(this).find("td[name='welfare-type-td']").each(function(){
					if(typeof($(this).attr("class")) != "undefined" && $(this).attr("class").indexOf("公共部门") > -1){
						var td_type = $(this).text();
						td_type = td_type.replace(/\s*/g, "");
						if(td_type == type){
							var row_span = parseInt($(this).attr("rowspan"));
							row_num += row_span;
						}
					}
				});
			});
			var fir_tag = false;
			$("tr[name='welfare-tr']").each(function(){
				$(this).find("td[name='welfare-type-td']").each(function(){
					if(typeof($(this).attr("class")) != "undefined" && $(this).attr("class").indexOf("公共部门") > -1){
						var td_type = $(this).text();
						td_type = td_type.replace(/\s*/g, "");
						if(td_type == type){
							if(!fir_tag){
								$(this).attr("rowspan",row_num);
								fir_tag = true;
							}else{
								$(this).remove();
							}
						}
					}
				});
			});
		});

		$.each(office_arr,function(key, value){
			var type = value;
			var row_num = 0;
			$("tr[name='office-tr']").each(function(){
				$(this).find("td[name='office-type-td']").each(function(){
					if(typeof($(this).attr("class")) != "undefined" && $(this).attr("class").indexOf("公共部门") > -1){
						var td_type = $(this).text();
						td_type = td_type.replace(/\s*/g, "");
						if(td_type == type){
							var row_span = parseInt($(this).attr("rowspan"));
							row_num += row_span;
						}
					}
				});
			});
			var fir_tag = false;
			$("tr[name='office-tr']").each(function(){
				$(this).find("td[name='office-type-td']").each(function(){
					if(typeof($(this).attr("class")) != "undefined" && $(this).attr("class").indexOf("公共部门") > -1){
						var td_type = $(this).text();
						td_type = td_type.replace(/\s*/g, "");
						if(td_type == type){
							if(!fir_tag){
								$(this).attr("rowspan",row_num);
								fir_tag = true;
							}else{
								$(this).remove();
							}
						}
					}
				});
			});
		});
	}

	// 精确加法
	function accAdd(arg1,arg2){  
		var r1,r2,m;  
		try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}  
		try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}  
		m=Math.pow(10,Math.max(r1,r2))  
		return (arg1*m+arg2*m)/m  
	}
</script>