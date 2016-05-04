<?php
echo "<script type='text/javascript'>";
echo "console.log('recruitApplySummary');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">招聘申请记录</h4>
	<div class="m0 p0 pd20">
        <!-- 快速搜索 -->
        <div class="mb15">
            <label>快速搜索</label>
            <label class="ml10">月份：</label>
            <input class="form-control w130 inline" style="cursor:pointer;" onclick="setmonth(this,'yyyy-MM','2014-10-1','2014-10-2',1)" value="<?php //if($month=="") echo date('Y-m'); else echo $month; ?>" id="search_date" ></input>
            <div class="inline-block">
                <label class="ml10">部门：</label>
                <select id="search_department" class="f15px w130 inline form-control">
                  <option value="all">所有部门</option>
                  <?php 
                    foreach($departments as $department_detail){
                        echo "<option value='{$department_detail->department_id}'>{$department_detail->name}</option>";
                    }
                  ?>
                </select>
            </div>
            <button class="btn btn-success mt-5 ml10 w80" onClick="recruitApplySummarySearch();">查询</button>
        </div>
        <!-- 可选标签 -->
	    <ul class="nav nav-tabs" role="tablist">
	        <li role="presentation" class="<?php if($status == 'wait') echo 'active';?>"><a class="pointer" onclick="switchTabs(this);">待审批</a></li>
	        <li role="presentation" class="<?php if($status == 'success') echo 'active';?>"><a class="pointer" onclick="switchTabs(this);">已通过</a></li>
	        <li role="presentation" class="<?php if($status == 'reject') echo 'active';?>"><a class="pointer" onclick="switchTabs(this);">未通过</a></li>
	        <li role="presentation" class="<?php if($status == 'entry') echo 'active';?>"><a class="pointer" onclick="switchTabs(this);">已确定</a></li>
	        <li role="presentation" class="<?php if(!empty($date) || !empty($department)) echo 'active';else echo 'hidden';?>"><a class="pointer" onclick="switchTabs(this);">搜索结果</a></li>
        </ul>
        <!-- 招聘申请记录 -->
	    <table class="table bor-1-ddd m0 f15px">
    		<tr>
                <th class="center w20"></th>
            	<th>内容</th>
            	<th class="w200">申请人</th>
            	<th class="w200">日期</th>
    		</tr>
            <?php if(!empty($data)): ?>
                <?php foreach($data as $row): ?>
    	    		<tr>
                    <td class="center">
                        <?php 
                            if($row->status == "wait"){
                                echo "<span class='glyphicon glyphicon-time'>";
                            }else if($row->status == "success" || $row->status == 'entry'){
                                echo "<span class='glyphicon glyphicon-ok b5c'>";
                            }else{
                                echo "<span class='glyphicon glyphicon-remove b2'>";
                            }
                        ?>
                    </td>
                    <td>
                        <a href="/oa/recruitApplyDetail/id/<?php echo $row->id; ?>/type/recruitApplySummary">
                            <?php 
                                echo "{$row->department}-";
                                if($row->type == "replace"){
                                    echo "编制内替代";
                                }else if($row->type == "internal"){
                                    echo "编制内增补";
                                }else if($row->type == "add"){
                                    echo "编制外增补";
                                }
                                echo "-【{$row->title}】-{$row->number}人"; 
                            ?>
                        </a>
                    </td>
                        <td><?php echo $row->user->cn_name; ?></td>
                        <td><?php echo date('Y-m-d',strtotime($row->create_date)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="center f18px">没有申请记录</td>
                </tr>
            <?php endif; ?>
        </table>
        <!-- 分页 -->
        <div class="pd20">
        <div class="w500 m0a">
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

<!-- js -->
<script type="text/javascript">
    function recruitApplySummarySearch(){
        var date = $("#search_date").val();
        var department = $("#search_department").val();
        var date_pattern = /^\d{4}-\d{2}$/;
        if(date == ""  && department == "all"){
            showHint("提示信息","请输入搜索条件！");
        }else{
            var href_str = "/oa/recruitApplySummary/status/all";
            if(date!=""){
                if(!date_pattern.exec(date)){
                    showHint("提示信息","日期格式输入错误！");
                    return false;
                }
                var date_str = "/date/"+date;
                href_str += date_str;
            }
            if(department!=" "){
                var department_str = "/department/"+department;
                href_str += department_str;
            }
            location.href= href_str;
        }   
    }

    function switchTabs(obj){
    	var click_obj = $(obj).text();
    	switch(click_obj){
    		case "待审批":{
    			location.href = "/oa/recruitApplySummary/status/wait";
    			break;
    		}
    		case "已通过":{
    			location.href = "/oa/recruitApplySummary/status/success";
    			break;
    		}
    		case "未通过":{
    			location.href = "/oa/recruitApplySummary/status/reject";
    			break;
    		}
    		case "已确定":{
    			location.href = "/oa/recruitApplySummary/status/entry";
    			break;
    		}
    		case "搜索结果":{
    			recruitApplySummarySearch();
    			break;
    		}
    	}
    }

    <?php if(!empty($department)): ?>
    $("#search_department").val("<?php echo $department; ?>");
    <?php endif; ?>
    <?php if(!empty($date)): ?>
    $("#search_date").val("<?php echo $date;?>");
    <?php endif; ?>
</script>
