<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <!-- 头部信息 -->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
  <meta http-equiv="Expires" content="0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-control" content="no-cache">
  <meta http-equiv="Cache" content="no-cache">
  <base href="<?php echo Yii::app()->request->hostInfo; ?>" />
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

  <!-- JS -->
  <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
  <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap.js"></script>
  <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/oa.js"></script>
	
  <!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />
</head>

<!-- <body onload="guideSet('<?#php echo empty($this->url) ? '' : $this->url; ?>');"> -->
<body>
<div class="container main" id="page">
  <div class="row">
    <div class="w1300 m0a p00 h120 top-main">
      <img src="../images/logo2.png" class="fl w144 h50 mt15 pb10 pointer" onclick="location.href='/user/index'" title="回首页"><!-- 左侧logo -->
      <div class="fr ml10 mt15 h40">
        <button class="h40 bg-trans f16px bor-none top-btn b33" onclick="location.href='/user/personalInfo';"><span class="glyphicon glyphicon-user"></span>
        <?php 
          $user = $this->user;
          if(!empty($user->en_name) && $user->en_name != " ")
          {
            echo $user->en_name;
          }
          else if(!empty($user->cn_name))
          {
            echo $user->cn_name;
          }
          //输出问候语
          Helper::printGreetings();
        ?>
        </button><!-- 用户名 -->
        <?php  if(!empty($this->user->msgCount)): ?>
        <button class="h40 bg-trans f16px b5c bor-none top-btn b33" onclick="location.href='/oa/msgs';" id="user-msg-btn"><span class="glyphicon glyphicon-envelope"></span>
        <?php echo "[{$this->user->msgCount}条新消息]"; ?>
        <?php else: ?>
        <button class="f16px h40 bg-trans bor-none top-btn b33" onclick="location.href='/oa/msgs';"><span class="glyphicon glyphicon-envelope"></span>
        <?php echo "消息"; ?>
        <?php endif; ?>
        </button><!-- 消息数 -->
        <button class="h40 bg-trans f16px bor-none top-btn b33" onclick="location.href='/user/notification'">
          <span class="glyphicon glyphicon-bullhorn"></span>&nbsp;公告
        </button><!-- 公告 -->
        <button class="h40 bg-trans f16px bor-none top-btn b33" onclick="location.href='/user/userpc'">
          <span class="glyphicon glyphicon-hdd b33"></span>&nbsp;内网电脑
        </button><!-- 内网电脑 -->
        <button class="f16px h40 bg-trans bor-none top-btn b33" onclick="location.href='/ajax/logout'"><span class="glyphicon glyphicon-log-out">
          </span>&nbsp;退出
        </button><!-- 退出 -->
      </div><!-- 用户信息栏 -->

      <div class="fl p00 m0a w1300 h40 mt10 bor-1-ddd" style="clear:left;">
        <ul id="mainmenu" class="w300 nav nav-justified main-nav" >
          <li><a href="/oa/notificationManage" id="administration">行政</a></li>
          <li><a href="/oa/structure" id="personnel">人事</a></li>
        </ul>
      </div><!-- 水平导航栏 -->

    </div><!-- 顶部栏 -->
   

    <div id="breadcrumbs-div" class="hidden">
      <?php if(isset($this->breadcrumbs)):?>
      <?php $this->widget('zii.widgets.CBreadcrumbs', array('links'=>$this->breadcrumbs,)); ?><!-- breadcrumbs -->
      <?php endif?>
    </div><!-- 面包屑导航 -->

    <div class="main-content bg-white m0a nw1300 w1300" id="main-content">
      <div class="fl relative p00 m0 nh80 w196" id="guide-div">
        <div class="guide-div w198 fl relative hidden" id="guide-personnel">
          <!-- 公司信息 -->
          <a><h4><span class="glyphicon glyphicon-th-list"></span>&nbsp;公司</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/structure">公司架构</a></li>
            <li><a href="/oa/formation">人员编制</a></li>
            <li><a href="/oa/processManage">流程管理</a></li>
            <li><a href="/oa/personnelChange/year/<?php echo date('Y');?>">人事变动统计</a></li>
            <li><a href="/oa/adminSet">操作人员设置</a></li>
            <?php if(!empty($this->user) && Roles::Check_User_in_roles($this->user)):?>
            <li><a href="/oa/RolesSet">系统权限设置</a></li>
            <?php endif; ?>
            <?php if (EditorRoles::checkUserInRolesTable($this->user->user_id)):?>
              <li><a href="/oa/editorRolesSet">文档库权限设置</a></li>
            <?php endif; ?>
          </ul>
          <!-- 招聘管理 -->
          <a><h4><span class="glyphicon glyphicon-flag"></span>&nbsp;招聘</h4></a>
          <ul class="nav page-nav">
            <?php if(Yii::app()->session['is_leader']): ?>
            <li><a href="/oa/recruitApply">招聘申请</a></li>
            <li><a href="/oa/recruitApplyRecord">我的招聘申请</a></li>
            <?php endif; ?>
            <li><a href="/oa/recruitApplySummary">招聘申请记录</a></li>
          </ul>
          <!-- 面试管理 -->
          <a><h4><span class="glyphicon glyphicon-thumbs-up"></span>&nbsp;面试</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/interviewEvaluateRecord">面试评估记录</a></li>
          </ul>
          <!-- 转正 -->
          <a><h4><span class="glyphicon glyphicon-share-alt"></span>&nbsp;转正</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/positiveApply">发起转正申请</a></li>
            <li><a href="/oa/positiveApplyRecord">转正申请记录</a></li>
          </ul>
          <!-- 离职处理 -->
          <a><h4><span class="glyphicon glyphicon-new-window"></span>&nbsp;离职</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/quitProcess" >发起离职申请</a></li>
            <li><a href="/oa/quitRecord">离职申请记录</a></li>
            <li><a href="/oa/deliverWorkRecord">工作交接记录</a></li>
          </ul>
          <!-- 简历中心 -->
          <a><h4><span class="glyphicon glyphicon-file"></span>&nbsp;简历中心</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/resumeManage">简历存档</a></li>
          </ul>
        </div><!-- 人事导航栏 -->


        <div class="guide-div w198 fl relative hidden" id="guide-administration">
          <!-- 公告 -->
          <a><h4><span class="glyphicon glyphicon-bullhorn"></span>&nbsp;公告</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/newNotification">发布公告</a></li>
            <li><a href="/oa/notificationManage">公告管理</a></li>
          </ul>
          <!-- 请假 -->
          <a><h4><span class="glyphicon glyphicon-time"></span>&nbsp;请假</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/processLeaveRecord">审批记录</a></li>
            <li><a href="/oa/leaveSummaryWait">请假记录</a></li>
            <li><a href="/oa/leaveForm">请假统计</a></li>
          </ul>
          <!-- 加班管理 -->
          <a><h4><span class="glyphicon glyphicon-list-alt"></span>&nbsp;加班</h4></a>
          <ul class="nav page-nav">
            <!-- 公司加班查询、加班统计只有 人事行政和总经理 能够访问 -->
            <li ><a href="/oa/departmentOverTime/month/<?php echo date('Y-m');?>">部门加班管理</a></li>

           <?php if(!empty($this->user) && ($this->user->user_id == Users::getAdminId()->user_id || $this->user->user_id == Users::getCeo()->user_id || $this->user->user_id == Users::getCcommissioner()->user_id || $this->user->user_id == Users::getHr()->user_id ||Roles::Check_role('account', $this->user))):?>
            <li ><a href="/oa/overtime/month/<?php echo date('Y-m');?>">公司加班查询</a></li>
            <li><a href="/oa/overTimeList/month/<?php echo date('Y-m',strtotime('-1months'));?>">加班统计</a></li>
            <?php endif; ?>

          </ul>
          <!-- 费用管理 -->
          <a><h4><span class="glyphicon glyphicon-usd"></span>&nbsp;费用</h4></a>
          <ul class="nav page-nav">
            <!-- 费用预算、申请记录、报销记录只有 人事行政和总经理 能够访问 -->
            <?php if(!empty($this->user) && ($this->user->user_id == Users::getAdminId()->user_id || $this->user->user_id == Users::getCeo()->user_id || $this->user->user_id == Users::getCcommissioner()->user_id || $this->user->user_id == Users::getHr()->user_id ||Roles::Check_role('account', $this->user))):?>
            <li><a href="/oa/budget">费用预算</a></li>
            <?php endif; ?>
            <li><a href="/oa/subscribeProcessRecord">审批记录</a></li>
            <?php if(!empty($this->user) && ($this->user->user_id == Users::getAdminId()->user_id || $this->user->user_id == Users::getCeo()->user_id || $this->user->user_id == Users::getCcommissioner()->user_id || $this->user->user_id == Users::getHr()->user_id ||Roles::Check_role('account', $this->user))):?>
            <li><a href="/oa/subscribeRecord">申请记录</a></li>
            <li><a href="/oa/reimburseRecord">报销记录</a></li>
            <?php endif; ?>
            <li><a href="/oa/costForm">费用报表</a></li>
          </ul>
          <!-- 出差 -->
          <a><h4><span class="glyphicon glyphicon-plane"></span>&nbsp;出差</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/processBusinessTripRecord">审批记录</a></li>
            <li><a href="/oa/businessTrip_summary_wait" >出差记录</a></li>
          </ul>
          <!-- 图书借阅 -->
          <a><h4><span class="glyphicon glyphicon-book"></span>&nbsp;图书</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/editbook">图书管理</a></li>
            <li><a href="/oa/borrowRecord" >借阅记录</a></li>
          </ul>
          <!-- 兴趣小组 -->
          <a><h4><img src="../images/interest_2.png" style="width:18px;height:18px;vertical-align:bottom;">&nbsp;兴趣小组</h4></a>
          <ul class="nav page-nav">
            <!-- 费用预算、组长设置只有 人事行政和总经理 能够访问 -->
            <?php if(!empty($this->user) && ($this->user->user_id == Users::getAdminId()->user_id || $this->user->user_id == Users::getCeo()->user_id || $this->user->user_id == Users::getCcommissioner()->user_id || $this->user->user_id == Users::getHr()->user_id ||Roles::Check_role('account', $this->user))):?>
            <li><a href="/oa/activityBudget">费用预算</a></li>
            <li><a href="/oa/activityHeadSet">组长设置</a></li>
            <?php endif; ?>
            <li><a href="/oa/activityRecord">参与统计</a></li>
          </ul>
          <!-- 邮件通知 -->
          <a><h4><span class="glyphicon glyphicon-envelope"></span>&nbsp;邮件通知</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/mail">群发邮件</a></li>
            <li><a href="/oa/mailList">邮件列表</a></li>
          </ul>

          <!-- 其他 -->
          <a><h4><span class="glyphicon glyphicon-pushpin"></span>&nbsp;其他</h4></a>
          <ul class="nav page-nav">
            <li><a href="/oa/seal">印鉴申请</a></li>
            <li><a href="/oa/vote">发起投票</a></li>
          </ul>
        </div><!-- 行政导航栏 -->
      </div>

      <div class="fl nh600 w1100 pl20" id="page-div">
        <?php echo empty($content) ? "" : $content; ?>
      </div><!-- 加载进来的页面 -->

      <div class="clear"></div>
    </div><!-- 页面内容 -->

    <div class="w1300 m0a pd10 footer bor-t-2-33 mt20">
      <p class="center m0a">Version:&nbsp;Beta 2.0</p>
      <p class="center m0a">Copyright © 2013-2014 shanyougame.com. All Rights Reserved</br>广州善游网络科技有限公司 (七喜控股)  粤ICP备13087700号-1</p>
    </div><!-- 页脚 -->

  </div><!-- 页面内容行 -->
