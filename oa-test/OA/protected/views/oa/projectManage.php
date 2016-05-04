<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd"><strong>项目管理</strong></h4>
	<div class="pd20 bor-b-1-ddd  center">
        <!-- 新建项目按钮 -->
		<button class="btn btn-success fr mb5" onclick="showAddProject()">新建项目</button>
        <!-- 显示项目信息的表格 -->
		<table class="table table-bordered" id="project-manage-table">
			<thead>
				<tr class="bg-fa">
                    <th class="hidden">ID</th>
					<th class="w80 center">项目编号</th>
					<th class="w80 center">项目开始时间</th>
					<th class="w80 center">项目名称</th>
					<th class="w80 center">项目说明</th>
					<th class="w80 center">所属部门</th>
					<th class="w80 center">项目负责人</th>
					<th class="w80 center">操作</th>
				</tr>
			</thead>
			<tbody>
            <!-- 读取项目列表 -->
            <?php foreach ($project_list as $value): ?>
                <tr class="project-tr">
                    <!-- 项目ID（隐藏） -->
                    <td class="hidden"><?php echo $value['project_id'] ?></td>
                    <!-- 项目编号 -->
                    <td><?php echo $value['serial_number'] ?></td>
                    <!-- 项目开始时间 -->
                    <td><?php echo $value['create_time'] ?></td>
                    <!-- 项目名称 -->
                    <td><?php echo $value['name'] ?></td>
                    <!-- 项目说明 -->
                    <td><?php echo $value['remark'] ?></td>
                    <!-- 项目所属部门，这里读取部门id，之后用js将id换成部门名称 -->
                    <td class="dep-id" data-name="dep-id"><?php echo $value['department_id'] ?></td>
                    <!-- 项目负责人，这里读取项目负责人id，之后用js将id换成项目负责人的名字 -->
                    <td><?php echo $value['project_admin'] ?></td>
                    <!-- 项目修改、删除操作 -->
                    <td><a href="javascript:;" onclick="showChangeProject(this)">修改</a> <a href="javascript:;" onclick="deleteProject(this)">删除</a></td>
        		</tr>
            <?php endforeach ?>
			</tbody>
		</table>
		
	</div>
</div>

<!-- 新建項目模态框 -->
<div id="add-project-div" class="modal fade in hint bor-rad-5 w600">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      	<a class="close" data-dismiss="modal" onclick="recover();">×</a>
      	<h4 class="hint-title">新建项目</h4>
    </div>
    <div class="modal-body">
      	<table class="table table-unbordered center m0">
        	<tbody id="newProject-tbody">
          		<tr>
            		<th class="w80 va-t">项目编号</th>
            		<td class="w80"><input class="form-control w200" id="add_project_id" value=''></td>
          		</tr>
          		<tr>
            		<th class="w80 va-t">项目名称</th>
            		<td><input class="form-control w200" id="add_project_name" value=''></td>
          		</tr>
          		<tr>
            		<th class="w80 va-t">项目开始时间</th>
                    <td><input class="form-control w200 pointer" id="add_project_time" value="<?php echo date('Y-m-d H:i');?>"></td>
          		</tr>
          		<tr>
           		 	<th class="w80 va-t">所属部门</th>
            		<td>
            			<select id="add_project_department" class="form-control w200">
                        <!-- 读取部门列表 -->
                        <?php foreach ($department_list as $value): ?>
            				<option><?php echo $value['name'] ?></option>
            			<?php endforeach ?>
            			</select>
            		</td>
          		</tr>
          		<tr>
          			<th class="w80 va-t">项目负责人</th>
          			<td><input class="form-control w200" id="add_project_admin" value=''></td>
          		</tr>
          		<tr>
          			<th class="w80 va-t">项目说明</th>
          			<td><textarea style="resize:none;height:100px" id="add_project_state" class="form-control"></textarea></td>
          		</tr>
        	</tbody>
      	</table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100 fl ml10 mr20" onclick="newProject()">确认</button>
      <button class="btn btn-default w100 fl" data-dismiss="modal">取消</button>
    </div>
</div>

