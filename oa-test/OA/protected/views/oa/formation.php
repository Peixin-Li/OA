<?php
echo "<script type='text/javascript'>";
echo "console.log('formation');";
echo "</script>";
?>

<!-- 主界面 -->
<div class="bor-1-ddd">
  <div class="w250 fl">
      <!-- 标题+查看编制按钮 -->
      <h4 class="bor-b-1-ddd pd10 m0">
        <span>&nbsp;</span>
        <strong class="fl">人员编制</strong>
        <button class="btn btn-success pd3 fl mt-2 ml10" onclick="showTotalFormation();">查看编制</button>
      </h4>
      <!-- 读取部门信息 -->
      <div class="tree pl20 oatree overflow-a" id="tree-div" style="max-height:692px;">
      <?php if(!empty($result)):?>
      <?php foreach($result as $row):?>
      <?php if($row['pId'] == "00"): ?>
      <ul class="p00">
        <li>
          <span id="department-<?php echo $row['id'];?>" class="<?php if(!empty($department_id) && $department_id == 1) echo 'active';?>">
            <p class="m0"><strong><?php echo $row['name'];?></strong></p>
          </span>
        </li>
      </ul>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php else: ?>
      <h4 class="center"style="vertical-align:middle;">读取部门信息失败，请重试</h4>
      <?php endif; ?>
    </div>
  </div>

  <div class="fr bor-l-1-ddd" style="width:828px;min-height:732px;">
    <!-- 右边标题 -->
    <h4 class="bor-b-1-ddd pd10 m0">
      <strong>部门编制信息</strong>
      <button class="mt-5 btn btn-primary fr pd3 mr10" onclick="deleteDepartment();"><span class="glyphicon glyphicon-minus"></span>&nbsp;删除部门</button>
      <button class="mt-5 btn btn-success fr pd3 mr10" onclick="newDepartment();"><span class="glyphicon glyphicon-plus"></span>&nbsp;新建部门</button>
    </h4>
    <!-- 部门信息详情 -->
    <div id="detail-div" style="max-height:692px;" class="overflow-a">
      <?php if(!empty($department)): ?>
      <div class="pd20">
        <h4 class="bor-b-1-ddd pd10 m0">概况<button class="mt-5 btn btn-default fr pd3 hidden" onclick="deleteDepartment();"><span class="glyphicon glyphicon-remove"></span>&nbsp;撤销部门</button></h4>
        <table class="table w400 table-unbordered ml20">
          <tbody>
            <tr>
              <th class="w100">部门名称</th>
              <td>
                <span><?php echo $department->name; ?></span>
                <input id="edit_department_name_input" class="hidden form-control w150 inline">
                <a class="pointer ml10" onclick="editDepartmentName();">更改</a>
                <a class="pointer ml10 hidden" onclick="sendEditDepartmentName();">保存</a>
                <a class="pointer ml10 hidden" onclick="cancelEditDepartmentName();">取消</a>
              </td>
            </tr>
            <tr>
              <th class="w100">部门负责人</th>
              <td>
                <span><?php echo empty($department->leader) ? '未指定' :$department->leader->cn_name; ?></span>
                <?php if(!empty($users)): ?>
                <select class="form-control w150 inline hidden" id="edit_department_admin_select">
                  <?php foreach($users as $urow):?>
                  <option value="<?php echo $urow['user_id']?>"><?php echo $urow['cn_name']?></option>
                  <?php endforeach; ?>
                </select>
                <a class="pointer ml10" onclick="editDepartmentAdmin();">更改</a>
                <a class="pointer ml10 hidden" onclick="sendEditDepartmentAdmin();">保存</a>
                <a class="pointer ml10 hidden" onclick="cancelEditDepartmentAdmin();">取消</a>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th class="w100">上级部门</th>
              <td>
                <?php
                  if(!empty($department_id) && $department_id != 1){
                    if(!empty($result)){
                      $head_id = "";
                      $d_head_id = "0".$department_id;
                      foreach($result as $rrrow){
                        if($rrrow['id'] == $d_head_id){
                          $head_id = $rrrow['pId'];
                        }
                      }
                      foreach($result as $rrrow){
                        if($rrrow['id'] == $head_id) echo "<span>".$rrrow['name']."</span>";
                      }
                    }
                  }
                ?>
                <?php if(!empty($department_id) && $department_id != 1): ?>
                <select class="form-control w150 inline hidden" id="edit_department_head_select">
                  <?php if(!empty($result)): ?>
                  <?php foreach($result as $srow):?>
                  <?php if($srow['id'] != $d_head_id): ?>
                  <option value="<?php echo $srow['id']?>"><?php echo $srow['name']?></option>
                  <?php endif; ?>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <a class="pointer ml10" onclick="editDepartmentHead();">更改</a>
                <a class="pointer ml10 hidden" onclick="sendEditDepartmentHead();">保存</a>
                <a class="pointer ml10 hidden" onclick="cancelEditDepartmentHead();">取消</a>
                <?php else: ?>
                <span>无</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th class="w100">编制人数</th>
              <td><?php echo empty($formation_count) ? '0' :$formation_count; ?>人</td>
            </tr>
            <tr>
              <th class="w100">现有人数</th>
              <td><?php echo empty($department_count) ? '0':$department_count; ?>人</td>
            </tr>
            <tr>
              <th class="w100">缺编人数</th>
              <td><?php echo empty($lack_count) ? '0' : $lack_count; ?>人</td>
            </tr>
          </tbody>
        </table>

        <?php if(!empty($data)): ?>
        <h4 class="pd10 m0">职位编制
          <button class="btn btn-success fr pd3 mt-5" onclick="editFormation();"><span class="glyphicon glyphicon-edit"></span>&nbsp;修改编制</button>
        </h4>
        
        <table class="table-bordered table m0 center table-hover">
          <thead>
            <tr class="bg-fa">
              <th class="w200 center">职位</th>
              <th class="w100 center">在编人数</th>
              <th class="w100 center">现有人数</th>
              <th class="w100 center">缺编人数</th>
              <th class="center">在编人员</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($data as $d_key => $drow): ?>
            <tr <?php if($drow['lack_num'] > 0 || $drow['lack_num'] < 0)echo "class='bg-fa bold'";?>>
              <td><?php echo $d_key; ?></td>
              <td><?php echo $drow['num']; ?></td>
              <td><?php echo $drow['department_num']; ?></td>
              <td <?php if($drow['lack_num'] > 0 || $drow['lack_num'] < 0)echo "class='b2'";?>><?php echo $drow['lack_num']; ?></td>
              <td><?php echo $drow['list']; ?></td>
            </tr>
            <?php endforeach; ?>
            
          </tbody>
        </table>
        <?php else: ?>
        <h4 class="pd10 m0">没有职位编制信息
          <button class="btn btn-success fr pd3 mt-5" onclick="editFormation();"><span class="glyphicon glyphicon-edit"></span>&nbsp;修改编制</button>
        </h4>
        <?php endif; ?>
      </div>
      

      <?php elseif(!empty($department_id)): ?>
      <div class="m0a w400 center" id="logo-div">
        <img src="./images/logo_lg.png" class="w300 mt160" style="opacity:0.6;">
        <p class="f18px center mt20 b200">未找到编制信息，请重试</p>
      </div>
      <?php else: ?>
      <div class="m0a w400 center" id="logo-div">
        <img src="./images/logo_lg.png" class="w300 mt160" style="opacity:0.6;">
        <p class="f18px center mt20 b200">点击左侧部门名称，显示部门编制信息</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="clear"></div>
