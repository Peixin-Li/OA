<?php
echo "<script type='text/javascript'>";
echo "console.log('mail');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/datepicker_cn.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui-timepicker-addon.css" />

<!-- 主界面 -->
<div>
    <h4 class="pd10 m0 b33 bor-1-ddd">群发邮件</h4>
    <!-- 第一步 -->
    <!-- 发件人 -->
    <div id="sender-div"  class="bor-1-ddd">
        <label class="m0 pd10">选择发件人：</label>
        <div id="sender-select-div" class="pd10 bor-b-1-ddd">
            <button class="btn btn-default w100" onclick="senderSet(this);">HR</button>
            <!-- <button class="btn btn-default w100" onclick="senderSet(this);">IT</button>
            <button class="btn btn-default w100" onclick="senderSet(this);">行政助理</button> -->
        </div>
        <button class="btn btn-primary w100 ml10 mt10 mb10 disabled" onclick="$('#sender-div').addClass('hidden');$('#receive-div').removeClass('hidden');">下一步</button>
    </div>
    <!-- 第二步 -->
    <!-- 收件人 -->
    <div id="receive-div"  class="hidden bor-1-ddd">
        <!-- 标题 -->
        <h4 class="pl10">选择收件人：</h4>
        <div class="pd10 bor-b-1-ddd" id="receive-type">
            <button class="btn btn-default w100" onclick="receiveSet(this);">公司内部</button>
            <button class="btn btn-default w100" onclick="receiveSet(this);">其他</button>
        </div>
        
        <div id="receive-company" class="hidden pd10 bor-b-1-ddd">
            <button class="btn btn-default pd5" id="all">所有人</button>
            <?php 
                foreach($departments as $department){
                    if($department['department_status'] == "display"){
                        echo "<button class='btn btn-default pd5 ml10' id='{$department->name}'>{$department->name}</button>";
                    }
                }
            ?>
        </div>

        <div id="employee-remind" class="hidden">
            <h5 class="pl10">已选收件人：</h5>
        </div>
        <!-- 员工 -->
        <div id="receive-employee" class="hidden pd10 bor-b-1-ddd">
            <?php 
                if(!empty($users)){
                    foreach($users as $user){
                        if($user->department->department_status == "display"){
                            echo "<button class='btn btn-success hidden w100 mr10 mb10' name='{$user->department->name}' id='{$user->user_id}'>{$user->cn_name}&nbsp;<span class='glyphicon glyphicon-remove'></span></button>";
                        }
                    }
                }
            ?>
        </div>
        <div id="all-div" class="hidden pd10 bor-b-1-ddd">
            <button class="btn btn-success w100 mb10">所有人</button>
        </div>

        <div id="receive-other" class="hidden pd10 bor-b-1-ddd">
            <label>输入邮箱:</label>
            <div class="form-control" id="receives" style="height:auto;word-break:break-all;">
                <input class="fl w800 bor-none" id="email-received"  onfocus="checkReceive();" onblur="clearInterval(checkInterval);">
                <div class="clear"></div>
            </div>
        </div>
        <button class="btn btn-primary w100 ml10 mt10 mb10" id="receive-prev" onclick="$('#sender-div').removeClass('hidden');$('#receive-div').addClass('hidden');">上一步</button>
        <button class="btn btn-primary w100 ml10 mt10 mb10 disabled" id="receive-next" onclick="receiveNext();">下一步</button>
    </div>
    <!-- 第三步 -->
    <div id="model-div" class="hidden pd10 bor-1-ddd">
        <label>选择邮件模板:&nbsp;</label>
        <select class="w200 form-control" id="model-select" onchange="modelSelect()">
            <option value="welcome">欢迎入职模板</option>
            <option value="interview">面试模板</option>
            <option value="offer">offer模板</option>
        </select>

        <label class="mt10">填写邮件主题:</label>
        <input class="form-control mb15" id="detail-theme" placeholder="请输入邮件主题">

        <div id="detail-div" class="mt10">
            <!-- 欢迎表格 -->
            <table class="table bor-1-ddd" id="welcome-table">
                <tr>
                    <th class="center w100">姓名</th>
                    <td><input class="form-control w200" id="welcome-name" placeholder="请输入新员工姓名"></td>
                </tr>
                <tr>
                    <th class="center w100">部门</th>
                    <td>
                        <select id="welcome-department" class="w200 inline form-control">
                            <?php
                                foreach($departments as $department){
                                    if($department['department_status'] == "display"){
                                        echo "<option value='{$department->name}'>{$department->name}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="center w100">职位</th>
                    <td><input class="form-control w200" id="welcome-title" placeholder="请输入新员工职位"></td>
                </tr>
                <tr>
                    <th class="center w100">企业QQ</th>
                    <td><input class="form-control w200" id="welcome-qq" placeholder="请输入新员工QQ"></td>
                </tr>
                <tr>
                    <th class="center w100">企业邮箱</th>
                    <td><input class="form-control w200" id="welcome-email" placeholder="请输入新员工邮箱"></td>
                </tr>
                <tr>
                    <th class="center w100">电话</th>
                    <td><input class="form-control w200" id="welcome-mobile" placeholder="请输入新员工电话"></td>
                </tr>
                <tr>
                    <th class="center w100">照片</th>
                    <td><img src="" id="imgPre" alt="待上传员工图片">(请选择jpg、png或gif格式,小于2M的图片)<input type="file" id="imgOne" onchange="preImg(this.id,'imgPre');"></td>
                </tr>
            </table>
            <!-- 面试表格 -->
            <table class="table bor-1-ddd hidden" id="interview-table">
                <tr>
                    <th class="center w100">姓名</th>
                    <td><input class="form-control w200" id="interview-name" placeholder="请输入面试人的姓名"></td>
                </tr>
                <tr>
                    <th class="center w100">日期</th>
                    <td><input class="form-control w200 pointer" id="interview-date" placeholder="请输入面试日期"></td>
                </tr>
                <tr>
                    <th class="center w100">职位</th>
                    <td><input class="form-control w200" id="interview-title" placeholder="请输入面试的职位"></td>
                </tr>
            </table>
            <!-- offer表格 -->
            <table class="table bor-1-ddd hidden" id="offer-table">
                <tr>
                    <th class="center w100">姓名</th>
                    <td><input class="form-control w200" id="offer-name" placeholder="请输入姓名"></td>
                </tr>
                <tr>
                    <th class="center w100">职位</th>
                    <td><input class="form-control w200" id="offer-title" placeholder="请输入职位"></td>
                </tr>
                <tr>
                    <th class="center w130">试用期税前月薪</th>
                    <td><input class="form-control w200 inline" id="offer-b-salary" placeholder="请输入税前月薪">&nbsp;元</td>
                </tr>
                <tr>
                    <th class="center w130">转正后税前月薪</th>
                    <td><input class="form-control w200 inline" id="offer-a-salary" placeholder="请输入税后月薪">&nbsp;元</td>
                </tr>
            </table>
        </div>
        <button class="btn btn-primary w100 ml10 mt10 mb10" id="detail-prev" onclick="$('#receive-div').removeClass('hidden');$('#model-div').addClass('hidden');">上一步</button>
        <button class="btn btn-primary w100 ml10 mt10 mb10" id="detail-next" onclick="preview();">下一步</button>
    </div>
</div>
<!-- 第四步 -->
<div class="hidden" id="preview-div">
  <table class="table bor-1-ddd">
    <tr>
        <th class="w100 center">发件人</th>
        <td id="last-sender"></td>
    </tr>
    <tr>
        <th class="w100 center">收件人</th>
        <td id="last-receive"></td>
    </tr>
    <tr>
        <th class="w100 center">主题</th>
        <td id="last-theme"></td>
    </tr>
    <tr id="preview-tr">
        <th class="w100 center">正文</th>
        <td class="w970 bor-1-ddd" id="last-content"></td>
    </tr>
    <tr class="hidden" id="edit-tr">
        <th class="w100 center">正文</th>
        <td class="w970"><script id="editor" type="text/plain" class="w100%" style="height:500px;"></script></td>
    </tr>
    <tr>
        <th class="w100 center">操作</th>
        <td>
            <button class="btn btn-primary w100 ml10 mt10 mb10" id="preview-prev" onclick="$('#preview-div').addClass('hidden');$('#model-div').removeClass('hidden');">上一步</button>
            <button class="btn btn-success w100" id="edit-email" onclick="editEmail()">编辑邮件正文</button>
            <button class="btn btn-success w100 hidden" id="save-email" onclick="saveEmail()">保存</button>
            <button class="btn btn-success w100" id="send-email" onclick="sendEmail()">发送邮件</button>
        </td>
    </tr>
  </table>
</div>

<!-- js -->
<script type="text/javascript">
    // 页面初始化
    $(document).ready(function(){
        // 日期选择控件初始化
        $('#interview-date').datepicker({dateFormat: 'yy-mm-dd',changeYear: true});
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']);

        // 注册点击事件
        $("#receive-company").children().bind("click",function(){
            departmentSelect(this.id);
        });
        $("#receive-employee").children().bind("click",function(){
            employeeSelect(this.id);
        });
    });

    // 发件人
    var sender = "";
    function senderSet(obj){
        $(obj).removeClass("btn-default");
        $(obj).parent().find(".btn-success").addClass("btn-default").removeClass("btn-success");
        $(obj).addClass("btn-success");
        $("#sender-div").find("button").removeClass("disabled");
        sender = $(obj).text();
    }

    // 收件人
    // 选择公司内部或者其他
    function receiveSet(obj){
        $(obj).parent().find(".btn-success").addClass("btn-default").removeClass("btn-success");
        $(obj).removeClass("btn-default").addClass("btn-success");
        if($(obj).text()=="公司内部"){
            $("#receive-company").children().removeClass("btn-success").addClass("btn-default");
            $("#all-div").addClass("hidden");
            $("#receive-employee").children().addClass("hidden");
            $("#receive-company").removeClass("hidden");
            $("#receive-other").addClass("hidden");
            $("#employee-remind").removeClass("hidden");
            $("#receive-next").addClass("disabled");
        }else if($(obj).text()=="其他"){
            $("#all-div").addClass("hidden");
            $("#receive-other").removeClass("hidden");
            $("#receive-employee").addClass("hidden");
            $("#receive-company").addClass("hidden");
            $("#employee-remind").addClass("hidden");
            $("#receive-next").addClass("disabled");
        }
    }

    // 选择部门
    function departmentSelect(id){
        $("#receive-employee").removeClass("hidden");  //显示员工
        if(id == "all"){
            if($("#all").hasClass("btn-success")){      // 已经点亮，则去掉所有人
                $("#receive-company").children().removeClass("btn-success").addClass("btn-default");  //所有部门熄灭
                $("#receive-employee").children().addClass("hidden");
            }else{
                $("#receive-company").children().removeClass("btn-default").addClass("btn-success");  //所有部门都变为绿色
                $("#receive-employee").children().removeClass("hidden");
            }
        }else{
            if($("#"+id).hasClass("btn-success")){    //已经点亮，则去掉该部门的人
                $("#"+id).removeClass("btn-success").addClass("btn-default");       //熄灭部门按钮
                $("#receive-employee").children().each(function(){
                    if($(this).attr("name")==id){
                        $(this).addClass("hidden");
                    }
                });
            }else{                                      //没有点亮，则加入该部门的人
                $("#"+id).removeClass("btn-default").addClass("btn-success");  //只有点击的部门变为绿色
                $("#receive-employee").children().each(function(){
                    if($(this).attr("name")==id){
                        $(this).removeClass("hidden");
                    }
                });
            }
        }

        selectCheck("");
    }

    // 选中检查
    function selectCheck(id){
        if($("#receive-employee").find(".hidden").text()==""){         // 选中全部人
            $("#receive-company").children().removeClass("btn-default").addClass("btn-success");
            addReceive();
            $("#receive-next").removeClass("disabled");
        }else if($("#receive-employee").find(".hidden").text()!=""){        // 选中不为空     
            var click_count = 0;
            $("#all").removeClass("btn-success").addClass("btn-default");
            $("#receive-employee").children().each(function(){
                if(!$(this).hasClass("hidden")){
                    click_count++;
                }
            });
            if(click_count == 0){                                           // 没有选中一个人
                $("#receive-company").children().removeClass("btn-success").addClass("btn-default");  // 熄灭所有部门按钮
                $("#receive-next").addClass("disabled");
            }else{
                addReceive();                                                   // 添加选中的人到数组中
                $("#receive-next").removeClass("disabled");                     // 点亮下一步的图标
            }
        }

        // 点击个人的检测
        if(id!=""){
            var d_select = $("#"+id).attr("name");    // 部门名
            var total = 0;                             //部门总人数
            var count = 0;                              // 该部门点亮的人数
            $("#receive-employee").children().each(function(){
                if($(this).attr("name") == d_select){
                    total++;
                    if(!$(this).hasClass("hidden")){
                        count++;
                    }
                }
            });

            if(count == total){                   // 该部门所有人被点亮，则该部门的按钮点亮
                $("#receive-company").children().each(function(){
                    if($(this).text()==d_select) $(this).removeClass("btn-default").addClass("btn-success");
                });
            }else{                             // 该部门点亮人数小于总人数，则该部门的按钮熄灭
                $("#receive-company").children().each(function(){
                    if($(this).text()==d_select) $(this).removeClass("btn-success").addClass("btn-default");
                    $("#all").removeClass("btn-success").addClass("btn-default");
                });
            }
        }

        if($("#all").hasClass("btn-success")){
            $("#receive-employee").addClass("hidden");
            $("#all-div").removeClass("hidden");
        }else{
            $("#receive-employee").removeClass("hidden");
            $("#all-div").addClass("hidden");
        }
    }
    
    // 点击个人
    function employeeSelect(id){
        $("#"+id).addClass("hidden");
        selectCheck(id);
    }

    // 添加已点亮的人的id到send_arr数组中
    var send_arr;
    function addReceive(){
        send_arr = new Array();
        $("#receive-employee").children().each(function(){
            if(!$(this).hasClass("hidden")){
                send_arr.push($(this).attr("id"));
            }
        });

        // $.each(send_arr,function(key,value){
        //     alert(value);
        // });
    }

    // 选好收件人后的操作
    var email_type = "";
    var email_arr = new Array();
    function receiveNext(){
        email_type = $("#receive-type").find(".btn-success").text();
        if(email_type == "其他"){
            //已输入并形成div的收件人
            if($("#receives").find("div.fl").text()!=""){
                $("#receives").find("div.fl").each(function(){
                    var text = $(this).text();
                    var detail_arr = text.split("\;");
                    trim(detail_arr[0]);

                    // 判断数组中是否已经添加过
                    var find_tag = false;
                    $.each(email_arr, function(key,value){
                        if(value.indexOf(trim(detail_arr[0])) > -1){
                            find_tag = true;
                        }
                    });
                    if(!find_tag){
                        email_arr.push(trim(detail_arr[0]));
                    }
                });
            }

            //最后一个输入的，就是在input里面的
            var last_receive = $("#email-received").val();
            var email_pattern = /^[\w\-\_\.]+\@[\w\-\_\.]+$/;
            if(last_receive!=""){                                            
                //如果有输入分号则去掉分号
                if(last_receive.indexOf(";")>-1){
                    var last_receive_arr=last_receive.split("\;");
                    last_receive = last_receive_arr[0];
                }


                if(!email_pattern.exec(last_receive)){      //判断格式是否正确
                    showHint("提示信息","收件人邮箱格式错误！");
                    return false;
                }else{                                      //正确则判断前面是否有重复联系人
                    var flag = 0;
                    if($("#receives").find("div.fl").text!=""){
                        $("#receives").find("div.fl").each(function(){
                            if($(this).text().indexOf(last_receive)>-1){
                                showHint("提示信息","存在重复联系人!");
                                $(this).addClass("bg-66");
                                flag = 1;
                            }
                        });
                        if(flag == 0){
                            trim(last_receive);

                            // 判断数组中是否已经添加过
                            var find_tag = false;
                            $.each(email_arr, function(key,value){
                                if(value.indexOf(last_receive) > -1){
                                    find_tag = true;
                                }
                            });
                            if(!find_tag){
                                email_arr.push(last_receive);
                            }

                        }
                    }
                } 
            }

            //email数组中为空，提示输入收件人
            if(email_arr.length==0){
                showHint("提示信息","请输入收件人！");
                return false;
            }else{
                //邮箱检测
                $.each(email_arr,function(){
                    if(!email_pattern.exec(this)){
                        showHint("提示信息","收件人邮箱格式错误！");
                        return false;
                    }
                });
            }
            
            $('#receive-div').addClass('hidden');
            $('#model-div').removeClass('hidden');
        }else{
            $('#receive-div').addClass('hidden');
            $('#model-div').removeClass('hidden');
        }
        

        
    }

    // 选择模板
    var model = "";
    function modelSelect(){
        model = $("#model-select").val();
        $("#detail-div").find("input").val("");
        switch(model){
            case "interview":{
                $("#detail-div").children().addClass("hidden");
                $("#interview-table").removeClass("hidden");
                break;
            }
            case "offer":{
                $("#detail-div").children().addClass("hidden");
                $("#offer-table").removeClass("hidden");
                break;
            }
            case "welcome":{
                $("#detail-div").children().addClass("hidden");
                $("#welcome-table").removeClass("hidden");
                break;
            }
        }
    }

    // 邮件预览
    var model_str = "";
    var img_str = "";
    var theme = "";
    var m_flag = 0;

    // 上传图片
    function UploadFile() {
        var fileObj = document.getElementById("imgOne").files[0]; // 获取文件对象
        if(typeof(fileObj) != "undefined"){
            var FileController = "/ajax/imgUpload";                    // 接收上传文件的后台地址 
            // FormData 对象
            var form = new FormData();
            form.append("upload_pic", fileObj);                       // 文件对象

            // XMLHttpRequest 对象
            var xhr = new XMLHttpRequest();
            xhr.open("post", FileController, true);
            xhr.onload = function () {
                // showHint("提示信息","上传成功");
            };
            xhr.send(form);
            
            xhr.onreadystatechange=function(){
                if (xhr.readyState==4 && xhr.status==200){
                    var response = xhr.responseText;
                    try{
                        domParser = new  DOMParser();
                        xmlDoc = domParser.parseFromString(response, 'text/xml');
                        var code = xmlDoc.getElementsByTagName("code")[0].childNodes[0].nodeValue;
                        var url = xmlDoc.getElementsByTagName("url")[0].childNodes[0].nodeValue;
                    }catch(e){
                        showHint("提示信息","解析返回信息失败，请重试");
                    }
                    if(code == 0){
                        if(url != ""){
                            img_str = "<p ><span style='font-size:19px;font-family:宋体'><img src='"+url+"' style='width:100px;' alt='待上传员工图片'></span></p>";
                            if(m_flag == 1){
                                showPreviewDiv();
                            }
                        }
                    }else if(code == -1){
                        showHint("提示信息","上传图片失败");
                    }else if(code == -2){
                        showHint("提示信息","参数错误");
                    }else if(code == -4){
                        showHint("提示信息","图片格式不正确");
                    }else if(code == -5){
                        showHint("提示信息","图片大小超过了2M");
                    }
                    
                }
            }
        }else{
            showHint("提示信息","请选择照片！");
        }
    }

    // 预览
    function preview(){
        theme = $("#detail-theme").val();
        if(theme.length < 1){
            showHint("提示信息","请输入邮箱主题");
            return false;
        }

        var mobile_pattern = /^\d{1}\d{10}$/;
        var email_pattern = /^[\w\-\_\.]+\@[\w\-\_\.]+$/;
        var d_pattern = /^\d+$/;
        var date_pattern = /^\d{4}-\d{2}-\d{2}$/;
        model = $("#model-select").val();
        switch(model){
            case "welcome":{
                if($("#welcome-name").val()==""){
                    showHint("提示信息","请输入新入职员工姓名");
                }else if($("#welcome-title").val()==""){
                    showHint("提示信息","请输入新入职员工职位");
                }else if(!d_pattern.exec($("#welcome-qq").val())){
                    showHint("提示信息","QQ邮箱格式错误");
                }else if(!email_pattern.exec($("#welcome-email").val())){
                    showHint("提示信息","邮箱格式错误");
                }else if(!mobile_pattern.exec($("#welcome-mobile").val())){
                    showHint("提示信息","电话格式错误");
                }else{
                    m_flag = 1;
                }
                break;
            }
            case "interview":{
                if($("#interview-name").val()==""){
                    showHint("提示信息","请输入面试人姓名！");
                }else if(!date_pattern.exec($("#interview-date").val())){
                    showHint("提示信息","日期格式错误！");
                }else if($("#interview-title").val()==""){
                    showHint("提示信息","请输入面试职位！");
                }else{
                    m_flag = 1;
                }
                break;
            }
            case "offer":{
                if($("#offer-name").val()==""){
                    showHint("提示信息","请输入录用人姓名！");
                }else if($("#offer-title").val()==""){
                    showHint("提示信息","请输入录用职位！");
                }else if(!d_pattern.exec($("#offer-b-salary").val())){
                    showHint("提示信息","试用期税前月薪格式错误！");
                }else if(!d_pattern.exec($("#offer-a-salary").val())){
                    showHint("提示信息","转正税前月薪格式错误！");
                }else{
                    m_flag = 1;
                }
                break;
            }
        }

        // 上传图片
        if(model == "welcome"){
            if(m_flag == 1){
                UploadFile();
            }
        }else{
            if(m_flag == 1){
                showPreviewDiv();
            }
        }
    }

    // 显示预览页面
    function showPreviewDiv(){
        $('#model-div').addClass('hidden');
        $('#preview-div').removeClass('hidden');
        email_type = $("#receive-type").find(".btn-success").text();
        var sender = $("#sender-div").find(".btn-success").text();
        $("#last-sender").text(sender);

        // 添加收件人  如果选择了所有人则只显示所有人，否则显示部门，如果不够一个部门的，单独显示
        if(email_type=="其他"){
            // 清空收件人
            $("#last-receive").children().remove();
            
            $.each(email_arr,function(key,value){
                var str = "<button class='btn btn-success cur-default pd5 mr20 mb10'>"+value+"</button>";
                $("#last-receive").append(str);
            });
        }else{
            var all_tag = 0;
            $("#last-receive").html("");
            $("#receive-company").find(".btn-success").each(function(){
                if($(this).attr("id")=="all"){
                    var str = "<button class='btn btn-primary cur-default pd5 mr20 mb10'>所有人</button>";
                    $("#last-receive").append(str);
                    all_tag = 1;
                }else if(all_tag != 1){
                    var str = "<button class='btn btn-primary cur-default pd5 mr20 mb10'>"+$(this).text()+"</button>";
                    $("#last-receive").append(str);
                }
            });
        }
        

        // 单独显示个人
        $("#receive-employee").children().each(function(){
            if(!$(this).hasClass("hidden")){
                var department_e = $(this).attr("name");
                var name = $(this).text();
                var flag = 0;
                if($("#receive-company").find(".btn-success").text()!=""){
                    $("#receive-company").find(".btn-success").each(function(){
                        if(department_e == $(this).text()){
                            flag = 0;
                            return false;
                        }else{
                            flag = 1;
                        }
                    });
                    if(flag == 1){
                        var str = "<button class='btn btn-success cur-default pd5 mr20 mb10'>"+name+"</button>";
                        $("#last-receive").append(str);
                    }
                }else{
                    var str = "<button class='btn btn-success cur-default pd5 mr20 mb10'>"+name+"</button>";
                    $("#last-receive").append(str);
                }
            }
        });

        $("#last-theme").text($("#detail-theme").val());

        //模板设置
        switch(model){
            case "interview":{
                var date_str = $("#interview-date").val();
                var date_arr = date_str.split("-");
                month = date_arr[1];
                day = date_arr[2];
                model_str = "<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'><span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#interview-name").val()+"&nbsp; </span></span><span style='font-size:19px;font-family:宋体;color:black'>：</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>您好！</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>我是七喜控股善游网络的HR曾小姐，经过跟您的初步了解，现诚邀您于<span style='text-decoration:underline;'><strong>&nbsp;"+month+"&nbsp; 月&nbsp;"+day+"&nbsp; 日下午16：00</strong></span>前往我司面试<strong><span style='text-decoration:underline;'><span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#interview-title").val()+"&nbsp; </span></span></strong>一职。</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>面试地址：天河北路908号高科大厦B座701善游网络（华师地铁站B出口喜士多旁）</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>广州善游网络科技有限公司由七喜控股全资投资拥有。公司成立于2013年，是一家专注于手机游戏研发的互联网企业。</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>想了解更多相关信息，请戳<strong>公司官网</strong>：</span><a href='http://shanyougame.com/'><span style='font-size:19px;font-family:宋体;color:blue'>http://shanyougame.com/</span></a></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>如果有任何问题，可随时联系我。</span></p>"+
"<p><br/></p>";
                $("#last-content").html(model_str);
                break;
            }
            case "offer":{
                model_str = "<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'><span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#offer-name").val()+"&nbsp; </span></span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>：&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>你好！</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>我代表<strong><span style='text-decoration:underline;'>广州善游网络科技有限公司</span></strong>通知你，现正式录取你任职本司<strong><span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#offer-title").val()+"&nbsp; </span></strong>一职。</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>以下为该岗位相关信息：</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>岗位： <span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#offer-title").val()+"&nbsp; <br/></span></span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>月薪：试用期税前<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#offer-b-salary").val()+"&nbsp; <br/></span>元/月，转正后税前<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#offer-a-salary").val()+"&nbsp; <br/></span>元/月。（正式转正后，退回试用期工资差额）</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>试用期：一个月</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family: &#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>请你于9月9日早上9点30分到公司报到时携带以下资料：</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>1</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、身份证原件和复印件；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>2</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、学历证原件和复印件；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>3</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、小一寸相片3张；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>4</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、离职证明；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>5</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、近三个月的体检报告，体检费用由公司报销，金额100元以内（入职体检项目包括：1、五官科 &nbsp;2、血常规 &nbsp;3、身高、体重 &nbsp; 4、肝功能 &nbsp;5、内科等）；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>6</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、广州招商银行卡复印件。</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px'><strong><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>公司福利：</span></strong></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>1</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、社会保险一金：员工入职次月即购买五险一金；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>2</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、住房补贴：员工可享有500元/月/人的租房补贴；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>3</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、工作餐：公司为员工免费提供工作餐（中午发放餐补&lt;15元/餐&gt;，晚上由公司统一订餐）；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>4</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、下午茶：公司为员工免费提供下午茶和咖啡、奶茶、茶包等饮料；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>5</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、带薪年假：除国家规定的带薪假期外，公司提供带薪年假（一年以上7天，三年以上10天，五年以上15天）；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>6</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、体检：员工可享受一年一次的健康体检；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>7</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、生日贺礼：员工生日可享受公司为其准备的生日礼物或礼金；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>8</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、旅游：公司每年组织1-2次的旅游；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>9</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、公司活动：公司定期根据员工建议组织各种兴趣小组、娱乐、体育、郊游活动；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>10</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、部门活动：员工每月享有100元/月/人的活动经费；</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>11</span><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>、年终奖：公司根据绩效考核成绩，实行年终奖金奖励机制。</span></p>"+
