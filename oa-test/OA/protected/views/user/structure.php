<?php
echo "<script type='text/javascript'>";
echo "console.log('structure');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<div class="bor-1-ddd">
  <!-- 左侧栏 -->
  <div class="w350 fl bor-r-1-ddd">
    <!-- 标题 -->
    <h4 class="bor-b-1-ddd pd10 m0">
      <strong>公司架构</strong>
    </h4>
    <!-- 搜索框 -->
    <div class="bor-b-1-ddd bg-fa pd5">
      <label class="ml5">查找：</label>
      <input class="w200" id="search-input">
      <button class="btn btn-success pd5 mt-2 w30" onclick="search();"><span class="mt-2 glyphicon glyphicon-search"></span></button>
    </div>
    <!-- 左侧部门 -->
    <div class="tree pl10 " id="tree-div">
      <?php if(!empty($departments)):?>
      <?php foreach($departments as $row):?>
      <?php if($row['pId'] == "00"): ?>
      <ul class="p00">
        <li>
          <span id="department-<?php echo $row['id'];?>">
            <p class="bor-b-1-ddd f16px m0"><strong><?php echo $row['name'];?></strong></p>
            <p class="m0">现有:<?php echo $row['count'];?>人&nbsp;编制:<?php echo $row['formation_count'];?>人&nbsp;<?php if($row['lack_count'] > 0){echo "<strong class='m0'>缺编:".$row['lack_count'].'人</strong>';}else if($row['lack_count'] < 0){echo "<strong class='m0'>超编:".explode('-', $row['lack_count']).'人</strong>';}?></p>
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
  <!-- 员工信息 -->
  <div class="fr" style="width:948px;min-height:800px;">
    <!-- 标题 -->
    <h4 class="bor-b-1-ddd pd10 m0">
      <strong>员工信息</strong>
    </h4>
    <!-- 概览信息 -->
    <div class="bor-b-1-ddd bg-fa pd5" style="height:39px;">
      <!-- <label id='department_or_total' class="ml5 pt4">总人数：<span id="department_num">23</span></label> -->
      <label id='department_or_total' class="ml5 pt4">总人数：</label> <label id="department_num"><?php echo count($first_users);?></label>
      <label id='departmen_or_ceo' class="ml20 pt4"></label> <a id="department_admin" class="pointer" title="点击查找负责人" onclick="searchAdmin();"></a>
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
      <table class="table center" id="employee-div">
        <tbody>
          <?php if(!empty($first_users)): ?>
          <?php foreach($first_users as $frow) : ?>
          <?php 
            $en_name_str = (empty($frow['en_name'])) ? '': "-{$frow['en_name']}";
            $sex_str = ($frow['gender'] == "m") ? "♂" : "♀";
            $sex_class = ($frow['gender'] == "m") ? "blue" : "b2";
            $job_status_str = "";
            $job_bg = "";
            if($frow['job_status'] == "formal_employee"){
              $job_status_str = "正式员工";
              $job_bg = "";
            }else if($frow['job_status'] == "intern"){
              $job_status_str = "<strong>实习生</strong>";
              $job_bg = "bg-240";
            }else{
              $job_status_str = "<strong>试用期</strong>";
              $job_bg = "bg-240";
            }
            $img_class = "";
            foreach ($departments as $drow) {
              if($drow['admin'] == $frow['user_id']){
                $img_class = "bor-5-orange";
              }
            }
          ?>
          <tr class='<?php echo $job_bg;?>'><td rowspan='3' class='w100'><img src='<?php echo $frow['photo'];?>' class='h100 w100 <?php echo $img_class;?>'></td>
            <td class='w130'><strong><?php echo $frow['cn_name'].$en_name_str;?></strong>&nbsp;<span class='"+sex_class+"'><?php echo $sex_str; ?></span></td>
            <td class='w130'><?php echo $frow['title']; ?></td>
            <td class='w130'><?php echo $frow['email']; ?></td></tr>
          <tr class='<?php echo $job_bg;?>' ><td class='w130'><?php echo $frow['birthday']; ?> 出生</td>
            <td class='w130'><?php echo $frow['entry_day']; ?> 入职</td>
            <td class='w130'><?php echo $frow['qq']; ?></td></tr>
          <tr class='<?php echo $job_bg;?>'><td class='w130'><?php echo $frow['native_place']; ?></td>
            <td class='w130'><?php echo $job_status_str; ?></td>
            <td class='w130'><?php echo $frow['mobile']; ?></td></tr>
          <td colspan='4'></td>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="clear"></div>
</div>

