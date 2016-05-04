<?php
echo "<script type='text/javascript'>";
echo "console.log('processManage');";
echo "</script>";
?>


<div class="bor-1-ddd">
	<h4 class="pd10 m0 b33 bor-b-1-ddd"><strong>流程管理</strong></h4>
	<div class="pd20 bor-b-1-ddd  center">
		<table class="table table-bordered" id="leave-change-table">
			<tbody>
				<tr class="bg-fa">
					<th class="w80 center hidden">ID</th>
					<th class="w80 center">请假</th>
					<th class="w80 center">审批人</th>
					<th class="w80 center">审批条件</th>
					<th class="w80 center">操作</th>
				</tr>
			</tbody>
		</table>
		<button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
	</div>

	<div class="pd20 bor-b-1-ddd center">
		<table class="table table-bordered" id="overtime-change-table">
			<tbody>
				<tr class="bg-fa">
					<th class="w80 center hidden">ID</th>
					<th class="w80 center">加班</th>
					<th class="w80 center">审批人</th>
					<th class="w80 center">审批条件</th>
					<th class="w80 center">操作</th>
				</tr>
			</tbody>
		</table>
		<button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
	</div>
	<div class="pd20 bor-b-1-ddd center">
		<table class="table table-bordered" id="goodsApply-change-table">
			<tbody>
				<tr class="bg-fa">
					<th class="w80 center hidden">ID</th>
					<th class="w80 center">费用申请</th>
					<th class="w80 center">审批人</th>
					<th class="w80 center">审批条件</th>
					<th class="w80 center">操作</th>
				</tr>
			</tbody>
		</table>
		<button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
	</div>
	<div class="pd20 bor-b-1-ddd center">
		<table class="table table-bordered" id="out-change-table">
			<tbody>
				<tr class="bg-fa">
					<th class="w80 center hidden">ID</th>
					<th class="w80 center">出差</th>
					<th class="w80 center">审批人</th>
					<th class="w80 center">审批条件</th>
					<th class="w80 center">操作</th>
				</tr>
			</tbody>
		</table>
		<button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
	</div>
  <div class="pd20 bor-b-1-ddd center">
    <table class="table table-bordered" id="recruit-change-table">
      <tbody>
        <tr class="bg-fa">
          <th class="w80 center hidden">ID</th>
          <th class="w80 center">招聘</th>
          <th class="w80 center">审批人</th>
          <th class="w80 center">审批条件</th>
          <th class="w80 center">操作</th>
        </tr>
      </tbody>
    </table>
    <button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
  </div>
  <div class="pd20 bor-b-1-ddd center">
    <table class="table table-bordered" id="positive_apply-change-table">
      <tbody>
        <tr class="bg-fa">
          <th class="w80 center hidden">ID</th>
          <th class="w80 center">转正申请</th>
          <th class="w80 center">审批人</th>
          <th class="w80 center">审批条件</th>
          <th class="w80 center">操作</th>
        </tr>
      </tbody>
    </table>
    <button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
  </div>
  <div class="pd20 bor-b-1-ddd center">
    <table class="table table-bordered" id="quit_apply-change-table">
      <tbody>
        <tr class="bg-fa">
          <th class="w80 center hidden">ID</th>
          <th class="w80 center">离职工作交接</th>
          <th class="w80 center">审批人</th>
          <th class="w80 center">审批条件</th>
          <th class="w80 center">操作</th>
        </tr>
      </tbody>
    </table>
    <button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
  </div>
  <div class="pd20 bor-b-1-ddd center hidden">
    <table class="table table-bordered" id="seal-change-table">
      <tbody>
        <tr class="bg-fa">
          <th class="w80 center hidden">ID</th>
          <th class="w80 center">印鉴申请</th>
          <th class="w80 center">审批人</th>
          <th class="w80 center">审批条件</th>
          <th class="w80 center">操作</th>
        </tr>
      </tbody>
    </table>
    <button class="btn btn-success" onclick="addProcess(this)">添加流程</button>
  </div>
</div>

