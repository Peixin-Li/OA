<?php
echo "<script type='text/javascript'>";
echo "console.log('index');";
echo "</script>";
?>

<!-- 我的资料 -->
<div class="fl pd3 w300">
	<div class="bg-33 h406 pd5 user-index-div" id="personal-div" onmouseover="$('#edit-btn').removeClass('hidden');" onmouseout="$('#edit-btn').addClass('hidden');">
		<span class="f15px white ml5">我的资料</span>
		<button class="f15px white fr bg-trans bor-none btn p00 hidden" id="edit-btn" onclick="location.href='/user/personalInfo';">
			<span class="glyphicon glyphicon-edit f13"></span>修改
		</button>
		<div class="pd10" >
			<div class="h250">
				<div class="w100 h100 pl20 mt10" id="user-head-div">
					<div style="border-left:5px solid white;" class="pl10">
						<img src="<?php echo $user['photo']; ?>" class="w100 h100 bor-rad-5" style="box-shadow:0px 0px 10px 1px rgba(255,255,255,0.6);">
					</div>
				</div>
				<div class="mt10 pl20" id="user-title-div" >
					<span class="white center f25px pl10" style="border-left:5px solid white;"><?php echo empty($user['title']) ? " ":$user['title'];?></span>
				</div>
				<div class="mt10 pl20" id="user-cnname-div">
					<span class="white center f20px pl10" style="border-left:5px solid white;"><?php echo empty($user['cn_name']) ? " ":$user['cn_name'];?></span>
				</div>
				<div class="mt10 pl20" id="user-entrydate-div">
					<span class="white center f15px pl10" style="border-left:5px solid white;"><?php if(!empty($user['job_status'])){if($user['job_status'] == "intern"){echo '实习生';}else if($user['job_status'] == "formal_employee"){echo '正式员工';}else{echo '试用期';}}?></span>
				</div>
                <!--入职时间-->
				<div class="mt10 pl20" id="user-entrydate-div">
					<span class="white center f13 pl10" style="border-left:5px solid white;">
                        <?php echo empty($user['entry_day']) ? " ":$user['entry_day'];?>入职
                    </span>
				</div>

				<div class="mt10 pl20" id="user-nativeplace-div">
					<span class="white center f13 pl10" style="border-left:5px solid white;"><?php echo empty($user['native_place']) ? " ": $user['native_place'];?></span>
				</div>
			</div>
			<div class="pl20 mt20">
				<span class="m0 white f36px fl" id="cur-weekday"></span>
				<span class="white fl mt10 ml20 w100"><strong><?php echo date('Y-m-d');?></strong></span>
				<span class="white fl ml20 w100"><strong id="cur-time"><?php echo date('H:i:s');?></strong></span>
				<div class="clear"></div>
			</div>
			<div class="h5 bg-white ml20 mr20 m0 mt5" id="user-white-div"></div>
		</div>
	</div>
</div>
<!-- 快捷入口 -->
<div class="fl pd3 w1000 h106 " >
	<div class="h100 bg-33" id="guide-div">
		<ul class="nav nav-pills nav-justified user-main-ul user-index-ul">
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/leave';">
					<span class="glyphicon glyphicon-time white f30px"></span>
					<p class="white m0 f15px mt5">请假</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/overTime';">
					<span class="glyphicon glyphicon-list-alt white f30px"></span>
					<p class="white m0 f15px mt5">加班</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/subscribe';">
					<span class="glyphicon glyphicon-usd white f30px"></span>
					<p class="white m0 f15px mt5">费用</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/businessTrip';">
					<span class="glyphicon glyphicon-plane white f30px"></span>
					<p class="white m0 f15px mt5">出差</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/books';">
					<span class="glyphicon glyphicon-book white f30px"></span>
					<p class="white m0 f15px mt5">图书</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/meetingRoomManage';">
					<span class="glyphicon glyphicon-comment white f30px"></span>
					<p class="white m0 f15px mt5">会议室</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/activity';">
					<img src="./images/interest.png" style="width:30px;height:30px;vertical-align:bottom;">
					<p class="white m0 f15px mt5">兴趣小组</p>
				</button>
			</li>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/structure';">
					<span class="glyphicon glyphicon-th-list white f30px"></span>
					<p class="white m0 f15px mt5">公司架构</p>
				</button>
			</li>
            <li class="center">
                <button class="bor-none bg-trans h100 w90" onclick="location.href='/user/editor';">
                    <span class="glyphicon glyphicon-folder-open white f30px"></span>
                    <p class="white m0 f15px mt5">文档库</p>
                </button>
            </li>
			<li class="center hidden">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/personalProperty';">
					<span class="glyphicon glyphicon-save white f30px"></span>
					<p class="white m0 f15px mt5">资产领用</p>
				</button>
			</li>
			
			<!-- 如果已被发起过转正并且不是正式员工的话就显示 -->
			<?php if(!empty($this->user) && $this->user->job_status != "formal_employee" && !empty($this->user->qualifyTag)): ?>
			<li class="center  ">
				<button class="bor-none bg-trans h100  w90" onclick="location.href='/user/personalPositiveApply';">
					<span class="glyphicon glyphicon-share-alt white f30px"></span>
					<p class="white m0 f15px mt5">转正</p>
				</button>
			</li>
			<?php endif; ?>
			
			<!-- 如果已被发起离职的话就显示 -->
            <?php if(!empty($this->user) && !empty($this->user->quitApply)): ?>
			<li class="center">
				<button class="bor-none bg-trans h100 w90" onclick="location.href='/user/personalQuitRecord';">
					<span class="glyphicon glyphicon-new-window white f30px"></span>
					<p class="white m0 f15px mt5">离职</p>
				</button>
			</li>
            <?php endif; ?>
		</ul>
	</div>
