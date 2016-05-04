<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd"><strong>费用分摊模板管理</strong></h4>
	<div class="pd20 bor-b-1-ddd  center">
        <!-- 新建模板按钮 -->
		<button class="btn btn-success fr mb5" onclick="showAddTpl()">新建模板</button>
        <!-- 模板信息表格 -->
		<table class="table table-bordered" id="Tpl-manage-table">
			<thead>
				<tr class="bg-fa">
                    <th class="hidden">ID</th>
					<th class="w80 center">模板名称</th>
					<th class="w80 center">摊销项目比例</th>
					<th class="w80 center">操作</th>
				</tr>
			</thead>
			<tbody>
                <!-- 遍历模板列表，显示模板信息 -->
        		<?php foreach ($fee_tpl_list as $value): ?>
        		<tr class="tpl-tr">
                    <!-- 模板ID，默认隐藏 -->
            		<td class="hidden"><?php echo $value['tpl_id'] ?></td>
                    <!-- 模板名称 -->
            		<td><?php echo $value['name'] ?></td>
                    <!-- 摊销项目比例 -->
            		<td>
                        <!-- 遍历每个模板的fee_div_p数组， -->
          	    		<?php foreach (CJSON::decode($value['fee_div_p'],true) as $key => $value2): ?>
                            <!-- 项目名称（这里显示的是id，之后用js将模板id更换为模板名称） -->
          	    			<p class="inline Tpl-proportion"><?php echo $key ?></p>
                            <!-- 冒号 -->
          	    			<p class="inline">:</p>
                            <!-- 所占比例 -->
          	    			<p class="inline"><?php echo $value2 ?>%&nbsp;&nbsp;</p>
          	    		<?php endforeach ?>
            		</td>
                    <!-- 修改、删除操作 -->
            		<td><a href="javascript:;" onclick="showChangeTpl(this)">修改</a> <a href="javascript:;" onclick="deleteTpl(this)">删除</a></td>
				</tr>
        		<?php endforeach ?>
			</tbody>
		</table>
		
	</div>
</div>

<!-- 新建模板模态框 -->
<div id="add-Tpl-div" class="modal fade in hint bor-rad-5 w600">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      	<a class="close" data-dismiss="modal" onclick="">×</a>
      	<h4 class="hint-title">新建模板</h4>
    </div>
    <div class="modal-body">
        <!-- 新建模板表格 -->
      	<table class="table table-unbordered m0">
        	<tbody id="newTpl-tbody">
                <!-- 输入模板名称 -->
          		<tr>
            		<th class="w80 va-t center">模板名称</th>
            		<td class="w80"><input class="form-control w200" id="add_Tpl_name" value=''></td>
          		</tr>
                <!-- 输入项目及其所占比例 -->
          		<tr>
           		 	<th class="w80 va-t center">摊销项目</th>
            		<td class="w80 add_Tpl_pro">
                        <!-- 遍历项目列表，显示可选择的项目 -->
            			<select class="form-control w200 inline-block add-Tpl-select">
                        <?php foreach ($project_list as $value): ?>
            				<option><?php echo $value['name'] ?></option>
            			<?php endforeach ?>
            			</select>
                        <!-- 输入该项目所占比例 -->
            			<input class="form-control w150 inline-block add-Tpl-input" value='' placeholder="比例">
                        <!-- 删除按钮，删除一行 -->
            			<a href="javascript:;" onclick="deleteLine(this)">删除</a>
            		</td>
          		</tr>
        	</tbody>
      	</table>
    </div>
    <!-- 增加一行 -->
    <div class="center">
    	<a href="javascript:;" class="" onclick="addLine()">增加一行</a>
    </div>

    <div class="modal-footer center" id="modal-footer">
	    	<button class="btn btn-success w100 ml10 mr120" onclick="newTpl()">确认</button>
	        <button class="btn btn-default w100" data-dismiss="modal">取消</button>
    </div>
</div>

