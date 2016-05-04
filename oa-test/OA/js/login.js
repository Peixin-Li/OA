// 页面初始化
$(document).ready(function(){
	// 注册按钮事件
    $("#submit").click(function(){login();});

    // 绑定回车键
    document.onkeydown = function(e){
        if(!e) e = window.event;//火狐中是 window.event
        if((e.keyCode || e.which) == 13) login();
    }
});

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

