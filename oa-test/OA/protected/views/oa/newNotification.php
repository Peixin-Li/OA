<?php
echo "<script type='text/javascript'>";
echo "console.log('newNotification');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
  <!-- 标题 -->
  <h4 class="pd10 m0 b33">发布公告</h4>
  <!-- 发布公告 -->
  <table class="table bor-t-none m0">
  	<tr>
  		<th class="w130 center">公告类型</th>
  		<td>
        <button class="btn btn-success w100" onclick="typeSelect(this);">放假通知</button>
        <button class="btn btn-default w100" onclick="typeSelect(this);">行政通知</button>
        <button class="btn btn-default w100" onclick="typeSelect(this);">活动通知</button>
        <button class="btn btn-default w100" onclick="typeSelect(this);">人事任命</button>
        <button class="btn btn-default w100" onclick="typeSelect(this);">内部悬赏</button>
        <span class="b200 ml20">(点击选择公告类型)</span>
  		</td>
  	</tr>
  	<tr>
  		<th class="w130 center">标题</th>
  		<td><input class="form-control" id="title" placeholder="请输入标题，50字以内"></td>
  	</tr>
  	<tr>
  		<th class="w130 center">内容</th>
  		<td><textarea class="form-control" rows="10" id="content" placeholder="请输入内容，不超过1500字"></textarea></td>
  	</tr>
    <tr>
      <th class="w130 center">过期方式</th>
      <td>
        <input type="radio" name="edit-end-type" value="auto" id="auto-end-type" onclick="endTypeChange(this.id)" ><span class="mr20 pointer" onclick="$('#auto-end-type').click();">自动</span>
        <input type="radio" name="edit-end-type" value="manual" id="manual-end-type" onclick="endTypeChange(this.id)" checked><span class="pointer" onclick="$('#manual-end-type').click();">手动撤销</span>
      </td>
    </tr>
  	<tr id="end-time-tr" class="hidden">
  		<th class="w130 center">过期时间</th>
  		<td><p class="m0 hidden" style="padding:7px;">无</p><input class="form-control w130 pointer" id="end-time" value="<?php echo date('Y-m-d');?>" onchange="dateCheck();"></td>
  	</tr>
  	<tr>
  		<th class="w130 center">操作</th>
  		<td><button class="btn btn-success w100" onclick="sendNotification();">发布</button></td>
  	</tr>
  </table>
</div>

<!-- js -->
<script type="text/javascript">
  // 页面初始化
	$(document).ready(function(){
    // 日期选择控件初始化
		$('#end-time').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
	    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	});

  // 日期检测-不能选择今天以前的日期
  function dateCheck(){
    var today = "<?php echo date('Y-m-d');?>";
    if($("#end-time").val() < today){
      showHint("提示信息","过期时间不能在今天之前");
      $("#end-time").val(today);
    }
  }

  // 选择过期方式
  function endTypeChange(id){
    if(id == "auto-end-type"){
      $("#end-time-tr").removeClass("hidden"); 
    }else{
      $("#end-time-tr").addClass("hidden"); 
    }
  }

	// 发布通知
	function sendNotification(){
		var title = $("#title").val();
		var content = $("#content").val();
		var end_time = "";
    if($("input[name='edit-end-type']:checked").val() == "auto"){
      end_time = $("#end-time").val();
    }
    var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
    if(title == ""){
      showHint("提示信息","请输入标题");
      $("#title").focus();
    }else if(title.length > 50){
      showHint("提示信息","标题长度不能大于50字");
      $("#title").focus();
    }else if(content == ""){
      showHint("提示信息","请输入内容");
      $("#content").focus();
    }else if(content.length > 1500){
      showHint("提示信息","内容长度不能大于1500字");
      $("#content").focus();
    }else if(!date_pattern.exec(end_time) && end_time != ""){
      showHint("提示信息","过期时间格式不正确");
    }else{
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/addNotify',
        data:{'type':type, 'title':title, 'content':content, 'expire_time':end_time},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","发布成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","发布失败，请重试！");
          }else if(result.code == -2){
            showHint("提示信息","参数错误，请重试！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
	}

  // 公告类型选择
  var type = "holiday";
  function typeSelect(obj){
    switch($(obj).text()){
      case "放假通知":{
        $("#manual-end-type").click();
        type = "holiday";
        break;
      }
      case "内部悬赏":{
        $("#manual-end-type").click();
        type = "internal";
        break;
      }
      case "人事任命":{
        $("#manual-end-type").click();
        type = "appointments";
        break;
      }
      case "活动通知":{
        $("#manual-end-type").click();
        type = "activity";
        break;
      }
      case "行政通知":{
        $("#manual-end-type").click();
        type = "admin";
        break;
      }
      case "其他通知":{
        $("#manual-end-type").click();
        type = "other";
        break;
      }
    }
    $(obj).parent().find("button").removeClass("btn-success").addClass("btn-default");
    $(obj).addClass("btn-success");
  }
</script>