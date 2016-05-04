<?php
echo "<script type='text/javascript'>";
echo "console.log('leave');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
  <div id="leave-info-div" class="pd20 bor-1-ddd">
    <div class="pb20 pl5">
      <!-- 标题 -->
      <h4 class="mb15">
        <strong>请假情况</strong>
        <button class="btn btn-lg btn-success ml20" onclick="showLeave();">我要请假</button>
      </h4>
      <!-- 请假情况切换标签 -->
      <ul class="nav nav-tabs">
        <li role="presentation" class="active"><a class="pointer" onclick="switchToPersonal(this);">我的请假情况</a></li>
        <li role="presentation"><a class="pointer" onclick="switchToDepartment(this);">部门请假情况</a></li>
      </ul>
      <!-- 我的请假情况表格 -->
      <table class="table table-bordered m0 center" id="personal-table">
        <thead>
          <tr>
            <th class="center">剩余补休(天)</th>
            <th class="center">剩余年假(天)</th>
            <th class="center">本月已请假(天)</th>
            <th class="center">总共请假(天)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <!-- 待修改 -->
            <td><?php echo empty($compensatTime) ? '0' : $compensatTime;?></td>
            <td><?php echo empty($annual_days) ? '0' : (float)$annual_days;?></td>
            <td><?php echo empty($monthleaveTotal) ? '0' : $monthleaveTotal;?></td>
            <td><?php echo empty($leaveTotal) ? '0' : $leaveTotal;?></td>
          </tr>
        </tbody>
      </table>
      <!-- 部门请假情况表格 -->
      <table class="table table-bordered hidden m0 center" id="department-table">
        <caption class="hidden m0 p00" id="department-none">
          <h4 class="center m0 pd20 bor-1-ddd black">没有部门请假信息</h4>
        </caption>
        <caption class="bor-l-1-ddd bor-r-1-ddd m0 p00" id="department-caption">
          <h4 class="m0 pd10 center">
            <button class="btn btn-default" onclick="prevDepartment();">
              <span class="glyphicon glyphicon-chevron-left"></span>
            </button>
            <span id="department-date"></span>
            <button class="btn btn-default" onclick="nextDepartment();">
              <span class="glyphicon glyphicon-chevron-right"></span>
            </button>
          </h4>
          <h4 class="p00 m0 pd20 black center bor-b-1-ddd hidden" id="department-search-none">没有请假信息</h4>
        </caption>
        <thead></thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- 历史请假记录 -->
    <div class="center" id="history-div">
      <!-- 标题 -->
      <h4 class="mt20 ml5 left">
        <strong>历史请假记录</strong>
      </h4>
      <!-- 如果有请假记录，则显示 -->
      <?php if(!empty($leaveRecords)): ?>
      <table class="bor-1-ddd m0 table center table-hover">
        <thead>
          <tr class="bg-fa">
            <th class="w80 center">状态</th>
            <th class="w150 center">类型</th>
            <th class="w150 center">天数</th>
            <th class="center">内容</th>
            <th class="w150 center">申请日期</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($leaveRecords as $row):?>
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
            <td><?php echo $row['cntype'];?></td>
            <td><?php echo $row->total_days;?></td>
            <td>
              <a title="查看详情"  href="/user/msg/leave/<?php echo $row->leave_id; ?>">
              你请了从 <?php echo date('Y-m-d H:i',strtotime($row->start_time));?> 到 <?php echo date('Y-m-d H:i',strtotime($row->end_time));?> 共 <?php echo $row->total_days;?> 天的<?php echo $row['cntype'];?>
              </a>
            </td>
            <td><?php echo date('Y-m-d',strtotime($row['create_time']));?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <!-- 如果没有请假记录，则显示“你还没有请过假” -->
      <?php else: ?>
      <h4 class="bor-1-ddd pd20 center">你还没有请过假</h4>
      <?php endif; ?>

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
    <!-- 历史请假记录END -->
  </div>
  
  <!-- 我要请假div -->
  <div style="display:none;" class="center bor-1-ddd"  id="add-leave-div">
    <!-- 返回按钮 -->
    <div class="left mb15">
      <button class="btn btn-default ml10 mt10 f18px" onclick="hideLeave();"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回</button>
    </div>
    <div class="pb20">
      <table class="table f15px table-bordered w800 m0a">
        <caption class="p00">
          <h3 class="center black bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd m0 pd20">请假单</h3>
        </caption>
        <tr>
          <?php if(!empty($user)): ?>
          <th class="w100 center bg-fa">姓名</th>
          <td id="name" class="w200 pl20"><?php echo "{$user->cn_name}";?></td>
          <th class="w100 center bg-fa">部门</th>
          <td id="department" class="w200 pl20"><?php echo "{$user->department->name}"; ?></td>
          <th class="w100 center bg-fa">岗位</th>
          <td id="title" class="w200 pl20"><?php echo "{$user->title}"; ?></td>
          <?php endif; ?>
        </tr>
        <tr>
          <th class="w100 center bg-fa">请假类型</th>
          <td class="w200 pl20 left" colspan="5">
            <button class="btn-success btn" onclick="showRemind(this);setLeaveType(this);" name="casual">事假</button>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="sick">病假</button>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="funeral">丧假</button>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="marriage">婚假</button>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="maternity">产假</button>
            <?php if(!empty($annual_days) && $annual_days > 0): ?>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="annual">年假</button>
            <?php endif; ?>
            <?php if(!empty($compensatTime) && $compensatTime > 0): ?>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="compensatory">补休</button>
            <?php endif; ?>
            <button class="btn-default btn" onclick="showRemind(this);setLeaveType(this);" name="others">其他假</button>
          </td>
        </tr>
        <tr>
          <th class="w100 center bg-fa">说明</th>
          <td id="leaveRemindText" class="left" colspan="5">
            事假：</br>1、员工因私人原因请假的，视为事假。</br>2、事假工资的计算方法：</br>当月工资总额/当月应出勤天数*请假天数</br>
          </td>
        </tr>
        <tr>
          <th class="w100 center bg-fa">请假时间</th>
          <td class="w800 pl20 left"  colspan="5">
            <input type="text" id="start_date" class="form-control bg-white w130 inline pointer" style="cursor:pointer;" id="inputEmail3" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"  onchange="showDelay();calculateDay();" placeholder="开始日期">
            <select id="start_time" class="form-control w100 inline" onchange="showDelay();calculateDay();">
              <option value="09:30">09:30</option>
              <option value="13:30">13:30</option>
            </select>
              &nbsp;到&nbsp;
            <input type="text" id="end_date" class="form-control bg-white w130 inline pointer" style="cursor:pointer;" id="inputEmail3" value="<?php echo date('Y-m-d', strtotime('+2 day')); ?>"  onchange="calculateDay();" placeholder="结束日期">
            <select id="end_time" class="form-control w100 inline" onchange="calculateDay();">
              <option value="12:00">12:00</option>
              <option value="18:30">18:30</option>
            </select>
            <label class="inline ml20" id="count-day"></label>
          </td>
        </tr>
        <tr>
          <th class="w100 center bg-fa">请假事由</th>
          <td class="w200 pl20" colspan="5"><textarea class="form-control" id="content" rows="3"></textarea></td>
        </tr>
        <tr id="delay-tr" class="hidden">
          <th class="w100 center bg-fa">延迟提交理由</th>
          <td class="w200 pl20" colspan="5"><textarea class="form-control" id="delay" rows="3"></textarea></td>
        </tr>
        <tr id="attachment-tr" class="hidden">
          <th class="w100 center bg-fa">附件</th>
          <td colspan="5" id="attanchment-td">
            <input type="file" id="attachment" onchange="preImg();">
          </td>
        </tr>
        <tr id="preImg-tr" class="hidden">
           <th class="w100 center bg-fa">附件预览</th>
           <td colspan="5" class="left">
            <img class="w400 hidden" id="prevImg-img" />
           </td>
        </tr>
      </table>
    </div>
    <button type="button" id="submit" class="btn btn-success w100 btn-lg mb15">提交</button>
  </div>
