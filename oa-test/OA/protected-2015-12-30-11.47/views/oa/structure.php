<?php
echo "<script type='text/javascript'>";
echo "console.log('structure');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.Jcrop.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/datepicker.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery.Jcrop.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
  <div class="w250 fl bor-r-1-ddd" style="min-height:732px;">
    <!-- 标题 -->
    <h4 class="bor-b-1-ddd pd10 m0"><strong>公司架构</strong></h4>
    <!-- 查找框 -->
    <div class="bor-b-1-ddd bg-fa pd5">
      <label class="ml5">查找：</label>
      <input class="w150" id="search-input">
      <button class="btn btn-success pd5 mt-2 w30" onclick="search();"><span class="mt-2 glyphicon glyphicon-search"></span></button>
    </div>
    <!-- 读取左边部门 -->
    <div class="tree pl20 oatree" id="tree-div">
      <?php if(!empty($departments)):?>
      <?php foreach($departments as $row):?>
      <?php if($row['pId'] == "00"): ?>
      <ul class="p00">
        <li>
          <span id="department-<?php echo $row['id'];?>">
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
  
  <!-- 显示员工信息 -->
  <div class="fr" style="width:828px;">
    <h4 class="bor-b-1-ddd pd10 m0"><strong>员工信息</strong><button class="mt-5 btn btn-success fr pd3" onclick="newEmployee();"><span class="glyphicon glyphicon-plus"></span>&nbsp;新增员工</button></h4>
    <div class="bor-b-1-ddd bg-fa pd5" style="height:39px;">
      <label class="ml5 pt4">部门总人数：<span id="department_num"></span></label>
      <label class="ml20 pt4">部门负责人：<a id="department_admin" class="pointer" title="点击查找负责人" onclick="searchAdmin();"></a></label>
    </div>
    <table class="table m0 hidden" id="table-th">
      <thead>
        <tr class="bg-fa">
          <th class="center w105">头像</th>
          <th class="center w130">个人资料</th>
          <th class="center w130">工作信息</th>
          <th class="center w130">联系方式</th>
        </tr>
      </thead>
    </table>
    <div id="detail-div" class="overflow-a">
    <div class="m0a w300" id="logo-div">
      <img src="./images/logo_lg.png" class="w300 mt160" style="opacity:0.6;">
      <p class="f18px center mt20 b200">点击左侧部门名称，显示员工信息</p>
    </div>
    
    <table class="table center" id="employee-div">
      <tbody>
        <tr>
          
        </tr>
      </tbody>
    </table>
    </div>
  </div>

  <div class="clear"></div>
</div>

<!-- 编辑员工信息模态框 -->
<div id="edit-employee-div" class="modal fade in hint bor-rad-5 w700" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">编辑员工信息</h4>
    </div>

    <div class="modal-body">
      <div class="w300 fl">
        <h4 class="pb10 m0"><strong>基本信息</strong></h4>
        <table class="table bor-r-1-ddd bor-l-1-ddd bor-b-1-ddd m0">
          <tr>
            <th class="w100">姓名：</th>
            <td class="hidden" id="edit_id_td"></td>
            <td><input id="edit_cn_name_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">英文名：</th>
            <td><input id="edit_en_name_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">性别：</th>
            <td>
              <select class="form-control" id="edit_sex_select">  
                <option value="m">男</option>
                <option value="f">女</option>
              </select> 
            </td>
          </tr>
          <tr>
            <th class="w100">出生日期：</th>
            <td><input id="edit_birthday_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">电话：</th>
            <td><input id="edit_mobile_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">email：</th>
            <td><input id="edit_email_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">QQ：</th>
            <td><input id="edit_qq_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">籍贯：</th>
            <td><input id="edit_native_place_input" class="form-control"></td>
          </tr>
        </table>
      </div>


      <div class="w300 fl ml50">
        <h4 class="pb10 m0"><strong>工作信息</strong></h4>
       <table class="table bor-r-1-ddd bor-l-1-ddd bor-b-1-ddd m0">
          <tr>
            <th class="w100">所属部门：</th>
            <td>
              <select class="form-control" id="edit_department_select" onchange="getTitle('edit');">
                <?php if(!empty($departments)): ?>
                <?php foreach($departments as $drow):?>
                <option value="<?php echo $drow['id']; ?>"><?php echo $drow['name']; ?></option>
                <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th class="w100">职位：</th>
            <td>
              <select class="form-control" id="edit_title_select"></select>
            </td>
          </tr>
          
          <tr>
            <th class="w100">入职日期：</th>
            <td><input id="edit_entry_date_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">转正日期：</th>
            <td><input id="edit_regularized_date_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">工作类型：</th>
            <td>
              <select id="edit_job_status_select" class="form-control">
                <option value="probation_employee">试用期</option>
                <option value="intern">实习生</option>
                <option value="formal_employee">正式员工</option>
              </select>
            </td>
          </tr>
          <tr>
            <th class="w100">职位描述：</th>
            <td><textarea id="edit_job_description_input" class="form-control"></textarea></td>
          </tr>
          <tr>
            <th class="w100">津贴：</th>
            <td><input id="allowance_input" class="form-control"></td>
          </tr>
        </table>
      </div>
      <div class="clear"></div>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-default w100 fl" onclick="deleteEmployee();">删除该员工</button>
      <button class="btn btn-success w100" onclick="sendEditEmployee();">提交</button>
    </div>