</div>

<!-- 新建部门模态框 -->
<div id="new-department-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">新建部门</h4>
    </div>
    <div class="modal-body">
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">部门名称</th>
            <td><input class="form-control" id="new_department_name_input"></td>
          </tr>
          <tr>
            <th class="w130">上级部门</th>
            <td>
              <select class="form-control" id="new_head_department_select">
                <?php if(!empty($result)): ?>
                <?php foreach($result as $rrow): ?>
                <option value="<?php echo $rrow['id']; ?>"><?php echo $rrow['name']; ?></option>
                <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100" onclick="sendNewDepartment();">提交</button>
    </div>
</div>

<!-- 删除部门模态框 -->
<div id="delete-department-div" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
      <a class="close" data-dismiss="modal">×</a>
      <h4 class="hint-title">删除部门</h4>
    </div>
    <div class="modal-body">
      <table class="table table-unbordered center m0">
        <tbody>
          <tr>
            <th class="w130">部门名称</th>
            <td>
              <select class="form-control" id="delete-department-select">
                <?php if(!empty($result)): ?>
                <?php foreach($result as $rrow): ?>
                <option value="<?php echo $rrow['id']; ?>"><?php echo $rrow['name']; ?></option>
                <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100" onclick="confirmDelete();">提交</button>
    </div>
