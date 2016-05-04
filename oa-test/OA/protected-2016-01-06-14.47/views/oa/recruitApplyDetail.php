<?php
echo "<script type='text/javascript'>";
echo "console.log('recruitApplyDetail');";
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
	<h4 class="pd10 m0 b33 bor-1-ddd">招聘申请详情</h4>
    <!-- 招聘申请进度 -->
	<div class="bor-l-1-ddd bor-r-1-ddd">
        <ul class="nav nav-justified">
            <li class="bg-66 flow-li">
                <h4 class="white m0 mt5 center">1.提交招聘申请</h4>
                <div class="center"><span class="mt5 mb10 f18px white glyphicon glyphicon-ok-sign"></span></div>
            </li>

            <?php 
                if(!empty($procedure)){
                    $row_num = empty($procedure) ? 0 :  count($procedure);
                    $no_num = 2;
                    foreach($procedure as $row){
                        $row[2] = empty($row[2]) ? '' : $row[2];
                        if($row[2] == "wait"){
                            echo "<li><h4 class='m0 mt5 center'>{$no_num}.{$row[1]}({$row[0]})</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
                        }else if($row[2] == "agree"){
                            echo "<li class='bg-66 flow-li'><h4 class='white m0 mt5 center'>{$no_num}.{$row[1]}({$row[0]})</h4><div class='center'><span class='white mt5 mb10 f18px glyphicon glyphicon-ok-sign'></span></div></li>";
                        }else{
                            echo "<li class='flow-li-red bg-99'><h4 class='white m0 mt5 center'>{$no_num}.{$row[1]}({$row[0]})</h4><div class='center'><span class='white mt5 mb10 f18px glyphicon glyphicon-remove-sign'></span></div></li>";
                        }
                        $no_num ++;
                        if($no_num-2 == $row_num){
                            if($row[2] == "wait"){
                                echo "<li><h4 class='m0 mt5 center'>{$no_num}.招聘申请结果</h4><div class='center'><span class='mt5 mb10 f18px glyphicon glyphicon-time'></span></div></li>";
                            }else if($row[2] == "agree"){
                                echo "<li class='bg-66'><h4 class='white m0 mt5 center'>{$no_num}.招聘申请结果</h4><div class='center'><span class='white mt5 mb10 f18px glyphicon glyphicon-ok-sign'></span></div></li>";
                            }else{
                                echo "<li class='bg-99'><h4 class='white m0 mt5 center'>{$no_num}.招聘申请结果</h4><div class='center'><span class='white mt5 mb10 f18px glyphicon glyphicon-remove-sign'></span></div></li>";
                            }
                        }
                    }
                }
            ?>
        </ul>
    </div>
    <!-- 招聘申请详情表 -->
	<table class="table table-bordered m0">
		<tr class="hidden">
			<th class="center w130">ID</th>
            <td id="apply-id"><?php echo $apply->id; ?></td>
		</tr>
		<tr>
			<th class="center w130">填表日期</th>
            <td><?php echo $apply->create_date; ?></td>
		</tr>
		<tr>
			<th class="center w130">申请人</th>
            <td><?php echo $apply->user->cn_name; ?></td>
		</tr>
		<tr>
			<th class="center w130">申请部门</th>
            <td><?php echo $apply->user->department->name; ?></td>
		</tr>
		<tr>
			<th class="center w130">招聘职位</th>
            <td><?php echo $apply->title; ?></td>
		</tr>
		<tr>
			<th class="center w130">希望到职日期</th>
            <td><?php echo date('Y-m-d',strtotime($apply->entry_day)); ?></td>
		</tr>
		<tr>
			<th class="center w130">建议薪酬范围</th>
			<td><?php echo $apply->pay; ?></td>
		</tr>
		<tr>
			<th class="center w130">招聘类型</th>
			<td>
                <?php 
                    if($apply->type == "replace"){
                        echo "编制内替代";
                    }else if($apply->type == "internal"){
                        echo "编制内增补";
                    }else if($apply->type == "add"){
                        echo "编制外增补";
                    }
                ?>
            </td>
		</tr>
        <?php if($apply->type == 'replace'){ ?>
		<tr>
			<th class="center w130">替代人姓名</th>
            <td><?php echo $apply->quitUser->cn_name; ?></td>
		</tr>
		<tr>
			<th class="center w130">替代人离职日期</th>
			<td><?php echo $apply->quit_date; ?></td>
        </tr>
        <?php } else { ?>
		<tr>
			<th class="center w130">招聘原因</th>
			<td><?php echo $apply->add_reason; ?></td>
        </tr>
        <?php } ?>
		<tr>
			<th class="center w130">主要工作职责</th>
			<td><?php echo $apply->work_content; ?></td>
		</tr>
		<tr>
			<th class="center w130">入职条件</th>
			<td>
				<table class="m0">
					<tr>
						<th class="w100">性别:</th>
                        <td class="w150"><?php $genders = array('m'=>'男', 'f'=>'女', 'none'=>'不要求'); echo empty($genders[$apply->condition->gender])? '不要求' :$genders[$apply->condition->gender] ; ?></td>
						<th class="w100">年龄:</th>
						<td class="w150"><?php if($apply->condition->age != "0") echo $apply->condition->age;else echo "不要求" ?></td>
						<th class="w100">学历:</th>
						<td class="w150"><?php $edus = array('junior'=>'初中','high'=>'高中','college'=>'大专','undergraduate'=>'本科','graduate'=>'研究生','master'=>'硕士','dr'=>'博士'); echo empty($edus[$apply->condition->education])?'不要求':$edus[$apply->condition->education]; ; ?></td>
					</tr>
					<tr>
						<th class="w100">专业:</th>
						<td class="w150"><?php if(!empty($apply->condition->professional)) echo $apply->condition->professional;else echo "不要求"; ?></td>
						<th class="w100">计算机水平:</th>
						<td class="w150"><?php $computers=array('great'=>'优秀','good'=>'良好','general'=>'一般','none'=>'不要求'); echo empty($computers[$apply->condition->computer]) ? '不要求' :$computers[$apply->condition->computer]; ?></td>
						<th class="w100">国语水平:</th>
						<td class="w150"><?php $langues = array('good'=>'流利','general'=>'一般','none'=>'不要求'); echo empty($langues[$apply->condition->mandarin]) ? '不要求': $langues[$apply->condition->mandarin]; ?></td>
					</tr>
					<tr>
						<th class="w100">粤语水平:</th>
						<td class="w150"><?php echo empty($langues[$apply->condition->cantonese]) ? '不要求': $langues[$apply->condition->cantonese]; ?></td>
						<th class="w100">外语水平:</th>
						<td class="w150"><?php echo empty($langues[$apply->condition->foreign]) ? '不要求': $langues[$apply->condition->foreign]; ?></td>
						<th class="w100">户籍:</th>
						<td class="w150"><?php $residences = array('local'=>'本地','nonlocal'=>'外地','none'=>'不要求'); echo empty($residences[$apply->condition->residence]) ? '' : $residences[$apply->condition->residence] ; ?></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th class="center w130">工作经验</th>
			<td><?php echo $apply->work_life; ?>年</td>
		</tr>
		<tr>
			<th class="center w130">个性</th>
			<td><?php echo $apply->individuality; ?></td>
		</tr>
		<tr>
			<th class="center w130">备注</th>
			<td><?php echo $apply->comment; ?></td>
		</tr>
        <?php if($logs = $apply->alllogs): ?>
        <?php foreach($logs as $log): ?>
		<tr>
        <th class="center w130"><?php echo $log->user->department->name; ?>审批</th>
			<td>
				<div class="fl">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 f15px">同意</h5>
                        <?php else: ?>
                        <h5 class="w200 f15px">不同意</h5>
                        <h5 class="w200 f15px">不同意原因：</h5>
                        <div class="xw600" style="word-break:break-all;"><?php echo $apply->reason; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fr">
                    <div style="display:table-cell;" class="middle h80">
                        <?php if($log->action == 'agree'): ?>
                        <h5 class="w200 center">签名：<span><?php echo $log->user->cn_name;?></span></h5>
                        <?php endif; ?>
                        <h5 class="w200 center">审批日期：<span><?php echo date('Y-m-d',strtotime($log->create_time));?></span></h5>
                    </div>
                </div>
			</td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php if(!empty($user) && $user->user_id == $apply->next): ?>
		<tr>
            <th class="w130 center">回复操作</th>
            <td>
                <button class="btn btn-success w100" id="agree">同意</button>
                <button class="btn btn-primary w100 ml20" id="reject">不同意</button>
            </td>
        </tr>
        <?php endif; ?>
        <?php if($apply->status == "success"  || $apply->status == 'entry'): ?>
        <tr>
            <th class="w130 center">操作</th>
            <td><button class="btn btn-success w100" onclick="showViewResume();">查看简历</button></td>
        </tr>
        <?php endif; ?>
	</table>
