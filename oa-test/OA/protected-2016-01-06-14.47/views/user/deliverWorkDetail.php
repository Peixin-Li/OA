<?php
echo "<script type='text/javascript'>";
echo "console.log('deliverWorkDetail');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />


<div class="center">
	<?php 
		// 初始化变量
		if(!empty($apply)){
			// 交接人
			$handover_user = $handover_user_info; 
			// 行政专员
			$commissioner =  $commissioner_info;
			// 人事总监
			$admin_user = $admin_user_info;
			// 人事专员
			$hr_user = $hr_user_info;
			// IT运维工程师
			$web_user = $web_user_info;

			// 部门交接内容
			$work = $work_info;
			// 行政交接内容
			$admin = $admin_info;
			// 人事交接内容
			$hr = $hr_info;
			// IT交接内容
			$it = $it_info;
		}
	?>
	<!-- 标题 -->
	<h4 class="pd20 m0 bor-l-1-ddd bor-t-1-ddd bor-r-1-ddd">
		<strong>工作交接详情</strong>
	</h4>
	<table class="table table-bordered center m0">
		<tbody>
			<tr>
            <td id="apply-id" class="hidden"><?php echo $apply->id; ?></td>
				<th class="w100 center">姓名</th>
                <td class="w100"><?php echo $apply->user->cn_name;?></td>
				<th class="w100 center">部门</th>
				<td class="w100"><?php echo $apply->user->department->name;?></td>
				<th class="w100 center">职位</th>
				<td class="w100"><?php echo $apply->user->title;?></td>
			</tr>
			<tr>
				<th class="w100 center">入职时间</th>
				<td colspan="2"><?php echo $apply->user->entry_day;?></td>
				<th class="w100 center">离职时间</th>
				<td colspan="2"><?php echo $apply->quit_date;?></td>
			</tr>
			<tr>
				<th class="w100 center">离职原因</th>
				<td colspan="5"><?php echo $apply->quit_reason;?></td>
			</tr>
			<!-- 未完成时，只有本人、交接人、监督人才看到这一部分, 完成了以后就所有人都可以看见 -->
			<?php if(((!empty($user) && ($user->user_id == $apply->user->user_id)) || (!empty($work) && ($supervision_info->user_id == $user->user_id)) || ($user->user_id == $apply->handover_user_id)) || (!empty($work) && ($work->status == "success"))): ?>
			<tr>
				<th class="w100 center">部门</th>
				<th class="center" colspan="3">交接事项</th>
				<th class="w100 center">经手人</br>（接收人）</th>
				<th class="w100 center">监交人</br>（部门负责人）</th>
			</tr>
			<!-- 部门交接 -->
			<tr>
				<!-- 部门 -->
				<td class="center">部门</td>
				<!-- 显示交接事项 -->
				<td colspan="3" class="left">
					<?php
						if(!empty($work)){
							if($work_details){
								// 遍历工作交接内容
								foreach($work_details as $key => $detail){
									// 如果是数组则用新的方法显示，如果不是就用span输出
									if(is_array($detail->jsonContent)){
										if(!empty($detail->jsonContent)){
											foreach($detail->jsonContent as $dkey => $drow){
												echo "<div><label>{$dkey}：</label><span>";
												echo join($drow, '、');
												echo "</span></div>";
											}
										}else{
											echo "无";
										}
									}else{
										echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$detail->content}&nbsp;";
										if($key % 2 != 0){ echo '<br />'; }
									}
		        				}
							}
					    }else{
							echo "暂无";
						}
					?>
				</td>
				<!-- 指定交接人 -->
				<td>
					<?php if(!empty($work)): ?>
					<!-- 显示签名 -->
					<p class="f15px m0"><?php echo $handover_user->cn_name; ?></p>
					<?php else: ?>
					<!-- 显示操作按钮 -->
					<?php if($user->user_id == $apply->handover_user_id): ?>
					<button class="btn btn-success" onclick="showDepartment();">填写交接事项</button>
					<?php endif; ?>
					<?php endif; ?>
				</td>
				<!-- 部门负责人 -->
				<td>
					<?php if(!empty($work) && $work->status == 'success'): ?>
					<!-- 显示签名 -->
					<p class="f15px m0"><?php echo $supervision_info->cn_name; ?></p>
					<?php elseif(!empty($user) && !empty($work) && $user->user_id == $work->supervision_id): ?>
					<!-- 显示操作按钮 -->
					<button class="btn btn-success" onclick="showDepartmentConfirm('department');">确认交接</button>
					<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>


			<!-- 未完成时，只有本人、交接人、监督人才看到这一部分, 完成了以后就所有人都可以看见 -->
			<?php 
				$admin_tag = ((((!empty($user) && ($user->user_id == $apply->user->user_id)) || (!empty($admin) && ( $admin_sid == $user->user_id)) || ($user->user_id == $commissioner->user_id))) || (!empty($admin) && $admin->status == "success"));
				$hr_tag = ((((!empty($user) && ($user->user_id == $apply->user->user_id)) || (!empty($hr) && ( $hr_sid == $user->user_id)) || ($user->user_id == $hr_user->user_id))) || (!empty($hr) && $hr->status == "success"));
				if(!empty($work) && $work->status == "success" && ($admin_tag || $hr_tag || (!empty($user) && $user->user_id == $admin_user->user_id))): 
			?>
			<!-- 行政部交接 -->
			<tr>
				<!-- 部门 -->
				<td rowspan="2">人事行政部</td>
				<!-- 显示交接事项 -->
				<td colspan="3" class="left">
					<?php
						if(!empty($admin)){
							if($admin_details = $admin_details_info ){
								// 遍历行政交接内容
								foreach($admin_details as $key=>$detail){
									// 判断是否为数组，不是的话就用span输出
									if(is_array($detail->jsonContent)){
										if(!empty($detail->jsonContent)){
											foreach($detail->jsonContent as $dkey => $drow){
												echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$drow}&nbsp;";
												if($dkey % 2 != 0){
										    		echo '<br />';
										    	}
											}
										}else{
											echo "无";
										}
									}else{
										echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$detail->content}&nbsp;";
								    	if($key % 2 != 0){
								    		echo '<br />';
								    	}
									}
							    }
							}
						}else{
							echo "暂无";
						}
					?>
				</td>
				<!-- 行政专员 -->
				<td>
					<?php if($user->user_id == $commissioner->user_id && empty($admin)): ?>
					<!-- 显示交接事项按钮 -->
					<button class="btn btn-success" onclick="showAdministration();">填写交接事项</button>
					<?php endif; ?>
					<?php if(!empty($admin)): ?>
					<p class="f15px m0"><?php echo $commissioner->cn_name; ?></p>
					<?php endif; ?>
				</td>
				<!-- 人事总监 -->
				<td rowspan="2">
					<?php if(!empty($admin) && !empty($hr)): ?>
					<?php if($admin->status == "wait" && $hr->status == "wait" && !empty($user) && $user->user_id == $admin_user->user_id):?>
					<button class="btn btn-success" onclick="showDepartmentConfirm('administration');">确认交接</button>
					<?php elseif($admin->status == "success" && $hr->status == "success"): ?>
					<p class="f15px m0"><?php echo $admin_user->cn_name; ?></p>
					<?php endif; ?>
					<?php endif; ?>
				</td>
			</tr>
			<!-- 人事部交接 -->
			<tr>
				<!-- 显示交接事项 -->
				<td colspan="3" class="left">
					<?php
						if(!empty($hr)){
							if($hr_details =  $hr_details_info ){
								// 遍历人事部交接内容
								foreach($hr_details as $key=>$detail){
									// 判断是否为数组，不是的话就用span输出
									if(is_array($detail->jsonContent)){
										if(!empty($detail->jsonContent)){
											foreach($detail->jsonContent as $dkey => $drow){
												echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$drow}&nbsp;";
												if($dkey % 2 != 0){
										    		echo '<br />';
										    	}
											}
										}else{
											echo "无";
										}
									}else{
										echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$detail->content}&nbsp;";
										if($key % 2 != 0){ 
											echo '<br />';
										}
									}
								}
							}
						}else{
							echo "暂无";
						}
					?>
				</td>
				<!-- 人事专员 -->
				<td>
					<?php if($user->user_id == $hr_user->user_id && empty($hr) && !empty($admin)): ?>
					<!-- 显示交接事项按钮 -->
					<button class="btn btn-success" onclick="showHR();">填写交接事项</button>
					<?php endif; ?>
					<?php if(!empty($hr)): ?>
					<p class="f15px m0"><?php echo $hr_user->cn_name; ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>


			<!-- 未完成时，只有本人、交接人、监督人才看到这一部分, 完成了以后就所有人都可以看见 -->
			<?php 
				if((((!empty($user) && ($user->user_id == $apply->user->user_id)) || (!empty($it) && !empty($it->supervision_id) && ( $it_sid == $user->user_id)) || (!empty($user) && ($user->user_id == $web_user->user_id))) || (!empty($it) && ($it->status == "success"))) && !empty($work) && (!empty($admin) && $admin->status == "success")): 
			?>
			<!-- IT运维部交接 -->
			<tr>
				<!-- 部门 -->
				<td>IT运维部</td>
				<!-- 显示交接事项 -->
				<td colspan="3" class="left">
					<?php 
						if(!empty($it)){
							if($it_details = $it_details_info ){
								// 遍历IT运维部交接内容
								foreach($it_details as $key=>$detail){
									// 判断是否为数组，不是的话就用span输出
									if(is_array($detail->jsonContent)){
										if(!empty($detail->jsonContent)){
											foreach($detail->jsonContent as $dkey => $drow){
												echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$drow}&nbsp;";
												if($dkey % 2 != 0){
										    		echo '<br />';
										    	}
											}
										}else{
											echo "无";
										}
									}else{
										echo "<span class='glyphicon glyphicon-ok-sign b5c'></span>&nbsp;{$detail->content}&nbsp;";
										if($key % 2 != 0){ 
											echo '<br />';
										}
									}
								}
							}
						}else{
							echo "暂无";
						}
					?>
				</td>
				<!-- IT运维工程师 -->
				<td colspan="2">
					<?php if(empty($it) && $user->user_id == $web_user->user_id): ?>
					<button class="btn btn-success" onclick="showIT();">填写交接事项</button>
					<?php endif; ?>
					<?php if(!empty($it) && $it->status == "success" && !empty($web_user)): ?>
					<p class="f15px m0"><?php echo $web_user->cn_name; ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td colspan="6" class="left">
					<h4 class="center f18px">离职声明</h4>
					<p class="f15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;本人已办妥离职交接手续，特声明本人与公司就本人的劳动权益无任何纠纷和争议，发生违反保密协议及竞业条款约定之情况例外，本人在协议期内将严格遵守保密竞业相关条款。</p>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- 查看离职申请按钮 -->
	<button class="btn btn-lg btn-success mr20 mb50 mt20" onclick="location.href='user/quitDetail/id/<?php echo $apply->id;?>'">查看离职申请</button>
	<?php if(!empty($user) && !empty($apply) && $user->user_id == $apply->user->user_id): ?>
	<!-- 返回按钮 -->
	<button class="btn btn-lg btn-default w100 mb50 mt20" onclick="location.href='/user/personalQuitRecord';">返回</button>
	<?php endif; ?>
