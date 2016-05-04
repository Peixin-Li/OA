<?php
echo "<script type='text/javascript'>";
echo "console.log('notificationManage');";
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
  <h4 class="pd10 m0 b33 bor-b-1-ddd">公告管理</h4>
  <div class="pd20 center">
    <!-- 可选标签 -->
    <ul class="nav nav-tabs">
      <li role="presentation" <?php if(!empty($status) && $status == "display") echo "class='active'";?>><a class="pointer" onclick="location.href='/oa/notificationManage/status/display';">展示中</a></li>
      <li role="presentation" <?php if(!empty($status) && $status == "hidden") echo "class='active'";?>><a class="pointer" onclick="location.href='/oa/notificationManage/status/hidden';">过期通知</a></li>
    </ul>
  <?php if(!empty($data)): ?>
  <!-- 展示中表格 -->
    <?php if($status == 'display'): ?>
    <table class="table bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd m0 center " id="show-table">
      <thead>
        <tr>
          <th class="w100 center">类型</th>
          <th class="center">标题</th>
          <th class="w130 center">过期时间</th>
          <th class="w130 center">发布日期</th>
          <th class="w200 center">操作</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($data as $row): ?>
        <tr>
          <td class="hidden"><?php echo $row->id; ?></td>
          <td>
            <?php 
              if($row->type == "internal"){
                echo "内部悬赏";
              }else if($row->type == "holiday"){
                echo "放假通知";
              }else if($row->type == "appointments"){
                echo "人事任命";
              }else if($row->type == "activity"){
                echo "活动通知";
              }else if($row->type == "admin"){
                echo "行政通知";
              }else if($row->type == "other"){
                echo "其他通知";
              }
            ?>
          </td>
          <td><a class="pointer" onclick="showDetail(this);" title="查看详情"><?php echo $row->title; ?></a></td>
          <td class="hidden"><?php echo $row->content; ?></td>
          <td><?php if($row->expire_time != "0000-00-00"){echo $row->expire_time;}else{echo '无';} ?></td>
          <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
          <td>
            <button class="btn btn-default" onclick="showEdit(this);"><span class="glyphicon glyphicon-edit" title="编辑"></span>&nbsp;编辑</button>
            <button class="btn btn-default" onclick="showDelete(this);"><span class="glyphicon glyphicon-remove b2" title="撤销"></span>&nbsp;撤销</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
    <!-- 过期通知 -->
    <?php if($status == 'hidden'): ?>
    <table class="table bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd m0 center" id="past-table">
      <thead>
        <tr>
          <th class="w100 center">类型</th>
          <th class="center">标题</th>
          <th class="w130 center">过期时间</th>
          <th class="w130 center">发布日期</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($data as $row): ?>
        <tr>
          <td class="hidden"><?php echo $row->id; ?></td>
          <td>
            <?php 
              if($row->type == "internal"){
                echo "内部悬赏";
              }else if($row->type == "holiday"){
                echo "放假通知";
              }else if($row->type == "appointments"){
                echo "人事任命";
              }else if($row->type == "activity"){
                echo "活动通知";
              }else if($row->type == "admin"){
                echo "行政通知";
              }else if($row->type == "other"){
                echo "其他通知";
              }
            ?>
          <td><a class="pointer" onclick="showDetail(this);"><?php echo $row->title; ?></a></td>
          <td class="hidden"><?php echo $row->content; ?></td>
          <td><?php if($row->expire_time != "0000-00-00"){echo $row->expire_time;}else{echo '无';} ?></td>
          <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  <!-- 分页 -->
  <div class="inline-block pd20 center">
    <?php 
        $this->widget('CLinkPager',array(
            'firstPageLabel'=>'首页',
            'lastPageLabel'=>'末页',
            'prevPageLabel'=>'上一页',
            'nextPageLabel'=>'下一页',
            'pages'=>$page,
            'maxButtonCount'=>5,   
        )
    );
    ?>
    </div>

    <?php else:?>
    <h4 class="center bor-r-1-ddd bor-l-1-ddd bor-b-1-ddd pd10 m0">没有公告</h4>
  <?php endif; ?>
  </div>
</div>

<!-- 编辑按钮模态框 -->
<div id="edit-div" class="modal fade in hint bor-rad-5 w800" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">修改公告</h4>
    </div>

    <div class="modal-body">
      <table class="table bor-1-ddd m0">
        <tr>
          <th class="w130 center">公告类型</th>
          <td id="edit-id" class="hidden"></td>
          <td id="edit-type"></td>
        </tr>
        <tr>
          <th class="w130 center">标题</th>
          <td><input class="form-control" id="edit-title" placeholder="请输入标题"></td>
        </tr>
        <tr>
          <th class="w130 center">内容</th>
          <td><textarea class="form-control" rows="10" id="edit-content" placeholder="请输入内容"></textarea></td>
        </tr>
        <tr>
          <th class="w130 center">过期方式</th>
          <td>
            <input type="radio" name="edit-end-type" value="auto" id="auto-end-type" onclick="endTypeChange(this.id)"><span class="mr20 pointer" onclick="$('#auto-end-type').click();">自动</span>
            <input type="radio" name="edit-end-type" value="manual" id="manual-end-type" onclick="endTypeChange(this.id)"><span class="pointer" onclick="$('#manual-end-type').click();">手动撤销</span>
          </td>
        </tr>
        <tr id="end-time-tr">
          <th class="w130 center">过期时间</th>
          <td>
            <input class="form-control w130 pointer" id="edit-end-time" value="<?php echo date('Y-m-d');?>" onchange="dateCheck();">
          </td>
        </tr>
      </table>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success w100" onclick="editNotification();">确认修改</button>
    </div>