</div>
<!-- 未读消息 -->
<div class="fl pd3 w500 h306" >
	<div class="bg-33 h300 pd5" id="msg-div">
		<span class="f15px white ml5">未读消息</span>
		<div class="pd20" style="padding-top:10px;">
			<div id="notice-exist-div" class="hidden">
				<div class="pd10 mt10" id="more-message">
					<a href="/user/msgs" class="hover-ddd white fr">查看更多消息>></a>
				</div>
			</div>
			<div id="notice-none-div" >
				<h4 class="center mt50"><span class="glyphicon glyphicon-envelope f50px white"></span></h4>
				<h4 class="center white">目前没有未读消息</h4>
			</div>
		</div>
	</div>
</div>
<!-- 公告 -->
<div class="fl pd3 w500 h306" >
	<div class="bg-33 h300 pd5">
		<span class="f15px white ml5">公告</span>
		<div class="pd20" style="padding-top:7px;">
			<div id="notification-exist-div" class="hidden">
				<div class="pd10 mt10" id="more-notification">
					<a href="/user/notification" class="hover-ddd white fr">查看更多公告>></a>
				</div>
			</div>
			<div id="notification-none-div" >
				<h4 class="center mt50"><span class="glyphicon glyphicon-bullhorn f50px white"></span></h4>
				<h4 class="center white">目前没有公告</h4>
			</div>
		</div>
	</div>
</div>
<!-- 每周活动 -->
<div class="fl pd3 w500 h306 hidden">
	<div class="bg-33 h300 pd5 user-index-div" id="activity-div">
		<span class="f15px white ml5">每周活动</span>
		<div class="pd20">
			<?php if(!empty($activity)): ?>
			<p class="hidden" id="activity-id"><?php echo $activity['id'];?></p>
			<h2 class="white mt5"><?php echo $activity['title']; ?></h2>
			<h4 class="white mt20 pl50" style="line-height:30px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $activity['content']; ?></h4>
			<?php if($activity->status == "wait"):?>
			<?php if(!$tag): ?>
			<button class="btn btn-success w130 fr mr20 <?php if(date('Y-m-d H:i:s') > $activity['end_time']) echo 'disabled'; ?>" onclick="joinActivity();" id="join-btn">我要报名<?php if(date('Y-m-d H:i:s') > $activity['end_time']) echo '(已截止)'; ?></button>
			<?php else: ?>
			<button class="btn btn-primary w130 fr mr20 <?php if(date('Y-m-d H:i:s') > $activity['end_time']) echo 'disabled'; ?>" onclick="unJoinActivity();" id="unjoin-btn">取消报名<?php if(date('Y-m-d H:i:s') > $activity['end_time']) echo '(已截止)'; ?></button>
			<?php endif; ?>
			<?php else: ?>
			<span class="f16px bg-white pd5 fr mr20 bor-rad-5">活动已结束</span>
			<?php endif; ?>
			<div class="clear"></div>
			<?php else: ?>
			<h4 class="center mt50"><span class="glyphicon glyphicon-exclamation-sign f50px white"></span></h4>
			<h4 class="center white">本周活动正在筹备中</h4>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="index-oa-link">
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


