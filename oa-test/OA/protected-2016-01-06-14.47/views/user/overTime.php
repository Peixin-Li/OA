<?php
echo "<script type='text/javascript'>";
echo "console.log('overTime');";
echo "</script>";
?>

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
<div>
  <div id="overtime-info-div" class="pd20 bor-1-ddd">
    <!-- 加班登记 -->
    <div class="pb20 pl5">
      <!-- 标题 -->
      <h4 class="mb15">
        <strong>加班登记</strong>
      </h4>
      <!-- 登记类型切换标签 -->
      <ul class="nav nav-tabs w800">
        <li role="presentation" class="active"><a class="pointer w150 center" onclick="switchToWeekday(this);">工作日</a></li>
        <li role="presentation"><a class="pointer w150 center" onclick="switchToHoliday(this);">周末及法定节假日</a></li>
      </ul>
      <div class="pd10 bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd w800">
        <!-- 加班时间 -->
        <div>
          <span class="f15px">加班时间：</span>
          <!-- 周一到周五 -->
          <div class="inline-block" id="weekday-date-div" style="vertical-align:middle;">
            <input  style="vertical-align:middle;" class="form-control w150 inline pointer" id="weekday-date" value="<?php echo date('Y-m-d H:i');?>" onchange="weekdayCal();weekdayCheck();">
            <span id="weekday-weekday" class="ml5"></span>
          </div>
          
          <?php 
            // 计算这周末的起始和结束
            if(date('w') == 0){ // 周日
              $start = date('Y-m-d',strtotime('last saturday'));
              $end = date('Y-m-d');
            }else if(date('w') == 6){  // 周六
              $start =  date('Y-m-d');
              $end = date('Y-m-d', strtotime('next sunday'));
            }else{
              $start =  date('Y-m-d', strtotime('next saturday'));
              $end = date('Y-m-d', strtotime('next sunday'));
            }
          ?>
          <!-- 节假日及周末 -->
          <div class="inline-block hidden" id="holiday-date-div" style="vertical-align:middle;">
            <input class="form-control w130 inline pointer" id="holiday-start" value="<?php echo $start;?>" onchange="$('#holiday-end').val($(this).val());holidayCal();workdayCheck();holidayCheck();">
            <span id="holiday-weekday-start" class="ml5 mr5"></span>
            <select class="form-control w100 inline" id="start-time">
              <option value="09:30">09:30</option>
              <option value="13:30">13:30</option>
            </select>
            <span class="ml5 mr5">至</span>
            <input  class="form-control w130 inline pointer" id="holiday-end" value="<?php echo $end;?>" onchange="holidayCal();workdayCheck();holidayCheck();">
            <span id="holiday-weekday-end" class="ml5"></span>
            <select class="form-control w100 inline" id="end-time">
              <option value="12:00" id="noon-time">12:00</option>
              <option value="18:30">18:30</option>
            </select>
          </div>
        </div>
        <!-- 工作内容 -->
        <div class="mt10">
          <span class="f15px">工作内容：</span>
          <textarea class="form-control w400 inline" rows="3" id="overtime-content" style="vertical-align:top;" placeholder="请输入工作内容，限制在100字以内"></textarea>
        </div>
        <!-- 加班政策提示 -->
        <div id="remind-hint-div" class="gray" style="padding:5px 5px 5px 80px ;">
          加班政策：工作日加班，员工加班至晚上21:00可享有50元/天的加班补贴，加班至22：30可报销交通费。
        </div>
        <div class="pl50 pt10 nh48">
          <button class="btn btn-success w100 ml35" onclick="addWeekdayOverTime();" id="weekday-overtime-btn">提交</button>
          <button class="btn btn-success w100 ml35 hidden" onclick="addHolidayOverTime();" id="holiday-overtime-btn">提交</button>
        </div>
      </div>
    </div>
    <!-- 加班信息 -->
    <div class="center mt20">
      <!-- 标题 -->
      <h4 class="mb15 left">
        <strong>加班情况</strong>
      </h4>
      <!-- 加班情况类型切换标签 -->
      <ul class="nav nav-tabs">
        <li role="presentation" class="active"><a class="pointer" onclick="switchToPersonal(this);">我的加班情况</a></li>
        <li role="presentation"><a class="pointer" onclick="switchToDepartment(this);">部门加班情况</a></li>
      </ul>
      <!-- 我的加班信息 -->
      <div id="personal-overtime-div">
        <!-- 搜索栏 -->
        <div class="left pd10 bor-l-1-ddd bor-r-1-ddd">
          <label>日期：</label>
          <input class="form-control inline w130" id="personal-month" value="<?php echo date('Y-m');?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)">
          <button class="btn btn-success mt-5 w80 ml10" onclick="personalSearch();">查询</button>
        </div>
        <div style="min-height:70px;">
          <table class="table table-bordered m0 hidden" id="personal-table">
            <tr class="bg-fa">
              <th class="center">加班次数(工作日)</th>
              <th class="center">领取补贴(元)</th>
              <th class="center">加班天数(周末及法定节假日)</th>
              <th class="center">剩余补休(天)</th>
            </tr>
            <tr>
              <td id="personal-times"></td>
              <td id="personal-money"></td>
              <td id="personal-days"></td>
              <td id="personal-leaveCount"></td>
            </tr>
          </table>
          <h4 class="pd20 m0 center hidden bor-1-ddd" id="personal-none">没有加班记录</h4>
        </div> 
      </div>
      <!-- 部门加班信息 -->
      <div id="department-overtime-div" class="hidden">
        <div class="left pd10 bor-l-1-ddd bor-r-1-ddd">
         <label>日期：</label>
          <input class="form-control inline w130" id="department-month" value="<?php echo date('Y-m');?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)">
          <button class="btn btn-success mt-5 w80 ml10" onclick="departmentSearch();">查询</button>
        </div><!-- 搜索栏 -->
        <div style="min-height:70px;">
          <table class="table table-bordered m0 hidden" id="department-table">
            <thead>
              <tr class="bg-fa">
                <th class="center">姓名</th>
                <th class="center">职位</th>
                <th class="center">加班次数(工作日)</th>
                <th class="center">加班天数(周末及法定节假日)</th>
              </tr>
            </thead>
            <tbody>
              
            </tbody>
          </table>
          <h4 class="pd20 m0 center bor-1-ddd hidden" id="department-none">没有加班记录</h4>
        </div>
      </div>
    </div>
    <!-- 历史加班记录 -->
    <div class="center mt20" id="history-div">
      <!-- 标题 -->
      <h4 class="mt20 ml5 left">
        <strong>历史加班记录</strong>
      </h4>
      <!-- 历史加班记录表格 -->
      <?php if(!empty($data)): ?>
      <table class="bor-1-ddd m0 table center table-hover table-bordered">
        <thead>
          <tr class="bg-fa">
            <th class="w130 center">状态</th>
            <th class="center w150">类型</th>
            <th class="center left">工作内容</th>
            <th class="w300 center">加班时间</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($data as $row):?>
          <tr>
            <td>
              <?php
                if($row['status'] == "success"){
                  echo '已通过';
                }else if($row['status'] == "wait"){
                  echo '待审批';
                }else{
                  echo '已退回';
                }
              ?>
            </td>
            <td>
              <?php echo ($row['type'] == "holiday") ? "节假日或周末" : "工作日"; ?>
            </td>
            <td class="left">
              <?php if($row['type'] != "normal"): ?>
                <a href="/user/overtimeDetail/id/<?php echo $row['id'];?>"><?php echo $row['content']; ?></a>
              <?php else: ?>
                <?php echo $row['content']; ?>
              <?php endif; ?>
            </td>
            <td>
              <?php echo ($row['type'] == "holiday") ? substr($row['start_time'], 0, 16)." 至 ".substr($row['end_time'], 0, 16) : substr($row['end_time'], 0, 16);?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <h4 class="bor-1-ddd pd20 center">你还没有加过班</h4>
      <?php endif; ?>
      <!-- 分页栏 -->
      <div class="mt20">
        <?php 
            $this->widget('CLinkPager',array(
                'firstPageLabel'=>'首页',
                'lastPageLabel'=>'末页',
                'prevPageLabel'=>'上一页',
                'nextPageLabel'=>'下一页',
                'pages'=>$page,
                'maxButtonCount'=>10,
              )
            );
        ?>
      </div>
    </div>
  </div>
