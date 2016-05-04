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

    // 判断是否已存在
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