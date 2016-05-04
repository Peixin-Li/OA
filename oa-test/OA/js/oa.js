/**
*导航栏初始化
**/
function guideSet(url){
    // 对地址进行处理
    if(url.indexOf("formation") > -1){url = "/oa/formation";}
    if(url.indexOf("quitRecord") > -1){url = "/oa/quitRecord";}
    if(url.indexOf("positiveApplyRecord") > -1){url = "/oa/positiveApplyRecord";}
    if(url.indexOf("interviewEvaluateRecord") > -1){url = "/oa/interviewEvaluateRecord";}
    if(url.indexOf("recruitApplySummary") > -1){url = "/oa/recruitApplySummary";}
    if(url.indexOf("notificationManage") > -1){url = "/oa/notificationManage";}
    if(url.indexOf("subscribeRecord") > -1){url = "/oa/subscribeRecord";}
    if(url.indexOf("mailList") > -1){url = "/oa/mailList";}
    if(url.indexOf("leaveSummary") > -1){url = "/oa/leaveSummaryWait";}
    if(url.indexOf("businessTrip_summary") > -1 || url.indexOf("businessTripSummarySearch") > -1){url = "/oa/businessTrip_summary_wait";}
    if(url.indexOf("activityBudget") > -1){url = "/oa/activityBudget";}

    // 对当前访问的页面的对应导航加上一个标记
    $(".page-nav").each(function(){
        $(this).find("a").each(function(){
            if(url == $(this).attr("href")){
                $(this).append("<span class='glyphicon glyphicon-map-marker ml5'></span>");
            }
        });
    });

    //设置模块active
    if($("#breadcrumbs-div").find("a").first().next().text()=="首页"){        //首页
        $("#breadcrumbs-div").addClass("hidden");
        $("#index").parent().addClass("active");
        $("#guide-index").removeClass("hidden");
    }else if($("#breadcrumbs-div").find("a").first().next().text()=="消息列表"){        //消息列表
        $("#breadcrumbs-div").removeClass("hidden");
        $("#guide-div").addClass("hidden");
        $("#page-div").removeClass("pl20");
        $("#page-div").removeClass("w1100");
        $("#page-div").addClass("w1300");
    }else if($("#breadcrumbs-div").find("a").first().next().text()=="入职信息登记"){        //入职信息登记
        $("#breadcrumbs-div").removeClass("hidden");
        $("#guide-div").addClass("hidden");
        $("#page-div").removeClass("pl20");
        $("#page-div").removeClass("w1100");
        $("#page-div").addClass("w1300");
        $("#breadcrumbs-div").addClass("hidden");
        $(".top-main").addClass("hidden");
        $(".footer").addClass("hidden");
    }else{                                                                     //非首页
        $("#breadcrumbs-div").removeClass("hidden");
        $("#guide-div").removeClass("hidden");

        // 面包屑中首页之后的模块名字，例如图书借阅，所以controller中面包屑要写好
        var guideTitle = $("#breadcrumbs-div").find("a").first().next().text();
        if(guideTitle=="公司"||guideTitle=="招聘"||guideTitle=="转正"||guideTitle=="面试管理"
            ||guideTitle=="面试"||guideTitle=="简历中心"||guideTitle=="离职"){                                             //人事
            $("#guide-personnel").removeClass("hidden");
            $("#personnel").parent().addClass("active"); 

        }else if(guideTitle=="图书"||guideTitle=="请假"||guideTitle=="出差"         //行政
            ||guideTitle=="消息列表"||guideTitle=="费用"||guideTitle=="资产管理"||guideTitle=="会议室管理"
            ||guideTitle=="我的请假"||guideTitle=="请假记录"||guideTitle=="审批记录"
            ||guideTitle=="我的出差"||guideTitle=="出差记录"||guideTitle=="邮件通知"||guideTitle=="兴趣小组"
            ||guideTitle=="加班"||guideTitle=="申购记录"||guideTitle=="申购"||guideTitle=="公告"||guideTitle=="其他"){
            $("#guide-administration").removeClass("hidden");
            $("#administration").parent().addClass("active");
        }
    }
}