<!-- 修改項目模态框 -->
<div id="change-project-div" class="modal fade in hint bor-rad-5 w600">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="recover();">×</a>
        <h4 class="hint-title">修改项目</h4>
    </div>
    <div class="modal-body">
        <table class="table table-unbordered center m0">
            <tbody id="changeProject-tbody">
                <tr class="hidden">
                    <th class="w80 va-t">项目ID</th>
                    <td class="w80"><input class="form-control w200" id="change_project_realId" value=''></td>
                </tr>
                <tr>
                    <th class="w80 va-t">项目编号</th>
                    <td class="w80"><input class="form-control w200" id="change_project_id" value=''></td>
                </tr>
                <tr>
                    <th class="w80 va-t">项目名称</th>
                    <td><input class="form-control w200" id="change_project_name" value=''></td>
                </tr>
                <tr>
                    <th class="w80 va-t">项目开始时间</th>
                    <td><input class="form-control w200 pointer" id="change_project_time" value="<?php echo date('Y-m-d H:i');?>"></td>
                </tr>
                <tr>
                    <th class="w80 va-t">所属部门</th>
                    <td>
                        <select id="change_project_department" class="form-control w200">
                        <?php foreach ($department_list as $value): ?>
                            <option><?php echo $value['name'] ?></option>
                        <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="w80 va-t">项目负责人</th>
                    <td><input class="form-control w200" id="change_project_admin" value=''></td>
                </tr>
                <tr>
                    <th class="w80 va-t">项目说明</th>
                    <td><textarea style="resize:none;height:100px" id="change_project_state" class="form-control"></textarea></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100 fl ml10 mr20" onclick="changeProject()">修改</button>
      <button class="btn btn-default w100 fl" data-dismiss="modal">取消</button>
    </div>
</div>