</div>
<!-- 部门交接模态框 -->
<div id="department-deliver-div" class="modal fade in hint bor-rad-5 w700" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">工作交接</h4>
    </div>

    <div class="modal-body">
        <table class="table table-bordered m0">
        	<tr id="work-deliver-tr">
        		<th class="center w130"><input type="checkbox" checked onclick="switchCheckbox(this);" id="work-deliver-checkbox"><span class="pointer" onclick="$(this).prev().click();">工作交接</span></th>
        		<td>
        			<div class="pd3">
        				<input class="form-control inline w400" placeholder="请输入工作交接内容"><a class="pointer ml20" onclick="newLine('work');"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;继续添加</a>
        			</div>
        		</td>
        	</tr>
        	<tr id="item-deliver-tr">
        		<th class="center w130"><input type="checkbox" checked onclick="switchCheckbox(this);" id="item-deliver-checkbox"><span class="pointer" onclick="$(this).prev().click();">领用物品</span></th>
        		<td>
        			<div class="pd3">
        				<input class="form-control inline w400" placeholder="请输入领用物品内容"><a class="pointer ml20" onclick="newLine('item');"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;继续添加</a>
        			</div>
        		</td>
        	</tr>
        	<tr id="authority-deliver-tr">
        		<th class="center w130"><input type="checkbox" checked onclick="switchCheckbox(this);" id="authority-deliver-checkbox"><span class="pointer" onclick="$(this).prev().click();">权限回收</span></th>
        		<td>
        			<div class="pd3">
        				<input class="form-control inline w400" placeholder="请输入权限回收内容"><a class="pointer ml20" onclick="newLine('authority');"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;继续添加</a>
        			</div>
        		</td>
        	</tr>
        	<tr id="other-deliver-tr">
        		<th class="center w130"><input type="checkbox" checked onclick="switchCheckbox(this);" id="other-deliver-checkbox"><span class="pointer" onclick="$(this).prev().click();">其他</span></th>
        		<td>
        			<div class="pd3">
        				<input class="form-control inline w400" placeholder="请输入其他事项内容"><a class="pointer ml20" onclick="newLine('other');"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;继续添加</a>
        			</div>
        		</td>
        	</tr>
        </table>
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="sendDepartment();">提交</button>
    </div>
