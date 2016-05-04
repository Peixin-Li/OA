<?php
echo "<script type='text/javascript'>";
echo "console.log('businessTrip');";
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
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui-timepicker-addon.css" />

<!-- 主界面 -->
<div>
    <div id="businesstrip-info-div" class="pd20 bor-1-ddd">
    <div class="pb20 pl5">
      <!-- 标题 -->
      <h4 class="mb15">
        <strong>出差情况</strong>
        <button class="btn btn-success btn-lg ml20" onclick="showBusiness();">我要出差</button>
      </h4>
      <table class="table table-bordered m0 center">
        <tr>
          <th class="center">本月已出差(天)</th>
          <th class="center">今年已出差(天)</th>
        </tr>
        <tr>
          <td><?php echo empty($month_days)? "0": $month_days;?></td>
          <td><?php echo empty($days)? "0": $days;?></td>
        </tr>
      </table>
    </div>
    <!-- 历史出差记录 -->
    <div class="center">
      <!-- 标题 -->
      <h4 class="mt20 ml5 left">
        <strong>历史出差记录</strong>
      </h4>
      <?php if(!empty($outRecords)): ?>
      <table class="bor-1-ddd m0 table center table-hover">
        <thead>
          <tr class="bg-fa">
            <th class="w80 center">状态</th>
            <th class="w150 center">类型</th>
            <th class="center">内容</th>
            <th class="center">同行人</th>
            <th class="w150 center">申请日期</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($outRecords as $row):?>
          <tr>
            <td>
              <?php
                // 输出状态
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
              <?php
                // 输出会议类型
                if($row['type'] == "meeting"){
                  echo "会议";
                }else if($row['type'] == "business"){
                  echo "商务洽谈";
                }else if($row['type'] == "out"){
                  echo "市内外出";
                }else if($row['type'] == "recruit"){
                  echo "校园招聘";
                }
              ?>
            </td>
            <td>
              <!-- 链接 -->
              <a href="/user/outMsg/out/<?php echo $row->out_id;?>">
                <?php if($row['type'] == "out"):?>
                你申请外出到 <?php echo $row['place'];?> 办事
                <?php else: ?>
                你申请到 <?php echo $row['place'];?> 出差 <?php echo $row['total_days']?> 天
                <?php endif; ?>
              </a>
            </td>
            <td>
              <!-- 同行人 -->
              <?php if(!empty($row->members) && count($row->members) > 1): ?>
              <?php foreach($row->members as $mrow): ?>
              <span class="mr10"><?php echo $mrow->user->cn_name; ?></span>
              <?php endforeach; ?>
              <?php else: ?>
              无
              <?php endif; ?>
            </td>
            <td><?php echo date('Y-m-d',strtotime($row['create_time']));?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <h4 class="bor-1-ddd pd20 center">你还没有出过差</h4>
      <?php endif; ?>
      <!-- 分页 -->
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
  <!-- 我要出差 -->
  <div id="businesstrip-div" style="display:none;" class="center bor-1-ddd">
    <!-- 返回按钮 -->
    <div class="left mb15">
      <button class="btn btn-default ml10 mt10 f18px" onclick="hideBusiness();">
        <span class="glyphicon glyphicon-chevron-left"></span>&nbsp;返回
      </button>
    </div>
    <div class="mb15 pb20 pl50 pr50">
      <caption class="p00">
        <h3 class="center black bor-t-1-ddd bor-l-1-ddd bor-r-1-ddd m0 pd20">出差申请单</h3>
      </caption>
      <table class="table table-bordered m0a">
        <tr>
          <?php if(!empty($user)):?>
          <th class="w130 center bg-fa">姓名</th>
          <td id="name"><?php echo "{$user->cn_name}"; ?></td>
          <th class="w130 center bg-fa">部门</th>
          <td id="department" ><?php echo "{$user->department->name}"; ?></td>
          <th class="w130 center bg-fa">职位</th>
          <td id="title" ><?php echo "{$user->title}"; ?></td>
          <?php endif; ?>
        </tr>
        <tr>
          <th class="w130 center bg-fa">出差类型</th>
          <td colspan="5" class="left">
              <input type="radio" value="business" name="type" checked onclick="typeChange(this);"><span class="pointer" onclick="$(this).prev().click();">商务洽谈</span>&nbsp;&nbsp;
              <input type="radio" value="meeting" name="type" onclick="typeChange(this);"><span class="pointer" onclick="$(this).prev().click();">会议</span>&nbsp;&nbsp;
              <input type="radio" value="recruit" name="type" onclick="typeChange(this);"><span class="pointer" onclick="$(this).prev().click();">校园招聘</span>&nbsp;&nbsp;
              <input type="radio" value="out" name="type" onclick="typeChange(this);"><span class="pointer" onclick="$(this).prev().click();">市内外出</span>&nbsp;&nbsp;
          </td>
        </tr>
        <tr>
          <th class="center w130 bg-fa" id="type-th">公司名称</th>
          <td colspan="5"  class="left"><input class="form-control w500" id="company" placeHolder="请填写对方公司名称"></td>
        </tr>
        <tr>
          <th class="w130 center bg-fa">出差地点</th>
          <td colspan="5"  class="left">
            <select id="province" onchange="getRegion();" class="form-control inline w100"></select>
            <select id="city" class="form-control inline w100"><option>---市---</option></select>
            <input type="text" class="form-control w400 inline" id="detail-address" placeholder="详细地址">
          </td>
        </tr>
        <tr id="transport-tr">
          <th class="w130 center bg-fa">交通工具</th>
          <td colspan="5" class="left">
            <input type="checkbox" value="飞机" name="transport" checked><span class="pointer" onclick="$(this).prev().click();">飞机</span>&nbsp;&nbsp;
            <input type="checkbox" value="软卧" name="transport" checked><span class="pointer" onclick="$(this).prev().click();">软卧</span>&nbsp;&nbsp;
            <input type="checkbox" value="硬卧" name="transport" checked><span class="pointer" onclick="$(this).prev().click();">硬卧</span>&nbsp;&nbsp;
            <input type="checkbox" value="高铁" name="transport"><span class="pointer" onclick="$(this).prev().click();">高铁</span>&nbsp;&nbsp;
            <input type="checkbox" value="轮船" name="transport"><span class="pointer" onclick="$(this).prev().click();">轮船</span>&nbsp;&nbsp;
            <input type="checkbox" value="汽车" name="transport"><span class="pointer" onclick="$(this).prev().click();">汽车</span>&nbsp;&nbsp;
            <input type="checkbox" value="其他" name="transport"><span class="pointer" onclick="$(this).prev().click();">其他</span>&nbsp;&nbsp;
          </td>
        </tr>
        <tr id="date-tr">
          <th class="w130 center bg-fa">出差日期</th>
          <td colspan="5"  class="left">
            <input type="text" id="start_date" class="form-control bg-white w130 inline pointer" style="cursor:pointer;" id="inputEmail3" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="showDelay();calculateDay();" placeholder="开始日期">
            <select id="start_time" class="form-control inline w100" onchange="showDelay();calculateDay();">
              <option value="09:30">09:30</option>
              <option value="13:30">13:30</option>
            </select>
              &nbsp;到&nbsp;
            <input type="text" id="end_date" class="form-control bg-white w130 inline pointer" style="cursor:pointer;" id="inputEmail3" value="<?php echo date('Y-m-d', strtotime('+2 day')); ?>"  onchange="calculateDay();" placeholder="结束日期">
            <select id="end_time" class="form-control inline w100 mr20" onchange="calculateDay();">
              <option value="12:00">12:00</option>
              <option value="18:30" checked>18:30</option>
            </select>
            <input type="radio" name="date-type-select" value="normal" onclick="setNormal();" checked><span class="pointer" onclick="$(this).prev().click();">正常</span>&nbsp;&nbsp;
            <input type="radio" name="date-type-select" value="morning" onclick="setMorning();"><span class="pointer" onclick="$(this).prev().click();">仅上午(09:30-12:00)</span>&nbsp;&nbsp;
            <input type="radio" name="date-type-select" value="afternoon" onclick="setAfternoon();"><span class="pointer" onclick="$(this).prev().click();">仅下午(13:30-18:30)</span>&nbsp;&nbsp;
            <label class='ml20 inline' id="count-day"></label>
          </td>
        </tr>
        <tr>
          <th class="w130 center bg-fa">同行人员</th>
          <td colspan="5" class="left">
            <div id="menber-div" class="inline-block"></div>
            <a class="pointer" onclick="showAddMember();" id="show-addmember-btn"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;添加同行人员</a>
            <div id="add-member-div" class="inline-block hidden">
              <input class="form-control inline w200" id="new-member-input" placeholder="请输入中文名">
              <a class="pointer" onclick="addMember();">确定</a>
              <a class="pointer" onclick="cancelAddMember();">取消</a>
            </div>
          </td>
        </tr>
        <tr id="cost-tr">
          <th class="w130 center bg-fa">预计费用</th>
          <td colspan="5" class="left"><input class="w100 form-control w100 inline" id="cost" placeholder="请输入费用">&nbsp;元</td>
        </tr>
        <tr id="plan-tr">
          <th class="w130 center bg-fa">行程说明</th>
          <td colspan="5"  class="left"><textarea class="form-control" id="plan" rows="3"></textarea></td>
        </tr>
        <tr>
          <th class="w130 center bg-fa">出差事由</th>
          <td colspan="5"  class="left"><textarea class="form-control" id="content" rows="3"></textarea></td>
        </tr>
        <tr id="delay-tr" class="hidden">
          <th class="w100 center bg-fa">延迟提交理由</th>
          <td colspan="5"  class="left"><textarea class="form-control" id="delay" rows="3"></textarea></td>
        </tr>
      </table>
    </div>
    <button type="button" id="submit" class="btn btn-success w100 btn-lg mb15" onClick="businessTrip();">提交</button>
  </div>
