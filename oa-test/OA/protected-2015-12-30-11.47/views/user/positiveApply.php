<?php
echo "<script type='text/javascript'>";
echo "console.log('positiveApply');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="center bor-1-ddd">
	<div class="left mb15">
	    <button class="btn btn-default ml10 mt10 f18px" onclick="location.href='/user/personalPositiveApply';"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回</button>
	</div>
    <div class="pd20 ">
    	

	<table class="table table-bordered center m0" id="positive-table">
		<caption class="p00">
		  	<!-- 标题 -->
          	<h3 class="center black bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd m0 pd20">转正申请</h3>
        </caption>
		<tr>
			<th class="w50 center bg-fa">姓名</th>
			<td><?php if(!empty($this->user)) echo $this->user->cn_name;?></td>
			<th class="w50 center bg-fa">部门</th>
			<td colspan="2"><?php if(!empty($this->user)) echo $this->user->department->name;?></td>
			<th class="w50 center bg-fa">职位</th>
			<td colspan="2"><?php if(!empty($this->user)) echo $this->user->title;?></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">入职时间</th>
			<td><?php if(!empty($this->user)) echo $this->user->entry_day;?></td>
			<th class="w50 center bg-fa">转正前薪资</th>
			<td colspan="2"><input class="form-control inline w80" id="probation-salary">&nbsp;元/月</td>
			<th class="w50 center bg-fa">工作年限</th>
			<td colspan="2"><input class="form-control inline w80" id="work-time">&nbsp;年</td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">序号</th>
			<th class="w200 center bg-fa">工作内容</th>
			<th class="w50 center bg-fa">占比</th>
			<th class="w100 center bg-fa">参考标准</th>
			<th class="w80 center bg-fa">工作量</th>
			<th class="w50 center bg-fa">完成率</th>
			<th class="w50 center bg-fa">延误率</th>
			<th class="w50 center bg-fa">返工率</th>
		</tr>
		<tr class="work-content-tr">
			<td>1</td>
			<td><input class="form-control content-input" placeholder="请输入工作内容"></td>
			<td><input class="form-control inline w50 percent-input">&nbsp;%</td>
			<td><input class="form-control standard-input" placeholder="请输入参考标准"></td>
			<td><input class="form-control workload-input" placeholder="请输入工作量"></td>
			<td><input class="form-control inline w50 complete-input" >&nbsp;%</td>
			<td><input class="form-control inline w50 delay-input" >&nbsp;%</td>
			<td><input class="form-control inline w50 redo-input" >&nbsp;%</td>
		</tr>
		<tr class="work-content-tr">
			<td>2</td>
			<td><input class="form-control content-input" placeholder="请输入工作内容"></td>
			<td><input class="form-control inline w50 percent-input">&nbsp;%</td>
			<td><input class="form-control standard-input" placeholder="请输入参考标准"></td>
			<td><input class="form-control workload-input" placeholder="请输入工作量"></td>
			<td><input class="form-control inline w50 complete-input" >&nbsp;%</td>
			<td><input class="form-control inline w50 delay-input" >&nbsp;%</td>
			<td><input class="form-control inline w50 redo-input" >&nbsp;%</td>
		</tr>
		<tr class=" bg-fa">
			<td colspan="8"><a class="pointer" onclick="newLine(this);">增加一行</a></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">员工自评</th>
			<td colspan="7"><textarea class="form-control" id="self-assessment"></textarea></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">个人规划</th>
			<td colspan="7"><textarea class="form-control" id="personal-plan"></textarea></td>
		</tr>
		<tr>
			<th class="w50 center bg-fa">意见</br>与建议</th>
			<td colspan="7"><textarea class="form-control" id="opinion"></textarea></td>
		</tr>
	</table>
    </div>
    <button class="btn btn-success btn-lg w100 mb15" onclick="sendpositiveApply();">提交</button>
</div>