</div>
<!-- 行政部交接模态框 -->
<div id="administration-deliver-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">工作交接</h4>
    </div>

    <div class="modal-body">
        <div class="pl20">
        	<div>
        		<input type="checkbox" name="administration-checkbox" value="办公用品">&nbsp;<span class="pointer" onclick="$(this).prev().click();">办公用品</span>
        	</div>
        	<div>
        		<input type="checkbox" name="administration-checkbox" value="办公室/活动柜钥匙">&nbsp;<span class="pointer" onclick="$(this).prev().click();">办公室/活动柜钥匙</span>
        	</div>
        	<div>
        		<input type="checkbox" name="administration-checkbox" value="办公设备与物品">&nbsp;<span class="pointer" onclick="$(this).prev().click();">办公设备与物品</span>
        	</div>
        	<div>
        		<input type="checkbox" name="administration-checkbox" value="借款欠款">&nbsp;<span class="pointer" onclick="$(this).prev().click();">借款欠款</span>
        	</div>
        	<div>
        		<input type="checkbox" name="administration-checkbox" value="费用报销">&nbsp;<span class="pointer" onclick="$(this).prev().click();">费用报销</span>
        	</div>
        	<div>
        		<input type="checkbox" name="administration-checkbox" value="年假剩余 7 天">&nbsp;<span class="pointer" onclick="$(this).prev().click();">年假剩余 7 天</span>
        	</div>
        	<div class="mt5">
        		其他：<input class="w200" id="administration-other">
        	</div>
        </div>
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="sendAdministration();">提交</button>
    </div>