</div>


<!-- js -->
<script type="text/javascript">
/*------------------------------------------个人和部门加班信息--------------------------------------------*/

  // 查找自己的加班信息
  function personalSearch(){
    var month = $("#personal-month").val();
    var date_pattern = /^\d{4}-\d{2}$/;
    if(!date_pattern.exec(month)){
      showHint("提示信息", "日期输入格式有误");
    }else{
      $.ajax({
        type:'post',
        url:'/ajax/getUserOvertime',
        dataType:'json',
        data:{'month':month},
        success:function(result){
            if(result.code == 0){
              // 填充我的加班信息
              $("#personal-money").text(result.data['amount']);
              $("#personal-times").text(result.data['times']);
              $("#personal-days").text(result.data['days']);
              $("#personal-leaveCount").text(result.data['leaveCount']);

              // 显示我的加班信息表格
              $("#personal-table").removeClass("hidden");
              $("#personal-none").addClass("hidden");
            }else if(result.code == '-1'){
              $("#personal-table").addClass("hidden");
              $("#personal-none").removeClass("hidden");
            }else if(result.code == '-2'){
              $("#personal-table").addClass("hidden");
              $("#personal-none").removeClass("hidden");
            }else if(result.code == '-3'){
              $("#personal-table").addClass("hidden");
              $("#personal-none").removeClass("hidden");
            }
          }
      });
    }
  }

  // 查找部门的加班信息
  function departmentSearch(){
    var month = $("#department-month").val();
    var date_pattern = /^\d{4}-\d{2}$/;
    if(!date_pattern.exec(month)){
      showHint("提示信息", "日期输入格式有误");
    }else{
      $.ajax({
        type:'post',
        url:'/ajax/getDepartmentOvertime',
        dataType:'json',
        data:{'month':month},
        success:function(result){
            if(result.code == 0){
              // 清空部门加班信息表格
              $("#department-table").find("tbody").children().remove();

              // 填充数据
              $.each(result.data, function(key,value){
                var str = "<tr><td>"+value['name']+"</td><td>"+value['title']+"</td><td>"+value['times']+"</td><td>"+value['days']+"</td></tr>"
                $("#department-table").find("tbody").append(str);
              });

              // 显示部门加班信息表格
              $("#department-table").removeClass("hidden");
              $("#department-none").addClass("hidden");
            }else if(result.code == '-1'){
              $("#department-table").addClass("hidden");
              $("#department-none").removeClass("hidden");
            }else if(result.code == '-2'){
              $("#department-table").addClass("hidden");
              $("#department-none").removeClass("hidden");
            }else if(result.code == '-3'){
              $("#department-table").addClass("hidden");
              $("#department-none").removeClass("hidden");
            }
          }
      });
    }
  }

  // 显示部门加班信息
  function switchToDepartment(obj){
    $(obj).parent().addClass("active");
    $(obj).parent().prev().removeClass("active");
    $("#personal-overtime-div").addClass("hidden");
    $("#department-overtime-div").removeClass("hidden");
    $("#history-div").addClass("hidden");
  }

  // 显示我的加班信息
  function switchToPersonal(obj){
    $(obj).parent().addClass("active");
    $(obj).parent().next().removeClass("active");
    $("#personal-overtime-div").removeClass("hidden");
    $("#department-overtime-div").addClass("hidden");
    $("#history-div").removeClass("hidden");
  }