// 滑动设置
function guideSlide(obj){
    $(obj).next().slideToggle(400);
}



/**
*等待框
*@content 内容
**/
function showWait(content){
    var str = "<div id='page-wait' class='modal fade in bor-rad-5 hint' style='display: none;'>"+
    "<div class='modal-header bg-33 move'><a class='close' data-dismiss='modal'>×</a><h4 class='hint-title'>请稍候</h4></div>"+
    "<div class='modal-body'><div class='m0a p0 w50'><img src='/images/loading.gif' class='w50 h50'></div><h3 class='hint-content'>"+content+"</h3></div>"+
    "</div>";

    // 判断是否已经存在
    if($(document).find("#page-wait").text()==""){
        $("html").append(str);
    }else{
        $("#page-wait").remove();
        $("html").append(str);
    }
    $("#page-wait").find("div.modal-header").attr("onmousedown","beforeMove('page-wait',event);");

    // 显示等待框
    var ySet = (window.innerHeight - $("#page-wait").height())/3;
    var xSet = (window.innerWidth - $("#page-wait").width())/2;
    $("#page-wait").css("top",ySet);
    $("#page-wait").css("left",xSet);
    $('#page-wait').modal({show:true});
  }


/**
*提示框
*@title 提示框标题
*@content 提示内容
**/
function showHint(title,content){
    var str="<div id='page-hint' class='modal fade in bor-rad-5 hint' style='display: none;'>"+
    "<div class='modal-header bg-33'><a class='close' data-dismiss='modal'>×</a><h4 class='hint-title'>"+title+"</h4></div>"+
    "<div class='modal-body'><h3 class='hint-content'>"+content+"</h3></div>"+
    "</div>";

    // 判断是否已经存在
    if($(document).find("#page-hint").text()==""){
    	$("html").append(str);
    }
    else{
        $("#page-hint").remove();
        $("html").append(str);
    }

    // 显示提示框
    var ySet = (window.innerHeight - $("#page-hint").height())/3;
    var xSet = (window.innerWidth - $("#page-hint").width())/2;
    $("#page-hint").css("top",ySet);
    $("#page-hint").css("left",xSet);
    $('#page-hint').modal({show:true});

    // 定时消失
    setTimeout(function(){
        $('#page-hint').modal('hide');
    },1800);
}

/**
*确认框
*@title 提示框标题
*@content 提示内容
*@str1 按钮1的文字
*@f1 按钮1点击执行的函数
*@str2 按钮2的文字
**/
function showConfirm(title,content,str1,f1,str2){
	var str = "<div id='page-confirm' class='modal fade in bor-rad-5 hint' style='display: none;'>"+
    "<div class='modal-header bg-33 move'><a class='close' data-dismiss='modal'>×</a><h4 class='hint-title'>"+title+"</h4></div>"+
    "<div class='modal-body'><h3 class='hint-content'>"+content+"</h3></div>"+
    "<div class='modal-footer'><button class='btn btn-success w80' onclick='"+f1+"' data-dismiss='modal'>"+str1+"</button><button class='btn w80' data-dismiss='modal'>"+str2+"</button></div>"+
    "</div>";

    // 判断是否已经存在
    if($(document).find("#page-confirm").text()==""){
    	$("html").append(str);
    }else{
        $("#page-confirm").remove();
        $("html").append(str);
    }
    $("#page-confirm").find("div.modal-header").attr("onmousedown","beforeMove('page-confirm',event);");
    $("#page-confirm").find("div.modal-header").attr("onmousedown","beforeMove('page-confirm',event);");

    // 显示确认框
    var ySet = (window.innerHeight - $("#page-confirm").height())/3;
    var xSet = (window.innerWidth - $("#page-confirm").width())/2;
    $("#page-confirm").css("top",ySet);
    $("#page-confirm").css("left",xSet);
    $('#page-confirm').modal({show:true});
}


/**
*去除空格
**/
function trim(str){ //删除左右两端的空格
　　 return str.replace(/(^\s*)|(\s*$)/g, "");
}
function ltrim(str){ //删除左边的空格
　　 return str.replace(/(^\s*)/g,"");
}
function rtrim(str){ //删除右边的空格
　　 return str.replace(/(\s*$)/g,"");
}