</div>

<!-- 输入拒绝理由的模态框 -->
<div id="reject-reason-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">输入不同意原因</h4>
    </div>

    <div class="modal-body">
        <label>不同意原因：</label>
        <textarea type="text" class="form-control inline" id="reject-input"></textarea>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="rejectSubmit()" data-dismiss="modal">提交</button>
    </div>
</div>

<!-- 查看简历的模态框 -->
<div id="view-resume-div" class="modal fade in hint bor-rad-5 w1000" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">查看收集到的简历</h4>
    </div>

    <div class="modal-body overflow-a xh600">
        <?php if(!empty($resumes)): ?>
        <table class="table bor-1-ddd center m0 " id="resume-table">
            <thead>
                <tr>
                    <th class="hidden w20 center"></th>
                    <th class="center w100">姓名</th>
                    <th class="center w100">渠道</th>
                    <th class="center w500">简历</th>
                    <th class="center">操作</th>
                </tr>
            </thead>
            <?php foreach($resumes as $key=>$resume): ?>
            <tr>
                <td class="hidden"><?php echo "{$resume->id}";?></td>
                <td class="hidden"><input type="checkbox" name="checkbox"></td>
                <td class="hidden"><?php  if(!empty($resume->assessment)) echo $resume->assessment->id; ?></td>
                <td><?php echo $resume->name; ?></td>
                <td><?php echo $resume->source; ?></td>
                <td>
                    <a target="_blank" href="<?php echo '/oa/viewResume/id/'.$resume->id; ?>"><?php echo "{$resume->apply->title}-{$resume->name}-{$resume->source}-".date('Ymd',strtotime($resume->create_time)).substr($resume->resume_file,8); ?></a>
                    <a href="<?php echo "/oa/downloadResume/id/{$resume->id}";?>">下载</a>
                </td>
    <?php if($resume->status == 'create'): ?>
                <td>
                    <?php if($resume->apply->user_id == Yii::app()->session['user_id']): ?>
                    <button class="btn btn-default b5c w80 pd3 conform-btn" onclick="conformResume(this);"><span class="glyphicon glyphicon-ok" ></span>&nbsp;符合</button>
                    <button class="btn btn-default b2 w80 pd3 inconformity-btn" onclick="conformResume(this);"><span class="glyphicon glyphicon-remove" ></span>&nbsp;不符合</button>
                    <?php else: ?>
                    待审批
                    <?php endif; ?>
                </td>
    <?php elseif($resume->status == 'conform'): ?>
                <td>
                    <?php if(!$tag): ?>
                    <span class="glyphicon glyphicon-time"></span>&nbsp;安排面试中
                    <?php else: ?>
                    <a class="pointer" onclick="interviewManage(this);">安排面试时间</a>
                    <?php endif; ?>
                </td>
    <?php elseif($resume->status == 'inconformity'): ?>
                <td>不符合要求</td>
    <?php elseif($resume->status == 'arrange' && empty($resume->interviewer)): ?>         
                <td>

                    面试时间：<?php echo date('Y年m月d日 H:i', strtotime($resume->interview_time)); ?>
                    <?php if($tag): ?>
                    <a class="pointer" onclick="editInterviewTime(this, '<?php echo $resume->interview_time;?>');">修改</a>
                    <?php endif; ?>
                    <?php if($resume->apply->user_id == Yii::app()->session['user_id']): ?>
                    </br>
                    选择面试官：
                    <input class="w130 interviewer-input" placeholder="请输入中文名">
                    <button class="btn btn-success pd3 mt10" onclick="sendInterviewer(this);">确定面试官</button>
                    <?php endif; ?>
                </td>
    <?php elseif($resume->status == 'arrange' && !empty($resume->interviewer)): ?>
                <td>
                    面试时间：<?php echo date('Y年m月d日 H:i', strtotime($resume->interview_time)); ?>
                    <?php if($tag): ?>
                    <a class="pointer" onclick="editInterviewTime(this, '<?php echo $resume->interview_time;?>');">修改</a>
                    <?php endif; ?>
                    </br>
                    面试官：<?php $interviewer_apply = $apply_add_info[$key]; if(!empty($interviewer_apply)) echo $interviewer_apply->cn_name; ?>
                    </br>
                    <?php if($tag):?>
                    <button class="btn btn-default b5c w80 pd3 success-btn mt10" onclick="conformResume(this);"><span class="glyphicon glyphicon-ok" ></span>&nbsp;已面试</button>
                    <button class="btn btn-default b2 w80 pd3 nonarrival-btn mt10" onclick="conformResume(this);"><span class="glyphicon glyphicon-remove" ></span>&nbsp;缺席</button>
                    <?php endif; ?>
                </td>
    
    <?php elseif($resume->status == 'nonarrival'): ?>
                <td>没有来面试</td>
    <?php elseif($resume->status == 'giveup'): ?>
                <td>放弃入职</td>
    <?php elseif($resume->status == 'success'): ?>
                <td>
                    <?php if(!$tag): ?>
                    已面试，待填写评估表
                    <?php else: ?>
                    <a href="/oa/interviewEvaluateDetail/id/<?php echo $resume->id;?>">已面试，填写评估表</a>
                    <?php endif; ?>
                </td>
    <?php elseif($resume->status == 'assessment'): ?>
                <td>
                    <?php if(!$tag): ?>
                    <a href="/oa/interviewEvaluateDetail/id/<?php echo $resume->id;?>">请填写评估表</a>
                    <?php else: ?>
                    <a href="/oa/interviewEvaluateDetail/id/<?php echo $resume->id;?>">查看评估表</a>
                    <?php endif; ?>
                </td>
    <?php elseif($resume->status == 'entry'): ?>
                <td>
                    <?php if(!$tag): ?>
                    <a href="/oa/interviewEvaluateDetail/id/<?php echo $resume->id;?>" title="查看评估表">即将入职</a>
                    <?php else: ?>
                    <a href="/oa/interviewEvaluateDetail/id/<?php echo $resume->id;?>" title="查看评估表">即将入职</a>
                    <button class="btn btn-default b2 w80 pd3 giveup-btn ml10" onclick="sendGiveUp(this);"><span class="glyphicon glyphicon-remove" ></span>&nbsp;放弃入职</button>
                    <?php endif; ?>
                </td>
    <?php else: ?>
                <td></td>
    <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <h4 class="center">还没有收集到符合要求的简历</h4>
        <?php endif; ?>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success w100 <?php if(!$tag || $apply->status == "entry") echo 'hidden';?>" onclick="showUploadResume();">上传简历</button>
        <?php if(!empty($resumes)):?>
        <button class="btn btn-success w100" onclick="showCheckbox();" id="download-any-btn">批量下载</button>
        <button class="btn btn-success w100 hidden" onclick="downloadAny();" id="download-btn">下载</button>
        <button class="btn btn-default w100 hidden" onclick="hideCheckbox();" id="download-cancel-btn">取消</button>
        <?php endif; ?>
    </div>
