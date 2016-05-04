<?php
echo "<script type='text/javascript'>";
echo "console.log('msgs');";
echo "</script>";
?>

<div>
    <div class="m0 p0 bor-1-ddd pd20 ">
        <!-- 标题 -->
        <h4 class="mb15 pl5 f20px">
            <strong>消息列表</strong>
        </h4>
        <!-- 消息类型切换标签 -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" <?php if($status==''){ echo "class='active'"; } ?>><a href="/user/msgs">全部</a></li>
            <li role="presentation" <?php if($count_wait>0){if($status=='wait'){ echo "class='active'";}}else{ echo "class='hidden'";} ?>><a href="/user/msgs/status/wait">未读(<?php echo $count_wait;?>)</a></li>
        </ul>
        <!-- 消息列表表格 -->
        <?php if(!empty($msgs)): ?>
        <table  class="table table-striped table-hover bor-1-ddd">
            <tr>
                <th class="hidden">ID</th>
                <th></th>
                <th class="center w80">来源</th>
                <th>内容</th>
                <th class="w200">日期</th>
            </tr>
        <?php
            foreach($msgs as $msg){
                echo '<tr>';
                echo "<td class='hidden'>{$msg->id}</td>";
                if($msg->status == "wait"){
                    echo "<td class='w36 center'><img src='/images/msg_wait.png' class='w20 h20'></td>";
                }else{
                    echo "<td class='w36 center'><img src='/images/msg_read.png' class='w20 h20'></td>";
                }
                echo "<td class='center'>{$types["$msg->type"]}</td>";
                if(!empty($msg->title)){
                    echo "<td><a class='pointer' onclick='goToMsg(this, \"{$msg->id}\");'>{$msg->title}</a></td>";
                }else{
                    echo "<td><a class='pointer' onclick='goToMsg(this, \"{$msg->id}\");'>{$msg->content}</a></td>";
                }
                echo "<td>";
                if(date('Y-m-d',strtotime($msg->create_time)) == date('Y-m-d') ){
                    $time = floor((strtotime(date('Y-m-d H:i:s'))-strtotime($msg->create_time))/60);
                    if($time < 3){
                        echo "刚刚"; 
                    }elseif($time < 60){
                        echo "{$time}分钟前";
                    }else{
                        echo floor($time/60).'小时前';
                    }
                }else{
                    if( date('Y-m-d',strtotime($msg->create_time)) == date('Y-m-d', strtotime('-1day', strtotime(date('Y-m-d')))) ){
                        echo '昨天&nbsp&nbsp'.date('H:i', strtotime($msg->create_time));
                    }else{
                        echo date('Y-m-d',strtotime($msg->create_time));
                    }   
                }
                echo "</td>";
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
        <!-- 底部栏 -->
        <div id="page" class="w100%">
            <!-- 全部标为已读 -->
            <div class="inline-block w300">
                <?php if($count_wait > 0):?>
                <button class="btn btn-success pd5" onclick="markAllRead();">全部标为已读</button>
                <?php else: ?>
                <button class="btn btn-success pd5 hidden" onclick="markAllRead();">全部标为已读</button>
                <?php endif; ?>
            </div>
            <!-- 分页栏 -->
            <div class="w600 ml50 inline-block">
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
                // 判断消息状态
                if($status=='wait'){
                    for($i=1;$i<=$total;$i++){
                        if($page->currentPage+1 == $i){
                            echo "<a class='btn btn-default btn-block left mt10 active' href='/user/msgs?page=".$i."'>".$i."</a>";
                        }else{
                            echo "<a class='btn btn-default btn-block left mt10' href='/user/msgs?page=".$i."'>".$i."</a>";
                        }
                    }
                } else{
                    for($i=1;$i<=$total;$i++){
                        if($page->currentPage+1 == $i){
                            echo "<a class='btn btn-default btn-block left mt10 active' href='/user/msgs?page=".$i."'>".$i."</a>";
                        }else{
                            echo "<a class='btn btn-default btn-block left mt10' href='/user/msgs?page=".$i."'>".$i."</a>";
                        }
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
        var ySet = (window.innerHeight - $("#msgs_pager").height())/2;
        var xSet = (window.innerWidth - $("#msgs_pager").width())/2;
        $("#msgs_pager").css("top",ySet);
        $("#msgs_pager").css("left",xSet);
        $('#msgs_pager').modal({show:true});
    }

    // 全部标为已读
    function markAllRead(){
        $.ajax({
            type:'post',
            dataType:'json',
            url:'/ajax/markAllRead',
            data:{},
            success:function(result){
                if(result.code == 0){
                    location.href="/user/msgs";
                }else if(result.code == -1){
                    showHint("提示信息","设置已读失败！");
                }else if(result.code == -2){
                    showHint("提示信息","没有未读消息");
                }else{
                    showHint("提示信息","你没有权限执行此操作！");
                }
            }
        });
    }

    function goToMsg(obj, id){
        var href_str = 'user/msgDetail/id/'+id;
        $(obj).parent().parent().find("img").attr("src","/images/msg_read.png");
        location.href = href_str;
    }
</script>
