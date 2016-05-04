<?php
echo "<script type='text/javascript'>";
echo "console.log('leaveForm');";
echo "</script>";
?>

<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/menu.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />
<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>


<p class="hidden" id="month"><?php echo date('Y-m'); ?></p>
<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-l-1-ddd bor-t-1-ddd bor-r-1-ddd">请假报表</h4>
    <div class="m0 p0">
    <div id="leaveform_div" class="m0 p00">
    <!-- 搜索 -->
    <div class="m0 p0 bg-fa pd8 bor-l-1-ddd bor-t-1-ddd bor-r-1-ddd" >
        <label class="ml10">请选择日期：</label>
        <input class="form-control w130 inline" style="cursor:pointer;" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)" value="<?php if(!empty($month)) echo $month;?>" id="search-input"></input>
        <button class="btn btn-success m0 ml10 mt-2" onClick="leaveFormQuery();">查看报表</button>
        <a class="btn btn-success fr mr10 pointer" target="_blank" href="/oa/LeaveReport/month/<?php if(!empty($month)) echo $month;?>" id="excel-btn"><span class="glyphicon glyphicon-new-window"></span>&nbsp;导出报表</a>
    </div>
    <!-- 请假报表 -->
    <table class="table table-striped table-bordered table-hover f13 center leaveform-table m0" id="leaveform-table">
        <thead>
            <tr>
                <th class="center w80">月份</th>
                <th class="center w80">部门</th>
                <th class="center w80">姓名</th>
                <th class="center w80">应出勤</th>
                <th class="center">事假</th>
                <th class="center">病假</th>
                <th class="center">婚假</th>
                <th class="center">丧假</th>
                <th class="center">年假</th>
                <th class="center">陪产假</th>
                <th class="center">补休</th>
                <th class="center">其他假</th>
                <th class="center w150">备注</th>
            </tr>
        </thead>
<?php 
if(!empty($leavemonthreport) && !empty($month)){
      echo '<tbody>';
      $month_tag = 0;
      $count = count($leavemonthreport);
      foreach($leavemonthreport as $leave){
        echo '<tr>';
        echo "<td class='hidden leave-id-td'>{$leave->id}</td>";
        if($month_tag == 0){
            $date = date('m',strtotime($leave->month));
            if($date < 10) $date -= "0";
            echo "<td rowspan='{$count}'>{$date}月份</td>";
            $month_tag = 1;
        }
        echo "<td class='department-name'>{$leave->user->department->name}</td>";
        echo "<td>{$leave->user->cn_name}</td>";

        $count = 0;
        for($i = date('Y-m-01', strtotime($month)); $i <= date('Y-m-t', strtotime($month)); $i = date('Y-m-d', strtotime("+1days",strtotime($i)))){
            if(date('w', strtotime($i)) >= 1 && date('w', strtotime($i)) <= 5){
                $count ++;
            }
        }
        echo "<td>{$count}天</td>";
        if($leave->casual != 0){
            echo "<td class='w80'>{$leave->casual}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->sick != 0){
            echo "<td class='w80'>{$leave->sick}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->marriage != 0){
            echo "<td class='w80'>{$leave->marriage}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->funeral != 0){
            echo "<td class='w80'>{$leave->funeral}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->annual != 0){
            echo "<td class='w80'>{$leave->annual}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->maternity != 0){
            echo "<td class='w80'>{$leave->maternity}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->compensatory != 0){
            echo "<td class='w80'>{$leave->compensatory}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if($leave->others != 0){
            echo "<td class='w80'>{$leave->others}天</td>";
        }else{
            echo "<td class='w80'></td>";
        }
        if(!empty($leave->content)){
            echo "<td style='word-break:all;'>{$leave->content}</td>";
        }else{
            if($department){
                echo "<td><button class='btn btn-success content-btn'>输入备注</button></td>";
            }else{
                echo "<td></td>";
            }
            
        }
        echo '</tr>';
      }  
      echo '</tbody>';
    }
    else{
        echo "<tbody><tr><td colspan='12' class='f18px'>本月没有人请假</td></tr></tbody>";
    }

    ?>

    </table>
    </div>
</div>
</div>

<!-- 输入备注模态框 -->
<div id="content-div" class="modal fade in hint bor-rad-5 w400" style="display: none; ">
    <div class="modal-header bg-33 move" onmousedown="beforeMove($(this).parent().attr('id'),event);">
        <a class="close" data-dismiss="modal">×</a>
        <h4 class="hint-title">输入备注</h4>
    </div>

    <div class="modal-body">
        <label>备注：</label>
        <textarea type="text" class="form-control inline" id="content-input"></textarea>
    </div>

    <div class="modal-footer">
        <button class="w100 btn btn-success" onclick="contentSubmit()">提交</button>
    </div>
</div>

<!-- js -->
<script type="text/javascript">
    // 注册按钮点击事件
    var content_id = "";
    $("button.content-btn").click(function(){
        content_id = $(this).parent().parent().find("td.leave-id-td").text();
        var ySet = (window.innerHeight - $("#content-div").height())/3;
        var xSet = (window.innerWidth - $("#content-div").width())/2;
        $("#content-div").css("top",ySet);
        $("#content-div").css("left",xSet);
        $("#content-div").modal({show:true});
    });

    // 提交备注
    function contentSubmit(){
        var id = content_id;
        var content = $("#content-input").val();
        if(content == ""){
            showHint("提示信息","请输入备注！");
        }else{
            $.ajax({
                type:'post',
                dataType:'json',
                url:'/ajax/addLeaveFormComment',
                data:{'id':id,'content':content},
                success:function(data){
                    if(data.code == 0){
                        showHint("提示信息","添加备注成功");
                        setTimeout(function(){location.reload();},1200);
                    }else if(data.code == -1){
                        showHint("提示信息","添加备注失败！");
                    }else if(data.code == -2){
                        showHint("提示信息","参数错误！");
                    }else if(data.code == -3){
                        showHint("提示信息","找不到该请假记录！");
                    }else if(data.code == -99){
                        showHint("提示信息","你没有权限进行此操作！");
                    }
                }
            });
        }
    }

    // 按月份查询
    function leaveFormQuery(){
        var search_str = $("#search-input").val();
        var date_pattern = /^\d{4}-\d{2}$/;
        if(search_str == ""){
            showHint("提示信息","请输入月份");
        }else if(!date_pattern.exec(search_str)){
            showHint("提示信息","月份输入格式错误");
        }else{
            var href_str = "/oa/leaveForm/month/"+search_str;
            location.href = href_str;
        }
    }

    // 合并部门名称单元格
    $(document).ready(function(){
        var count = 0;
        var cur_department = "";
        var department_num = 1;
        $(".department-name").each(function(){
            if(cur_department != $(this).text()){
                // 给这个td赋值

                var department_id = "department-"+department_num;
                $(this).attr("id",department_id);
                $("#department-"+(parseInt(department_num)-1)).attr("rowspan",count);
                department_num++;

                count = 1;
                cur_department = $(this).text();
            }else{
                count ++;
                $(this).remove();
            }
        });
        $("#department-"+(parseInt(department_num)-1)).attr("rowspan", count);
    });
</script>