</div>

<!-- 安排面试时间的模态框 -->
<div id="interview-time-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" >×</a>
        <h4 class="hint-title">安排面试时间</h4>
    </div>

    <div class="modal-body">
        <table class="table bor-1-ddd m0 center">
            <tr>
                <th class="w100 center">面试日期</th>
                <td><input class="form-control w150 pointer" id="interview-date" value="<?php echo date('Y-m-d',strtotime('+2days'));?>"></td>
                <td class="hidden" id="interview-id"></td>
            </tr>
            <tr>
                <th class="w100 center">面试时间</th>
                <td>
                    <div class="fl">
                        <div>
                            <button class="btn btn-default pd3 w36 bor-none f10px mr20 ml5" id="start-hour-minus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-up"></span></button>
                            <button class="btn btn-default pd3 w36 bor-none f10px" id="start-minute-minus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-up"></span></button>
                        </div>
                        <div>
                            <input type="text" class="form-control center w50 m0a h30 inline" id="interview-start-hour-input" value="09" onchange="hourInputCheck(this);">
                            :
                            <input type="text" class="form-control center w50 m0a h30 inline" id="interview-start-minute-input" value="30" onchange="minuteInputCheck(this);">
                        </div>
                        <div>
                            <button class="btn btn-default pd3 w36 bor-none f10px mr20 ml5" id="start-hour-plus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-down"></span></button>
                            <button class="btn btn-default pd3 w36 bor-none f10px" id="start-minute-plus" onclick="timeSet(this.id);"><span class="glyphicon glyphicon-chevron-down"></span></button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success hidden" onclick="sendInterviewTime();" id="send-interview-time-btn">确定</button>
        <button class="w100 btn btn-success hidden" onclick="modifyInterviewTime();" id="modify-interview-time-btn">确定更改</button>
    </div>
