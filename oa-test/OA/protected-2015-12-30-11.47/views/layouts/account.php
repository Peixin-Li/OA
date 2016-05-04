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
  <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/account.js"></script>
	
  <!-- CSS -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/oa.css" />
</head>

<!-- <body onload="guideSet('<?#php echo empty($this->url) ? '' : $this->url; ?>');"> -->
<body>
<div class="container main" id="page">
  <div class="row">
    <div class="w1300 m0a p00 h90 top-main">
      <img src="../images/logo2.png" class="fl w144 h50 mt15 pb10 pointer" onclick="location.href='/account/index'" title="回首页">
      <div class="fr ml10 mt15 h40">
        <button class="f16px h40 bg-trans bor-none top-btn b33" onclick="location.href='/ajax/logout'">
            <span class="glyphicon glyphicon-log-out"></span>&nbsp;退出
        </button><!-- 退出 -->
      </div><!-- 用户信息栏 -->

    </div><!-- 顶部栏 -->

    <div class="main-content bg-white m0a nw1300 w1300" id="main-content">
      <div class="fl nh600 pl20" id="page-div" style="width:100%">
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

</script>



