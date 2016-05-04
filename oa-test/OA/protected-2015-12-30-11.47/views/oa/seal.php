<?php
echo "<script type='text/javascript'>";
echo "console.log('seal');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/ajaxupload.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">印鉴申请<button class="btn btn-success pd3 w80 ml10 " onclick="showNewSeal();">申请印鉴表</button></h4>
	<!-- 查询 -->
	<div class="pd20 bor-l-1-ddd bor-r-1-ddd">
		<label>部门：</label>
		<select class="form-control inline w150" id="department-select">
			<option value="all">所有部门</option>
			<?php if(!empty($departments)): ?>
			<?php foreach($departments as $drow): ?>
			<option value="<?php echo $drow['department_id']; ?>"><?php echo $drow['name']; ?></option>
			<?php endforeach; ?>
			<?php endif;  ?>
		</select>
		<label class="ml20">姓名：</label>
		<input class="form-control inline w150" id="user-input" placeholder="请输入中文名">
		<button class="btn btn-success w80 ml20" onclick="search();">查询</button>
	</div>
	<!-- 印鉴申请表 -->
	<?php if(!empty($seals)): ?>
	<table class="table table-bordered m0 center">
		<tbody>
			<tr class="bg-fa">
				<th class="center">部门</th>
				<th class="center">员工</th>
				<th class="center">使用印章</th>
				<th class="center">用鉴理由</th>
				<th class="center">用鉴日期</th>
				<th class="center">操作</th>
			</tr>
			<?php foreach($seals as $srow): ?>
			<tr>
				<td class="hidden"><?php echo $srow['id']; ?></td>
				<td><?php echo $srow->user->department->name; ?></td>
				<td><?php echo $srow->user->cn_name; ?></td>
				<td>
					<?php 
						$type_str = "";
						foreach(explode(',', $srow['type']) as $st_key => $seal_type){
							$str = "";
							if($st_key != count(explode(',', $srow['type']))-1){
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
				<td class="w700"><?php echo $srow['reason']; ?></td>
				<td><?php echo $srow['use_time']; ?></td>
				<td class="hidden"><?php echo $srow['address']; ?></td>
				<td class="hidden"><?php echo $srow['number']; ?></td>
				<td class="hidden"><?php echo $srow['type']; ?></td>
				<td class="hidden"><?php echo $srow['path']; ?></td>
				<td>
					<?php if($srow['user_id'] == $this->user->user_id): ?>
					<a class="pointer mr10" onclick="showEditSeal(this);">修改</a>
					<?php endif; ?>
					<a href="/oa/printSeal/id/<?php echo $srow['id']; ?>" target="_blank">打印</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<!-- 分页 -->
	<div class="w600 m0a pd20 center">
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

	<?php else: ?>
	<h4 class="center pd20 m0 bor-1-ddd">没有印鉴申请记录</h4>
	<?php endif; ?>
</div>

<!-- 新增印鉴表模态框 -->
<div id="new-seal-div" class="modal fade in hint bor-rad-5 w600" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">申请印鉴表</h4>
    </div>
    <div class="modal-body">
        <table class="table table-bordered m0">
			<tr>
				<th class="center w150">用鉴时间</th>
				<td><input class="form-control w150" id="new-date" value="<?php echo date('Y-m-d', strtotime('+1days'));?>"></td>
			</tr>
			<tr>
				<th class="center w150">印鉴</th>
				<td>
					<input type="checkbox" name="seal-checkbox" value="official">&nbsp;<span class="pointer" onclick="$(this).prev().click();">公章</span>&nbsp;&nbsp;
					<input type="checkbox" name="seal-checkbox" value="financial">&nbsp;<span class="pointer" onclick="$(this).prev().click();">财务章</span>&nbsp;&nbsp;
					<input type="checkbox" name="seal-checkbox" value="legal">&nbsp;<span class="pointer" onclick="$(this).prev().click();">法人章</span>
				</td>
			</tr>
			<tr>
				<th class="center w150">材料份数</th>
				<td><input class="form-control inline w50" id="new-num">&nbsp;份</td>
			</tr>
			<tr>
				<th class="center w150">发往单位</th>
				<td><input class="form-control" id="new-company" placeholder="请输入发往单位名称，可选"></td>
			</tr>
			<tr>
				<th class="center w150">用鉴事由</th>
				<td><textarea class="form-control" id="new-reason" placeholder="请输入用鉴事由" rows="3"></textarea></td>
			</tr>
			<tr>
				<th class="center w150">附件(可选)</th>
				<td><input type="file" id="attachment"></td>
			</tr>
        </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendNewSeal();">提交</button>
    </div>
</div>

<!-- 修改印鉴表模态框 -->
<div id="edit-seal-div" class="modal fade in hint bor-rad-5 w600" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">修改印鉴表</h4>
    </div>
    <div class="modal-body">
        <table class="table table-bordered m0">
			<tr>
				<th class="center w150">用鉴时间</th>
				<td><input class="form-control w150" id="edit-date" value="<?php echo date('Y-m-d', strtotime('+1days'));?>"></td>
			</tr>
			<tr>
				<th class="center w150">印鉴</th>
				<td>
					<input type="checkbox" name="edit-seal-checkbox" value="official">&nbsp;<span class="pointer" onclick="$(this).prev().click();">公章</span>&nbsp;&nbsp;
					<input type="checkbox" name="edit-seal-checkbox" value="financial">&nbsp;<span class="pointer" onclick="$(this).prev().click();">财务章</span>&nbsp;&nbsp;
					<input type="checkbox" name="edit-seal-checkbox" value="legal">&nbsp;<span class="pointer" onclick="$(this).prev().click();">法人章</span>
				</td>
			</tr>
			<tr>
				<th class="center w150">材料份数</th>
				<td><input class="form-control inline w50" id="edit-num">&nbsp;份</td>
			</tr>
			<tr>
				<th class="center w150">发往单位</th>
				<td><input class="form-control" id="edit-company" placeholder="请输入发往单位名称"></td>
			</tr>
			<tr>
				<th class="center w150">用鉴事由</th>
				<td><textarea class="form-control" id="edit-reason" placeholder="请输入用鉴事由" rows="3"></textarea></td>
			</tr>
			<tr>
				<th class="center w150">附件</th>
				<td id="edit-attachment-show"></td>
			</tr>
			<tr>
				<th class="center w150">重新上传(可选)</th>
				<td><input type="file" id="edit-attachment"></td>
			</tr>
        </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendEditSeal();">提交</button>
    </div>
</div>

<!-- 新增印鉴表成功模态框 -->
<div id="new-seal-remind-div" class="modal fade in hint bor-rad-5 w400" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="location.reload();">×</a>
        <h4 class="hint-title">提示信息</h4>
    </div>
    <div class="modal-body center f18px">
        新增印鉴成功
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="goTo();" id="goto-btn" name="" data-dismiss='modal'>查看印鉴表</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
	function goTo(){
		var id = $("#goto-btn").attr("name");
		var str = "/oa/printSeal/id/"+id;
		window.open(str);
	}

	// 用户数组初始化
	var users_arr = new Array();
	var cn_name_arr = new Array();
	<?php if(!empty($users)): ?>
	<?php foreach($users as $urow): ?>
		users_arr.push({'id':"<?php echo $urow['user_id'];?>", 'name':"<?php echo $urow['cn_name'];?>"});
		cn_name_arr.push("<?php echo $urow['cn_name']; ?>");
	<?php endforeach; ?>
	<?php endif; ?>

	// 页面初始化
	$(document).ready(function(){
		$("#user-input").autocomplete({
			source: cn_name_arr
		});

		$("#new-date").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});

		$("#department-select").val("<?php echo empty($department_id) ? 'all' : $department_id;?>");
	});	

	// 查询
	function search(){
		var name = $("#user-input").val();
		var department_id = $("#department-select").val();
		var user_id = "";
		if(name != ""){
			$.each(users_arr, function(){
				if(this['name'] == name){
					user_id = this['id'];
					return false;
				}
			});
			if(user_id == ""){
				showHint("提示信息","查找不到此用户");
				$("#user-input").focus();
			}else{
				location.href = "/oa/seal/department_id/"+department_id+"/user_id/"+user_id;
			}
		}else{
			location.href = "/oa/seal/department_id/"+department_id;
		}
	}

	// 修改印鉴表
	var edit_id = "";
	function showEditSeal(obj){
		edit_id = $(obj).parent().parent().children().first().text();
		var type = $(obj).parent().parent().children().first().next().next().next().next().next().next().next().next().text();
		var type_arr = type.split(",");
		$.each(type_arr, function(){
			var type = this;
			$("input[name='edit-seal-checkbox']").each(function(){
				if($(this).val() == type){
					this.checked = true;
				}
			});	
		});
		var reason = $(obj).parent().parent().children().first().next().next().next().next().text();
		var date = $(obj).parent().parent().children().first().next().next().next().next().next().text();
		var address = $(obj).parent().parent().children().first().next().next().next().next().next().next().text();
		var number = $(obj).parent().parent().children().first().next().next().next().next().next().next().next().text();
		var attachment = $(obj).parent().parent().children().first().next().next().next().next().next().next().next().next().next().text().split("seal/")[1];


		$("#edit-num").val(number);
		$("#edit-company").val(address);
		$("#edit-date").val(date);
		$("#edit-reason").val(reason);
		$("#edit-attachment-show").text(attachment);

		var ySet = (window.innerHeight - $("#edit-seal-div").height())/2;
	    var xSet = (window.innerWidth - $("#edit-seal-div").width())/2;
	    $("#edit-seal-div").css("top",ySet);
	    $("#edit-seal-div").css("left",xSet);
	    $("#edit-seal-div").modal({show:true});
	}

	// 发送修改殷鉴表
	function sendEditSeal(){
		var date = $("#edit-date").val();
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		var seal_arr = new Array;
		$("input[name='edit-seal-checkbox']:checked").each(function(){
			seal_arr.push($(this).val());
		});
		var num = $("#edit-num").val();
		var d_pattern = /^\d+$/;
		var company = $("#edit-company").val();
		var reason = $("#edit-reason").val();

		var fileObj = document.getElementById("edit-attachment").files[0];

		var file_tag = true;
		if(typeof(fileObj) != "undefined"){
			var type = fileObj.type;
			var name = fileObj.name;
			name = name.toLowerCase();
			if(type.indexOf('officedocument') < 0 && type.indexOf('pdf') < 0 && type.indexOf('image') < 0 && type.indexOf('word') < 0 && type.indexOf('powerpoint') < 0 && type.indexOf('excel') < 0 && name.indexOf('.rar') < 0 && name.indexOf('.7z') < 0 && name.indexOf('.zip') < 0 && name.indexOf('.bz2') < 0 && name.indexOf('.kz') < 0 && name.indexOf('.tar') < 0){
				showHint("提示信息","请上传office文件、图片、pdf或压缩包");
				file_tag = false;
			}
		}

		if(file_tag){
			if(!date_pattern.exec(date)){
				showHint("提示信息","用鉴时间输入格式错误");
			}else if(seal_arr.length < 1){
				showHint("提示信息","请选择印鉴");
			}else if(!d_pattern.exec(num)){
				showHint("提示信息","材料份数输入格式错误");
				$("#new-num").focus();
			}else if(reason == ""){
				showHint("提示信息","请输入用鉴事由");
				$("#new-reason").focus();
			}else{
				var FileController = "/ajax/editSeal";                    // 接收上传文件的后台地址 
				// FormData 对象
				var form = new FormData();
				form.append("id", edit_id);
				form.append("use_time", date);
				form.append("number", num);
				form.append("address", company);
				form.append("reason", reason);
				for(var i = 0; i < seal_arr.length; i++){
					var type_name = "type["+i+"]";
					form.append(type_name, seal_arr[i]);   
				}       

				// 如果有附件就上传
				if(typeof(fileObj) != "undefined"){
					form.append("file", fileObj);
				}

				var xhr = new XMLHttpRequest();
				xhr.open("post", FileController, true);
				xhr.send(form);

				// 回调函数
				xhr.onreadystatechange = function(){
					if(xhr.readyState==4 && xhr.status==200){
						var response = xhr.responseText;
						// 从xml字符串转换成xml对象
						try{
							domParser = new  DOMParser();
							xmlDoc = domParser.parseFromString(response, 'text/xml');
							var code = xmlDoc.getElementsByTagName("code")[0].childNodes[0].nodeValue;
						}catch(e){
							showHint("提示信息","解析返回信息失败，请重试");
						}
						// 回调提示
						if(code == 0){
							showHint("提示信息","修改印鉴成功");
							setTimeout(function(){location.reload();},1200);
						}else if(code == -1){
							showHint("提示信息","申请印鉴表失败！");
						}else if(code == -2){
							showHint("提示信息","参数错误");
						}else if(code == -3){
							showHint("提示信息","附件超出了大小限制");
						}else if(code == -4){
							showHint("提示信息","附件上传失败");
						}else if(code == -5){
							showHint("提示信息","没有该印鉴记录");
						}else{
							showHint("提示信息","你没有权限执行此操作");
						}
					}
				}
			}
		}
	}

	// 新增印鉴表
	function showNewSeal(){
		var ySet = (window.innerHeight - $("#new-seal-div").height())/2;
	    var xSet = (window.innerWidth - $("#new-seal-div").width())/2;
	    $("#new-seal-div").css("top",ySet);
	    $("#new-seal-div").css("left",xSet);
	    $("#new-seal-div").modal({show:true});
	}

	// 发送新增印鉴表
	function sendNewSeal(){
		var date = $("#new-date").val();
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		var seal_arr = new Array;
		$("input[name='seal-checkbox']:checked").each(function(){
			seal_arr.push($(this).val());
		});
		var num = $("#new-num").val();
		var d_pattern = /^\d+$/;
		var company = $("#new-company").val();
		var reason = $("#new-reason").val();

		var fileObj = document.getElementById("attachment").files[0];

		var file_tag = true;
		if(typeof(fileObj) != "undefined"){
			var type = fileObj.type;
			var name = fileObj.name;
			name = name.toLowerCase();
			if(type.indexOf('officedocument') < 0 && type.indexOf('pdf') < 0 && type.indexOf('image') < 0 && type.indexOf('word') < 0 && type.indexOf('powerpoint') < 0 && type.indexOf('excel') < 0 && name.indexOf('.rar') < 0 && name.indexOf('.7z') < 0 && name.indexOf('.zip') < 0 && name.indexOf('.bz2') < 0 && name.indexOf('.kz') < 0 && name.indexOf('.tar') < 0){
				showHint("提示信息","请上传office文件、图片、pdf或压缩包");
				file_tag = false;
			}
		}

		if(file_tag){
			if(!date_pattern.exec(date)){
				showHint("提示信息","用鉴时间输入格式错误");
			}else if(seal_arr.length < 1){
				showHint("提示信息","请选择印鉴");
			}else if(!d_pattern.exec(num)){
				showHint("提示信息","材料份数输入格式错误");
				$("#new-num").focus();
			}else if(reason == ""){
				showHint("提示信息","请输入用鉴事由");
				$("#new-reason").focus();
			}else{
				var FileController = "/ajax/applySeal";                    // 接收上传文件的后台地址 
				// FormData 对象
				var form = new FormData();
				form.append("use_time", date);
				form.append("number", num);
				form.append("address", company);
				form.append("reason", reason);
				for(var i = 0; i < seal_arr.length; i++){
					var type_name = "type["+i+"]";
					form.append(type_name, seal_arr[i]);   
				}       

				// 如果有附件就上传
				if(typeof(fileObj) != "undefined"){
					form.append("file", fileObj);
				}

				var xhr = new XMLHttpRequest();
				xhr.open("post", FileController, true);
				xhr.send(form);

				// 回调函数
				xhr.onreadystatechange = function(){
					if(xhr.readyState==4 && xhr.status==200){
						var response = xhr.responseText;
						// 从xml字符串转换成xml对象
						try{
							domParser = new  DOMParser();
							xmlDoc = domParser.parseFromString(response, 'text/xml');
							var code = xmlDoc.getElementsByTagName("code")[0].childNodes[0].nodeValue;
							var id = xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue;
						}catch(e){
							showHint("提示信息","解析返回信息失败，请重试");
						}
						// 回调提示
						if(code == 0){
							showHint("提示信息","申请印鉴表成功!");
							setTimeout(function(){location.reload();}, 1200);
							// $("#new-seal-div").modal("hide");
							// $("#goto-btn").attr("name",id);
							// setTimeout(function(){
								// 清空输入框
								// $("#new-date").val("<?php echo date('Y-m-d', strtotime('+1days')); ?>");
								// $("#new-num").val("");
								// $("#new-reason").val("");
								// $("#new-company").val("");
								// $("input[name='seal-checkbox']").each(function(){
								// 	this.checked = false;
								// });

								// var ySet = (window.innerHeight - $("#new-seal-remind-div").height())/2;
							 //    var xSet = (window.innerWidth - $("#new-seal-remind-div").width())/2;
							 //    $("#new-seal-remind-div").css("top",ySet);
							 //    $("#new-seal-remind-div").css("left",xSet);
							 //    $("#new-seal-remind-div").modal({show:true});
							// },400);
						}else if(code == -1){
							showHint("提示信息","申请印鉴表失败！");
						}else if(code == -2){
							showHint("提示信息","参数错误");
						}else if(code == -3){
							showHint("提示信息","附件超出了大小限制");
						}else if(code == -4){
							showHint("提示信息","附件上传失败");
						}else{
							showHint("提示信息","你没有权限执行此操作");
						}
					}
				}
			}
		}
	}
</script>