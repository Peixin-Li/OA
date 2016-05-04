<?php
echo "<script type='text/javascript'>";
echo "console.log('mailList');";
echo "</script>";
?>

<!-- 主界面 -->
<div>
    <!-- 标题 -->
    <h4 class="pd10 m0 b33 bor-1-ddd">邮件列表</h4>

    <div  class="bor-1-ddd pd20">
        <!-- 可选标签:全部+未发送 -->
    	<ul class="nav nav-tabs" role="tablist">
	        <li role="presentation" <?php if($status==''){ echo "class='active'"; } ?>><a href="/oa/mailList">全部</a></li>
	        <li role="presentation" <?php if($count_wait>0){if($status=='wait'){ echo "class='active'";}}else{ echo "class='hidden'";} ?>><a href="/oa/mailList/status/wait">未发送(<?php echo $count_wait;?>)</a></li>
	    </ul>
        <!-- 邮件列表 -->
	    <table class="table bor-l-1-ddd bor-b-1-ddd bor-r-1-ddd table-hover table-striped" style="word-break:keep-all;">
	    	<thead>
                <tr>
                    <th class="center w50">状态</th>
                    <th class="center w200">发件人</th>
                    <th class="w300">收件人</th>
                    <th class="w300">主题</th>
                    <th class="w200">发送时间</th>
                    <th class='w130 center'>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($mails)): ?>
                <?php foreach($mails as $mail): ?>
    	    	<tr>
                    <?php
                        echo "<td class='hidden'>{$mail->mail_id}</td>";
                        if($mail->status=="success"){
                            echo "<td class='center w50'><a class='inherit' title='发送成功'><span class='glyphicon glyphicon-ok'></span></a></td>";
                        }else if($mail->status=="fail"){
                            echo "<td class='center w50'><a class='inherit' title='发送失败'><span class=' glyphicon glyphicon-remove'></span></a></td>";
                        }else{
                            echo "<td class='center w50'><a class='inherit' title='未发送'><span class='glyphicon glyphicon-time'></span></a></td>";
                        }
                        
                        echo "<td class='w200 center'>{$mail->sender_email}</td>";
                        echo "<td class='overflow-hidden w300 xw300'>{$mail->receive_email}</td>";
                        echo "<td class='overflow-hidden w300 xw300'>{$mail->subject}</td>"; 
                        
                     echo "<td class='w200 '>"; 
                      
                    if(date('Y-m-d',strtotime($mail->create_time)) == date('Y-m-d') )
                    {
                        $time = floor((strtotime(date('Y-m-d H:i:s'))-strtotime($mail->create_time))/60);
                        if($time < 60)
                        {
                            echo "{$time}分钟前";
                        }
                        else
                        {
                            echo floor($time/60).'小时前';
                        }
                    }
                    else
                    {
                        if( date('Y-m-d',strtotime($mail->create_time)) == date('Y-m-d', strtotime('-1day', strtotime(date('Y-m-d')))) )
                        {
                            echo '昨天&nbsp&nbsp'.date('H:i');
                        }
                        else
                        {
                            echo date('Y-m-d',strtotime($mail->create_time));
                        }   
                    }
                        echo "</td>";
                        echo "<td class='w130 center'><button class='btn btn-default'>查看详情</button></td>";
                    ?>
    	    	</tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
	    </table>
    <!-- 分页 -->
    <div class="m0a w500">
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
    // 页面初始化
    $(document).ready(function(){
        // 注册点击事件
        $("tbody").children().addClass("pointer");
        $("tbody").children().click(function(){
            var id = $(this).children().first().text();
            mailDetail(id);
        });
    });

    // 查看邮件详情
    function mailDetail(id){
        var str = "/oa/mailDetail/"+id;
        location.href = str;
    }
</script>
