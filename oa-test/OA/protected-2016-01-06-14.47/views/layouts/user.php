<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <!-- 头信息 -->
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
  <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/user.js"></script>
	
  <!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />
</head>

<body>
<div class="container main" id="page">
  <div class="row h100%">
    <div class="w1300 m0a p00 h70 top-main">
      <img src="../images/logo2.png" class="fl w144 h40 mt15 pointer" onclick="location.href='/user/'" title="首页"><!-- logo -->
      <div class="fr ml10 mt15 h40">
        <?php if( (!empty(Yii::app()->session['admin']) && Yii::app()->session['admin']) || Roles::Check_User_in_roles($this->user) ): ?>
        <a href="/oa/" class="mr10 f15px top-btn b33"><span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;进入管理界面</a>
        <?php endif;?>
        <button class="h40 bg-trans f16px bor-none top-btn b33" onclick="location.href='/user/personalInfo';"><span class="glyphicon glyphicon-user"></span>
        <?php 
          $user = empty($this->user) ? "" : $this->user;
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
          <button class="h40 bg-trans f16px b33 bor-none top-btn b5c" onclick="location.href='/user/msgs';" id="user-msg-btn"><span class="glyphicon glyphicon-envelope"></span>
        <?php echo "[{$this->user->msgCount}</span>条新消息]"; ?>
        <?php else: ?>
          <button class="f16px h40 bg-trans bor-none top-btn b33" onclick="location.href='/user/msgs';"><span class="glyphicon glyphicon-envelope"></span>
        <?php echo "消息"; ?>
        <?php endif; ?>
        </button><!-- 消息数 -->
        <button class="h40 bg-trans f16px bor-none top-btn b33" onclick="location.href='/user/userpc'">
          <span class="glyphicon glyphicon-hdd b33"></span>&nbsp;内网电脑
        </button><!-- 内网电脑 -->
        <button class="h40 bg-trans f16px bor-none top-btn b33" onclick="location.href='/user/notification'">
          <span class="glyphicon glyphicon-bullhorn b33"></span>&nbsp;公告
        </button><!-- 公告 -->
        <button class="f16px h40 bg-trans bor-none top-btn b33" onclick="location.href='/ajax/logout'">
          <span class="glyphicon glyphicon-log-out"></span>&nbsp;退出
        </button><!-- 退出 -->
      </div><!-- 用户信息栏 -->
    </div><!-- 顶部栏 -->

    <div class="main-content h100% m0a nw1300 w1300" id="main-content">
      <div class="fl nh900 w1300 bg-white" id="page-div">
        <div class="h100 mt5 <?php if(in_array(strtolower(trim($this->url,'/')),array("","user","user/index"))) echo 'hidden';?>">
          <ul class="nav nav-pills nav-justified bg-33 user-main-ul">
            <li class="center">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='';">
                <span class="glyphicon glyphicon-home white f30px"></span>
                <p class="white m0 f15px mt5">首页</p>
              </button>
            </li><!-- 首页 -->
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'leave') != false) echo 'active'; ?>" >
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/leave';">
                <span class="glyphicon glyphicon-time white f30px"></span>
                <p class="white m0 f15px mt5">请假</p>
              </button>
            </li><!-- 请假 -->
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'overTime') != false) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/overTime';">
                <span class="glyphicon glyphicon-list-alt white f30px"></span>
                <p class="white m0 f15px mt5">加班</p>
              </button>
            </li><!-- 加班 -->
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'subscribe') != false) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/subscribe';">
                <span class="glyphicon glyphicon-usd white f30px"></span>
                <p class="white m0 f15px mt5">费用</p>
              </button>
            </li><!-- 费用 -->
            <li class="center <?php if(!empty($this->url) && (strpos($this->url,'businessTrip') != false || strpos($this->url,'outMsg') != false)) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/businessTrip';">
                <span class="glyphicon glyphicon-plane white f30px"></span>
                <p class="white m0 f15px mt5">出差</p>
              </button>
            </li><!-- 出差 -->
            <li class="center <?php if(!empty($this->url) && (strpos($this->url,'Books') != false || strpos($this->url,'books') != false)) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/books';">
                <span class="glyphicon glyphicon-book white f30px"></span>
                <p class="white m0 f15px mt5">图书</p>
              </button>
            </li><!-- 图书 -->
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'meetingRoom') != false) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/meetingRoomManage';">
                <span class="glyphicon glyphicon-comment white f30px"></span>
                <p class="white m0 f15px mt5">会议室</p>
              </button>
            </li><!-- 会议室 -->
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'activity') != false) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/activity';">
                <img src="./images/interest.png" style="width:30px;height:30px;vertical-align:bottom;">
                <p class="white m0 f15px mt5">兴趣小组</p>
              </button>
            </li><!-- 兴趣小组 -->
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'structure') != false) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/structure';">
                <span class="glyphicon glyphicon-th-list white f30px"></span>
                <p class="white m0 f15px mt5">公司架构</p>
              </button>
            </li><!-- 公司架构 -->
            
            <li class="center <?php if(!empty($this->url) && strpos($this->url,'editor') != false) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/editor';">
                <span class="glyphicon glyphicon-folder-open white f30px"></span>
                <p class="white m0 f15px mt5">文档库</p>
              </button>
            </li><!-- 公司架构 -->

            <li class="center hidden <?php if(!empty($this->url) && (strpos($this->url,'Property') != false || strpos($this->url,'property'))) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/personalProperty';">
                <span class="glyphicon glyphicon-save white f30px"></span>
                <p class="white m0 f15px mt5">资产领用</p>
              </button>
            </li><!-- 资产领用 -->
           
            <!-- 只要本人被发起过转正申请就显示，如果成功了就不显示 -->
            <?php if(!empty($this->user) && $this->user->job_status != "formal_employee" && !empty($this->user->qualifyTag)): ?>
            <li class="center <?php if(!empty($this->url) && (strpos($this->url,'positive') != false || strpos($this->url,'Positive') != false)) echo 'active'; ?>">
              <button class="bor-none bg-trans h100  w90" onclick="location.href='/user/personalPositiveApply';">
                <span class="glyphicon glyphicon-share-alt white f30px"></span>
                <p class="white m0 f15px mt5">转正</p>
              </button>
            </li><!-- 转正 -->
            <?php endif; ?>
            
            <!-- 本人有被发起离职申请就显示 -->
            <?php if(!empty($this->user) && !empty($this->user->quitApply)): ?>
            <li class="center <?php if(!empty($this->url) && (strpos($this->url,'quit') != false || strpos($this->url,'Quit') != false)) echo 'active'; ?>">
              <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/personalQuitRecord';">
                <span class="glyphicon glyphicon-new-window white f30px"></span>
                <p class="white m0 f15px mt5">离职</p>
              </button>
            </li><!-- 离职 -->
            <?php endif; ?>
          </ul>
        </div><!-- 导航栏 -->
        <?php echo empty($content) ? '':$content; ?>
      </div><!-- 加载进来的页面 -->
      <div class="clear"></div>
    </div><!-- 页面内容 -->

    <div class="w100% w1300 m0a pd10 footer bor-t-2-33 mt20 b33">
      <p class="center m0a"><strong>Version:&nbsp;Beta 2.0</strong></p>
      <p class="center m0a"><strong>Copyright © 2013-2014 shanyougame.com. All Rights Reserved</br>广州善游网络科技有限公司 (七喜控股)  粤ICP备13087700号-1</strong></p>
    </div><!-- 页脚 -->

  </div><!-- 页面内容行 -->
</div><!-- page -->

<div id="newMsg-div" class="newMsg-div" style="z-index:20;">
  <h5 class="m0 bg-33 pd5 white" >新消息提醒<span class="fr glyphicon glyphicon-remove pointer" onclick="$('#newMsg-div').slideUp();"></span></h5>
  <h5 id="content-text" class="mt50 center"></h5>
</div><!-- 新消息提示窗 -->

<div id="opinion-button" class="opinion-button-div bg-33" style="z-index:20;" onclick="showOpinion();">
  <span class="white">意</br>见</br>反</br>馈</span>
</div><!-- 意见反馈按钮 -->

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
</div><!-- 意见反馈输入模态框 -->
</body>
</html>



<script type="text/javascript">
  // 只允许用Chrome、Firefox、Safari浏览器访问
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
    $("#opinion-input").val("");
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
      // 数据发送
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