/**
*心跳请求，每一分钟发送一次
**/
// $(document).ready(function(){
//     heartBeat();
//     setInterval("heartBeat()",60000);

//     // 新消息窗口随页面滚动
//     window.onscroll = function(){
//         var top = $("html").scrollTop();
//         if(top == 0) top = document.body.scrollTop;
//         bottom =  0 - top;
//         $("#newMsg-div").css("bottom",bottom);
//         if((document.body.scrollHeight - window.innerHeight) >= top){
//             $("#newMsg-div").css("bottom",bottom);
//         }
        
//         bottom = window.innerHeight/2 - top;
//         $("#opinion-button").css("bottom",bottom);
//     };
// });

window.onload = function(){
    heartBeat();
    setInterval("heartBeat()",60000);

    // 新消息窗口随页面滚动
    window.onscroll = function(){
        var top = $("html").scrollTop();
        if(top == 0) top = document.body.scrollTop;
        bottom =  0 - top;
        $("#newMsg-div").css("bottom",bottom);
        if((document.body.scrollHeight - window.innerHeight) >= top){
            $("#newMsg-div").css("bottom",bottom);
        }
        
        bottom = window.innerHeight/2 - top;
        $("#opinion-button").css("bottom",bottom);
    };

    guideSet(window.location.pathname);
};

/**
*心跳请求函数
**/
function heartBeat(){
    $.ajax({
        type:'post',
        dataType:'json',
        url:'/ajax/heartbeat',
        data:{},
        success:function(result){
            if(result.code == 0)
            {
                if(!$("#breadcrumbs-div").hasClass("hidden")){
                    // 消息数
                    var msg_count = result.count;

                    // 当前访问地址
                    var href_str = location.href;

                    if(msg_count!=0 && href_str.indexOf('/oa/msgs/status/wait')< 0){ // 如果当前在未读消息页面则不提示新消息
                        // 显示消息提示框
                        var str = "<a href='/oa/msgs/status/wait'>您有"+msg_count+"条新的未读消息</a>";
                        $("#content-text").html(str);
                        var top = $("html").scrollTop();
                        if(top == 0) top = document.body.scrollTop;
                        bottom =  0 - top;
                        $("#newMsg-div").css("bottom",bottom);
                        $("#newMsg-div").slideDown(200);
                        
                        // 修改右上角消息图标
                        var msg_str = "<span class='glyphicon glyphicon-envelope'></span>&nbsp;["+msg_count+"条新消息]";
                        $("#user-msg-btn").html(msg_str);
                        $("#user-msg-btn").addClass("b33");
                    }else if(msg_count==0){  // 如果新消息数为0
                        // 修改右上角消息图标
                        var msg_str = "<span class='glyphicon glyphicon-envelope'></span>&nbsp;消息";
                        $("#user-msg-btn").html(msg_str);
                        $("#user-msg-btn").removeClass("b33");
                    }
                }
            }
            else if(result.code == -1)
            {
                showHint("提示信息","网络连接异常，请检查你的网络设置！");
            }
            else
            {
                showHint("提示信息","网络连接异常，请检查你的网络设置！");
            }
        }
    });
}


/**
*拖动元素之前
**/
var deltaX,deltaY;
function beforeMove(id,e){
    var move_str = "elementMove('"+id+"',event);";
    var after_move_str = "afterMove('"+id+"',event);";
    $("#"+id).attr("onmousemove", move_str);
    $("#"+id).attr("onmouseup", after_move_str);
    deltaX = e.clientX - parseInt($("#"+id).css("left"));
    deltaY = e.clientY - parseInt($("#"+id).css("top"));
}

/**
*拖动元素
**/
function elementMove(id,event){
    if(!event) event = window.event;
    var left = (event.clientX - deltaX) + "px";
    var top = (event.clientY - deltaY) + "px";
    $("#"+id).css("left", left);
    $("#"+id).css("top", top);
}

/**
*拖动元素之后
**/
function afterMove(id,event){
    if(!event) event = window.event;
    $("#"+id).attr("onmousemove","");
    $("#"+id).attr("onmouseup","");
}