</div>

<!-- 公告详情模态框 -->
<div id="detail-div" class="modal fade in hint bor-rad-5 w800" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">公告详情</h4>
    </div>

    <div class="modal-body">
      <table class="table bor-1-ddd m0">
        <tr>
          <th class="w130 center">公告类型</th>
          <td id="detail-id" class="hidden"></td>
          <td id="detail-type"></td>
        </tr>
        <tr>
          <th class="w130 center">标题</th>
          <td id="detail-title"></td>
        </tr>
        <tr>
          <th class="w130 center">内容</th>
          <td><pre class="m0 p00 bg-trans bor-none" id="detail-content" style="max-width:600px;"></pre></td>
        </tr>
        <tr>
          <th class="w130 center">过期时间</th>
          <td id="detail-end-time"></td>
        </tr>
      </table>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
  // 页面初始化
  $(document).ready(function(){
    // 日期选择控件初始化
    $('#edit-end-time').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
      $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
  });

  // 日期检测-不能选择今天以前的日期
  function dateCheck(){
    var today = "<?php echo date('Y-m-d');?>";
    if($("#edit-end-time").val() < today){
      showHint("提示信息","过期时间不能在今天之前");
      $("#edit-end-time").val(today);
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

  // 显示详情
  function showDetail(obj){
    var id = $(obj).parent().parent().children().first();
    var type = id.next();
    var title = type.next();
    var content = title.next();
    var end_time = content.next();

    $("#detail-id").text(id.text());
    $("#detail-type").text(type.text());
    $("#detail-title").text(title.text());
    $("#detail-content").text(content.text());
    $("#detail-end-time").text(end_time.text());

    var ySet = (window.innerHeight - $("#detail-div").height())/3;
    var xSet = (window.innerWidth - $("#detail-div").width())/2;
    $("#detail-div").css("top",ySet);
    $("#detail-div").css("left",xSet);
    $("#detail-div").modal({show:true});

  }

  // 修改公告
  function editNotification(obj){
    var id = $("#edit-id").text();
    var type = $("#edit-type").text();
    var title = $("#edit-title").val();
    var content = $("#edit-content").val();
    var end_time = "";
    if($("input[name='edit-end-type']:checked").val() == "auto"){
      end_time = $("#edit-end-time").val();
    }

    var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
    if(title == ""){
      showHint("提示信息","请输入标题");
      $("#edit-title").focus();
    }else if(title.length > 50){
      showHint("提示信息","标题长度不能大于50字");
      $("#edit-title").focus();
    }else if(content == ""){
      showHint("提示信息","请输入内容");
      $("#edit-content").focus();
    }else if(content.length > 1500){
      showHint("提示信息","内容长度不能大于1500字");
      $("#edit-content").focus();
    }else if(!date_pattern.exec(end_time) && end_time != ""){
      showHint("提示信息","过期时间格式不正确");
    }else{
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/editNotify',
        data:{'id':id, 'title':title, 'content':content, 'expire_time':end_time},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","修改公告成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","修改公告失败，请重试！");
          }else if(result.code == -2){
            showHint("提示信息","参数错误，请重试！");
          }else if(result.code == -3){
            showHint("提示信息","找不到该公告！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 显示修改
  function showEdit(obj){
    var id = $(obj).parent().parent().children().first();
    var type = id.next();
    var title = type.next();
    var content = title.next();
    var end_time = content.next();
    if(end_time.text().indexOf("无") > -1){
      $("#manual-end-type").click();
      $("#end-time-tr").addClass("hidden");
    }else{
      $("#auto-end-type").click();
      $("#end-time-tr").removeClass("hidden");
    }

    $("#edit-id").text(id.text());
    $("#edit-type").text(type.text());
    $("#edit-title").val(title.text());
    $("#edit-content").val(content.text());
    if(end_time.text().indexOf("无") > -1){
      $("#edit-end-time").val("<?php echo date('Y-m-d');?>");
    }else{
      $("#edit-end-time").val(end_time.text());
    }

    var ySet = (window.innerHeight - $("#edit-div").height())/3;
    var xSet = (window.innerWidth - $("#edit-div").width())/2;
    $("#edit-div").css("top",ySet);
    $("#edit-div").css("left",xSet);
    $("#edit-div").modal({show:true});
  }

  // 提示删除
  var delete_id = "";
  function showDelete(obj){
    delete_id = $(obj).parent().parent().children().first().text();
    showConfirm("提示信息","确认撤销该公告？","确认","deleteNotification()","取消");
  }

  // 发送删除
  function deleteNotification(){
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/revokeNotify',
      data:{'id':delete_id},
      success:function(result){
        if(result.code == 0){
          showHint("提示信息","撤销公告成功！");
          setTimeout(function(){location.reload();},1200);
        }else if(result.code == -1){
          showHint("提示信息","撤销公告失败，请重试！");
        }else if(result.code == -2){
          showHint("提示信息","参数错误，请重试！");
        }else if(result.code == -3){
          showHint("提示信息","找不到该公告！");
        }else if(result.code == -99){
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }
</script>
