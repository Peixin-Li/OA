<?php
echo "<script type='text/javascript'>";
echo "console.log('leaveSummary');";
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
	<h4 class="pd10 m0 b33 bor-b-1-ddd">请假记录</h4>
    <!-- 搜索 -->
    <div class="m0 p0 pd20">
        <div class="mb15">
            <label>快速搜索</label>
            <label class="ml10">姓名：</label>
            <input class="form-control w130 inline" id="search_name" ></input>
            <label class="ml10">月份：</label>
            <input class="form-control w130 inline" style="cursor:pointer;" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)" value="<?php //if($month=="") echo date('Y-m'); else echo $month; ?>" id="search_date" ></input>
            <div class="inline-block">
                <label class="ml10">部门：</label>
                <select id="search_department" class="f15px inline form-control w130">
                  <option value=" "> </option>
                  <?php foreach($departments as $department): ?>
                  <?php echo "<option value='{$department->name}' >{$department->name}</option>"; ?>
                  <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-success mt-5 ml10 w80" onClick="leaveSummarySearch();">查询</button>
        </div>
    <!-- 可选标签 -->
	<ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="/oa/leaveSummaryWait">待审批</a></li>
        <li role="presentation" class="active"><a href="/oa/leaveSummary">已通过</a></li>
        <li role="presentation" ><a href="/oa/leaveSummaryFailed">未通过</a></li>
    </ul>
    <!-- 请假记录 -->
    <?php if(!empty($LeaveSummarys)): ?>
    <table class="table table-striped table-bordered table-hover">
    	<tr>
            <th class="hidden">ID</th>
            <th>内容</th>
            <th class="w200">日期</th>
        </tr>
     <?php
     $types = array('casual'=>'事假','sick'=>'病假','funeral'=>'丧假','marriage'=>'婚假','maternity'=>'产假','annual'=>'年假','compensatory'=>'补假','others'=>'其他假');
        foreach($LeaveSummarys as $msg){
            echo '<tr>';
            echo "<td class='hidden'>{$msg->leave_id}</td>";
            if(date('Y-m-d', strtotime($msg->start_time)) == date('Y-m-d', strtotime($msg->end_time)))
            {
                echo "<td><a href='/oa/msg/leave/{$msg->leave_id}/type/leaveSummary'>{$msg->user->cn_name}---".(empty($types[$msg->type])?'':$types[$msg->type])."---时间:&nbsp&nbsp<b>".date('Y-m-d H:i', strtotime($msg->start_time))."</b>&nbsp到&nbsp<b>".date('H:i', strtotime($msg->end_time))."</b></a></td>";
            }
            else
            {
                echo "<td><a href='/oa/msg/leave/{$msg->leave_id}/type/leaveSummary'>{$msg->user->cn_name}---".(empty($types[$msg->type])?'':$types[$msg->type])."---时间:&nbsp&nbsp<b>".date('Y-m-d', strtotime($msg->start_time))."</b>&nbsp到&nbsp<b>".date('Y-m-d', strtotime($msg->end_time))."</b></a></td>";
            }
            echo "<td>{$msg->create_time}</td>";
            echo '</tr>';
        }
    ?>
    </table>
    <?php else: ?>
    <table  class="table table-striped table-bordered table-hover">
        <tr>
            <td class="center">没有新的消息</td>
        </tr>
    </table>
    <?php endif; ?>
    <!-- 分页 -->
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
        ));
        ?>
        <?php if($count>$size): ?>
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
                        if($page->currentPage+1 == $i)
                        {
                            echo "<a class='btn btn-default btn-block left mt10 active' href='/oa/leaveSummary?page=".$i."'>".$i."</a>";
                        }
                        else
                        {
                            echo "<a class='btn btn-default btn-block left mt10' href='/oa/leaveSummary?page=".$i."'>".$i."</a>";
                        }
                    }
            ?>
        </div>
    </div>
</div>

<!-- js -->
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
    function leaveSummarySearch(){
        var date = $("#search_date").val();
        var name = $("#search_name").val();
        var department = $("#search_department").val();
        var date_pattern = /^\d{4}-\d{2}$/;
        if(date == "" && name == "" && department == ""){
            showHint("提示信息","请输入搜索条件！");
        }else{
            var href_str = "/oa/leaveSummarySearch";
            if(date!=""){
                if(!date_pattern.exec(date)){
                    showHint("提示信息","日期格式输入错误！");
                    return false;
                }
                var date_str = "/date/"+date;
                href_str += date_str;
            }
            if(name!=""){
                var name_str = "/name/"+name;
                href_str += name_str;
            }
            if(department!=" "){
                var department_str = "/department/"+department;
                href_str += department_str;
            }
            location.href= href_str;
        }
        
    }

    // 自动补全
    var availableTags = new Array();
    <?php
    if(!empty($users_names))
    {
        foreach($users_names as $users_name)
        {
            echo "availableTags.push('{$users_name->cn_name}');";
        }
    }
    ?>
    $( "#search_name" ).autocomplete({
        source: availableTags
    });
</script>