<!-- 修改流程模态框 -->
<div id="change-process-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      	<a class="close" data-dismiss="modal" onclick="recover();">×</a>
      	<h4 class="hint-title">修改流程</h4>
    </div>
    <div class="modal-body">
      	<table class="table table-unbordered center m0">
        	<tbody>
        		<tr class="hidden">
            		<th class="w130">ID</th>
            		<td><input class="form-control" id="process_id" value='' disabled="true"></td>
          		</tr>
          		<tr>
            		<th class="w130">流程ID</th>
            		<td><input class="form-control" id="change_process_id" value='' disabled="true"></td>
          		</tr>
          		<tr>
            		<th class="w130">审批人</th>
            		<td>
            			<select class="form-control" id="change_process_node">
                			<option value="d_admin">申请人直属上司</option>
                			<option value="d2_admin">上一级部门主管</option>
                			<option value="hr_admin">人事行政部主管</option>
                			<option value="ceo">总经理</option>
              			</select>
            		</td>
          		</tr>
          		<tr>
           		 	<th class="w130">审批条件</th>
            		<td id="change_process_condition">
            			
            		</td>
          		</tr>
        	</tbody>
      	</table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100" onclick="sendProcessChange();">提交</button>
    </div>
</div>

<!-- 添加流程模态框 -->
<div id="add-process-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      	<a class="close" data-dismiss="modal" onclick="recover();">×</a>
      	<h4 class="hint-title">添加流程</h4>
    </div>
    <div class="modal-body">
      	<table class="table table-unbordered center m0">
        	<tbody>
          		<tr>
            		<th class="w130">流程类型</th>
            		<td><input class="form-control" id="add_process_type" value='default' disabled="true"></td>
          		</tr>
          		<tr>
            		<th class="w130">审批人</th>
            		<td>
            			<select class="form-control" id="add_process_node">
                			<option value="d_admin">申请人直属上司</option>
                			<option value="d2_admin">上一级部门主管</option>
                			<option value="hr_admin">人事行政部主管</option>
                			<option value="ceo">总经理</option>
              			</select>
            		</td>
          		</tr>
          		<tr>
           		 	<th class="w130">审批条件</th>
            		<td id="add_process_condition">
            			
            		</td>
          		</tr>
        	</tbody>
      	</table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100" onclick="sendProcessAdd();">提交</button>
    </div>
</div>

