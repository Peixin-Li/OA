<?php
echo "<script type='text/javascript'>";
echo "console.log('activity');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui-timepicker-addon.js"></script>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd pd20">
  <!-- 我创建的活动 -->
  <?php if(!empty($team)): ?>
  <div class="pb20">
    <!-- 标题 -->
    <h4>
      <strong>我创建的活动</strong>
      <button class="btn btn-success btn-lg ml10" onclick="showNewActivity();">创建活动</button>
    </h4>
    <!-- 创建的活动内容 -->
    <?php if(!empty($own_activity)): ?>
    <table class="table m0 table-bordered center">
      <tr class="bg-fa">
        <th class="center w100">活动</th>
        <th class="center w150">举办时间</th>
        <th class="center w150">截止报名时间</th>
        <th class="center w80">报名人数</th>
        <th class="center w400">报名人员</th>
        <th class="center w80">状态</th>
        <th class="center w80">活动信息</th>
        <th class="center w80">活动结果</th>
      </tr>
      <tr>
        <td class="hidden"><?php echo $own_activity['id']; ?></td>
        <td><?php echo $team->name; ?></td>
        <td><?php echo $own_activity['activity_time']; ?></td>
        <td><?php echo $own_activity['end_time']; ?></td>
        <td><?php echo empty($own_activity->joins) ? '0':count($own_activity->joins);?></td>
        <td>
          <?php
            if(!empty($own_activity->joins)){
              $index = 1;  // 末尾标记
              foreach($own_activity->joins as $name_row_own){
                if($index++ == count($own_activity->joins)){   // 如果到末尾的话就不输出顿号
                  echo $name_row_own->user->cn_name;
                }else{
                  echo $name_row_own->user->cn_name.'、';
                }
              }
            }
          ?>
        </td>
        <td>
          <button class="btn btn-primary <?php echo ($own_activity['status'] == 'enroll') ? '':'disabled';?>" onclick="cancelActivity(this);">取消活动</button>
        </td>
        <td>
          <button class="btn btn-success <?php echo ($own_activity['status'] == 'hold' && $own_activity['activity_time'] >= date('Y-m-d H:i:s')) ? '':'disabled';?>" onclick="showActivityInfo(this);">我要发送</button>
        </td>
        <td>
          <button class="btn btn-success <?php echo ($own_activity['status'] == 'hold' && $own_activity['activity_time'] <= date('Y-m-d H:i:s')) ? '':'hidden';?>" onclick="showActivityResult(this);">我要确认</button>
        </td>
      </tr>
    </table>
    <?php else:?>
    <h4 class="m0 pd20 bor-1-ddd center">没有我创建的活动</h4>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <!-- 我报名的活动 -->
  <div class="pb20">
    <!-- 标题 -->
    <h4>
      <strong>我报名的活动</strong>
    </h4>

    <?php $joins_show_tag = false; if(!empty($joins)){foreach($joins as $jrow){if($jrow->activity['activity_time'] >= date('Y-m-d H:i:s')){$joins_show_tag = true;}}} ?>
    <!-- 报名的活动内容 -->
    <?php if(!empty($joins) && $joins_show_tag): ?>
    <table class="table m0 table-bordered center">
      <tr class="bg-fa">
        <th class="center w100">活动</th>
        <th class="center w150">举办时间</th>
        <th class="center w150">截止报名时间</th>
        <th class="center w80">报名人数</th>
        <th class="center w400">报名人员</th>
        <th class="center w80">状态</th>
      </tr>
      <?php foreach($joins as $jrow): ?>
      <?php if($jrow->activity['activity_time'] >= date('Y-m-d H:i:s')): ?>
      <tr>
        <td class="hidden"><?php echo $jrow->activity['id']?></td>
        <td><?php echo $jrow->activity->team['name'];?></td>
        <td><?php echo $jrow->activity['activity_time']?></td>
        <td><?php echo $jrow->activity['end_time']?></td>
        <td>
          <?php echo empty($jrow->activity->joins) ? '0':count($jrow->activity->joins);?>
        </td>
        <td>
          <?php
            if(!empty($jrow->activity->joins)){
              $index = 1;  // 末尾标记
              foreach($jrow->activity->joins as $name_row_self){
                if($index++ == count($jrow->activity->joins)){    // 如果到末尾的元素就不输出顿号
                  echo $name_row_self->user->cn_name;
                }else{
                  echo $name_row_self->user->cn_name.'、';
                }
              }
            }
          ?>
        </td>
        <td>
          <?php if($jrow->activity->team->admin != $this->user->user_id): ?>
          <button class="btn btn-primary w100 <?php if($jrow->activity['end_time'] < date('Y-m-d H:i:s')){echo "disabled"; }?>" onclick="unJoinActivity(this);">取消报名</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endif; ?>
      <?php endforeach; ?>
    </table>
    <?php else:?>
    <h4 class="m0 pd20 bor-1-ddd center">没有参与活动</h4>
    <?php endif; ?>
  </div>
  <!-- 活动大厅 -->
  <div class="mt20">
    <!-- 标题 -->
    <h4>
      <strong>活动大厅</strong>
    </h4>
    <?php if(!empty($activitys)): ?>
    <table class="table table-bordered center m0">
      <tr class="bg-fa">
        <th class="center w100">活动</th>
        <th class="center w150">举办时间</th>
        <th class="center w150">截止报名时间</th>
        <th class="center w80">报名人数</th>
        <th class="center w400">报名人员</th>
        <th class="center w80">状态</th>
      </tr>
      <?php foreach($activitys as $arow): ?>
      <tr>
        <td class="hidden"><?php echo $arow['id']; ?></td>
        <td><?php echo $arow->team->name;?></td>
        <td><?php echo date('Y-m-d H:i', strtotime($arow['activity_time']));?></td>
        <td><?php echo date('Y-m-d H:i', strtotime($arow['end_time']));?></td>
        <td>
          <?php echo empty($arow->joins) ? '0' : count($arow->joins);?>
        </td>
        <td>
          <?php
            $join_tag = false;  // 参加了的标记
            if(!empty($arow->joins)){
              $index = 1;  // 末尾标记
              foreach($arow->joins as $name_row){
                if($index++ == count($arow->joins)){   // 如果输入到末尾就不输出顿号
                  echo $name_row->user->cn_name;
                }else{
                  echo $name_row->user->cn_name.'、';
                }
                // 如果在参与人员中找到了就代表已经参加了
                if($name_row->user->user_id == $this->user->user_id){
                  $join_tag = true;
                }
              }
            }
          ?>
        </td>
        <td>
          <!-- 已经参加了显示已报名，未参加就显示我要报名 -->
          <?php if($arow['status'] == "hold"): ?>
          <button class="btn btn-success disabled w100" onclick="joinActivity(this);">举办中</button>
          <?php elseif(!$join_tag): ?>
          <button class="btn btn-success w100" onclick="joinActivity(this);">我要报名</button>
          <?php elseif($join_tag): ?>
          <button class="btn btn-success disabled w100" onclick="joinActivity(this);">已报名</button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </table>
    <?php else:?>
    <h4 class="m0 pd20 bor-1-ddd center">目前没有活动</h4>
    <?php endif; ?>
  </div>