</div>

<!-- js -->
<script type="text/javascript">
  // 完成添加同行人员
  function cancelAddMember(){
    $("#add-member-div").addClass("hidden");
    $("#show-addmember-btn").removeClass("hidden");
  }

  // 删除同行人员
  function removeMember(obj){
    var user_id = $(obj).prev().attr("id").split("-")[1];  // 获取id
    var remove_tag = false;
    // 在同行人数组中寻找，如果找到就设置为null
    $.each(member_arr, function(key, value){
      if(value == user_id){
        member_arr[key] = null;
        remove_tag = true;
        return false;
      }
    });
    if(remove_tag){  // 删除成功
      $(obj).parent().remove();
    }else{  // 删除失败
      showHint("提示信息", "删除同行人员失败");
    }
  }

  // 添加同行人员
  var member_arr = new Array();
  function addMember(){
    var user_name = $("#new-member-input").val();  
    var find_tag = false;
    var user_id = "";
    // 在用户数组中寻找添加的人员
    $.each(user_arr, function(){
      if(this["name"] == user_name){
        find_tag = true;
        user_id = this['id'];
        return false;
      }
    });
    if(find_tag){   // 如果找到
      var same_tag = false;
      // 在同行人数组中看是否已经存在
      $.each(member_arr, function(key, value){
        if(value == user_id){
          same_tag = true;
          return false;
        }
      });
      if(!same_tag){  // 不存在，则添加button，并加入到同行人数组中
        var str = "<button class='btn btn-success mr10'><span id='member-"+user_id+"'>"+user_name+"</span>&nbsp;<span class='b2 glyphicon glyphicon-remove' onclick='removeMember(this);' title='删除该同行人员'></span></button>";
        $("#menber-div").append(str);
        member_arr.push(user_id);
        $("#new-member-input").val("");
      }else{  // 已经存在
        showHint("提示信息", "该同行人已存在");
      }
    }else{  // 找不到
      showHint("提示信息", "查找不到此用户");
    }
  }

  // 显示添加同行人员
  function showAddMember(){
    $("#add-member-div").removeClass("hidden");
    $("#show-addmember-btn").addClass("hidden");
  }

  // 选择正常时间
  function setNormal(){
    $("#start_time").removeClass("hidden");
    $("#end_time").removeClass("hidden");
    calculate_type = "normal";
    calculateDay();
  }

  // 选择仅上午
  function setMorning(){
    $("#start_time").val("09:30");
    $("#end_time").val("12:00");
    $("#start_time").addClass("hidden");
    $("#end_time").addClass("hidden");
    calculate_type = "morning";
    calculateDay();
  }

  // 选择仅下午
  function setAfternoon(){
    $("#start_time").val("13:30");
    $("#end_time").val("18:30");
    $("#start_time").addClass("hidden");
    $("#end_time").addClass("hidden");
    calculate_type = "afternoon";
    calculateDay();
  }

  // 出差类型选择
  function typeChange(obj){
    var type = $(obj).val();
    switch(type){
      case "business":{
        $("#type-th").text("公司名称");
        $("#cost-tr").removeClass("hidden");
        $("#transport-tr").removeClass("hidden");
        $("#plan-tr").removeClass("hidden");
        $("#company").attr("placeholder","请输入公司名称");
        break;
      }
      case "meeting":{
        $("#type-th").text("会议名称");
        $("#cost-tr").removeClass("hidden");
        $("#transport-tr").removeClass("hidden");
        $("#plan-tr").removeClass("hidden");
        $("#company").attr("placeholder","请输入会议名称");
        break;
      }
      case "recruit":{
        $("#type-th").text("大学名称");
        $("#cost-tr").removeClass("hidden");
        $("#transport-tr").removeClass("hidden");
        $("#plan-tr").removeClass("hidden");
        $("#company").attr("placeholder","请输入大学名称");
        break;
      }
      case "out":{
        $("#type-th").text("公司名称");
        $("#cost-tr").addClass("hidden");
        $("#transport-tr").addClass("hidden");
        $("#plan-tr").addClass("hidden");
        $("#company").attr("placeholder","请输入公司名称");
        $("#plan").val("");
        $("#cost").val("");
        setOutPlace();
        break;
      }
    }
  }

  // 市内外出设置
  function setOutPlace(){
    $("#province").val("18");
    getRegion();
    $("#city").val("广州");
  }

  // user数组初始化
  var user_arr = new Array();
  var cn_name_arr = new Array();
  <?php 
    if(!empty($users)){
      foreach ($users as $urow) {
        echo "user_arr.push({'id':'{$urow['user_id']}', 'name':'{$urow['cn_name']}'});";
        echo "cn_name_arr.push('{$urow['cn_name']}');";
      }
    }
  ?>

  // 页面初始化
  $(document).ready(function(){
    // 日期控件初始化
    $('#start_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $('#end_date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#end_time").val("18:30");
    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);

    // 获取省份
    getProvince();

    // 计算日期
    calculateDay();

    // 自动匹配
    $("#new-member-input").autocomplete({
      source:cn_name_arr
    });
  });

  // 初始化发送数据
  var cost = "";
  var start_date = "";
  var end_date = "";
  var content = "";
  var type = "";
  var transport = new Array();
  var company = "";
  var address = "";
  var delay = "";
  var plan = "";
  var date_type = "";

  // 确认发送出差信息
  function businessTrip(){
    // 获取数据
    cost = $("#cost").val();
    start_date = $("#start_date").val()+" "+$("#start_time").val();
    end_date = $("#end_date").val()+" "+$("#end_time").val();
    content = $("#content").val();
    type = $('input[name="type"]:checked').val();
    transport = new Array();
    if(type != "out"){
      $('input[name="transport"]:checked').each(function(){
        transport.push($(this).val());
      });
    }   
    company = $("#company").val();
    delay = $("#delay").val();
    plan = $("#plan").val();
    date_type = $("input[name='date-type-select']:checked").val();

    var province_selected = "";
    //地址str的编辑
    $("#province").find("option").each(function(){
      if($(this).val()==$("#province").val()) province_selected = $(this).text();
    });
    if(province_selected=="重庆"){
      address = province_selected +"市"+ $("#detail-address").val();
    }else if(province_selected=="香港"||province_selected=="澳门"){
      address = province_selected+$("#detail-address").val();
    }else if(province_selected=="北京"||province_selected=="天津"||province_selected=="上海"){
      address = province_selected+"市"+$("#city").val()+"区"+$("#detail-address").val();
    }else{
      address = province_selected +"省"+ $("#city").val() +"市"+ $("#detail-address").val();
    }

    // 验证数据
    var d_pattern = /^\d+$/;
    var date_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/;
    if(!d_pattern.exec(cost) && type != "out"){
      showHint("提示信息","差旅费用输入格式错误！");
      $("#cost").focus();
    }else if(!date_pattern.exec(start_date)||!date_pattern.exec(end_date)){
      showHint("提示信息","日期输入格式错误！");
    }else if($("#province").val()=="---省份---"||$("#city").val()==""||$("#detail-address").val()==""){
      showHint("提示信息","请输入正确的地址！");
    }else if($("#company").val()==""){
      showHint("提示信息","请输入对方公司名称！");
      $("#company").focus();
    }else if(transport.length < 1  && type != "out"){
      showHint("提示信息","请选择交通工具！");
    }else if(start_date > end_date){
      showHint("提示信息","出差日期有误，请重新选择！");
    }else if(delay == "" && !$("#delay-tr").hasClass("hidden")){
      showHint("提示信息","请输入延迟提交原因！");
      $("#delay").focus();
    }else if(plan == "" && type != "out"){
      showHint("提示信息","请输入行程计划说明！");
      $("#plan").focus();
    }else if(content == ""){
      showHint("提示信息","请输入出差理由！");
      $("#content").focus();
    }else{
      var str = "确定 从 "+start_date+" 到 "+end_date+" 出差 "+$("#count-day").text()+"?";
      // 二次提示
      showConfirm("提交出差申请",str,"确定","sendBusinessTrip()","取消");
    }
  }

  // 发送出差信息
  function sendBusinessTrip(){
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/AddOut',
      data:{
        'cost':cost , 'company':company , 'transport':transport, 'member':member_arr, 
        'date_type':date_type, 'place':address , 'start_time':start_date,'end_time':end_date, 
        'content':content, 'delay':delay,'plan':plan,'type':type
      },
      success:function(result){
          if(result.code == 0){
              showHint("提示信息","出差申请提交成功，待审批");
              setTimeout(function(){location.href=result.url;},1200);
          }else if(result.code == -1){
              showHint("提示信息","出差申请提交失败！");
          }else if(result.code == -2){
            showHint("提示信息","差旅费用输入错误！");
          }else if(result.code == -5){
              showHint("提示信息","请输入延迟提交原因！");
          }else if(result.code == -7){
            showHint("提示信息","请输入本市地址！");
          }else{
            showHint("提示信息","你没有权限进行此操作！");
          }
      }
    });
  }

  // 初始化城市数组
  var city=new Array(34);
  city[0]=new Array(19); city[0][0]="北京";city[0][1]="东城";city[0][2]="西城";city[0][3]="宣武";city[0][4]="朝阳";city[0][5]="崇文";city[0][6]="海淀";city[0][7]="丰台";city[0][8]="石景山";city[0][9]="通州";city[0][10]="平谷";city[0][11]="顺义";city[0][12]="怀柔";city[0][13]="密云";city[0][14]="延庆";city[0][15]="昌平";city[0][16]="门头沟";city[0][17]="房山";city[0][18]="大兴";
  city[1]=new Array(20);city[1][0]="上海";city[1][1]="近郊";city[1][2]="闵行";city[1][3]="浦东";city[1][4]="南汇";city[1][5]="奉贤";city[1][6]="金山";city[1][7]="松江";city[1][8]="青浦";city[1][9]="嘉定";city[1][10]="宝山";city[1][11]="崇明";city[1][12]="黄埔";city[1][13]="卢湾";city[1][14]="徐汇";city[1][15]="长宁";city[1][16]="静安";city[1][17]="普陀";city[1][18]="闸北";city[1][19]="虹口";city[1][20]="杨浦";
  city[2]=new Array(18);city[2][0]="天津";city[2][1]="塘沽";city[2][2]="汉沽";city[2][3]="宁河";city[2][4]="静海";city[2][5]="武清";city[2][6]="宝坻";city[2][7]="蓟县";city[2][8]="和平";city[2][9]="河东";city[2][10]="河西";city[2][11]="南开";city[2][12]="河北";city[2][13]="红桥";city[2][14]="东丽";city[2][15]="西青";city[2][16]="津南";city[2][17]="北辰";
  city[3]=new Array(1);city[3][0]="重庆";
  city[4]=new Array(14);city[4][0]="内蒙古";city[4][1]="呼和浩特";city[4][2]="集宁";city[4][3]="包头";city[4][4]="临河";city[4][5]="乌海";city[4][6]="东胜";city[4][7]="海拉尔";city[4][8]="赤峰";city[4][9]="锡林浩特";city[4][10]="太仆寺旗";city[4][11]="通辽";city[4][12]="阿拉善盟";city[4][13]="白城";
  city[5]=new Array(12);city[5][0]="河北";city[5][1]="石家庄";city[5][2]="衡水";city[5][3]="邢台";city[5][4]="邯郸";city[5][5]="沧州";city[5][6]="唐山";city[5][7]="廊坊";city[5][8]="秦皇岛";city[5][9]="承德";city[5][10]="保定";city[5][11]="张家口";
  city[6]=new Array(15);city[6][0]="辽宁";city[6][1]="沈阳";city[6][2]="铁岭";city[6][3]="抚顺";city[6][4]="鞍山";city[6][5]="营口";city[6][6]="大连";city[6][7]="本溪";city[6][8]="丹东";city[6][9]="锦州";city[6][10]="朝阳";city[6][11]="阜新";city[6][12]="盘锦";city[6][13]="辽阳";city[6][14]="葫芦岛";
  city[7]=new Array(12);city[7][0]="吉林";city[7][1]="长春";city[7][2]="吉林";city[7][3]="延吉";city[7][4]="通化";city[7][5]="梅河口";city[7][6]="四平";city[7][7]="白城";city[7][8]="松原";city[7][9]="白山";city[7][10]="延边";city[7][11]="辽源";
  city[8]=new Array(15);city[8][0]="黑龙江";city[8][1]="哈尔滨";city[8][2]="绥化";city[8][3]="佳木斯";city[8][4]="牡丹江";city[8][5]="齐齐哈尔";city[8][6]="大庆";city[8][7]="北安";city[8][8]="大兴安岭";city[8][9]="黑河";city[8][10]="七台河";city[8][11]="伊春";city[8][12]="双鸭山";city[8][13]="鹤岗";city[8][14]="鸡西";
  city[9]=new Array(15);city[9][0]="江苏";city[9][1]="南京";city[9][2]="镇江";city[9][3]="常州";city[9][4]="无锡";city[9][5]="苏州";city[9][6]="徐州";city[9][7]="连云港";city[9][8]="淮阴";city[9][9]="盐城";city[9][10]="扬州";city[9][11]="南通";city[9][12]="泰州";city[9][13]="宿迁";city[9][14]="淮安";
  city[10]=new Array(19);city[10][0]="安徽";city[10][1]="合肥";city[10][2]="淮南";city[10][3]="蚌埠";city[10][4]="宿州";city[10][5]="阜阳";city[10][6]="六安";city[10][7]="巢湖";city[10][8]="滁州";city[10][9]="芜湖";city[10][10]="屯溪";city[10][11]="安庆";city[10][12]="马鞍山";city[10][13]="淮北";city[10][14]="铜陵";city[10][15]="黄山";city[10][16]="池州";city[10][17]="毫州";city[10][18]="宣城";
  city[11]=new Array(18);city[11][0]="山东";city[11][1]="济南";city[11][2]="聊城";city[11][3]="德州";city[11][4]="淄博";city[11][5]="东营";city[11][6]="潍坊";city[11][7]="烟台";city[11][8]="青岛";city[11][9]="泰安";city[11][10]="济宁";city[11][11]="荷泽";city[11][12]="临沂";city[11][13]="枣庄";city[11][14]="威海";city[11][15]="日照";city[11][16]="莱芜";city[11][17]="滨州";
  city[12]=new Array(12);city[12][0]="浙江";city[12][1]="杭州";city[12][2]="绍兴";city[12][3]="湖州";city[12][4]="嘉兴";city[12][5]="宁波";city[12][6]="舟山";city[12][7]="台州";city[12][8]="金华";city[12][9]="丽水";city[12][10]="衢州";city[12][11]="温州";
  city[13]=new Array(12);city[13][0]="江西";city[13][1]="南昌";city[13][2]="九江";city[13][3]="景德镇";city[13][4]="上饶";city[13][5]="鹰潭";city[13][6]="宜春";city[13][7]="萍乡";city[13][8]="赣州";city[13][9]="吉安";city[13][10]="抚州";city[13][11]="新余";
  city[14]=new Array(10);city[14][0]="福建";city[14][1]="福州";city[14][2]="南平";city[14][3]="邵武";city[14][4]="福安";city[14][5]="厦门";city[14][6]="泉州";city[14][7]="漳州";city[14][8]="龙岩";city[14][9]="三明";
  city[15]=new Array(17);city[15][0]="湖南";city[15][1]="长沙";city[15][2]="株洲";city[15][3]="益阳";city[15][4]="岳阳";city[15][5]="常德";city[15][6]="吉首";city[15][7]="娄底";city[15][8]="怀化";city[15][9]="衡阳";city[15][10]="邵阳";city[15][11]="郴州";city[15][12]="零陵";city[15][13]="张家界";city[15][14]="湘潭";city[15][15]="永州";city[15][16]="湘西";
  city[16]=new Array(19);city[16][0]="湖北";city[16][1]="武汉";city[16][2]="沙市";city[16][3]="黄石";city[16][4]="鄂州";city[16][5]="咸宁";city[16][6]="襄樊";city[16][7]="十堰";city[16][8]="宜昌";city[16][9]="恩施";city[16][10]="荆门";city[16][11]="孝感";city[16][12]="荆州";city[16][13]="黄冈";city[16][14]="随州";city[16][15]="仙桃";city[16][16]="潜江";city[16][17]="天门";city[16][18]="神农架";
  city[17]=new Array(19);city[17][0]="河南";city[17][1]="郑州";city[17][2]="新乡";city[17][3]="安阳";city[17][4]="许昌";city[17][5]="漯河";city[17][6]="驻马店";city[17][7]="信阳";city[17][8]="周口";city[17][9]="平顶山";city[17][10]="洛阳";city[17][11]="三门峡";city[17][12]="南阳";city[17][13]="开封";city[17][14]="商丘";city[17][15]="鹤壁";city[17][16]="焦作";city[17][17]="濮阳";city[17][18]="济源";
  city[18]=new Array(23);city[18][0]="广东";city[18][1]="广州";city[18][2]="韶关";city[18][3]="英德";city[18][4]="梅州";city[18][5]="汕头";city[18][6]="惠州";city[18][7]="深圳";city[18][8]="湛江";city[18][9]="茂名";city[18][10]="肇庆";city[18][11]="佛山";city[18][12]="珠海";city[18][13]="汕尾";city[18][14]="江门";city[18][15]="河源";city[18][16]="阳江";city[18][17]="清远";city[18][18]="东莞";city[18][19]="中山";city[18][20]="潮州";city[18][21]="揭阳";city[18][22]="云浮";
  city[19]=new Array(14);city[19][0]="广西";city[19][1]="南宁";city[19][2]="百色";city[19][3]="钦州";city[19][4]="玉林";city[19][5]="桂林";city[19][6]="梧州";city[19][7]="柳州";city[19][8]="河池";city[19][9]="来宾";city[19][10]="崇左";city[19][11]="防城港";city[19][12]="贺州";city[19][13]="北海";
  city[20]=new Array(13);city[20][0]="贵州";city[20][1]="贵阳";city[20][2]="六盘水";city[20][3]="玉屏";city[20][4]="凯里";city[20][5]="都匀";city[20][6]="安顺";city[20][7]="遵义";city[20][8]="铜仁";city[20][9]="黔西南";city[20][10]="毕节";city[20][11]="黔东南";city[20][12]="黔南";
  city[21]=new Array(24);city[21][0]="四川";city[21][1]="成都";city[21][2]="乐山";city[21][3]="凉山";city[21][4]="渡口";city[21][5]="绵阳";city[21][6]="汶川";city[21][7]="雅安";city[21][8]="甘孜";city[21][9]="广元";city[21][10]="南充";city[21][11]="达州";city[21][12]="内江";city[21][13]="自贡";city[21][14]="宜宾";city[21][15]="泸州";city[21][16]="攀枝花";city[21][17]="德阳";city[21][18]="遂宁";city[21][19]="眉山";city[21][20]="广安";city[21][21]="巴中";city[21][22]="资阳";city[21][23]="阿坝";
  city[22]=new Array(20);city[22][0]="云南";city[22][1]="昆明";city[22][2]="曲靖";city[22][3]="昭通";city[22][4]="开远";city[22][5]="文山";city[22][6]="思茅";city[22][7]="大理";city[22][8]="楚雄";city[22][9]="临沧";city[22][10]="保山";city[22][11]="六盘水";city[22][12]="渡口";city[22][13]="丽江";city[22][14]="普洱";city[22][15]="红河";city[22][16]="西双版纳";city[22][17]="德宏";city[22][18]="怒江";city[22][19]="迪庆";
  city[23]=new Array(11);city[23][0]="陕西";city[23][1]="西安";city[23][2]="渭南";city[23][3]="延安";city[23][4]="绥德";city[23][5]="榆林";city[23][6]="宝鸡";city[23][7]="汉中";city[23][8]="安康";city[23][9]="商洛";city[23][10]="铜川";
  city[24]=new Array(17);city[24][0]="甘肃";city[24][1]="兰州";city[24][2]="武威";city[24][3]="张掖";city[24][4]="酒泉";city[24][5]="安西";city[24][6]="金昌";city[24][7]="天水";city[24][8]="定西";city[24][9]="平凉";city[24][10]="西峰";city[24][11]="陇西";city[24][12]="甘南";city[24][13]="白银";city[24][14]="庆阳";city[24][15]="陇南";city[24][16]="临夏";
  city[25]=new Array(6);city[25][0]="宁夏";city[25][1]="银川";city[25][2]="吴忠";city[25][3]="石咀山";city[25][4]="固原";city[25][5]="中卫";
  city[26]=new Array(10);city[26][0]="青海";city[26][1]="西宁";city[26][2]="果洛";city[26][3]="玉树";city[26][4]="格尔木";city[26][5]="海西";city[26][6]="阿坝";city[26][7]="海东";city[26][8]="海北";city[26][9]="海南";
  city[27]=new Array(21);city[27][0]="新疆";city[27][1]="乌鲁木齐";city[27][2]="石河子";city[27][3]="乌苏";city[27][4]="克拉玛依";city[27][5]="伊宁";city[27][6]="吐鲁番";city[27][7]="哈密";city[27][8]="巴音郭楞";city[27][9]="阿克苏";city[27][10]="喀什";city[27][11]="博尔塔拉";city[27][12]="克孜勒苏";city[27][13]="和田";city[27][14]="伊犁";city[27][15]="塔城";city[27][16]="阿勒泰";city[27][17]="阿拉尔";city[27][18]="图木舒克";city[27][19]="五家渠";city[27][20]="北屯";
  city[28]=new Array(8);city[28][0]="西藏";city[28][1]="拉萨";city[28][2]="那曲";city[28][3]="昌都";city[28][4]="山南";city[28][5]="日喀则";city[28][6]="阿里";city[28][7]="林芝";
  city[29]=new Array(21);city[29][0]="海南";city[29][1]="海口";city[29][2]="三亚";city[29][3]="海南";city[29][4]="三沙";city[29][5]="五指山";city[29][6]="琼海";city[29][7]="儋州";city[29][8]="文昌";city[29][9]="万宁";city[29][10]="东方";city[29][11]="定安";city[29][12]="屯昌";city[29][13]="澄迈";city[29][14]="临高";city[29][15]="白沙";city[29][16]="昌江";city[29][17]="乐东";city[29][18]="陵水";city[29][19]="保亭";city[29][20]="琼中";
  city[30]=new Array(12);city[30][0]="山西";city[30][1]="太原";city[30][2]="离石";city[30][3]="忻州";city[30][4]="宁武";city[30][5]="大同";city[30][6]="临汾";city[30][7]="侯马";city[30][8]="运城";city[30][9]="阳泉";city[30][10]="长治";city[30][11]="晋城";
  city[31]=new Array(17);city[31][0]="台湾";city[31][1]="台北";city[31][2]="台中";city[31][3]="基隆";city[31][4]="台南";city[31][5]="嘉义";city[31][6]="桃园";city[31][7]="苗粟";city[31][8]="屏东";city[31][9]="南投";city[31][10]="花莲";city[31][11]="新竹";city[31][12]="彰化";city[31][13]="高雄";city[31][14]="宜兰";city[31][15]="台东";city[31][16]="彭湖";
  city[32]=new Array(1);city[32][0]="香港";
  city[33]=new Array(1);city[33][0]="澳门"; 

  // 获取省份
  function getRegion(){ 
    var prov=$("#province").val();
    var str = "";
    if(prov == 3||prov == 32 ||prov == 33){  // 选择重庆、香港、澳门就不显示县级市了
      $("#city").hide();
    }else if(prov == 99){   // 选择省份，就显示市级
      str = "<option>---市---</option>";
      $("#city").html(str);
    }else{   // 填充县级市
      $("#city").show();
      for(var i=0;i < city[prov].length;i++){         
        if(i!=0){
          str=str+"<option>"+city[prov][i]+"</option>";   
        }
      } 
      $("#city").html(str);
    }
  } 

  // 填充省份
  function getProvince(){
    var strs="<option value='99'>---省份---</option>"; 
    for(var i=0;i<=33;i++) { 
        strs=strs+"<option value="+i+">"+city[i][0]+"</option>"; 
    }
    $("#province").html(strs);
  } 

  // 显示延迟原因
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

  var calculate_str = "";
  var calculate_type = "normal";
  function calculateDay(type){
    // 获取起始日期和结束日期
    start_date = $("#start_date").val()+" "+$("#start_time").val();
    end_date = $("#end_date").val()+" "+$("#end_time").val();

    if(start_date <= end_date){ // 如果起始日期小于结束日期
      // 发送数据
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/outCountDays',
        data:{'start':start_date,'end':end_date, 'type':calculate_type},
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
          }
        }
      });
    }else{
      $("#count-day").text("共 0 天");
    }
  }

  // 显示出差申请的div
  function showBusiness(){
    $("#businesstrip-info-div").fadeOut(400,function(){
      $("#businesstrip-div").slideDown(400);
    });
  }

  // 隐藏出差申请的div
  function hideBusiness(){
    $("#businesstrip-div").fadeOut(400,function(){
      $("#businesstrip-info-div").slideDown(400);
    });
  }
</script>