</div><!-- page -->

<div id="newMsg-div" class="newMsg-div" style="z-index:20;">
  <h5 class="m0 bg-33 pd5 white" >新消息提醒<span class="fr glyphicon glyphicon-remove pointer" onclick="$('#newMsg-div').slideUp();"></span></h5>
  <h5 id="content-text" class="mt50 center"></h5>
</div><!-- 新消息提醒的框 -->

<div id="opinion-button" class="opinion-button-div bg-33" style="z-index:20;" onclick="showOpinion();">
  <span class="white">意</br>见</br>反</br>馈</span>
</div><!-- 意见反馈的框 -->

<div id="opinion-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">意见反馈</h4>
    </div>

    <div class="modal-body">
        <textarea class="form-control" rows="6" placeholder="请输入您的意见" id="opinion-input"></textarea>
    </div>

    <div class="modal-footer">
      <button class="btn btn-success w100" onclick="sendOpinion();">提交</button>
    </div>
</div><!-- 意见反馈的输入框 -->

</body>
</html>

<script type="text/javascript">
  // 只允许chrome，firefox和safari访问
  if(navigator.userAgent.indexOf("Chrome")>-1||navigator.userAgent.indexOf("Firefox")>-1||navigator.userAgent.indexOf("Safari")>-1){
  }else{
    $("#page-div").remove(); 
    $("body").html("<h1>您的浏览器版本太旧，请使用谷歌浏览器或者火狐浏览器访问本页面！</h1>");
  }
  
  // 显示意见收集窗口
  function showOpinion(){
    var ySet = (window.innerHeight - $("#opinion-div").height())/2;
    var xSet = (window.innerWidth - $("#opinion-div").width())/2;
    $("#opinion-div").css("top",ySet);
    $("#opinion-div").css("left",xSet);
    $("#opinion-div").modal({show:true});
    $("#opinion-input").val("");    // 清空意见输入框
  }

  // 发送意见
  function sendOpinion(){
    // 获取数据
    var opinion = $("#opinion-input").val();
    var url = '<?php echo $this->url;?>';
    // 验证数据
    if(opinion == ""){
      showHint("提示信息","请输入您的意见");
    }else{
      // 发送数据
      $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/suggest',
        data:{'content':opinion,'url':url},
        success:function(result){
          if(result.code == 0){
            showHint("提示信息","意见反馈提交成功");
            $("#opinion-div").modal('hide');
          }else if(result.code == -1){
            showHint("提示信息","意见反馈提交失败！");
          }else if(result.code == -2){
            showHint("提示信息","参数错误！");
          }else{
            showHint("提示信息","你没有权限执行此操作！");
          }
        }
      });
    }
  }
</script>