</div>

<!-- 新增员工模态框 -->
<div id="new-employee-div" class="modal fade in hint bor-rad-5 w700" style="display: none; ">
    <div class="modal-header bg-33 move"  onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">新增员工</h4>
    </div>

    <div class="modal-body">
      <div class="w300 fl">
        <h4 class="pb10 m0"><strong>基本信息</strong></h4>
        <table class="table bor-r-1-ddd bor-l-1-ddd bor-b-1-ddd m0">
          <tr>
            <th class="w100">姓名：</th>
            <td><input id="new_cn_name_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">英文名：</th>
            <td><input id="new_en_name_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">性别：</th>
            <td>
              <select class="form-control" id="new_sex_select">  
                <option value="m">男</option>
                <option value="f">女</option>
              </select> 
            </td>
          </tr>
          <tr>
            <th class="w100">出生日期：</th>
            <td><input id="new_birthday_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">域用户名：</th>
            <td><input id="new_login_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">电话：</th>
            <td><input id="new_mobile_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">email：</th>
            <td><input id="new_email_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">QQ：</th>
            <td><input id="new_qq_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">籍贯：</th>
            <td><input id="new_native_place_input" class="form-control"></td>
          </tr>
        </table>
      </div>


      <div class="w300 fl ml50">
        <h4 class="pb10 m0"><strong>工作信息</strong></h4>
       <table class="table bor-r-1-ddd bor-l-1-ddd bor-b-1-ddd m0">
          <tr>
            <th class="w100">所属部门：</th>
            <td>
              <select class="form-control" id="new_department_select" onchange="getTitle('new');">
                <?php if(!empty($departments)): ?>
                <?php foreach($departments as $drow):?>
                <option value="<?php echo $drow['id']; ?>"><?php echo $drow['name']; ?></option>
                <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th class="w100">职位：</th>
            <td>
              <select class="form-control" id="new_title_select"></select>
            </td>
          </tr>
          <tr>
            <th class="w100">入职日期：</th>
            <td><input id="new_entry_date_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">转正日期：</th>
            <td><input id="new_regularized_date_input" class="form-control"></td>
          </tr>
          <tr>
            <th class="w100">工作类型：</th>
            <td>
              <select id="new_job_status_select" class="form-control">
                <option value="probation_employee">试用期</option>
                <option value="intern">实习生</option>
                <option value="formal_employee">正式员工</option>
              </select>
            </td>
          </tr>
          <tr>
            <th class="w100">职位描述：</th>
            <td><textarea id="new_job_description_input" class="form-control"></textarea></td>
          </tr>
        </table>
      </div>
      <div class="clear"></div>
    </div>

    <div class="modal-footer" id="modal-footer">
      <button class="btn btn-success w100" onclick="sendNewEmployee();">提交</button>
    </div>
</div>

<!-- 修改头像模态框 -->
<div id="newhead-div" class="modal fade in hint bor-rad-5 w600" style="display: none; ">
  <!-- 模态框头部 -->
  <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
    <a class="close" data-dismiss="modal">×</a>
    <h4 class="hint-title">修改头像</h4>
  </div>
  <!-- 模态框主体 -->
  <div class="modal-body">
    <div class="w50 m0a hidden" id="loading-div">
      <img src="./images/loading.gif" class="h50 w50">
    </div>
    <div class="example hidden nh200" id="example">
      <span class="w100 inline-block">你上传的图片:</span>
      <span class="w100" style="margin-left:195px;">你截取的头像:</span>
        <img id="imgPre" src="" alt="[Jcrop Example]" onload="setImgInfo(this);">
        <div id="preview-pane">
          <div class="preview-container" style="overflow:hidden;width:100px;height:100px;margin-left:300px;">
            <img src="" class="jcrop-preview " id="imgPre2" alt="Preview">
          </div>
        </div>
    </div>
  </div>
  <!-- 模态框底部 -->
  <div class="modal-footer">
    <div class="fl">
      <input type="file" name="imgOne"  id="imgOne" onchange="reset();preImg(this.id,'imgPre');" />
      <p class="m0">(请选择大于100像素,小于2M,格式为jpg/png格式的图片)</p>
    </div>      
    <button class="btn btn-success w100 disabled fr" id="upload-btn" onclick="UploadFile();">上传</button>
    <div class="clear"></div>
  </div>