<script type="text/javascript">
    
    $(document).ready(function(){
        // 获取项目信息行，之后进行遍历，将每行信息的所属部门id和项目负责人id置换成对应名称
        var projectList = $('.project-tr');
        $.each(projectList,function(){
            // 置换所属部门id
            var depId = this.children[5].innerText;
            this.children[5].innerText = findDep(depId);
            // 置换项目负责人id
            var adminId = this.children[6].innerText;
            this.children[6].innerText = findAdmin(adminId);
        });
        //初始化新增项目模态框和修改项目模态框里的时间控件
        $('#add_project_time').datetimepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $('#change_project_time').datetimepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        //获取所有用户的中文名
        var cn_name = new Array();
        var userArr = <?php echo CJSON::encode($user_list) ?>;
        $.each(userArr, function(){
            cn_name.push(this['cn_name']);
        });
        //添加自动完成提示
        $("#add_project_admin").autocomplete({source:cn_name});
        $("#change_project_admin").autocomplete({source:cn_name});
    });
    // 显示新建项目模态框
	function showAddProject(){
		var ySet = (window.innerHeight - $("#add-project-div").height())/3;
    	var xSet = (window.innerWidth - $("#add-project-div").width())/2;
    	$("#add-project-div").css("top",ySet);
    	$("#add-project-div").css("left",xSet);
    	$("#add-project-div").modal({show:true});
	}
    // 根据部门id返回部门名称
    function findDep(id){
        var depArr = <?php echo CJSON::encode($department_list) ?>;
        for(var x in depArr){
            if(depArr[x]['department_id'] == id){
                return depArr[x]['name'];
            }
        }
    }
    // 根据部门名称返回部门id
    function findDepID(name){
        var depArr = <?php echo CJSON::encode($department_list) ?>;
        // console.log(depArr);
        for(var x in depArr){
            if(depArr[x]['name'] == name){
                return depArr[x]['department_id'];
            }
        }
    }
    // 根据用户id返回用户名字，用于返回项目负责人名字
    function findAdmin(id){
        var userArr = <?php echo CJSON::encode($user_list) ?>;
        // console.log(depArr);
        for(var x in userArr){
            if(userArr[x]['user_id'] == id){
                return userArr[x]['cn_name'];
            }
        }
    }
    // 根据用户名字返回用户id，用于返回项目负责人id
    function findAdminID(name){
        var userArr = <?php echo CJSON::encode($user_list) ?>;
        // console.log(depArr);
        for(var x in userArr){
            if(userArr[x]['cn_name'] == name){
                return userArr[x]['user_id'];
            }
        }
    }
    // 新建项目模态框的提交按钮事件
    function newProject(){
        // 获取表单里新建项目所需的信息
        var id = $('#newProject-tbody #add_project_id').val();
        var name = $('#newProject-tbody #add_project_name').val();
        var time = $('#newProject-tbody #add_project_time').val();
        var department = $('#newProject-tbody #add_project_department').val();
        var admin = $('#newProject-tbody #add_project_admin').val();
        var state = $('#newProject-tbody #add_project_state').val();
        // 将部门和项目负责人名字转化为id
        var depId = findDepID(department);
        var adminId = findAdminID(admin);
        //验证信息是否输入完整
        if(id == ""){
            showHint("提示信息","请输入项目编号！");
        }else if(name == ""){
            showHint("提示信息","请输入项目名称！");
        }else if(time == ""){
            showHint("提示信息","请输入项目开始时间！");
        }else if(department == ""){
            showHint("提示信息","请选择项目所属部门！");
        }else if(admin == ""){
            showHint("提示信息","请输入项目负责人！");
        }else if(depId == ""){
            showHint("提示信息","该部门不存在！");
        }else if(adminId == ""){
            showHint("提示信息","部门负责人输入错误！");
        }else{
            // 新建项目
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/AddProject',
                data:{'serial_number':id, 'name':name,'department_id':depId,'project_admin':adminId,'remark':state},
                success:function(result){
                    if(result.code == 0){
                        showHint("提示信息","新建项目成功！");
                        setTimeout(function(){location.reload();},1200);
                    }else if(result.code == -3){
                        showHint("提示信息","部门信息错误！");
                    }else if(result.code == -2){
                        showHint("提示信息","参数错误！");
                    }else if(result.code == -4){
                        showHint("提示信息","负责人信息错误！");
                    }else if(result.code == -98){
                        showHint("提示信息","你没有权限执行此操作！");
                    }else{
                        showHint("提示信息","未知错误，请联系管理员！");
                    }
                }
            });
        }        
    }
    // 删除项目按钮
    function deleteProject(row){
        // 获取要删除项目的id
        var id = row.parentNode.parentNode.children[0].innerText;
        //删除项目
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/DelProject',
            data:{'project_id':id},
            success:function(result){
                if(result.code == 0){
                    showHint("提示信息","删除项目成功！");
                    setTimeout(function(){location.reload();},1200);
                }else if(result.code == -2){
                    showHint("提示信息","参数错误！");
                }else if(result.code == -5){
                    showHint("提示信息","该项目的公共费用摊销比例不为0！");
                }else if(result.code == -4){
                    showHint("提示信息","找不到项目信息！");
                }else if(result.code == -98){
                    showHint("提示信息","你没有权限执行此操作！");
                }else{
                    showHint("提示信息","未知错误，请联系管理员！");
                }
            }
        });
    }
    // 新建数组，用于保存项目修改前的信息
    var before = [];
    //显示修改项目模态框
    function showChangeProject(row){
        //获取原来的项目信息
        var id = row.parentNode.parentNode.children[0].innerText;
        var SYid = row.parentNode.parentNode.children[1].innerText;
        var time = row.parentNode.parentNode.children[2].innerText;
        var name = row.parentNode.parentNode.children[3].innerText;
        var state = row.parentNode.parentNode.children[4].innerText;
        var department = row.parentNode.parentNode.children[5].innerText;
        var admin = row.parentNode.parentNode.children[6].innerText;
        var depId = findDepID(department);
        var adminId = findAdminID(admin);
        //将原来的项目信息保存到数组里
        before = [];
        before.push(SYid,time,name,state,department,admin);
        //将原来的项目信息读入表单里
        $('#changeProject-tbody #change_project_realId').val(id);
        $('#changeProject-tbody #change_project_id').val(SYid);
        $('#changeProject-tbody #change_project_name').val(name);
        $('#changeProject-tbody #change_project_time').val(time);
        $('#changeProject-tbody #change_project_department').val(department);
        $('#changeProject-tbody #change_project_admin').val(admin);
        $('#changeProject-tbody #change_project_state').val(state);
        //显示修改项目模态框
        var ySet = (window.innerHeight - $("#change-project-div").height())/3;
        var xSet = (window.innerWidth - $("#change-project-div").width())/2;
        $("#change-project-div").css("top",ySet);
        $("#change-project-div").css("left",xSet);
        $("#change-project-div").modal({show:true});
    }
    // 新建数组，用于保存项目修改后的项目信息
    var after = new Array();
    // 修改项目操作
    function changeProject(){
        // 获取新的项目信息
        var id = $('#changeProject-tbody #change_project_realId').val();
        var SYid = $('#changeProject-tbody #change_project_id').val();
        var name = $('#changeProject-tbody #change_project_name').val();
        var time = $('#changeProject-tbody #change_project_time').val();
        var department = $('#changeProject-tbody #change_project_department').val();
        var admin = $('#changeProject-tbody #change_project_admin').val();
        var state = $('#changeProject-tbody #change_project_state').val();
        // 将所属部门和项目负责人名称转换为id
        var depId = findDepID(department);
        var adminId = findAdminID(admin);
        // 将修改后的项目信息保存到数组里
        after = [];
        after.push(SYid,time,name,state,department,admin);
        // 验证信息，如果项目信息未改变，则进行提示
        if(before[0] == after[0] && before[1] == after[1] && before[2] == after[2] && before[3] == after[3] && before[4] == after[4] && before[5] == after[5]){
            showHint("提示信息","项目信息未改变");
        }else{
            // 修改项目
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/EditProject',
                data:{'project_id':id,'serial_number':SYid,'name':name,'department_id':depId,'project_admin':adminId,'remark':state},
                success:function(result){
                    if(result.code == 0){
                        showHint("提示信息","修改项目成功！");
                        setTimeout(function(){location.reload();},1200);
                    }else if(result.code == -2){
                        showHint("提示信息","参数错误！");
                    }else if(result.code == -3){
                        showHint("提示信息","部门信息错误！");
                    }else if(result.code == -4){
                        showHint("提示信息","负责人信息错误！");
                    }else if(result.code == -98){
                        showHint("提示信息","你没有权限执行此操作！");
                    }else{
                        showHint("提示信息","未知错误，请联系管理员！");
                    }
                }
            });
        }
    }
</script>