</div>


<!-- js -->
<script type="text/javascript">
/*----------------------------------------------------部门请假情况------------------------------------------------------------*/
  
  // 加载部门请假情况
  function loadDepartmentLeave(data, start, end){
    // 清空表格数据
    $("#department-table").find("thead").children().remove();
    $("#department-table").find("tbody").children().remove();

    // 获取基础数据
    var start_date = new Date(start);
    var end_date = new Date(end);
    var days = end_date.getDate() - start_date.getDate();
    var month = start_date.getMonth()+1;

    // 加载标题
    var title_str = month+"月"+start_date.getDate()+"号 - "+month+"月"+end_date.getDate()+"号";
    $("#department-date").text(title_str);

    // 加载表头
    var thead_str = "<tr><th class='center w130'>姓名</th><th class='center w130'>职位</th>";     
    for(var i = 0;i <= days;i++){
      var str = month + "月" + (start_date.getDate()+i) + "号";
      thead_str += "<th class='center w130'>"+str+"</th>";
    }

    // 如果有不足七天，就补全
    var minus_day = 6-days;
    if(minus_day != 0){   
      for(var j = 0;j<minus_day;j++){
        thead_str += "<th class='center w130'>&nbsp;</th>";
      }
    }
    thead_str += "</tr>";
    $("#department-table").find("thead").append(thead_str);

    // 加载表格，遍历部门请假数组
    $.each(data, function(key,value){
      var tbody_str = "<tr><td>"+value['name']+"</td><td>"+value['title']+"</td>";
      for(var i = 0;i <= days;i++){
        var str = "";
        // 遍历请假日期数组
        $.each(value['list'], function(key, value){
          var current_date = new Date(key);
          var tmp = current_date.getDate() - start_date.getDate();
          // 判断是否为当前输出日期
          if(tmp == i){
            // 类型转换
            switch(value){
              case "casual":{str = "事假";break;}
              case "sick":{str = "病假";break;}
              case "compensatory":{str = "补休";break;}
              case "annual":{str = "年假";break;}
              case "marriage":{str = "婚假";break;}
              case "maternity":{str = "产假";break;}
              case "others":{str = "其他假";break;}
              case "funeral":{str = "丧假";break;}
            }
            return false;
          }
        });
        tbody_str += "<td>"+str+"</td>";
      }

      // 如果有不足七天，就补全
      if(minus_day != 0){   
        for(var j = 0;j<minus_day;j++){
          tbody_str += "<td>&nbsp;</td>";
        }
      }
      tbody_str += "</tr>";
      $("#department-table").find("tbody").append(tbody_str);
    });
  }

  // 获取部门请假情况
  function getDepartmentLeave(start,end){
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/getDepartmentLeaveInfo',
      data:{'start':start,'end':end},
      success:function(result){
        if(result.code == 0){
          // 加载部门请假情况
          loadDepartmentLeave(result.data, start, end);
          $("#department-search-none").addClass("hidden");
        }else if(result.code == -1){
          // 隐藏部门请假表格
          $("#department-search-none").removeClass("hidden");
          $("#department-table").find("thead").children().remove();
          $("#department-table").find("tbody").children().remove();

          // 加载部门请假情况标题
          var start_date = new Date(start);
          var end_date = new Date(end);
          var month = start_date.getMonth()+1;
          var title_str = month+"月"+start_date.getDate()+"号 - "+month+"月"+end_date.getDate()+"号";
          $("#department-date").text(title_str);
        }else if(result.code == -2){
          // 隐藏部门请假表格
          $("#department-search-none").removeClass("hidden");
          $("#department-table").find("thead").children().remove();
          $("#department-table").find("tbody").children().remove();

          // 加载部门请假情况标题
          var start_date = new Date(start);
          var end_date = new Date(end);
          var month = start_date.getMonth()+1;
          var title_str = month+"月"+start_date.getDate()+"号 - "+month+"月"+end_date.getDate()+"号";
          $("#department-date").text(title_str);
        }
      }
    });
  }

  // 划分周,用于获取部门请假情况
  var week_arr = new Array();
  function weekPartition(){
    // 获取日期字符串
    var date = new Date();
    var year = date.getFullYear();
    var month = date.getMonth()+1;
    var month_str = (parseInt(month) < 10) ? "0"+month : month;
    var date_str = year+"-"+month_str+"-";

    // 将前四周的日期填入数组中
    week_arr.push({'start':date_str+"01", 'end':date_str+"07"});
    week_arr.push({'start':date_str+"08", 'end':date_str+"14"});
    week_arr.push({'start':date_str+"15", 'end':date_str+"21"});
    week_arr.push({'start':date_str+"22", 'end':date_str+"28"});

    // 判断最后一天是否大于28(二月会出现28),出现则再添加一次数据
    var last_day = new Date(year,month,0).getDate();
    if(last_day > 28){
      week_arr.push({'start':date_str+"29",'end':date_str+last_day});
    }
  }

  // 前一周期-部门
  function prevDepartment(){
    // 获取当前查看的年月
    if(current_week == 0){
      showHint("提示信息", "已经是本月第一周");
    }else{
      current_week --;
      getDepartmentLeave(week_arr[current_week]['start'], week_arr[current_week]['end']);
    }
  }

  // 下一周期-部门
  function nextDepartment(){
    // 获取当前查看的日期
    if(current_week == week_arr.length - 1){
      showHint("提示信息", "已经是本月最后一周");
    }else{
      current_week ++;
      getDepartmentLeave(week_arr[current_week]['start'], week_arr[current_week]['end']);
    }
  }

  // 清空并重置上传组件
  function resetAttachment(){
    $("#attanchment-td").children().remove();
    var str = "<input type='file' id='attachment' onchange='preImg();'>";
    $("#attanchment-td").append(str);
    $("#prevImg-img").attr("src", "");
  }

  // 附件预览
  function preImg(){
    // 文件的类型
    var file_type = document.getElementById("attachment").files[0].type;

    // 文件的大小
    var file_size = document.getElementById("attachment").files[0].size;

    // 验证文件
    if(file_type.indexOf("image") < 0){  // 是否为图片
      showHint("提示信息","请选择jpg或png或gif格式的图片");
      $("#prevImg-img").attr("src", "");
      resetAttachment();
    }else if(file_size > 5242880){  // 是否小于5M
      showHint("提示信息","请选择小于5M的照片");
      $("#prevImg-img").attr("src", "");
      resetAttachment();
    }else{
      var reader = new FileReader();
      reader.onload = function(e){
        //给预览图src赋值
        var src = this.result;
        var img = document.getElementById("prevImg-img");
        img.src = src; 
      }
      reader.readAsDataURL(document.getElementById("attachment").files[0]);
      $("#prevImg-img").removeClass("hidden");
    }
  }