</div>

<!-- 更改部门编制模态框 -->
<div id="department-formation-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
  <!-- 模态框头部 -->
  <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
    <a class="close" data-dismiss="modal">×</a>
    <h4 class="hint-title">更改部门编制</h4>
  </div>
  <!-- 模态框主体 -->
  <div class="modal-body">
    <ul class="nav nav-tabs" role="tablist">  
      <li role="presentation" class="active"><a class="pointer" onclick="switchFormation(this);" id="modify-init-btn">更改编制</a></li>
      <li role="presentation"><a class="pointer" onclick="switchFormation(this);">新增职位</a></li>
      <li role="presentation"><a class="pointer" onclick="switchFormation(this);">删除职位</a></li>
    </ul>

    <div id="modidy-formation-div" class="bor-l-1-ddd bor-b-1-ddd bor-r-1-ddd pd10">
      <label>职位名称：</label>
      <select class="inline form-control w150 mt10" id="newDepartment-select" onchange="newDepartmentChange();">
        <?php foreach($data as $f_key => $frow):?>
        <option value="<?php echo $frow['formation_id']; ?>"><?php echo $f_key;?></option>
        <?php endforeach; ?>
      </select>
      </br>
      <label>编制人数：</label>
      <input class="form-control inline w50 mt10" id="newDepartment-input">&nbsp;人
    </div>

    <div id="new-formation-div" class="hidden bor-l-1-ddd bor-b-1-ddd bor-r-1-ddd pd10">
      <label>职位名称：</label>
      <input class="form-control w200 inline" id="new-title-input">
      </br>
      <label>编制人数：</label>
      <input class="form-control inline w50 mt10" id="newFormation-input">&nbsp;人
    </div>

    <div id="delete-formation-div" class="hidden bor-l-1-ddd bor-b-1-ddd bor-r-1-ddd pd10">
      <label>职位名称：</label>
      <select class="inline form-control w150 mt10" id="deleteDepartment-select">
        <?php foreach($data as $ff_key => $ffrow): ?>
        <option value="<?php echo $ffrow['formation_id'];?>"><?php echo $ff_key; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    
  </div>
  <!-- 模态框底部 -->
  <div class="modal-footer">
    <button class="btn btn-success w100" id="modify-btn" onclick="sendNewFormation('modify');" >确定更改</button>
    <button class="btn btn-success w100 hidden" id="new-btn" onclick="sendNewFormation('new');" >确定</button>
    <button class="btn btn-success w100 hidden" id="delete-btn" onclick="sendDeleteFormation();" >确定</button>
  </div>

</div>

<!-- 查看编制按钮模态框 -->
<div id="total-formation-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
  <!-- 模态框头部 -->
  <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
    <a class="close" data-dismiss="modal">×</a>
    <h4 class="hint-title">公司编制</h4>
  </div>
  <!-- 模态框主体 -->
  <div class="modal-body">
    <table  class="center w500 m0a" style="line-height:40px; font-size:18px;">
      <tbody>
        <tr>
          <th class="w100 right">定编人数：</th>
          <td class="w80 left"><?php echo empty($total_formation_number) ? '0' : $total_formation_number; ?>人</td>
          <th class="right  w100">在编人数：</th>
          <td class="w80 left"><?php echo empty($total_user_number) ? '0' : $total_user_number; ?>人</td>
          <th class="right  w100">缺编人数：</th>
          <td class="w80 left"><?php echo (empty($total_formation_number) || empty($total_user_number)) ? '0' :$total_formation_number-$total_user_number; ?>人</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- js -->
