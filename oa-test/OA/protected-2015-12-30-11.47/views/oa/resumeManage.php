<?php
echo "<script type='text/javascript'>";
echo "console.log('resumeManage');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">简历存档</h4>
	<!-- 快速搜索 -->
	<div class="bor-l-1-ddd bor-r-1-ddd pd10 bg-fa">
		<label class="fl mt5 ml100">快速搜索</label>
		<div class="input-group w500 fl ml10">
			<div class="input-group-btn">
				<button class="btn btn-default dropdown-toggle w80" data-toggle="dropdown" id="search-condition-btn">
					<?php 
						if(!empty($name)){
							echo "姓名";
						}else if(!empty($title)){
							echo "职位";
						}else if(!empty($department)){
							echo "部门";
						}else{
							echo "职位";
						}
					?>
					&nbsp;
					<span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
		          <li><a class="pointer" onclick="searchCondition(this);">职位</a></li>
		          <li><a class="pointer" onclick="searchCondition(this);">姓名</a></li>
		          <li><a class="pointer" onclick="searchCondition(this);">部门</a></li>
		        </ul>
			</div>
			<input class="form-control" id="search-input" placeholder="请输入搜索条件" value="<?php 
						if(!empty($name)){
							echo $name;
						}else if(!empty($title)){
							echo $title;
						}else if(!empty($department)){
							echo $department;
						}else{
							echo "";
						}
					?>">	
		</div><!-- 搜索条 -->
		<button class="btn btn-success w80 ml10" onclick="search();">查询</button>
		<button class="btn btn-success w80 ml10" onclick="location.href='/oa/resumeManage';">刷新&nbsp;<span class="glyphicon glyphicon-refresh"></span></button>
		<div class="clear"></div>
	</div>
	<!-- 简历列表 -->
	<table class="table bor-1-ddd m0 table-striped" id="resume-table">
		<thead>
			<tr>
				<th class="w20 hidden" id="checkbox-th"></th>
				<th>简历名</th>
				<th class="w200">上传日期</th>
			</tr>
		</thead>
		<tbody>
			<?php if(!empty($data)): ?>
			<?php foreach($data as $row):?>
			<tr>
				<td class="center hidden"><input type="checkbox" name="checkbox"></td>
				<td><a target="_blank" href="<?php echo '/oa/viewResume/id/'.$row->id; ?>"><?php echo "{$row['name']}-{$row->apply->title}-{$row->apply->department}"; ?></a> <a target="_blank" href="<?php echo "/oa/downloadResume/id/{$row->id}";?>" class="ml10">下载</a></td>
				<td><?php echo date('Y-m-d', strtotime($row['create_time']));?></td>
			</tr>
			<?php endforeach; ?>
			<?php else: ?>
			<tr>
				<td colspan="2" class="center">没有简历存档</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<!-- 操作 -->
	<div class="bg-fa bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd pd10">
		<button class="btn btn-success w80 pd5" onclick="showCheckbox();" id="showCheckbox-btn">批量下载</button>
		<button class="btn btn-success w80 pd5 hidden" onclick="downloadAny();" id="downloadAny-btn">下载</button>
		<button class="btn btn-default w80 pd5 hidden" onclick="hideCheckbox();" id="hideCheckbox-btn">取消</button>
	</div>	
</div>

<!-- js -->
<script type="text/javascript">
	// 显示多选框
	function showCheckbox(){
		$("#checkbox-th").removeClass("hidden");
		$("#downloadAny-btn").removeClass("hidden");
		$("#hideCheckbox-btn").removeClass("hidden");
		$("#showCheckbox-btn").addClass("hidden");
		$("input[name='checkbox']").parent().removeClass("hidden");
	}

	// 隐藏多选框
	function hideCheckbox(){
		$("#checkbox-th").addClass("hidden");
		$("#downloadAny-btn").addClass("hidden");
		$("#hideCheckbox-btn").addClass("hidden");
		$("#showCheckbox-btn").removeClass("hidden");
		$("input[name='checkbox']").parent().addClass("hidden");
	}

	// 批量下载
	function downloadAny(){
		$("input[name='checkbox']:checked").each(function(){
			var href_str = $(this).parent().parent().find("a").first().next().attr("href");
			window.open(href_str);
		});
	}

	// 改变搜索条件
	var name_arr = new Array();
	<?php foreach($names as $row){echo "name_arr.push('{$row['name']}');";}?>
	var title_arr = new Array();
	<?php foreach($titles as $row){echo "title_arr.push('{$row}');";}?>
	var department_arr = new Array();
	<?php foreach($departments as $row){echo "department_arr.push('{$row}');";}?>
	<?php 
		if(!empty($name)){
			echo "var search_condition = 'name';";
			echo "$('#search-input').autocomplete({source: name_arr});";
		}else if(!empty($title)){
			echo "var search_condition = 'title';";
			echo "$('#search-input').autocomplete({source: title_arr});";
		}else if(!empty($department)){
			echo "var search_condition = 'department';";
			echo "$('#search-input').autocomplete({source: department_arr});";
		}else{
			echo "var search_condition = 'title';";
			echo "$('#search-input').autocomplete({source: title_arr});";
		}
	?>
	function searchCondition(obj){
		var click_obj = $(obj).text();
		switch(click_obj){
			case "职位":{
				search_condition = "title";
				$("#search-condition-btn").html("职位&nbsp;<span class='caret'></span>");
				$("#search-input").autocomplete({
				    source: title_arr
				});
				break;
			}
			case "姓名":{
				search_condition = "name";
				$("#search-condition-btn").html("姓名&nbsp;<span class='caret'></span>");
				$("#search-input").autocomplete({
				    source: name_arr
				});
				break;
			}
			case "部门":{
				search_condition = "department";
				$("#search-condition-btn").html("部门&nbsp;<span class='caret'></span>");
				$("#search-input").autocomplete({
				    source: department_arr
				});
				break;
			}
		}
	}

	// 查询
	function search(){
		var search_str = $("#search-input").val();
		if(search_str == ""){
			showHint("提示信息","请输入搜索条件！");
		}else{
			var href_str = "/oa/resumeManage/"+search_condition+"/"+search_str;
			location.href = href_str;
		}
	}
	
</script>