/*----------------------------------------------------页面数据初始化------------------------------------------------------------*/

  // 节假日数组初始化
  var rest_day_arr = new Array();
  <?php 
    if(!empty($holidays)){
      foreach($holidays as $hrow){
        echo "rest_day_arr.push({'date':'{$hrow['holiday']}', 'type':'{$hrow['status']}','comment':'{$hrow['comment']}'});";
      }
    }
  ?>

  // 切换到个人请假信息
  function switchToPersonal(obj){
    $(obj).parent().addClass("active");
    $(obj).parent().next().removeClass("active");
    $("#personal-table").removeClass("hidden");
    $("#department-table").addClass("hidden");
    $("#history-div").removeClass("hidden");
  }

  // 切换到部门请假信息
  function switchToDepartment(obj){
    $(obj).parent().addClass("active");
    $(obj).parent().prev().removeClass("active");
    $("#personal-table").addClass("hidden");
    $("#department-table").removeClass("hidden");
    $("#history-div").addClass("hidden");
  }

  // 页面初始化
  var type   = "";
  var start  = "";
  var end  = "";
  var content = "";
  var delay = "";
  var annual_days = "<?php echo empty($annual_days) ? 0 : (float)$annual_days; ?>";
  var compensatTime = "<?php echo empty($compensatTime) ? 0 : $compensatTime; ?>";
  var current_week = "";
  $(document).ready(function(){
    // 划分周
    weekPartition();

    // 获取部门请假情况
    getDepartmentLeave(week_arr['0']['start'], week_arr['0']['end']);

    // 日期选择控件初始化
    $('#start_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $('#end_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#end_time").val("18:30");
    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);

    // 设置日期选择器选择周一到周五
    $.datepicker.regional['zh-CN'].week_special_tag = true;
    $.datepicker.regional['zh-CN'].rest_special_tag = true;
    $.datepicker.regional['zh-CN'].rest_day = rest_day_arr;

    // 计算请假日期
    calculateDay();

    var user_pattern = /^\d+$/;
    var types = ['casual','sick','funeral','marriage','maternity','annual','compensatory','others'];
    var date_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/;
    
    // 获取部门请假信息
    $.each(week_arr, function(key,value){
      var start_time = new Date(this['start']).getTime();
      var end_time = new Date(this['end']).getTime();
      var date = new Date().getTime();

      // 判断当前属于哪一周
      if(start_time <= date && end_time >= date){
        current_week = key;
        getDepartmentLeave(this['start'], this['end']);
        return false;
      }
    });

    // 提交按钮的操作
    $("#submit").click(function(){
      // 获取数据
      type  = leave_type;
      var type_name = "";
      type_name = $("button[name='"+type+"']").text();
      start  = $("#start_date").val()+" "+$("#start_time").val();
      end  = $("#end_date").val()+" "+$("#end_time").val();
      content = $("#content").val();
      delay = $("#delay").val();
      var count_day = $("#count-day").text().split("共")[1].split("天")[0];

      // 验证数据
      if(!$("#delay-tr").hasClass("hidden") && delay == ""){
        showHint("提示信息","请填写延迟提交原因！");
        $("#delay").focus();
      }else if($.inArray(type , types) == -1){
        showHint("提示信息","重新选择类型");
      }else if(!date_pattern.exec(start)){
          showHint("提示信息","重新填写开始时间");
      }else if(!date_pattern.exec(end)){
          showHint("提示信息","重新填写结束时间");
      }else if(start >= end){
          showHint("提示信息","两个时间有问题，请重新填写时间");
      }else if(content.length == 0){
          showHint("提示信息","请填写请假事由");
          $("#content").focus();
      }else if(calculate_str == ""){
        showHint("提示信息","总天数为0天，不需要请假！");
      }else if(parseFloat(annual_days) < parseFloat(count_day) && type == "annual"){
        showHint("提示信息","你的年假剩余"+annual_days+"天，请重新选择！");
      }else if(parseFloat(compensatTime) < parseFloat(count_day) && type == "compensatory"){
        showHint("提示信息","你的补休剩余"+compensatTime+"天，请重新选择！");
      }else{
        // 二次提醒
        var str = "确定 从 "+start+" 到 "+end+" 请 "+$("#count-day").text()+type_name+"?";
        showConfirm("提交请假申请",str,"确定","sendLeave()","取消");
      }
    });

    //默认显示补休，如果用户没有补休天数则将默认请假类型置为事假
    if($('button[name=compensatory]')){
      $('button[name=compensatory]').click();
    }

  });
  
