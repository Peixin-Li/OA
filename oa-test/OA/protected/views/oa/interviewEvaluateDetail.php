<?php
echo "<script type='text/javascript'>";
echo "console.log('interviewEvaluateDetail');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
	<!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">面试评估表</h4>
	<?php if(!empty($resume->assessment)): ?>
	<?php if($resume->assessment->status == "giveup"):?>
	<div class="bor-l-1-ddd bor-r-1-ddd">
		<h4 class="center"><img src="../images/cry.png" class="w50 h50 m0a">该应聘者放弃了入职</h4>
	</div>
	<?php endif; ?>
	<!-- 面试进度 -->
	<div class="bor-l-1-ddd bor-r-1-ddd">

		<ul class="nav nav-justified">
	        <li class="bg-66 flow-li">
	            <h4 class="white m0 mt5 center">1.面试结束</h4>
	            <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
	        </li>

	        <?php 
	        	if(!empty($procedure)){
	        		$no_num = 2;
	        		$count = count($procedure);
	        		$last_status = "";
	        		foreach($procedure as $row){
	        			if($row[1] == "agree" || $row[1] == "giveup"){
							echo "<li class='bg-66 flow-li'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
	        			}else if($row[1] == "reject"){
	        				echo "<li class='flow-li-red bg-99'><h4 class='white m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
	        			}else{
	        				echo "<li><h4 class='m0 mt5 center'>{$no_num}.{$row[0]}</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
	        			}
	        			$last_status = $row[1];
	        			$no_num++;
	        		}
	        		if($last_status == "agree" || $last_status == "giveup"){
	        			echo "<li class='bg-66'><h4 class='white m0 mt5 center'>{$no_num}.面试结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-ok-sign'></span></div></li>";
	        		}else if($last_status == "reject"){
	        			echo "<li class='bg-99'><h4 class='white m0 mt5 center'>{$no_num}.面试结果</h4><div class='center'><span class='mt5 mb10 f18px white glyphicon glyphicon-remove-sign'></span></div></li>";
	        		}else{
	        			echo "<li><h4 class='m0 mt5 center'>{$no_num}.面试结果</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
	        		}
	        	}
	        ?>
	    </ul>
	</div>
	<?php endif;?>
	<!-- 面试详情表格 -->
	<table class="table table-bordered m0">
		<tbody>
			<tr>
				<th class="w130 center">面试日期</th>
				<td><?php echo $resume->interview_time; ?></td>
				<td class="hidden" id="resume-id"><?php echo $resume->id; ?></td>
				<td class="hidden" id="evaluate-id"><?php if(!empty($resume->assessment)) echo $resume->assessment->id; ?></td>
			</tr>
			<tr>
				<th class="w130 center">应聘者姓名</th>
				<td><?php echo $resume->name; ?></td>
			</tr>
			<tr>
				<th class="w130 center">来源</th>
				<td><?php echo $resume->source; ?></td>
			</tr>
			<tr>
				<th class="w130 center">应聘者职位</th>
				<td><?php echo $resume->apply->title; ?></td>
			</tr>
			<tr>
				<th class="w130 center">所属部门</th>
				<td><?php echo $resume->apply->department; ?></td>
			</tr>