<!-- 修改模板模态框 -->
<div id="change-Tpl-div" class="modal fade in hint bor-rad-5 w600">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      	<a class="close" data-dismiss="modal" onclick="">×</a>
      	<h4 class="hint-title">修改模板</h4>
    </div>
    <div class="modal-body">
        <!-- 用于读取模板的id，默认隐藏 -->
        <span class="hidden" id="change_Tpl_id"></span>
        <!-- 修改模板表格 -->
      	<table class="table table-unbordered m0">
        	<tbody id="changeTpl-tbody">
                <!-- 输入模板名称 -->
          		<tr>
            		<th class="w80 va-t center">模板名称</th>
            		<td class="w80"><input class="form-control w200" id="change_Tpl_name" value=''></td>
          		</tr>
                <!-- 输入项目及其所占比例 -->
          		<tr id="change_Tpl_tr">
           		 	<th class="w80 va-t center">摊销项目</th>
            		<td class="w80 change_Tpl_pro">
                        <!-- 遍历项目列表，显示可选择的项目 -->
            			<select class="form-control w200 inline-block change-Tpl-select">
                        <?php foreach ($project_list as $value): ?>
            				<option><?php echo $value['name'] ?></option>
            			<?php endforeach ?>
            			</select>
                        <!-- 输入该项目所占比例 -->
            			<input class="form-control w150 inline-block change-Tpl-input" value='' placeholder="比例">
                        <!-- 删除按钮，删除一行 -->
            			<a href="javascript:;" onclick="deleteLine(this)">删除</a>
            		</td>
          		</tr>
        	</tbody>
      	</table>
        <!-- 增加一行 -->
      	<div class="center">
    		<a href="javascript:;" class="" onclick="addLineForChange()">增加一行</a>
    	</div>
    </div>

    <div class="modal-footer center" id="modal-footer">
	    	<button class="btn btn-success w100 ml10 mr120" onclick="sendEditTpl()">确认</button>
	        <button class="btn btn-default w100" data-dismiss="modal">取消</button>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
        // 获取所有的模板信息行
		var tpl_pro = $('.Tpl-proportion');
		// 遍历模板信息行，将模板id置换成模板名称，用于摊销项目比例里模板名的显示
		$.each(tpl_pro,function(){
			var projectId = $(this).text();
			var projectName = findProject(projectId);
			$(this).text(projectName);
		});
	});
    // 根据模板id，返回模板名称
	function findProject(id){
        var proArr = <?php echo CJSON::encode($project_list) ?>;
        for(var x in proArr){
            if(proArr[x]['project_id'] == id){
                return proArr[x]['name'];
            }
        }
    }
    // 根据模板名称，返回模板id
    function findProjectId(name){
        var proArr = <?php echo CJSON::encode($project_list) ?>;
        for(var x in proArr){
            if(proArr[x]['name'] == name){
                return proArr[x]['project_id'];
            }
        }
    }
    // 显示新建模板模态框
    function showAddTpl(){
    	var ySet = (window.innerHeight - $("#add-Tpl-div").height())/3;
    	var xSet = (window.innerWidth - $("#add-Tpl-div").width())/2;
    	$("#add-Tpl-div").css("top",ySet);
    	$("#add-Tpl-div").css("left",xSet);
    	$("#add-Tpl-div").modal({show:true});
    }
    // 新建模板模态框里的增加一行功能
    function addLine(){
        // 获取tbody节点,再将新行添加到节点末尾
    	var tbody = $('#newTpl-tbody');
    	var tr = "<tr class='addAfter'>"+
           		 	"<th class='w80 va-t center'></th>"+
            		"<td class='w80 add_Tpl_pro'>"+
            			"<select class='form-control w200 inline-block add-Tpl-select'>"+
                        "<?php foreach ($project_list as $value): ?>"+
            				"<option><?php echo $value['name'] ?></option>"+
            			"<?php endforeach ?>"+
            			"</select>"+" "+
            			"<input class='form-control w150 inline-block add-Tpl-input' value='' placeholder='比例''>"+" "+
            			"<a href='javascript:;' onclick='deleteLine(this)'>删除</a>"
            		"</td>"+
          		"</tr>";
        tbody.append(tr);
    }
    // 修改模板模态框里的增加一行功能
    function addLineForChange(){
    	var tbody = $('#changeTpl-tbody');
    	var tr = "<tr class='addAfter'>"+
           		 	"<th class='w80 va-t center'></th>"+
            		"<td class='w80 change_Tpl_pro'>"+
            			"<select class='form-control w200 inline-block change-Tpl-select'>"+
                        "<?php foreach ($project_list as $value): ?>"+
            				"<option><?php echo $value['name'] ?></option>"+
            			"<?php endforeach ?>"+
            			"</select>"+" "+
            			"<input class='form-control w150 inline-block change-Tpl-input' value='' placeholder='比例''>"+" "+
            			"<a href='javascript:;' onclick='deleteLine(this)'>删除</a>"
            		"</td>"+
          		"</tr>";
        tbody.append(tr);
    }
    // 新建模板
    function newTpl(){

    	var name = $('#add_Tpl_name').val();
    	var td = $('.add_Tpl_pro');
    	var proArr = {};
    	var	proNameArr = new Array();
    	var total = 0;
    	var flag_repeat = true;
    	var flag_empty = true;

    	$.each(td,function(){
    		var projectName = this.children[0].value;
    		var projectId = findProjectId(projectName);
    		if($.inArray(projectId,proNameArr) == -1){
    			proNameArr.push(projectId);
    		}else{
    			flag_repeat = false;
    		}
    		var pro = this.children[1].value;
    		if(pro == ""){
    			pro = "0";
    			flag_empty = false;
    		}
    		total += parseInt(pro);
    		proArr[projectId] = pro;
    	});
        // 验证数据,如果项目分摊比例和不为100,或者项目被选中多次,或者比例为空,则提示重新输入
    	if(name == ""){
    		showHint("提示信息","请输入模板名称！");
    	}else if(total != 100){
    		showHint("提示信息","比例之和必须为100,请重新输入！");
    	}else if(flag_repeat == false){
    		showHint("提示信息","所选项目出现重复，请重新选择！");
    	}else if(flag_empty == false){
    		showHint("提示信息","项目分摊费用比例未填写完整！");
    	}else{
    		var ajaxResult = 0;
            // 新建模板
    		$.ajax({
	            type:'post',
	            dataType:'json',
	            url:'/ajax/AddFeeTpl',
	            data:{'name':name,'fee_div_p':proArr,'remark':""},
	            success:function(result){
	                if(result.code == 0){
	                    showHint("提示信息","新建模板成功！");
	                    ajaxResult = 1;
	                    setTimeout(function(){location.reload();},1200);
	                }else if(result.code == -3){
	                    showHint("提示信息","fee_div_p错误！");
	                }else if(result.code == -2){
	                    showHint("提示信息","参数错误！");
	                }else if(result.code == -98){
	                    showHint("提示信息","你没有权限执行此操作！");
	                }else{
	                    showHint("提示信息","未知错误，请联系管理员！");
	                }
	            }
	        });
            // 如果新建模板成功,则将修改时添加的行删除,避免下次再次显示
    		if(ajaxResult == 1){
    			$('.addAfter').remove();
    		}
    	}
    }

    //yeqingwen  2016-01-05  修改费用分摊模板
    function sendEditTpl() {
        // 获取数据
        var name = $('#change_Tpl_name').val();
        var tpl_id = $("#change_Tpl_id").text();
        var td = $('.change_Tpl_pro');
        // 初始化数组、标识符等
        var proArr = {};
        var proNameArr = new Array();
        var total = 0;
        var flag_repeat = true;
        var flag_empty = true;
        //遍历项目分摊信息行，获取 
        $.each(td,function(){
            // 获取要修改项目的id
            var projectName = this.children[0].value;
            var projectId = findProjectId(projectName);
            // 判断项目是否许选择重复
            if($.inArray(projectId,proNameArr) == -1){
                proNameArr.push(projectId);
            }else{
                flag_repeat = false;
            }
            // 判断比例是否有未填写
            var pro = this.children[1].value;
            if(pro == ""){
                pro = "0";
                flag_empty = false;
            }
            // 计算比例之和,之前未填的比例按0计算
            total += parseInt(pro);
            // 保存项目id对应占的分摊比例
            proArr[projectId] = pro;
        });
        // 验证数据,如果项目分摊比例和不为100,或者项目被选中多次,或者比例为空,则提示重新输入
        if(name == ""){
            showHint("提示信息","请输入模板名称！");
        }else if(total != 100){
            showHint("提示信息","比例之和必须为100,请重新输入！");
        }else if(flag_repeat == false){
            showHint("提示信息","所选项目出现重复，请重新选择！");
        }else if(flag_empty == false){
            showHint("提示信息","项目分摊费用比例未填写完整！");
        }
        else{
            var ajaxResult = 0;
            $.ajax({
                // 修改模板
                type:'post',
                dataType:'json',
                url:'/ajax/EditFeeTpl',
                data:{'tpl_id':tpl_id,'name':name,'fee_div_p':proArr,'remark':""},
                success:function(result){
                    
                    if(result.code == 0){
                        showHint("提示信息","修改成功！");
                        ajaxResult = 1;
                        setTimeout(function(){location.reload();},1200);
                    }else if(result.code == -3){
                        showHint("提示信息","fee_div_p错误！");
                    }else if(result.code == -2){
                        showHint("提示信息","参数错误！");
                    }else if(result.code == -98){
                        showHint("提示信息","你没有权限执行此操作！");
                    }else{
                        showHint("提示信息","未知错误，请联系管理员！");
                    }
                }
            });
            // 如果修改模板成功,则将修改时添加的行删除,避免下次再次显示
            if(ajaxResult == 1){
                $('.addAfter').remove();
            }
        }
    }
    // 删除模板
    function deleteTpl(row){
        // 获取要删除模板的id
    	var id = row.parentNode.parentNode.children[0].innerText;
        // 删除模板ajax
    	$.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/DeleteFeeTpl',
            data:{'tpl_id':id},
            success:function(result){
                if(result.code == 0){
                    showHint("提示信息","删除模板成功！");
                    setTimeout(function(){location.reload();},1200);
                }else if(result.code == -2){
                    showHint("提示信息","参数错误！");
                }else if(result.code == -98){
                    showHint("提示信息","你没有权限执行此操作！");
                }else{
                    showHint("提示信息","未知错误，请联系管理员！");
                }
            }
        });
    }
    // 显示修改模板模态框
    function showChangeTpl(row){
        // 每次显示前,将之前增加的行移除
        $('tr.addAfter').remove();
        // 获取要修改的模板id
    	var id = row.parentNode.parentNode.children[0].innerText;
        // 获取模板列表，根据获取的id将模板信息显示出来
    	var arr = <?php echo CJSON::encode($fee_tpl_list) ?>;
    	for(var x in arr){
    		if(id == arr[x]['tpl_id']){
				$('#change_Tpl_name').val(arr[x]['name']);
                $('#change_Tpl_id').text(id);
				var fee_arr = arr[x]['fee_div_p'];
                var first_tag = true;
				$.each(JSON.parse(fee_arr),function(name,value){
                    if(!first_tag) {
                        addLineForChange();
                    }
                    var last_str = $("#change-Tpl-div tr:last");
                    last_str.find('select option:contains("'+findProject(name)+'")').attr('selected', true);
                    last_str.find('input').val(value);
                    first_tag = false;
				});
    		}
    		
    	}
        // 显示修改模板模态框
    	var ySet = (window.innerHeight - $("#change-Tpl-div").height())/3;
    	var xSet = (window.innerWidth - $("#change-Tpl-div").width())/2;
    	$("#change-Tpl-div").css("top",ySet);
    	$("#change-Tpl-div").css("left",xSet);
    	$("#change-Tpl-div").modal({show:true});
    }
    // 删除一行
    function deleteLine(row){
    	var thisRow = row.parentNode.parentNode;
        // 如果是最后一行,则不能删除
    	if(thisRow.parentNode.children.length <= 2){
    	}else{
    		thisRow.remove();
    	}
    }
</script>