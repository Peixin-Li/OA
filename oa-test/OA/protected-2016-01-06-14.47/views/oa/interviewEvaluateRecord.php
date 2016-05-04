<?php
echo "<script type='text/javascript'>";
echo "console.log('interviewEvaluateRecord');";
echo "</script>";
?>

<div class="bor-1-ddd">
    <!-- 标题 -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">面试评估记录</h4>
	<div class="m0 p0 pd20">
        <!-- 类别标签 -->
		<ul class="nav nav-tabs" role="tablist">
            <li role="presentation" <?php if($status=='wait'){ echo 'class="active"';} ?>><a class="pointer" onclick="switchTabs(this);">待评估</a></li>
	        <li role="presentation"  <?php if($status=='success'){ echo 'class="active"';} ?>><a class="pointer" onclick="switchTabs(this);">已通过</a></li>
	        <li role="presentation"  <?php if($status=='reject'){ echo 'class="active"';} ?>><a class="pointer" onclick="switchTabs(this);">未通过</a></li>
	        <li role="presentation"  <?php if($status=='giveup'){ echo 'class="active"';} ?>><a class="pointer" onclick="switchTabs(this);">放弃入职</a></li>
        </ul>
        <!-- 面试评估记录 -->
        <table class="table table-bordered">
            <?php if(!empty($assessments)): ?>
        	<thead>
        		<tr>
        			<th>内容</th>
        			<th class="w200">日期</th>
        		</tr>
        	</thead>
        	<tbody>
                <?php foreach($assessments as $row): ?>
        		<tr>
                <td><a href="/oa/interviewEvaluateDetail/id/<?php echo $row->resume->id; ?>"><?php echo "{$row->resume->name}-{$row->resume->apply->title}-{$row->resume->source}"; ?></a></td>
                    <td><?php echo date('Y-m-d',strtotime($row->create_time)); ?></td>
        		</tr>	
                <?php endforeach; ?>
        	</tbody>
            <?php else:?>
            <tr><td colspan="2"><h4 class="center">没有记录</h4></td></tr>
            <?php endif; ?>
        </table>
        <!-- 分页 -->
        <div class="w600 m0a center">
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

<!-- js -->
<script type="text/javascript">
    // 切换标签
	function switchTabs(obj){
    	var click_obj = $(obj).text();
    	switch(click_obj){
    		case "待评估":{
    			location.href = "/oa/interviewEvaluateRecord/status/wait";
    			break;
    		}
    		case "已通过":{
    			location.href = "/oa/interviewEvaluateRecord/status/success";
    			break;
    		}
    		case "未通过":{
    			location.href = "/oa/interviewEvaluateRecord/status/reject";
    			break;
    		}
    		case "放弃入职":{
    			location.href = "/oa/interviewEvaluateRecord/status/giveup";
    			break;
    		}
    	}
    }
</script>
	