</div>

<?php //echo "<pre>";//var_dump($users);?>

<!-- js -->
<script type="text/javascript">
  // 查找部门负责人
  function searchAdmin(){
    $("#search-input").val($("#department_admin").text());
    search();
  }

  // 发送删除员工信息
  function sendDeleteEmployee(){
    $.ajax({
      type:'post',
      url: '/ajax/deleteuser',
      dataType: 'json',
      data:{'user_id':delete_id},
      success:function(result){
        if(result.code == 0)
        {
          showHint("提示信息","删除成功！");
          setTimeout(function(){location.reload();},1200);
        }else if(result.code == -1){
          showHint("提示信息","删除失败！");
        }else if(result.code == -2){
          showHint("提示信息","员工ID不正确！");
        }else if(result.code == -3){
          showHint("提示信息","没有找到该员工！");
        }else if(result.code == -4){
          showHint("提示信息","用户不属于该部门！");
        }else if(result.code == -99){
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 删除员工信息
  var delete_id = "";
  function deleteEmployee(){
    delete_id = $("#edit_id_td").text();
    var cn_name = $("#edit_cn_name_input").val();
    var remind_str = "是否删除 "+cn_name+" ?";
    showConfirm("提示信息", remind_str, "是", "sendDeleteEmployee()", "否");
  }

  // 发送修改员工信息
  function sendEditEmployee(){
    var id = $("#edit_id_td").text();
    var cn_name = $("#edit_cn_name_input").val();
    var en_name = $("#edit_en_name_input").val();
    var sex = $("#edit_sex_select").val();
    var title = $("#edit_title_select").val();
    var mobile = $("#edit_mobile_input").val();
    var email = $("#edit_email_input").val();
    var qq = $("#edit_qq_input").val();
    var native_place = $("#edit_native_place_input").val();
    var regularized_date = $("#edit_regularized_date_input").val();
    var job_description = $("#edit_job_description_input").val();
    var birthday = $("#edit_birthday_input").val();
    var entry_date = $("#edit_entry_date_input").val();
    var job_status = $("#edit_job_status_select").val();
    var login = $("#edit_login_input").val();
    var department_id = parseInt($("#edit_department_select").val());
    var allowance = $("#allowance_input").val();   //添加津贴

    var mobile_pattern = /^\d{1}\d{10}$/;
    var qq_pattern = /^\d+$/;
    var email_pattern = /^[\w\-\_\.]+\@[\w\-\_\.]+$/;
    var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
    
    if(cn_name==""){
      showHint("提示信息","姓名不能为空！");
      $("#new_cn_name_input").focus();
    }else if(!date_pattern.exec(birthday)){
      showHint("提示信息","出生日期格式不正确！");
    }else if(login == ""){
      showHint("提示信息","域用户名不能为空！");
      $("#new_login_input").focus();
    }else if(!mobile_pattern.exec(mobile)){
      showHint("提示信息","电话格式不正确！");
      $("#new_mobile_input").focus();
    }else if(!email_pattern.exec(email)){
      showHint("提示信息","email格式不正确！");
      $("#new_email_input").focus();
    }else if(!qq_pattern.exec(qq)){
      showHint("提示信息","QQ格式不正确！");
      $("#new_qq_input").focus();
    }else if(native_place==""){
      showHint("提示信息","籍贯不能为空！");
      $("#new_native_place_input").focus();
    }else if(job_description==""){
      showHint("提示信息","职位描述不能为空！");
      $("#new_job_description_input").focus();
    }else if(!date_pattern.exec(entry_date)){
      showHint("提示信息","入职日期格式不正确！");
    }else if(!date_pattern.exec(regularized_date)){
      showHint("提示信息","转正日期格式不正确！");
    }
    // else if( (allowance!="")&&(!qq_pattern.exec(allowance)) ){
    //   showHint("提示信息","津贴格式不正确！");
    //   $("#allowance_input").focus();
    // }
    else{
      $.ajax({
        type:'post',
        url: '/ajax/editUser',
        dataType: 'json',
        data:{'id':id, 'department_id':department_id,'entry_day':entry_date,'birthday':birthday,'job_description':job_description,'regularized_date':regularized_date, 'native_place':native_place, 'job_status':job_status, 'login':login, 'cn_name':cn_name, 'en_name':en_name, 'sex':sex, 'title':title, 'mobile':mobile, 'email':email, 'qq':qq},
        success:function(result){
          if(result.code == 0)
          {
            showHint("提示信息","修改员工信息成功！");
            setTimeout(function(){location.reload();},1200);
          }else if(result.code == -1){
            showHint("提示信息","修改员工信息失败！");
          }else if(result.code == -2){
            showHint("提示信息","找不到该员工！");
          }else if(result.code == -3){
            showHint("提示信息","信息不能为空！");
          }else if(result.code == -4){
            showHint("提示信息","性别错误！");
          }else if(result.code == -5){
            showHint("提示信息","职位状态错误！");
          }else if(result.code == -6){
            showHint("提示信息","转正日期格式错误！");
          }else if(result.code == -7){
            showHint("提示信息","邮件格式错误！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 修改员工信息
  var newheaddivlock = 0;
  function editEmployee(obj){
    if(newheaddivlock == 0){
      if($(obj).find("td.id-td").text() == ""){
        if($(obj).prev().find("td.id-td").text() == ""){
          var id_td = $(obj).prev().prev().find("td.id-td");
        }else{
          var id_td = $(obj).prev().find("td.id-td");
        }
      }else{
        var id_td = $(obj).find("td.id-td");
      }
      var id = id_td.text();
      if(id_td.next().next().find("strong").text().indexOf("-") > -1){
        var cn_name = id_td.next().next().find("strong").text().split("-")[1];
        var en_name = id_td.next().next().find("strong").text().split("-")[0];
      }else{
        var cn_name = id_td.next().next().find("strong").text();
        var en_name = "";
      }
      if(id_td.next().next().find("span").hasClass("blue")){
        var sex = "m";
      }else{
        var sex = "f";
      }
      var title = id_td.next().next().next().text();
      var email = id_td.next().next().next().next().text();
      var birthday = id_td.parent().next().children().first().text().split(" ")[0];
      var entry_date = id_td.parent().next().children().first().next().text().split(" ")[0];
      var qq = id_td.parent().next().children().first().next().next().text();
      var native_place = id_td.parent().next().next().children().first().text();
      if(id_td.parent().next().next().children().first().next().text() == "正式员工"){
        var job_status = "formal_employee";
      }else if(id_td.parent().next().next().children().first().next().text() == "试用期"){
        var job_status = "probation_employee";
      }else{
        var job_status = "intern";
      }
      var mobile = id_td.parent().next().next().children().first().next().next().text();
      var job_description = id_td.parent().next().next().children().first().next().next().next().text();
      var regularized_date = id_td.parent().next().next().children().first().next().next().next().next().text();
      var department_id = "0"+id_td.parent().next().next().children().first().next().next().next().next().next().text();

      $("#edit_id_td").text(id);
      $("#edit_sex_select").val(sex);
      $("#edit_cn_name_input").val(cn_name);
      $("#edit_en_name_input").val(en_name);
      $("#edit_birthday_input").val(birthday);
      
      $("#edit_email_input").val(email);
      $("#edit_entry_date_input").val(entry_date);
      $("#edit_qq_input").val(qq);
      $("#edit_native_place_input").val(native_place);
      $("#edit_job_status_select").val(job_status);
      $("#edit_mobile_input").val(mobile);
      $("#edit_regularized_date_input").val(regularized_date);
      $("#edit_job_description_input").val(job_description);
      $("#edit_department_select").val(department_id);

      var ySet = (window.innerHeight - $("#edit-employee-div").height())/3;
      var xSet = (window.innerWidth - $("#edit-employee-div").width())/2;
      $("#edit-employee-div").css("top",ySet);
      $("#edit-employee-div").css("left",xSet);
      $("#edit-employee-div").modal({show:true});
      getTitle('edit');
      setTimeout(function(){$("#edit_title_select").val(title)}, 200);
    }
  }

  // 发送新增员工信息
  function sendNewEmployee(){
    var id = parseInt($("#new_department_select").val());
    var cn_name = $("#new_cn_name_input").val();
    var en_name = $("#new_en_name_input").val();
    var sex = $("#new_sex_select").val();
    var title = $("#new_title_select").val();
    var mobile = $("#new_mobile_input").val();
    var email = $("#new_email_input").val();
    var qq = $("#new_qq_input").val();
    var native_place = $("#new_native_place_input").val();
    var regularized_date = $("#new_regularized_date_input").val();
    var job_description = $("#new_job_description_input").val();
    var birthday = $("#new_birthday_input").val();
    var entry_date = $("#new_entry_date_input").val();
    var job_status = $("#new_job_status_select").val();
    var login = $("#new_login_input").val();

    var mobile_pattern = /^\d{1}\d{10}$/;
    var qq_pattern = /^\d+$/;
    var email_pattern = /^[\w\-\_\.]+\@[\w\-\_\.]+$/;
    var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
    
    if(cn_name==""){
      showHint("提示信息","姓名不能为空！");
      $("#new_cn_name_input").focus();
    }else if(!date_pattern.exec(birthday)){
      showHint("提示信息","出生日期格式不正确！");
    }else if(login == ""){
      showHint("提示信息","域用户名不能为空！");
      $("#new_login_input").focus();
    }else if(!mobile_pattern.exec(mobile)){
      showHint("提示信息","电话格式不正确！");
      $("#new_mobile_input").focus();
    }else if(!email_pattern.exec(email)){
      showHint("提示信息","email格式不正确！");
      $("#new_email_input").focus();
    }else if(!qq_pattern.exec(qq)){
      showHint("提示信息","QQ格式不正确！");
      $("#new_qq_input").focus();
    }else if(native_place==""){
      showHint("提示信息","籍贯不能为空！");
      $("#new_native_place_input").focus();
    }else if(job_description==""){
      showHint("提示信息","职位描述不能为空！");
      $("#new_job_description_input").focus();
    }else if(!date_pattern.exec(entry_date)){
      showHint("提示信息","入职日期格式不正确！");
    }else if(!date_pattern.exec(regularized_date)){
      showHint("提示信息","转正日期格式不正确！");
    }else{
      $.ajax({
        type:'post',
        url: '/ajax/createUser',
        dataType: 'json',
        data:{'pId':id, 'entry_day':entry_date,'birthday':birthday,'job_description':job_description,'regularized_date':regularized_date, 'native_place':native_place, 'job_status':job_status, 'login':login, 'cn_name':cn_name, 'en_name':en_name, 'sex':sex, 'title':title, 'mobile':mobile, 'email':email, 'qq':qq},
        success:function(result){
          if(result.code == 0)
          {
            showHint("提示信息","新增员工成功！");
            setTimeout(function(){location.href="/oa/structure"},1200);
          }else if(result.code == -1){
            showHint("提示信息","新增员工失败！");
          }else if(result.code == -2){
            showHint("提示信息","域用户名重复！");
          }else if(result.code == -3){
            showHint("提示信息","信息不能为空！");
          }else if(result.code == -4){
            showHint("提示信息","性别错误！");
          }else if(result.code == -5){
            showHint("提示信息","职位状态错误！");
          }else if(result.code == -6){
            showHint("提示信息","转正日期错误！");
          }else if(result.code == -7){
            showHint("提示信息","请输入正确的email！");
          }else if(result.code == -99){
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }

  // 根据部门获取职位
  function getTitle(id){
    var d_id = parseInt($("#"+id+"_department_select").val());
    $.ajax({
      type:'post',
      url: '/ajax/getTitleByDepartment',
      dataType: 'json',
      data:{'id':d_id},
      success:function(result){
        if(result.code == 0)
        {
          $("#"+id+"_title_select").children().remove();
            $.each(result['titles'],function(){
              var str = "<option value='"+this['title']+"'>"+this['title']+"</option>";
              $("#"+id+"_title_select").append(str);
            });
          }
          else{
          $("#"+id+"_title_select").children().remove();
        }
      }
    });
  }

  // 新增员工
  function newEmployee(){
    var ySet = (window.innerHeight - $("#new-employee-div").height())/3;
    var xSet = (window.innerWidth - $("#new-employee-div").width())/2;
    $("#new-employee-div").css("top",ySet);
    $("#new-employee-div").css("left",xSet);
    $("#new-employee-div").modal({show:true});
    getTitle('new');
  }

  // 搜索
  function search(){
    var search_str = $("#search-input").val();
    var find_tag = 0;
    var department_id = "";
    var name = "";
    $.each(arr_users, function(){
      if(this['name'].indexOf(search_str) > -1){
        department_id = this['pId'];
        if(this['name'].indexOf("-") > -1){
          name = this['name'].split("-")[1];
        }else{
          name = this['name'];
        }
        find_tag = 1;
        return false;
      }
    });
    if(find_tag == 0){
      showHint("提示信息","不存在的用户");
    }else{
      $("#department-"+department_id).click();
      setTimeout(function(){
        $("#employee-div").find("strong").each(function(){
          if($(this).text().indexOf(name) > -1){
            // 滑动到相应位置
            var height = $(this).parent().parent().offset().top;
            var detail_height = $("#detail-div").offset().top;
            var current_height = $("#detail-div").scrollTop();
            $("#detail-div").animate({
              scrollTop: height-detail_height+current_height
            });
          }
        });
      },600);
    }
  }

  // 用户数组初始化
  var arr_users = new Array();
  var arr_name = new Array();
  <?php 
    if(!empty($users)){
      foreach($users as $urow){
        echo "arr_users.push({'id':'{$urow['id']}', 'name':'{$urow['name']}', 'pId':'{$urow['pId']}'});";
        echo "arr_name.push('{$urow['name']}');";
      }
    }
  ?>

  // 部门数组初始化
  var departments = new Array();
  <?php 
    if(!empty($departments)){
      foreach($departments as $row){
        if($row['status'] == "display"){
          $row['count'] = empty($row['count']) ? 0 : $row['count'];
          echo "departments.push({'id':'{$row['id']}', 'pId':'{$row['pId']}', 'name':'{$row['name']}', 'count':'{$row['count']}', 'admin_name':'{$row['admin_name']}', 'admin':'{$row['admin']}'});";
        }
      }
    }
  ?>
  // 构建树
  $.each(departments, function(){
    if(this['pId'] != "00"){
      if($("#department-"+this['id']).length <= 0){
        if($("#department-"+this['pId']).parent().find("ul").text() == ""){
          $("#department-"+this['pId']).parent().append("<ul class='pl30'></ul>");
        }
        var str = "<li><span id='department-"+this['id']+"'><p class='m0'><strong>"+this['name']+"</strong></p>"+
        "</span></li>";
        $("#department-"+this['pId']).parent().find("ul").append(str);
      }
    }
  });


  $(document).ready(function(){
    // 给左侧树结点注册事件
    $("#tree-div").find("span").bind("click", function(){
      $("#tree-div").find("span").removeClass("active");
      $(this).addClass("active");
      getInfo(this);
    });

    $("#detail-div").css("max-height", $("#tree-div").height()-4);

    $("#search-input").autocomplete({
      source: arr_name
    });

    $("#search-input").focus();

    $('#new_entry_date_input').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#new_regularized_date_input").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#new_birthday_input").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $('#edit_entry_date_input').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#edit_regularized_date_input").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
    $("#edit_birthday_input").datepicker({dateFormat: 'yy-mm-dd',changeYear: true});

    document.onkeydown = function(e){
        if(!e) e = window.event;//火狐中是 window.event
        if((e.keyCode || e.which) == 13) search();
    }
  });
  
  // 获取部门内员工的信息
  function getInfo(obj){
    var id = $(obj).attr('id').split("department-")[1];
    var num = "";
    var admin_name = "";
    var admin = "";
    $.each(departments, function(){
      if(this['id'] == id){
        num = this['count'] +" 人";
        admin = this['admin'];
        admin_name = this['admin_name'];
        return false;
      }
    });
    $("#department_num").text(num);
    $("#department_admin").text(admin_name);
    $.ajax({
      type:'post',
      url: '/ajax/getInfo',
      dataType: 'json',
      data:{'id':id,'type':'department'},
      success:function(result){
        if(result.code == 0){
          $("#logo-div").addClass("hidden");
          $("#table-th").removeClass("hidden");
          $("#employee-div").find("tbody").children().remove();
          $.each(result['result'], function(key, value){
            if(value.en_name == ""){
              var en_name_str = "";
            }else{
              var en_name_str = value.en_name+"-";
            }
            if(value.sex == "m"){
              var sex_str = "♂";
              var sex_class= "blue";
            }else{
              var sex_str = "♀";
              var sex_class = "b2";
            }
            if(value.job_status == "formal_employee"){
              var job_status_str = "正式员工";
              var job_bg = '';
            }else if(value.job_status == "intern"){
              var job_status_str = "<strong>实习生</strong>";
              var job_bg = 'bg-fa';
            }else{
              var job_status_str = "<strong>试用期</strong>";
              var job_bg = 'bg-fa';
            }
            if(admin == value.id){
              var img_class = "bor-5-orange";
            }else{
              var img_class = "";
            }

              var str = "<tr class='pointer "+job_bg+"' title='点击修改资料' onclick='editEmployee(this);'><td class='hidden id-td'>"+value.id+"</td><td rowspan='3' class='w100'><img src='"+value.photo+"' title='修改头像' class='h100 w100 pointer "+img_class+"' onmouseover='newheaddivlock = 1;' onmouseout='newheaddivlock = 0;' onclick='changeHead(this);'></td>"+
                "<td class='w130'><strong>"+en_name_str+value.name+"</strong>&nbsp;<span class='"+sex_class+"'>"+sex_str+"</span></td>"+
                "<td class='w130'>"+value.title+"</td>"+
                "<td class='w130'>"+value.email+"</td></tr>"+
              "<tr class='pointer "+job_bg+"' title='点击修改资料' onclick='editEmployee(this);'><td class='w130'>"+value.birthday+" 出生</td>"+
                "<td class='w130'>"+value.entry_day+" 入职</td>"+
                "<td class='w130'>"+value.qq+"</td></tr>"+
              "<tr class='pointer "+job_bg+"' title='点击修改资料' onclick='editEmployee(this);'><td class='w130'>"+value.native_place+"</td>"+
                "<td class='w130'>"+job_status_str+"</td>"+
                "<td class='w130'>"+value.mobile+"</td><td class='hidden'>"+value.job_description+"</td><td class='hidden'>"+value.regularized_date+"</td><td class='hidden'>"+value.department_id+"</td></tr>"+
                "<td colspan='4'></td>";

              $("#employee-div").find("tbody").append(str);
          });
        }else if(result.code == -1){
          showHint("提示信息","未找到该部门人员信息！");
          $("#logo-div").removeClass("hidden");
          $("#table-th").addClass("hidden");
          $("#employee-div").find("tbody").children().remove();
        }else if(result.code == -99){
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }


// 修改头像
var changehead_id = "";
  function changeHead(obj){
    changehead_id = $(obj).parent().prev().text();
    $('#editEmployee-div').modal('hide');
    var ySet = (window.innerHeight - $("#newhead-div").height())/3;
    var xSet = (window.innerWidth - $("#newhead-div").width())/2;
    $("#newhead-div").css("top",ySet);
    $("#newhead-div").css("left",xSet);
    $('#newhead-div').modal({show:true});
  }

var jcrop_api;
var xLoc;
var yLoc;
var wSize;
var boundx,boundy;
//初始化截取框
function loadJcrop(){
  // Create variables (in this scope) to hold the API and image size

  
  // Grab some information about the preview pane
  var $preview = $('#preview-pane'),
      $pcnt = $('#preview-pane .preview-container'),
      $pimg = $('#preview-pane .preview-container img'),
  
  xsize = $pcnt.width(),
  ysize = $pcnt.height();

  $('#imgPre').Jcrop({
    onChange: updatePreview,
    onSelect: getSize,
    aspectRatio: xsize / ysize
  },function(){
    // Store the API in the jcrop_api variable
    jcrop_api = this;

    // Use the API to get the real image size
    var bounds = this.getBounds();
    boundx = real_width;
    boundy = real_height;
    
    // Move the preview into the jcrop container for css positioning
    $preview.appendTo(jcrop_api.ui.holder);
  });

  //更新预览
  function updatePreview(c){
    if (parseInt(c.w) > 0) {
      var rx = real_width/250;
      var ry = real_width/250;
      
      var pic_width = (c.w/250) * real_width;
      var pic_height = (c.w/250) * real_height;

      $pimg.css({
        width: Math.round(250*100/c.w) + 'px',
        height: Math.round(real_height/rx*100/c.w) + 'px',
        marginLeft: '-' + Math.round(c.x *100/c.w) + 'px',
        marginTop: '-' + Math.round(c.y *100/c.w) + 'px'
      });
    }
  };

  //获取选框数据
  function getSize(c){
    xLoc = c.x;
    yLoc = c.y;
    wSize = c.w;
  }
};
 
var changeFlag = 0;
function changeImg(){
  if(changeFlag == 0){
    $(".jcrop-holder").find("img").attr("src",$("#imgPre").attr("src"));
    $(".jcrop-holder").attr("width",250);
    $(".jcrop-holder").attr("height",real_height*250/real_width);
    changeFlag = 1;
  }
}
//获取图片真实高度和宽度
var real_width;
var real_height;
var percent;
var imgObj;
function setImgInfo(obj){
  $("#loading-div").addClass("hidden");
  imgObj = new Image();
  imgObj.src = obj.src;
  real_width = imgObj.width;
  real_height = imgObj.height;

  percent = real_width/250;

  //检测图像是否大于100像素
  if(real_width<100||real_height<100){
    showHint("提示信息","请选择像素大于100的照片！");
    $("#example").addClass("hidden");
    $("#upload-btn").addClass("disabled");
    $("#imgPre").attr("src","");
    $("#imgPre2").attr("src","");
    return false;
  }else{
    $("#upload-btn").removeClass("disabled");
    $("#example").removeClass("hidden");
  }
  loadJcrop();
  changeImg();
}

// 初始化预览区
var reset_flag = 0;
function reset(){
  if(reset_flag == 1){
    $("#newhead-div").find(".modal-body").children().remove();
    var str = "<div class='w50 m0a hidden' id='loading-div'>"+
      "<img src='./images/loading.gif' class='h50 w50'>"+
    "</div><div class='example hidden nh200' id='example'>"+
    "<span class='w100 inline-block'>你上传的图片:</span>"+
    "<span class='w100' style='margin-left:195px;'>你截取的头像:</span>"+
    "<img id='imgPre' src='' alt='[Jcrop Example]' onload='setImgInfo(this);'>"+
    "<div id='preview-pane'>"+
    "<div class='preview-container' style='overflow:hidden;width:100px;height:100px;margin-left:300px;'>"+
    "<img src='' class='jcrop-preview' id='imgPre2' alt='Preview'>"+
    "</div></div></div>";
    $("#newhead-div").find(".modal-body").append(str);
  }
}


//图片预览设置
function preImg(sourceId, targetId) { 
  if(document.getElementById(sourceId).files[0].type.indexOf("image") < 0){
    showHint("提示信息","请选择jpg或png或gif格式的图片");
  }else{
    //先隐藏，判断图片大于100像素才显示
    $("#example").addClass("hidden");

    //浏览器支持的判断
    if (typeof FileReader === 'undefined') {  
        alert('Your browser does not support FileReader...');  
        return;  
    }  
    var reader = new FileReader();  

    reader.onload = function(e) {  
      //给预览图src赋值
      var src = this.result;
      var img = document.getElementById(targetId);
      var img2 = document.getElementById(targetId+"2");   
      img.src = src; 
      img2.src = src;   
    }  
    reader.readAsDataURL(document.getElementById(sourceId).files[0]); 

    //设置大预览图的宽度 
    $("#imgPre").attr("width","250");

    changeFlag = 0;
    reset_flag = 1;
    $("#loading-div").removeClass("hidden");
  }
}  

//上传头像
function UploadFile() {
  
  if(real_width<100||real_height<100){
    showHint("提示信息","请选择像素大于100的照片！");
    return false;
  }else{
    var fileObj = document.getElementById("imgOne").files[0]; // 获取文件对象
    var FileController = "/ajax/uploadPic";                    // 接收上传文件的后台地址 
    // FormData 对象
    var form = new FormData();
    var x = xLoc*percent;
    var y = yLoc*percent;
    var w = wSize*percent;
    var user_id = changehead_id;
    form.append("x", x);
    form.append("y", y);
    form.append("width", w);
    form.append("upload_head", fileObj);
    form.append("user_id", user_id);                           // 文件对象

    // XMLHttpRequest 对象
    var xhr = new XMLHttpRequest();
    xhr.open("post", FileController, true);
    xhr.onload = function () {
        // showHint("提示信息","上传成功");
    };
    xhr.send(form);

    xhr.onreadystatechange=function(){
      if (xhr.readyState==4 && xhr.status==200){
          var code = xhr.responseText;
          if(code == 0){
            showHint("提示信息","上传成功！");
            setTimeout(function(){location.reload();},1000);
          }else if(code == -1){
            showHint("提示信息","上传失败！");
          }else if(code == -2){
            showHint("提示信息","参数错误！");
          }else if(code == -3){
            showHint("提示信息","找不到该用户！");
          }else if(code == -4){
            showHint("提示信息","格式错误！");
          }else if(code == -5){
            showHint("提示信息","图片大小超过2M！");
          }else{
            showHint("提示信息","你没有权限执行此操作！");
          }
      }
    }
  }
}
</script>  
  