/*-----------------------------------------------------我要请假的操作---------------------------------------*/

  // 显示请假申请的div
  function showLeave(){
    $("#leave-info-div").fadeOut(500,function(){
      $("#add-leave-div").slideDown(500);
    });
  }

  // 隐藏请假申请的div
  function hideLeave(){

    $("#add-leave-div").fadeOut(500,function(){
      $("#leave-info-div").slideDown(500);
    });
  }

  // 设置请假类型
  var leave_type = "casual";
  function setLeaveType(obj){
    $(obj).parent().children().removeClass("btn-success").addClass("btn-default");
    $(obj).addClass("btn-success").removeClass("btn-default");
    leave_type = $(obj).attr("name");

    //婚假、产假可上传附件
    if(leave_type == "marriage" || leave_type == "maternity"){
      $("#preImg-tr").removeClass("hidden");
      $("#attachment-tr").removeClass("hidden");
    }else{
      $("#preImg-tr").addClass("hidden");
      $("#attachment-tr").addClass("hidden");
      resetAttachment();
    }
  }


  // 显示请假类型提示
  function showRemind(obj){
    var type = $(obj).attr("name");
    var str = "";
    switch(type){
      case "casual":{
        str = "事假：</br>1、员工因私人原因请假的，视为事假。</br>2、事假工资的计算方法：</br>当月工资总额/当月应出勤天数*请假天数</br>";
        break;
      }
      case "sick":{
        str = "病假：</br>1、员工患病或非因工负伤需治疗的，视为病假。</br>2、病假须于返回公司当天向公司提交医生出具的诊断证明书、病假单以及医药费用凭证，如无法提供的，则按事假处理；</br>3、员工每月可享受一天带薪病假。</br>";
        break;
      }
      case "funeral":{
        str = "丧假：</br>1、员工直系亲属（配偶、父母、子女）身故，公司给予丧假3天，省外路程假2天，省内路程假1天。</br>2、祖（外）父母、兄弟姐妹、配偶父母死亡的，公司给予丧假1天，省外路程假2天，省内路程假1天。</br>";
        break;
      }
      case "marriage":{
        str = "婚假：</br>员工符合国家规定结婚条件的（男性满22周岁、女性满20周岁），可享受国家规定带薪婚假。婚假规定如下：</br>1、法定婚假为3天。</br>2、如员工满足晚婚条件的（男年满25周岁，女年满23周岁），增加晚婚假10天。</br>3、婚假不含法定节假日，如遇法定节假日则顺延，婚假需一次休完。</br>4、员工休婚假应持结婚证原件及复印件提前10天以书面形式申请，获准后方可休假。</br>5、婚假以员工结婚证日期为准，一年内有效，逾期视为自动放弃休婚假权利。</br>";
        break;
      }
      case "maternity":{
        str = "产假：</br>1、女员工申请产假，须持《准生证》和《计划生育服务证》提前一个月以书面形式申请；</br>2、女职工生育享受98天产假，其中产前可以休假15天；难产的，增加产假15天；生育多胞胎的，每多生育1个婴儿，增加产假15天。实行晚育者（24周岁后生育第一胎）增加产假15天，领取《独生子女优待证》者（生育一胎但生的是多胞胎，不属于独生子女），增加产假35天。</br>3、女职工怀孕未满4个月流产的，享受15天产假；怀孕满4个月流产的，享受42天产假；仅限于领取《同意生育通知书》或《生育证》的流产假。</br>4、产假期满后15天内须向人事行政部提交《婴儿出生证明》复印件；</br>5、男员工陪产假为3天，已领取《独生子女父母光荣证》的，陪产假为10天（含公休假日）；男员工陪产假期满，需在7天内向人事行政部提交《婴儿出生证明》复印件办理补假手续，否则按事假处理；</br>6、产假均已包括休息日和法定假日（不论是正产或小产）在内，故不再补假。</br>";
        break;
      }
      case "annual":{
        str = "年假：</br>1、在公司工作满一年的员工均可享受带薪年假。</br>2、员工年假标准：</br>（1）工作满一年未满三年的年假为7天；</br>（2）工作满三年不满五年的年假为10天；</br>（3）工作五年以上的年假为15天。</br>3、年假的有关规定</br>（1）部门负责人在合理安排员工工作，总体不影响本部门、岗位工作的前提下，有计划地安排好员工年假；</br>（2）每年的年假须于一年内休完，不得累计至下一年度。如因工作需要不能安排休假的，可报请总经理获得有效期延长的批准，最多延期一年</br>（3）年假可一次休完，也可分次休完，每次休假最小单位不少于半天；</br>（4）年假不包括公休假日及法定假日。</br>4、员工存在以下情况则不享有当年年假：</br>（1）一年内事假累计超过15天；</br>（2）一年内病假累计超过30天。</br>";
        break;
      }
      case "compensatory":{
        str = "加班补休：</br>1、在上班时间内未能完成的本职工作，由本人自行完成，公司不作加班计算；</br>2、加班须有考勤记录，加班按照以下规定执行：</br>（1）周六、周日加班：按1：1予以补休；</br>（3）法定节假日加班：按1：3予以补休或支付加班费。</br>";
        break;
      }
      case "others":{
        str = "其他假：以上没有提及到的特殊的假期，应向人事申报";
        break;
      }
    }
    $("#leaveRemindText").html(str);
  }

  // 发送请假申请
  var lock = false;
  function sendLeave(){
    // 用锁来判断是否可以提交
    if(lock == false){
      lock = true;
      var fileObj = document.getElementById("attachment").files[0]; // 获取文件对象
      var FileController = "/ajax/addLeave";                    // 接收上传文件的后台地址 
      // FormData 对象
      var form = new FormData();
      form.append("type", type);
      form.append("start_time", start);
      form.append("end_time", end);
      form.append("content", content);
      form.append("delay", delay);                          

      // 如果有附件就上传
      if(typeof(document.getElementById("attachment").files[0]) != "undefined"){
        form.append("file", fileObj);
      }

      // XMLHttpRequest 对象
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
            var url = xmlDoc.getElementsByTagName("url")[0].childNodes[0].nodeValue;
          }catch(e){
            showHint("提示信息","解析返回信息失败，请重试");
          }
          // 回调提示
          if(code == 0){
            showHint("提示信息","请假申请提交成功，待审批");
            setTimeout(function(){location.href = url;},1200);
          }else if(code == -1){
              showHint("提示信息","请假申请提交失败！");
          }else if(code == -2){
              showHint("提示信息","请输入请假理由");
          }else if(code == -3){
              showHint("提示信息","请输入正确时间！");
          }else if(code == -4){
              showHint("提示信息","起始时间不能大于结束时间！");
          }else if(code == -5){
              showHint("提示信息","请输入延迟提交原因！");
          }else if(code == -6){
              showHint("提示信息","没有超时，不能输入延迟提交原因！");
          }else if(code == -7){
              showHint("提示信息","图片不能超过5M！");
          }else if(code == -8){
              showHint("提示信息","请提交jpg、png或gif格式的图片！");
          }else if(code == -9){
              showHint("提示信息","图片上传失败！");
          }else if(code == -10){
              showHint("提示信息","该请假时间已提交过了！");
          }else{
              showHint("提示信息","系统错误，请联系管理员");
          }
          lock = false;
        }
      }
    }
  }

  // 显示延迟提交原因
  function showDelay(){
    var start_date  = $("#start_date").val();
    var start_time = $("#start_time").val();

    var date_str = getCurentDate();
    var time_str = getCurentTime();

    // 获取当前时间
    function getCurentTime(){   
      var now = new Date();    
      var hh = now.getHours(); //时
      var mm = (now.getMinutes()) % 60;  //分
      if ((now.getMinutes()) / 60 > 1) {
          hh += Math.floor((now.getMinutes()) / 60);
      }
      var clock = "";
      if(hh < 10)   clock += "0";   
      clock += hh + ":";   
      if (mm < 10) clock += '0';   
      clock += mm;   
      return(clock);
    }

    // 获取当前日期
    function getCurentDate(){
      var now = new Date();    
      var year = now.getFullYear();       //年   
      var month = now.getMonth() + 1;     //月   
      var day = now.getDate();            //日

      var date = year + "-";
      if(month < 10)   date += "0";   
      date += month + "-";   
      if(day < 10)   date += "0";
      date += day;
      return (date);
    }

    //如果起始时间在当前时间之后，则显示延迟提交原因
    if(date_str > start_date){
      $("#delay-tr").removeClass("hidden");
    }else if(date_str == start_date){
      if(time_str > start_time){
        $("#delay-tr").removeClass("hidden");
      }else{
        $("#delay-tr").addClass("hidden");
      }
    }else{
      $("#delay-tr").addClass("hidden");
    }
  }

  // 计算天数
  var calculate_str = "";
  function calculateDay(){
    // 获取起始日期和结束日期
    start  = $("#start_date").val()+" "+$("#start_time").val();
    end  = $("#end_date").val()+" "+$("#end_time").val();
    // 发送数据
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/countdays',
      data:{'start':start,'end':end},
      success:function(result){
        if(result.code == 0){
          calculate_str = result.count;
          var str = "共 "+result.count+" 天";
          $("#count-day").text(str);
          $("#submit").removeClass("disabled");
        }else if(result.code == -1){
          calculate_str = "";
          $("#count-day").text("共 0 天");
        }else if(result.code == -2){
          calculate_str = "";
          $("#count-day").text("共 0 天");
        }else if(result.code == -3){
          calculate_str = "";
          $("#count-day").text("共 0 天");
        }else if(result.code == -4){
          calculate_str = "";
          $("#count-day").text("共 0 天");
          $("#submit").addClass("disabled");
        }
      }
    });
  }
</script>