<?php 
if (!empty($resume->assessment)){
if($logs = $resume->assessment->allLogs){
    foreach($logs as $log){

?>
        <tr>
        <th class="w130 center"><?php if($log->user_id == $hr_id){ echo 'HR评价';} else {echo "{$log->user->department->name}评价";} ?></th>
            <td>
                <div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                    <h5 class="w200 f15px"><?php echo ($log->action=='agree')?'同意':'不同意'; ?></h5>
                        <h5 class="w600 f15px">意见：<?php echo $log->opinion;?></h5>
                        <div class="xw600" style="word-break:break-all;"></div>
<?php if($log->action != "reject"){ ?>
<?php if($log->user_id != $hr_id){ ?>
<h5 class="w600 f15px"><?php if($log->probation_salary != '0.00'){ ?><strong>建议试用期薪资：</strong><?php echo (int)$log->probation_salary;?>元&nbsp;&nbsp;&nbsp;<?php } ?><strong>转正薪资：</strong><?php echo (int)$log->official_salary;?>元</h5>
<?php } ?>

<?php if($log->user_id == $admin_id || $log->user_id == $ceo_id){ ?>
  <h5 class="w600 f15px">
  	<strong>试用期限：</strong><?php echo $log->periods;?>个月&nbsp;&nbsp;&nbsp;
  	<?php if($log->user_id == $admin_id): ?>
  	<strong>预计入职日期：</strong><?php echo $resume->assessment->entry_day;?>
  	<?php endif; ?>
  </h5>
<?php }} ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                    <?php if($log->action=='agree'){ ?>
                        <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name; ?></span></h5>
                    <?php } ?>
                        <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d',strtotime($log->create_time));?></span></h5>
                    </div>
                </div>
            </td>
        </tr>
<?php if($log->user_id == $hr_id): ?>
		<?php if (!empty($resume->assessment) && !empty($resume->assessment->experience)): ?>
			<tr>
				<th class="w130 center">面试官评价</th>
				<td>
					<table class="center table m0">
						<thead>
							<tr>
								<th class="w130 center">评价内容</th>
								<th class="w200 center">工作经验</br>(40分)</th>
								<th class="w200 center">专业技能</br>(30分)</th>
								<th class="w200 center">执行力</br>(10分)</th>
								<th class="w200 center">沟通能力</br>(10分)</th>
								<th class="w200 center">工作态度</br>(5分)</th>
								<th class="w200 center">主动学习</br>(5分)</th>
								<th class="w200 center">总分</th>
								<th class="w200 center">评级</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th class="w130 center">分值</th>
                                <td><?php echo $resume->assessment->experience; ?></td>
                                <td><?php echo $resume->assessment->skill; ?></td>
                                <td><?php echo $resume->assessment->execution; ?></td>
                                <td><?php echo $resume->assessment->communicate; ?></td>
                                <td><?php echo $resume->assessment->attitude; ?></td>
                                <td><?php echo $resume->assessment->learning; ?></td>
                                <td><?php $total_score = $resume->assessment->experience + $resume->assessment->skill + $resume->assessment->execution + $resume->assessment->communicate + $resume->assessment->attitude + $resume->assessment->learning; echo $total_score ?></td>
								<td>
									<?php
										if($total_score >= 90){
											echo "资深";
										}else if($total_score >= 80){
											echo "高级";
										}else if($total_score >= 70){
											echo "中级";
										}else{
											echo "初级";
										}
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
            <?php endif; ?>
<?php endif; ?>
<?php
	$probation_salary = $log->probation_salary;
	$official_salary = $log->official_salary;
	$periods = $log->periods;
	$entry_day = $resume->assessment->entry_day;
    }
}
}
if((empty($resume->assessment) && $resume->interviewer == Yii::app()->session['user_id']) || (!empty($resume->assessment) && $resume->assessment->next == Yii::app()->session['user_id']) ){ ?>
<?php if($resume->interviewer == Yii::app()->session['user_id'] && empty($resume->assessment->experience)): ?>
			<tr>
				<th class="w130 center">操作</th>
				<td><button class="btn btn-success w100" onclick="showInterviewEvaluate();">填写评价</button></td>
			</tr>
<?php else: ?>
			<tr>
				<th class="w130 center">操作</th>
				<td><button class="btn btn-success w100" onclick="showEvaluate();">填写评价</button></td>
			</tr>
<?php endif; ?>
<?php } ?>



<?php //if((empty($resume->assessment) && Yii::app()->session['user_id'] == $hr_id)){ ?>
			<!-- <tr>
				<th class="w130 center">操作</th>
				<td><button class="btn btn-success w100" onclick="showEvaluate();">填写评价</button></td>
			</tr> -->
<?php //} ?>
			<tr>
				<th class="w130 center">查看</th>
				<td><a href="/oa/recruitApplyDetail/id/<?php echo $resume->apply_id;?>/type/interviewEvaluateDetail">查看招聘申请详情</a></td>
			</tr>
			<?php if($resume->status == "entry" && !empty($tag) && $tag): ?>
			<tr>
				<th class="w130 center">操作</th>
				<td><button class="b2 btn btn-default w100" onclick="sendGiveUp();"><span class="glyphicon glyphicon-remove" ></span>&nbsp;放弃入职</button></td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<!-- 面试记录表 -->
	<table class="table table-bordered m0 mt20">
		<caption>附件——面试记录表</caption>
		<?php if(!empty($resume->assessment->record_file)): ?>
		<tr>
        <td><a target="_blank" href="<?php echo "/oa/viewRecord/id/{$resume->assessment->id}";?>"><?php list($_name,$_ext) = explode('.', $resume->assessment->record_file);
        $dst_name = "{$resume->apply->title}-{$resume->name}-{$resume->source}-".'评估表.'.$_ext; echo $dst_name; ?></a> <a target="_blank" href="<?php echo "/oa/downloadRecord/id/{$resume->assessment->id}";?>">下载</a></td>
		</tr>
		<?php else: ?>
		<tr>
			<td>没有面试记录表</td>
		</tr>
		<?php endif;?>
		<?php if($hr_id == Yii::app()->session['user_id']): ?>
		<tr>
			<td>
				<label>选择文件(小于5M)：</label>
				<input type="file" style="display:inline-block;" id="interview-log-upload" onchange="checkFileType();">
				<button class="btn btn-success pd3 w80 disabled ml10 mt-5" onclick="submitInterviewLog();">上传</button>
			</td>
		</tr>
		<?php endif;?>
	</table>
