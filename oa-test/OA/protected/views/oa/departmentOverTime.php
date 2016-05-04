<?php
echo "<script type='text/javascript'>";
echo "console.log('departmentOverTime');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<div>
  <!-- 标题 -->
  <h4 class="pd10 m0 b33 bor-1-ddd">部门加班管理</h4>
  <!-- 搜索条件 -->
  <div class="bor-l-1-ddd bor-r-1-ddd pd20">
    <label>姓名：</label>
    <input class="form-control inline w130" id="name">
    <label class="ml10">日期：</label>
    <input class="form-control inline w130" id="month" value="<?php echo empty($month) ? date('Y-m') : $month;?>" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)">
    <button class="btn btn-success w80 ml10 mt-2" onclick="search();">查询</button>
  </div>
  <div>
    <!--可选标签-->
    <ul class="nav nav-tabs pl10 bor-l-1-ddd bor-r-1-ddd">
      <li role="presentation" class="<?php echo (!empty($status) && empty($user_id) &&  $status == "wait") ? 'active' : '';?>"><a class="pointer" href="/oa/departmentOverTime/month/<?php echo date('Y-m');?>/status/wait">待审批</a></li>
      <li role="presentation" class="<?php echo (!empty($status) && empty($user_id) &&  $status == "success") ? 'active' : '';?>"><a class="pointer" href="/oa/departmentOverTime/month/<?php echo date('Y-m');?>/status/success">已通过</a></li>
      <li role="presentation" class="<?php echo (!empty($status) && empty($user_id) &&  $status == "reject") ? 'active' : '';?>"><a class="pointer" href="/oa/departmentOverTime/month/<?php echo date('Y-m');?>/status/reject">未通过</a></li>
      <li role="presentation" class="<?php echo (!empty($status) && $status == "all") ? 'active' : 'hidden';?>"><a class="pointer">搜索结果</a></li>
    </ul>
    <!-- 部门加班记录表格 -->
    <?php if(!empty($data)): ?>
    <table class="table bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd m0">
      <thead>
        <tr>
          <th class="w30"></th>
          <th class="w130">姓名</th>
          <th class="w130">类型</th>
          <th class="w300">内容</th>
          <th class="w130">日期</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($data as $row): ?>
        <tr>
          <td class="center">
            <?php if($row['status'] == "wait"): ?>
            <span class="glyphicon glyphicon-time"></span>
            <?php elseif($row['status'] == "success"): ?>
            <span class="glyphicon glyphicon-ok-sign b5c"></span>
            <?php elseif($row['status'] == "reject"): ?>
            <span class="glyphicon glyphicon-remove-sign b2"></span>
            <?php endif; ?>
          </td>
          <td><?php echo $row->user->cn_name; ?></td>
          <td><?php echo ($row['type'] == "holiday") ? '周末及法定节假日': '工作日';?></td>
          <td>
            <?php if($row['type']!="normal"): ?>
            <a href="/oa/overtimeDetail/id/<?php echo $row['id']?>/type/departmentOverTime"><?php echo $row['content'];?></a>
            <?php else: ?>
            <?php echo $row['content'];?>
            <?php endif; ?>
          </td>
          <td><?php echo $row['end_time'];?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php else: ?>
    <h4 class="m0 pd20 bor-l-1-ddd bor-r-1-ddd bor-b-1-ddd center">没有记录</h4>
    <?php endif; ?>
    <!-- 分页栏 -->
    <div class="w600 m0a pd20">
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
  </div>
</div>

<!--js-->
<script type="text/javascript">
  // 用户数组初始化
  var user_arr = new Array();
  var cn_name = new Array();
  <?php 
    if(!empty($users)){
      foreach($users as $urow){
        echo "user_arr.push({'id':'{$urow['user_id']}','name':'{$urow['cn_name']}'});";
        echo "cn_name.push('{$urow['cn_name']}');";
      }
    }
  ?>

  // 页面初始化
  $(document).ready(function(){
    // 自动补全
    $("#name").autocomplete({
      source:cn_name
    });

    // 填充搜索的员工姓名
    var user_id = "<?php echo empty($user_id)? '' : $user_id;?>";
    if(user_id != ""){
      $.each(user_arr, function(){
        if(this['id'] == user_id){
          $("#name").val(this['name']);
        }
      });
    }
  });

  // 查询
  function search(){
    var name = $("#name").val();
    var month = $("#month").val();
    var user_id = "";
    var user_id_search = "";
    var month_search = "";
    var date_pattern = /^\d{4}-\d{2}$/;
    var f_tag = false;
    if(name != "" && !f_tag){
      $.each(user_arr, function(){
        if(name == this['name']){
          user_id = this['id'];
        }
      });
      if(user_id == ""){
        showHint("提示信息","查找不到此员工，请检查");
        $("#name").focus();
        f_tag = true;
      }else{
        user_id_search = "/user_id/"+user_id;
      }
    }
    if(month != "" && !f_tag){
      if(!date_pattern.exec(month)){
        showHint("提示信息","日期输入格式错误");
        f_tag = true;
      }else{
        month_search = "/month/"+month;
      }
    }
    if(!f_tag){
      location.href = "/oa/departmentOverTime"+month_search+user_id_search+"/status/all";
    } 
  }
</script>
