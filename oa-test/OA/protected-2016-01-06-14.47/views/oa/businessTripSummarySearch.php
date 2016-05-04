<?php
echo "<script type='text/javascript'>";
echo "console.log('businessTripSummarySearch');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery-ui.js"></script>
<!-- css -->
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/jquery-ui.css" />

<!-- 主界面 -->
<div>
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-1-ddd">出差记录</h4>
    <!-- 搜索栏 -->
    <div class="m0 p0 bor-1-ddd pd20">
        <div class="mb15">
            <label>快速搜索</label>
            <label class="ml10">姓名：</label>
            <input class="form-control w130 inline" id="search_name" ></input>
            <label class="ml10">月份：</label>
            <input class="form-control w130 inline" style="cursor:pointer;" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)" value="<?php echo empty($month) ? date('Y-m') : $month; ?>" id="search_date"></input>
            <div class="inline-block">
                <label class="ml10">部门：</label>
                <select id="search_department" class="f15px w130 inline form-control">
                  <option value="all">所有部门</option>
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
            <li role="presentation"><a href="/oa/businessTrip_summary_failed">未通过</a></li>
            <li role="presentation" class="active"><a href="/oa/businessTripSummarySearch">搜索结果</a></li>
	    </ul>
        <!-- 出差记录-搜索结果表格 -->
	    <table class="table table-striped table-hover table-bordered">
        <?php 
            if(!empty($out_summarys)): 
            if($out_summarys['0'] != array()):
        ?>
                <tr>
                    <th class="hidden">ID</th>
                    <th>内容</th>
                    <th class="w200">日期</th>
                </tr>
        <?php
            $types = array('casual'=>'事假','sick'=>'病假','funeral'=>'丧假','marriage'=>'婚假','maternity'=>'产假','annual'=>'年假','compensatory'=>'补假','others'=>'其他假');
            $status = array('success'=>'已通过', 'wait'=>'待审批', 'reject'=>'未通过');
            foreach($out_summarys as $out_summary){
                echo '<tr>';
                    echo "<td class='hidden'>{$out_summary->out_id}</td>";
                    if(date('Y-m-d', strtotime($out_summary->start_time)) == date('Y-m-d', strtotime($out_summary->end_time))){
                        echo "<td><a href=''/oa/outMsg/out/{$out_summary->out_id}/type/businessTripSummarySearch'>"."{$out_summary->user->cn_name}---{$out_summary->place}---".(empty($status[$out_summary->status])?'':$status[$out_summary->status])."---时间&nbsp&nbsp<b>".date('Y-m-d H:i', strtotime($out_summary->start_time))."</b>&nbsp到&nbsp<b>".date('H:i', strtotime($out_summary->end_time))."</b></a>";
                    }
                    else{
                         echo "<td><a href='/oa/outMsg/out/{$out_summary->out_id}/type/businessTripSummarySearch'>"."{$out_summary->user->cn_name}---{$out_summary->place}---".(empty($status[$out_summary->status])?'':$status[$out_summary->status])."---时间&nbsp&nbsp<b>".date('Y-m-d', strtotime($out_summary->start_time))."</b>&nbsp到&nbsp<b>".date('Y-m-d',strtotime($out_summary->end_time))."</b></a>";;
                    }
                    echo "<br>";
                    if(!empty($out_summary->members) && count($out_summary->members) > 1){
                        echo "<p class='gray pt5 m0'>同行人：";
                        foreach($out_summary->members as $mrow){
                            echo "<span class='ml5'>{$mrow->user->cn_name}</span>";
                        }
                        echo "</p>";
                    }
                    echo "</td>";
                    echo "<td>{$out_summary->create_time}</td>";
                    echo '</tr>';
            }
        ?>
        <?php else: ?>
            <tr>
                <td class="center">没有请假记录</td>
            </tr>
        <?php 
            endif;
            else:  ?>
            <tr>
                <td class="center">没有出差记录</td>
            </tr>
        <?php endif; ?>
        </table>
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
            </div>
        </div>
    </div>
</div>

<!--js-->
<script type="text/javascript">
    // 搜索
    function businessTripSummarySearch(){
        // 获取数据
        var date = $("#search_date").val();
        var name = $("#search_name").val();
        var department = $("#search_department").val();

        // 判断是否存在此员工
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

    // 页面初始化
    $(document).ready(function(){
        // 填充搜索部门
        $("#search_department").val("<?php echo empty($department_id) ? 'all' : $department_id; ?>");

        // 填充搜索员工名称
        var user_id = "<?php echo empty($user_id) ? '' : $user_id; ?>";
        if(user_id != ""){
            $.each(users_arr, function(){
                if(this['id'] == user_id){
                    $("#search_name").val(this['name']);
                }
            });
        }
    });  
</script>