</div>

<!-- 面试评估模态框 -->
<div id="interviewer-evaluate-div" class="modal fade in hint bor-rad-5 w1000" style="display: none; ">
	<!-- 模态框头部 -->
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">面试评估</h4>
    </div>
    <!-- 模态框主体 -->
    <div class="modal-body">
    	<table class="table table-bordered m0 center">
    			<tr>
    				<th class="center">级别</th>
    				<th class="center">工作经验(40)</th>
    				<th class="center">专业技能(30)</th>
    				<th class="center">执行力(10)</th>
    				<th class="center">沟通能力(10)</th>
    				<th class="center">工作态度、责任心(5)</th>
    				<th class="center">主动学习(5)</th>
    				<th class="center">总分(100)</th>
    				<th class="center">评级</th>
    			</tr>
    			<tr>
    				<td>初级</td>
    				<td>20-25</td>
    				<td>15-20</td>
    				<td>8</td>
    				<td>8</td>
    				<td>4</td>
    				<td>4</td>
    				<td>60-70</td>
    				<td rowspan="5" id="assessment-level">初级</td>
    			</tr>
    			<tr>
    				<td>中级</td>
    				<td>25-30</td>
    				<td>20-25</td>
    				<td>8</td>
    				<td>8</td>
    				<td>4</td>
    				<td>4</td>
    				<td>71-80</td>
    			</tr>
    			<tr>
    				<td>高级</td>
    				<td>30-35</td>
    				<td>25-30</td>
    				<td>8</td>
    				<td>8</td>
    				<td>4</td>
    				<td>4</td>
    				<td>80以上</td>
    			</tr>
    			<tr>
    				<td>资深</td>
    				<td>35-40</td>
    				<td>25-30</td>
    				<td>9-10</td>
    				<td>9-10</td>
    				<td>5</td>
    				<td>5</td>
    				<td>90以上</td>
    			</tr>
    			<tr>
    				<td>评分</td>
    				<td><input id="experience-input"  class="w50 center" onchange="scoreCalculate();" value="1"></td>
    				<td><input id="skill-input"  class="w50  center"  onchange="scoreCalculate();" value="1"></td>
    				<td><input id="execution-input"  class="w50  center"  onchange="scoreCalculate();" value="1"></td>
    				<td><input id="communicate-input"  class="w50  center"  onchange="scoreCalculate();" value="1"></td>
    				<td><input id="attitude-input"  class="w50  center"  onchange="scoreCalculate();" value="1"></td>
    				<td><input id="learning-input"  class="w50  center"  onchange="scoreCalculate();" value="1"></td>
    				<td id="total-score"></td>
    			</tr>
    	</table>
    </div>
    <!-- 模态框底部 -->
    <div class="modal-footer">
    	<button class="btn btn-success w100" onclick="<?php if($resume->interviewer == $resume->apply->user_id){echo "nextStep(this);";}else{echo "sendInterviewerAssessment(this);";}?>"><?php if($resume->interviewer == $resume->apply->user_id){echo "下一步";}else{echo "提交";}?></button>
    </div>
</div>