<!-- js -->
<script type="text/javascript">
	// 页面初始化
	$(document).ready(function(){
		// 设置个人信息下方的时钟
		setInterval('setTime()',1000);
		var date = new Date();
		var weekday = date.getDay();
		var weekday_str = "";
		if(weekday == 0){
			weekday_str = "周日";
		}else if(weekday == 1){
			weekday_str = "周一";
		}else if(weekday == 2){
			weekday_str = "周二";
		}else if(weekday == 3){
			weekday_str = "周三";
		}else if(weekday == 4){
			weekday_str = "周四";
		}else if(weekday == 5){
			weekday_str = "周五";
		}else if(weekday == 6){
			weekday_str = "周六";
		}
		$("#cur-weekday").text(weekday_str);
	});

	// 设置时间
	function setTime(){
		var date = new Date();
		var hour = date.getHours();
		var minute = date.getMinutes();
		var second = date.getSeconds();
		if(parseInt(hour) < 10) hour = "0"+parseInt(hour);
		if(parseInt(minute) < 10) minute = "0"+parseInt(minute);
		if(parseInt(second) < 10) second = "0"+parseInt(second);
		var time_str = hour+":"+minute+":"+second;
		$("#cur-time").text(time_str);
	}

  // 报名参加活动
  function joinActivity(){
    var id = $("#activity-id").text();
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/activityJoin',
      data:{'activity_id':id},
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
            showHint("提示信息","已经超过截止时间！");
        }else{
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 取消报名参加活动
  function unJoinActivity(){
    var id = $("#activity-id").text();
    $.ajax({
      type:'post',
      dataType:'json',
      url:'/ajax/activityExit',
      data:{'activity_id':id},
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
            showHint("提示信息","已经超过截止时间！");
        }else{
          showHint("提示信息","你没有权限执行此操作！");
        }
      }
    });
  }

  // 页面初始化
  $(document).ready(function(){
  	// 加载消息
  	loadNotice();

  	// 加载公告
  	loadNotification();
  });

  // 加载消息
  function loadNotice(){
  	// 判断是否有未读消息
  	if(notice_arr.length < 1){
  		$("#notice-none-div").removeClass("hidden");
  		$("#notice-exist-div").addClass("hidden");
  	}else{
		$("#notice-none-div").addClass("hidden");
		$.each(notice_arr,function(){
			var content = this['content'];

			// 数字的长度
			var num_str_length = content.replace(/\D/g, '').length;

			// 空格的长度
			var space_str_length = content.replace(/\S/g, '').length;

			// 少算的长度，因为两个数字或空格占一个中文符的位置
			var minus = parseInt((num_str_length + space_str_length)/2);

			// 截断内容
			if(minus > 6){
				if(content.length > (14 + minus)){
					content = content.substr(0, (12+minus) )+"...";
				}
			}else{
				if(content.length > (20 + minus)){
					content = content.substr(0, (18+minus) )+"...";
				}
			}

			// 显示内容
			var str = "<div style='padding:12px 10px;border-bottom:1px dashed white;'><a href='/user/msgDetail/id/"+this['id']+"' class='hover-ddd white'>"+content+"</a><span class='fr white'>"+this['create_time']+"</span></div>";
			$("#more-message").before(str);
		});
		$("#notice-exist-div").removeClass("hidden");
  	}
  }

  // 消息数组初始化
  var notice_arr = new Array();
  <?php 
	  if(!empty($notices)){
	  	foreach($notices as $nkey => $nrow){
	  		if($nkey < 4){
	  			$create_time = date('m-d H:i',strtotime($nrow['create_time']));
	  			echo "notice_arr.push({'id':'{$nrow['id']}','content':'{$nrow['content']}','create_time':'{$create_time}'});";
	  		}
	  	}
	  }
  ?>

  // 加载公告
  function loadNotification(){
  	// 判断是否有公告
  	if(notification_arr.length < 1){
  		$("#notification-none-div").removeClass("hidden");
  		$("#notification-exist-div").addClass("hidden");
  	}else{
		$("#notification-none-div").addClass("hidden");
		$.each(notification_arr,function(){
			var title = this['title'];

			// 数字的长度
			var num_str_length = title.replace(/\D/g, '').length;

			// 空格的长度
			var space_str_length = title.replace(/\S/g, '').length;

			// 少算的长度，因为两个数字或空格占一个中文符的位置
			var minus = parseInt((num_str_length + space_str_length)/2);

			// 截断内容
			if(minus > 6){
				if(title.length > (14 + minus)){
					title = title.substr(0,(12+minus) )+"...";
				}
			}else{
				if(title.length > (20 + minus)){
					title = title.substr(0,(18+minus) )+"...";
				}
			}

			// 显示内容
			var str = "<div class='pd10' style='border-bottom:1px dashed white;'><p class='m0 white'><strong>"+this['type']+"</strong></p><a href='/user/notificationDetail/id/"+this['id']+"' class='hover-ddd white'>"+title+"</a><span class='fr white'>"+this['create_time']+"</span></div>";
			$("#more-notification").before(str);
		});
		$("#notification-exist-div").removeClass("hidden");
  	}
  }

   // 公告数组初始化
  var notification_arr = new Array();
  <?php 
	  if(!empty($notifys)){
	  	foreach($notifys as $key => $notify_row){
	  		if($key < 3){
	  			$create_time = date('m-d H:i',strtotime($notify_row['create_time']));

	  			// 类型的转换
		  		$type = "";
		  		switch($notify_row['type']){
		  			case "other":{$type = "其他通知";break;}
		  			case "admin":{$type = "行政通知";break;}
		  			case "holiday":{$type = "放假通知";break;}
		  			case "internal":{$type = "内部悬赏";break;}
		  			case "activity":{$type = "活动通知";break;}
		  			case "appointments":{$type = "人事任命";break;}
		  		}
		  		echo "notification_arr.push({'id':'{$notify_row['id']}',  'type':'{$type}', 'title':'{$notify_row['title']}','create_time':'{$create_time}'});";
	  		}
	  	}
	  }
  ?>
</script>