</div>

<!-- 上传简历的模态框 -->
<div id="upload-resume-div" class="modal fade in hint bor-rad-5 w1000" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">上传简历</h4>
    </div>

    <div class="modal-body">
        <label>请选择要上传的文件,文件类型为pdf(推荐)/doc(小于5M)：</label>
        <input type="file" id="file-upload" multiple onchange="addSelectedFile();">
        <label class="mt20">已选择的文件：</label></br>
        <table id="selected-file-table" class="table mb15 center table-bordered hidden">
            <thead>
                <tr>
                    <th class="w50 center">序号</th>
                    <th class="w400 center">文件名</th>
                    <th class="w200 center">姓名</th>
                    <th class="w200 center">来源</th>
                    <th class="w100 center">状态</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success w100 disabled" id="file-upload-btn" onclick="checkUploadInput();">上传</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
    // 显示上传
    function showUploadResume(){
        var ySet = (window.innerHeight - $("#upload-resume-div").height())/3;
        var xSet = (window.innerWidth - $("#upload-resume-div").width())/2;
        $("#upload-resume-div").css("top",ySet);
        $("#upload-resume-div").css("left",xSet);
        $('#upload-resume-div').modal({show:true});
    }

    // 显示简历
    function showViewResume(){
        var ySet = (window.innerHeight - $("#view-resume-div").height())/3;
        var xSet = (window.innerWidth - $("#view-resume-div").width())/2;
        $("#view-resume-div").css("top",ySet);
        $("#view-resume-div").css("left",xSet);
        $('#view-resume-div').modal({show:true});
    }

    // 修改面试时间-发送
    function modifyInterviewTime(){
        var id = $("#interview-id").text();
        var date = $("#interview-date").val();
        var time = $("#interview-start-hour-input").val()+":"+$("#interview-start-minute-input").val()+":00";
        var time_str = date+" "+time;
        var time_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
        if(!time_pattern.exec(time_str)){
            showHint("提示信息","时间格式不正确!");
        }else{
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/editInterviewTime',
                data:{'id':id, 'time':time_str},
                success:function(data){
                    if(data.code == 0)
                    {
                        showHint("提示信息","修改面试时间成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(data.code == -1)
                    {
                        showHint("提示信息","修改面试时间失败");
                    }
                    else if(data.code == -2)
                    {
                        showHint("提示信息","参数错误");
                    }
                    else if(data.code == -3)
                    {
                        showHint("提示信息","找不到该简历");
                    }
                    else
                    {
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
        }
    }

    // 修改面试时间-显示
    function editInterviewTime(obj,time){
        var id = $(obj).parent().parent().children().first().text();
        $("#interview-id").text(id);
        $("#modify-interview-time-btn").removeClass("hidden");
        $("#send-interview-time-btn").addClass("hidden");
        $("#interview-time-div").find("h4.hint-title").text("修改面试时间");
        
        var date = time.split(" ")[0];
        var hour = time.split(" ")[1].split(":")[0];
        var minute = time.split(" ")[1].split(":")[1];
        $("#interview-date").val(date);
        $("#interview-start-hour-input").val(hour);
        $("#interview-start-minute-input").val(minute);
        var ySet = (window.innerHeight - $("#interview-time-div").height())/3;
        var xSet = (window.innerWidth - $("#interview-time-div").width())/2;
        $("#interview-time-div").css("top",ySet);
        $("#interview-time-div").css("left",xSet);
        $("#interview-time-div").modal({show:true});
    }

    // 安排面试-发送
    function sendInterviewTime(){
        var id = $("#interview-id").text();
        var date = $("#interview-date").val();
        var time = $("#interview-start-hour-input").val()+":"+$("#interview-start-minute-input").val()+":00";
        var time_str = date+" "+time;
        var time_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
        if(!time_pattern.exec(time_str)){
            $("#interview-date").focus();
            showHint("提示信息","日期格式不正确!");
        }else{
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/interviewTime',
                data:{'id':id, 'time':time_str},
                success:function(data){
                    if(data.code == 0)
                    {
                        showHint("提示信息","设置面试时间成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(data.code == -1)
                    {
                        showHint("提示信息","设置面试时间失败");
                    }
                    else if(data.code == -2)
                    {
                        showHint("提示信息","参数错误");
                    }
                    else if(data.code == -3)
                    {
                        showHint("提示信息","找不到该简历");
                    }
                    else
                    {
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
        }
    }

    // 安排面试-显示
    function interviewManage(obj){
        var id = $(obj).parent().parent().children().first().text();
        $("#interview-id").text(id);
        $("#modify-interview-time-btn").addClass("hidden");
        $("#send-interview-time-btn").removeClass("hidden");
        $("#interview-time-div").find("h4.hint-title").text("安排面试时间");
        var ySet = (window.innerHeight - $("#interview-time-div").height())/3;
        var xSet = (window.innerWidth - $("#interview-time-div").width())/2;
        $("#interview-time-div").css("top",ySet);
        $("#interview-time-div").css("left",xSet);
        $("#interview-time-div").modal({show:true});
    }

    // 批量下载-显示多选框
    function showCheckbox(){
        $("#resume-table").find("thead").children().first().children().first().removeClass("hidden");
        $('input[name="checkbox"]').parent().removeClass("hidden");
        $("#download-cancel-btn").removeClass("hidden");
        $("#download-btn").removeClass("hidden");
        $("#download-any-btn").addClass("hidden");
    }

    // 取消-隐藏多选框
    function hideCheckbox(){
        $("#resume-table").find("thead").children().first().children().first().addClass("hidden");
        $('input[name="checkbox"]').parent().addClass("hidden");
        $("#download-any-btn").removeClass("hidden");
        $("#download-cancel-btn").addClass("hidden");
        $("#download-btn").addClass("hidden");
    }

    // 下载-下载选中的简历
    function downloadAny(){
        var href_str = "";
        $('input[name="checkbox"]:checked').each(function(){
            href_str = $(this).parent().next().next().next().next().find("a").first().next().attr("href");
            window.open(href_str);
        });
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

    // 确定面试官
    function sendInterviewer(obj){
        var id = $(obj).parent().parent().children().first().text();
        var name = $(obj).prev().val();
        var user_id = "";
        $.each(users_arr, function(){
            if(this['name'] == name) user_id = this['id'];
        });
        if(user_id == ""){
            showHint("提示信息","查找不到此用户");
            $(obj).prev().focus();
        }else{
            $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/interviewer',
            data:{'id':id,'interviewer':user_id},
            success:function(data){
                if(data.code == 0){
                    showHint("提示信息","指定面试官成功");
                    setTimeout(function(){location.reload();},1200);
                }else if(data.code == -1){
                    showHint("提示信息","指定面试官失败");
                }else if(data.code == -2){
                    showHint("提示信息","参数错误");
                }else if(data.code == -3){
                    showHint("提示信息","查找不到此申请");
                }else if(data.code == -4){
                    showHint("提示信息","查找不到此面试官");
                }else{
                    showHint("提示信息","你没有权限执行此操作");
                }
            }
        });
        }
    }


    // 页面初始化
	$(document).ready(function(){
        $('#interview-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']);

        $(".interviewer-input").autocomplete({
            source:cn_name_arr
        });

        // 同意申请
	    $("#agree").click(function(){
	    	var id = $("#apply-id").text();
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/agreeRecruitApply',
                data:{'id':id},
                success:function(data){
                    if(data.code == 0)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","同意成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(data.code == -1)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","同意失败");
                    }
                    else if(data.code == -2)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","参数错误");
                    }
                    else if(data.code == -3)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","找不到该申请");
                    }
                    else
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","你没有权限执行此操作");
                    }
                }
            });
   		});
        // 弹出模态框
	    $("#reject").click(function(){
	        var ySet = (window.innerHeight - $("#reject-reason-div").height())/3;
            var xSet = (window.innerWidth - $("#reject-reason-div").width())/2;
            $("#reject-reason-div").css("top",ySet);
            $("#reject-reason-div").css("left",xSet);
	        $("#reject-reason-div").modal({show:true});
	        $('#agree').addClass('disabled');
	        $('#reject').addClass('disabled');
	    });
	});

    // 拒绝申请
	function rejectSubmit(){
	    var reject_reason = $("#reject-input").val();
	    var id = $("#apply-id").text();
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/rejectRecruitApply',
            data:{'id':id,'reason':reject_reason},
            success:function(data){
                if(data.code == 0)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","退回申请成功");
                        setTimeout(function(){location.reload();},1200);
                    }
                    else if(data.code == -1)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","退回申请失败");
                    }
                    else if(data.code == -2)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","参数错误");
                    }
                    else if(data.code == -3)
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","找不到该申请");
                    }
                    else
                    {
                        $("#page-wait").modal('hide');
                        showHint("提示信息","你没有权限执行此操作");
                    }
            }
        });
	}

    // 放弃职位
    function sendGiveUp(obj){
        var id = $(obj).parent().parent().children().first().next().next().text();
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/giveUp',
            data:{'id':id},
            success:function(data){
                if(data.code == 0)
                {
                    var str = "放弃入职";
                    $(obj).parent().html(str);
                }
                else if(data.code == -1)
                {
                    showHint("提示信息","操作失败，请重试");
                }
                else if(data.code == -2)
                {
                    showHint("提示信息","参数错误");
                }
                else if(data.code == -3)
                {
                    showHint("提示信息","找不到该招聘申请");
                }
                else
                {
                    showHint("提示信息","你没有权限执行此操作");
                }
            }
        });
    }

    // 符合或不符合-发送
    function conformResume(obj){
        var id = $(obj).parent().parent().children().first().text();
        var status = "";
        if($(obj).hasClass("conform-btn")){
            status = "conform";
        }else if($(obj).hasClass("inconformity-btn")){
            status = "inconformity";
        }else if($(obj).hasClass("success-btn")){
            status = "success";
        }else if($(obj).hasClass("giveup-btn")){
            status = "giveup";
        }else{
            status = "nonarrival";
        }
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/conformResume',
            data:{'id':id,'status':status},
            success:function(data){
                if(data.code == 0)
                    {
                        if(status == "conform"){
                            var str = "<span class='glyphicon glyphicon-time'></span>&nbsp;安排面试中";
                            $(obj).parent().html(str);
                        }else if(status == "inconformity"){
                            var str = "不符合要求";
                            $(obj).parent().html(str);
                        }else if(status == "success"){
                            <?php if($tag):?>
                            var str = "<a href='/oa/interviewEvaluateDetail/id/"+id+"'>已面试，填写评估表</a>";
                            $(obj).parent().html(str);
                            <?php else: ?>
                            var str = "已面试，待填写评估表";
                            $(obj).parent().html(str);
                            <?php endif; ?>
                        }else if(status == "giveup"){
                            var str = "放弃入职";
                            $(obj).parent().html(str);
                        }else{
                            var str = "没有来面试";
                            $(obj).parent().html(str);
                        }
                    }
                    else if(data.code == -1)
                    {
                        showHint("提示信息","操作失败，请重试");
                    }
                    else if(data.code == -2)
                    {
                        showHint("提示信息","参数错误");
                    }
                    else if(data.code == -3)
                    {
                        showHint("提示信息","找不到该简历");
                    }
                    else
                    {
                        showHint("提示信息","你没有权限执行此操作");
                    }
            }
        });
    }

    
    var file_arr = new Array();

    // 将打开的添加进file_arr中
    function addSelectedFile(){
        var new_file_arr = document.getElementById("file-upload").files;
        var same_tag = 0;
        $.each(new_file_arr,function(){
            var tag = 0;
            var new_name = this.name;
            $.each(file_arr, function(){
                if(new_name == this.name){
                    tag = 1;
                    same_tag = 1;
                }
            });
            if(tag == 0){
                file_arr.push(this);
            }
        });

        if(same_tag == 1) showHint("提示信息","重复选择了文件！");
        showSelectedFile();
    }

    // 显示选中的文件
	function showSelectedFile(){
        // 显示file_arr的文件
        $("#selected-file-table").removeClass("hidden");
		$("#selected-file-table").find("tbody").children().remove();
        var file_type_tag = 0;
        var num = 1;
		$.each(file_arr, function(key,value){
            if(value != null){
                // var file_type = this.name.split("\.")[1];
                var file_type = this.type;
                if(file_type.indexOf("officedocument") > -1 || file_type.indexOf("word") > -1 || file_type.indexOf("pdf") > -1){
                    var str = "<tr id='tr-"+(key+1)+"'><td>"+num+"</td><td>"+this.name+"&nbsp;<span class='glyphicon glyphicon-remove b2 pointer' title='删除'></span></td>"+
                    "<td><input class='form-control center' placeholder='请输入应聘人姓名'></td>"+
                    "<td class='center'><select class='form-control' onchange='showOther(this);'><option value='51job投递'>51job投递</option><option value='51job搜索'>51job搜索</option><option value='大街网'>大街网</option><option value='业内推荐'>业内推荐</option><option value='内部推荐'>内部推荐</option><option value='Q群'>Q群</option><option value='学校就业网'>学校就业网</option><option value='其他'>其他</option></select><a class='pointer hidden ml10' onclick='hideOther(this);'>返回</a></td><td>待上传</td></tr>";
                    $("#selected-file-table").find("tbody").append(str);
                    num ++;
                }else{
                    var str = "<tr id='tr-"+(key+1)+"'><td>"+num+"</td><td class='b2'>"+this.name+"&nbsp;格式不符&nbsp;<span class='glyphicon glyphicon-remove b2 pointer' title='删除'></span></td><td></td><td></td><td></td></tr>";
                    $("#selected-file-table").find("tbody").append(str);
                    num ++;
                    file_type_tag = 1;
                }

                // 给删除按钮绑定事件
                $("#tr-"+(key+1)).find("span.glyphicon-remove").bind("click", function(){
                    deleteSelectedFile(key);
                });
            }
		});
        
        // 提示用户选择的文件格式不正确
        if(file_type_tag == 1){
            showHint("提示信息","请选择word文档！");
        }else{
            $("#file-upload-btn").removeClass("disabled");
        }
	}

    // 删除选中的文件
    function deleteSelectedFile(key){
        file_arr[key] = null;
        showSelectedFile();
    }

    // 来源-下拉列表选择其他时变成输入框
    function showOther(obj){
        if($(obj).val() == "other"){
            $(obj).addClass("hidden");
            $(obj).next().val("");
            $(obj).next().removeClass("hidden");
            $(obj).next().next().removeClass("hidden");
        }
    }

    // 来源-返回显示下拉列表
    function hideOther(obj){
        $(obj).prev().prev().val("recommend");
        $(obj).prev().prev().removeClass("hidden");
        $(obj).addClass("hidden");
        $(obj).prev().addClass("hidden");
    }  

    // 检查上传的输入
    function checkUploadInput(){
        var input_tag = 0;
        $("#selected-file-table").find("tbody").children().each(function(){
            if(input_tag == 0){
                var name = $(this).find("input").first().val();
                if(name == ""){
                    showHint("提示信息","请输入应聘人姓名!");
                    $(this).find("input").first().focus();
                    input_tag = 1;
                }else if($(this).children().last().prev().find("select.hidden").text() != ""){
                    if($(this).children().last().prev().find("input").first().val() == ""){
                        showHint("提示信息","请输入来源!");
                        $(this).children().last().prev().find("input").first().focus();
                        input_tag = 1;
                    }
                }
            }
        });
        if(input_tag == 0) uploadResume();
    }

    // 上传简历
    var index = 0;
    function uploadResume(){
        sendResume();
        index++;
    }
    
    var fault_tag = 0;
    function sendResume(){
        // 跳过空的文件
        var null_tag = 0;
        do{
            if(file_arr[index] == null){
                if(index == file_arr.length){
                    null_tag = 1;
                }else{
                    index ++;
                }
            }else{
                null_tag = 1;
            }
        }while(null_tag == 0);
        
        // 最后一个为空则跳出循环
        if(index == file_arr.length && file_arr[index] == null){
            index = 0;
            if(fault_tag == 0){
                file_arr = new Array();
                showHint("提示信息","上传完毕！");
                setTimeout(function(){location.reload();},1200);
            }else{
                showHint("提示信息","出现错误，未完成上传111！");
                var obj = $("#file-upload");
                $(obj).before("<input type='file' id='file-upload' multiple onchange='addSelectedFile();'>").remove();
                fault_tag = 0;
            }
            return false;
        }

        // 获取参数-id,name,resource
        var id = "<?php echo $apply->id; ?>";
        var name = $("#tr-"+(index+1)).find("input").first().val();
        if($("#tr-"+(index+1)).children().last().prev().find("select.hidden").text() != ""){         // 其他
            var resource = $("#tr-"+(index+1)).children().last().prev().find("input").first().val();
        }else{   
            var resource = $("#tr-"+(index+1)).find("select").first().val();                                                                    // 预设的选项
        }

        // 发送resume
        var FileController = "/ajax/batchCommitResume";                    // 接收上传文件的后台地址 
        // FormData 对象
        var form = new FormData();
        form.append("resume", file_arr[index]);                          // 文件对象
        form.append("name", name);
        form.append("resource", resource);
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
                    $("#tr-"+(index)).children().last().html("<span class='glyphicon glyphicon-ok b5c'></span>&nbsp;上传成功");
                }else if(code == -1){
                    $("#tr-"+(index)).children().last().html("<span class='glyphicon glyphicon-remove b2'></span>&nbsp;上传失败");
                    fault_tag = 1;
                }else if(code == -2){
                    $("#tr-"+(index)).children().last().html("<span class='glyphicon glyphicon-remove b2'></span>&nbsp;参数错误");
                    fault_tag = 1;
                }else if(code == -3){
                    $("#tr-"+(index)).children().last().html("<span class='glyphicon glyphicon-remove b2'></span>&nbsp;找不到该申请");
                    fault_tag = 1;
                }else if(code == -4){
                    $("#tr-"+(index)).children().last().html("<span class='glyphicon glyphicon-remove b2'></span>&nbsp;文件类型错误");
                    fault_tag = 1;
                }else if(code == -5){
                    $("#tr-"+(index)).children().last().html("<span class='glyphicon glyphicon-remove b2'></span>&nbsp;大小超过5M");
                    fault_tag = 1;
                }else{
                    $("#tr-"+(index+1)).children().last().html("<span class='glyphicon glyphicon-remove b2'></span>&nbsp;没有权限");
                    fault_tag = 1;
                }

                // 判断是否上传完成
                if(index < file_arr.length){
                    setTimeout(function(){uploadResume();},100);
                }else{
                    index = 0;
                    if(fault_tag == 0){
                        file_arr = new Array();
                        showHint("提示信息","上传完毕！");
                        setTimeout(function(){location.reload();},1200);
                    }else{
                        showHint("提示信息","出现错误，未完成上传！");
                        var obj = $("#file-upload");
                        $(obj).before("<input type='file' id='file-upload' multiple onchange='addSelectedFile();'>").remove();
                        fault_tag = 0;
                        file_arr = new Array();
                    }
                }
            }
        }
    }

    // 时间设置
    function timeSet(id){
        switch(id){
            case "start-hour-minus":{
                var start_hour = parseInt($("#interview-start-hour-input").val());
                if(start_hour == 0){
                    start_hour = 23;
                }else{
                    start_hour -= 1;
                }
                var start_hour_str = "";
                if(start_hour < 10){
                    start_hour_str = "0"+start_hour;
                }else{
                    start_hour_str = start_hour;
                }
                $("#interview-start-hour-input").val(start_hour_str);
                break;
            }
            case "start-hour-plus":{
                var start_hour = parseInt($("#interview-start-hour-input").val());
                if(start_hour == 23){
                    start_hour = 0;
                }else{
                    start_hour += 1;
                }
                
                var start_hour_str = "";
                if(start_hour < 10){
                    start_hour_str = "0"+start_hour;
                }else{
                    start_hour_str = start_hour;
                }
                $("#interview-start-hour-input").val(start_hour_str);
                break;
            }
            case "start-minute-minus":{
                var start_minute = $("#interview-start-minute-input").val();
                if(start_minute == "00"){
                    start_minute = "30";
                }else{
                    start_minute = "00";
                }
                $("#interview-start-minute-input").val(start_minute);
                break;
            }
            case "start-minute-plus":{
                var start_minute = $("#interview-start-minute-input").val();
                if(start_minute == "00"){
                    start_minute = "30";
                }else{
                    start_minute = "00";
                }
                $("#interview-start-minute-input").val(start_minute);
                break;
            }


            case "edit-start-hour-minus":{
                var start_hour = parseInt($("#edit-start-hour-input").val());
                if(start_hour == 0){
                    start_hour = 23;
                }else{
                    start_hour -= 1;
                }
                var start_hour_str = "";
                if(start_hour < 10){
                    start_hour_str = "0"+start_hour;
                }else{
                    start_hour_str = start_hour;
                }
                $("#edit-start-hour-input").val(start_hour_str);
                break;
            }
            case "edit-start-hour-plus":{
                var start_hour = parseInt($("#edit-start-hour-input").val());
                if(start_hour == 23){
                    start_hour = 0;
                }else{
                    start_hour += 1;
                }
                
                var start_hour_str = "";
                if(start_hour < 10){
                    start_hour_str = "0"+start_hour;
                }else{
                    start_hour_str = start_hour;
                }
                $("#edit-start-hour-input").val(start_hour_str);
                break;
            }
            case "edit-start-minute-minus":{
                var start_minute = $("#edit-start-minute-input").val();
                if(start_minute == "00"){
                    start_minute = "30";
                }else{
                    start_minute = "00";
                }
                $("#edit-start-minute-input").val(start_minute);
                break;
            }
            case "edit-start-minute-plus":{
                var start_minute = $("#edit-start-minute-input").val();
                if(start_minute == "00"){
                    start_minute = "30";
                }else{
                    start_minute = "00";
                }
                $("#edit-start-minute-input").val(start_minute);
                break;
            }
        }
    }

    // 小时输入检测
    function hourInputCheck(obj){
        var hour = $(obj).val();
        if(parseInt(hour) < 10){
            hour = "0" + parseInt(hour);
        }
        var d_pattern = /^\d{2}$/;
        if(!d_pattern.exec(hour) || parseInt(hour) >= 24 || parseInt(hour) < 0){
            showHint("提示信息","小时格式输入错误");
            $(obj).val("09");
        }else{
            $(obj).val(hour);
        }
    }

    // 分钟输入检测
    function minuteInputCheck(obj){
        var minute = $(obj).val();
        if(parseInt(minute) < 10){
            minute = "0" + parseInt(minute);
        }
        if(minute != "30" && minute != "00"){
            showHint("提示信息","分钟格式输入错误！");
            $(obj).val("00");
        }else{
            $(obj).val(minute);
        }
    }
</script>