/*--------------------------------------------页面初始化--------------------------------------------*/

  // 切换到周一到周五
  function switchToWeekday(obj){
    $("#holiday-date-div").addClass("hidden");
    $("#holiday-overtime-btn").addClass("hidden");
    $("#weekday-date-div").removeClass("hidden");
    $("#weekday-overtime-btn").removeClass("hidden");

    // 清空工作内容
    $("#overtime-content").val("");

    // 高亮标签
    $(obj).parent().addClass("active");
    $(obj).parent().next().removeClass("active");

    // 工作日周几计算
    weekdayCal();

    // 工作日检测
    weekdayCheck();

    // 设置日历选择器
    $.datepicker.regional['zh-CN'].week_special_tag = true;
    $.datepicker.regional['zh-CN'].rest_special_tag = false;

    // 加班政策提示设置
    $("#remind-hint-div").text("加班政策：工作日加班，员工加班至晚上21:00可享有50元/天的加班补贴，加班至22：30可报销交通费。");
  }

  // 切换到周末和节假日
  function switchToHoliday(obj){
    $("#holiday-date-div").removeClass("hidden");
    $("#weekday-date-div").addClass("hidden");
    $("#weekday-overtime-btn").addClass("hidden");
    $("#holiday-overtime-btn").removeClass("hidden");

    // 清空工作内容
    $("#overtime-content").val("");

    // 高亮标签
    $(obj).parent().addClass("active");
    $(obj).parent().prev().removeClass("active");

    // 节假日周几的计算
    holidayCal();

    // 节假日检测
    holidayCheck();

    // 设置日历选择器
    $.datepicker.regional['zh-CN'].week_special_tag = false;
    $.datepicker.regional['zh-CN'].rest_special_tag = true;
    
    // 加班政策提示设置
    $("#remind-hint-div").text("加班政策：周末及法定节假日加班，员工可补休，需提前填写加班申请审批。");
  }

  // 节假日数组初始化
  var rest_day_arr = new Array();
  <?php 
    if(!empty($holidays)){
      foreach($holidays as $hrow){
        echo "rest_day_arr.push({'date':'{$hrow['holiday']}', 'type':'{$hrow['status']}','comment':'{$hrow['comment']}'});";
      }
    }
  ?>

  // 页面初始化
  var input_date = "<?php echo date('Y-m-d');?>";
  $(document).ready(function(){
    // 日期控件初始化
    $('#weekday-date').datetimepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $('#holiday-start').datepicker({
      dateFormat: 'yy-mm-dd',
      changeYear: true,
      minDate: '2015-02-01'
    });
    $('#holiday-end').datepicker({
      dateFormat: 'yy-mm-dd',
      changeYear: true,
      minDate: '2015-02-01'
    });

    // 设置日期选择器选择周一到周五
    $.datepicker.regional['zh-CN'].week_special_tag = true;
    $.datepicker.regional['zh-CN'].rest_special_tag = false;
    $.datepicker.regional['zh-CN'].rest_day = rest_day_arr;

    // 自动聚焦工作内容
    $("#overtime-content").focus();

    // 工作日周几的计算
    weekdayCal();

    // 工作日检测
    weekdayCheck();

    // 个人加班信息查询
    personalSearch();

    // 部门加班信息查询
    departmentSearch();
  });