</div>
<!-- 创办活动模态框 -->
<div id="new-activiy-div" class="modal fade in hint bor-rad-5 w500" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">创建活动</h4>
    </div>
    <div class="modal-body">
        <table class="table table-bordered m0">
          <tr>
            <th class="center w150">活动</th>
            <td>
              <span><?php echo empty($team) ? '' : $team['name']; ?></span>
              <span class="b2 ml20">
                <!-- 显示年度预算 -->
                <?php if(empty($team) || empty($team->teamBudget)): ?>
                (暂时不能创办)
                <?php else: ?>
                (年度预算剩余：
                <?php echo empty($team) ? '' : ($team->teamBudget['total'] - $team->teamBudget['cost']);?>
                元)
                <?php endif; ?>
              </span>
            </td>
          </tr>
          <tr>
            <th class="center w150">举办时间</th>
            <td>
              <input class="form-control w200 inline" id="new-activity-time" value="<?php echo date('Y-m-d 19:30',strtotime('+1weeks'));?>" onchange="setNewWeekday();">
              <span id="new-activity-weekday"></span>
            </td>
          </tr>
          <tr>
            <th class="center w150">截止报名时间</th>
            <td>
              <input class="form-control w200 inline" id="new-activity-join-time" value="<?php echo date('Y-m-d 18:30',strtotime('+4days'));?>" onchange="setNewJoinWeekday();">
              <span id="new-activity-join-weekday"></span>
            </td>
          </tr>
          <tr>
            <th class="center w150">最低人数限制</th>
            <td><?php echo empty($team) ? '0' : $team->min_num ;?>人</td>
          </tr>
        </table>
    </div>
    <div class="modal-footer">
      <!-- 如果没有添加年度预算或者年度预算为负数的话就不能提交 -->
      <button class="btn btn-success w100 <?php if(empty($team) || empty($team->teamBudget) || (!empty($team->teamBudget) &&($team->teamBudget['total'] - $team->teamBudget['cost']) <= 0)){echo "disabled";}?>" onclick="sendNewActivity();">提交</button>
    </div>