<!-- js -->
<script type="text/javascript">

	// 增加一行-工作内容
	var row_num = 3;
	function newLine(obj){
		var str = "<tr class='work-content-tr'><td>"+row_num+"</td>"+
			"<td><input class='form-control content-input' placeholder='请输入工作内容'></td>"+
			"<td><input class='form-control inline w50 percent-input'>&nbsp;%</td>"+
			"<td><input class='form-control standard-input' placeholder='请输入参考标准'></td>"+
			"<td><input class='form-control workload-input' placeholder='请输入工作量'></td>"+
			"<td><input class='form-control inline w50 complete-input' >&nbsp;%</td>"+
			"<td><input class='form-control inline w50 delay-input' >&nbsp;%</td>"+
			"<td><input class='form-control inline w50 redo-input' >&nbsp;%</td></tr>";
		$(obj).parent().parent().before(str);
		row_num++;
	}

	// 发送转正申请
	function sendpositiveApply(){
		var probation_salary = $("#probation-salary").val();
		var work_time = $("#work-time").val();
		var opinion = $("#opinion").val();
		var personal_plan = $("#personal-plan").val();
		var self_assessment = $("#self-assessment").val();

		var d_pattern = /^\d+$/;
		// 转正前薪资的验证
		if(probation_salary == ""){
			showHint("提示信息","请输入转正前薪资");
			$("#probation-salary").focus();
			return false;
		}else if(!d_pattern.exec(probation_salary)){
			showHint("提示信息","转正前薪资格式输入不正确");
			$("#probation-salary").focus();
			return false;
		}

		// 工作年限的验证
		if(work_time == ""){
			showHint("提示信息","请输入工作年限");
			$("#work-time").focus();
			return false;
		}else if(!d_pattern.exec(work_time)){
			showHint("提示信息","工作年限格式输入不正确");
			$("#work-time").focus();
			return false;
		}

		var work_content = new Array();
		var content_flag = 0;
		var f_flag = 0;
		var serial = 1;
		$("#positive-table").find("tr.work-content-tr").each(function(){
			var content = $(this).find(".content-input").val();
			var percent = $(this).find(".percent-input").val();
			var standard = $(this).find(".standard-input").val();
			var workload = $(this).find(".workload-input").val();
			var complete = $(this).find(".complete-input").val();
			var delay = $(this).find(".delay-input").val();
			var redo = $(this).find(".redo-input").val();

			var row_content_flag = 0;
			if(content != ""||percent !=""||standard != ""||workload != ""||complete != ""||delay != ""||redo != ""){
				content_flag = 1;
				row_content_flag = 1;

				// 工作内容的验证
				if(content == ""){
					showHint("提示信息","请输入工作内容");
					$(this).find(".content-input").focus();
					f_flag = 1;
					return false;
				}

				// 占比的验证
				if(percent == ""){
					showHint("提示信息","请输入工作占比");
					$(this).find(".percent-input").focus();
					f_flag = 1;
					return false;
				}else{
					if(!d_pattern.exec(percent)){
						alert(percent);
						showHint("提示信息","占比格式输入错误");
						$(this).find(".percent-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(percent) >100 || parseInt(percent) <= 0){
						showHint("提示信息","占比为1到100之间的数值");
						$(this).find(".percent-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 工作量的验证
				if(workload == ""){
					showHint("提示信息","请输入工作量");
					$(this).find(".workload-input").focus();
					f_flag = 1;
					return false;
				}

				// 完成率的验证
				if(complete == ""){
					showHint("提示信息","请输入工作完成率");
					$(this).find(".complete-input").focus();
					f_flag = 1;
					return false;
				}else{
					if(!d_pattern.exec(complete)){
						showHint("提示信息","完成率格式输入错误");
						$(this).find(".complete-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(complete) >100 || parseInt(complete) < 0){
						showHint("提示信息","完成率为1到100之间的数值");
						$(this).find(".complete-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 延误率的验证
				if(delay == ""){
					showHint("提示信息","请输入工作延误率");
					$(this).find(".delay-input").focus();
					f_flag = 1;
					return false;
				}else{
					if(!d_pattern.exec(delay)){
						showHint("提示信息","延误率格式输入错误");
						$(this).find(".delay-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(delay) >100 || parseInt(delay) < 0){
						showHint("提示信息","延误率为1到100之间的数值");
						$(this).find(".delay-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 返工率的验证
				if(redo == ""){
					showHint("提示信息","请输入工作返工率");
					$(this).find(".redo-input").focus();
					f_flag = 1;
					return false;
				}else{
					if(!d_pattern.exec(redo)){
						showHint("提示信息","返工率格式输入错误");
						$(this).find(".redo-input").focus();
						f_flag = 1;
						return false;
					}else if(parseInt(redo) >100 || parseInt(redo) < 0){
						showHint("提示信息","返工率为1到100之间的数值");
						$(this).find(".redo-input").focus();
						f_flag = 1;
						return false;
					}
				}

				// 完成率+延误率+返工率是否等于100%的验证
				if(parseInt(complete)+parseInt(delay)+parseInt(redo) != 100){
					showHint("提示信息","完成率+延误率+返工率必须等于100%");
					f_flag = 1;
					return false;
				}
			}

			// 如果这一行有内容 并且 验证通过，则加入到数组中
			if(row_content_flag == 1 && f_flag == 0){
				work_content.push({"serial":serial,"content":content, "proportion":percent, "reference":standard, "quantity":workload, "completion_rate":complete, "delay_rate":delay, "rework_rate":redo});
				serial++;
			}
		});
		if(content_flag == 0){
			showHint("提示信息","请输入工作内容");
		}else if(f_flag == 0){
			var total_percent = 0;
			$.each(work_content, function(){
				total_percent += parseInt(this.proportion);
			});
			if(total_percent != 100){
				showHint("提示信息","占比总和必须等于100%");
				return false;
			}else if(self_assessment == ""){
				showHint("提示信息","请输入员工自评");
				$("#self-assessment").focus();
				return false;
			}else if(personal_plan == ""){
				showHint("提示信息","请输入个人规划");
				$("#personal-plan").focus();
				return false;
			}else if(opinion == ""){
				showHint("提示信息","请输入意见与建议");
				$("#opinion").focus();
				return false;
			}else{
				$.ajax({
	                type:'post',
	                url:'/ajax/applyQualify',
	                dataType:'json',
	                data:{'trial_salary':probation_salary, 'work_life':work_time, 'contents':work_content, 'evaluation':self_assessment, 'plan':personal_plan, 'suggest':opinion},
	                success:function(result){
	                    if(result.code == 0){
	                        showHint("提示信息","转正申请提交成功");
	                        var href_str = "/user/positiveApplyDetail/id/"+result.id;
	                        setTimeout(function(){location.href = href_str;},1200);  
	                    }else if(result.code == '-1'){
	                        showHint("提示信息","转正申请提交失败");
	                    }else if(result.code == '-2'){
	                        showHint("提示信息","参数错误");
	                    }else if(result.code == '-3'){
	                        showHint("提示信息","你已经转正了，不需要重复提交");
	                    }else{
	                        showHint("提示信息","你没有权限执行此操作");
	                    }
	                }
	            });
			}
		}
	}
</script>