<script type="text/javascript">
  // 显示总的编制
  function showTotalFormation(){
    var ySet = (window.innerHeight - $("#total-formation-div").height())/3;
    var xSet = (window.innerWidth - $("#total-formation-div").width())/2;
    $("#total-formation-div").css("top",ySet);
    $("#total-formation-div").css("left",xSet);
    $("#total-formation-div").modal({show:true});
  }

  // 编制数组初始化
  var formation_count_arr = new Array();
  <?php 
    if(!empty($data)){
      foreach($data as $krow){
        echo "formation_count_arr.push({'formation_id':'{$krow['formation_id']}','formation_count':'{$krow['num']}'});";
      }
    }
  ?>

  // 部门选择绑定事件
  function newDepartmentChange(){
    var formation_id = $("#newDepartment-select").val();
    $.each(formation_count_arr, function(){
      if(this['formation_id'] == formation_id) $("#newDepartment-input").val(this['formation_count']);
    });
  }

  // 确定更改
  function sendNewFormation(type){
    var department_id = "<?php echo empty($department_id) ? '0' : $department_id; ?>";
    if(type == "new"){
      var num = $("#newFormation-input").val();
      var title = $("#new-title-input").val();
    }else{
      var formation_id = $("#newDepartment-select").val();
      var title = "";
      $("#newDepartment-select").find("option").each(function(){
        if($(this).val() == formation_id) title = $(this).text();
      });
      if(title == " ") title = "";
      var num = $("#newDepartment-input").val();
    }
    var num_pattern = /^\d+$/;
    if(title == ""){
      showHint("提示信息","请输入职位的名称");
    }else if(num == ""){
      showHint("提示信息","请输入职位的编制人数");
    }else if(!num_pattern.exec(num)){
      showHint("提示信息","职位的人数格式输入错误");
    }else{
      $.ajax({
          type:'post',
          url: '/ajax/editFormation',
          dataType:'json',
          data:{'department_id':department_id ,'title':title, 'number':num},
          success:function(result){
              if(result.code == '0'){
                showHint("提示信息","更改编制成功！");
                setTimeout(function(){location.reload();},1200);
              }else if(result.code == '-1'){
                showHint("提示信息","更改编制失败");
              }else if(result.code == '-2'){
                  showHint("提示信息","参数错误");
              }else if(result.code == '-3'){
                  showHint("提示信息","找不到该职位");
              }else{
                  showHint("提示信息","你没有权限执行此操作");
              }
          }
      });
    }
  }

  // 发送删除编制
  function sendDeleteFormation(){
    var formation_id = $("#deleteDepartment-select").val();
    $.ajax({
      type:'post',
      url: '/ajax/deleteFormation',
      dataType:'json',
      data:{'formation_id':formation_id},
      success:function(result){
          if(result.code == '0'){
            showHint("提示信息","删除职位成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == '-1'){
            showHint("提示信息","删除职位失败");
          }else if(result.code == '-2'){
              showHint("提示信息","参数错误");
          }else if(result.code == '-3'){
              showHint("提示信息","找不到该职位");
          }else{
              showHint("提示信息","你没有权限执行此操作");
          }
      }
    });
  }


  // 修改编制标签切换
  function switchFormation(obj){
    var click_obj = $(obj).text();
    $(obj).parent().parent().find(".active").removeClass("active");
    $(obj).parent().addClass("active");
    switch(click_obj){
      case "更改编制":{
        $("#modidy-formation-div").removeClass("hidden");
        $("#new-formation-div").addClass("hidden");
        $("#delete-formation-div").addClass("hidden");
        $("#modify-btn").removeClass("hidden");
        $("#new-btn").addClass("hidden");
        $("#delete-btn").addClass("hidden");
        break;
      }
      case "新增职位":{
        $("#modidy-formation-div").addClass("hidden");
        $("#new-formation-div").removeClass("hidden");
        $("#delete-formation-div").addClass("hidden");
        $("#modify-btn").addClass("hidden");
        $("#new-btn").removeClass("hidden");
        $("#delete-btn").addClass("hidden");
        newTitle();
        break;
      }
      case "删除职位":{
        $("#modidy-formation-div").addClass("hidden");
        $("#new-formation-div").addClass("hidden");
        $("#delete-formation-div").removeClass("hidden");
        $("#modify-btn").addClass("hidden");
        $("#new-btn").addClass("hidden");
        $("#delete-btn").removeClass("hidden");
        deleteTitle();
        break;
      }
    }
  }

  // 修改编制
  function editFormation(){
    newDepartmentChange();
    var ySet = (window.innerHeight - $("#department-formation-div").height())/3;
    var xSet = (window.innerWidth - $("#department-formation-div").width())/2;
    $("#department-formation-div").css("top",ySet);
    $("#department-formation-div").css("left",xSet);
    $("#department-formation-div").modal({show:true});
  }

  // 取消修改部门负责人
  function cancelEditDepartmentAdmin(){
    $("#edit_department_admin_select").addClass("hidden");
    $("#edit_department_admin_select").prev().removeClass("hidden");
    $("#edit_department_admin_select").next().removeClass("hidden");
    $("#edit_department_admin_select").next().next().addClass("hidden");
    $("#edit_department_admin_select").next().next().next().addClass("hidden");
  }

  // 修改部门负责人
  function editDepartmentAdmin(){
    $("#edit_department_admin_select").focus();
    $("#edit_department_admin_select").val("<?php echo empty($department->leader) ? '':$department->leader->user_id; ?>");
    $("#edit_department_admin_select").removeClass("hidden");
    $("#edit_department_admin_select").prev().addClass("hidden");
    $("#edit_department_admin_select").next().addClass("hidden");
    $("#edit_department_admin_select").next().next().removeClass("hidden");
    $("#edit_department_admin_select").next().next().next().removeClass("hidden");
  }

  // 发送修改部门负责人
  function sendEditDepartmentAdmin(){
    var pId = "<?php echo empty($department_id) ? '0' : $department_id; ?>";
    var id = $("#edit_department_admin_select").val();
    $.ajax({
      type:'post',
      url: '/ajax/departmentAdmin',
      dataType: 'json',
      data:{'pId':pId,'id':id},
      success:function(result){
        if(result.code == 0)
        {
          showHint("提示信息","更改部门负责人成功！");
          setTimeout(function(){location.reload();},1200);
        }else if(result.code == -1){
          showHint("提示信息","更改部门负责人失败！");
        }else if(result.code == -2){
          showHint("提示信息","找不到该部门！");
        }else if(result.code == -3){
          showHint("提示信息","找不到该用户！");
        }else if(result.code == -4){
          showHint("提示信息","用户不属于该部门！");
        }else if(result.code == -99){
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 取消修改上级部门
  function cancelEditDepartmentHead(){
    $("#edit_department_head_select").addClass("hidden");
    $("#edit_department_head_select").prev().removeClass("hidden");
    $("#edit_department_head_select").next().removeClass("hidden");
    $("#edit_department_head_select").next().next().addClass("hidden");
    $("#edit_department_head_select").next().next().next().addClass("hidden");
  }

  // 修改上级部门
  function editDepartmentHead(){
    $("#edit_department_head_select").focus();
    $("#edit_department_head_select").val("<?php echo empty($head_id) ? '0':$head_id; ?>");
    $("#edit_department_head_select").removeClass("hidden");
    $("#edit_department_head_select").prev().addClass("hidden");
    $("#edit_department_head_select").next().addClass("hidden");
    $("#edit_department_head_select").next().next().removeClass("hidden");
    $("#edit_department_head_select").next().next().next().removeClass("hidden");
  }

  // 发送修改上级部门
  function sendEditDepartmentHead(){
    var id = "<?php echo empty($department_id) ? '0' : $department_id; ?>";
    var pId = parseInt($("#edit_department_head_select").val());
    var newname = "";
    $("#edit_department_head_select").children().each(function(){
      if($(this).val() == pId) newname = $(this).text();
    });
    $.ajax({
      type:'post',
        url: '/ajax/drag',
        dataType: 'json',
        data:{'id':id,'pId':pId,'type':'department'},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","更改上级部门成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","更改上级部门失败！");
          }else if(result.code == -2){
            showHint("提示信息","寻找不到该部门！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
    });
  }

  // 取消修改部门名称
  function cancelEditDepartmentName(){
    $("#edit_department_name_input").addClass("hidden");
    $("#edit_department_name_input").prev().removeClass("hidden");
    $("#edit_department_name_input").next().removeClass("hidden");
    $("#edit_department_name_input").next().next().addClass("hidden");
    $("#edit_department_name_input").next().next().next().addClass("hidden");
  }

  // 修改部门名称
  function editDepartmentName(){
    $("#edit_department_name_input").focus();
    $("#edit_department_name_input").val($("#edit_department_name_input").prev().text());
    $("#edit_department_name_input").removeClass("hidden");
    $("#edit_department_name_input").prev().addClass("hidden");
    $("#edit_department_name_input").next().addClass("hidden");
    $("#edit_department_name_input").next().next().removeClass("hidden");
    $("#edit_department_name_input").next().next().next().removeClass("hidden");
  }

  // 发送修改部门名称
  function sendEditDepartmentName(){
    var id = "<?php echo empty($department_id) ? '0' : $department_id; ?>";
    var newname = $("#edit_department_name_input").val();
    if(newname == ""){
      showHint("提示信息","请输入部门名称");
      $("#edit_department_name_input").focus();
    }else{
      $.ajax({
        type:'post',
        url: '/ajax/updateDepartment',
        dataType: 'json',
        data:{'id':id,'name':newname},
        success:function(result){
          if(result.code == 0)
          {
            showHint("提示信息","修改部门名称成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","修改部门名称失败！");
          }else if(result.code == -2){
            showHint("提示信息","找不到该部门！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
    
  }

  // 发送删除部门
  function sendDeleteDepartment(){
    $.ajax({
      type:'post',
        url: '/ajax/removeDepartment',
        dataType: 'json',
        data:{'id': delete_department_id},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","删除部门成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","删除部门失败！");
          }else if(result.code == -2){
            showHint("提示信息","查找不到该部门！");
          }else if(result.code == -3){
            showHint("提示信息","该部门还有员工，不能删除！");
          }else if(result.code == -4){
            showHint("提示信息","该部门有下属部门，不能删除！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
    });
  }

  var delete_department_id = 0;
  function confirmDelete(){
    var name = "";
    delete_department_id = $("#delete-department-select").val();
    $("#delete-department-select").children().each(function(){
      if($(this).val() == delete_department_id) name = $(this).text();
    });
    var remind_text = "确认要删除部门："+name+" ?";
    showConfirm("提示信息",remind_text, "确定","sendDeleteDepartment();", "取消");
  }

  // 删除部门
  function deleteDepartment(){
    var ySet = (window.innerHeight - $("#delete-department-div").height())/3;
    var xSet = (window.innerWidth - $("#delete-department-div").width())/2;
    $("#delete-department-div").css("top",ySet);
    $("#delete-department-div").css("left",xSet);
    $("#delete-department-div").modal({show:true});
  }

  // 发送新建部门
  function sendNewDepartment(){
    var pId = parseInt($("#new_head_department_select").val());
    var name = $("#new_department_name_input").val();
    if(name == ""){
      showHint("提示信息", "请输入部门名称");
      $("#new_department_name_input").focus();
    }else{
      $.ajax({
        type:'post',
        url: '/ajax/createDepartment',
        dataType: 'json',
        data:{'pId':pId, 'name':name},
        success:function(result){
          if(result.code == 0)
          {
            showHint("提示信息","新建部门成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","新建部门失败！");
          }else if(result.code == -2){
            showHint("提示信息","上级部门未找到！");
          }else if(result.code == -3){
            showHint("提示信息","部门名称重复！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 新建部门
  function newDepartment(){
    var ySet = (window.innerHeight - $("#new-department-div").height())/3;
    var xSet = (window.innerWidth - $("#new-department-div").width())/2;
    $("#new-department-div").css("top",ySet);
    $("#new-department-div").css("left",xSet);
    $("#new-department-div").modal({show:true});
  }

   // 部门数组初始化
  var departments = new Array();
  <?php 
    if(!empty($result)){
      foreach($result as $row){
        if($row['status'] == "display"){
          echo "departments.push({'id':'{$row['id']}', 'pId':'{$row['pId']}', 'name':'{$row['name']}'});";
        }
      }
    }
  ?>
  // 构建树
  var current_department_id = "0<?php echo empty($department_id) ? '': $department_id; ?>";
  $.each(departments, function(){
    if(this['pId'] != "00"){
      if($("#department-"+this['id']).length <= 0){
        if($("#department-"+this['pId']).parent().find("ul").text() == ""){
          $("#department-"+this['pId']).parent().append("<ul class='pl30'></ul>");
        }
        if(current_department_id == this['id']){
          var str = "<li><span id='department-"+this['id']+"' class='active'><p class='m0'><strong>"+this['name']+"</strong></p>"+
        "</span></li>";
        }else{
          var str = "<li><span id='department-"+this['id']+"'><p class='m0'><strong>"+this['name']+"</strong></p>"+
        "</span></li>";
        }
        
        $("#department-"+this['pId']).parent().find("ul").append(str);
      }
    }
  });

  // 页面初始化
  $(document).ready(function(){
    // 注册左边树的点击事件
    $("#tree-div").find("span").bind("click", function(){
      location.href = "/oa/formation/department_id/"+parseInt($(this).attr("id").split("-")[1]);
    });
  });
</script>