/*-----------------------------------------------------加班登记-----------------------------------------------------*/

  // 提交工作日加班信息
  function addWeekdayOverTime(){
    // 获取数据
    var date = $("#weekday-date").val();
    var content = $("#overtime-content").val();
    var date_str = date.split(" ")[0];
    var time_str = date.split(" ")[1]+":00";
    var var_date = new Date(date);

    // 验证数据
    var date_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/;
    if(!date_pattern.exec(date)){
      showHint("提示信息","日期输入格式有误,请检查");
    }else if (var_date.getHours() < 21) {
      showHint("提示信息","加班时间未超过21点，提交失败")
    }else if(content == ""){
      showHint("提示信息","请输入工作内容");
      $("#overtime-content").focus();
    }
    else{
      $.ajax({
        type:'post',
        url:'/ajax/createOvertime',
        dataType:'json',
        data:{'overtime_date':date_str, 'overtime_time':time_str, 'content':content},
        success:function(result){
            if(result.code == 0){
              showHint("提示信息","提交加班信息成功");
              setTimeout(function(){location.reload();},1200);
            }else if(result.code == '-1'){
              showHint("提示信息","提交加班信息失败");
            }else if(result.code == '-2'){
              showHint("提示信息","参数错误");
            }else if(result.code == '-3'){
              showHint("提示信息","该加班时间已提交过了");
            }else{
              showHint("提示信息","你没有权限执行此操作");
            }
          }
      });
    }
  }

  // 提交周末和节假日加班信息
  function addHolidayOverTime(){
    // 获取数据
    var start_date = $("#holiday-start").val();
    var end_date = $("#holiday-end").val();
    var content = $("#overtime-content").val();
    var start_time = $("#start-time").val();
    var end_time = $("#end-time").val();
    var start = start_date+" "+start_time;
    var end = end_date+" "+end_time;

    // 验证数据
    var date_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/;
    if(!date_pattern.exec(start) || !date_pattern.exec(end)){
      showHint("提示信息","日期输入格式有误,请检查");
    }else if(start_date > end_date){
      showHint("提示信息","起始时间不能大于结束时间");
    }else if(start_date == end_date && start_time > end_time){
      showHint("提示信息","起始时间不能大于结束时间");
    }else if(content == ""){
      showHint("提示信息","请输入工作内容");
      $("#overtime-content").focus();
    }else{
      $.ajax({
        type:'post',
        url:'/ajax/createHolidayOvertime',
        dataType:'json',
        data:{'start':start, 'end':end, 'content':content},
        success:function(result){
            if(result.code == 0){
              showHint("提示信息","提交加班信息成功");
              setTimeout(function(){location.reload();},1200);
            }else if(result.code == '-1'){
              showHint("提示信息","提交加班信息失败");
            }else if(result.code == '-2'){
              showHint("提示信息","参数错误");
            }else if(result.code == '-3'){
              showHint("提示信息","该加班时间已提交过了");
            }else if(result.code == '-4'){
              showHint("提示信息","发送通知失败，请重试");
            }else{
              showHint("提示信息","你没有权限执行此操作");
            }
          }
      });
    }
  }

  // 工作日周几的计算
  function weekdayCal(){
    var date = $("#weekday-date").val().split(" ")[0];
    var select_day = new Date(date);
    var day = select_day.getDay();
    switch(day){
      case 0:{$("#weekday-weekday").text("周日");break;}
      case 1:{$("#weekday-weekday").text("周一");break;}
      case 2:{$("#weekday-weekday").text("周二");break;}
      case 3:{$("#weekday-weekday").text("周三");break;}
      case 4:{$("#weekday-weekday").text("周四");break;}
      case 5:{$("#weekday-weekday").text("周五");break;}
      case 6:{$("#weekday-weekday").text("周六");break;}
    }
  }

  // 节假日周几的计算
  function holidayCal(){
    var start = $("#holiday-start").val();
    var end = $("#holiday-end").val();
    var start_day = new Date(start).getDay();
    var end_day = new Date(end).getDay();
    switch(start_day){
      case 0:{$("#holiday-weekday-start").text("周日");break;}
      case 1:{$("#holiday-weekday-start").text("周一");break;}
      case 2:{$("#holiday-weekday-start").text("周二");break;}
      case 3:{$("#holiday-weekday-start").text("周三");break;}
      case 4:{$("#holiday-weekday-start").text("周四");break;}
      case 5:{$("#holiday-weekday-start").text("周五");break;}
      case 6:{$("#holiday-weekday-start").text("周六");break;}
    }
    switch(end_day){
      case 0:{$("#holiday-weekday-end").text("周日");break;}
      case 1:{$("#holiday-weekday-end").text("周一");break;}
      case 2:{$("#holiday-weekday-end").text("周二");break;}
      case 3:{$("#holiday-weekday-end").text("周三");break;}
      case 4:{$("#holiday-weekday-end").text("周四");break;}
      case 5:{$("#holiday-weekday-end").text("周五");break;}
      case 6:{$("#holiday-weekday-end").text("周六");break;}
    }
  }

  // 工作日检测
  function weekdayCheck(){
    var tag = true;
    var work_tag = false;  // 工作日标记
    var date = $("#weekday-date").val().split(" ")[0];

    // 判断是否为节假日
    $.each(rest_day_arr, function(){
      if(this['date'] == date){
        if(this['type'] == "rest" || this['type'] == "legal"){  // 休息日或者是法定节假日
          tag = false;
        }else if(this['type'] == "work"){  // 调休的工作日
          tag = true;
          work_tag = true;
        }
        return false;
      }
    });

    // 如果不是因放假调休的工作日，判断是否为周末，是周末则不是工作日
    if(!work_tag){
      var select_date = new Date(date);
      var weekday = select_date.getDay();
      if(weekday <= 0 || weekday >= 6) tag = false;
    }

    tag = true; // 目前为了工作日开放所有日期，先这么改

    // 如果是工作日就可用，否则不可用
    if(tag){
      $("#weekday-overtime-btn").removeClass("disabled");
    }else{
      $("#weekday-overtime-btn").addClass("disabled");
    }
  }

  // 判断起始和结束中间是否包含有工作日
  function workdayCheck(){
    // 输入的起始日期和结束日期
    var start = $("#holiday-start").val();
    var end = $("#holiday-end").val();
    var start_day = new Date(start);
    var end_day = new Date(end);

    var work_tag = false; // 工作日标记

    // 对起始日期到结束日期之间的日期进行遍历
    for(var i = start_day.getTime();i <= end_day.getTime();i += 86400000){
      // 获取日期字符串
      var date = new Date(i);
      var year = date.getFullYear();
      var month = date.getMonth()+1;
      var day = date.getDate();
      var weekday = date.getDay();
      if(month < 10) month = "0"+month;
      if(day < 10) day = "0"+day;
      var date_str = year+"-"+month+"-"+day;

      var s_tag = false; // 特殊日期标记

      // 判断是否为三种日期中的一种
      $.each(rest_day_arr, function(){
        if(!s_tag && this['date'] == date_str){
          // 判断是否为调休的日期
          if(this['type'] != "work"){
            s_tag = true;
          }else{
            s_tag = true;
            work_tag = true;
          }
        }
      }); 

      // 如果不是三种日期中的一种，则判断是否为周一到周五
      if(!s_tag){
        if(weekday > 0 && weekday < 6){
          work_tag = true;
        }
      }
    }

    // 判断是否包含工作日
    if(work_tag){
      showHint("提示信息","亲，工作日不能申请加班!");
      $("#holiday-end").val(start);
    }
  }

  // 周末及节假检测
  function holidayCheck(){
    var start_tag = false;  // 起始标记
    var start_work_tag = false; // 起始工作日标记
    var end_tag = false;  // 结束标记
    var end_work_tag = false; // 结束工作日标记

    // 输入的起始日期和结束日期
    var start = $("#holiday-start").val();
    var end = $("#holiday-end").val();

    // 判断是否为节假日
    $.each(rest_day_arr, function(){
      // 起始日期的判断
      if(this['date'] == start){
        if(this['type'] == "rest"){
          start_tag = true;
        }else if(this['type'] == "legal"){
          start_tag = true;
        }else if(this['type'] == "work"){
          start_tag = false;
          start_work_tag = true;
        }
      }

      // 结束日期的判断
      if(this['date'] == end){
        if(this['type'] == "rest"){
          end_tag = true;
        }else if(this['type'] == "legal"){
          end_tag = true;
        }else if(this['type'] == "work"){
          end_tag = false;
          end_work_tag = true;
        }
      }
    });

    // 判断是否为周末并且不是调休日
    if(!start_work_tag){
      var select_date = new Date(start);
      var weekday = select_date.getDay();
      if(weekday <= 0 || weekday >= 6) start_tag = true;
    }
    if(!end_work_tag){
      var select_date = new Date(end);
      var weekday = select_date.getDay();
      if(weekday <= 0 || weekday >= 6) end_tag = true;
    }

    // 根据判断结果提示
    if(start_tag && end_tag){
      $("#holiday-overtime-btn").removeClass("disabled");
    }
    if(!start_tag || !end_tag){
      $("#holiday-overtime-btn").addClass("disabled");
    }
  }

  // 时间选择绑定
  function timeCheck(){
    var start = $("#start-time").val();

    // 如果起始时间选择13：30，结束时间就默认选择18：30
    if(start == "13:30"){
      $("#end-time").val("18:30");
      $("#noon-time").addClass("hidden");
    }else{
      $("#noon-time").removeClass("hidden");
    }
  }
</script>