<script type="text/javascript">
	console.log(<?php echo $procedure_list ?>);
	//获取流程数据
	var list = <?php echo $procedure_list ?>;
	//处理获得的数据
	for(var x in list){
		if(list[x]['user_role'] == "d_admin"){
			list[x]['user_role'] = "申请人直属上司";
		}else if(list[x]['user_role'] == "d2_admin"){
			list[x]['user_role'] = "上一级部门主管";
		}else if(list[x]['user_role'] == "hr_admin"){
			list[x]['user_role'] = "人事行政部主管";
		}else if(list[x]['user_role'] == "ceo"){
			list[x]['user_role'] = "总经理";
		}
	}
	//初始化表格
	//请假
	var tableContent = "";
	var count = 0;
	for(i=0;i<list.length;i++){
		if(list[i]['type'] == "leave"){
			var condition = "";
			count++;
			if(list[i]['value'] == 0){
				condition = "所有";
			}else{
				condition = "请假天数>= " + list[i]['value'] + " 天";
			}
			tableContent += "<tr class='bg-fa'>"+
							"<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
							"<td class='w80 center'>"+count+"</td>"+
							"<td class='w80 center'>"+list[i]['user_role']+"</td>"+
							"<td class='w80 center'>"+condition+"</td>"+
							"<td class='w80 center'>"+
								"<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
								"<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
							"</td>"+
						"</tr>";
	}
	}
	$('#leave-change-table').children().append(tableContent);
	//加班
	tableContent = "";
	count = 0;
	for(i=0;i<list.length;i++){

		if(list[i]['type'] == "overtime"){
			var condition = "";
			count++;
			if(list[i]['value'] == 0){
				condition = "周末 + 法定节假日";
			}else{
				condition = "";
			}
			tableContent += "<tr class='bg-fa'>"+
							"<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
							"<td class='w80 center'>"+count+"</td>"+
							"<td class='w80 center'>"+list[i]['user_role']+"</td>"+
							"<td class='w80 center'>"+condition+"</td>"+
							"<td class='w80 center'>"+
								"<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
								"<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
							"</td>"+
						"</tr>";
	}
	}
	$('#overtime-change-table').children().append(tableContent);
	//费用申请
	tableContent = "";
	count = 0;
	for(i=0;i<list.length;i++){
		if(list[i]['type'] == "goods_apply"){
			var condition = "";
			count++;
			if(list[i]['value'] == 0){
				condition = "所有";
			}else if(list[i]['value'] == 1){
				condition = "费用报销金额 > 对应费用申请";
			}
			tableContent += "<tr class='bg-fa'>"+
							"<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
							"<td class='w80 center'>"+count+"</td>"+
							"<td class='w80 center'>"+list[i]['user_role']+"</td>"+
							"<td class='w80 center'>"+condition+"</td>"+
							"<td class='w80 center'>"+
								"<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
								"<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
							"</td>"+
						"</tr>";
	}
	}
	$('#goodsApply-change-table').children().append(tableContent);
	//出差
	tableContent = "";
	count = 0;
	for(i=0;i<list.length;i++){
		if(list[i]['type'] == "out"){
			var condition = "";
			count++;
			if(list[i]['value'] == 0){
				condition = "所有";
			}else if(list[i]['value'] == 1){
				condition = "跨市出差";
			}
			tableContent += "<tr class='bg-fa'>"+
							"<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
							"<td class='w80 center'>"+count+"</td>"+
							"<td class='w80 center'>"+list[i]['user_role']+"</td>"+
							"<td class='w80 center'>"+condition+"</td>"+
							"<td class='w80 center'>"+
								"<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
								"<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
							"</td>"+
						"</tr>";
	}
	}
	$('#out-change-table').children().append(tableContent);

  //招聘
  tableContent = "";
  count = 0;
  for(i=0;i<list.length;i++){
    if(list[i]['type'] == "recruit"){
      var condition = "";
      count++;
      if(list[i]['value'] == 0){
        condition = "所有";
      }
      tableContent += "<tr class='bg-fa'>"+
              "<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
              "<td class='w80 center'>"+count+"</td>"+
              "<td class='w80 center'>"+list[i]['user_role']+"</td>"+
              "<td class='w80 center'>"+condition+"</td>"+
              "<td class='w80 center'>"+
                "<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
                "<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
              "</td>"+
            "</tr>";
  }
  }
  $('#recruit-change-table').children().append(tableContent);

  //转正申请
  tableContent = "";
  count = 0;
  for(i=0;i<list.length;i++){
    if(list[i]['type'] == "positive_apply"){
      var condition = "";
      count++;
      if(list[i]['value'] == 0){
        condition = "所有";
      }
      tableContent += "<tr class='bg-fa'>"+
              "<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
              "<td class='w80 center'>"+count+"</td>"+
              "<td class='w80 center'>"+list[i]['user_role']+"</td>"+
              "<td class='w80 center'>"+condition+"</td>"+
              "<td class='w80 center'>"+
                "<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
                "<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
              "</td>"+
            "</tr>";
  }
  }
  $('#positive_apply-change-table').children().append(tableContent);

  //离职工作交接
  tableContent = "";
  count = 0;
  for(i=0;i<list.length;i++){
    if(list[i]['type'] == "quit_apply"){
      var condition = "";
      count++;
      if(list[i]['value'] == 0){
        condition = "所有";
      }
      tableContent += "<tr class='bg-fa'>"+
              "<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
              "<td class='w80 center'>"+count+"</td>"+
              "<td class='w80 center'>"+list[i]['user_role']+"</td>"+
              "<td class='w80 center'>"+condition+"</td>"+
              "<td class='w80 center'>"+
                "<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
                "<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
              "</td>"+
            "</tr>";
  }
  }
  $('#quit_apply-change-table').children().append(tableContent);

  //印鉴申请
  tableContent = "";
  count = 0;
  for(i=0;i<list.length;i++){
    if(list[i]['type'] == "seal"){
      var condition = "";
      count++;
      if(list[i]['value'] == 0){
        condition = "所有";
      }
      tableContent += "<tr class='bg-fa'>"+
              "<td class='w80 center hidden'>"+list[i]['procedure_id']+"</td>"+
              "<td class='w80 center'>"+count+"</td>"+
              "<td class='w80 center'>"+list[i]['user_role']+"</td>"+
              "<td class='w80 center'>"+condition+"</td>"+
              "<td class='w80 center'>"+
                "<button class='btn btn-default' onclick='changeProcess(this)'>修改</button>&nbsp"+
                "<button class='btn btn-default' onclick='deleteProcess(this)'>删除</button>"+
              "</td>"+
            "</tr>";
  }
  }
  $('#seal-change-table').children().append(tableContent);

	// 修改流程
 	function changeProcess(obj){
 		//获取id、流程类型以及审批人对应职位
 		var id = $(obj).parent().parent().children().first().next().text();
 		var realID = $(obj).parent().parent().children().first().text();
 		var type = $(obj).parent().parent().parent().children().first().children().first().next().text();
 		if(type == "请假"){
 			var nodeValue = $('#leave-change-table').children().children().eq(id).children().eq(2).text();
 		}else if(type == "加班"){
 			var nodeValue = $('overtime-change-table').children().children().eq(id).children().eq(2).text();
 		}else if(type == "费用申请"){
 			var nodeValue = $('#goodsApply-change-table').children().children().eq(id).children().eq(2).text();
 		}else if(type == "出差"){
 			var nodeValue = $('#out-change-table').children().children().eq(id).children().eq(2).text();
 		}else if(type == "招聘"){
      var nodeValue = $('#recruit-change-table').children().children().eq(id).children().eq(2).text();
    }
 		//找到原来的流程对应的审批人，将其默认显示
 		if(nodeValue == "申请人直属上司"){
 			nodeValue = "d_admin";
 		}else if(nodeValue == "上一级部门主管"){
 			nodeValue = "d2_admin";
 		}else if(nodeValue == "人事行政部主管"){
 			nodeValue = "hr_admin";
 		}else if(nodeValue == "总经理"){
 			nodeValue = "ceo";
 		}
 		if(nodeValue!="d_admin" && nodeValue!="hr_admin" && nodeValue!="ceo" && nodeValue != "d2_admin"){
 			nodeValue = "d_admin";
 		}
 		// alert(nodeValue);
 		$("#change_process_node").find("option[value="+ nodeValue +"]").attr("selected",true);
 		//显示默认ID，包括用户看到的ID和真实的ID
 		$('#process_id').val(realID);
 		$('#change_process_id').val(type+" - "+id);
 		//根据不同的类型显示审批条件更改选项
 		if(type == "请假"){
 			var newCondition = "<p>请假天数>= "+"<input id='updateCondition' class='form-control inline w80' />"+" 天</p>";
 		}else if(type == "加班"){
 			var newCondition =  "<select class='form-control' id='updateCondition'>"+
                					"<option value='0'>周末+法定节假日</option>"+
              					"</select>";
 		}else if(type == "费用申请"){
 			var newCondition =  "<select class='form-control' id='updateCondition'>"+
                					"<option value='0'>所有</option>"+
                					"<option value='1'>费用报销金额 > 对应费用申请</option>"+
              					"</select>";
 		}else if(type == "出差"){
 			var newCondition =  "<select class='form-control' id='updateCondition'>"+
                					"<option value='0'>所有</option>"+
                					"<option value='1'>跨市出差</option>"+
              					"</select>";
 		}else if(type == "招聘"){
      var newCondition =  "<select class='form-control' id='updateCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }else if(type == "转正申请"){
      var newCondition =  "<select class='form-control' id='updateCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }else if(type == "离职工作交接"){
      var newCondition =  "<select class='form-control' id='updateCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }else if(type == "印鉴申请"){
      var newCondition =  "<select class='form-control' id='updateCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }
 		$("#change_process_condition").append(newCondition);
 		//显示模态框
    	var ySet = (window.innerHeight - $("#change-process-div").height())/3;
    	var xSet = (window.innerWidth - $("#change-process-div").width())/2;
    	$("#change-process-div").css("top",ySet);
    	$("#change-process-div").css("left",xSet);
    	$("#change-process-div").modal({show:true});
  	}
  	//添加流程
  	function addProcess(obj){
  		//显示所添加流程的类型
  		var type = $(obj).parent().children().first().children().children().first().children().eq(1).text();
  		$('#add_process_type').val(type);
  		//根据不同的流程类型，给出不同的审批条件
  		if(type == "请假"){
 			var newCondition = "<p>请假天数>= "+"<input id='addCondition' class='form-control inline w80' />"+" 天</p>";
 		}else if(type == "加班"){
 			var newCondition =  "<select class='form-control' id='addCondition'>"+
                					"<option value='0'>周末+法定节假日</option>"+
              					"</select>";
 		}else if(type == "费用申请"){
 			var newCondition =  "<select class='form-control' id='addCondition'>"+
                					"<option value='0'>所有</option>"+
                					"<option value='1'>费用报销金额 > 对应费用申请</option>"+
              					"</select>";
 		}else if(type == "出差"){
 			var newCondition =  "<select class='form-control' id='addCondition'>"+
                					"<option value='0'>所有</option>"+
                					"<option value='1'>跨市出差</option>"+
              					"</select>";
 		}else if(type == "招聘"){
      var newCondition =  "<select class='form-control' id='addCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }else if(type == "转正申请"){
      var newCondition =  "<select class='form-control' id='addCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }else if(type == "离职工作交接"){
      var newCondition =  "<select class='form-control' id='addCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }else if(type == "印鉴申请"){
      var newCondition =  "<select class='form-control' id='addCondition'>"+
                          "<option value='0'>所有</option>"+
                        "</select>";
    }
 		$("#add_process_condition").append(newCondition);
 		//显示模态框
  		var ySet = (window.innerHeight - $("#add-process-div").height())/3;
    	var xSet = (window.innerWidth - $("#add-process-div").width())/2;
    	$("#add-process-div").css("top",ySet);
    	$("#add-process-div").css("left",xSet);
    	$("#add-process-div").modal({show:true});
  	}
  	//删除流程
  	function deleteProcess(obj){
  		var id = $(obj).parent().parent().children().first().text();
  		$.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/delProcedure',
        data:{'procedure_id':id},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","删除流程成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","操作失败！");
          }else if(result.code == -3){
            showHint("提示信息","参数错误！");
          }else if(result.code == -90){
            showHint("提示信息","无权限删除！");
          }
        }
      });
  	}
  	//当修改完流程时，将模态框里的审批条件置空
  	function recover(){
  		// debugger;
  		$("#change_process_condition").children().remove();
  		var newCondition = "";
  		$("#change_process_condition").append(newCondition);

  		$("#add_process_condition").children().remove();
  		var newCondition = "";
  		$("#add_process_condition").append(newCondition);
  	}

  	function sendProcessChange(){
  		if($('#updateCondition').val()==""){
  			showHint("提示信息","请输入审批条件！");
  		}else{
  		var id = $('#process_id').val();
  		var role = $('#change_process_node').val();
  		var order;
  		if(role == "d_admin"){
  			order = "10";
  		}else if(role == "d2_admin"){
  			order = "15";
  		}else if(role == "hr_admin"){
  			order = "20";
  		}else if(role == "ceo"){
  			order = "30";
  		}
  		var type = $('#change_process_id').val().split(' - ')[0];
  		if(type == "请假"){
  			type = "leave";
  		}else if(type == "加班"){
  			type = "overtime";
  		}else if(type == "费用申请"){
  			type = "goods_apply";
  		}else if(type == "出差"){
  			type = "out";
  		}else if(type == "招聘"){
        type = "recruit";
      }else if(type == "转正申请"){
        type = "positive_apply";
      }else if(type == "离职工作交接"){
        type = "quit_apply";
      }else if(type == "印鉴申请"){
        type = "seal";
      }
  		var value = $('#updateCondition').val();
  		$.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/editProcedure',
        data:{'procedure_id':id,'user_role':role,'type':type,'value':value,'procedure_order':order},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","修改流程成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -4){
            showHint("提示信息","审批人或者审批条件取值不正确！");
          }else if(result.code == -3){
            showHint("提示信息","参数错误！");
          }else if(result.code == -90){
            showHint("提示信息","无权限修改！");
          }
        }
      	});
  		}
  	}
  	function sendProcessAdd(){
  		if($('#addCondition').val() == ""){
  			showHint("提示信息","请输入审批条件！");
  		}else{
  			var type = $('#add_process_type').val();
  			if(type == "请假"){
  			type = "leave";
  			}else if(type == "加班"){
  			type = "overtime";
  			}else if(type == "费用申请"){
  			type = "goods_apply";
  			}else if(type == "出差"){
  			type = "out";
  			}else if(type == "招聘"){
        type = "recruit";
        }else if(type == "转正申请"){
          type = "positive_apply";
        }else if(type == "离职工作交接"){
          type = "quit_apply";
        }else if(type == "印鉴申请"){
          type = "seal";
        }

  			var value = $('#addCondition').val();

  			var role = $('#add_process_node').val();
  			var order;
  			if(role == "d_admin"){
  				order = "10";
  			}else if(role == "d2_admin"){
  				order = "15";
  			}else if(role == "hr_admin"){
  				order = "20";
  			}else if(role == "ceo"){
  				order = "30";
  			}
  			console.log([role,type,value,order]);
  			$.ajax({
        	type:'post',
        	dataType:'json',
        	url:'/ajax/addProcedure',
        	data:{'user_role':role,'type':type,'value':value,'procedure_order':order},
        	success:function(result){
          	if(result.code == 0){
            	showHint("提示信息","添加流程成功！");
            	setTimeout(function(){location.reload();},1200);
          	}else if(result.code == -4){
            	showHint("提示信息","审批人或者审批条件取值不正确！");
            }else if(result.code == -5){
            	showHint("提示信息","该审批人已有审批条件！");
            }else if(result.code == -6){
            	showHint("提示信息","参数错误2！");
          	}else if(result.code == -3){
            	showHint("提示信息","参数错误！");
          	}else if(result.code == -90){
            	showHint("提示信息","无权限修改！");
          	}else if(result.code == -1){
              showHint("提示信息","未知错误！");
            }
        }
      	});
  		}

  	}
</script>