</div>
<!-- 人事部交接模态框 -->
<div id="hr-deliver-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">工作交接</h4>
    </div>

    <div class="modal-body">
        <div class="pl20">
        	<div class="pd5">
				1、社保（五险）&nbsp;<input class="w50 inline" id="hr-shebao-input">&nbsp;月停保
			</div>
			<div class="pd5 mt5">
				2、公积金&nbsp;<input class="w50 inline" id="hr-gongjijin-input">&nbsp;月封存，<input type="radio" name="hr-accumulation-fund" value="转移" checked>&nbsp;<span class="pointer" onclick="$(this).prev().click();">转移</span>&nbsp;&nbsp;<input type="radio" value="提取" name="hr-accumulation-fund">&nbsp;<span class="pointer" onclick="$(this).prev().click();">提取</span>
			</div>
			<div class="pd5 mt5">
				3、工资结算至&nbsp;<input class="inline w130 pointer" id="hr-end-date" value="<?php echo date('Y-m-d'); ?>">
			</div>
			<div class="pd5 mt5">
				4、离职证明：<input type="radio" value="yes" name="hr-quit-prove" checked>&nbsp;已发&nbsp;&nbsp;<input type="radio" name="hr-quit-prove" value="no">&nbsp;未发
			</div>
			<div class="pd5 mt5">
				5、其他：<input class="inline w300 form-control" id="hr-other">
			</div>
        </div>
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="sendHR();">提交</button>
    </div>