<!-- js -->
<script type="text/javascript">
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

  // 搜索
  function search(){
    var search_str = $("#search-input").val();
    var find_tag = 0; // 查找标记
    var department_id = "";
    var name = "";

    // 判断是否存在
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

    // 判断是否存在
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

  // 部门数组初始化
  var departments = new Array();
  <?php 
    if(!empty($departments)){
      foreach($departments as $row){
        if($row['status'] == "display"){
          $row['count'] = empty($row['count']) ? 0 : $row['count'];
          echo "departments.push({'id':'{$row['id']}', 'pId':'{$row['pId']}', 'name':'{$row['name']}','count':'{$row['count']}','formation_count':'{$row['formation_count']}', 'lack_count':'{$row['lack_count']}', 'admin_name':'{$row['admin_name']}', 'admin':'{$row['admin']}'});";
        }
      }
    }
  ?>

  // 查找部门负责人
  function searchAdmin(){
    $("#search-input").val($("#department_admin").text());
    search();
  }

  // 构建树
  $.each(departments, function(){
//    if(this['pId'] != "00" && this['count'] != "0"){
    if(this['pId'] != "00"){
      if($("#department-"+this['id']).length <= 0){
        if($("#department-"+this['pId']).parent().find("ul").text() == ""){
          $("#department-"+this['pId']).parent().append("<ul class='pl30'></ul>");
        }
        if(parseInt(this['lack_count']) > 0){
          var lack_count_str = "<strong class='m0'>缺编:"+this['lack_count']+"人</strong>";
        }else if(parseInt(this['lack_count']) < 0){
          var lack_count_str = "<strong class='m0'>缺编:"+this['lack_count'].split("-")[1]+"人</strong>";
        }else{
          var lack_count_str = "";
        }
        var str = "<li><span id='department-"+this['id']+"'><p class='bor-b-1-ddd f16px m0'><strong>"+this['name']+"</strong></p>"+
        "<p class='m0'>现有:"+this['count']+"人&nbsp;编制:"+this['formation_count']+"人&nbsp;"+lack_count_str+"</p></span></li>";
        $("#department-"+this['pId']).parent().find("ul").append(str);
      }
    }
  });

  // 页面初始化
  $(document).ready(function(){
    // 左侧树注册事件
    $("#tree-div").find("span").bind("click", function(){
      $("#tree-div").find("span").removeClass("active");
      $(this).addClass("active");
      getInfo(this);
    });

    // 设置员工详情板块高度
    $("#detail-div").css("max-height", $("#tree-div").height()-30);

    // 自动补全
    $("#search-input").autocomplete({
      source: arr_name
    });

    // 自动聚焦
    $("#search-input").focus();

    // 绑定回车键
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

    // 填充部门信息
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
    $("#department_or_total").text('部门总人数：');
    $("#departmen_or_ceo").text('部门负责人：');

    // 获取部门内员工的信息
    $.ajax({
      type:'post',
      url: '/ajax/getInfo',
      dataType: 'json',
      data:{'id':id,'type':'department'},
      success:function(result){
        if(result.code == 0){
          // 清空员工信息
          $("#employee-div").find("tbody").children().remove();

          // 填充员工信息
          $.each(result['result'], function(key, value){
            if(value.en_name == ""){
              var en_name_str = "";
            }else{
              var en_name_str = "-"+value.en_name;
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
              var job_bg = 'bg-240';
            }else{
              var job_status_str = "<strong>试用期</strong>";
              var job_bg = 'bg-240';
            }
            if(admin == value.id){
              var img_class = "bor-5-orange";
            }else{
              var img_class = "";
            }
              var str = "<tr class='"+job_bg+"'><td rowspan='3' class='w100'><img src='"+value.photo+"' class='h100 w100 "+img_class+"'></td>"+
                "<td class='w130'><strong>"+value.name+en_name_str+"</strong>&nbsp;<span class='"+sex_class+"'>"+sex_str+"</span></td>"+
                "<td class='w130'>"+value.title+"</td>"+
                "<td class='w130'>"+value.email+"</td></tr>"+
              "<tr class='"+job_bg+"' ><td class='w130'>"+value.birthday+" 出生</td>"+
                "<td class='w130'>"+value.entry_day+" 入职</td>"+
                "<td class='w130'>"+value.qq+"</td></tr>"+
              "<tr class='"+job_bg+"'><td class='w130'>"+value.native_place+"</td>"+
                "<td class='w130'>"+job_status_str+"</td>"+
                "<td class='w130'>"+value.mobile+"</td></tr>"+
                "<td colspan='4'></td>";

              $("#employee-div").find("tbody").append(str);
          });
        }else if(result.code == -1){
            showHint("提示信息","获取部门信息失败！");
        }else if(result.code == -99){
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }
</script>
  
