<?php
echo "<script type='text/javascript'>";
echo "console.log('businessTrip_summary_failed');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-b-1-ddd">出差记录</h4>
    <!-- 搜索栏 -->
    <div class="m0 p0 pd20">
        <div class="mb15">
            <label>快速搜索</label>
            <label class="ml10">姓名：</label>
            <input class="form-control w130 inline" id="search_name" ></input>
            <label class="ml10">月份：</label>
            <input class="form-control w130 inline" style="cursor:pointer;" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)" value="<?php echo empty($month) ? date('Y-m') : $month; ?>" id="search_date" ></input>
            <div class="inline-block">
                <label class="ml10">部门：</label>
                <select id="search_department" class="f15px w130 inline form-control">
                  <option value=" "> </option>
                  <?php foreach($departments as $department): ?>
                  <?php echo "<option value='{$department->department_id}' >{$department->name}</option>"; ?>
                  <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-success mt-5 ml10 w80" onClick="businessTripSummarySearch();">查询</button>
        </div>
        <!-- 类别标签 -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" ><a href="/oa/businessTrip_summary_wait">待审批</a></li>
            <li role="presentation" ><a href="/oa/businessTrip_summary">已通过</a></li>
            <li role="presentation" class="active"><a href="/oa/businessTrip_summary_failed">未通过</a></li>
        </ul>
        <!-- 出差记录-未通过表格 -->
        <?php if(!empty($msgs)): ?>
        <table  class="table table-striped table-bordered table-hover">
            <tr>
                <th class="hidden">ID</th>
                <th>内容</th>
                <th class="w200">日期</th>
            </tr>
            <?php
                foreach($msgs as $msg){
                    echo '<tr>';
                    echo "<td class='hidden'>{$msg->user->cn_name}</td>";
                    if(date('Y-m-d', strtotime($msg->start_time)) == date('Y-m-d', strtotime($msg->end_time))){
                         echo "<td><a href='/oa/outMsg/out/{$msg->out_id}/type/businessTrip_summary_failed'>{$msg->user->cn_name}--<b>出差地点:</b>&nbsp{$msg->place}---<b>出差时间:</b>&nbsp<b>".date('Y-m-d H:i', strtotime($msg->start_time))."到".date('H:i', strtotime($msg->end_time))."</a>";
                    }else{
                        echo "<td><a href='/oa/outMsg/out/{$msg->out_id}/type/businessTrip_summary_failed'>{$msg->user->cn_name}--<b>出差地点:</b>&nbsp{$msg->place}---<b>出差时间:</b>&nbsp<b>".date('Y-m-d H:i', strtotime($msg->start_time))."到".date('H:i', strtotime($msg->end_time))."</a>";
                    }
                    echo "<br>";
                    if(!empty($msg->members) && count($msg->members) > 1){
                        echo "<p class='gray pt5 m0'>同行人：";
                        foreach($msg->members as $mrow){
                            echo "<span class='ml5'>{$mrow->user->cn_name}</span>";
                        }
                        echo "</p>";
                    }
                    echo "</td>";
                    echo "<td>{$msg->create_time}</td>";
                    echo '</tr>';
                }
            ?>
        </table>

        <?php else: ?>
        <table  class="table table-striped table-bordered table-hover">
            <tr>
                <td class="center">没有记录</td>
            </tr>
        </table>
        <?php endif; ?> 

        <!-- 分页栏 -->
        <div id="page" class="w100%">
            <div class="w600 m0a">
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
                <?php
                   if($count>$size):
                   ?>
                <p class="pd5 f15px inline ml20">跳转到：</p>
                <button class="btn btn-default pd3" onclick="showPager();">&nbsp;<?php echo $page->currentPage+1; ?>&nbsp;&nbsp;<span class="right caret"></span></button>
                <p class="pd5 f15px inline ">页</p>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- 跳页模态框 -->
<div id="msgs_pager" class="modal fade in hint bor-rad-5 w500" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal" onclick="$('#agree').removeClass('disabled');$('#reject').removeClass('disabled');">×</a>
        <h4 class="hint-title">跳转</h4>
    </div>

    <div class="modal-body">
        <div class="overflow-a xh400">
            <label>点击页数进行跳转：</label>
            <?php
                for($i=1;$i<=$total;$i++){
                    if($page->currentPage+1 == $i){
                        echo "<a class='btn btn-default btn-block left mt10 active' href='/oa/businessTrip_summary_failed?page=".$i."'>".$i."</a>";
                    }else{
                        echo "<a class='btn btn-default btn-block left mt10' href='/oa/businessTrip_summary_failed?page=".$i."'>".$i."</a>";
                    }
                }
            ?>
        </div>
    </div>
</div>

<!--js-->
<script type="text/javascript">
    // 显示跳页模态框
    function showPager(){
        var ySet = (window.innerHeight - $("#msgs_pager").height())/3;
        var xSet = (window.innerWidth - $("#msgs_pager").width())/2;
        $("#msgs_pager").css("top",ySet);
        $("#msgs_pager").css("left",xSet);
        $('#msgs_pager').modal({show:true});
    }
    
    // 搜索
    function businessTripSummarySearch(){
        // 获取数据
        var date = $("#search_date").val();
        var name = $("#search_name").val();
        var department = $("#search_department").val();

        // 判断是否存在该员工
        var user_id = "";
        var user_find_tag = false;
        if(name != ""){
            $.each(users_arr, function(){
                if(this['name'] == name){
                    user_id = this['id'];
                    user_find_tag = true;
                    return false;
                }
            });
        }
        
        // 验证数据
        var date_pattern = /^\d{4}-\d{2}$/;
        if(date != "" && !date_pattern.exec(date)){
            showHint("提示信息","日期输入格式错误");
        }else if(name != "" && !user_find_tag){
            showHint("提示信息","查找不到该员工");
            $("#search_date").focus();
        }else{
            var date_str = "";
            if(date != ""){
                date_str = "/month/"+date;
            }
            var user_str = "";
            if(name != ""){
                user_str = "/user_id/"+user_id;
            }
            var department_str = "/department_id/"+department;
            location.href = "/oa/businessTripSummarySearch"+date_str+user_str+department_str;
        }
    }

    // 用户数组初始化
    var users_arr = new Array();
    var cn_name_arr = new Array();
    <?php
    if(!empty($users_names)){
        foreach($users_names as $users_name){
            echo "users_arr.push({'name':'{$users_name->cn_name}','id':'$users_name->user_id'});";
            echo "cn_name_arr.push('{$users_name->cn_name}');"; 
        }
    }
    ?>

    // 自动补全
    $( "#search_name" ).autocomplete({
        source: cn_name_arr
    });
</script>