<!-- 面试评估模态框2 -->
<div id="evaluate-div" class="modal fade in hint bor-rad-5 w800" style="display: none; ">
	<!-- 模态框头部 -->
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">面试评估</h4>
    </div>
    <!-- 模态框主体 -->
    <div class="modal-body">
        <table class="table table-bordered m0">
        	<!-- HR评估的地方 -->
        	<?php if($admin_id == Yii::app()->session['user_id']): ?>
			<tr>
				<th class="w130 center">HR评价</th>
				<td>
					<table class="lh50 m0">
						<tr>
							<th class="w80 center">意见</th>
							<td><textarea class="form-control w400" id="hr-opinion-input"></textarea></td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- 部门负责人评估的地方 -->
			<?php elseif($resume->apply->user_id == Yii::app()->session['user_id'] && $resume->apply->user_id != $admin_id): ?>
			<tr>
				<th class="w130 center">部门负责人评价</th>
				<td>
					<table class="lh50 m0">
						<tr>
							<th class="w130 center">评估</th>
							<td>
								<input type="radio" name="admin_decision" checked value="agree" onchange="showNext(this);">&nbsp;同意&nbsp;&nbsp;&nbsp;
								<input type="radio" name="admin_decision" value="reject" onchange="hideNext(this);">&nbsp;不同意
							</td>
						</tr>
						<tr>
							<th class="w130 center">建议试用期薪资</th>
							<td><input class="inline form-control w100" placeholder="试用期薪资" id="admin-probation-salary">&nbsp;元</td>
						</tr>
						<tr>
							<th class="w130 center">转正薪资</th>
							<td><input class="inline form-control w100" placeholder="转正薪资" id="admin-official-salary">&nbsp;元</td>
						</tr>
						<tr>
							<th class="w130 center">意见</th>
							<td><textarea class="form-control w400" id="admin-opinion-input"></textarea></td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- 人事行政部评估的地方 -->
			<?php elseif($hr_id == Yii::app()->session['user_id']): ?>
			<tr>
				<th class="w130 center">HR</th>
				<td>
					<table class="lh50 m0">
						<tr>
							<th class="w130 center">评估</th>
							<td>
								<input type="radio" name="administration_decision" checked value="agree" onchange="showNext(this);">&nbsp;同意&nbsp;&nbsp;&nbsp;
								<input type="radio" name="administration_decision" value="reject" onchange="hideNext(this);">&nbsp;不同意
							</td>
						</tr>
						<tr>
							<th class="w130 center">建议试用期薪资</th>
							<td><input class="inline form-control w100" placeholder="试用期薪资" id="administration-probation-salary" value="<?php echo empty($probation_salary) ? '' : $probation_salary;?>">&nbsp;元</td>
						</tr>
						<tr>
							<th class="w130 center">转正薪资</th>
							<td><input class="inline form-control w100" placeholder="转正薪资" id="administration-official-salary" value="<?php echo empty($official_salary) ? '' : $official_salary;?>">&nbsp;元</td>
						</tr>
						<tr>
							<th class="w130 center">试用期限</th>
							<td>	
								<select class="pd5" id="administration-periods" value="<?php echo empty($periods) ? '' : $periods;?>">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>&nbsp;月
							</td>
						</tr>
						<tr>
							<th class="w130 center">预计入职日期</th>
							<td><input class="form-control w150 pointer" id="administration-entry-date" value="<?php echo empty($entry_day) ? '' : $entry_day;?>"></td>
						</tr>
						<tr>
							<th class="w130 center">意见</th>
							<td><textarea class="form-control w400" id="administration-opinion-input"></textarea></td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- 总经理评估的地方 -->
			<?php elseif($ceo_id == Yii::app()->session['user_id']): ?>
			<tr>
				<th class="w130 center">总经理审批</th>
				<td>
					<table class="lh50 m0">
						<tr>
							<th class="w130 center">评估</th>
							<td>
								<input type="radio" name="ceo_decision" checked value="agree" onchange="showNext(this);">&nbsp;同意&nbsp;&nbsp;&nbsp;
								<input type="radio" name="ceo_decision" value="reject" onchange="hideNext(this);">&nbsp;不同意
							</td>
						</tr>
						<tr>
							<th class="w130 center">建议试用期薪资</th>
							<td><input class="inline form-control w100" placeholder="试用期薪资" id="ceo-probation-salary" value="<?php echo empty($probation_salary) ? '' : $probation_salary;?>">&nbsp;元</td>
						</tr>
						<tr>
							<th class="w130 center">转正薪资</th>
							<td><input class="inline form-control w100" placeholder="转正薪资" id="ceo-official-salary" value="<?php echo empty($official_salary) ? '' : $official_salary;?>">&nbsp;元</td>
						</tr>
						<tr>
							<th class="w130 center">试用期限</th>
							<td>	
								<select class="pd5" id="ceo-periods" value="<?php echo empty($periods) ? '' : $periods;?>">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
									<option value="11">11</option>
									<option value="12">12</option>
								</select>&nbsp;月
							</td>
						</tr>
						<tr>
							<th class="w130 center">意见</th>
							<td><textarea class="form-control w400" id="ceo-opinion-input"></textarea></td>
						</tr>
					</table>
				</td>
			</tr>
			<?php endif; ?>
        </table>
    </div>
    <!-- 模态框底部 -->
    <div class="modal-footer">
    	<?php if($admin_id == Yii::app()->session['user_id']): ?>
        <button class="w100 btn btn-success" onclick="addEvaluate();">提交</button>
        <?php elseif($resume->apply->user_id == Yii::app()->session['user_id'] && $resume->apply->user_id != $admin_id): ?>
        <button class="w100 btn btn-success" onclick="<?php if($resume->interviewer == $resume->apply->user_id){echo "sendSameEvaluate(this);";}else{echo "sendEvaluate(this);";}?>" id="admin-send">提交</button>
        <?php elseif($hr_id == Yii::app()->session['user_id']): ?>
        <button class="w100 btn btn-success" onclick="<?php if($resume->interviewer == $resume->apply->user_id && $resume->apply->user_id == $admin_id){echo "sendSameEvaluate(this);";}else{echo "sendEvaluate(this);";}?>" id="administration-send">提交</button>
        <?php elseif($ceo_id == Yii::app()->session['user_id']): ?>
        <button class="w100 btn btn-success" onclick="sendEvaluate(this);" id="ceo-send">提交</button>
        <?php endif; ?>
    </div>