</div>
<!-- 发送活动信息模态框 -->
<div id="info-activiy-div" class="modal fade in hint bor-rad-5 w600" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">发送活动信息</h4>
    </div>
    <div class="modal-body">
        <table class="table table-bordered m0">
          <tr>
            <th class="w150 center">联系人</th>
            <td>
              <input class="form-control w150" id="activity-info-name" placeholder="请输入联系人中文名" value="<?php echo empty($this->user->cn_name) ? '':$this->user->cn_name;?>">
            </td>
          </tr>
          <tr>
            <th class="w150 center">手机号码</th>
            <td>
              <input class="form-control w150" id="activity-info-mobile" placeholder="请输入联系人手机" value="<?php echo empty($this->user->cn_name) ? '':$this->user->mobile;?>">
            </td>
          </tr>
          <tr>
            <th class="w150 center">活动时间</th>
            <td>
              <span><?php if(!empty($own_activity)){echo date('Y-m-d', strtotime($own_activity['activity_time']));}?></span>
              <input class="form-control inline w80 center ml10" id="activity-info-time" placeholder="请输入活动时间" value="<?php echo empty($own_activity) ? '': date('H:i', strtotime($own_activity['activity_time']));?>">
            </td>
          </tr>
          <tr>
            <th class="w150 center">活动地点</th>
            <td>
              <input class="form-control" id="activity-info-location" placeholder="请输入活动地点">
            </td>
          </tr>
          <tr>
            <th class="w150 center">路线说明</th>
            <td>
              <textarea class="form-control" id="activity-info-way" placeholder="请输入路线说明" rows="3"></textarea>
            </td>
          </tr>
        </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendActivityInfo();">提交</button>
    </div>