"<p style='text-align:left;line-height:21px'><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>&nbsp;</span></p>"+
"<p style='text-align:left;line-height:21px'><strong><span style=';font-family:&#39;微软雅黑&#39;,&#39;sans-serif&#39;;color:black'>欢迎你的加入，如有任何疑问可随时与我联系。</span></strong></p>"+
"<p><br/></p>";
                $("#last-content").html(model_str);
                break;
            }
            case "welcome":{
                model_str = "<p><span style='font-size:19px;font-family:宋体'>Dear all</span><span style='font-size:19px;font-family:宋体'>：</span></p>"+
"<p ><span style='font-size:19px;font-family:宋体'>欢迎今天入职的新员工<span style='text-decoration: underline;'>&nbsp;"+$("#welcome-name").val()+"&nbsp; </span>加入我们善游团队。</span></p>"+img_str+
"<p ><span style='font-size:19px;font-family:宋体'>姓名：<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#welcome-name").val()+"&nbsp; <br/></span></span></p>"+
"<p ><span style='font-size:19px;font-family:宋体'>部门：<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#welcome-department").val()+"&nbsp; <br/></span></span></p>"+
"<p ><span style='font-size:19px;font-family:宋体'>职位：<span style='color:black;background:white'><span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#welcome-title").val()+"&nbsp; <br/></span></span></span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>企业QQ：<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#welcome-qq").val()+"&nbsp; <br/></span></span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>企业邮箱：<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#welcome-email").val()+"&nbsp; <br/></span></span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'></span><span style='font-size:19px;font-family:宋体;color:black'>电话：<span style='font-size: 19px; font-family: 宋体; text-decoration: underline;'>&nbsp;"+$("#welcome-mobile").val()+"&nbsp; <br/></span></span></p>"+
"<p style='text-align:left;line-height:21px;background:white'><span style='font-size:19px;font-family:宋体;color:black'>&nbsp;</span></p>"+
"<p ><span style='font-size:19px;font-family:宋体'>请各位同事积极支持和配合他们的工作！</span><span style='font-size:19px;font-family:宋体;color:black'>希望大家</span><span style='font-size:19px;font-family:宋体;color:black;background:white'>一起与公司共同成长！</span></p>"+
"<p ><span style='font-size:19px;font-family:宋体;color:black;background:white'><br/></span></p>";
                $("#last-content").html(model_str);
                break;
            }
        }   
    }

    //图片预览设置
    function preImg(sourceId, targetId) { 
      //先隐藏，判断图片大于100像素才显示
      $("#example").addClass("hidden");

      //浏览器支持的判断
      if (typeof FileReader === 'undefined') {  
          alert('Your browser does not support FileReader...');  
          return;  
      }  
      var reader = new FileReader();  

      reader.onload = function(e) {  
        //给预览图src赋值
        var src = this.result;
        var img = document.getElementById(targetId);
        img.src = src;  
      }  
      reader.readAsDataURL(document.getElementById(sourceId).files[0]); 

      //设置大预览图的宽度 
      $("#imgPre").attr("width","200");
    }  


    function editEmail(){
        $("#edit-tr").removeClass("hidden");
        $("#preview-tr").addClass("hidden");
        var ue = UE.getEditor('editor');
        setTimeout(function(){ue.setContent(model_str, false);},500);
        $("#edit-email").addClass("hidden");
        $("#save-email").removeClass("hidden");
        $("#send-email").addClass("disabled");
        $("#preview-prev").addClass("disabled");
    }

    function saveEmail(){
        $("#save-email").addClass("hidden");
        $("#edit-email").removeClass("hidden");
        $("#send-email").removeClass("disabled");
        var content = UE.getEditor('editor').getContent();
        $("#last-content").html(content);
        $("#preview-tr").removeClass("hidden");
        $("#edit-tr").addClass("hidden");
        $("#preview-prev").removeClass("disabled");

        model = $("#model-select").val();
        if(model == "welcome"){
            $("#last-content").find("p").first().next().next().remove();
            $("#last-content").find("p").first().next().after(img_str);
        }
        
    }

    //获得焦点时设置时钟函数
    var checkInterval;
    function checkReceive(){
        checkInterval = setInterval("checkSplit()",100);
    }

    //检测输入，有分号自动执行newReceive()
    function checkSplit(){

        if($("#email-received").val()!="" || $("#receives").find("div.fl").text()!=""){
            $("#receive-next").removeClass("disabled");
        }else{
            $("#receive-next").addClass("disabled");
        }

        var receive = $("#email-received").val();
        if(receive.indexOf(";")>-1){
            var receive_arr = receive.split("\;");
            if(receive_arr[1].length>0){
                newReceive();
            }
        }
    }

    //新的收件人操作，已输入的转换成div
    function newReceive(){
        var str = $("#email-received").val();
        var str_arr = str.split("\;");

        var tag = 0;
        //从前面的输入找,如果有相同联系人的时候就提示已添加过，没有就转化成div
        $("#receives").find("div.fl").each(function(){
            if($(this).text().indexOf(str_arr[0])>-1){
                showHint("提示信息","已添加过此联系人!");
                tag = 1;
            }
        });

        //如果不是重复的输入，则将已输入的转化成div
        if(tag == 0){
            $("#email-received").before("<div class='fl'>"+str_arr[0]+";</div>");
            $("#receives").find("div.fl").each(function(){
                $(this).click(function(){
                    $("#receives").find("div.fl").removeClass("bg-66");
                    $(this).addClass("bg-66");
                });
            });
        }
        
        $("#email-received").val(str_arr[1]);
    }

    //删除键的操作，优先删除选中的邮箱，若输入框中为空的话就删除前面一个邮箱
    document.onkeydown = function(e){
        var keyPressed;  
        if(window.event){  
            keyPressed = window.event.keyCode;//IE和CHROME下有效  
        }else{  
            keyPressed = e.which;//火狐下捕获  
        }  
        if(keyPressed == 8){//8是删除键代码  
            if($("#receives").find(".bg-66").text()==""){           // 如果没有选中，就删除最后的一个
                if($("#email-received").val()==""){                  
                    $("#receives").find("div.fl").last().remove();
                }
            }else{                                                  //如果选中，就删除选中的一个
                $("#receives").find(".bg-66").remove();
            }
            var obj = e.target || e.srcElement;
            var t = obj.type || obj.getAttribute('type');
            if(t != "password" && t != "text" && t != "textarea"){          // 如果不是在输入框中，删除键无效
                return false;
            }
        }
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

    // 发送邮件具体操作
    email_type = $("#receive-type").find(".btn-success").text();

    model = $("#model-select").val();

    function sendEmail(){

        theme = $("#detail-theme").val();

        var content = $("#last-content").html();

        var user_id = "<?php echo $this->user->user_id;?>";

        var sender_email = "";
        if(sender == "HR"){
            sender_email = "hr@shanyougame.com";
        }else if(sender == "IT"){

        }else if(sender == "行政助理"){

        }

        var search_flag = 0;
        $("#last-receive").find("button").each(function(){
            if($(this).text()=="所有人"){
                search_flag = 1;
            }
        });


        if(email_type == "其他" || search_flag == 1){

            if(search_flag == 1){
                email_arr.push("all@shanyougame.com");
            }

            if(theme.length < 1){
                showHint("提示信息","请输入邮件主题！");
            }else if(content.length < 1){
                showHint("提示信息","请输入邮箱正文！");
            }else{
                showWait("正在发送邮件");
                $.ajax({
                    type:'post',
                    dataType:'json',
                    url:'/ajax/Mail',
                    data:{'user_id':user_id,'emails':email_arr,'sender_email':sender_email,'subject':theme,'message':content},
                    success:function(result){
                        if(result.code == 0)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","发送成功！");
                            // setTimeout(function(){location.reload();},1200);
                        }
                        else if(result.code == -1)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","发送失败！");
                        }
                        else if(result.code == -2)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","请输入正确的邮箱地址");
                        }
                        else if(result.code == -3)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","邮件主题不能为空！");
                        }
                        else if(result.code == -4)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","邮件正文不能为空！");
                        }
                        else
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","系统错误，请联系管理员");
                        }
                    }
                });
            }
        }else if(search_flag != 1){

            var user_ids = send_arr;

            if(user_ids.length == 0){
                showHint("提示信息","请选择收件人！");
            }else if(theme.length < 1){
                showHint("提示信息","请输入邮箱主题！");
            }else if(content.length < 1){
                showHint("提示信息","请输入邮箱正文！");
            }else{
                showWait("正在发送邮件");
                $.ajax({
                    type:'post',
                    dataType:'json',
                    url:'/ajax/CreateMailMany',
                    data:{'user_id':user_id,'sender_email':sender_email,'user_ids':user_ids,'subject':theme,'message':content},
                    success:function(result){
                        if(result.code == 0)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","发送成功！");
                            // setTimeout(function(){location.reload();},1200);
                        }
                        else if(result.code == -1)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","发送失败！");
                        }
                        else if(result.code == -2)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","收件人不能为空");
                        }
                        else if(result.code == -3)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","收件人不存在！");
                        }
                        else if(result.code == -4)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","发送邮件人邮箱格式不正确！");
                        }
                        else if(result.code == -5)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","发送邮件人不存在！");
                        }
                        else if(result.code == -6)
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","标题或内容不能为空！");
                        }
                        else
                        {
                            $("#page-wait").modal('hide');
                            showHint("提示信息","你没有权限执行此操作！");
                        }
                    }
                });
            }

            
        }
    }
</script>