</div>
<!-- IT运维部交接模态框 -->
<div id="it-deliver-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">工作交接</h4>
    </div>

    <div class="modal-body">
        <div class="pl20">
        	<div>
        		<input type="checkbox" name="it-checkbox" value="电脑及配件">&nbsp;<span class="pointer" onclick="$(this).prev().click();">电脑及配件</span>
        	</div>
        	<div>
        		<input type="checkbox" name="it-checkbox" value="电脑密码更改及电脑用户销户">&nbsp;<span class="pointer" onclick="$(this).prev().click();">电脑密码更改及电脑用户销户</span>
        	</div>
        	<div>
        		<input type="checkbox" name="it-checkbox" value="SVN密码更改及权限关闭">&nbsp;<span class="pointer" onclick="$(this).prev().click();">SVN密码更改及权限关闭</span>
        	</div>
        	<div>
        		<input type="checkbox" name="it-checkbox" value="邮箱销号(<?php echo $apply->user->email; ?>)">&nbsp;<span class="pointer" onclick="$(this).prev().click();">邮箱销号(<?php echo $apply->user->email; ?>)</span>
        	</div>
        	<div>
        		<input type="checkbox" name="it-checkbox" value="聊天工具回收(企业QQ：<?php echo $apply->user->qq; ?>)">&nbsp;<span class="pointer" onclick="$(this).prev().click();">聊天工具回收(企业QQ：<?php echo $apply->user->qq; ?>)</span>
        	</div>
        	<div>
        		<input type="checkbox" name="it-checkbox" value="Tower帐号注销">&nbsp;<span class="pointer" onclick="$(this).prev().click();">Tower帐号注销</span>
        	</div>
        	<div>
        		<input type="checkbox" name="it-checkbox" value="测试手机回收">&nbsp;<span class="pointer" onclick="$(this).prev().click();">测试手机回收</span>
        	</div>
        	<div class="mt5">
        		其他：<input class="w200" id="it-other">
        	</div>
        </div>
    </div>

    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="sendIT();">提交</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
	// 页面初始化
	$(document).ready(function(){
		// 日期控件初始化
		$('#hr-end-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
	    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

/*-------------------------------------------各部门交接内容---------------------------------------------------*/

	// 发送交接内容
	function sendWorkDeliverContent(id, contents){
		$.ajax({
	        type:'post',
	        dataType:'json',
	        url:'/ajax/newHandoverWork',
	        data:{'id':id, 'contents':contents},
	        success:function(result){
	          	if(result.code == 0){
		            showHint("提示信息","提交成功！");
		            setTimeout(function(){location.reload();},1200);
	          	}else if(result.code == -1){
	            	showHint("提示信息","提交失败！");
	          	}else if(result.code == -2){
	            	showHint("提示信息","参数错误！");
	          	}else if(result.code == -3){
	            	showHint("提示信息","找不到该离职申请！");
	          	}else if(result.code == -4){
	            	showHint("提示信息","请勿重复提交！");
	          	}else{
	          		showHint("提示信息","你没有权限执行此操作！");
	          	}
		    }
	    });
	}

	// 发送IT运维部交接
	function sendIT(){
		var id = $("#apply-id").text();

		// 将数据添加到数组中
		var it_arr = new Array();
		$("input[name='it-checkbox']:checked").each(function(){
			it_arr.push($(this).val());
		});
		var other_str = "其他:"+$("#it-other").val();
		if($("#it-other").val() != "") it_arr.push(other_str);

		// 判断是否已填写交接内容
		if(it_arr.length < 1){
			showHint("提示信息","请至少选择一项交接内容！");
		}else{
			// 发送数据
			sendWorkDeliverContent(id, it_arr);
		}
	}

	// 显示IT交接
	function showIT(){
		var ySet = (window.innerHeight - $("#it-deliver-div").height())/3;
        var xSet = (window.innerWidth - $("#it-deliver-div").width())/2;
        $("#it-deliver-div").css("top",ySet);
        $("#it-deliver-div").css("left",xSet);
		$("#it-deliver-div").modal({show:true});
	}

	// 发送人事交接
	function sendHR(){
		// 获取数据
		var id = $("#apply-id").text();
		var hr_arr = new Array();
		var shebao = $("#hr-shebao-input").val();
		var gongjijin = $("#hr-gongjijin-input").val();
		var gongjijin_type = $("input[name='hr-accumulation-fund']:checked").val();
		var end_date = $("#hr-end-date").val();
		var quit_prove_type = $("input[name='hr-quit-prove']:checked").val();
		var other = $("#hr-other").val();

		// 验证数据
		var d_pattern = /^\d+$/;
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		var f_tag = false;
		if(!d_pattern.exec(shebao)){
			showHint("提示信息","社保停保时间格式输入不正确");
			$("#hr-shebao-input").focus();
			f_tag = true;
		}else if(parseInt(shebao) > 12 || parseInt(shebao) < 1){
			showHint("提示信息","请输入1到12的数字");
			$("#hr-shebao-input").focus();
			f_tag = true;
		}else if(!d_pattern.exec(gongjijin)){
			showHint("提示信息","公积金封存时间格式输入不正确");
			$("#hr-gongjijin-input").focus();
			f_tag = true;
		}else if(parseInt(gongjijin) > 12 || parseInt(gongjijin) < 1){
			showHint("提示信息","请输入1到12的数字");
			$("#hr-gongjijin-input").focus();
			f_tag = true;
		}else if(!date_pattern.exec(end_date)){
			showHint("提示信息","工资结算日期输入格式错误");
			$("#hr-end-date").focus();
			f_tag = true;
		}else{
			// 对数据进行转换
			var content_1 = "社保(五险)"+shebao+"月停保";
			var content_2 = "公积金"+gongjijin+"月封存["+gongjijin_type+"]";
			var content_3 = "工资结算至"+end_date;
			if(quit_prove_type == "yes"){
				var content_4 = "离职证明[已发]";
			}else{
				var content_4 = "离职证明[未发]";
			}
			var content_5 = "其他:"+other;

			// 将数据添加到数组中
			hr_arr.push(content_1);
			hr_arr.push(content_2);
			hr_arr.push(content_3);
			hr_arr.push(content_4);
			if(other != "") hr_arr.push(content_5);

			// 发送数据
			sendWorkDeliverContent(id, hr_arr);
		}
	}

	// 显示人事交接
	function showHR(){
		var ySet = (window.innerHeight - $("#hr-deliver-div").height())/3;
        var xSet = (window.innerWidth - $("#hr-deliver-div").width())/2;
        $("#hr-deliver-div").css("top",ySet);
        $("#hr-deliver-div").css("left",xSet);
		$("#hr-deliver-div").modal({show:true});
	}

	// 发送行政交接
	function sendAdministration(){
		var id = $("#apply-id").text();

		// 将数据添加到数组中
		var administration_arr = new Array();
		$("input[name='administration-checkbox']:checked").each(function(){
			administration_arr.push($(this).val());
		});
		var other_str = "其他:"+$("#administration-other").val();
		if($("#administration-other").val() != "") administration_arr.push(other_str);

		// 判断是否已填写交接内容
		if(administration_arr.length < 1){
			showHint("提示信息","请至少选择一项交接内容！");
		}else{
			// 发送数据
			sendWorkDeliverContent(id, administration_arr);
		}
	}

	// 显示行政交接
	function showAdministration(){
		var ySet = (window.innerHeight - $("#administration-deliver-div").height())/3;
        var xSet = (window.innerWidth - $("#administration-deliver-div").width())/2;
        $("#administration-deliver-div").css("top",ySet);
        $("#administration-deliver-div").css("left",xSet);
		$("#administration-deliver-div").modal({show:true});
	}

/*-------------------------------------------发送监交确认信息---------------------------------------------------*/

	// 发送人事监交
	function sendAdministrationConfirm(){
		var id = $("#apply-id").text();
		$.ajax({
	        type:'post',
	        dataType:'json',
	        url:'/ajax/newConfirmWorkHandover',
	        data:{'id':id},
	        success:function(result){
	          	if(result.code == 0){
		            showHint("提示信息","确认交接成功！");
		            setTimeout(function(){location.reload();},1200);
	          	}else if(result.code == -1){
	            	showHint("提示信息","确认交接失败！");
	          	}else if(result.code == -2){
	            	showHint("提示信息","参数错误！");
	          	}else if(result.code == -3){
	            	showHint("提示信息","找不到该离职申请！");
	          	}else if(result.code == -4){
	            	showHint("提示信息","请勿重复提交！");
	          	}else{
	          		showHint("提示信息","你没有权限执行此操作！");
	          	}
		    }
	    });
	}

	// 发送部门监交
	function sendDepartmentConfirm(){
		var id = $("#apply-id").text();
		$.ajax({
	        type:'post',
	        dataType:'json',
	        url:'/ajax/confirmWorkHandover',
	        data:{'id':id},
	        success:function(result){
	          	if(result.code == 0){
		            showHint("提示信息","确认交接成功！");
		            setTimeout(function(){location.reload();},1200);
	          	}else if(result.code == -1){
	            	showHint("提示信息","确认交接失败！");
	          	}else if(result.code == -2){
	            	showHint("提示信息","参数错误！");
	          	}else if(result.code == -3){
	            	showHint("提示信息","找不到该离职申请！");
	          	}else if(result.code == -4){
	            	showHint("提示信息","请勿重复提交！");
	          	}else{
	          		showHint("提示信息","你没有权限执行此操作！");
	          	}
		    }
	    });
	}

	// 显示部门监交
	function showDepartmentConfirm(type){
		if(type == "department"){
			showConfirm("提示信息","确认交接内容无误?","确定","sendDepartmentConfirm();", "取消");
		}else{
			showConfirm("提示信息","确认交接内容无误?","确定","sendAdministrationConfirm();", "取消");
		}
	}

/*-------------------------------------------发送交接人交接内容---------------------------------------------------*/

	// 发送交接人交接
	function sendDepartment(){
		var id = $("#apply-id").text();

		// 数据初始化
		var work_arr = new Array();
		var item_arr = new Array();
		var authority_arr = new Array();
		var other_arr = new Array();
		var content_arr = new Array();

		// 为空的错误标记, 如果每一项中所有单元格都没有填写, 则为 false
		var empty_f_tag = true;

		// 判断是否有勾选此项
		if(document.getElementById("work-deliver-checkbox").checked && empty_f_tag){
			empty_f_tag = false;
			$("#work-deliver-tr").find("td").find("input").each(function(){
				if($(this).val() != ""){
					work_arr.push($(this).val());
					empty_f_tag = true;
				}
			});
			if(!empty_f_tag){
				$("#work-deliver-tr").find("td").find("input").first().focus();
				showHint("提示信息","请输入工作交接内容");
			}
		}

		// 判断是否有勾选此项
		if(document.getElementById("item-deliver-checkbox").checked && empty_f_tag){
			empty_f_tag = false;
			$("#item-deliver-tr").find("td").find("input").each(function(){
				if($(this).val() != ""){
					item_arr.push($(this).val());
					empty_f_tag = true;
				}
			});
			if(!empty_f_tag){
				$("#item-deliver-tr").find("td").find("input").first().focus();
				showHint("提示信息","请输入领用物品内容");
			}
		}

		// 判断是否有勾选此项
		if(document.getElementById("authority-deliver-checkbox").checked && empty_f_tag){
			empty_f_tag = false;
			$("#authority-deliver-tr").find("td").find("input").each(function(){
				if($(this).val() != ""){
					authority_arr.push($(this).val());
					empty_f_tag = true;
				}
			});
			if(!empty_f_tag){
				$("#authority-deliver-tr").find("td").find("input").first().focus();
				showHint("提示信息","请输入权限回收内容");
			}
		}

		// 判断是否有勾选此项
		if(document.getElementById("other-deliver-checkbox").checked && empty_f_tag){
			empty_f_tag = false;
			$("#other-deliver-tr").find("td").find("input").each(function(){
				if($(this).val() != ""){
					other_arr.push($(this).val());
					empty_f_tag = true;
				}
			});
			if(!empty_f_tag){
				$("#other-deliver-tr").find("td").find("input").first().focus();
				showHint("提示信息","请输入其他内容");
			}
		}

		// 判断是否有错误
		if(empty_f_tag){
			// 判断是否至少有一项有数据
			if(work_arr.length < 1 && item_arr < 1 && authority_arr < 1 && other_arr < 1){
				showHint("提示信息","请至少选择一项交接内容！");
			}else{
				// 填入数据
				content_arr = {"工作交接": work_arr, "领用物品":item_arr, "权限回收":authority_arr, "其他":other_arr};

				// 发送数据
				sendWorkDeliverContent(id, content_arr);
			}
		}
	}

	// 显示交接人交接
	function showDepartment(){
		var ySet = (window.innerHeight - $("#department-deliver-div").height())/3;
        var xSet = (window.innerWidth - $("#department-deliver-div").width())/2;
        $("#department-deliver-div").css("top",ySet);
        $("#department-deliver-div").css("left",xSet);
		$("#department-deliver-div").modal({show:true});
	}

	// 新增一行
	function newLine(type){
		var str = "<div class='pd3'><input class='form-control inline w400'><a class='pointer ml20 b2 hover-red' onclick='deleteLine(this);'><span class='glyphicon glyphicon-remove-sign'></span>&nbsp;删除一行</a></div>";
		$("#"+type+"-deliver-tr").find("td").append(str);
	}

	// 删除一行
	function deleteLine(obj){
		$(obj).parent().remove();
	}

	// 选择是否要输入
	function switchCheckbox(obj){
		if(obj.checked){
			$(obj).parent().next().find("input").removeAttr("readonly");
		}else{
			$(obj).parent().next().find("input").attr("readonly",true);
		}
	}
</script>