</div>
<!-- 确认活动结果模态框 -->
<div id="result-activiy-div" class="modal fade in hint bor-rad-5 w600" style="display: none;">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">确认活动结果</h4>
    </div>
    <div class="modal-body">
        <table class="table table-bordered m0">
          <tr>
            <th class="w150 center">实际活动经费</th>
            <td>
              <input class="form-control w150" id="activity-result-money" placeholder="请输入实际经费">
            </td>
          </tr>
          <tr>
            <th class="w150 center">参加人员</th>
            <td id="activity-result-people">
              <?php if(!empty($own_activity) && !empty($own_activity->joins)): ?>
              <?php foreach($own_activity->joins as $r_joins): ?>
              <button class="btn btn-success mr10 mb15" name="<?php echo $r_joins->user->user_id;?>"><span class='name-span'><?php echo $r_joins->user->cn_name;?></span>&nbsp;<span class="glyphicon glyphicon-remove" onclick="removePeople(this);"></span></button>
              <?php endforeach; ?>
              <?php else: ?>
              没有参加人员
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th class="w150 center">添加参加人员</th>
            <td>
              <a class="pointer" onclick="showAddPeople(this);">添加</a>
              <input class="form-control w150 inline hidden" id="activity-result-name" placeholder="请输入参加人员">
              <button class="btn btn-primary  hidden" onclick="addPeople();">添加</button>
            </td>
          </tr>
        </table>
    </div>
    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendActivityResult();">提交</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
  // 用户数组初始化
  var users_arr = new Array();  // 用户数组
  var cn_name_arr = new Array();  // 用户中文名称数据
  <?php if(!empty($users)): ?>
  <?php foreach($users as $urow): ?>
    users_arr.push({'id':"<?php echo $urow['user_id'];?>", 'name':"<?php echo $urow['cn_name'];?>"});
    cn_name_arr.push("<?php echo $urow['cn_name']; ?>");
  <?php endforeach; ?>
  <?php endif; ?>

  // 发送创建活动
  function sendNewActivity(){
    // 获取数据
    var time = $("#new-activity-time").val();
    var join_time = $("#new-activity-join-time").val();
    var time_pattern = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/;
    // 验证数据
    if(!time_pattern.exec(time)){
      showHint("提示信息","举办时间输入格式不正确");
    }else if(!time_pattern.exec(join_time)){
      showHint("提示信息","截止报名时间输入格式不正确");
    }else if(time <= join_time){
      showHint("提示信息","截止报名时间必须小于举办时间");
    }else{
      // 发送数据
      $.ajax({
        type:'post',
        url:'/ajax/addTeamActivity',
        dataType:'json',
        data:{'team_id':team_id, 'end_time':join_time, 'activity_time':time},
        success:function(result){
            if(result.code == 0){
              showHint("提示信息","创建活动成功");
              setTimeout(function(){location.reload();},1200);
            }else if(result.code == '-1'){
              showHint("提示信息","创建活动失败");
            }else if(result.code == '-2'){
              showHint("提示信息","参数错误");
            }else if(result.code == '-3'){
              showHint("提示信息","没有找到该小组");
            }else if(result.code == '-4'){
              showHint("提示信息","小组费用预算不足");
            }else if(result.code == '-5'){
              showHint("提示信息","亲，每次只能举办一次活动");
            }else{
              showHint("提示信息","你没有权限执行此操作");
            }
          }
      });
    }
  }

  // 显示创办活动
  var team_id = "<?php echo empty($team) ? '' : $team['id']; ?>";
  function showNewActivity(){
    var ySet = (window.innerHeight - $("#new-activiy-div").height())/2;
    var xSet = (window.innerWidth - $("#new-activiy-div").width())/2;
    $("#new-activiy-div").css("top",ySet);
    $("#new-activiy-div").css("left",xSet);
    $("#new-activiy-div").modal({show:true});
  }

  // 页面初始化
  $(document).ready(function(){
    // 日期选择器初始化
    $("#new-activity-time").datetimepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#new-activity-join-time").datetimepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $('#activity-info-time').datetimepicker({timeOnly:true});

    // 显示周几
    setNewWeekday();
    setNewJoinWeekday();

    // 自动匹配用户名
    $("#activity-info-name").autocomplete({
      source: cn_name_arr
    });
    $("#activity-result-name").autocomplete({
      source: cn_name_arr
    });
  });

  // 设置举办时间的周几
  function setNewWeekday(){
    var time = $("#new-activity-time").val();
    $("#new-activity-weekday").text(weekdayCal(time));
  }

  // 设置截止时间的周几
  function setNewJoinWeekday(){
    var time = $("#new-activity-join-time").val();
    $("#new-activity-join-weekday").text(weekdayCal(time));
  }

  // 周几的计算
  function weekdayCal(date){
    var select_day = new Date(date);
    var day = select_day.getDay();
    switch(day){
      case 0:{return "周日";break;}
      case 1:{return "周一";break;}
      case 2:{return "周二";break;}
      case 3:{return "周三";break;}
      case 4:{return "周四";break;}
      case 5:{return "周五";break;}
      case 6:{return "周六";break;}
    }
  }

  // 报名参加活动
  function joinActivity(obj){
    // 获取数据
    var id = $(obj).parent().parent().children().first().text();
    // 发送数据
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/joinActivity',
      data:{'id':id},
      success:function(result){
        if(result.code == 0){
          showHint("提示信息","报名成功！");
          setTimeout(function(){location.reload();},1200);
        }else if(result.code == -1){
            showHint("提示信息","报名失败！");
        }else if(result.code == -2){
            showHint("提示信息","参数错误！");
        }else if(result.code == -3){
            showHint("提示信息","没有此活动！");
        }else if(result.code == -4){
            showHint("提示信息","该活动当前不在报名阶段！");
        }else if(result.code == -5){
            showHint("提示信息","不能同时参加同一时间举办的活动！");
        }else{
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 取消报名
  function unJoinActivity(obj){
    // 获取数据
    var id = $(obj).parent().parent().children().first().text();
    // 发送数据
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/cancelJoinActivity',
      data:{'id':id},
      success:function(result){
        if(result.code == 0){
          showHint("提示信息","取消报名成功！");
          setTimeout(function(){location.reload();},1200);
        }else if(result.code == -1){
            showHint("提示信息","取消报名失败！");
        }else if(result.code == -2){
            showHint("提示信息","参数错误！");
        }else if(result.code == -3){
            showHint("提示信息","没有此活动！");
        }else if(result.code == -4){
            showHint("提示信息","该活动当前不在报名阶段！");
        }else{
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 取消活动
  var cancel_id = "";
  function cancelActivity(obj){
    // 获取数据
    cancel_id = $(obj).parent().parent().children().first().text();
    // 二次提示
    showConfirm("提示信息","确定要取消活动?","确定","sendCancelActivity();","取消");
  }

  // 发送取消活动
  function sendCancelActivity(){
    // 发送数据
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/cancelTeamActivity',
      data:{'id':cancel_id},
      success:function(result){
        if(result.code == 0){
          showHint("提示信息","取消活动成功！");
          setTimeout(function(){location.reload();},1200);
        }else if(result.code == -1){
            showHint("提示信息","取消活动失败！");
        }else if(result.code == -2){
            showHint("提示信息","参数错误！");
        }else if(result.code == -3){
            showHint("提示信息","没有此活动！");
        }else if(result.code == -4){
            showHint("提示信息","该活动不能取消！");
        }else{
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 显示发送活动消息
  var activity_info_id = "";
  function showActivityInfo(obj){
    activity_info_id = $(obj).parent().parent().children().first().text();
    var ySet = (window.innerHeight - $("#info-activiy-div").height())/2;
    var xSet = (window.innerWidth - $("#info-activiy-div").width())/2;
    $("#info-activiy-div").css("top",ySet);
    $("#info-activiy-div").css("left",xSet);
    $("#info-activiy-div").modal({show:true});
  }

  // 发送活动信息
  function sendActivityInfo(){
    // 获取数据
    var name = $("#activity-info-name").val();
    var mobile = $("#activity-info-mobile").val();
    var mobile_pattern = /^1\d{10}$/;
    var time = $("#activity-info-time").val();
    var time_pattern = /^\d{2}:\d{2}$/;
    var location = $("#activity-info-location").val();
    var way = $("#activity-info-way").val();
    var date = "<?php echo empty($own_activity) ? '' :date('Y-m-d', strtotime($own_activity['activity_time']));?>"+" "+time;
    // 验证数据
    if(name == ""){
      showHint("提示信息","请输入联系人中文名");
      $("#activity-info-name").focus();''
    }else if(!mobile_pattern.exec(mobile)){
      showHint("提示信息","联系电话输入格式错误");
      $("#activity-info-mobile").focus();
    }else if(!time_pattern.exec(time)){
      showHint("提示信息","活动时间输入格式错误");
      $("#activity-info-time").focus();
    }else if(location == ""){
      showHint("提示信息","请输入活动地点");
      $("#activity-info-location").focus();
    }else if(way == ""){
      showHint("提示信息","请输入路线说明");
      $("#activity-info-way").focus();
    }else{
      // 发送数据
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/sendActivityLine',
        data:{'id':activity_info_id, 'activity_time':date, 'mobile':mobile, 'contact':name, 'address':location, 'line':way},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","发送活动信息成功！");
            reloadPage();
          }else if(result.code == -1){
              showHint("提示信息","发送活动信息失败！");
          }else if(result.code == -2){
              showHint("提示信息","参数错误！");
          }else if(result.code == -3){
              showHint("提示信息","没有此活动！");
          }else if(result.code == -4){
              showHint("提示信息","只能修改举办活动的小时和分钟!");
          }else if(result.code == -5){
              showHint("提示信息","目前活动还没有举办!");
          }else{
              showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 刷新页面
  function reloadPage(){
    setTimeout(function(){location.reload();},1200);
  }

  // 显示确认活动结果
  var activity_result_id = "";
  function showActivityResult(obj){
    activity_result_id = $(obj).parent().parent().children().first().text();
    var ySet = (window.innerHeight - $("#result-activiy-div").height())/2;
    var xSet = (window.innerWidth - $("#result-activiy-div").width())/2;
    $("#result-activiy-div").css("top",ySet);
    $("#result-activiy-div").css("left",xSet);
    $("#result-activiy-div").modal({show:true});
  }

  // 发送确认活动结果
  function sendActivityResult(){
    // 获取数据
    var money = $("#activity-result-money").val();
    var d_pattern = /^\d+(\.\d{1,2})?$/;
    var user_arr = new Array();
    $("#activity-result-people").find("button").each(function(){
      user_arr.push($(this).attr("name"));
    });
    // 验证数据
    if(!d_pattern.exec(money)){
      showHint("提示信息","实际活动经费输入格式错误");
      $("#activity-result-money").focus();
    }else if(user_arr.length < 1){
      showHint("提示信息","请添加活动参加人员");
      $("#activity-result-name").focus();
    }else{
      // 发送数据
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/setActivityJoin',
        data:{'activity_id':activity_result_id, 'outlay':money, 'users':user_arr},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","确认活动结果成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
              showHint("提示信息","确认活动结果失败！");
          }else if(result.code == -2){
              showHint("提示信息","参数错误！");
          }else if(result.code == -3){
              showHint("提示信息","没有此活动！");
          }else if(result.code == -4){
              showHint("提示信息","该活动还没有举办!");
          }else{
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 添加人员
  function addPeople(){
    var name = $("#activity-result-name").val();
    var exist_tag = false; // 是否已经添加的标记
    $("#activity-result-people").find("button").each(function(){
      if($(this).find('span.name-span').text() == name){
        exist_tag = true;   // 找到了就证明存在
        return false;
      }
    });
    var find_tag = false;  // 是否存在于用户数组中的标记
    var user_id = "";
    $.each(users_arr, function(){
      if(this['name'] == name){
        user_id = this['id'];
        find_tag = true;
        return false;
      }
    });
    if(find_tag && !exist_tag){  // 如果还没添加并且存在
      var str = "<button class='btn btn-success mr10 mb15' name='"+user_id+"'><span class='name-span'>"+name+"</span>&nbsp;<span class='glyphicon glyphicon-remove' onclick='removePeople();'></span></button>";
      $("#activity-result-people").append(str);
      $("#activity-result-name").val("");
      $("#activity-result-name").focus();
    }else if(exist_tag){  // 如果已经添加
      showHint("提示信息","该人员已存在");
      $("#activity-result-name").focus();
    }else if(!find_tag){  // 如果不存在
      showHint("提示信息","查找不到该人员");
      $("#activity-result-name").focus();
    }
  }

  // 取消人员
  function removePeople(obj){
    $(obj).parent().remove();
  }

  // 显示添加人员
  function showAddPeople(obj){
    $(obj).addClass("hidden");
    $(obj).next().removeClass("hidden");
    $(obj).next().next().removeClass("hidden");
  }
</script>