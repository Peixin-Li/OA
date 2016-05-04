<?php
echo "<script type='text/javascript'>";
echo "console.log('login');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/login.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/login.js"></script>

<!-- 主界面 -->
<div class="container main" id="container-div">
    <!-- 页面内容行 -->
	<div class="row">
        <!-- 页面内容 -->
		<div class="m0a w1300 nw1300 login-div pt100" style="min-height: 700px;">
			<!-- 登录框 -->
            <div class="w1300 m0a pd50">
    			<div class="w1000 center pd50 m0a login_bg">
    				<form>
    					<img src="../images/logo_lg.png" style="width:800px;" />
                        <!-- 输入框 -->
    					<div class="form-group mt50 pl50 w700 m0a">
                            <!-- 用户名 -->
    						<div class="inline-block fl">
    							<button class="bg-trans  bor-none f18px mt10"><span  class="glyphicon glyphicon-user"></span></button>
    							<input type="text" id="name" class="bor-rad-20 form-control center w200 inline mt5" onkeydown="if(event.keyCode==13) return false;$('#wrong-div').hide();" placeholder="域用户名（开机登录的）" tabindex="1" required autofocus>
    						</div>
    						<div class="ml20 inline-block fl">
    							<button class="bg-trans bor-none f18px mt10"><span  class="glyphicon glyphicon-lock"></span></button>
    							<input type="password" id="pwd" class="bor-rad-20 form-control center w200 inline mt5" onkeydown="if(event.keyCode==13) return false;$('#wrong-div').hide();" placeholder="域用户密码" tabindex="2" required>
    							<input type="checkbox" id="remember" class="hidden" checked="checked">
    						</div><!-- 密码 -->
    						<button id="submit" class="btn btn-lg ml20 btn-success centerbtn-block fl w100" tabindex="3" type="button">登录&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></button>
    						<div class="clear"></div>
    					</div>
    				</form>
    				<div class="alert alert-danger wrong-div mt20" id="wrong-div"><p id="wrong-text" class="f20px center"></p></div>
    			</div>
    			<div class="clear"></div>
		    </div>
		</div>
        <div class="oa-link">
            <button class="h40 bg-trans f16px bor-none top-btn white" onclick="window.open('http://exmail.qq.com/')">
                企业邮箱
            </button>
            <button class="h40 bg-trans f16px bor-none top-btn white" onclick="window.open('https://tower.im/')">
                项目管理
            </button>
            <button class="h40 bg-trans f16px bor-none top-btn white" onclick="window.open('http://zentao.i.shanyougame.com/zentao/')">
                Bug管理
            </button>
            <button class="h40 bg-trans f16px bor-none top-btn white" onclick="window.open('http://owncloud.i.shanyougame.com')">
                文件服务器
            </button>
        </div>
	</div>
</div>


<!-- js -->
<script type="text/javascript">
    // 只允许chrome,firefox,safari访问
    if(navigator.userAgent.indexOf("Chrome")>-1||navigator.userAgent.indexOf("Firefox")>-1||navigator.userAgent.indexOf("Safari")>-1){
    }else{
        $("#container-div").remove();
        $("body").html("<h1>您的浏览器版本太旧，请使用谷歌浏览器或者火狐浏览器访问本页面！</h1>");
    }

    // 登录
	function login(){
        // 获取数据
        var name = $("#name").val();
        var pwd  = $("#pwd").val();
        name = trim(name);
        pwd = trim(pwd);

        // 访问的参数
        var hash = window.location.hash;

        // 记住密码的设置
        var remember = 0;
        if(document.getElementById("remember").checked==true){
            remember = 1;
        }

        // 验证数据
        $.ajax({
            type:'post',
            url: '/ajax/ldapLogin',
            dataType: 'json',
            data:{'user':name,'pwd':pwd,'remember':remember},
            success:function(result){
                if(result.code == 0){
                    // 判断是否带有参数
                    if(hash.length == 0){  // 没有参数就跳转到首页
                        window.location.href="/user/index"; 
                    }else{  // 有参数就跳转到对应的页面
                        window.location.href = hash.substring(1);
                    }
                }else if(result.code == -1){
                    $("#wrong-text").html("用户名或密码错误！");
		    $("#wrong-text").html("用户名或密码错误！<br>请核对用户名/密码与开机登陆的用户名/密码是否一致");
                    $("#wrong-div").css("display","block");
                    $("#name").focus();
                }else if(result.code == -2){
                    $("#wrong-text").html("无效用户！");
                    $("#wrong-div").css("display","block");
                    $("#name").focus();
                }else if(result.code == -3){
                    $("#wrong-text").html("该用户已离职！");
                    $("#wrong-div").css("display","block");
                    $("#name").focus();
                }else{
                    $("#wrong-text").html("用户名或密码错误！");
                    $("#wrong-div").css("display","block");
                    $("#name").focus();
                }
            }
        });
    }
</script>