</div>


<!-- js -->
<script type="text/javascript">
	// 初始化
	$(document).ready(function(){
		$('#administration-entry-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

/*-----------------------------------------------------------面试官的操作-----------------------------------------------------------*/

	// 显示面试官的评分
	function showInterviewEvaluate(){
		var ySet = (window.innerHeight - $("#interviewer-evaluate-div").height())/3;
        var xSet = (window.innerWidth - $("#interviewer-evaluate-div").width())/2;
        $("#interviewer-evaluate-div").css("top",ySet);
        $("#interviewer-evaluate-div").css("left",xSet);
		$("#interviewer-evaluate-div").modal({show:true});
	}

	// 发送面试官的评分
	function sendInterviewerAssessment(obj){
		var id = $("#evaluate-id").text();
		var d_pattern = /^\d+$/;
		var experience = $("#experience-input").val();
		var attitude = $("#attitude-input").val();
		var skill = $("#skill-input").val();
		var execution = $("#execution-input").val();
		var communicate = $("#communicate-input").val();
		var learning = $("#learning-input").val();
		if(!d_pattern.exec(experience) || parseInt(experience) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#experience-input").val("1");
		}else if(!d_pattern.exec(skill) || parseInt(skill) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#skill-input").val("1");
		}else if(!d_pattern.exec(execution) || parseInt(execution) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#execution-input").val("1");
		}else if(!d_pattern.exec(communicate) || parseInt(communicate) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#communicate-input").val("1");
		}else if(!d_pattern.exec(attitude) || parseInt(attitude) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#attitude-input").val("1");
		}else if(!d_pattern.exec(learning) || parseInt(learning) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#learning-input").val("1");
		}else{
			$.ajax({
	            type:'post',
	            dataType:'json',
	            url:'/ajax/interviewerAssessment',
	            data:{'id':id, 'experience':experience, 'skill':skill, 'attitude':attitude, 'execution':execution, 'communicate':communicate, 'learning':learning},
	            success:function(data){
	                if(data.code == 0){
	                	showHint("提示信息","评估信息提交成功");
	                	// 如果不是下一步就刷新
	                	if($(obj).text() != "下一步"){
	                		setTimeout(function(){location.reload();},1200);
	                    }
	                }else if(data.code == -1){
	                    showHint("提示信息","评估信息提交失败");
	                }else if(data.code == -2){
	                    showHint("提示信息","参数错误");
	                }else if(data.code == -3){
	                    showHint("提示信息","查找不到该记录");
	                }else{
	                	showHint("提示信息","你没有权限执行此操作");
	                }
	            }
	        });
		}
	}

	// 计算并且检测分值
	function scoreCalculate(){
		var total = 0;
		var d_pattern = /^\d+$/;
		var experience = $("#experience-input").val();
		var attitude = $("#attitude-input").val();
		var skill = $("#skill-input").val();
		var execution = $("#execution-input").val();
		var communicate = $("#communicate-input").val();
		var learning = $("#learning-input").val();
		if(!d_pattern.exec(experience)){
			showHint("提示信息","只能输入数字！");
			$("#experience-input").val("0");
		}else if(!d_pattern.exec(skill)){
			showHint("提示信息","只能输入数字！");
			$("#skill-input").val("0");
		}else if(!d_pattern.exec(execution)){
			showHint("提示信息","只能输入数字！");
			$("#execution-input").val("0");
		}else if(!d_pattern.exec(communicate)){
			showHint("提示信息","只能输入数字！");
			$("#communicate-input").val("0");
		}else if(!d_pattern.exec(attitude)){
			showHint("提示信息","只能输入数字！");
			$("#attitude-input").val("0");
		}else if(!d_pattern.exec(learning)){
			showHint("提示信息","只能输入数字！");
			$("#learning-input").val("0");
		}else if(parseInt(experience) > 40 || parseInt(experience) < 0){
			showHint("提示信息","请输入0到40的分值！");
			$("#experience-input").val("0");
		}else if(parseInt(skill) > 30 || parseInt(skill) < 0){
			showHint("提示信息","请输入0到30的分值！");
			$("#skill-input").val("0");
		}else if(parseInt(execution) > 10 || parseInt(execution) < 0){
			showHint("提示信息","请输入0到10的分值！");
			$("#execution-input").val("0");
		}else if(parseInt(communicate) > 10 || parseInt(communicate) < 0){
			showHint("提示信息","请输入0到10的分值！");
			$("#communicate-input").val("0");
		}else if(parseInt(attitude) > 5 || parseInt(attitude) < 0){
			showHint("提示信息","请输入0到5的分值！");
			$("#attitude-input").val("0");
		}else if(parseInt(learning) > 5 || parseInt(learning) < 0){
			showHint("提示信息","请输入0到5的分值！");
			$("#learning-input").val("0");
		}else{
			total = parseInt(experience) + parseInt(skill) + parseInt(execution) + parseInt(communicate) + parseInt(attitude) + parseInt(learning);
			$("#total-score").text(total);
			if(total >= 90){
				$("#assessment-level").text("资深");
			}else if(total >= 80){
				$("#assessment-level").text("高级");
			}else if(total >= 70){
				$("#assessment-level").text("中级");
			}else{
				$("#assessment-level").text("初级");
			}
		}
	}

/*-----------------------------------------------------------其他人的操作-----------------------------------------------------------*/

	// 显示评估表
	function showEvaluate(){
		var ySet = (window.innerHeight - $("#evaluate-div").height())/3;
        var xSet = (window.innerWidth - $("#evaluate-div").width())/2;
        $("#evaluate-div").css("top",ySet);
        $("#evaluate-div").css("left",xSet);
		$("#evaluate-div").modal({show:true});
	}

	// 面试官和部门负责人是同一个人的时候
	function nextStep(obj){
		var d_pattern = /^\d+$/;
		var experience = $("#experience-input").val();
		var attitude = $("#attitude-input").val();
		var skill = $("#skill-input").val();
		var execution = $("#execution-input").val();
		var communicate = $("#communicate-input").val();
		var learning = $("#learning-input").val();
		if(!d_pattern.exec(experience) || parseInt(experience) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#experience-input").val("1");
		}else if(!d_pattern.exec(skill) || parseInt(skill) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#skill-input").val("1");
		}else if(!d_pattern.exec(execution) || parseInt(execution) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#execution-input").val("1");
		}else if(!d_pattern.exec(communicate) || parseInt(communicate) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#communicate-input").val("1");
		}else if(!d_pattern.exec(attitude) || parseInt(attitude) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#attitude-input").val("1");
		}else if(!d_pattern.exec(learning) || parseInt(learning) < 1){
			showHint("提示信息","只能输入大于0的数字！");
			$("#learning-input").val("1");
		}else{
			showEvaluate();
	    	$("#interviewer-evaluate-div").modal('hide');
		}
	}

	// 发送评价-部门负责人和面试官是同一个人
	function sendSameEvaluate(obj){
		var id = $("#evaluate-id").text();
		var experience = $("#experience-input").val();
		var attitude = $("#attitude-input").val();
		var skill = $("#skill-input").val();
		var execution = $("#execution-input").val();
		var communicate = $("#communicate-input").val();
		var learning = $("#learning-input").val();
		$.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/interviewerAssessment',
            data:{'id':id, 'experience':experience, 'skill':skill, 'attitude':attitude, 'execution':execution, 'communicate':communicate, 'learning':learning},
            success:function(data){
                if(data.code == 0){
                	sendEvaluate(obj);
                }else if(data.code == -1){
                    showHint("提示信息","评估信息提交失败, 请刷新重试");
                }else if(data.code == -2){
                    showHint("提示信息","参数错误, 请刷新重试");
                }else if(data.code == -3){
                    showHint("提示信息","查找不到该记录, 请刷新重试");
                }else{
                	showHint("提示信息","你没有权限执行此操作, 请刷新重试");
                }
            }
        });
	}

	// 发送评价-部门负责人||人事行政部||ceo
	function sendEvaluate(obj){

		var id = $("#evaluate-id").text();

		var opinion = "";
		var action = "";
		var probation_salary = "";
		var official_salary = "";
		var periods = "";
		var entry_date = "";
		var click_obj = $(obj).attr("id");
		switch(click_obj){
			case "admin-send":{
				opinion = $("#admin-opinion-input").val();
				action = $("input[name='admin_decision']:checked").val();
				probation_salary = $("#admin-probation-salary").val();
				official_salary = $("#admin-official-salary").val();
				periods = "";
				break;
			}
			case "administration-send":{
				opinion = $("#administration-opinion-input").val();
				action = $("input[name='administration_decision']:checked").val();
				probation_salary = $("#administration-probation-salary").val();
				official_salary = $("#administration-official-salary").val();
				periods = $("#administration-periods").val();
				entry_date = $("#administration-entry-date").val();
				break;
			}
			case "ceo-send":{
				opinion = $("#ceo-opinion-input").val();
				action = $("input[name='ceo_decision']:checked").val();
				probation_salary = $("#ceo-probation-salary").val();
				official_salary = $("#ceo-official-salary").val();
				periods = $("#ceo-periods").val();
				break;
			}
		}
		
		var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
		var d_pattern = /^\d+(\.\d{1,2})?$/;
		if(action == "agree"){
			if(probation_salary != "" && !d_pattern.exec(probation_salary)){
				showHint("提示信息","试用期薪资输入格式不正确");
			}else if(official_salary != "" && !d_pattern.exec(official_salary)){
				showHint("提示信息","转正薪资输入格式不正确");
			}else if(entry_date != "" && !date_pattern.exec(entry_date)){
				showHint("提示信息","预计入职日期输入格式不正确");
			}else if(entry_date != "" && entry_date == "0000-00-00"){
				showHint("提示信息","请选择预计入职日期");
			}else if(opinion == ""){
				showHint("提示信息","请输入意见");
			}else{
				$.ajax({
		            type:'post',
		            dataType:'json',
		            url:'/ajax/agreeAssessment',
		            data:{'id':id, 'periods':periods, 'probation_salary':probation_salary, 'official_salary':official_salary, 'entry_day':entry_date, 'opinion':opinion},
		            success:function(data){
		                if(data.code == 0){
		                    showHint("提示信息","评估信息提交成功");
		                    setTimeout(function(){location.reload();},1200);
		                }else if(data.code == -1){
		                    showHint("提示信息","评估信息提交失败");
		                }else if(data.code == -2){
		                    showHint("提示信息","参数错误");
		                }else if(data.code == -3){
		                    showHint("提示信息","找不到该简历");
		                }else if(data.code == -4){
		                    showHint("提示信息","已经进行过评估");
		                }else{
		                    showHint("提示信息","你没有权限执行此操作");
		                }
		            }
		        });
			}
		}else if(action == "reject"){
			if(opinion == ""){
				showHint("提示信息","请输入意见");
			}else{
				$.ajax({
		            type:'post',
		            dataType:'json',
		            url:'/ajax/rejectAssessment',
		            data:{'id':id, 'opinion':opinion},
		            success:function(data){
		                if(data.code == 0){
		                    showHint("提示信息","评估信息提交成功");
		                    setTimeout(function(){location.reload();},1200);
		                }else if(data.code == -1){
		                    showHint("提示信息","评估信息提交失败");
		                }else if(data.code == -2){
		                    showHint("提示信息","参数错误");
		                }else if(data.code == -3){
		                    showHint("提示信息","找不到该简历");
		                }else if(data.code == -4){
		                    showHint("提示信息","已经进行过评估");
		                }else{
		                    showHint("提示信息","你没有权限执行此操作");
		                }
		            }
		        });
			}
		}
		
	}

	// 同意的话-把下面要输入的显示出来
	function showNext(obj){
		var click_obj = $(obj).attr("name");
		switch(click_obj){
			case "admin_decision":{
				$(obj).parent().parent().next().removeClass("hidden");
				$(obj).parent().parent().next().next().removeClass("hidden");
				break;
			}
			case "administration_decision":{
				$(obj).parent().parent().next().removeClass("hidden");
				$(obj).parent().parent().next().next().removeClass("hidden");
				$(obj).parent().parent().next().next().next().removeClass("hidden");
				$(obj).parent().parent().next().next().next().next().removeClass("hidden");
				break;
			}
			case "ceo_decision":{
				$(obj).parent().parent().next().removeClass("hidden");
				$(obj).parent().parent().next().next().removeClass("hidden");
				$(obj).parent().parent().next().next().next().removeClass("hidden");
				break;
			}
		}
	}

	// 不同意的话-把下面要输入的隐藏
	function hideNext(obj){
		var click_obj = $(obj).attr("name");
		switch(click_obj){
			case "admin_decision":{
				$(obj).parent().parent().next().addClass("hidden");
				$(obj).parent().parent().next().next().addClass("hidden");
				break;
			}
			case "administration_decision":{
				$(obj).parent().parent().next().addClass("hidden");
				$(obj).parent().parent().next().next().addClass("hidden");
				$(obj).parent().parent().next().next().next().addClass("hidden");
				$(obj).parent().parent().next().next().next().next().addClass("hidden");
				break;
			}
			case "ceo_decision":{
				$(obj).parent().parent().next().addClass("hidden");
				$(obj).parent().parent().next().next().addClass("hidden");
				$(obj).parent().parent().next().next().next().addClass("hidden");
				break;
			}
		}
	}

/*-----------------------------------------------------------HR的操作-----------------------------------------------------------*/

	// 放弃职位
    function sendGiveUp(){
        var id = "<?php echo empty($resume->assessment->id) ? '' :$resume->assessment->id; ?>";
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/giveUp',
            data:{'id':id},
            success:function(data){
                if(data.code == 0){
                    showHint("提示信息", "操作成功，该应聘者已放弃入职");
                    setTimeout(function(){
                    	location.reload();
                    }, 1200);
                }else if(data.code == -1){
                    showHint("提示信息","操作失败，请重试");
                }else if(data.code == -2){
                    showHint("提示信息","参数错误");
                }else if(data.code == -3){
                    showHint("提示信息","找不到该招聘申请");
                }else{
                    showHint("提示信息","你没有权限执行此操作");
                }
            }
        });
    }

    // 检测上传的面试记录表的文件格式
	function checkFileType(){
		var name = document.getElementById("interview-log-upload").files[0].name;
		if(name.split("\.")[1].indexOf("doc") >-1||name.split("\.")[1].indexOf("docx") >-1){
			$("#interview-log-upload").next().removeClass("disabled");
		}else{
			$("#interview-log-upload").val("");
			showHint("提示信息","请选择word文档！");
		}
	}

	// 提交面试记录表
	function submitInterviewLog(){
		var id = $("#evaluate-id").text();
		if(id == ""){
			showHint("提示信息","请先填写评价");
		}else{
	        var FileController = "/ajax/uploadRecordFile";                    // 接收上传文件的后台地址 
	        // FormData 对象
	        var form = new FormData();
	        form.append("record_file", document.getElementById("interview-log-upload").files[0]);                          // 文件对象
	        form.append("id", id);

	        // XMLHttpRequest 对象
	        var xhr = new XMLHttpRequest();
	        xhr.open("post", FileController, true);
	        xhr.onload = function () {
	            // showHint("提示信息","上传成功");
	        };
	        xhr.send(form);

	        xhr.onreadystatechange=function(){
	            if(xhr.readyState==4 && xhr.status==200){
	                var code = xhr.responseText;
	                if(code == 0){
	                	showHint("提示信息","上传成功");
	                    setTimeout(function(){location.reload();},1200);
	                }else if(code == -1){
	                    showHint("提示信息","上传失败");
	                }else if(code == -2){
	                    showHint("提示信息","参数错误");
	                }else if(code == -3){
	                    showHint("提示信息","找不到该评估表");
	                }else if(code == -4){
	                    showHint("提示信息","文件大小大于5M");
	                }else{
	                    showHint("提示信息","你没有权限执行此操作");
	                }
	            }
	        }
		}
		
	}

	// 发起面试评估-HR
	function addEvaluate(){
		var resume_id = $("#resume-id").text();
		var opinion = $("#hr-opinion-input").val();
		if(opinion == ""){
			showHint("提示信息","请输入意见");
		}else{
			$.ajax({
	            type:'post',
	            dataType:'json',
	            url:'/ajax/addAssessment',
	            data:{'resume_id':resume_id, 'opinion':opinion},
	            success:function(data){
	                if(data.code == 0){
	                    showHint("提示信息","评估信息提交成功");
	                    setTimeout(function(){location.reload();},1200);
	                }else if(data.code == -1){
	                    showHint("提示信息","评估信息提交失败");
	                }else if(data.code == -2){
	                    showHint("提示信息","参数错误");
	                }else if(data.code == -3){
	                    showHint("提示信息","找不到该简历");
	                }else if(data.code == -4){
	                    showHint("提示信息","已经进行过评估");
	                }else{
	                    showHint("提示信息","你没有权限执行此操作");
	                }
	            }
	        });
		}
	}
</script>
