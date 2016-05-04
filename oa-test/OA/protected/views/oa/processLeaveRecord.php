<?php
echo "<script type='text/javascript'>";
echo "console.log('processLeaveRecord');";
echo "</script>";
?>

<!-- js -->
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/DatePickerForMonth.js"></script>

<!-- 主界面 -->
<div class="bor-1-ddd">
    <!-- 标题  -->
	<h4 class="pd10 m0 b33 bor-b-1-ddd">审批记录</h4>

    <div class="m0 p0 pd20">
        <!-- 请假审批记录 -->
    	<table class="table table-striped table-hover table-bordered">
        <?php if(!empty($processLeaveRecord)): ?>
            <tr>
                <th class="hidden">ID</th>
                <th>内容</th>
                <th class="w200">日期</th>
            </tr>
         <?php
         $types = array('casual'=>'事假','sick'=>'病假','funeral'=>'丧假','marriage'=>'婚假','maternity'=>'产假','annual'=>'年假','compensatory'=>'补假','others'=>'其他假');
            foreach($processLeaveRecord as $processLeaveRecord){
                echo '<tr>';
                echo "<td class='hidden'>{$processLeaveRecord->leave_id}</td>";
                if(date('Y-m-d', strtotime($processLeaveRecord->start_time)) == date('Y-m-d', strtotime($processLeaveRecord->end_time)))
                {
                    echo "<td><a href='/oa/msg/leave/{$processLeaveRecord->leave_id}/type/processLeaveRecord'>"."{$processLeaveRecord->user->cn_name}--".(empty($types[$processLeaveRecord->type])?'':$types[$processLeaveRecord->type]) 
                    ."---时间:&nbsp&nbsp<b>".date('Y-m-d H:i', strtotime($processLeaveRecord->start_time))."</b>&nbsp到&nbsp<b>".date('H:i', strtotime($processLeaveRecord->end_time))."</b></a></td>";
                }
                else
                {
                    echo "<td><a href='/oa/msg/leave/{$processLeaveRecord->leave_id}/type/processLeaveRecord'>"."{$processLeaveRecord->user->cn_name}--".(empty($types[$processLeaveRecord->type])?'':$types[$processLeaveRecord->type]) 
                    ."---时间:&nbsp&nbsp<b>".date('Y-m-d', strtotime($processLeaveRecord->start_time))."</b>&nbsp到&nbsp<b>".date('Y-m-d', strtotime($processLeaveRecord->end_time))."</b></a></td>";
                }
                echo "<td>{$processLeaveRecord->create_time}</td>";
                echo '</tr>';
            }
        ?>

        <?php else: ?>
            <tr>
                <td class="center">没有新的消息</td>
            </tr>
        
        <?php endif; ?>
        </table>
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

<!-- 跳转页面模态框 -->
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
                        echo "<a class='btn btn-default btn-block left mt10 active' href='/oa/processLeaveRecord?page=".$i."'>".$i."</a>";
                    }
                    else
                    {
                        echo "<a class='btn btn-default btn-block left mt10' href='/oa/processLeaveRecord?page=".$i."'>".$i."</a>";
                    }
                }
            ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    // 显示跳页模态框
    function showPager(){
        var ySet = (window.innerHeight - $("#msgs_pager").height())/3;
        var xSet = (window.innerWidth - $("#msgs_pager").width())/2;
        $("#msgs_pager").css("top",ySet);
        $("#msgs_pager").css("left",xSet);
        $('#msgs_pager').modal({show:true});
    }
</script>
